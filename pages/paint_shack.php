<?php
require_login();
require_once __DIR__.'/../lib/pets.php';

$uid = current_user()['id'];

$regions = q("SELECT region_id, region_name FROM regions ORDER BY region_name")->fetchAll(PDO::FETCH_ASSOC);
$regionId = (int)($_GET['region'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paint_pet'])) {
    $regionId = (int)($_POST['region_id'] ?? $regionId);
}
if ($regionId <= 0 && $regions) {
    $regionId = (int)$regions[0]['region_id'];
}
$region = null;
foreach ($regions as $r) {
    if ((int)$r['region_id'] === $regionId) {
        $region = $r;
        break;
    }
}
if (!$region && $regions) {
    $region = $regions[0];
    $regionId = (int)$region['region_id'];
}

$messages = ['error' => null, 'success' => null];

$colors = q("SELECT color_id, color_name FROM pet_colors ORDER BY color_id")->fetchAll(PDO::FETCH_ASSOC);
$colorNameToId = [];
foreach ($colors as $c) {
    $colorNameToId[strtolower($c['color_name'])] = (int)$c['color_id'];
}

$paintItemColors = [
    'red paint' => 'Red',
    'blue paint' => 'Blue',
    'green paint' => 'Green',
    'yellow paint' => 'Yellow',
    'purple paint' => 'Purple',
    'black paint' => 'Black',
    'real paintbrush' => 'Real',
];

$resolvePaintColor = function (string $itemName) use ($paintItemColors, $colorNameToId): ?array {
    $key = strtolower($itemName);
    if (!isset($paintItemColors[$key])) {
        return null;
    }
    $targetName = $paintItemColors[$key];
    $colorId = $colorNameToId[strtolower($targetName)] ?? null;
    if (!$colorId) {
        return null;
    }
    return ['color_id' => $colorId, 'color_name' => $targetName];
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paint_pet'])) {
    $petId = (int)($_POST['pet_id'] ?? 0);
    $itemId = (int)($_POST['item_id'] ?? 0);

    $pet = q(
        "SELECT pi.pet_instance_id, pi.nickname, ps.species_name, ps.region_id "
        . "FROM pet_instances pi "
        . "JOIN pet_species ps ON ps.species_id = pi.species_id "
        . "WHERE pi.pet_instance_id = ? AND pi.owner_user_id = ?",
        [$petId, $uid]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$pet) {
        $messages['error'] = 'Could not find that pet.';
    } elseif ((int)$pet['region_id'] !== $regionId) {
        $messages['error'] = 'This pet is from a different region.';
    } else {
        $itemRow = q(
            "SELECT ui.quantity, i.item_name FROM user_inventory ui "
            . "JOIN items i ON i.item_id = ui.item_id "
            . "LEFT JOIN item_categories ic ON ic.category_id = i.category_id "
            . "WHERE ui.user_id = ? AND ui.item_id = ? AND ic.category_name = 'Paint'",
            [$uid, $itemId]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$itemRow) {
            $messages['error'] = 'That paint is not in your inventory.';
        } elseif ((int)$itemRow['quantity'] <= 0) {
            $messages['error'] = 'You are out of that paint.';
        } else {
            $targetColor = $resolvePaintColor($itemRow['item_name']);
            if (!$targetColor) {
                $messages['error'] = 'This paint cannot be used on pets.';
            } else {
                q(
                    "UPDATE pet_instances SET color_id = ? WHERE pet_instance_id = ? AND owner_user_id = ?",
                    [$targetColor['color_id'], $petId, $uid]
                );
                if ((int)$itemRow['quantity'] > 1) {
                    q(
                        "UPDATE user_inventory SET quantity = quantity - 1 WHERE user_id = ? AND item_id = ?",
                        [$uid, $itemId]
                    );
                } else {
                    q("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ?", [$uid, $itemId]);
                }
                $petName = $pet['nickname'] ?: $pet['species_name'];
                $messages['success'] = sprintf('%s is now painted %s!', $petName, $targetColor['color_name']);
            }
        }
    }
}

$userPaints = q(
    "SELECT ui.item_id, ui.quantity, i.item_name FROM user_inventory ui "
    . "JOIN items i ON i.item_id = ui.item_id "
    . "LEFT JOIN item_categories ic ON ic.category_id = i.category_id "
    . "WHERE ui.user_id = ? AND ic.category_name = 'Paint'",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);

$usablePaints = [];
foreach ($userPaints as $paint) {
    $resolved = $resolvePaintColor($paint['item_name']);
    if ($resolved) {
        $usablePaints[] = array_merge($paint, $resolved);
    }
}

$pets = get_user_pets($uid);
$regionPets = array_values(array_filter($pets, static fn($p) => (int)($p['region_id'] ?? 0) === $regionId));
?>
<h1>Paint Shack - <?= htmlspecialchars($region['region_name'] ?? 'Unknown Region') ?></h1>
<p class="muted">Only creatures that hail from this region can visit this paint shack.</p>

<form method="get" class="form inline-form">
  <label for="region">Region</label>
  <select id="region" name="region">
    <?php foreach ($regions as $r): ?>
      <option value="<?= (int)$r['region_id'] ?>" <?= ((int)$r['region_id'] === $regionId) ? 'selected' : '' ?>><?= htmlspecialchars($r['region_name']) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Go</button>
</form>

<?php if ($messages['error']): ?>
  <div class="alert error"><?= htmlspecialchars($messages['error']) ?></div>
<?php endif; ?>
<?php if ($messages['success']): ?>
  <div class="alert success"><?= htmlspecialchars($messages['success']) ?></div>
<?php endif; ?>

<?php if (!$regionPets): ?>
  <p>You do not have any creatures from this region.</p>
<?php else: ?>
  <div class="pets-grid">
    <?php foreach ($regionPets as $pet): ?>
      <div class="card glass pet-card">
        <img class="thumb" src="<?= htmlspecialchars(pet_image_url($pet['species_name'], $pet['color_name'])) ?>" alt="">
        <h2><?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?></h2>
        <p class="muted">Species: <?= htmlspecialchars($pet['species_name']) ?></p>
        <p class="muted">Current color: <?= htmlspecialchars($pet['color_name'] ?? 'None') ?></p>
        <?php if ($usablePaints): ?>
          <form method="post" class="paint-form">
            <input type="hidden" name="pet_id" value="<?= (int)$pet['pet_instance_id'] ?>">
            <input type="hidden" name="region_id" value="<?= $regionId ?>">
            <label>Choose paint
              <select name="item_id">
                <?php foreach ($usablePaints as $paint): ?>
                  <option value="<?= (int)$paint['item_id'] ?>">
                    <?= htmlspecialchars($paint['item_name']) ?> (<?= htmlspecialchars($paint['color_name']) ?>, x<?= (int)$paint['quantity'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
            <button type="submit" name="paint_pet" value="1">Paint <?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?></button>
          </form>
        <?php else: ?>
          <p>You do not have any paint items.</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>