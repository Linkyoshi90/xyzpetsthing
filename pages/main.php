<?php require_login();
require_once __DIR__.'/../lib/pets.php';
$uid = current_user()['id'];
$pets = get_user_pets($uid);
?>
<h1>Your Pets</h1>
<?php if(count($pets) < 4): ?>
<a class="btn" href="?pg=create_pet">Create pet</a>
<?php endif; ?>
<div class="grid three">
<?php foreach($pets as $p): ?>
  <div class="card glass">
    <img class="thumb" src="<?= htmlspecialchars(pet_image_url($p['species_name'], $p['color_name'])) ?>" alt="">
    <h3><?= htmlspecialchars($p['nickname'] ?: $p['species_name']) ?> <small>(<?= htmlspecialchars($p['species_name']) ?>)</small></h3>
    <p>Level <?= (int)$p['level'] ?>  HP <?= (int)($p['hp_current'] ?? 0) ?></p>
  </div>
<?php endforeach; ?>
<?php if(!$pets): ?>
  <p>No pets yet — create your first companion!</p>
<?php endif; ?>
</div>