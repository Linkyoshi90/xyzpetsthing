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
<h1>Your Pet</h1>
<?php if ($pet): ?>
<div class="card glass">
  <img class="thumb" src="<?= htmlspecialchars(pet_image_url($pet['species_name'], $pet['color_name'])) ?>" alt="">
  <h2><?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?></h2>
  <p>Level <?= (int)$pet['level'] ?> HP <?= (int)($pet['hp_current'] ?? 0) ?></p>
</div>
<?php else: ?>
<p>No pets yet. <a href="?pg=create_pet">Create one</a>.</p>
<?php endif; ?>