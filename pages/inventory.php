<?php require_login();
require_once __DIR__.'/../lib/temp_user.php';
$uid = current_user()['id'];
if ($uid === 0) {
    $rows = temp_user_inventory_rows();
} else {
    $rows = q("SELECT i.item_name, ic.category_name, ui.quantity FROM user_inventory ui JOIN items i ON i.item_id=ui.item_id LEFT JOIN item_categories ic ON ic.category_id=i.category_id WHERE ui.user_id=?",[$uid])->fetchAll(PDO::FETCH_ASSOC);
}
?>
<h1>Inventory</h1>
<div class="grid three">
<?php foreach($rows as $r): ?>
<?php
    $itemName = $r['item_name'];
    $imageBase = 'images/items/' . $itemName;
    $pngPath = $imageBase . '.png';
    $webpPath = $imageBase . '.webp';
    $pngFile = __DIR__ . '/../' . $pngPath;
    $webpFile = __DIR__ . '/../' . $webpPath;
    $imageSrc = $pngPath;
    if (!file_exists($pngFile) && file_exists($webpFile)) {
        $imageSrc = $webpPath;
    }
  ?>
  <div class="card glass">
    <img class="icon" src="<?= htmlspecialchars($imageSrc) ?>">
    <h3><?= htmlspecialchars($r['item_name']) ?></h3>
    <p class="muted"><?= htmlspecialchars($r['category_name'] ?? '') ?>  x<?= (int)$r['quantity'] ?></p>
    <!-- future: Use item on a pet -->
  </div>
<?php endforeach; ?>
<?php if(!$rows): ?><p>Your bag is empty.</p><?php endif; ?>
</div>