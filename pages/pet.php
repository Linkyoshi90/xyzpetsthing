<?php require_login();
require_once __DIR__.'/../lib/pets.php';
$uid = current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'feed') {
    $pet_id = (int)($_POST['pet_id'] ?? 0);
    $item_id = (int)($_POST['item_id'] ?? 0);
    $row = q(
        "SELECT ui.quantity, i.replenish FROM user_inventory ui
         JOIN items i ON i.item_id = ui.item_id
         LEFT JOIN item_categories ic ON ic.category_id = i.category_id
         WHERE ui.user_id = ? AND ui.item_id = ? AND ic.category_name = 'Food'",
        [$uid, $item_id]
    )->fetch(PDO::FETCH_ASSOC);
    if ($row && (int)$row['quantity'] > 0) {
        q("UPDATE pet_instances SET hunger = GREATEST(0, hunger - ?) WHERE pet_instance_id = ? AND owner_user_id = ?", [$row['replenish'], $pet_id, $uid]);
        if ((int)$row['quantity'] > 1) {
            q("UPDATE user_inventory SET quantity = quantity - 1 WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
        } else {
            q("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
        }
    }
    header('Location: ?pg=pet&id=' . $pet_id);
    exit;
}
$pets = get_user_pets($uid);
$food_items = q(
    "SELECT ui.item_id, i.item_name, ui.quantity FROM user_inventory ui
     JOIN items i ON i.item_id = ui.item_id
     LEFT JOIN item_categories ic ON ic.category_id = i.category_id
     WHERE ui.user_id = ? AND ic.category_name = 'Food'",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);
$pet = null;
$pid = isset($_GET['id']) ? (int)$_GET['id'] : null;
if ($pid) {
    foreach ($pets as $p) {
        if ((int)$p['pet_instance_id'] === $pid) {
            $pet = $p;
            break;
        }
    }
}
if (!$pet && $pets) {
    $pet = $pets[0];
}
?>
<h1>Your Pets</h1>
<?php if ($pets): ?>
<a class="btn" href="?pg=create_pet">Create pet</a>
<p></p>
<div class="pets-grid">
<?php foreach ($pets as $pet): ?>
  <div class="card glass pet-card">
    <img class="thumb" src="<?= htmlspecialchars(pet_image_url($pet['species_name'], $pet['color_name'])) ?>" alt="">
    <h2><?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?></h2>
    <button class="show-details" data-id="<?= (int)$pet['pet_instance_id'] ?>">Details</button>
    <div id="pet-<?= (int)$pet['pet_instance_id'] ?>" class="pet-details" style="display:none;">
      <p>Species: <?= htmlspecialchars($pet['species_name']) ?></p>
      <p>Color: <?= htmlspecialchars($pet['color_name'] ?? 'None') ?></p>
      <p>Gender: <?= htmlspecialchars($pet['gender']) ?></p>
      <p>Level: <?= (int)$pet['level'] ?></p>
      <p>HP: <?= (int)($pet['hp_current'] ?? 0) ?></p>
      <p>Hunger: <?= (int)$pet['hunger'] ?></p>
      <p>Happiness: <?= (int)$pet['happiness'] ?></p>
      <div class="actions">
        <button class="play">Play</button>
        <button class="feed">Feed</button>
        <button class="read">Read</button>
        <button class="heal">Heal</button>
        <button class="close">Close</button>
      </div>
      <div class="feed-form" style="display:none;">
        <form method="post">
          <input type="hidden" name="action" value="feed">
          <input type="hidden" name="pet_id" value="<?= (int)$pet['pet_instance_id'] ?>">
          <select name="item_id">
            <?php foreach ($food_items as $item): ?>
              <option value="<?= (int)$item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?> (x<?= (int)$item['quantity'] ?>)</option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Feed to <?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?></button>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<script>
document.querySelectorAll('.show-details').forEach(btn => {
  btn.addEventListener('click', () => {
    const details = document.getElementById('pet-' + btn.dataset.id);
    if (details) details.style.display = 'block';
  });
});
document.querySelectorAll('.pet-details .close').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.closest('.pet-details').style.display = 'none';
  });
});
document.querySelectorAll('.pet-details .actions .feed').forEach(btn => {
  btn.addEventListener('click', () => {
    const form = btn.closest('.pet-details').querySelector('.feed-form');
    if (form) form.style.display = 'block';
  });
});
document.querySelectorAll('.pet-details .actions button').forEach(btn => {
  if (!btn.classList.contains('close') && !btn.classList.contains('feed')) {
    btn.addEventListener('click', () => alert('Not implemented'));
  }
});
</script>
<?php else: ?>
<p>No pets yet. <a href="?pg=create_pet">Create one</a>.</p>
<?php endif; ?>