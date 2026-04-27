<?php
require_login();
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/input.php';
require_once __DIR__.'/../lib/shops.php';

$uid = current_user()['id'];
$action = input_string($_POST['action'] ?? '', 32);
$pdo = db();

function petting_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function petting_fetch_pet_row(PDO $pdo, int $userId, int $petId, bool $forUpdate = false): ?array
{
    $sql = "SELECT pi.pet_instance_id,
                   pi.owner_user_id,
                   pi.nickname,
                   pi.hunger,
                   pi.happiness,
                   pi.hp_current,
                   pi.hp_max,
                   ps.species_name
              FROM pet_instances pi
              JOIN pet_species ps ON ps.species_id = pi.species_id
             WHERE pi.pet_instance_id = ? AND pi.owner_user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$petId, $userId]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);

    return $pet ?: null;
}

function petting_build_pet_state(array $pet): array
{
    $maxHealth = max(1, (int)($pet['hp_max'] ?? 100));
    $health = max(0, min((int)($pet['hp_current'] ?? 0), $maxHealth));

    return [
        'id' => (int)$pet['pet_instance_id'],
        'name' => ($pet['nickname'] ?? '') !== '' ? $pet['nickname'] : (string)$pet['species_name'],
        'hunger' => max(0, min((int)($pet['hunger'] ?? 0), 100)),
        'health' => $health,
        'maxHealth' => $maxHealth,
        'happiness' => max(0, min((int)($pet['happiness'] ?? 0), 100)),
    ];
}

function petting_fetch_inventory_item(PDO $pdo, int $userId, int $itemId, string $categoryName, bool $forUpdate = false): ?array
{
    $sql = "SELECT ui.item_id,
                   ui.quantity,
                   i.item_name,
                   COALESCE(i.replenish, 0) AS replenish
              FROM user_inventory ui
              JOIN items i ON i.item_id = ui.item_id
              LEFT JOIN item_categories ic ON ic.category_id = i.category_id
             WHERE ui.user_id = ? AND ui.item_id = ? AND ic.category_name = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $itemId, $categoryName]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    return $item ?: null;
}

function petting_consume_inventory_item(PDO $pdo, int $userId, int $itemId, int $currentQuantity): int
{
    $nextQuantity = max(0, $currentQuantity - 1);
    if ($nextQuantity > 0) {
        $stmt = $pdo->prepare(
            "UPDATE user_inventory SET quantity = ? WHERE user_id = ? AND item_id = ?"
        );
        $stmt->execute([$nextQuantity, $userId, $itemId]);
    } else {
        $stmt = $pdo->prepare(
            "DELETE FROM user_inventory WHERE user_id = ? AND item_id = ?"
        );
        $stmt->execute([$userId, $itemId]);
    }

    return $nextQuantity;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo) {
        petting_json_response(['ok' => false, 'message' => 'The database is unavailable right now.'], 500);
    }

    if ($action === 'fetch_hunger') {
        $petId = input_int($_POST['pet_id'] ?? 0, 1);
        $pet = petting_fetch_pet_row($pdo, $uid, $petId);
        if (!$pet) {
            petting_json_response(['ok' => false, 'message' => 'That pet is not available.'], 404);
        }

        $state = petting_build_pet_state($pet);
        petting_json_response(['ok' => true, 'hunger' => $state['hunger'], 'pet' => $state]);
    }

    if ($action === 'sync_hunger') {
        $petId = input_int($_POST['pet_id'] ?? 0, 1);
        $hunger = max(0, min(input_int($_POST['hunger'] ?? 0), 100));
        $pet = petting_fetch_pet_row($pdo, $uid, $petId);
        if (!$pet) {
            petting_json_response(['ok' => false, 'message' => 'That pet is not available.'], 404);
        }

        $stmt = $pdo->prepare(
            "UPDATE pet_instances SET hunger = ? WHERE pet_instance_id = ? AND owner_user_id = ?"
        );
        $stmt->execute([$hunger, $petId, $uid]);

        $pet['hunger'] = $hunger;
        petting_json_response(['ok' => true, 'hunger' => $hunger, 'pet' => petting_build_pet_state($pet)]);
    }

    if ($action === 'pet') {
        $petId = input_int($_POST['pet_id'] ?? 0, 1);
        $gain = max(1, min(input_int($_POST['gain'] ?? 1), 24));

        try {
            $pet = petting_fetch_pet_row($pdo, $uid, $petId);
            if (!$pet) {
                petting_json_response(['ok' => false, 'message' => 'That pet is not available.'], 404);
            }

            $stmt = $pdo->prepare(
                "UPDATE pet_instances
                    SET happiness = LEAST(100, COALESCE(happiness, 0) + ?)
                  WHERE pet_instance_id = ? AND owner_user_id = ?"
            );
            $stmt->execute([$gain, $petId, $uid]);

            $pet = petting_fetch_pet_row($pdo, $uid, $petId);
            if (!$pet) {
                petting_json_response(['ok' => false, 'message' => 'That pet is not available.'], 404);
            }

            petting_json_response(['ok' => true, 'pet' => petting_build_pet_state($pet)]);
        } catch (Throwable $e) {
            app_add_error_from_exception($e, 'Petting pet action failed:');
            petting_json_response(['ok' => false, 'message' => 'Petting progress could not be updated.'], 500);
        }
    }

    if ($action === 'feed') {
        $petId = input_int($_POST['pet_id'] ?? 0, 1);
        $itemId = input_int($_POST['item_id'] ?? 0, 1);

        try {
            $pet = petting_fetch_pet_row($pdo, $uid, $petId);
            if (!$pet) {
                petting_json_response(['ok' => false, 'message' => 'That pet is not available.'], 404);
            }

            $item = petting_fetch_inventory_item($pdo, $uid, $itemId, 'Food');
            if (!$item || (int)$item['quantity'] <= 0) {
                petting_json_response(['ok' => false, 'message' => 'That food item is no longer available.'], 409);
            }

            $currentState = petting_build_pet_state($pet);
            if ($currentState['hunger'] >= 100) {
                petting_json_response([
                    'ok' => false,
                    'message' => $currentState['name'] . ' is already full.',
                    'pet' => $currentState,
                ]);
            }

            $hungerGain = max(1, (int)$item['replenish']);
            $happinessGain = 6;
            $nextHunger = min(100, $currentState['hunger'] + $hungerGain);
            $nextHappiness = min(100, $currentState['happiness'] + $happinessGain);

            $updatePet = $pdo->prepare(
                "UPDATE pet_instances
                    SET hunger = ?, happiness = ?
                  WHERE pet_instance_id = ? AND owner_user_id = ?"
            );
            $updatePet->execute([$nextHunger, $nextHappiness, $petId, $uid]);

            $nextQuantity = petting_consume_inventory_item($pdo, $uid, $itemId, (int)$item['quantity']);

            $pet['hunger'] = $nextHunger;
            $pet['happiness'] = $nextHappiness;

            petting_json_response([
                'ok' => true,
                'pet' => petting_build_pet_state($pet),
                'item' => ['id' => $itemId, 'quantity' => $nextQuantity],
                'effects' => ['happinessGain' => $nextHappiness - $currentState['happiness']],
                'message' => $nextHunger >= 100 ? $currentState['name'] . ' is full now.' : null,
            ]);
        } catch (Throwable $e) {
            app_add_error_from_exception($e, 'Petting feed action failed:');
            petting_json_response(['ok' => false, 'message' => 'Feeding failed. Please try again.'], 500);
        }
    }

    if ($action === 'heal') {
        $petId = input_int($_POST['pet_id'] ?? 0, 1);
        $itemId = input_int($_POST['item_id'] ?? 0, 1);

        try {
            $pet = petting_fetch_pet_row($pdo, $uid, $petId);
            if (!$pet) {
                petting_json_response(['ok' => false, 'message' => 'That pet is not available.'], 404);
            }

            $item = petting_fetch_inventory_item($pdo, $uid, $itemId, 'Potion');
            if (!$item || (int)$item['quantity'] <= 0) {
                petting_json_response(['ok' => false, 'message' => 'That healing item is no longer available.'], 409);
            }

            $currentState = petting_build_pet_state($pet);
            if ($currentState['health'] >= $currentState['maxHealth']) {
                petting_json_response([
                    'ok' => false,
                    'message' => $currentState['name'] . ' is already at full health.',
                    'pet' => $currentState,
                ]);
            }

            $healAmount = max(1, (int)$item['replenish']);
            $nextHealth = min($currentState['maxHealth'], $currentState['health'] + $healAmount);

            $updatePet = $pdo->prepare(
                "UPDATE pet_instances
                    SET hp_current = ?
                  WHERE pet_instance_id = ? AND owner_user_id = ?"
            );
            $updatePet->execute([$nextHealth, $petId, $uid]);

            $nextQuantity = petting_consume_inventory_item($pdo, $uid, $itemId, (int)$item['quantity']);

            $pet['hp_current'] = $nextHealth;

            petting_json_response([
                'ok' => true,
                'pet' => petting_build_pet_state($pet),
                'item' => ['id' => $itemId, 'quantity' => $nextQuantity],
            ]);
        } catch (Throwable $e) {
            app_add_error_from_exception($e, 'Petting heal action failed:');
            petting_json_response(['ok' => false, 'message' => 'Healing failed. Please try again.'], 500);
        }
    }
}

$pets = get_user_pets($uid);
if (!$pets) {
    echo '<p>No pets yet. <a href="?pg=create_pet">Create one</a>.</p>';
    return;
}

$food = q(
    "SELECT ui.item_id, i.item_name, ui.quantity, i.replenish FROM user_inventory ui"
    . " JOIN items i ON i.item_id = ui.item_id"
    . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
    . " WHERE ui.user_id = ? AND ic.category_name = 'Food' AND ui.quantity > 0 ORDER BY i.item_name",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);

$healing = q(
    "SELECT ui.item_id, i.item_name, ui.quantity, i.replenish FROM user_inventory ui"
    . " JOIN items i ON i.item_id = ui.item_id"
    . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
    . " WHERE ui.user_id = ? AND ic.category_name = 'Potion' AND ui.quantity > 0 ORDER BY i.item_name",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);

$emoji_map = [
    'apple' => '🍎',
    'berry' => '🫐',
    'cake' => '🍰',
    'honey' => '🍯',
    'candy' => '🍬',
    'kelp' => '🥬',
    'potion' => '🧪',
    'elixir' => '🧴',
    'bandage' => '🩹',
];

$pick_emoji = static function (string $name, string $default) use ($emoji_map): string {
    $slug = strtolower($name);
    foreach ($emoji_map as $needle => $emoji) {
        if (strpos($slug, $needle) !== false) {
            return $emoji;
        }
    }
    return $default;
};

$pets_payload = array_map(static function (array $pet): array {
    return [
        'id' => (int)$pet['pet_instance_id'],
        'name' => $pet['nickname'] ?: $pet['species_name'],
        'species' => strtolower((string)$pet['species_name']),
        'image' => pet_image_url((string)$pet['species_name'], $pet['color_name'] ?? null),
        'level' => (int)($pet['level'] ?? 1),
        'hunger' => (int)($pet['hunger'] ?? 0),
        'health' => (int)($pet['hp_current'] ?? 0),
        'maxHealth' => max(1, (int)($pet['hp_max'] ?? 100)),
        'happiness' => (int)($pet['happiness'] ?? 0),
        'preferences' => new stdClass(),
    ];
}, $pets);

$food_payload = array_map(static function (array $item) use ($pick_emoji): array {
    $imageFile = shop_find_item_image((string)$item['item_name']);
    return [
        'id' => (int)$item['item_id'],
        'name' => $item['item_name'],
        'emoji' => $pick_emoji((string)$item['item_name'], '🍖'),
        'image' => 'images/items/' . rawurlencode($imageFile),
        'quantity' => (int)$item['quantity'],
        'replenish' => max(1, (int)($item['replenish'] ?? 1)),
    ];
}, $food);

$healing_payload = array_map(static function (array $item) use ($pick_emoji): array {
    $imageFile = shop_find_item_image((string)$item['item_name']);
    return [
        'id' => (int)$item['item_id'],
        'name' => $item['item_name'],
        'emoji' => $pick_emoji((string)$item['item_name'], '💊'),
        'image' => 'images/items/' . rawurlencode($imageFile),
        'quantity' => (int)$item['quantity'],
        'heal' => max(1, (int)($item['replenish'] ?? 1)),
    ];
}, $healing);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<script>
window.pettingBlaData = {
    activePetId: <?= (int)$pets_payload[0]['id'] ?>,
    pets: <?= json_encode($pets_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    food: <?= json_encode($food_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    healing: <?= json_encode($healing_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
};
</script>

<style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #e8f4fc;
            --bg-secondary: #fff5f5;
            --accent-pink: #ff6b9d;
            --accent-blue: #4fc3f7;
            --accent-green: #81c784;
            --accent-orange: #ffb74d;
            --accent-purple: #ba68c8;
            --text-dark: #2d3436;
            --text-light: #636e72;
            --card-bg: rgba(255, 255, 255, 0.95);
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 32px rgba(0, 0, 0, 0.12);
            --shadow-glow: 0 0 40px rgba(255, 107, 157, 0.3);
            --radius-sm: 12px;
            --radius-md: 20px;
            --radius-lg: 28px;
            --radius-full: 9999px;
        }

        body:has(.petting-page) {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            overflow-x: hidden;
        }

        
.petting-page {
    width: 100vw;
    max-width: none;
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);
    padding: 20px;
}

.page-header {
            text-align: center;
            margin-bottom: 20px;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .page-header p {
            font-size: 1rem;
            opacity: 0.9;
            max-width: 500px;
        }

        /* Main Petting Container */
        .petting-container {
            width: 100%;
            max-width: none;
            min-height: min(78vh, 900px);
            background: linear-gradient(180deg, 
                #87ceeb 0%, 
                #b4e4f7 20%, 
                #d4f1d4 50%, 
                #c8e6c9 70%,
                #a5d6a7 100%
            );
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-medium), var(--shadow-glow);
            position: relative;
            overflow: hidden;
            border: 4px solid rgba(255, 255, 255, 0.5);
        }

        /* Decorative Background Elements */
        .bg-decoration {
            position: absolute;
            pointer-events: none;
            z-index: 1;
        }

        .cloud {
            position: absolute;
            background: rgba(255, 255, 255, 0.8);
            border-radius: var(--radius-full);
            filter: blur(1px);
        }

        .cloud::before, .cloud::after {
            content: '';
            position: absolute;
            background: inherit;
            border-radius: inherit;
        }

        .cloud-1 {
            width: 100px;
            height: 40px;
            top: 10%;
            left: 10%;
            animation: floatCloud 20s ease-in-out infinite;
        }

        .cloud-1::before {
            width: 50px;
            height: 50px;
            top: -25px;
            left: 15px;
        }

        .cloud-1::after {
            width: 60px;
            height: 40px;
            top: -15px;
            right: 10px;
        }

        .cloud-2 {
            width: 80px;
            height: 30px;
            top: 15%;
            right: 15%;
            animation: floatCloud 25s ease-in-out infinite reverse;
        }

        .cloud-2::before {
            width: 40px;
            height: 40px;
            top: -20px;
            left: 10px;
        }

        .grass-patch {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 25%;
            background: linear-gradient(180deg, 
                transparent 0%,
                rgba(139, 195, 74, 0.3) 30%,
                rgba(104, 159, 56, 0.4) 100%
            );
        }

        .flowers {
            position: absolute;
            bottom: 5%;
            font-size: 1.5rem;
            opacity: 0.7;
            animation: swayFlower 3s ease-in-out infinite;
        }

        .flower-1 { left: 5%; animation-delay: 0s; }
        .flower-2 { left: 15%; animation-delay: 0.5s; }
        .flower-3 { left: 85%; animation-delay: 1s; }
        .flower-4 { right: 8%; animation-delay: 1.5s; }

        @keyframes floatCloud {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(30px); }
        }

        @keyframes swayFlower {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
        }

        /* Status Bars */
        .status-panel {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 20;
        }

        .status-bar {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            min-width: 200px;
            box-shadow: var(--shadow-soft);
            backdrop-filter: blur(10px);
        }

        .status-bar-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .status-bar-icon {
            font-size: 1.2rem;
        }

        .status-bar-label {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .status-bar-track {
            height: 14px;
            background: #e0e0e0;
            border-radius: var(--radius-full);
            overflow: hidden;
            position: relative;
        }

        .status-bar-fill {
            height: 100%;
            border-radius: var(--radius-full);
            transition: width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .status-bar-fill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .hunger-fill {
            background: linear-gradient(90deg, #ffb74d, #ff9800);
        }

        .health-fill {
            background: linear-gradient(90deg, #81c784, #4caf50);
        }

        .happiness-fill {
            background: linear-gradient(90deg, #f48fb1, #e91e63);
        }

        .status-bar-value {
            font-size: 0.75rem;
            color: var(--text-light);
            text-align: right;
            margin-top: 4px;
            font-weight: 600;
        }

        /* Notification Banner */
        .notification-banner {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple));
            color: white;
            padding: 12px 24px;
            border-radius: var(--radius-full);
            font-weight: 700;
            box-shadow: var(--shadow-medium);
            z-index: 30;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            white-space: nowrap;
        }

        .notification-banner.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* Pet Display Area */
        .pet-stage {
            position: absolute;
            inset: 0;
            z-index: 10;
            cursor: pointer;
        }

        .pet-sprite {
            position: absolute;
            width: 180px;
            height: 180px;
            left: 50%;
            bottom: 20%;
            transform: translateX(-50%);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 15;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
        }

        .pet-sprite img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .pet-sprite.hopping {
            animation: hop 0.4s ease-out;
        }

        .pet-sprite.eating {
            animation: eat 0.5s ease-in-out;
        }

        .pet-sprite.happy {
            animation: bounce 0.6s ease-in-out;
        }

        .pet-shadow {
            position: absolute;
            width: 120px;
            height: 30px;
            left: 50%;
            bottom: calc(20% - 10px);
            transform: translateX(-50%);
            background: radial-gradient(ellipse, rgba(0, 0, 0, 0.25) 0%, transparent 70%);
            z-index: 14;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes hop {
            0%, 100% { transform: translateX(-50%) translateY(0) scale(1); }
            50% { transform: translateX(-50%) translateY(-40px) scale(1.05); }
        }

        @keyframes eat {
            0%, 100% { transform: translateX(-50%) scale(1); }
            25% { transform: translateX(-50%) scale(1.1, 0.9); }
            50% { transform: translateX(-50%) scale(0.95, 1.1); }
            75% { transform: translateX(-50%) scale(1.05, 0.95); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0) rotate(0deg); }
            25% { transform: translateX(-50%) translateY(-20px) rotate(-5deg); }
            50% { transform: translateX(-50%) translateY(-30px) rotate(5deg); }
            75% { transform: translateX(-50%) translateY(-15px) rotate(-3deg); }
        }

        /* Pet Name Tag */
        .pet-name-tag {
            position: absolute;
            left: 50%;
            bottom: calc(20% + 200px);
            transform: translateX(-50%);
            background: var(--card-bg);
            padding: 8px 20px;
            border-radius: var(--radius-full);
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--text-dark);
            box-shadow: var(--shadow-soft);
            z-index: 16;
            white-space: nowrap;
        }

        .pet-name-tag .level {
            font-size: 0.8rem;
            color: var(--accent-purple);
            margin-left: 8px;
        }

        .pet-visual {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .pet-dirt-layer {
            position: absolute;
            inset: 12% 14% 10%;
            pointer-events: none;
        }

        .dirt-spot {
            position: absolute;
            border-radius: 50%;
            background:
                radial-gradient(circle at 40% 40%, rgba(118, 82, 44, 0.94), rgba(79, 53, 27, 0.8) 55%, rgba(38, 24, 12, 0.45) 100%);
            mix-blend-mode: multiply;
            filter: blur(0.4px);
            transform: translate(-50%, -50%) scale(var(--spot-scale, 1));
            opacity: var(--spot-opacity, 0.85);
            transition: opacity 0.16s ease, transform 0.16s ease;
        }

        .dirt-spot::after {
            content: '';
            position: absolute;
            inset: 18%;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            opacity: 0.6;
        }

        .petting-mode-hud {
            position: absolute;
            left: 50%;
            bottom: 18px;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 14px 18px;
            border-radius: var(--radius-md);
            background: rgba(31, 37, 64, 0.85);
            color: white;
            box-shadow: var(--shadow-medium);
            backdrop-filter: blur(12px);
            z-index: 40;
        }

        .petting-mode-copy {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 260px;
        }

        .petting-mode-copy strong {
            font-size: 0.95rem;
            letter-spacing: 0.02em;
        }

        .petting-mode-copy span,
        .zoom-exit-hint {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.82);
        }

        .petting-mode-tools {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tool-chip {
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: var(--radius-full);
            background: rgba(255, 255, 255, 0.08);
            color: white;
            padding: 10px 16px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease, opacity 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .tool-chip:hover:not(:disabled) {
            transform: translateY(-1px);
            border-color: rgba(255, 255, 255, 0.28);
        }

        .tool-chip.active {
            background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple));
            border-color: transparent;
        }

        .tool-chip:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .tool-counter {
            min-width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
        }

        .petting-container.is-petting-mode {
            box-shadow: var(--shadow-medium), 0 0 60px rgba(255, 107, 157, 0.35);
        }

        .petting-container.is-petting-mode .pet-stage {
            cursor: default;
        }

        .petting-container.tool-clean .pet-stage {
            cursor: crosshair;
        }

        .petting-container.is-petting-mode .pet-sprite {
            width: 320px;
            height: 320px;
            filter: drop-shadow(0 18px 28px rgba(0, 0, 0, 0.26));
        }

        .petting-container.is-petting-mode .pet-shadow {
            width: 190px;
            height: 38px;
            background: radial-gradient(ellipse, rgba(0, 0, 0, 0.3) 0%, transparent 72%);
        }

        .petting-container.is-petting-mode .pet-name-tag {
            bottom: calc(12% + 310px);
            transform: translateX(-50%) scale(1.03);
        }

        .spray-drop {
            position: absolute;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.95) 0%, rgba(123, 214, 255, 0.9) 45%, rgba(123, 214, 255, 0) 100%);
            animation: sprayDrop 0.45s ease-out forwards;
        }

        @keyframes sprayDrop {
            0% {
                opacity: 0.95;
                transform: translate(-50%, -50%) scale(0.4);
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -50%) translateY(20px) scale(1.4);
            }
        }

        /* Effects Layer */
        .effects-layer {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 25;
        }

        .heart {
            position: absolute;
            font-size: 2rem;
            animation: floatHeart 1.5s ease-out forwards;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        @keyframes floatHeart {
            0% { 
                opacity: 1; 
                transform: translateY(0) scale(0.5) rotate(-10deg); 
            }
            50% { 
                opacity: 1; 
                transform: translateY(-60px) scale(1.2) rotate(10deg); 
            }
            100% { 
                opacity: 0; 
                transform: translateY(-120px) scale(0.8) rotate(-5deg); 
            }
        }

        .sparkle {
            position: absolute;
            width: 20px;
            height: 20px;
            animation: sparkle 0.8s ease-out forwards;
        }

        .sparkle::before, .sparkle::after {
            content: '✨';
            font-size: 1.5rem;
        }

        @keyframes sparkle {
            0% { opacity: 0; transform: scale(0) rotate(0deg); }
            50% { opacity: 1; transform: scale(1.2) rotate(180deg); }
            100% { opacity: 0; transform: scale(0.5) rotate(360deg); }
        }

        .crumb {
            position: absolute;
            width: 8px;
            height: 8px;
            background: linear-gradient(135deg, #d4a574, #b8956e);
            border-radius: 50%;
            animation: crumbFall 1s ease-out forwards;
        }

        @keyframes crumbFall {
            0% { opacity: 1; transform: translateY(0) rotate(0deg); }
            100% { opacity: 0; transform: translateY(100px) rotate(360deg); }
        }

        .dust-puff {
            position: absolute;
            width: 60px;
            height: 40px;
            background: radial-gradient(ellipse, rgba(255, 255, 255, 0.8) 0%, transparent 70%);
            animation: dustPuff 0.6s ease-out forwards;
        }

        @keyframes dustPuff {
            0% { opacity: 0; transform: scale(0.5); }
            50% { opacity: 0.8; transform: scale(1.2); }
            100% { opacity: 0; transform: scale(1.5); }
        }

        /* Control Buttons */
        .control-buttons {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 20;
        }

        .control-btn {
            width: 56px;
            height: 56px;
            border: none;
            border-radius: var(--radius-md);
            background: var(--card-bg);
            box-shadow: var(--shadow-soft);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .control-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .control-btn:active {
            transform: translateY(0);
        }

        .control-btn.active {
            background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple));
            color: white;
        }

        .control-btn .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 22px;
            height: 22px;
            background: var(--accent-orange);
            color: white;
            font-size: 0.7rem;
            font-weight: 800;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Slide-out Panels */
        .slide-panel {
            position: absolute;
            background: linear-gradient(180deg, rgba(30, 30, 50, 0.95), rgba(50, 40, 70, 0.95));
            backdrop-filter: blur(20px);
            z-index: 50;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: white;
        }

        .slide-panel-top {
            top: 0;
            left: 0;
            right: 0;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            transform: translateY(-100%);
            padding: 20px;
        }

        .slide-panel-bottom {
            bottom: 0;
            left: 0;
            right: 0;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            transform: translateY(100%);
            padding: 20px;
        }

        .slide-panel-top.show {
            transform: translateY(0);
        }

        .slide-panel-bottom.show {
            transform: translateY(0);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .panel-title {
            font-size: 1.2rem;
            font-weight: 800;
        }

        .panel-close {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .panel-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Item Grid */
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            max-height: 200px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .item-grid::-webkit-scrollbar {
            width: 6px;
        }

        .item-grid::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .item-grid::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .item-card {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-md);
            padding: 12px;
            text-align: center;
            cursor: grab;
            transition: all 0.2s ease;
            position: relative;
        }

        .item-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .item-card:active {
            cursor: grabbing;
        }

        .item-card .item-icon {
            width: 52px;
            height: 52px;
            margin-bottom: 8px;
            display: block;
            margin-inline: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .item-card .item-name {
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-card .item-quantity {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        .item-card .item-preference {
            position: absolute;
            top: 6px;
            right: 6px;
            font-size: 0.7rem;
        }

        /* Pet Cards in Switch Panel */
        .pet-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 16px;
        }

        .pet-card {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-md);
            padding: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pet-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.02);
            border-color: var(--accent-pink);
        }

        .pet-card.active {
            border-color: var(--accent-green);
            background: rgba(129, 199, 132, 0.2);
        }

        .pet-card .pet-icon {
            width: 74px;
            height: 74px;
            margin-bottom: 8px;
            display: block;
            margin-inline: auto;
            object-fit: contain;
        }

        .pet-card .pet-name {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .pet-card .pet-level {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        /* Drag Proxy */
        .drag-proxy {
            position: fixed;
            pointer-events: none;
            z-index: 1000;
            width: 72px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
            transform: translate(-50%, -50%);
            transition: transform 0.1s ease;
        }

        .drag-proxy img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .drag-proxy.dragging {
            transform: translate(-50%, -50%) scale(1.2);
        }

        /* Eating Animation */
        .eating-item {
            position: absolute;
            width: 52px;
            height: 52px;
            z-index: 30;
            animation: eatItem 0.6s ease-out forwards;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        .eating-item img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        @keyframes eatItem {
            0% { 
                opacity: 1; 
                transform: translate(-50%, -50%) scale(1); 
            }
            50% { 
                opacity: 1; 
                transform: translate(-50%, -50%) scale(1.3); 
            }
            100% { 
                opacity: 0; 
                transform: translate(-50%, -50%) scale(0); 
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            opacity: 0.7;
        }

        .empty-state .empty-icon {
            font-size: 3rem;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .petting-container {
                aspect-ratio: 4 / 5;
                border-radius: var(--radius-md);
            }

            .control-buttons {
                flex-direction: row;
                top: auto;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
            }

            .status-panel {
                top: 10px;
                right: 10px;
            }

            .status-bar {
                min-width: 150px;
                padding: 10px 12px;
            }

            .pet-sprite {
                width: 140px;
                height: 140px;
            }

            .slide-panel-top {
                max-height: 50vh;
            }

            .petting-mode-hud {
                left: 12px;
                right: 12px;
                bottom: 84px;
                transform: none;
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }

            .petting-mode-copy {
                min-width: 0;
            }

            .petting-mode-tools {
                justify-content: space-between;
            }

            .tool-chip {
                justify-content: center;
                flex: 1 1 auto;
            }
        }

        /* Touch feedback */
        @media (hover: none) {
            .control-btn:active {
                transform: scale(0.95);
            }

            .item-card:active {
                transform: scale(0.98);
            }
        }

        /* Petting interaction indicator */
        .pet-indicator {
            position: absolute;
            width: 60px;
            height: 60px;
            border: 3px solid var(--accent-pink);
            border-radius: 50%;
            pointer-events: none;
            z-index: 100;
            opacity: 0;
            animation: ripple 0.6s ease-out forwards;
        }

        @keyframes ripple {
            0% { 
                transform: translate(-50%, -50%) scale(0.5); 
                opacity: 1; 
            }
            100% { 
                transform: translate(-50%, -50%) scale(2); 
                opacity: 0; 
            }
        }
    
</style>

<div class="petting-page">
    <header class="page-header">
        <h1>💕 Petting Mode</h1>
        <p>Click anywhere to call your pet, drag food to feed them, and watch them play!</p>
    </header>

    <div class="petting-container" id="pettingContainer">
        <!-- Background Decorations -->
        <div class="bg-decoration cloud cloud-1"></div>
        <div class="bg-decoration cloud cloud-2"></div>
        <div class="grass-patch"></div>
        <span class="flowers flower-1">🌸</span>
        <span class="flowers flower-2">🌼</span>
        <span class="flowers flower-3">🌺</span>
        <span class="flowers flower-4">🌷</span>

        <!-- Notification Banner -->
        <div class="notification-banner" id="notificationBanner">
            ✨ She's full!
        </div>

        <!-- Status Panel -->
        <div class="status-panel">
            <div class="status-bar" id="hungerBar">
                <div class="status-bar-header">
                    <span class="status-bar-icon">🍖</span>
                    <span class="status-bar-label">Hunger</span>
                </div>
                <div class="status-bar-track">
                    <div class="status-bar-fill hunger-fill" id="hungerFill" style="width: 65%"></div>
                </div>
                <div class="status-bar-value" id="hungerValue">65 / 100</div>
            </div>

            <div class="status-bar" id="healthBar">
                <div class="status-bar-header">
                    <span class="status-bar-icon">💚</span>
                    <span class="status-bar-label">Health</span>
                </div>
                <div class="status-bar-track">
                    <div class="status-bar-fill health-fill" id="healthFill" style="width: 80%"></div>
                </div>
                <div class="status-bar-value" id="healthValue">80 / 100</div>
            </div>

            <div class="status-bar" id="happinessBar">
                <div class="status-bar-header">
                    <span class="status-bar-icon">💖</span>
                    <span class="status-bar-label">Happiness</span>
                </div>
                <div class="status-bar-track">
                    <div class="status-bar-fill happiness-fill" id="happinessFill" style="width: 85%"></div>
                </div>
                <div class="status-bar-value" id="happinessValue">85 / 100</div>
            </div>
        </div>

        <!-- Control Buttons -->
        <div class="control-buttons">
            <button class="control-btn" id="foodBtn" title="Food Inventory">
                🍱
                <span class="badge" id="foodBadge">8</span>
            </button>
            <button class="control-btn" id="healBtn" title="Healing Items">
                🧪
                <span class="badge" id="healBadge">3</span>
            </button>
            <button class="control-btn" id="petBtn" title="Switch Pet">
                🐾
            </button>
        </div>

        <!-- Food Panel -->
        <div class="slide-panel slide-panel-top" id="foodPanel">
            <div class="panel-header">
                <span class="panel-title">🍱 Food Inventory</span>
                <button class="panel-close" id="foodClose">✕</button>
            </div>
            <div class="item-grid" id="foodGrid">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- Healing Panel -->
        <div class="slide-panel slide-panel-top" id="healPanel">
            <div class="panel-header">
                <span class="panel-title">🧪 Healing Items</span>
                <button class="panel-close" id="healClose">✕</button>
            </div>
            <div class="item-grid" id="healGrid">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- Pet Switch Panel -->
        <div class="slide-panel slide-panel-bottom" id="petPanel">
            <div class="panel-header">
                <span class="panel-title">🐾 Your Pets</span>
                <button class="panel-close" id="petClose">✕</button>
            </div>
            <div class="pet-grid" id="petGrid">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- Pet Stage -->
        <div class="pet-stage" id="petStage">
            <div class="pet-name-tag" id="petNameTag">
                <span id="petName">Sparkle</span>
                <span class="level" id="petLevel">Lv. 12</span>
            </div>
            <div class="pet-shadow" id="petShadow"></div>
            <div class="pet-sprite" id="petSprite">
                <span style="font-size: 8rem; line-height: 1;">🦋</span>
            </div>
        </div>

        <div class="petting-mode-hud" id="pettingHud" hidden>
            <div class="petting-mode-copy">
                <strong>Petting mode</strong>
                <span id="pettingHudText">Move across your pet to build happiness.</span>
            </div>
            <div class="petting-mode-tools">
                <button type="button" class="tool-chip active" id="petToolBtn">Pet</button>
                <button type="button" class="tool-chip" id="washToolBtn">
                    Shower
                    <span class="tool-counter" id="grimeBadge">0</span>
                </button>
            </div>
            <div class="zoom-exit-hint">Click the meadow to zoom back out.</div>
        </div>

        <!-- Effects Layer -->
        <div class="effects-layer" id="effectsLayer"></div>
    </div>

    
</div>
<script>
        // ==========================================
        // Game Data
        // ==========================================
        const gameData = window.pettingBlaData;
        gameData.activePetId = gameData.activePetId || (gameData.pets[0] ? gameData.pets[0].id : null);

        // ==========================================
        // DOM Elements
        // ==========================================
        const container = document.getElementById('pettingContainer');
        const petStage = document.getElementById('petStage');
        const petSprite = document.getElementById('petSprite');
        const petShadow = document.getElementById('petShadow');
        const petNameEl = document.getElementById('petName');
        const petLevelEl = document.getElementById('petLevel');
        const effectsLayer = document.getElementById('effectsLayer');
        const notificationBanner = document.getElementById('notificationBanner');

        // Status bars
        const hungerFill = document.getElementById('hungerFill');
        const hungerValue = document.getElementById('hungerValue');
        const healthFill = document.getElementById('healthFill');
        const healthValue = document.getElementById('healthValue');
        const happinessFill = document.getElementById('happinessFill');
        const happinessValue = document.getElementById('happinessValue');

        // Buttons and panels
        const foodBtn = document.getElementById('foodBtn');
        const healBtn = document.getElementById('healBtn');
        const petBtn = document.getElementById('petBtn');
        const foodPanel = document.getElementById('foodPanel');
        const healPanel = document.getElementById('healPanel');
        const petPanel = document.getElementById('petPanel');
        const foodGrid = document.getElementById('foodGrid');
        const healGrid = document.getElementById('healGrid');
        const petGrid = document.getElementById('petGrid');
        const foodBadge = document.getElementById('foodBadge');
        const healBadge = document.getElementById('healBadge');

        // ==========================================
        // State
        // ==========================================
        let activePet = gameData.pets.find(p => p.id === gameData.activePetId);
        let draggingItem = null;
        let dragProxy = null;
        let idleTimer = null;

        // ==========================================
        // Utility Functions
        // ==========================================
        function clamp(value, min, max) {
            return Math.min(Math.max(value, min), max);
        }

        function getActivePet() {
            return gameData.pets.find(p => p.id === gameData.activePetId);
        }

        function showNotification(message) {
            notificationBanner.textContent = message;
            notificationBanner.classList.add('show');
            setTimeout(() => notificationBanner.classList.remove('show'), 3000);
        }

        // ==========================================
        // UI Updates
        // ==========================================
        function updateStatusBars() {
            const pet = getActivePet();
            if (!pet) return;

            // Hunger
            hungerFill.style.width = `${pet.hunger}%`;
            hungerValue.textContent = `${pet.hunger} / 100`;

            // Health
            const healthPercent = Math.round((pet.health / pet.maxHealth) * 100);
            healthFill.style.width = `${healthPercent}%`;
            healthValue.textContent = `${pet.health} / ${pet.maxHealth}`;

            // Happiness
            happinessFill.style.width = `${pet.happiness}%`;
            happinessValue.textContent = `${pet.happiness} / 100`;
        }

        function updateBadges() {
            const totalFood = gameData.food.reduce((sum, item) => sum + item.quantity, 0);
            const totalHealing = gameData.healing.reduce((sum, item) => sum + item.quantity, 0);
            foodBadge.textContent = totalFood;
            healBadge.textContent = totalHealing;
        }

        function setPetPosition(leftPx, bottomPx) {
            petSprite.style.left = `${leftPx}px`;
            petSprite.style.bottom = `${bottomPx}px`;
            petShadow.style.left = `${leftPx}px`;
            petShadow.style.bottom = `${bottomPx - 10}px`;
        }

        function updatePetDisplay() {
            const pet = getActivePet();
            if (!pet) return;

            petSprite.innerHTML = `<img src="${pet.image}" alt="${pet.name}">`;
            petNameEl.textContent = pet.name;
            petLevelEl.textContent = `Lv. ${pet.level}`;
            updateStatusBars();
        }

        // ==========================================
        // Panels
        // ==========================================
        function closeAllPanels() {
            foodPanel.classList.remove('show');
            healPanel.classList.remove('show');
            petPanel.classList.remove('show');
            foodBtn.classList.remove('active');
            healBtn.classList.remove('active');
            petBtn.classList.remove('active');
        }

        function togglePanel(panel, btn) {
            const isShowing = panel.classList.contains('show');
            closeAllPanels();
            if (!isShowing) {
                panel.classList.add('show');
                btn.classList.add('active');
            }
        }

        foodBtn.addEventListener('click', () => togglePanel(foodPanel, foodBtn));
        healBtn.addEventListener('click', () => togglePanel(healPanel, healBtn));
        petBtn.addEventListener('click', () => togglePanel(petPanel, petBtn));

        document.getElementById('foodClose').addEventListener('click', closeAllPanels);
        document.getElementById('healClose').addEventListener('click', closeAllPanels);
        document.getElementById('petClose').addEventListener('click', closeAllPanels);

        // ==========================================
        // Render Functions
        // ==========================================
        function renderFoodGrid() {
            foodGrid.innerHTML = '';
            
            const pet = getActivePet();
            const availableFood = gameData.food.filter(item => item.quantity > 0);
            
            if (availableFood.length === 0) {
                foodGrid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <div class="empty-icon">🍽️</div>
                        <p>No food in inventory!</p>
                    </div>
                `;
                return;
            }

            availableFood.forEach(item => {
                const pref = pet?.preferences?.[item.id] || 0;
                const hearts = pref > 0 ? '❤️'.repeat(pref) : '';
                
                const card = document.createElement('div');
                card.className = 'item-card';
                card.dataset.itemId = item.id;
                card.dataset.type = 'food';
                card.innerHTML = `
                    <img class="item-icon" src="${item.image}" alt="${item.name}">
                    <div class="item-name">${item.name}</div>
                    <div class="item-quantity">x${item.quantity} • +${item.replenish}🍖</div>
                    ${hearts ? `<div class="item-preference">${hearts}</div>` : ''}
                `;
                
                card.addEventListener('pointerdown', (e) => startDrag(e, item, 'food'));
                foodGrid.appendChild(card);
            });
        }

        function renderHealGrid() {
            healGrid.innerHTML = '';
            
            const availableHealing = gameData.healing.filter(item => item.quantity > 0);
            
            if (availableHealing.length === 0) {
                healGrid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <div class="empty-icon">💊</div>
                        <p>No healing items!</p>
                    </div>
                `;
                return;
            }

            availableHealing.forEach(item => {
                const card = document.createElement('div');
                card.className = 'item-card';
                card.dataset.itemId = item.id;
                card.dataset.type = 'healing';
                card.innerHTML = `
                    <img class="item-icon" src="${item.image}" alt="${item.name}">
                    <div class="item-name">${item.name}</div>
                    <div class="item-quantity">x${item.quantity} • +${item.heal}💚</div>
                `;
                
                card.addEventListener('pointerdown', (e) => startDrag(e, item, 'healing'));
                healGrid.appendChild(card);
            });
        }

        function renderPetGrid() {
            petGrid.innerHTML = '';

            gameData.pets.forEach(pet => {
                const isActive = pet.id === gameData.activePetId;
                const card = document.createElement('div');
                card.className = `pet-card ${isActive ? 'active' : ''}`;
                card.dataset.petId = pet.id;
                card.innerHTML = `
                    <img class="pet-icon" src="${pet.image}" alt="${pet.name}">
                    <div class="pet-name">${pet.name}</div>
                    <div class="pet-level">Level ${pet.level}</div>
                `;
                
                card.addEventListener('click', () => switchPet(pet.id));
                petGrid.appendChild(card);
            });
        }

        // ==========================================
        // Drag and Drop
        // ==========================================
        function startDrag(e, item, type) {
            e.preventDefault();
            draggingItem = { item, type };
            
            dragProxy = document.createElement('div');
            dragProxy.className = 'drag-proxy';
            dragProxy.innerHTML = `<img src="${item.image}" alt="${item.name}">`;
            document.body.appendChild(dragProxy);
            
            moveDrag(e.clientX, e.clientY);
            closeAllPanels();
            
            window.addEventListener('pointermove', handleDragMove);
            window.addEventListener('pointerup', handleDragEnd, { once: true });
        }

        function moveDrag(x, y) {
            if (dragProxy) {
                dragProxy.style.left = `${x}px`;
                dragProxy.style.top = `${y}px`;
                dragProxy.classList.add('dragging');
            }
        }

        function handleDragMove(e) {
            if (!draggingItem) return;
            moveDrag(e.clientX, e.clientY);
        }

        function handleDragEnd(e) {
            window.removeEventListener('pointermove', handleDragMove);
            
            const petRect = petSprite.getBoundingClientRect();
            const droppedOnPet = (
                e.clientX >= petRect.left &&
                e.clientX <= petRect.right &&
                e.clientY >= petRect.top &&
                e.clientY <= petRect.bottom
            );

            if (droppedOnPet && draggingItem) {
                if (draggingItem.type === 'food') {
                    feedPet(draggingItem.item, e.clientX, e.clientY);
                } else {
                    healPet(draggingItem.item, e.clientX, e.clientY);
                }
            } else if (draggingItem?.type === 'food') {
                // Drop crumbs when missing pet
                createCrumbs(e.clientX, e.clientY);
            }

            if (dragProxy) {
                dragProxy.remove();
                dragProxy = null;
            }
            draggingItem = null;
        }

        // ==========================================
        // Pet Actions
        // ==========================================
        function feedPet(item, x, y) {
            const pet = getActivePet();
            if (!pet) return;

            if (pet.hunger >= 100) {
                showNotification('✨ She\'s full!');
                return;
            }

            // Optimistic update
            const oldHunger = pet.hunger;
            const oldQuantity = item.quantity;

            pet.hunger = clamp(pet.hunger + item.replenish, 0, 100);
            item.quantity = Math.max(0, item.quantity - 1);

            // Calculate hearts based on preference
            const pref = pet.preferences?.[item.id] || 2;
            const hearts = clamp(pref, 1, 3);

            // Add happiness
            pet.happiness = clamp(pet.happiness + hearts * 3, 0, 100);

            // Animations
            createEatingAnimation(item, x, y);
            petSprite.classList.add('eating');
            setTimeout(() => petSprite.classList.remove('eating'), 500);

            // Effects
            setTimeout(() => {
                createHearts(hearts);
                createCrumbs(x, y, 4);
                createSparkles();
            }, 200);

            updateStatusBars();
            updateBadges();
            renderFoodGrid();

            if (pet.hunger >= 100) {
                setTimeout(() => showNotification('✨ She\'s full!'), 500);
            }
        }

        function healPet(item, x, y) {
            const pet = getActivePet();
            if (!pet) return;

            if (pet.health >= pet.maxHealth) {
                showNotification('💚 Already at full health!');
                return;
            }

            // Optimistic update
            pet.health = clamp(pet.health + item.heal, 0, pet.maxHealth);
            item.quantity = Math.max(0, item.quantity - 1);

            // Animations
            createEatingAnimation(item, x, y);
            petSprite.classList.add('happy');
            setTimeout(() => petSprite.classList.remove('happy'), 600);

            // Effects
            setTimeout(() => {
                createHearts(2, '#81c784');
            }, 200);

            updateStatusBars();
            updateBadges();
            renderHealGrid();
        }

        function switchPet(petId) {
            if (petId === gameData.activePetId) {
                closeAllPanels();
                return;
            }

            const stageRect = petStage.getBoundingClientRect();
            const currentBottom = parseFloat(getComputedStyle(petSprite).bottom) || stageRect.height * 0.2;

            // Run off screen
            setPetPosition(-150, currentBottom);
            petSprite.style.opacity = '0.5';

            setTimeout(() => {
                gameData.activePetId = petId;
                activePet = getActivePet();
                
                updatePetDisplay();
                
                // Enter from other side
                setPetPosition(stageRect.width + 150, currentBottom);
                petSprite.style.opacity = '1';

                requestAnimationFrame(() => {
                    setPetPosition(stageRect.width / 2, currentBottom);
                    createDustPuff(stageRect.width / 2, stageRect.height - currentBottom);
                });
            }, 300);

            closeAllPanels();
            renderPetGrid();
        }

        // ==========================================
        // Effects
        // ==========================================
        function createHearts(count, color = '#ff6b9d') {
            const petRect = petSprite.getBoundingClientRect();
            const stageRect = petStage.getBoundingClientRect();

            for (let i = 0; i < count; i++) {
                setTimeout(() => {
                    const heart = document.createElement('div');
                    heart.className = 'heart';
                    heart.textContent = '❤';
                    heart.style.color = color;
                    heart.style.left = `${petRect.left - stageRect.left + petRect.width / 2 + (i - (count - 1) / 2) * 30}px`;
                    heart.style.top = `${petRect.top - stageRect.top}px`;
                    effectsLayer.appendChild(heart);
                    setTimeout(() => heart.remove(), 1500);
                }, i * 100);
            }
        }

        function createSparkles(count = 3) {
            const petRect = petSprite.getBoundingClientRect();
            const stageRect = petStage.getBoundingClientRect();

            for (let i = 0; i < count; i++) {
                const sparkle = document.createElement('div');
                sparkle.className = 'sparkle';
                sparkle.style.left = `${petRect.left - stageRect.left + Math.random() * petRect.width}px`;
                sparkle.style.top = `${petRect.top - stageRect.top + Math.random() * petRect.height}px`;
                effectsLayer.appendChild(sparkle);
                setTimeout(() => sparkle.remove(), 800);
            }
        }

        function createCrumbs(x, y, count = 6) {
            const stageRect = petStage.getBoundingClientRect();

            for (let i = 0; i < count; i++) {
                const crumb = document.createElement('div');
                crumb.className = 'crumb';
                crumb.style.left = `${x - stageRect.left + (Math.random() * 30 - 15)}px`;
                crumb.style.top = `${y - stageRect.top}px`;
                effectsLayer.appendChild(crumb);
                setTimeout(() => crumb.remove(), 1000);
            }
        }

        function createDustPuff(x, y) {
            const stageRect = petStage.getBoundingClientRect();
            const dust = document.createElement('div');
            dust.className = 'dust-puff';
            dust.style.left = `${x - 30}px`;
            dust.style.top = `${y - 20}px`;
            effectsLayer.appendChild(dust);
            setTimeout(() => dust.remove(), 600);
        }

        function createEatingAnimation(item, x, y) {
            const stageRect = petStage.getBoundingClientRect();
            const eating = document.createElement('div');
            eating.className = 'eating-item';
            eating.innerHTML = `<img src="${item.image}" alt="${item.name}">`;
            eating.style.left = `${x}px`;
            eating.style.top = `${y}px`;
            document.body.appendChild(eating);
            setTimeout(() => eating.remove(), 600);
        }

        function createPetIndicator(x, y) {
            const stageRect = petStage.getBoundingClientRect();
            const indicator = document.createElement('div');
            indicator.className = 'pet-indicator';
            indicator.style.left = `${x - stageRect.left}px`;
            indicator.style.top = `${y - stageRect.top}px`;
            effectsLayer.appendChild(indicator);
            setTimeout(() => indicator.remove(), 600);
        }

        // ==========================================
        // Pet Movement & Idle Behavior
        // ==========================================
        function hopTo(leftPx, bottomPx) {
            const stageRect = petStage.getBoundingClientRect();
            const clampedLeft = clamp(leftPx, 100, stageRect.width - 100);
            const clampedBottom = clamp(bottomPx, stageRect.height * 0.1, stageRect.height * 0.35);

            setPetPosition(clampedLeft, clampedBottom);
            petSprite.classList.add('hopping');
            createDustPuff(clampedLeft, stageRect.height - clampedBottom);

            setTimeout(() => petSprite.classList.remove('hopping'), 400);
        }

        function idleBehavior() {
            const pet = getActivePet();
            if (!pet) return;

            const stageRect = petStage.getBoundingClientRect();
            const rand = Math.random();

            if (rand < 0.4) {
                // Hop to new position
                const newLeft = Math.random() * (stageRect.width - 200) + 100;
                const newBottom = Math.random() * (stageRect.height * 0.15) + stageRect.height * 0.12;
                hopTo(newLeft, newBottom);
            } else if (rand < 0.7) {
                // Play animation
                petSprite.classList.add('happy');
                setTimeout(() => petSprite.classList.remove('happy'), 600);
            } else {
                // Subtle movement
                const currentLeft = parseFloat(getComputedStyle(petSprite).left) || stageRect.width / 2;
                const offset = (Math.random() - 0.5) * 20;
                petSprite.style.left = `${clamp(currentLeft + offset, 100, stageRect.width - 100)}px`;
            }
        }

        function resetIdleTimer() {
            clearTimeout(idleTimer);
            idleTimer = setTimeout(() => {
                idleBehavior();
                resetIdleTimer();
            }, 4000 + Math.random() * 3000);
        }

        // Click to call pet
        petStage.addEventListener('click', (e) => {
            if (draggingItem) return;

            const stageRect = petStage.getBoundingClientRect();
            const clickX = e.clientX - stageRect.left;
            const clickY = e.clientY - stageRect.top;

            createPetIndicator(e.clientX, e.clientY);
            hopTo(clickX, stageRect.height - clickY);
            resetIdleTimer();
        });

        // ==========================================
        // Initialize
        // ==========================================
        function init() {
            updatePetDisplay();
            updateBadges();
            renderFoodGrid();
            renderHealGrid();
            renderPetGrid();
            resetIdleTimer();

            // Center pet
            const stageRect = petStage.getBoundingClientRect();
            setPetPosition(stageRect.width / 2, stageRect.height * 0.2);
        }

        // Wait for layout
        requestAnimationFrame(() => {
            requestAnimationFrame(init);
        });

        // Handle resize
        window.addEventListener('resize', () => {
            const stageRect = petStage.getBoundingClientRect();
            setPetPosition(stageRect.width / 2, stageRect.height * 0.2);
        });
    
</script>
<script>
(() => {
    const originalContainer = document.getElementById('pettingContainer');
    if (!originalContainer || !window.pettingBlaData) {
        return;
    }

    originalContainer.outerHTML = originalContainer.outerHTML;

    const gameData = window.pettingBlaData;
    const requestedPetId = Number(new URLSearchParams(window.location.search).get('id') || 0);
    if (requestedPetId && gameData.pets.some((pet) => pet.id === requestedPetId)) {
        gameData.activePetId = requestedPetId;
    }
    gameData.activePetId = gameData.activePetId || (gameData.pets[0] ? gameData.pets[0].id : null);

    const container = document.getElementById('pettingContainer');
    const petStage = document.getElementById('petStage');
    const petSprite = document.getElementById('petSprite');
    const petShadow = document.getElementById('petShadow');
    const petNameEl = document.getElementById('petName');
    const petLevelEl = document.getElementById('petLevel');
    const effectsLayer = document.getElementById('effectsLayer');
    const notificationBanner = document.getElementById('notificationBanner');
    const hungerFill = document.getElementById('hungerFill');
    const hungerValue = document.getElementById('hungerValue');
    const healthFill = document.getElementById('healthFill');
    const healthValue = document.getElementById('healthValue');
    const happinessFill = document.getElementById('happinessFill');
    const happinessValue = document.getElementById('happinessValue');
    const foodBtn = document.getElementById('foodBtn');
    const healBtn = document.getElementById('healBtn');
    const petBtn = document.getElementById('petBtn');
    const foodPanel = document.getElementById('foodPanel');
    const healPanel = document.getElementById('healPanel');
    const petPanel = document.getElementById('petPanel');
    const foodGrid = document.getElementById('foodGrid');
    const healGrid = document.getElementById('healGrid');
    const petGrid = document.getElementById('petGrid');
    const foodBadge = document.getElementById('foodBadge');
    const healBadge = document.getElementById('healBadge');
    const pettingHud = document.getElementById('pettingHud');
    const pettingHudText = document.getElementById('pettingHudText');
    const petToolBtn = document.getElementById('petToolBtn');
    const washToolBtn = document.getElementById('washToolBtn');
    const grimeBadge = document.getElementById('grimeBadge');
    const pageHeaderTitle = document.querySelector('.page-header h1');
    const pageHeaderText = document.querySelector('.page-header p');

    if (pageHeaderTitle) {
        pageHeaderTitle.textContent = 'Petting Mode';
    }
    if (pageHeaderText) {
        pageHeaderText.textContent = 'Click your pet to zoom in, pet them by moving across their body, clean grime with the shower tool, and click the meadow to zoom back out.';
    }

    let draggingItem = null;
    let dragProxy = null;
    let idleTimer = null;
    let notificationTimer = null;
    let pettingMode = false;
    let activeTool = 'pet';
    let lastStrokePoint = null;
    let lastPetGainAt = 0;
    let lastSprayAt = 0;
    const pendingPetGains = new Map();
    const activeGainFlushes = new Set();
    const gainFlushTimers = new Map();
    const gainRetryCounts = new Map();
    const gainFailureNotified = new Set();
    const PET_GAIN_FLUSH_DELAY_MS = 700;
    const PET_GAIN_RETRY_DELAY_MS = 1600;
    const PET_GAIN_MAX_CHUNK = 24;
    const PET_GAIN_MAX_RETRIES = 3;

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function getActivePet() {
        return gameData.pets.find((pet) => pet.id === gameData.activePetId) || null;
    }

    function syncPetFromServer(serverPet) {
        if (!serverPet || !serverPet.id) {
            return null;
        }

        const localPet = gameData.pets.find((pet) => pet.id === serverPet.id);
        if (!localPet) {
            return null;
        }

        if (typeof serverPet.name === 'string' && serverPet.name) {
            localPet.name = serverPet.name;
        }
        if (typeof serverPet.hunger === 'number') {
            localPet.hunger = clamp(serverPet.hunger, 0, 100);
        }
        if (typeof serverPet.health === 'number') {
            localPet.health = Math.max(0, serverPet.health);
        }
        if (typeof serverPet.maxHealth === 'number' && serverPet.maxHealth > 0) {
            localPet.maxHealth = serverPet.maxHealth;
        }
        if (typeof serverPet.happiness === 'number') {
            localPet.happiness = clamp(serverPet.happiness, 0, 100);
        }

        return localPet;
    }

    function syncInventoryQuantity(collection, itemPayload) {
        if (!itemPayload || typeof itemPayload.id !== 'number') {
            return;
        }

        const localItem = collection.find((item) => item.id === itemPayload.id);
        if (localItem) {
            localItem.quantity = Math.max(0, Number(itemPayload.quantity || 0));
        }
    }

    async function postAction(action, payload = {}) {
        const formData = new FormData();
        formData.append('action', action);
        Object.entries(payload).forEach(([key, value]) => {
            formData.append(key, String(value));
        });

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData,
            keepalive: action === 'pet',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            throw new Error('The server returned an invalid response.');
        }
        if (data && typeof data === 'object') {
            data.httpStatus = response.status;
        }

        if (!response.ok && (!data || data.ok !== false)) {
            throw new Error('The request could not be completed.');
        }

        return data;
    }

    function showNotification(message) {
        if (!message) {
            return;
        }

        notificationBanner.textContent = message;
        notificationBanner.classList.add('show');
        clearTimeout(notificationTimer);
        notificationTimer = setTimeout(() => {
            notificationBanner.classList.remove('show');
        }, 2600);
    }

    function getStageTargetPosition() {
        const stageRect = petStage.getBoundingClientRect();
        return {
            left: stageRect.width / 2,
            bottom: stageRect.height * (pettingMode ? 0.1 : 0.2),
        };
    }

    function setPetPosition(leftPx, bottomPx) {
        petSprite.style.left = `${leftPx}px`;
        petSprite.style.bottom = `${bottomPx}px`;
        petShadow.style.left = `${leftPx}px`;
        petShadow.style.bottom = `${bottomPx - 10}px`;
    }

    function positionPetForCurrentMode() {
        const target = getStageTargetPosition();
        setPetPosition(target.left, target.bottom);
    }

    function generateGrimeSpots(pet) {
        const healthRatio = pet.maxHealth > 0 ? pet.health / pet.maxHealth : 1;
        const roughness = (1 - healthRatio) * 55 + (100 - pet.happiness) * 0.35 + (100 - pet.hunger) * 0.15;
        const count = clamp(Math.round(roughness / 28), 1, 4);
        const spots = [];

        for (let index = 0; index < count; index += 1) {
            spots.push({
                id: `${pet.id}-${index}`,
                x: 24 + Math.random() * 52,
                y: 22 + Math.random() * 52,
                size: 12 + Math.random() * 15,
                remaining: 100,
            });
        }

        return spots;
    }

    function ensurePetExtras(pet) {
        if (!pet) {
            return;
        }

        if (!Array.isArray(pet.grimeSpots)) {
            pet.grimeSpots = generateGrimeSpots(pet);
        }
        if (typeof pet.cleanBonusGranted !== 'boolean') {
            pet.cleanBonusGranted = false;
        }
    }

    function getRemainingGrimeCount(pet) {
        if (!pet || !Array.isArray(pet.grimeSpots)) {
            return 0;
        }

        return pet.grimeSpots.filter((spot) => spot.remaining > 0).length;
    }

    function updateStatusBars() {
        const pet = getActivePet();
        if (!pet) {
            return;
        }

        const healthPercent = pet.maxHealth > 0 ? Math.round((pet.health / pet.maxHealth) * 100) : 0;
        hungerFill.style.width = `${pet.hunger}%`;
        hungerValue.textContent = `${pet.hunger} / 100`;
        healthFill.style.width = `${healthPercent}%`;
        healthValue.textContent = `${pet.health} / ${pet.maxHealth}`;
        happinessFill.style.width = `${pet.happiness}%`;
        happinessValue.textContent = `${pet.happiness} / 100`;
    }

    function setActiveTool(tool) {
        activeTool = tool;
        container.classList.toggle('tool-clean', pettingMode && tool === 'clean');
        updateModeHud();
    }

    function updateGrimeBadge() {
        const pet = getActivePet();
        const remaining = getRemainingGrimeCount(pet);
        grimeBadge.textContent = String(remaining);
        washToolBtn.disabled = remaining === 0;
        if (remaining === 0 && activeTool === 'clean') {
            setActiveTool('pet');
        }
    }

    function updateBadges() {
        const totalFood = gameData.food.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
        const totalHealing = gameData.healing.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
        foodBadge.textContent = String(totalFood);
        healBadge.textContent = String(totalHealing);
        updateGrimeBadge();
    }

    function updateModeHud() {
        if (!pettingMode) {
            pettingHud.hidden = true;
            return;
        }

        const pet = getActivePet();
        const remaining = getRemainingGrimeCount(pet);
        pettingHud.hidden = false;
        petToolBtn.classList.toggle('active', activeTool === 'pet');
        washToolBtn.classList.toggle('active', activeTool === 'clean');

        if (activeTool === 'clean') {
            pettingHudText.textContent = remaining > 0
                ? 'Move the shower over grime to wash it away a little at a time.'
                : 'Everything is clean. Switch back to petting for more happiness.';
        } else {
            pettingHudText.textContent = remaining > 0
                ? 'Move over your pet to build happiness, or switch to Shower to clean grime.'
                : 'Move over your pet to build happiness.';
        }
    }

    function renderPetGrime() {
        const dirtLayer = petSprite.querySelector('.pet-dirt-layer');
        const pet = getActivePet();
        if (!dirtLayer || !pet || !pettingMode) {
            if (dirtLayer) {
                dirtLayer.innerHTML = '';
            }
            updateGrimeBadge();
            return;
        }

        ensurePetExtras(pet);
        const grimeMarkup = pet.grimeSpots
            .filter((spot) => spot.remaining > 0)
            .map((spot) => {
                const opacity = 0.12 + (spot.remaining / 100) * 0.72;
                const scale = 0.72 + (spot.remaining / 100) * 0.28;
                return `<span class="dirt-spot" style="left:${spot.x}%;top:${spot.y}%;width:${spot.size}%;height:${spot.size}%;--spot-opacity:${opacity};--spot-scale:${scale};"></span>`;
            })
            .join('');

        dirtLayer.innerHTML = grimeMarkup;
        updateGrimeBadge();
    }

    function updatePetDisplay() {
        const pet = getActivePet();
        if (!pet) {
            return;
        }

        ensurePetExtras(pet);
        petSprite.innerHTML = `
            <div class="pet-visual">
                <img src="${pet.image}" alt="${pet.name}">
                <div class="pet-dirt-layer"></div>
            </div>
        `;
        petNameEl.textContent = pet.name;
        petLevelEl.textContent = `Lv. ${pet.level}`;
        updateStatusBars();
        renderPetGrime();
        updateModeHud();
    }

    function closeAllPanels() {
        foodPanel.classList.remove('show');
        healPanel.classList.remove('show');
        petPanel.classList.remove('show');
        foodBtn.classList.remove('active');
        healBtn.classList.remove('active');
        petBtn.classList.remove('active');
    }

    function togglePanel(panel, button) {
        const isShowing = panel.classList.contains('show');
        closeAllPanels();
        if (!isShowing) {
            panel.classList.add('show');
            button.classList.add('active');
        }
    }

    function renderFoodGrid() {
        foodGrid.innerHTML = '';
        const availableFood = gameData.food.filter((item) => Number(item.quantity || 0) > 0);
        if (availableFood.length === 0) {
            foodGrid.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="empty-icon">Food</div>
                    <p>No food in inventory.</p>
                </div>
            `;
            return;
        }

        availableFood.forEach((item) => {
            const card = document.createElement('div');
            card.className = 'item-card';
            card.dataset.itemId = String(item.id);
            card.dataset.type = 'food';
            card.innerHTML = `
                <img class="item-icon" src="${item.image}" alt="${item.name}">
                <div class="item-name">${item.name}</div>
                <div class="item-quantity">x${item.quantity} | +${item.replenish} hunger</div>
            `;
            card.addEventListener('pointerdown', (event) => startDrag(event, item, 'food'));
            foodGrid.appendChild(card);
        });
    }

    function renderHealGrid() {
        healGrid.innerHTML = '';
        const availableHealing = gameData.healing.filter((item) => Number(item.quantity || 0) > 0);
        if (availableHealing.length === 0) {
            healGrid.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="empty-icon">Heal</div>
                    <p>No healing items.</p>
                </div>
            `;
            return;
        }

        availableHealing.forEach((item) => {
            const card = document.createElement('div');
            card.className = 'item-card';
            card.dataset.itemId = String(item.id);
            card.dataset.type = 'healing';
            card.innerHTML = `
                <img class="item-icon" src="${item.image}" alt="${item.name}">
                <div class="item-name">${item.name}</div>
                <div class="item-quantity">x${item.quantity} | +${item.heal} health</div>
            `;
            card.addEventListener('pointerdown', (event) => startDrag(event, item, 'healing'));
            healGrid.appendChild(card);
        });
    }

    function renderPetGrid() {
        petGrid.innerHTML = '';
        gameData.pets.forEach((pet) => {
            const card = document.createElement('div');
            card.className = `pet-card ${pet.id === gameData.activePetId ? 'active' : ''}`;
            card.dataset.petId = String(pet.id);
            card.innerHTML = `
                <img class="pet-icon" src="${pet.image}" alt="${pet.name}">
                <div class="pet-name">${pet.name}</div>
                <div class="pet-level">Level ${pet.level}</div>
            `;
            card.addEventListener('click', () => switchPet(pet.id));
            petGrid.appendChild(card);
        });
    }

    function startDrag(event, item, type) {
        event.preventDefault();
        draggingItem = { item, type };
        dragProxy = document.createElement('div');
        dragProxy.className = 'drag-proxy';
        dragProxy.innerHTML = `<img src="${item.image}" alt="${item.name}">`;
        document.body.appendChild(dragProxy);

        moveDrag(event.clientX, event.clientY);
        closeAllPanels();
        window.addEventListener('pointermove', handleDragMove);
        window.addEventListener('pointerup', handleDragEnd, { once: true });
    }

    function moveDrag(x, y) {
        if (!dragProxy) {
            return;
        }

        dragProxy.style.left = `${x}px`;
        dragProxy.style.top = `${y}px`;
        dragProxy.classList.add('dragging');
    }

    function handleDragMove(event) {
        if (!draggingItem) {
            return;
        }

        moveDrag(event.clientX, event.clientY);
    }

    function isPointOnPet(clientX, clientY) {
        const rect = petSprite.getBoundingClientRect();
        return clientX >= rect.left && clientX <= rect.right && clientY >= rect.top && clientY <= rect.bottom;
    }

    function handleDragEnd(event) {
        window.removeEventListener('pointermove', handleDragMove);
        const droppedOnPet = draggingItem && isPointOnPet(event.clientX, event.clientY);

        if (droppedOnPet && draggingItem) {
            if (draggingItem.type === 'food') {
                void feedPet(draggingItem.item, event.clientX, event.clientY);
            } else {
                void healPet(draggingItem.item, event.clientX, event.clientY);
            }
        } else if (draggingItem && draggingItem.type === 'food') {
            createCrumbs(event.clientX, event.clientY);
        }

        if (dragProxy) {
            dragProxy.remove();
            dragProxy = null;
        }
        draggingItem = null;
    }

    async function feedPet(item, clientX, clientY) {
        const pet = getActivePet();
        if (!pet || item.pending) {
            return;
        }
        if (pet.hunger >= 100) {
            showNotification(`${pet.name} is already full.`);
            return;
        }

        item.pending = true;
        try {
            const data = await postAction('feed', { pet_id: pet.id, item_id: item.id });
            if (!data.ok) {
                if (data.pet) {
                    syncPetFromServer(data.pet);
                    updateStatusBars();
                }
                showNotification(data.message || 'Feeding could not be completed.');
                return;
            }

            syncPetFromServer(data.pet);
            syncInventoryQuantity(gameData.food, data.item);
            updatePetDisplay();
            updateBadges();
            renderFoodGrid();

            createEatingAnimation(item, clientX, clientY);
            petSprite.classList.add('eating');
            setTimeout(() => petSprite.classList.remove('eating'), 500);
            setTimeout(() => {
                const heartCount = Math.max(1, Math.ceil(Number(data.effects?.happinessGain || 3) / 3));
                createHearts(heartCount);
                createCrumbs(clientX, clientY, 4);
                createSparkles(3);
            }, 200);

            if (data.message) {
                showNotification(data.message);
            }
        } catch (error) {
            showNotification('Feeding failed. Please try again.');
        } finally {
            item.pending = false;
            renderFoodGrid();
        }
    }

    async function healPet(item, clientX, clientY) {
        const pet = getActivePet();
        if (!pet || item.pending) {
            return;
        }
        if (pet.health >= pet.maxHealth) {
            showNotification(`${pet.name} is already at full health.`);
            return;
        }

        item.pending = true;
        try {
            const data = await postAction('heal', { pet_id: pet.id, item_id: item.id });
            if (!data.ok) {
                if (data.pet) {
                    syncPetFromServer(data.pet);
                    updateStatusBars();
                }
                showNotification(data.message || 'Healing could not be completed.');
                return;
            }

            syncPetFromServer(data.pet);
            syncInventoryQuantity(gameData.healing, data.item);
            updatePetDisplay();
            updateBadges();
            renderHealGrid();

            createEatingAnimation(item, clientX, clientY);
            petSprite.classList.add('happy');
            setTimeout(() => petSprite.classList.remove('happy'), 600);
            setTimeout(() => createHearts(2, '#81c784'), 180);
        } catch (error) {
            showNotification('Healing failed. Please try again.');
        } finally {
            item.pending = false;
            renderHealGrid();
        }
    }

    function enqueuePetGain(petId, gain) {
        if (!petId || gain <= 0) {
            return;
        }

        pendingPetGains.set(petId, (pendingPetGains.get(petId) || 0) + gain);
        if (activeGainFlushes.has(petId)) {
            return;
        }

        if ((pendingPetGains.get(petId) || 0) >= PET_GAIN_MAX_CHUNK) {
            void flushPetGains(petId);
            return;
        }

        schedulePetGainFlush(petId);
    }

    function clearPetGainTimer(petId) {
        const timer = gainFlushTimers.get(petId);
        if (!timer) {
            return;
        }

        clearTimeout(timer);
        gainFlushTimers.delete(petId);
    }

    function schedulePetGainFlush(petId, delay = PET_GAIN_FLUSH_DELAY_MS) {
        if (!petId || activeGainFlushes.has(petId)) {
            return;
        }

        clearPetGainTimer(petId);
        const timer = setTimeout(() => {
            gainFlushTimers.delete(petId);
            void flushPetGains(petId);
        }, delay);
        gainFlushTimers.set(petId, timer);
    }

    function flushAllPetGains() {
        pendingPetGains.forEach((pending, petId) => {
            if (pending > 0) {
                void flushPetGains(petId);
            }
        });
    }

    async function flushPetGains(petId) {
        if (activeGainFlushes.has(petId)) {
            return;
        }
        clearPetGainTimer(petId);
        if ((pendingPetGains.get(petId) || 0) <= 0) {
            return;
        }

        let shouldReschedule = true;
        let nextDelay = PET_GAIN_FLUSH_DELAY_MS;
        activeGainFlushes.add(petId);
        try {
            while ((pendingPetGains.get(petId) || 0) > 0) {
                const pending = pendingPetGains.get(petId) || 0;
                const chunk = Math.min(PET_GAIN_MAX_CHUNK, pending);
                pendingPetGains.set(petId, pending - chunk);

                try {
                    const data = await postAction('pet', { pet_id: petId, gain: chunk });
                    if (!data || data.ok !== true) {
                        const error = new Error(data?.message || 'Petting progress could not be saved just now.');
                        error.response = data || null;
                        throw error;
                    }

                    gainRetryCounts.delete(petId);
                    gainFailureNotified.delete(petId);
                    if (data.pet) {
                        syncPetFromServer(data.pet);
                        if (petId === gameData.activePetId) {
                            updateStatusBars();
                        }
                    }
                } catch (error) {
                    pendingPetGains.set(petId, (pendingPetGains.get(petId) || 0) + chunk);
                    const status = Number(error?.response?.httpStatus || 0);
                    const attempts = (gainRetryCounts.get(petId) || 0) + 1;
                    const retryable = status === 0 || status === 408 || status === 429 || status >= 500;
                    gainRetryCounts.set(petId, attempts);

                    if (retryable && attempts <= PET_GAIN_MAX_RETRIES) {
                        nextDelay = PET_GAIN_RETRY_DELAY_MS * attempts;
                    } else {
                        shouldReschedule = false;
                        if (!gainFailureNotified.has(petId)) {
                            showNotification(error?.message || 'Petting progress could not be saved just now.');
                            gainFailureNotified.add(petId);
                        }
                    }
                    break;
                }
            }
        } finally {
            activeGainFlushes.delete(petId);
            if (shouldReschedule && (pendingPetGains.get(petId) || 0) > 0) {
                schedulePetGainFlush(petId, nextDelay);
            }
        }
    }

    function handlePettingMove(clientX, clientY) {
        const pet = getActivePet();
        if (!pet || pet.happiness >= 100) {
            return;
        }

        const now = performance.now();
        if (!lastStrokePoint) {
            lastStrokePoint = { x: clientX, y: clientY };
            return;
        }

        const distance = Math.hypot(clientX - lastStrokePoint.x, clientY - lastStrokePoint.y);
        if (distance < 26 || now - lastPetGainAt < 160) {
            return;
        }

        lastStrokePoint = { x: clientX, y: clientY };
        lastPetGainAt = now;
        pet.happiness = clamp(pet.happiness + 1, 0, 100);
        updateStatusBars();
        createPetIndicator(clientX, clientY);
        if (Math.random() < 0.4) {
            createSparkles(1);
        }
        if (Math.random() < 0.2) {
            createHearts(1);
        }
        enqueuePetGain(pet.id, 1);
    }

    function createSprayBurst(clientX, clientY) {
        const stageRect = petStage.getBoundingClientRect();
        for (let index = 0; index < 4; index += 1) {
            const drop = document.createElement('div');
            drop.className = 'spray-drop';
            drop.style.left = `${clientX - stageRect.left + (Math.random() * 22 - 11)}px`;
            drop.style.top = `${clientY - stageRect.top + (Math.random() * 22 - 11)}px`;
            effectsLayer.appendChild(drop);
            setTimeout(() => drop.remove(), 450);
        }
    }

    function handleCleaningMove(clientX, clientY) {
        const pet = getActivePet();
        if (!pet) {
            return;
        }

        const now = performance.now();
        if (now - lastSprayAt < 70) {
            return;
        }
        lastSprayAt = now;

        ensurePetExtras(pet);
        const spriteRect = petSprite.getBoundingClientRect();
        const localX = ((clientX - spriteRect.left) / spriteRect.width) * 100;
        const localY = ((clientY - spriteRect.top) / spriteRect.height) * 100;
        let cleanedAny = false;

        pet.grimeSpots.forEach((spot) => {
            if (spot.remaining <= 0) {
                return;
            }

            const distance = Math.hypot(localX - spot.x, localY - spot.y);
            const reach = Math.max(spot.size * 0.68, 10);
            if (distance <= reach) {
                spot.remaining = Math.max(0, spot.remaining - 22);
                cleanedAny = true;
            }
        });

        createSprayBurst(clientX, clientY);
        if (!cleanedAny) {
            return;
        }

        renderPetGrime();
        const remaining = getRemainingGrimeCount(pet);
        if (remaining === 0 && !pet.cleanBonusGranted) {
            pet.cleanBonusGranted = true;
            pet.happiness = clamp(pet.happiness + 4, 0, 100);
            updateStatusBars();
            enqueuePetGain(pet.id, 4);
            createHearts(2, '#81c784');
            createSparkles(4);
            showNotification(`${pet.name} is all cleaned up.`);
            setActiveTool('pet');
        }
    }

    function createHearts(count, color = '#ff6b9d') {
        const petRect = petSprite.getBoundingClientRect();
        const stageRect = petStage.getBoundingClientRect();
        for (let index = 0; index < count; index += 1) {
            setTimeout(() => {
                const heart = document.createElement('div');
                heart.className = 'heart';
                heart.textContent = '❤';
                heart.style.color = color;
                heart.style.left = `${petRect.left - stageRect.left + petRect.width / 2 + (index - (count - 1) / 2) * 30}px`;
                heart.style.top = `${petRect.top - stageRect.top}px`;
                effectsLayer.appendChild(heart);
                setTimeout(() => heart.remove(), 1500);
            }, index * 100);
        }
    }

    function createSparkles(count = 3) {
        const petRect = petSprite.getBoundingClientRect();
        const stageRect = petStage.getBoundingClientRect();
        for (let index = 0; index < count; index += 1) {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            sparkle.style.left = `${petRect.left - stageRect.left + Math.random() * petRect.width}px`;
            sparkle.style.top = `${petRect.top - stageRect.top + Math.random() * petRect.height}px`;
            effectsLayer.appendChild(sparkle);
            setTimeout(() => sparkle.remove(), 800);
        }
    }

    function createCrumbs(clientX, clientY, count = 6) {
        const stageRect = petStage.getBoundingClientRect();
        for (let index = 0; index < count; index += 1) {
            const crumb = document.createElement('div');
            crumb.className = 'crumb';
            crumb.style.left = `${clientX - stageRect.left + (Math.random() * 30 - 15)}px`;
            crumb.style.top = `${clientY - stageRect.top}px`;
            effectsLayer.appendChild(crumb);
            setTimeout(() => crumb.remove(), 1000);
        }
    }

    function createDustPuff(x, y) {
        const dust = document.createElement('div');
        dust.className = 'dust-puff';
        dust.style.left = `${x - 30}px`;
        dust.style.top = `${y - 20}px`;
        effectsLayer.appendChild(dust);
        setTimeout(() => dust.remove(), 600);
    }

    function createEatingAnimation(item, clientX, clientY) {
        const eating = document.createElement('div');
        eating.className = 'eating-item';
        eating.innerHTML = `<img src="${item.image}" alt="${item.name}">`;
        eating.style.left = `${clientX}px`;
        eating.style.top = `${clientY}px`;
        document.body.appendChild(eating);
        setTimeout(() => eating.remove(), 600);
    }

    function createPetIndicator(clientX, clientY) {
        const stageRect = petStage.getBoundingClientRect();
        const indicator = document.createElement('div');
        indicator.className = 'pet-indicator';
        indicator.style.left = `${clientX - stageRect.left}px`;
        indicator.style.top = `${clientY - stageRect.top}px`;
        effectsLayer.appendChild(indicator);
        setTimeout(() => indicator.remove(), 600);
    }

    function hopTo(leftPx, bottomPx) {
        const stageRect = petStage.getBoundingClientRect();
        const clampedLeft = clamp(leftPx, 100, stageRect.width - 100);
        const clampedBottom = clamp(bottomPx, stageRect.height * 0.1, stageRect.height * 0.35);
        setPetPosition(clampedLeft, clampedBottom);
        petSprite.classList.add('hopping');
        createDustPuff(clampedLeft, stageRect.height - clampedBottom);
        setTimeout(() => petSprite.classList.remove('hopping'), 400);
    }

    function idleBehavior() {
        if (pettingMode || !getActivePet()) {
            return;
        }

        const stageRect = petStage.getBoundingClientRect();
        const roll = Math.random();
        if (roll < 0.45) {
            const newLeft = Math.random() * (stageRect.width - 200) + 100;
            const newBottom = Math.random() * (stageRect.height * 0.15) + stageRect.height * 0.12;
            hopTo(newLeft, newBottom);
        } else if (roll < 0.72) {
            petSprite.classList.add('happy');
            setTimeout(() => petSprite.classList.remove('happy'), 600);
        } else {
            const currentLeft = parseFloat(getComputedStyle(petSprite).left) || stageRect.width / 2;
            const offset = (Math.random() - 0.5) * 24;
            petSprite.style.left = `${clamp(currentLeft + offset, 100, stageRect.width - 100)}px`;
        }
    }

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        if (pettingMode) {
            return;
        }

        idleTimer = setTimeout(() => {
            idleBehavior();
            resetIdleTimer();
        }, 4000 + Math.random() * 3000);
    }

    function enterPettingMode() {
        if (pettingMode || !getActivePet()) {
            return;
        }

        pettingMode = true;
        lastStrokePoint = null;
        closeAllPanels();
        container.classList.add('is-petting-mode');
        setActiveTool('pet');
        clearTimeout(idleTimer);
        positionPetForCurrentMode();
        renderPetGrime();
        updateModeHud();
    }

    function exitPettingMode() {
        if (!pettingMode) {
            return;
        }

        pettingMode = false;
        lastStrokePoint = null;
        container.classList.remove('is-petting-mode', 'tool-clean');
        activeTool = 'pet';
        updateModeHud();
        renderPetGrime();
        positionPetForCurrentMode();
        resetIdleTimer();
        flushAllPetGains();
    }

    function switchPet(petId) {
        if (petId === gameData.activePetId) {
            closeAllPanels();
            return;
        }

        const stageRect = petStage.getBoundingClientRect();
        const currentBottom = parseFloat(getComputedStyle(petSprite).bottom) || stageRect.height * 0.2;
        exitPettingMode();
        setPetPosition(-150, currentBottom);
        petSprite.style.opacity = '0.5';

        setTimeout(() => {
            gameData.activePetId = petId;
            updatePetDisplay();
            updateBadges();
            renderFoodGrid();
            renderHealGrid();
            renderPetGrid();

            setPetPosition(stageRect.width + 150, currentBottom);
            petSprite.style.opacity = '1';
            requestAnimationFrame(() => {
                positionPetForCurrentMode();
                createDustPuff(stageRect.width / 2, stageRect.height - currentBottom);
            });
        }, 220);

        closeAllPanels();
    }

    foodBtn.addEventListener('click', () => togglePanel(foodPanel, foodBtn));
    healBtn.addEventListener('click', () => togglePanel(healPanel, healBtn));
    petBtn.addEventListener('click', () => togglePanel(petPanel, petBtn));
    document.getElementById('foodClose').addEventListener('click', closeAllPanels);
    document.getElementById('healClose').addEventListener('click', closeAllPanels);
    document.getElementById('petClose').addEventListener('click', closeAllPanels);
    petToolBtn.addEventListener('click', () => setActiveTool('pet'));
    washToolBtn.addEventListener('click', () => {
        if (!washToolBtn.disabled) {
            setActiveTool('clean');
        }
    });

    petSprite.addEventListener('click', (event) => {
        if (draggingItem) {
            return;
        }
        event.stopPropagation();
        if (!pettingMode) {
            enterPettingMode();
        }
    });

    petStage.addEventListener('pointermove', (event) => {
        if (!pettingMode || draggingItem) {
            return;
        }
        if (!isPointOnPet(event.clientX, event.clientY)) {
            lastStrokePoint = null;
            return;
        }

        if (activeTool === 'clean') {
            handleCleaningMove(event.clientX, event.clientY);
        } else {
            handlePettingMove(event.clientX, event.clientY);
        }
    });

    petStage.addEventListener('click', (event) => {
        if (draggingItem) {
            return;
        }

        if (pettingMode) {
            if (!isPointOnPet(event.clientX, event.clientY)) {
                exitPettingMode();
            }
            return;
        }

        if (isPointOnPet(event.clientX, event.clientY)) {
            return;
        }

        const stageRect = petStage.getBoundingClientRect();
        const clickX = event.clientX - stageRect.left;
        const clickY = event.clientY - stageRect.top;
        createPetIndicator(event.clientX, event.clientY);
        hopTo(clickX, stageRect.height - clickY);
        resetIdleTimer();
    });

    function init() {
        updatePetDisplay();
        updateBadges();
        renderFoodGrid();
        renderHealGrid();
        renderPetGrid();
        positionPetForCurrentMode();
        resetIdleTimer();
    }

    requestAnimationFrame(() => {
        requestAnimationFrame(init);
    });

    window.addEventListener('resize', () => {
        positionPetForCurrentMode();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            flushAllPetGains();
        }
    });
    window.addEventListener('pagehide', flushAllPetGains);
})();
</script>
