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

function simple_shop_handle_checkout(int $shopId, array $inventory, array $labels = []): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || input_string($_POST['action'] ?? '', 20) !== 'checkout') {
        return;
    }

    header('Content-Type: application/json');

    $inventoryById = shop_inventory_indexed($inventory);
    if (!$inventoryById) {
        echo json_encode(shop_error_payload($labels['empty_shop'] ?? 'This shop has no stock available.', [
            'shop_id' => $shopId,
        ]));
        exit;
    }

    $cartPayload = shop_normalize_cart_payload($_POST['cart'] ?? []);
    if (!$cartPayload) {
        echo json_encode(shop_error_payload($labels['empty_cart'] ?? 'Your cart is empty.', [
            'received_cart' => $_POST['cart'] ?? null,
        ]));
        exit;
    }

    $orderItems = [];
    foreach ($cartPayload as $entry) {
        $itemId = $entry['item_id'];
        $quantity = $entry['quantity'];

        if (!isset($inventoryById[$itemId])) {
            echo json_encode(shop_error_payload($labels['missing_item'] ?? 'One of the items is no longer sold here.', [
                'item_id' => $itemId,
                'shop_id' => $shopId,
            ]));
            exit;
        }

        if (isset($orderItems[$itemId])) {
            $orderItems[$itemId]['quantity'] += $quantity;
        } else {
            $orderItems[$itemId] = [
                'item_id' => $itemId,
                'quantity' => $quantity,
            ];
        }

        $item = $inventoryById[$itemId];
        $stock = $item['stock'];
        if ($stock !== null && $orderItems[$itemId]['quantity'] > $stock) {
            echo json_encode(shop_error_payload(
                sprintf($labels['stock_limit'] ?? 'You can only buy up to %d x %s.', $stock, $item['name']),
                [
                    'item_id' => $itemId,
                    'requested' => $orderItems[$itemId]['quantity'],
                    'available' => $stock,
                ]
            ));
            exit;
        }
    }

    $user = current_user();
    $uid = isset($user['id']) ? (int) $user['id'] : 0;

    try {
        $checkout = shop_checkout($shopId, $uid, array_values($orderItems));
    } catch (Throwable $err) {
        $message = $err instanceof RuntimeException
            ? $err->getMessage()
            : ($labels['checkout_failed'] ?? 'We could not complete the purchase. Please try again.');

        echo json_encode(shop_error_payload($message, [
            'shop_id' => $shopId,
            'user_id' => $uid,
            'exception' => get_class($err),
        ]));
        exit;
    }

    echo json_encode([
        'ok' => true,
        'message' => $labels['checkout_success'] ?? 'Order prepared! Please proceed to the counter to finalize payment.',
        'items' => $checkout['items'],
        'total' => $checkout['total'],
        'stock' => $checkout['stock'],
    ]);
    exit;
}

/**
 * Handle one round of haggling (action=haggle). Responds with JSON and exits.
 *
 * On an accepted deal it performs the real checkout (grants items, decrements
 * stock) so the negotiation and the purchase are atomic and server-authoritative
 * - the client never gets to name the final price on its own.
 */
function simple_shop_handle_haggle(int $shopId, array $inventory, array $labels = []): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || input_string($_POST['action'] ?? '', 20) !== 'haggle') {
        return;
    }

    header('Content-Type: application/json');

    if (!$inventory) {
        echo json_encode(['result' => 'error', 'message' => $labels['empty_shop'] ?? 'This shop has no stock available.']);
        exit;
    }

    $cartPayload = shop_normalize_cart_payload($_POST['cart'] ?? []);
    $resolved = shop_resolve_cart($inventory, $cartPayload);
    if ($resolved['error'] !== null) {
        echo json_encode(['result' => 'error', 'message' => $resolved['error']]);
        exit;
    }

    $offer = (float) ($_POST['offer'] ?? 0);
    $round = (int) ($_POST['round'] ?? 1);
    $outcome = shop_haggle_round($resolved['total'], $offer, $round);

    $outcome['required'] = $resolved['total'];
    $outcome['offer'] = round(max(0.0, $offer), 2);
    $outcome['round'] = max(1, $round);

    if ($outcome['result'] === 'accept') {
        $user = current_user();
        $uid = isset($user['id']) ? (int) $user['id'] : 0;
        try {
            $checkout = shop_checkout($shopId, $uid, $resolved['items']);
        } catch (Throwable $err) {
            echo json_encode([
                'result' => 'error',
                'message' => $err instanceof RuntimeException
                    ? $err->getMessage()
                    : ($labels['checkout_failed'] ?? 'The deal fell through at the counter. Please try again.'),
            ]);
            exit;
        }

        $charged = round((float) ($outcome['charged'] ?? $resolved['total']), 2);
        $outcome['charged'] = $charged;
        $outcome['saved'] = round($resolved['total'] - $charged, 2);
        $outcome['stock'] = $checkout['stock'];
        $outcome['items'] = $checkout['items'];
    }

    echo json_encode($outcome);
    exit;
}

function simple_shop_handle_purchase(int $shopId, array $inventory, array $labels = []): ?array
{
    simple_shop_handle_haggle($shopId, $inventory, $labels);
    simple_shop_handle_checkout($shopId, $inventory, $labels);

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

function simple_shop_json_script_literal(array $payload): string
{
    return json_encode(
        $payload,
        JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE
        | JSON_HEX_TAG
        | JSON_HEX_APOS
        | JSON_HEX_AMP
        | JSON_HEX_QUOT
    ) ?: '[]';
}

function render_simple_shop_page(array $config): void
{
    $shop = $config['shop'] ?? ['shop_name' => $config['fallback_name'] ?? 'Shop'];
    $inventory = $config['inventory'] ?? [];
    $notice = $config['notice'] ?? null;
    $title = (string) ($shop['shop_name'] ?? ($config['fallback_name'] ?? 'Shop'));
    $intro = (string) ($config['intro'] ?? '');
    $sectionTitle = (string) ($config['section_title'] ?? 'Stock');
    $emptyText = (string) ($config['empty_text'] ?? 'Nothing is stocked here right now.');
    $ariaLabel = (string) ($config['aria_label'] ?? 'Available items');
    $submitLabel = (string) ($config['submit_label'] ?? 'Buy');
    $cartTitle = (string) ($config['cart_title'] ?? 'Your Basket');
    $cartHelp = (string) ($config['cart_help'] ?? 'Adjust quantities or remove items before you submit.');
    $cartEmpty = (string) ($config['cart_empty'] ?? 'Select an item to begin building your order.');
    $cartAriaLabel = (string) ($config['cart_aria_label'] ?? 'Shopping cart');
    $clearLabel = (string) ($config['clear_label'] ?? 'Clear Cart');
    $sendingText = (string) ($config['sending_text'] ?? 'Sending your order to the counter...');
    $itemSoldOutText = (string) ($config['sold_out_text'] ?? '%s is sold out today.');
    $stockLimitText = (string) ($config['client_stock_limit'] ?? '%s only has %s remaining.');
    $currencyLong = APP_CURRENCY_LONG_NAME;
    $currencyShort = APP_CURRENCY_SHORT_NAME;
    $rootId = 'simple-shop-'.preg_replace('/[^a-z0-9_-]+/i', '-', strtolower((string) ($shop['shop_id'] ?? $title)));
    $itemJson = array_map(static function (array $item): array {
        return [
            'item_id' => $item['item_id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'stock' => $item['stock'],
        ];
    }, $inventory);

    // Pick a shop clerk portrait for this region (null => emoji fallback).
    $clerk = isset($shop['region_id']) ? shop_pick_clerk((int) $shop['region_id']) : null;
    $clerkImage = $clerk['image'] ?? '';
    $clerkGender = $clerk['gender'] ?? '';
    ?>
<section
  id="<?= htmlspecialchars($rootId, ENT_QUOTES, 'UTF-8') ?>"
  class="pizza-shop"
  data-simple-shop
  data-sending-text="<?= htmlspecialchars($sendingText, ENT_QUOTES, 'UTF-8') ?>"
  data-sold-out-text="<?= htmlspecialchars($itemSoldOutText, ENT_QUOTES, 'UTF-8') ?>"
  data-stock-limit-text="<?= htmlspecialchars($stockLimitText, ENT_QUOTES, 'UTF-8') ?>"
  data-currency-long="<?= htmlspecialchars($currencyLong, ENT_QUOTES, 'UTF-8') ?>"
  data-currency-short="<?= htmlspecialchars($currencyShort, ENT_QUOTES, 'UTF-8') ?>"
  data-clerk-image="<?= htmlspecialchars($clerkImage, ENT_QUOTES, 'UTF-8') ?>"
  data-clerk-gender="<?= htmlspecialchars($clerkGender, ENT_QUOTES, 'UTF-8') ?>"
>
  <header class="pizza-shop__header">
    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if ($intro !== ''): ?>
      <p class="muted"><?= htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
  </header>

  <?php if ($notice): ?>
    <div class="card glass" role="status">
      <p class="muted"><strong><?= $notice['type'] === 'error' ? 'Could not complete purchase:' : 'Purchase complete:' ?></strong> <?= htmlspecialchars($notice['message'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  <?php endif; ?>

  <?php foreach (($config['info_cards'] ?? []) as $card): ?>
    <div class="card glass">
      <?php if (!empty($card['title'])): ?>
        <h2><?= htmlspecialchars((string) $card['title'], ENT_QUOTES, 'UTF-8') ?></h2>
      <?php endif; ?>
      <?php if (!empty($card['text'])): ?>
        <p class="muted"><?= htmlspecialchars((string) $card['text'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
      <?php if (!empty($card['href']) && !empty($card['action'])): ?>
        <a class="btn" href="<?= htmlspecialchars((string) $card['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $card['action'], ENT_QUOTES, 'UTF-8') ?></a>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <div class="pizza-shop__layout">
    <div class="card glass pizza-shop__menu">
      <h2><?= htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') ?></h2>
      <?php if (!$inventory): ?>
        <p class="muted"><?= htmlspecialchars($emptyText, ENT_QUOTES, 'UTF-8') ?></p>
      <?php else: ?>
        <div class="pizza-menu-grid" role="list" aria-label="<?= htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') ?>">
          <?php foreach ($inventory as $item): ?>
            <?php
              $stock = $item['stock'];
              $priceDisplay = number_format((float) $item['price'], 2);
              $isSoldOut = ($stock !== null && $stock <= 0);
            ?>
            <button
              type="button"
              class="pizza-item-card<?= $isSoldOut ? ' is-sold-out' : '' ?>"
              role="listitem"
              data-item-id="<?= (int) $item['item_id'] ?>"
              data-price="<?= htmlspecialchars((string) $item['price'], ENT_QUOTES, 'UTF-8') ?>"
              data-stock="<?= $stock === null ? '' : (int) $stock ?>"
              data-name="<?= htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') ?>"
              aria-pressed="false"
              <?= $isSoldOut ? 'disabled aria-disabled="true"' : '' ?>
            >
              <figure>
                <div class="pizza-item-thumb">
                  <img src="<?= htmlspecialchars((string) $item['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
                </div>
                <figcaption>
                  <strong><?= htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <span class="muted"><?= $priceDisplay ?> <?= htmlspecialchars($currencyLong, ENT_QUOTES, 'UTF-8') ?></span>
                  <?php if ($stock !== null): ?>
                    <span class="pizza-item-stock<?= $isSoldOut ? ' sold-out' : '' ?>">
                      <?= $isSoldOut ? 'Sold out' : 'Stock: '.htmlspecialchars((string) $stock, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  <?php else: ?>
                    <span class="pizza-item-stock">Stock: plentiful</span>
                  <?php endif; ?>
                </figcaption>
              </figure>
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <aside class="card glass pizza-cart" aria-label="<?= htmlspecialchars($cartAriaLabel, ENT_QUOTES, 'UTF-8') ?>">
      <div class="pizza-cart__header">
        <h2><?= htmlspecialchars($cartTitle, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="muted"><?= htmlspecialchars($cartHelp, ENT_QUOTES, 'UTF-8') ?></p>
      </div>
      <div class="pizza-cart__body">
        <p class="pizza-cart__empty" data-shop-cart-empty><?= htmlspecialchars($cartEmpty, ENT_QUOTES, 'UTF-8') ?></p>
        <ul class="pizza-cart__list" data-shop-cart-list></ul>
      </div>
      <footer class="pizza-cart__footer">
        <div class="pizza-cart__total">
          <span>Total</span>
          <strong data-shop-cart-total>0.00</strong>
        </div>
        <div class="pizza-cart__actions">
          <button type="button" class="btn ghost" data-shop-cart-clear><?= htmlspecialchars($clearLabel, ENT_QUOTES, 'UTF-8') ?></button>
          <button type="button" class="btn" data-shop-cart-buy disabled>Haggle &#129309;</button>
        </div>
        <p class="pizza-cart__status muted" data-shop-cart-status role="status" aria-live="polite"></p>
      </footer>
    </aside>
  </div>

  <!-- Haggle modal: the clerk negotiation runs entirely over fetch(); no reload. -->
  <div class="haggle-overlay" data-haggle-overlay hidden>
    <div class="haggle-box card glass" role="dialog" aria-modal="true" aria-label="Haggle with the shop clerk">
      <div class="haggle-keeper">
        <div class="haggle-keeper__face" data-haggle-face>
          <?php if ($clerkImage !== ''): ?>
            <img src="<?= htmlspecialchars($clerkImage, ENT_QUOTES, 'UTF-8') ?>" alt="Shop clerk" loading="lazy" decoding="async">
          <?php else: ?>
            <span class="haggle-keeper__emoji">&#129333;</span>
          <?php endif; ?>
        </div>
        <div class="haggle-keeper__line">
          <p class="haggle-keeper__who">The Shop Clerk</p>
          <p class="haggle-keeper__bubble" data-haggle-bubble>So, what's your offer for this little pile?</p>
        </div>
      </div>
      <div class="haggle-figures">
        <span>Asking: <strong data-haggle-asking>0.00</strong> <?= htmlspecialchars($currencyShort, ENT_QUOTES, 'UTF-8') ?></span>
        <span class="muted" data-haggle-hint></span>
      </div>
      <div class="haggle-offer-row">
        <input type="number" min="0" step="1" inputmode="numeric" placeholder="Your offer..." data-haggle-input>
        <button type="button" class="btn" data-haggle-offer>Offer</button>
      </div>
      <div class="haggle-actions">
        <button type="button" class="btn ghost" data-haggle-walk>Walk away</button>
      </div>
      <p class="haggle-round muted" data-haggle-round>Round 1</p>
    </div>
  </div>
</section>
<script>
(function() {
  const root = document.getElementById(<?= json_encode($rootId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>);
  if (!root) return;

  const inventory = <?= simple_shop_json_script_literal($itemJson) ?>;
  const itemMap = new Map(inventory.map((item) => [String(item.item_id), item]));
  const menuButtons = Array.from(root.querySelectorAll('.pizza-item-card'));
  const cartList = root.querySelector('[data-shop-cart-list]');
  const cartEmpty = root.querySelector('[data-shop-cart-empty]');
  const cartTotalEl = root.querySelector('[data-shop-cart-total]');
  const cartClearBtn = root.querySelector('[data-shop-cart-clear]');
  const cartBuyBtn = root.querySelector('[data-shop-cart-buy]');
  const cartStatus = root.querySelector('[data-shop-cart-status]');
  const currencyLongName = (window.appCurrency && window.appCurrency.longName) || root.dataset.currencyLong || <?= json_encode($currencyLong, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  const cart = new Map();

  function setStatus(message = '', context = null) {
    const parts = [];
    if (message) {
      parts.push(message);
    }
    if (context != null) {
      let detail = '';
      if (typeof context === 'string') {
        detail = context;
      } else if (typeof context === 'object') {
        try {
          detail = JSON.stringify(context, null, 2);
        } catch (err) {
          detail = String(context);
        }
      } else {
        detail = String(context);
      }

      if (detail) {
        parts.push('Details: ' + detail);
      }
    }

    cartStatus.textContent = parts.join('\n');
  }

  function formatPrice(value) {
    return Number(value || 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function formatTemplate(template, values) {
    let index = 0;
    return String(template || '').replace(/%s/g, () => String(values[index++] ?? ''));
  }

  function stockText(stock) {
    return stock == null ? 'plenty' : String(stock);
  }

  function updateMenuButtonStock(itemId, stock) {
    const key = String(itemId);
    const item = itemMap.get(key);
    const normalizedStock = stock == null ? null : Number(stock);

    if (item) {
      item.stock = normalizedStock == null || Number.isNaN(normalizedStock) ? null : normalizedStock;
    }

    const button = menuButtons.find((btn) => btn.dataset.itemId === key);
    if (!button) {
      return;
    }

    const stockLabel = button.querySelector('.pizza-item-stock');
    if (normalizedStock == null || Number.isNaN(normalizedStock)) {
      button.dataset.stock = '';
      button.disabled = false;
      button.classList.remove('is-sold-out');
      button.removeAttribute('aria-disabled');
      if (stockLabel) {
        stockLabel.textContent = 'Stock: plentiful';
        stockLabel.classList.remove('sold-out');
      }
      return;
    }

    button.dataset.stock = String(normalizedStock);
    const isSoldOut = normalizedStock <= 0;
    button.disabled = isSoldOut;
    button.classList.toggle('is-sold-out', isSoldOut);
    if (isSoldOut) {
      button.setAttribute('aria-disabled', 'true');
    } else {
      button.removeAttribute('aria-disabled');
    }

    if (stockLabel) {
      stockLabel.textContent = isSoldOut ? 'Sold out' : 'Stock: ' + normalizedStock;
      stockLabel.classList.toggle('sold-out', isSoldOut);
    }
  }

  function updateCartUI() {
    cartList.innerHTML = '';
    let total = 0;

    cartEmpty.hidden = cart.size !== 0;
    cartClearBtn.disabled = cart.size === 0;
    cartBuyBtn.disabled = cart.size === 0;

    for (const [itemId, entry] of cart.entries()) {
      const li = document.createElement('li');
      li.className = 'pizza-cart__item';
      li.dataset.itemId = itemId;

      const info = document.createElement('div');
      info.className = 'pizza-cart__item-info';

      const name = document.createElement('strong');
      name.textContent = entry.name;

      const price = document.createElement('span');
      price.className = 'muted';
      price.textContent = formatPrice(entry.price) + ' each';

      info.append(name, price);

      const controls = document.createElement('div');
      controls.className = 'pizza-cart__item-controls';

      const decrease = document.createElement('button');
      decrease.type = 'button';
      decrease.className = 'qty-btn';
      decrease.dataset.action = 'decrease';
      decrease.setAttribute('aria-label', 'Decrease quantity');
      decrease.textContent = '-';

      const quantity = document.createElement('span');
      quantity.className = 'qty-display';
      quantity.setAttribute('aria-live', 'polite');
      quantity.textContent = String(entry.quantity);

      const increase = document.createElement('button');
      increase.type = 'button';
      increase.className = 'qty-btn';
      increase.dataset.action = 'increase';
      increase.setAttribute('aria-label', 'Increase quantity');
      increase.textContent = '+';

      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'qty-remove';
      remove.dataset.action = 'remove';
      remove.setAttribute('aria-label', 'Remove ' + entry.name);
      remove.textContent = 'Remove';

      controls.append(decrease, quantity, increase, remove);
      li.append(info, controls);
      cartList.appendChild(li);
      total += entry.price * entry.quantity;
    }

    cartTotalEl.textContent = formatPrice(total);

    menuButtons.forEach((btn) => {
      const itemId = btn.dataset.itemId;
      const selected = cart.has(itemId);
      btn.classList.toggle('selected', selected);
      btn.setAttribute('aria-pressed', selected ? 'true' : 'false');
    });
  }

  function adjustCart(itemId, delta) {
    const key = String(itemId);
    const item = itemMap.get(key);
    if (!item) return;

    setStatus('');
    const existing = cart.get(key);
    const entry = existing || {
      name: item.name,
      price: Number(item.price || 0),
      quantity: 0,
      stock: item.stock
    };
    const maxStock = entry.stock == null ? Infinity : Number(entry.stock);
    const newQty = entry.quantity + delta;

    if (newQty <= 0) {
      cart.delete(key);
      updateCartUI();
      return;
    }

    if (newQty > maxStock) {
      setStatus(formatTemplate(root.dataset.stockLimitText, [entry.name, stockText(maxStock)]));
      return;
    }

    entry.quantity = newQty;
    cart.set(key, entry);
    updateCartUI();
  }

  menuButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const itemId = btn.dataset.itemId;
      if (!itemId) return;

      const item = itemMap.get(itemId);
      if (!item) return;

      if (btn.disabled || item.stock === 0) {
        setStatus(formatTemplate(root.dataset.soldOutText, [item.name]));
        return;
      }

      adjustCart(itemId, 1);
    });
  });

  cartList.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;

    const li = target.closest('.pizza-cart__item');
    if (!li) return;

    const itemId = li.dataset.itemId;
    const action = target.dataset.action;
    if (!itemId || !action) return;

    if (action === 'increase') {
      adjustCart(itemId, 1);
    } else if (action === 'decrease') {
      adjustCart(itemId, -1);
    } else if (action === 'remove') {
      setStatus('');
      cart.delete(itemId);
      updateCartUI();
    }
  });

  cartClearBtn.addEventListener('click', () => {
    setStatus('');
    cart.clear();
    updateCartUI();
  });

  // ---- Haggling -------------------------------------------------------
  // The "Buy" button opens a negotiation modal instead of an instant sale.
  // Each offer is a fetch() round; the clerk accepts, counters, or boots you.
  const currencyShortName = root.dataset.currencyShort || currencyLongName;
  const overlay = root.querySelector('[data-haggle-overlay]');
  const haggleBubble = root.querySelector('[data-haggle-bubble]');
  const haggleFace = root.querySelector('[data-haggle-face]');
  const haggleAsking = root.querySelector('[data-haggle-asking]');
  const haggleHint = root.querySelector('[data-haggle-hint]');
  const haggleInput = root.querySelector('[data-haggle-input]');
  const haggleOfferBtn = root.querySelector('[data-haggle-offer]');
  const haggleRoundEl = root.querySelector('[data-haggle-round]');
  const haggleWalkBtn = root.querySelector('[data-haggle-walk]');

  let haggleRound = 1;
  let haggleAskingTotal = 0;
  let haggleBusy = false;

  function cartTotalValue() {
    let total = 0;
    for (const entry of cart.values()) {
      total += Number(entry.price || 0) * entry.quantity;
    }
    return total;
  }

  function setBubble(state, text) {
    if (!haggleBubble) return;
    haggleBubble.className = 'haggle-keeper__bubble' + (state ? ' ' + state : '');
    haggleBubble.textContent = text;
  }

  function setFaceMood(mood) {
    if (!haggleFace) return;
    haggleFace.dataset.mood = mood || '';
  }

  function openHaggle() {
    if (cart.size === 0 || !overlay) return;
    haggleRound = 1;
    haggleAskingTotal = cartTotalValue();
    haggleAsking.textContent = formatPrice(haggleAskingTotal);
    haggleHint.textContent = '';
    setBubble('', "So, what's your offer for this little pile?");
    setFaceMood('');
    haggleRoundEl.textContent = 'Round 1';
    haggleInput.value = '';
    haggleInput.disabled = false;
    haggleOfferBtn.disabled = false;
    overlay.hidden = false;
    setTimeout(() => haggleInput.focus(), 50);
  }

  function closeHaggle() {
    if (overlay) overlay.hidden = true;
  }

  async function sendOffer() {
    if (haggleBusy) return;
    const offer = Number(haggleInput.value);
    if (haggleInput.value === '' || Number.isNaN(offer) || offer < 0) {
      setBubble('counter', 'Give me a real number, friend.');
      return;
    }

    haggleBusy = true;
    haggleOfferBtn.disabled = true;
    setBubble('', '...');

    const payload = Array.from(cart.entries()).map(([itemId, entry]) => ({
      item_id: Number(itemId),
      quantity: entry.quantity
    }));

    let data;
    try {
      const formData = new FormData();
      formData.append('action', 'haggle');
      formData.append('offer', String(offer));
      formData.append('round', String(haggleRound));
      payload.forEach((entry, index) => {
        formData.append('cart[' + index + '][item_id]', String(entry.item_id));
        formData.append('cart[' + index + '][quantity]', String(entry.quantity));
      });

      const response = await fetch(window.location.href, { method: 'POST', body: formData });
      if (!response.ok) {
        throw new Error('Unexpected response: ' + response.status);
      }
      data = await response.json();
    } catch (err) {
      console.error('Haggle request failed', { error: err, payload });
      setBubble('critfail', 'The clerk got distracted. Try that again.');
      haggleBusy = false;
      haggleOfferBtn.disabled = false;
      return;
    }

    haggleBusy = false;
    setBubble(data.result === 'error' ? 'counter' : data.result, data.message || '...');

    if (data.result === 'accept') {
      setFaceMood('happy');
      haggleHint.textContent = '';
      haggleInput.disabled = true;
      haggleOfferBtn.disabled = true;
      if (data.stock && typeof data.stock === 'object') {
        Object.entries(data.stock).forEach(([itemId, stockValue]) => updateMenuButtonStock(itemId, stockValue));
      }
      const saved = Number(data.saved || 0);
      haggleRoundEl.textContent = 'Deal struck for ' + formatPrice(data.charged) + ' ' + currencyShortName +
        (saved > 0 ? ' — saved ' + formatPrice(saved) + '!' : '');
      setStatus('Deal struck! You paid ' + formatPrice(data.charged) + ' ' + currencyShortName + ' for your basket.');
      cart.clear();
      updateCartUI();
      setTimeout(closeHaggle, 2600);
    } else if (data.result === 'critfail') {
      setFaceMood('angry');
      haggleInput.disabled = true;
      haggleOfferBtn.disabled = true;
      haggleRoundEl.textContent = 'Booted from the shop!';
      setStatus('The clerk threw you out. Cool off and come back later.');
      cart.clear();
      updateCartUI();
      setTimeout(closeHaggle, 2600);
    } else if (data.result === 'counter') {
      setFaceMood('cross');
      haggleRound += 1;
      haggleRoundEl.textContent = 'Round ' + haggleRound;
      if (data.npc_min) {
        haggleHint.textContent = 'Hint: try ≥ ' + formatPrice(data.npc_min) + ' ' + currencyShortName;
      }
      haggleOfferBtn.disabled = false;
      haggleInput.focus();
      haggleInput.select();
    } else {
      haggleOfferBtn.disabled = false;
    }
  }

  cartBuyBtn.addEventListener('click', openHaggle);
  if (haggleOfferBtn) haggleOfferBtn.addEventListener('click', sendOffer);
  if (haggleInput) haggleInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') sendOffer(); });
  if (haggleWalkBtn) haggleWalkBtn.addEventListener('click', closeHaggle);
  if (overlay) overlay.addEventListener('click', (e) => { if (e.target === overlay) closeHaggle(); });

  updateCartUI();
})();
</script>
<?php
}
