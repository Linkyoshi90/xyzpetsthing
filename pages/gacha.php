<?php
require_login();
require_once __DIR__.'/../db.php';

$cost = 100;
$items = q(
    'SELECT item_id, item_name, base_price FROM items WHERE item_id BETWEEN 1 AND 10 ORDER BY item_id'
)->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="assets/css/gacha.css">
<section class="gacha-page">
  <header class="gacha-header">
    <div>
      <p class="eyebrow">Golden Gacha</p>
      <h1>Spin the capsule machine</h1>
      <p>Each spin costs <strong><?= number_format($cost) ?> Dosh</strong>. Loaded items come directly from your database (IDs 1-10).</p>
    </div>
    <div class="gacha-cost">100<span class="currency-label">Dosh</span></div>
  </header>

  <div class="gacha-stage">
    <div class="gacha-machine" id="gacha-machine" aria-label="Golden gacha machine">
      <div class="machine-top">
        <div class="machine-light"></div>
        <div class="machine-logo">⭐</div>
      </div>
      <div class="machine-window">
        <div class="window-glow"></div>
        <div class="capsule-outlet" id="capsule-outlet"></div>
      </div>
      <div class="machine-slot" aria-hidden="true">
        <div class="coin-slot"></div>
      </div>
      <div class="machine-handle" aria-hidden="true">
        <div class="handle-neck"></div>
        <div class="handle-knob"></div>
      </div>
      <div class="machine-base"></div>
      <div class="gacha-coin" id="gacha-coin" aria-hidden="true"></div>
    </div>

    <div class="gacha-actions">
      <p class="gacha-instruction">Insert a golden coin, watch the machine wiggle, then click the capsule to reveal the price.</p>
      <button class="btn btn-primary" id="gacha-spin" type="button">Insert coin &amp; spin</button>
      <div class="capsule-area" id="capsule-area">
        <p class="capsule-hint">Capsules will roll out here. Click one to reveal its price.</p>
      </div>
    </div>
  </div>

  <section class="gacha-pool">
    <div class="pool-heading">
      <h2>Item pool (preloaded from database)</h2>
      <p>Items 1-10 are fetched automatically. Each capsule pulls from these entries.</p>
    </div>
    <?php if ($items): ?>
      <ul class="pool-list">
        <?php foreach ($items as $item): ?>
          <li>
            <span class="pool-id">#<?= (int)$item['item_id'] ?></span>
            <span class="pool-name"><?= htmlspecialchars($item['item_name'] ?? 'Unknown item') ?></span>
            <span class="pool-price"><?= is_null($item['base_price']) ? 'No price set' : number_format((float)$item['base_price']) . ' Dosh' ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="pool-empty" role="alert">No items were found in the database for IDs 1-10.</div>
    <?php endif; ?>
  </section>
</section>
<script>
  window.gachaData = {
    cost: <?= json_encode($cost, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    items: <?= json_encode($items, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
  };
</script>
<script defer src="assets/js/gacha.js"></script>
