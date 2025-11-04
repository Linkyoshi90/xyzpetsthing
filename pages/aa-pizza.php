<?php
require_login();
require_once __DIR__.'/../db.php';

$shopId = 4;
$shop = q('SELECT shop_id, shop_name FROM shops WHERE shop_id = ?', [$shopId])->fetch(PDO::FETCH_ASSOC);

function aa_pizza_find_item_image(string $name): string {
    $baseDir = __DIR__.'/../images/items';
    $fallback = 'pizzeria-placeholder.svg';
    $extensions = ['png', 'webp', 'jpg', 'jpeg', 'gif', 'svg'];

    $variants = [];
    $candidates = [$name];
    $lower = strtolower($name);
    $candidates[] = $lower;
    $noApos = str_replace("'", '', $name);
    $candidates[] = $noApos;
    $candidates[] = strtolower($noApos);

    foreach ($candidates as $candidate) {
        $candidate = trim($candidate);
        if ($candidate === '') {
            continue;
        }
        $withUnderscore = str_replace(' ', '_', $candidate);
        $withDash = str_replace(' ', '-', $candidate);
        $variants[] = $candidate;
        $variants[] = $withUnderscore;
        $variants[] = $withDash;
    }

    $variants[] = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
    $variants = array_values(array_unique(array_filter($variants, fn($v) => $v !== null && $v !== '')));

    foreach ($variants as $variant) {
        foreach ($extensions as $ext) {
            $fileName = $variant.'.'.$ext;
            $fullPath = $baseDir.'/'.$fileName;
            if (is_file($fullPath)) {
                return $fileName;
            }
        }
    }

    return $fallback;
}

$inventoryRows = q(
    "SELECT si.item_id, si.price, si.stock, i.item_name, i.base_price, i.item_description\n"
    ."FROM shop_inventory si\n"
    ."JOIN items i ON i.item_id = si.item_id\n"
    ."WHERE si.shop_id = ?\n"
    ."ORDER BY i.item_name",
    [$shopId]
)->fetchAll(PDO::FETCH_ASSOC);

$inventory = [];
foreach ($inventoryRows as $row) {
    $price = $row['price'];
    if ($price === null) {
        $price = $row['base_price'];
    }
    $price = (float) $price;
    $stock = $row['stock'];
    $stock = $stock === null ? null : (int) $stock;
    $imageFile = aa_pizza_find_item_image($row['item_name']);
    $inventory[] = [
        'item_id' => (int) $row['item_id'],
        'name' => $row['item_name'],
        'price' => $price,
        'stock' => $stock,
        'description' => $row['item_description'],
        'image' => 'images/items/'.rawurlencode($imageFile),
    ];
}

$inventoryById = [];
foreach ($inventory as $item) {
    $inventoryById[$item['item_id']] = $item;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
    header('Content-Type: application/json');
    $cartPayload = json_decode($_POST['cart'] ?? '[]', true);

    if (!is_array($cartPayload)) {
        echo json_encode(['ok' => false, 'error' => 'Invalid cart payload.']);
        exit;
    }

    if (!$inventoryById) {
        echo json_encode(['ok' => false, 'error' => 'This shop has no stock available.']);
        exit;
    }

    $orderItems = [];
    $total = 0.0;

    foreach ($cartPayload as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $itemId = isset($entry['item_id']) ? (int) $entry['item_id'] : 0;
        $quantity = isset($entry['quantity']) ? (int) $entry['quantity'] : 0;
        if ($itemId <= 0 || $quantity <= 0) {
            continue;
        }
        if (!isset($inventoryById[$itemId])) {
            echo json_encode(['ok' => false, 'error' => 'One of the items is no longer sold here.']);
            exit;
        }
        $item = $inventoryById[$itemId];
        $maxStock = $item['stock'];
        if ($maxStock !== null && $quantity > $maxStock) {
            echo json_encode([
                'ok' => false,
                'error' => sprintf('You can only order up to %d × %s.', $maxStock, $item['name']),
            ]);
            exit;
        }
        if (isset($orderItems[$itemId])) {
            $orderItems[$itemId]['quantity'] += $quantity;
        } else {
            $orderItems[$itemId] = [
                'item_id' => $itemId,
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $quantity,
            ];
        }
    }

    if (!$orderItems) {
        echo json_encode(['ok' => false, 'error' => 'Your cart is empty.']);
        exit;
    }

    foreach ($orderItems as &$orderItem) {
        $itemId = $orderItem['item_id'];
        $inventoryItem = $inventoryById[$itemId];
        $maxStock = $inventoryItem['stock'];
        if ($maxStock !== null && $orderItem['quantity'] > $maxStock) {
            $orderItem['quantity'] = $maxStock;
        }
        $lineTotal = $orderItem['quantity'] * $orderItem['price'];
        $orderItem['line_total'] = $lineTotal;
        $total += $lineTotal;
    }
    unset($orderItem);

    echo json_encode([
        'ok' => true,
        'message' => 'Order prepared! Please proceed to the counter to finalize payment.',
        'items' => array_values($orderItems),
        'total' => $total,
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
      cartStatus.textContent = `Only ${maxStock} × ${entry.name} available.`;
      entry.quantity = maxStock;
      cart.set(key, entry);
      updateCartUI();
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
      const response = await fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'checkout',
          cart: JSON.stringify(payload)
        })
      });

      if (!response.ok) {
        throw new Error(`Unexpected response: ${response.status}`);
      }

      const data = await response.json();
      if (!data.ok) {
        cartStatus.textContent = data.error || 'Could not process your order.';
        cartBuyBtn.disabled = false;
        return;
      }

      cartStatus.textContent = `${data.message} Total: ${formatPrice(data.total)} denars.`;
      cart.clear();
      updateCartUI();
    } catch (err) {
      console.error(err);
      cartStatus.textContent = 'A kitchen gremlin intercepted the order. Please try again.';
      cartBuyBtn.disabled = false;
    }
  });

  updateCartUI();
})();
</script>