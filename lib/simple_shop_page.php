<?php

declare(strict_types=1);

require_once __DIR__.'/shops.php';
require_once __DIR__.'/input.php';

function simple_shop_filter_inventory_by_keywords(array $inventory, array $keywords): array
{
    $needles = array_values(array_filter(array_map(static function ($keyword): string {
        return strtolower(trim((string) $keyword));
    }, $keywords)));

    if (!$needles) {
        return $inventory;
    }

    return array_values(array_filter($inventory, static function (array $item) use ($needles): bool {
        $name = strtolower((string) ($item['name'] ?? ''));
        if ($name === '') {
            return false;
        }

        foreach ($needles as $needle) {
            if (strpos($name, $needle) !== false) {
                return true;
            }
        }

        return false;
    }));
}

function simple_shop_handle_purchase(int $shopId, array $inventory, array $labels = []): ?array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || input_string($_POST['action'] ?? '', 20) !== 'buy') {
        return null;
    }

    if (!$inventory) {
        return [
            'type' => 'error',
            'message' => $labels['empty_shop'] ?? 'This shop has no stock available.',
        ];
    }

    $inventoryById = shop_inventory_indexed($inventory);
    $itemId = input_int($_POST['item_id'] ?? 0, 1);
    $quantity = input_int($_POST['quantity'] ?? 1, 1, 99);

    if ($itemId <= 0 || $quantity <= 0 || !isset($inventoryById[$itemId])) {
        return [
            'type' => 'error',
            'message' => $labels['missing_item'] ?? 'That item is no longer sold here.',
        ];
    }

    $item = $inventoryById[$itemId];
    $stock = $item['stock'];
    if ($stock !== null && $quantity > $stock) {
        return [
            'type' => 'error',
            'message' => sprintf(
                $labels['stock_limit'] ?? 'You can only buy up to %d x %s.',
                $stock,
                $item['name']
            ),
        ];
    }

    $user = current_user();
    $uid = isset($user['id']) ? (int) $user['id'] : 0;

    try {
        $checkout = shop_checkout($shopId, $uid, [
            [
                'item_id' => $itemId,
                'quantity' => $quantity,
            ],
        ]);
    } catch (Throwable $err) {
        return [
            'type' => 'error',
            'message' => $err instanceof RuntimeException
                ? $err->getMessage()
                : ($labels['checkout_failed'] ?? 'We could not complete the purchase. Please try again.'),
        ];
    }

    $bought = $checkout['items'][0] ?? [
        'name' => $item['name'],
        'quantity' => $quantity,
    ];

    return [
        'type' => 'success',
        'message' => sprintf(
            $labels['success'] ?? 'You bought %d x %s for %s %s.',
            (int) ($bought['quantity'] ?? $quantity),
            (string) ($bought['name'] ?? $item['name']),
            number_format((float) ($checkout['total'] ?? 0), 2),
            APP_CURRENCY_LONG_NAME
        ),
    ];
}

function render_simple_shop_page(array $config): void
{
    $shop = $config['shop'] ?? ['shop_name' => $config['fallback_name'] ?? 'Shop'];
    $inventory = $config['inventory'] ?? [];
    $notice = $config['notice'] ?? null;
    $title = $shop['shop_name'] ?? ($config['fallback_name'] ?? 'Shop');
    $intro = $config['intro'] ?? '';
    $sectionTitle = $config['section_title'] ?? 'Stock';
    $emptyText = $config['empty_text'] ?? 'Nothing is stocked here right now.';
    $ariaLabel = $config['aria_label'] ?? 'Available items';
    $submitLabel = $config['submit_label'] ?? 'Buy';
    ?>
<section class="pizza-shop">
  <header class="pizza-shop__header">
    <h1><?= htmlspecialchars($title) ?></h1>
    <?php if ($intro !== ''): ?>
      <p class="muted"><?= htmlspecialchars($intro) ?></p>
    <?php endif; ?>
  </header>

  <?php if ($notice): ?>
    <div class="card glass" role="status">
      <p class="muted"><strong><?= $notice['type'] === 'error' ? 'Could not complete purchase:' : 'Purchase complete:' ?></strong> <?= htmlspecialchars($notice['message']) ?></p>
    </div>
  <?php endif; ?>

  <?php foreach (($config['info_cards'] ?? []) as $card): ?>
    <div class="card glass">
      <?php if (!empty($card['title'])): ?>
        <h2><?= htmlspecialchars((string) $card['title']) ?></h2>
      <?php endif; ?>
      <?php if (!empty($card['text'])): ?>
        <p class="muted"><?= htmlspecialchars((string) $card['text']) ?></p>
      <?php endif; ?>
      <?php if (!empty($card['href']) && !empty($card['action'])): ?>
        <a class="btn" href="<?= htmlspecialchars((string) $card['href']) ?>"><?= htmlspecialchars((string) $card['action']) ?></a>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <div class="card glass pizza-shop__menu">
    <h2><?= htmlspecialchars($sectionTitle) ?></h2>
    <?php if (!$inventory): ?>
      <p class="muted"><?= htmlspecialchars($emptyText) ?></p>
    <?php else: ?>
      <div class="pizza-menu-grid" role="list" aria-label="<?= htmlspecialchars($ariaLabel) ?>">
        <?php foreach ($inventory as $item): ?>
          <?php
            $stock = $item['stock'];
            $priceDisplay = number_format((float) $item['price'], 2);
            $isSoldOut = ($stock !== null && $stock <= 0);
            $description = (string) ($item['description'] ?? '');
          ?>
          <article class="pizza-item-card<?= $isSoldOut ? ' is-sold-out' : '' ?>" role="listitem">
            <figure>
              <div class="pizza-item-thumb">
                <img src="<?= htmlspecialchars($item['image'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>" loading="lazy" decoding="async">
              </div>
              <figcaption>
                <strong><?= htmlspecialchars($item['name']) ?></strong>
                <span class="muted"><?= $priceDisplay ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?></span>
                <?php if ($stock !== null): ?>
                  <span class="pizza-item-stock<?= $isSoldOut ? ' sold-out' : '' ?>">
                    <?= $isSoldOut ? 'Sold out' : 'Stock: '.htmlspecialchars((string) $stock) ?>
                  </span>
                <?php else: ?>
                  <span class="pizza-item-stock">Stock: plentiful</span>
                <?php endif; ?>
              </figcaption>
            </figure>
            <?php if ($description !== ''): ?>
              <p class="muted"><?= nl2br(htmlspecialchars($description)) ?></p>
            <?php endif; ?>
            <form method="post">
              <input type="hidden" name="action" value="buy">
              <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
              <label>
                <span class="muted">Qty</span>
                <input type="number" name="quantity" value="1" min="1" max="<?= $stock === null ? 99 : max(1, (int) $stock) ?>" <?= $isSoldOut ? 'disabled' : '' ?>>
              </label>
              <button class="btn" type="submit" <?= $isSoldOut ? 'disabled' : '' ?>><?= htmlspecialchars($submitLabel) ?></button>
            </form>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php
}
