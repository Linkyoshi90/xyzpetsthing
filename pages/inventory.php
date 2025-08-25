<?php require_login();
$uid = current_user()['id'];
$rows = q("SELECT i.name,i.slug,i.icon,i.type,ui.qty FROM user_items ui JOIN items i ON i.id=ui.item_id WHERE ui.user_id=?",[$uid])->fetchAll(PDO::FETCH_ASSOC);
?>
<h1>Inventory</h1>
<div class="grid three">
<?php foreach($rows as $r): ?>
  <div class="card glass">
    <img class="icon" src="<?= htmlspecialchars($r['icon'] ?: '/assets/items/placeholder.png') ?>">
    <h3><?= htmlspecialchars($r['name']) ?></h3>
    <p class="muted"><?= htmlspecialchars($r['type']) ?> · x<?= (int)$r['qty'] ?></p>
    <!-- future: Use item on a pet -->
  </div>
<?php endforeach; ?>
<?php if(!$rows): ?><p>Your bag is empty.</p><?php endif; ?>
</div>
