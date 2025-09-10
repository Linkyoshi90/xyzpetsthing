<?php require_login();
$uid = current_user()['id'];
$rows = q("SELECT i.item_name, ic.category_name, ui.quantity FROM user_inventory ui JOIN items i ON i.item_id=ui.item_id LEFT JOIN item_categories ic ON ic.category_id=i.category_id WHERE ui.user_id=?",[$uid])->fetchAll(PDO::FETCH_ASSOC);
?>
<h1>Inventory</h1>
<div class="grid three">
<?php foreach($rows as $r): ?>
  <div class="card glass">
    <img class="icon" src="images/items/<?= htmlspecialchars($r['item_name']) ?>.png">
    <h3><?= htmlspecialchars($r['item_name']) ?></h3>
    <p class="muted"><?= htmlspecialchars($r['category_name'] ?? '') ?>  x<?= (int)$r['quantity'] ?></p>
    <!-- future: Use item on a pet -->
  </div>
<?php endforeach; ?>
<?php if(!$rows): ?><p>Your bag is empty.</p><?php endif; ?>
</div>