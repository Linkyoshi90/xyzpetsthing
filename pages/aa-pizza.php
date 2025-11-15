<?php
require_login();
require_once __DIR__.'/../lib/shops.php';

$shopId = 4;
$shop = shop_get($shopId) ?? ['shop_id' => $shopId, 'shop_name' => 'Pizzeria Sol Invicta'];

$inventory = shop_inventory($shopId);
$inventoryById = shop_inventory_indexed($inventory);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
    header('Content-Type: application/json');

    if (!$inventoryById) {
        echo json_encode(shop_error_payload('This shop has no stock available.', [
            'shop_id' => $shopId,
        ]));
        exit;
    }

    $cartPayload = shop_normalize_cart_payload($_POST['cart'] ?? []);

    if (!$cartPayload) {
        echo json_encode(shop_error_payload('Your cart is empty.', [
            'received_cart' => $_POST['cart'] ?? null,
        ]));
    }

    $user = current_user();
    $uid = isset($user['id']) ? (int) $user['id'] : 0;

    $orderItems = [];
    foreach ($cartPayload as $entry) {
        $itemId = $entry['item_id'];
        $quantity = $entry['quantity'];
        if (!isset($inventoryById[$itemId])) {
            echo json_encode(shop_error_payload('One of the items is no longer sold here.', [
                'item_id' => $itemId,
                'shop_id' => $shopId,
            ]));
            exit;
        }
        $item = $inventoryById[$itemId];
        if (isset($orderItems[$itemId])) {
            $orderItems[$itemId]['quantity'] += $quantity;
        } else {
            $orderItems[$itemId] = [
                'item_id' => $itemId,
                'quantity' => $quantity,
            ];
        }

        $maxStock = $item['stock'];
        if ($maxStock !== null && $orderItems[$itemId]['quantity'] > $maxStock) {
            echo json_encode(shop_error_payload(
                sprintf('You can only order up to %d × %s.', $maxStock, $item['name']),
                [
                    'item_id' => $itemId,
                    'requested' => $orderItems[$itemId]['quantity'],
                    'available' => $maxStock,
                ]
            ));
            exit;
        }
    }

    try {
        $checkout = shop_checkout($shopId, $uid, array_values($orderItems));
    } catch (Throwable $err) {
        $message = $err instanceof RuntimeException
            ? $err->getMessage()
            : 'We could not finalize your order. Please try again.';

    echo json_encode(shop_error_payload($message, [
            'shop_id' => $shopId,
            'user_id' => $uid,
            'exception' => get_class($err),
        ]));
        exit;
    }

    echo json_encode([
        'ok' => true,
        'message' => 'Order prepared! Please proceed to the counter to finalize payment.',
        'items' => $checkout['items'],
        'total' => $checkout['total'],
        'stock' => $checkout['stock'],
    ]);
    exit;
}
?>
<section class="pizza-shop">
  <header class="pizza-shop__header">
    <h1><?= htmlspecialchars($shop['shop_name'] ?? 'Pizzeria Sol Invicta') ?></h1>
    <p class="muted">A neighborhood institution where civic debates are settled over blistered crusts and citrus oils.</p>
  </header>
  <div class="pizza-shop__layout">
    <div class="card glass pizza-shop__menu">
      <h2>Menu</h2>
      <?php if (!$inventory): ?>
      <p class="muted">The ovens are cooling right now. Please check back soon.</p>
      <?php else: ?>
      <div class="pizza-menu-grid" role="list" aria-label="Available dishes">
        <?php foreach ($inventory as $item): ?>
        <?php
          $stock = $item['stock'];
          $priceDisplay = number_format($item['price'], 2);
          $stockLabel = $stock === null ? '∞' : (string) $stock;
          $isSoldOut = ($stock !== null && $stock <= 0);
        ?>
        <button
          type="button"
          class="pizza-item-card<?= $isSoldOut ? ' is-sold-out' : '' ?>"
          role="listitem"
          data-item-id="<?= (int) $item['item_id'] ?>"
          data-price="<?= htmlspecialchars((string) $item['price'], ENT_QUOTES) ?>"
          data-stock="<?= $stock === null ? '' : (int) $stock ?>"
          data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>"
          aria-pressed="false"
          <?= $isSoldOut ? 'disabled aria-disabled="true"' : '' ?>
        >
          <figure>
            <div class="pizza-item-thumb">
              <img src="<?= htmlspecialchars($item['image'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>" loading="lazy" decoding="async">
            </div>
            <figcaption>
              <strong><?= htmlspecialchars($item['name']) ?></strong>
              <span class="muted"><?= $priceDisplay ?> denars</span>
              <?php if ($stock !== null): ?>
              <span class="pizza-item-stock<?= $isSoldOut ? ' sold-out' : '' ?>">
                <?php if ($isSoldOut): ?>Sold out<?php else: ?>Stock: <?= $stockLabel ?><?php endif; ?>
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
    <aside class="card glass pizza-cart" aria-label="Shopping cart">
      <div class="pizza-cart__header">
        <h2>Your Order</h2>
        <p class="muted">Adjust quantities or remove dishes before you submit.</p>
      </div>
      <div class="pizza-cart__body">
        <p class="pizza-cart__empty" id="pizza-cart-empty">Select a dish to begin building your order.</p>
        <ul class="pizza-cart__list" id="pizza-cart-list"></ul>
      </div>
      <footer class="pizza-cart__footer">
        <div class="pizza-cart__total">
          <span>Total</span>
          <strong id="pizza-cart-total">0.00</strong>
        </div>
        <div class="pizza-cart__actions">
          <button type="button" class="btn ghost" id="pizza-cart-clear">Clear Cart</button>
          <button type="button" class="btn" id="pizza-cart-buy" disabled>Buy</button>
        </div>
        <p class="pizza-cart__status muted" id="pizza-cart-status" role="status" aria-live="polite"></p>
      </footer>
    </aside>
  </div>
</section>
<script>
(function() {
  const inventory = <?= json_encode(array_map(function($item) {
    return [
      'item_id' => $item['item_id'],
      'name' => $item['name'],
      'price' => $item['price'],
      'stock' => $item['stock'],
    ];
  }, $inventory), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  const itemMap = new Map(inventory.map(item => [String(item.item_id), item]));

  const menuButtons = Array.from(document.querySelectorAll('.pizza-item-card'));
  const cartList = document.getElementById('pizza-cart-list');
  const cartEmpty = document.getElementById('pizza-cart-empty');
  const cartTotalEl = document.getElementById('pizza-cart-total');
  const cartClearBtn = document.getElementById('pizza-cart-clear');
  const cartBuyBtn = document.getElementById('pizza-cart-buy');
  const cartStatus = document.getElementById('pizza-cart-status');

  const cart = new Map();

  const formatPrice = (value) => {
    return Number(value || 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  };

  function updateMenuButtonStock(itemId, stock) {
    const key = String(itemId);
    const item = itemMap.get(key);
    if (item) {
      item.stock = stock == null ? null : Number(stock);
    }

    const button = menuButtons.find(btn => btn.dataset.itemId === key);
    if (!button) {
      return;
    }

    const normalizedStock = stock == null ? null : Number(stock);
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
      if (isSoldOut) {
        stockLabel.textContent = 'Sold out';
        stockLabel.classList.add('sold-out');
      } else {
        stockLabel.textContent = `Stock: ${normalizedStock}`;
        stockLabel.classList.remove('sold-out');
      }
    }
  }

  function updateCartUI() {
    cartList.innerHTML = '';
    let total = 0;

    if (cart.size === 0) {
      cartEmpty.hidden = false;
      cartBuyBtn.disabled = true;
      cartClearBtn.disabled = true;
      cartTotalEl.textContent = '0.00';
      menuButtons.forEach(btn => btn.classList.remove('selected'));
      cartStatus.textContent = '';
      return;
    }

    cartEmpty.hidden = true;
    cartClearBtn.disabled = false;
    cartBuyBtn.disabled = false;

    for (const [itemId, entry] of cart.entries()) {
      const li = document.createElement('li');
      li.className = 'pizza-cart__item';
      li.dataset.itemId = itemId;

      const info = document.createElement('div');
      info.className = 'pizza-cart__item-info';
      info.innerHTML = `<strong>${entry.name}</strong><span class="muted">${formatPrice(entry.price)} each</span>`;

      const controls = document.createElement('div');
      controls.className = 'pizza-cart__item-controls';
      controls.innerHTML = `
        <button type="button" class="qty-btn" data-action="decrease" aria-label="Decrease quantity">−</button>
        <span class="qty-display" aria-live="polite">${entry.quantity}</span>
        <button type="button" class="qty-btn" data-action="increase" aria-label="Increase quantity">+</button>
        <button type="button" class="qty-remove" data-action="remove" aria-label="Remove ${entry.name}">Remove</button>
      `;

      li.append(info, controls);
      cartList.appendChild(li);
      total += entry.price * entry.quantity;
    }

    cartTotalEl.textContent = formatPrice(total);

    menuButtons.forEach(btn => {
      const itemId = btn.dataset.itemId;
      btn.classList.toggle('selected', cart.has(itemId));
      btn.setAttribute('aria-pressed', cart.has(itemId) ? 'true' : 'false');
    });
  }

  function adjustCart(itemId, delta) {
    const key = String(itemId);
    const item = itemMap.get(key);
    if (!item) return;

    cartStatus.textContent = '';
    const entry = cart.get(key) || { name: item.name, price: item.price, quantity: 0, stock: item.stock };
    const maxStock = entry.stock == null ? Infinity : entry.stock;
    const newQty = entry.quantity + delta;

    if (newQty <= 0) {
      cart.delete(key);
      updateCartUI();
      return;
    }

    if (newQty > maxStock) {
      cartStatus.textContent = `${entry.name} only has ${maxStock === Infinity ? 'plenty' : maxStock} remaining.`;
      return;
    }

    entry.quantity = newQty;
    cart.set(key, entry);
    updateCartUI();
  }

  menuButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const itemId = btn.dataset.itemId;
      if (!itemId) return;
      if (btn.disabled) {
        const item = itemMap.get(itemId);
        if (item) {
          cartStatus.textContent = `${item.name} is sold out today.`;
        }
        return;
      }
      const existing = cart.get(itemId);
      if (existing) {
        adjustCart(itemId, 1);
        return;
      }
      const item = itemMap.get(itemId);
      if (!item) return;
      if (item.stock === 0) {
        cartStatus.textContent = `${item.name} is sold out today.`;
        return;
      }
      const stock = item.stock == null ? Infinity : item.stock;
      cartStatus.textContent = '';
      cart.set(itemId, {
        name: item.name,
        price: item.price,
        quantity: stock === Infinity ? 1 : Math.min(1, stock),
        stock: item.stock
      });
      updateCartUI();
    });
  });

  cartList.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    const li = target.closest('.pizza-cart__item');
    if (!li) return;
    const itemId = li.dataset.itemId;
    if (!itemId) return;

    const action = target.dataset.action;
    if (!action) return;

    switch (action) {
      case 'increase':
        adjustCart(itemId, 1);
        break;
      case 'decrease':
        adjustCart(itemId, -1);
        break;
      case 'remove':
        cartStatus.textContent = '';
        cart.delete(itemId);
        updateCartUI();
        break;
    }
  });

  cartClearBtn.addEventListener('click', () => {
    cartStatus.textContent = '';
    cart.clear();
    updateCartUI();
  });

  cartBuyBtn.addEventListener('click', async () => {
    if (cart.size === 0) return;
    const payload = Array.from(cart.entries()).map(([itemId, entry]) => ({
      item_id: Number(itemId),
      quantity: entry.quantity
    }));

    cartBuyBtn.disabled = true;
    cartStatus.textContent = 'Sending your order to the kitchen…';

    try {
      const formData = new FormData();
      formData.append('action', 'checkout');
      payload.forEach((entry, index) => {
        formData.append(`cart[${index}][item_id]`, String(entry.item_id));
        formData.append(`cart[${index}][quantity]`, String(entry.quantity));
      });
      const response = await fetch('', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Unexpected response: ${response.status}`);
      }

      const data = await response.json();
      if (!data.ok) {
          console.error('Checkout error', {
          message: data.error,
          context: data.context || null,
          payload
        });
        cartStatus.textContent = data.error || 'Could not process your order.';
        cartBuyBtn.disabled = false;
        return;
      }

      if (data.stock && typeof data.stock === 'object') {
        Object.entries(data.stock).forEach(([itemId, stockValue]) => {
          updateMenuButtonStock(itemId, stockValue);
        });
      }

      cartStatus.textContent = `${data.message} Total: ${formatPrice(data.total)} denars.`;
      cart.clear();
      updateCartUI();
    } catch (err) {
      console.error('Checkout request failed', {
        error: err,
        payload
      });
      let message = 'A kitchen gremlin intercepted the order. Please try again.';
      if (err instanceof Error && err.message) {
        message = err.message;
      }
      cartStatus.textContent = message;
      cartBuyBtn.disabled = false;
    }
  });

  updateCartUI();
})();
</script>