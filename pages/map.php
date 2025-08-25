<?php require_login();
$maps = q("SELECT * FROM maps ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<h1>World Map</h1>
<div class="grid three">
<?php foreach($maps as $m): 
  $locs = q("SELECT * FROM locations WHERE map_id=?",[$m['id']])->fetchAll(PDO::FETCH_ASSOC); ?>
  <div class="card glass">
    <h3><?= htmlspecialchars($m['name']) ?></h3>
    <p class="muted"><?= htmlspecialchars($m['desc_text']) ?></p>
    <ul class="tight">
      <?php foreach($locs as $l): ?>
        <li><a href="#"><?= htmlspecialchars($l['name']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endforeach; ?>
</div>
