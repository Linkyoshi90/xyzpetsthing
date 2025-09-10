<?php require_login();
require_once __DIR__.'/../lib/pets.php';
$uid = current_user()['id'];
$pets = get_user_pets($uid);
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
document.querySelectorAll('.pet-details .actions button').forEach(btn => {
  if (!btn.classList.contains('close')) {
    btn.addEventListener('click', () => alert('Not implemented'));
  }
});
</script>
<?php else: ?>
<p>No pets yet. <a href="?pg=create_pet">Create one</a>.</p>
<?php endif; ?>