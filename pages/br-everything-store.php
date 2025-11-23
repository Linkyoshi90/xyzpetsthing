<?php
require_login();
require_once __DIR__.'/../lib/shops.php';

$shopName = 'Everything Store';
$catalog = shop_full_catalog();
?>
<section class="pizza-shop">
  <header class="pizza-shop__header">
    <h1><?= htmlspecialchars($shopName) ?></h1>
    <p class="muted">A gallery of every known item. Browse, hover, and dream up combinations to hunt for later.</p>
  </header>
  <div class="card glass">
    <h3>Back to Bretonreach</h3>
    <a class="btn" href="?pg=bretonreach">Back</a>
  </div>
  <div class="card glass pizza-shop__menu">
    <h2>Item Catalog</h2>
    <p class="muted">All items are on display only. Hover or focus a card to read the description.</p>
    <?php if (!$catalog): ?>
    <p class="muted">No items have been documented yet.</p>
    <?php else: ?>
    <div class="pizza-menu-grid" role="list" aria-label="All discovered items">
      <?php foreach ($catalog as $item): ?>
      <?php
        $priceDisplay = $item['price'] === null
          ? '—'
          : number_format($item['price'], 2)." ".htmlspecialchars(APP_CURRENCY_LONG_NAME);
        $description = $item['description'] ?? '';
        $descriptionText = $description !== '' ? $description : 'No description provided yet.';
      ?>
      <article
        class="pizza-item-card pizza-item-card--showcase"
        role="listitem"
        tabindex="0"
        title="<?= htmlspecialchars($descriptionText, ENT_QUOTES) ?>"
      >
        <figure>
          <div class="pizza-item-thumb">
            <img src="<?= htmlspecialchars($item['image'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>" loading="lazy" decoding="async">
          </div>
          <figcaption>
            <strong><?= htmlspecialchars($item['name']) ?></strong>
            <span class="muted"><?= $priceDisplay ?></span>
          </figcaption>
        </figure>
        <p class="muted pizza-item-description"><?= nl2br(htmlspecialchars($descriptionText)) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
