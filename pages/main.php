<?php require_login();
$uid = current_user()['id'];
$pets = q("SELECT p.*, s.name AS species, s.img FROM pets p JOIN species s ON s.id=p.species_id WHERE p.user_id=?",[$uid])->fetchAll(PDO::FETCH_ASSOC);
?>
<h1>Your Pets</h1>
<a class="btn" href="?pg=create_pet">+ Create a pet</a>
<div class="grid three">
<?php foreach($pets as $p): ?>
  <div class="card glass">
    <img class="thumb" src="<?= htmlspecialchars($p['img'] ?: '/assets/creatures/placeholder.png') ?>" alt="">
    <h3><?= htmlspecialchars($p['name']) ?> <small>(<?= htmlspecialchars($p['species']) ?>)</small></h3>
    <p>HP <?= (int)$p['hp'] ?> · Happy <?= (int)$p['happiness'] ?> · Energy <?= (int)$p['energy'] ?></p>
  </div>
<?php endforeach; ?>
<?php if(!$pets): ?>
  <p>No pets yet — create your first companion!</p>
<?php endif; ?>
</div>
