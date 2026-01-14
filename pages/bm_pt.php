<?php
require_login();
require_once __DIR__.'/../db.php';
require_once __DIR__.'/../lib/input.php';

$uid = current_user()['id'];
$messages = ['success' => null, 'error' => null];

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = input_int($_POST['item_id'] ?? 0, 1);
    if ($itemId <= 0) {
        $messages['error'] = 'Please pick a valid picnic table item.';
    } else {
        try {
            $pdo->beginTransaction();
            $rowStmt = $pdo->prepare(
                'SELECT pti.picnic_item_id, pti.available_quantity, pti.chance_percent, i.item_name, i.item_id
                 FROM picnic_tree_items pti
                 JOIN items i ON i.item_id = pti.item_id
                 WHERE pti.item_id = ? FOR UPDATE'
            );
            $rowStmt->execute([$itemId]);
            $item = $rowStmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                $messages['error'] = 'That picnic item has already been cleared away.';
            } elseif ((int) $item['available_quantity'] <= 0) {
                $messages['error'] = 'The picnic table has run out of that item.';
            } else {
                $roll = mt_rand(0, 10000) / 100;
                if ($roll <= (float) $item['chance_percent']) {
                    $update = $pdo->prepare(
                        'UPDATE picnic_tree_items SET available_quantity = available_quantity - 1 WHERE picnic_item_id = ?'
                    );
                    $update->execute([(int) $item['picnic_item_id']]);

                    $inv = $pdo->prepare(
                        'INSERT INTO user_inventory (user_id, item_id, quantity)
                         VALUES (?, ?, 1)
                         ON DUPLICATE KEY UPDATE quantity = quantity + 1'
                    );
                    $inv->execute([$uid, (int) $item['item_id']]);

                    $messages['success'] = sprintf(
                        'You snagged the %s from the picnic table!',
                        htmlspecialchars($item['item_name'])
                    );
                } else {
                    $messages['error'] = 'You reached for the item, but someone else grabbed it first.';
                }
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $messages['error'] = 'Something went wrong while visiting the picnic table. Please try again.';
            app_add_error_from_exception($e, 'Picnic tree error:');
        }
    }
}

$items = q(
    'SELECT pti.item_id, pti.available_quantity, pti.chance_percent, i.item_name, i.item_description
     FROM picnic_tree_items pti
     JOIN items i ON i.item_id = pti.item_id
     ORDER BY i.item_name'
)->fetchAll(PDO::FETCH_ASSOC);

$totalAvailable = 0;
foreach ($items as $it) {
    $totalAvailable += (int) $it['available_quantity'];
}
?>
<link rel="stylesheet" href="assets/css/picnic-tree.css">
<section class="picnic-hero">
  <div>
    <h1>Picnic Tree</h1>
    <p>Like the old money tree, the picnic table gathers stray goodies left by generous visitors. Pick an item and hope your luck holds!</p>
    <ul>
      <li>Each item shows the remaining quantity.</li>
      <li>Click to try your luck—success depends on the listed chance.</li>
      <li>When the table is empty, you can only enjoy the shade.</li>
    </ul>
  </div>
  <div class="picnic-visual" aria-hidden="true">
    🍃🧺 A quiet grove with a picnic table. Free finds pile up here until someone lucky reaches for them.
  </div>
</section>

<?php if ($messages['success']): ?>
  <div class="picnic-message success" role="status"><?= $messages['success'] ?></div>
<?php elseif ($messages['error']): ?>
  <div class="picnic-message error" role="alert"><?= htmlspecialchars($messages['error']) ?></div>
<?php endif; ?>

<?php if ($totalAvailable <= 0): ?>
  <div class="picnic-empty" role="status">The picnic table is empty. You sit for a moment and enjoy the breeze.</div>
<?php else: ?>
  <div class="picnic-table">
    <?php foreach ($items as $item): ?>
      <article class="picnic-card">
        <h3><?= htmlspecialchars($item['item_name']) ?></h3>
        <?php if (!empty($item['item_description'])): ?>
          <p class="muted"><?= htmlspecialchars($item['item_description']) ?></p>
        <?php endif; ?>
        <div class="picnic-meta">
          <span>Left: <?= (int) $item['available_quantity'] ?></span>
          <span>Chance: <?= number_format((float) $item['chance_percent'], 2) ?>%</span>
        </div>
        <div class="picnic-actions">
          <?php if ((int) $item['available_quantity'] > 0): ?>
            <form method="post">
              <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
              <button class="btn btn-primary" type="submit">Try to take it</button>
            </form>
          <?php else: ?>
            <p class="muted">All gone.</p>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
