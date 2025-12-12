<?php
require_login();
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/shops.php';

$uid = current_user()['id'];
$action = $_POST['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'fetch_hunger') {
    header('Content-Type: application/json');
    $pet_id = (int)($_POST['pet_id'] ?? 0);

    $hunger = q(
        "SELECT hunger FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$pet_id, $uid]
    )->fetchColumn();

    if ($hunger === false) {
        echo json_encode(['ok' => false, 'message' => 'That pet is not available.']);
        exit;
    }

    echo json_encode(['ok' => true, 'hunger' => (int)$hunger]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'sync_hunger') {
    header('Content-Type: application/json');
    $pet_id = (int)($_POST['pet_id'] ?? 0);
    $hunger = (int)($_POST['hunger'] ?? 0);

    $exists = q(
        "SELECT 1 FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$pet_id, $uid]
    )->fetchColumn();

    if (!$exists) {
        echo json_encode(['ok' => false, 'message' => 'That pet is not available.']);
        exit;
    }

    $max_hunger = 100;
    $next_hunger = max(0, min($hunger, $max_hunger));

    q(
        "UPDATE pet_instances SET hunger = ? WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$next_hunger, $pet_id, $uid]
    );

    echo json_encode(['ok' => true, 'hunger' => $next_hunger]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'feed_pet') {
    header('Content-Type: application/json');
    $pet_id = (int)($_POST['pet_id'] ?? 0);
    $item_id = (int)($_POST['item_id'] ?? 0);

    $pet = q(
        "SELECT pet_instance_id, species_id, hunger FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$pet_id, $uid]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$pet) {
        echo json_encode(['ok' => false, 'message' => 'That pet is not available.']);
        exit;
    }

    $row = q(
        "SELECT ui.quantity, i.replenish FROM user_inventory ui"
        . " JOIN items i ON i.item_id = ui.item_id"
        . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
        . " WHERE ui.user_id = ? AND ui.item_id = ? AND ic.category_name = 'Food'",
        [$uid, $item_id]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$row || (int)$row['quantity'] < 1) {
        echo json_encode(['ok' => false, 'message' => 'No more of that item left.']);
        exit;
    }

    $like = q(
        "SELECT like_scale FROM food_preferences WHERE species_id = ? AND item_id = ?",
        [$pet['species_id'], $item_id]
    )->fetch(PDO::FETCH_ASSOC);

    $like_scale = $like ? (int)$like['like_scale'] : 2;
    $hearts = max(1, min(3, $like_scale));
    $replenish = max(1, (int)($row['replenish'] ?? 1));
    $max_hunger = 100;

    if ((int)$pet['hunger'] >= $max_hunger) {
        echo json_encode(['ok' => false, 'message' => 'Pet is already full.', 'hunger' => (int)$pet['hunger']]);
        exit;
    }

    q(
        "UPDATE pet_instances"
        . "   SET hunger = LEAST(?, hunger + ?),"
        . "       happiness = LEAST(100, happiness + ?)"
        . " WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$max_hunger, $replenish, $hearts * 5, $pet_id, $uid]
    );

    $new_hunger = q(
        "SELECT hunger FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$pet_id, $uid]
    )->fetchColumn();

    if ((int)$row['quantity'] > 1) {
        q("UPDATE user_inventory SET quantity = quantity - 1 WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
        $remaining = (int)$row['quantity'] - 1;
    } else {
        q("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
        $remaining = 0;
    }

    echo json_encode([
        'ok' => true,
        'hearts' => $hearts,
        'remaining' => $remaining,
        'hunger' => (int)$new_hunger,
        'full' => (int)$new_hunger >= $max_hunger,
    ]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'heal_pet') {
    header('Content-Type: application/json');
    $pet_id = (int)($_POST['pet_id'] ?? 0);
    $item_id = (int)($_POST['item_id'] ?? 0);

    $pet = q(
        "SELECT pet_instance_id, hp_current, hp_max FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$pet_id, $uid]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$pet) {
        echo json_encode(['ok' => false, 'message' => 'That pet is not available.']);
        exit;
    }

    $row = q(
        "SELECT ui.quantity, i.replenish FROM user_inventory ui"
        . " JOIN items i ON i.item_id = ui.item_id"
        . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
        . " WHERE ui.user_id = ? AND ui.item_id = ? AND ic.category_name = 'Potion'",
        [$uid, $item_id]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$row || (int)$row['quantity'] < 1) {
        echo json_encode(['ok' => false, 'message' => 'No more of that item left.']);
        exit;
    }

    $max_hp = isset($pet['hp_max']) ? (int)$pet['hp_max'] : (int)($pet['hp_current'] ?? 0);
    $max_hp = max(1, $max_hp);
    $current_hp = max(0, (int)($pet['hp_current'] ?? 0));

    if ($current_hp >= $max_hp) {
        echo json_encode(['ok' => false, 'message' => 'Pet is already fully healed.', 'hp' => $current_hp, 'hpMax' => $max_hp]);
        exit;
    }

    $healing = max(1, (int)($row['replenish'] ?? 1));

    q(
        "UPDATE pet_instances"
        . "   SET hp_current = IF(hp_max IS NULL, hp_current + ?, LEAST(hp_max, hp_current + ?))"
        . " WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$healing, $healing, $pet_id, $uid]
    );

    $after = q(
        "SELECT hp_current, hp_max FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$pet_id, $uid]
    )->fetch(PDO::FETCH_ASSOC);

    if ((int)$row['quantity'] > 1) {
        q("UPDATE user_inventory SET quantity = quantity - 1 WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
        $remaining = (int)$row['quantity'] - 1;
    } else {
        q("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
        $remaining = 0;
    }

    echo json_encode([
        'ok' => true,
        'remaining' => $remaining,
        'hp' => (int)($after['hp_current'] ?? $current_hp),
        'hpMax' => (int)($after['hp_max'] ?? $max_hp),
    ]);
    exit;
}


$pets = get_user_pets($uid);

if (!$pets) {
    echo '<p>No pets yet. <a href="?pg=create_pet">Create one</a>.</p>';
    return;
}

$food_items = q(
    "SELECT ui.item_id, i.item_name, ui.quantity, i.replenish FROM user_inventory ui"
    . " JOIN items i ON i.item_id = ui.item_id"
    . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
    . " WHERE ui.user_id = ? AND ic.category_name = 'Food'",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);

$healing_items = q(
    "SELECT ui.item_id, i.item_name, ui.quantity, i.replenish FROM user_inventory ui"
    . " JOIN items i ON i.item_id = ui.item_id"
    . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
    . " WHERE ui.user_id = ? AND ic.category_name = 'Potion'",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);


$pet_lookup = [];
foreach ($pets as $p) {
    $pet_lookup[(int)$p['pet_instance_id']] = $p;
}

$pid = isset($_GET['id']) ? (int)$_GET['id'] : null;
$active_pet = ($pid && isset($pet_lookup[$pid])) ? $pet_lookup[$pid] : $pets[0];

$species_ids = array_values(array_unique(array_map(fn($p) => (int)$p['species_id'], $pets)));
$preference_rows = [];
if ($species_ids) {
    $placeholders = implode(',', array_fill(0, count($species_ids), '?'));
    $preference_rows = q(
        "SELECT species_id, item_id, like_scale FROM food_preferences WHERE species_id IN ($placeholders)",
        $species_ids
    )->fetchAll(PDO::FETCH_ASSOC);
}

$preferences = [];
foreach ($preference_rows as $pref) {
    $sid = (int)$pref['species_id'];
    $iid = (int)$pref['item_id'];
    $preferences[$sid][$iid] = (int)$pref['like_scale'];
}

$food_payload = array_map(function ($item) use ($preferences, $active_pet) {
    $imageFile = shop_find_item_image($item['item_name']);
    return [
        'id' => (int)$item['item_id'],
        'name' => $item['item_name'],
        'quantity' => (int)$item['quantity'],
        'image' => 'images/items/' . rawurlencode($imageFile),
        'preference' => $preferences[(int)$active_pet['species_id']][(int)$item['item_id']] ?? null,
        'replenish' => (int)$item['replenish'],
    ];
}, $food_items);

$healing_payload = array_map(function ($item) {
    $imageFile = shop_find_item_image($item['item_name']);
    return [
        'id' => (int)$item['item_id'],
        'name' => $item['item_name'],
        'quantity' => (int)$item['quantity'],
        'image' => 'images/items/' . rawurlencode($imageFile),
        'healing' => (int)$item['replenish'],
    ];
}, $healing_items);

$pets_payload = array_map(function ($pet) use ($preferences) {
    return [
        'id' => (int)$pet['pet_instance_id'],
        'name' => $pet['nickname'] ?: $pet['species_name'],
        'speciesId' => (int)$pet['species_id'],
        'color' => $pet['color_name'] ?? '',
        'image' => pet_image_url($pet['species_name'], $pet['color_name']),
        'preferences' => $preferences[(int)$pet['species_id']] ?? [],
        'hunger' => (int)$pet['hunger'],
        'hpCurrent' => (int)$pet['hp_current'],
        'hpMax' => $pet['hp_max'] !== null ? (int)$pet['hp_max'] : (int)$pet['hp_current'],
    ];
}, $pets);
?>
<link rel="stylesheet" href="assets/css/petting.css">
<script>
    window.pettingData = {
        activePetId: <?= (int)$active_pet['pet_instance_id'] ?>,
        pets: <?= json_encode($pets_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        food: <?= json_encode($food_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        healing: <?= json_encode($healing_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        assets: {
            inventoryIcon: 'images/games/ui/inventory.png',
            petIcon: 'images/games/ui/pet_list.png',
            healingIcon: 'images/games/petting_inventory.svg',
            dust: 'images/games/petting_dust.svg',
            heart: 'images/games/petting_heart.svg',
            crumbs: 'images/games/petting_crumb.svg',
            eat: 'assets/sfx/eat.wav',
            hop: 'assets/sfx/hop.wav'
        },
        hungerMax: 100
    };
</script>
<h1>Petting Mode</h1>
<p class="petting-hint">Feed your pets directly, toss them snacks, and swap companions without leaving the screen. Everything works with the mouse. 
By the by, if it doesn't update the meters or inventory, refresh the page</p>
<div class="petting-shell">
    <div class="petting-viewport" id="petting-viewport">
        <button class="inventory-toggle" id="inventory-toggle" type="button" aria-label="Open inventory">🍱</button>
        <div class="inventory-banner" id="inventory-banner" aria-live="polite">
            <div class="inventory-list" id="inventory-list"></div>
            <button class="banner-close" id="inventory-close" type="button" aria-label="Close inventory">✕</button>
        </div>
        <button class="healing-toggle" id="healing-toggle" type="button" aria-label="Open healing items">🩹</button>
        <div class="inventory-banner" id="healing-banner" aria-live="polite">
            <div class="inventory-list" id="healing-list"></div>
            <button class="banner-close" id="healing-close" type="button" aria-label="Close healing inventory">✕</button>
        </div>

        <button class="pet-switch-toggle" id="pet-switch-toggle" type="button" aria-label="Switch pet">⬆</button>
        <div class="pet-banner" id="pet-banner" aria-live="polite">
            <div class="pet-list" id="pet-list"></div>
            <button class="banner-close" id="pet-banner-close" type="button" aria-label="Close pet list">✕</button>
        </div>

        <div class="petting-stage" id="petting-stage">
            <div class="pet-status">
                <div class="food-meter" id="hunger-meter">
                    <span class="label">Food</span>
                    <div class="bar"><div class="fill"></div></div>
                    <span class="value">0/100</span>
                </div>
                <div class="food-meter" id="health-meter">
                    <span class="label">HP</span>
                    <div class="bar"><div class="fill"></div></div>
                    <span class="value">0/0</span>
                </div>
                <div class="full-banner" id="full-banner">She's full</div>
            </div>
            <div class="pet-shadow"></div>
            <img id="active-pet" class="pet-sprite" src="<?= htmlspecialchars(pet_image_url($active_pet['species_name'], $active_pet['color_name'])) ?>" alt="Active pet">
            <div class="dust-cloud" id="dust-cloud"></div>
            <div class="heart-layer" id="heart-layer"></div>
            <div class="crumb-layer" id="crumb-layer"></div>
        </div>
    </div>
</div>
<script defer src="assets/js/petting.js"></script>
