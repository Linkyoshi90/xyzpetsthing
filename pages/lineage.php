<?php
require_login();
require_once __DIR__ . '/../lib/input.php';
require_once __DIR__ . '/../lib/lineage.php';

$pet_id = input_int($_GET['id'] ?? 0, 1);
$lineage = lineage_for_user((int)current_user()['id'], $pet_id);
?>
<h1>Pet Lineage</h1>

<?php if (!$lineage): ?>
  <div class="alert err">That pet was not found.</div>
  <a class="btn" href="?pg=pet">Back to pets</a>
<?php else: ?>
  <p><a class="btn" href="?pg=pet">Back to pets</a></p>
  <div class="card glass lineage-panel">
    <p class="mini">Recorded parents are shown beneath each member. Curved purple return lines connect branches that share the same ancestor.</p>
    <div id="lineage-tree" class="lineage-tree" aria-live="polite"></div>
  </div>
  <script id="lineage-data" type="application/json"><?= json_encode(
      $lineage,
      JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
  ) ?></script>
<?php endif; ?>
