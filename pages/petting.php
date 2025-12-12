<?php
require_login();
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/shops.php';

$uid = current_user()['id'];
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'feed_pet') {
    header('Content-Type: application/json');
    $pet_id = (int)($_POST['pet_id'] ?? 0);
    $item_id = (int)($_POST['item_id'] ?? 0);

    $pet = q(
        "SELECT pet_instance_id, species_id FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
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

    q(
        "UPDATE pet_instances"
        . "   SET hunger = GREATEST(0, hunger - ?),"
        . "       happiness = LEAST(100, happiness + ?)"
        . " WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$replenish, $hearts * 5, $pet_id, $uid]
    );

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
    ]);
    exit;
}

$pets = get_user_pets($uid);

if (!$pets) {
    echo '<p>No pets yet. <a href="?pg=create_pet">Create one</a>.</p>';
    return;
}

$food_items = q(
    "SELECT ui.item_id, i.item_name, ui.quantity FROM user_inventory ui"
    . " JOIN items i ON i.item_id = ui.item_id"
    . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
    . " WHERE ui.user_id = ? AND ic.category_name = 'Food'",
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
    ];
}, $food_items);

$pets_payload = array_map(function ($pet) use ($preferences) {
    return [
        'id' => (int)$pet['pet_instance_id'],
        'name' => $pet['nickname'] ?: $pet['species_name'],
        'speciesId' => (int)$pet['species_id'],
        'color' => $pet['color_name'] ?? '',
        'image' => pet_image_url($pet['species_name'], $pet['color_name']),
        'preferences' => $preferences[(int)$pet['species_id']] ?? [],
    ];
}, $pets);
?>
<link rel="stylesheet" href="assets/css/petting.css">
<script>
    window.pettingData = {
        activePetId: <?= (int)$active_pet['pet_instance_id'] ?>,
        pets: <?= json_encode($pets_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        food: <?= json_encode($food_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        assets: {
            inventoryIcon: 'images/games/ui/inventory.png',
            petIcon: 'images/games/ui/pet_list.png',
            dust: 'images/games/effects/dust.png',
            heart: 'images/games/effects/heart.png',
            crumbs: 'images/games/effects/crumb.png',
            eat: 'assets/sfx/eat.wav',
            hop: 'assets/sfx/hop.wav'
        }
    };
</script>
<h1>Petting Mode</h1>
<p class="petting-hint">Feed your pets directly, toss them snacks, and swap companions without leaving the screen. Everything works with the mouse.</p>
<div class="petting-shell">
    <div class="petting-viewport" id="petting-viewport">
        <button class="inventory-toggle" id="inventory-toggle" type="button" aria-label="Open inventory">🍱</button>
        <div class="inventory-banner" id="inventory-banner" aria-live="polite">
            <div class="inventory-list" id="inventory-list"></div>
            <button class="banner-close" id="inventory-close" type="button" aria-label="Close inventory">✕</button>
        </div>

        <button class="pet-switch-toggle" id="pet-switch-toggle" type="button" aria-label="Switch pet">⬆</button>
        <div class="pet-banner" id="pet-banner" aria-live="polite">
            <div class="pet-list" id="pet-list"></div>
            <button class="banner-close" id="pet-banner-close" type="button" aria-label="Close pet list">✕</button>
        </div>

        <div class="petting-stage" id="petting-stage">
            <div class="pet-shadow"></div>
            <img id="active-pet" class="pet-sprite" src="<?= htmlspecialchars(pet_image_url($active_pet['species_name'], $active_pet['color_name'])) ?>" alt="Active pet">
            <div class="dust-cloud" id="dust-cloud"></div>
            <div class="heart-layer" id="heart-layer"></div>
            <div class="crumb-layer" id="crumb-layer"></div>
        </div>
    </div>
</div>
<script defer src="assets/js/petting.js"></script>
