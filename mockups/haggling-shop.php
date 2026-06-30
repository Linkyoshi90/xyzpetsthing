<?php
/**
 * Haggling Shop — standalone mockup
 * ---------------------------------
 * A Neopets-style haggling system that never hard-refreshes the page.
 *
 * IMPORTANT: This mockup does NOT touch the live web database. It reads its
 * data straight from the committed SQL dumps in ../sql/ (items, shops,
 * shop_inventory), so you can run it locally on WAMP without a DB connection.
 *
 *   Browse to:  http://localhost/mockups/haggling-shop.php
 *
 * The haggle "loop" runs over fetch()/JSON — pick items, name your price, and
 * the shopkeeper either takes it, counters, or throws you out. No page reload.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/* -------------------------------------------------------------------------
 * 1. SQL dump reader (no database involved)
 * ---------------------------------------------------------------------- */

const SQL_DIR = __DIR__ . '/../sql';
const CURRENCY = 'Dosh';

/**
 * Parse every `INSERT INTO `table` (...) VALUES (...),(...);` block in a dump
 * into an array of associative rows. Handles quoted strings, backslash and
 * doubled-quote escapes, and NULL — enough for the phpMyAdmin dumps we ship.
 */
function sql_rows(string $file, string $table): array
{
    $path = SQL_DIR . '/' . $file;
    if (!is_file($path)) {
        return [];
    }
    $sql = file_get_contents($path);
    if ($sql === false) {
        return [];
    }

    $rows = [];
    $needle = 'INSERT INTO `' . $table . '`';
    $offset = 0;

    while (($pos = strpos($sql, $needle, $offset)) !== false) {
        // Column list: the (...) directly after the table name.
        $colStart = strpos($sql, '(', $pos);
        $colEnd = strpos($sql, ')', $colStart);
        $colRaw = substr($sql, $colStart + 1, $colEnd - $colStart - 1);
        $cols = array_map(static fn($c) => trim($c, " `\t\r\n"), explode(',', $colRaw));

        // Values start after the VALUES keyword that follows the column list.
        $valuesKw = stripos($sql, 'VALUES', $colEnd);
        [$tuples, $endPos] = sql_scan_tuples($sql, $valuesKw + 6);

        foreach ($tuples as $tuple) {
            if (count($tuple) === count($cols)) {
                $rows[] = array_combine($cols, $tuple);
            }
        }
        $offset = $endPos;
    }

    return $rows;
}

/**
 * Character scanner: from $start, read `(v,v,...),(...)` tuples until the
 * statement-terminating `;`. Returns [list-of-tuples, position-after-`;`].
 * Each tuple is a list of PHP scalars (string, int/float as string, or null).
 */
function sql_scan_tuples(string $sql, int $start): array
{
    $len = strlen($sql);
    $i = $start;
    $tuples = [];

    while ($i < $len) {
        $ch = $sql[$i];
        if ($ch === ';') {
            $i++;
            break;
        }
        if ($ch === '(') {
            [$tuple, $i] = sql_scan_one_tuple($sql, $i + 1);
            $tuples[] = $tuple;
            continue;
        }
        $i++; // whitespace or comma between tuples
    }

    return [$tuples, $i];
}

/** Read a single `v, v, v)` tuple starting just after its opening paren. */
function sql_scan_one_tuple(string $sql, int $i): array
{
    $len = strlen($sql);
    $values = [];
    $buf = '';
    $inStr = false;
    $hasContent = false; // distinguishes NULL from empty string

    while ($i < $len) {
        $ch = $sql[$i];

        if ($inStr) {
            if ($ch === '\\' && $i + 1 < $len) {        // backslash escape
                $next = $sql[$i + 1];
                $map = ['n' => "\n", 't' => "\t", 'r' => "\r", '0' => "\0"];
                $buf .= $map[$next] ?? $next;
                $i += 2;
                continue;
            }
            if ($ch === "'" && $i + 1 < $len && $sql[$i + 1] === "'") { // '' escape
                $buf .= "'";
                $i += 2;
                continue;
            }
            if ($ch === "'") {                            // end of string
                $inStr = false;
                $i++;
                continue;
            }
            $buf .= $ch;
            $i++;
            continue;
        }

        if ($ch === "'") {            // start of string
            $inStr = true;
            $hasContent = true;
            $buf = '';
            $i++;
            continue;
        }
        if ($ch === ',' || $ch === ')') {
            $token = trim($buf);
            if (!$hasContent && strcasecmp($token, 'NULL') === 0) {
                $values[] = null;
            } elseif (!$hasContent && $token === '') {
                $values[] = null;
            } else {
                // numeric or already-decoded string
                $values[] = $hasContent ? $buf : $token;
            }
            $buf = '';
            $hasContent = false;
            $i++;
            if ($ch === ')') {
                break;
            }
            continue;
        }

        $buf .= $ch;
        $i++;
    }

    return [$values, $i];
}

/* -------------------------------------------------------------------------
 * 2. Build the in-memory catalog from the dumps
 * ---------------------------------------------------------------------- */

function load_catalog(): array
{
    $items = [];
    foreach (sql_rows('items.sql', 'items') as $r) {
        $items[(int) $r['item_id']] = [
            'name' => (string) $r['item_name'],
            'base_price' => $r['base_price'] === null ? null : (float) $r['base_price'],
            'description' => (string) ($r['item_description'] ?? ''),
        ];
    }

    $shops = [];
    foreach (sql_rows('shops.sql', 'shops') as $r) {
        $shops[(int) $r['shop_id']] = [
            'shop_id' => (int) $r['shop_id'],
            'name' => (string) $r['shop_name'],
            'inventory' => [],
        ];
    }

    foreach (sql_rows('shop_inventory.sql', 'shop_inventory') as $r) {
        $shopId = (int) $r['shop_id'];
        $itemId = (int) $r['item_id'];
        if (!isset($shops[$shopId]) || !isset($items[$itemId])) {
            continue;
        }
        $price = $r['price'] !== null ? (float) $r['price'] : $items[$itemId]['base_price'];
        if ($price === null || $price <= 0) {
            continue; // priceless items can't be haggled over
        }
        $shops[$shopId]['inventory'][$itemId] = [
            'item_id' => $itemId,
            'name' => $items[$itemId]['name'],
            'price' => $price,
            'stock' => $r['stock'] === null ? null : (int) $r['stock'],
            'description' => $items[$itemId]['description'],
            'image' => find_item_image($items[$itemId]['name']),
        ];
    }

    // Keep only shops that actually have haggleable stock.
    return array_filter($shops, static fn($s) => !empty($s['inventory']));
}

/** Mirror of lib/shops.php image lookup, standalone (no DB include). */
function find_item_image(string $name): string
{
    $dir = __DIR__ . '/../images/items';
    $exts = ['png', 'webp', 'jpg', 'jpeg', 'gif', 'svg'];
    $bases = [];
    foreach ([$name, strtolower($name), str_replace("'", '', $name), strtolower(str_replace("'", '', $name))] as $c) {
        $c = trim($c);
        if ($c === '') {
            continue;
        }
        $bases[] = $c;
        $bases[] = str_replace(' ', '_', $c);
        $bases[] = str_replace(' ', '-', $c);
    }
    $bases[] = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
    foreach (array_unique($bases) as $b) {
        foreach ($exts as $ext) {
            if (is_file("$dir/$b.$ext")) {
                return '../images/items/' . rawurlencode("$b.$ext");
            }
        }
    }
    return '../images/items/pizzeria-placeholder.svg';
}

/* -------------------------------------------------------------------------
 * 3. The haggling engine
 * ---------------------------------------------------------------------- */

/**
 * Resolve a cart payload (item_id => qty) against a shop's real prices and
 * return the authoritative asking total. Computed server-side so the client
 * can't lie about what things cost.
 */
function cart_total(array $shop, array $cart): array
{
    $total = 0.0;
    $lines = [];
    foreach ($cart as $itemId => $qty) {
        $itemId = (int) $itemId;
        $qty = max(1, (int) $qty);
        if (!isset($shop['inventory'][$itemId])) {
            continue;
        }
        $item = $shop['inventory'][$itemId];
        $total += $item['price'] * $qty;
        $lines[] = ['name' => $item['name'], 'qty' => $qty, 'price' => $item['price']];
    }
    return ['total' => round($total, 2), 'lines' => $lines];
}

/**
 * Run ONE haggle round.
 *
 * @param float $required  the shop's asking price for the cart
 * @param float $offer     the player's perceived value
 * @param int   $round     1-based round counter (NPC softens as it climbs)
 * @return array  result payload for the client
 */
function haggle_round(float $required, float $offer, int $round): array
{
    $offer = max(0.0, round($offer, 2));

    // (4) At or above the asking price → instant yes.
    if ($offer >= $required) {
        return [
            'result' => 'accept',
            'charged' => $required, // never overpay
            'message' => pick([
                "Pleasure doing business with you!",
                "A fair price! It's yours.",
                "Sold! You drive a generous bargain.",
            ]),
        ];
    }

    // How far below asking is the offer? 0 = at price, 1 = offering nothing.
    $gap = ($required - $offer) / $required;

    // The shopkeeper grows more flexible each round it stays at the table.
    $roundBonus = min(0.10 * ($round - 1), 0.30);

    // (5,6) Acceptance chance falls as the gap widens; small offers ≈ no chance.
    $acceptChance = clamp(1.0 - ($gap * 1.7) + $roundBonus, 0.0, 0.95);

    // (12) Critical-fail (insult) chance only bites once you lowball hard.
    $critChance = clamp(($gap - 0.45) * 0.9, 0.0, 0.65);

    // (7) Randomizer goes wroom.
    $roll = mt_rand() / mt_getrandmax();

    // (8) Above the acceptance threshold → take the player's number.
    if ($roll < $acceptChance) {
        return [
            'result' => 'accept',
            'charged' => $offer,
            'message' => pick([
                "Hnng… fine. Robbery, but FINE. Take it.",
                "You're killing me here. Deal.",
                "Alright, alright — sold, before I change my mind.",
            ]),
        ];
    }

    // (12) Critical fail → booted from the shop.
    if ($roll > 1.0 - $critChance) {
        return [
            'result' => 'critfail',
            'message' => pick([
                "This is an outrage! Get out, you thief!",
                "GET OUT. And don't come back, you swindler!",
                "How DARE you insult my wares?! Out! OUT!",
            ]),
        ];
    }

    // (9,10) Otherwise the keeper counters with a floor it claims it can't pass.
    // The stated minimum drifts from near-asking down toward an absolute floor
    // as rounds go on, giving the player a moving target to chase.
    $absoluteFloor = $required * 0.55;
    $startMin = $required * 0.92;
    $npcMin = max($absoluteFloor, $startMin - ($startMin - $absoluteFloor) * min(($round - 1) / 4, 1.0));
    $npcMin = max($npcMin, $offer + 0.01); // always ask for more than was offered
    $npcMin = round($npcMin, 2);

    return [
        'result' => 'counter',
        'npc_min' => $npcMin,
        'message' => sprintf(
            pick([
                "Come on, I've got mouths to feed. I won't go below %s %s!",
                "Be reasonable! %s %s is the lowest I'll stomach.",
                "You wound me. Not a coin under %s %s, and that's mercy.",
            ]),
            number_format($npcMin, 2),
            CURRENCY
        ),
    ];
}

function clamp(float $v, float $lo, float $hi): float
{
    return max($lo, min($hi, $v));
}

function pick(array $opts): string
{
    return $opts[array_rand($opts)];
}

/* -------------------------------------------------------------------------
 * 4. AJAX endpoint — one haggle round, JSON in / JSON out (no page reload)
 * ---------------------------------------------------------------------- */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'haggle') {
    header('Content-Type: application/json');

    $catalog = load_catalog();
    $shopId = (int) ($_POST['shop_id'] ?? 0);
    $offer = (float) ($_POST['offer'] ?? 0);
    $round = max(1, (int) ($_POST['round'] ?? 1));
    $cart = json_decode($_POST['cart'] ?? '[]', true) ?: [];

    if (!isset($catalog[$shopId])) {
        echo json_encode(['result' => 'error', 'message' => 'Unknown shop.']);
        exit;
    }

    $resolved = cart_total($catalog[$shopId], $cart);
    if ($resolved['total'] <= 0) {
        echo json_encode(['result' => 'error', 'message' => 'Your basket is empty.']);
        exit;
    }

    $outcome = haggle_round($resolved['total'], $offer, $round);
    $outcome['required'] = $resolved['total'];
    $outcome['offer'] = round($offer, 2);
    $outcome['round'] = $round;
    echo json_encode($outcome);
    exit;
}

/* -------------------------------------------------------------------------
 * 5. Page render
 * ---------------------------------------------------------------------- */

$catalog = load_catalog();
$shopIds = array_keys($catalog);
$activeShopId = (int) ($_GET['shop'] ?? ($shopIds[0] ?? 0));
if (!isset($catalog[$activeShopId])) {
    $activeShopId = $shopIds[0] ?? 0;
}
$activeShop = $catalog[$activeShopId] ?? ['name' => 'No shops', 'inventory' => []];

$jsItems = [];
foreach ($activeShop['inventory'] as $item) {
    $jsItems[] = [
        'item_id' => $item['item_id'],
        'name' => $item['name'],
        'price' => $item['price'],
        'stock' => $item['stock'],
        'image' => $item['image'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Haggling Shop — Mockup</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { padding: 1.5rem; max-width: 1100px; margin: 0 auto; }
  .haggle-banner { margin-bottom: 1rem; }
  .haggle-banner code { background: rgba(0,0,0,.25); padding: .1rem .35rem; border-radius: .25rem; }
  .shop-picker { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; margin: .75rem 0 1.25rem; }
  .shop-picker select { padding: .4rem .6rem; border-radius: .5rem; }

  /* Haggle modal */
  .haggle-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.6);
    display: none; align-items: center; justify-content: center; z-index: 50; padding: 1rem;
  }
  .haggle-overlay.open { display: flex; }
  .haggle-box {
    width: min(460px, 100%); border-radius: 1rem; padding: 1.25rem 1.4rem;
    background: var(--card, #1c2030); color: var(--text, #eaeef7);
    box-shadow: 0 20px 60px rgba(0,0,0,.5); border: 1px solid rgba(255,255,255,.08);
  }
  .keeper { display: flex; gap: .75rem; align-items: flex-start; margin-bottom: 1rem; }
  .keeper-face { font-size: 2.6rem; line-height: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,.4)); }
  .keeper-line { flex: 1; }
  .keeper-line .who { font-weight: 700; margin: 0 0 .2rem; }
  .keeper-bubble {
    margin: 0; padding: .6rem .8rem; border-radius: .25rem .75rem .75rem .75rem;
    background: rgba(255,255,255,.06); min-height: 2.5rem; transition: background .2s;
  }
  .keeper-bubble.accept { background: rgba(74,222,128,.16); }
  .keeper-bubble.counter { background: rgba(250,204,21,.14); }
  .keeper-bubble.critfail { background: rgba(248,113,113,.18); }
  .haggle-figures { display: flex; justify-content: space-between; font-size: .9rem; margin: .75rem 0; gap: 1rem; }
  .haggle-figures .asking { opacity: .85; }
  .haggle-offer-row { display: flex; gap: .5rem; align-items: stretch; }
  .haggle-offer-row input {
    flex: 1; padding: .55rem .7rem; border-radius: .5rem; font-size: 1.05rem;
    border: 1px solid rgba(255,255,255,.15); background: rgba(0,0,0,.25); color: inherit;
  }
  .haggle-actions { display: flex; gap: .5rem; margin-top: .9rem; }
  .haggle-actions .btn { flex: 1; }
  .round-pill { font-size: .75rem; opacity: .6; text-align: center; margin-top: .6rem; }
  .selectable { cursor: pointer; }
  .pizza-item-card.selected { outline: 2px solid var(--accent, #7c9cff); outline-offset: 2px; }
</style>
</head>
<body>

<div class="card glass haggle-banner">
  <h1 style="margin:.2rem 0;">🪙 The Haggler's Counter <span class="muted" style="font-size:1rem;">— live mockup</span></h1>
  <p class="muted" style="margin:.3rem 0 0;">
    Reads items, shops &amp; prices straight from the <code>sql/</code> dumps — <strong>no database</strong>.
    Pick a basket, hit <em>Haggle</em>, and name your price. The whole back-and-forth runs over fetch(); the page never reloads.
  </p>
</div>

<form class="shop-picker" method="get">
  <label for="shop"><strong>Shop:</strong></label>
  <select id="shop" name="shop" onchange="this.form.submit()">
    <?php foreach ($catalog as $sid => $s): ?>
      <option value="<?= $sid ?>" <?= $sid === $activeShopId ? 'selected' : '' ?>>
        <?= htmlspecialchars($s['name']) ?> (<?= count($s['inventory']) ?> items)
      </option>
    <?php endforeach; ?>
  </select>
  <span class="muted">Only shops with priced, haggleable stock are listed.</span>
</form>

<div class="pizza-shop__layout">
  <div class="card glass pizza-shop__menu">
    <h2><?= htmlspecialchars($activeShop['name']) ?> — Stock</h2>
    <div class="pizza-menu-grid" role="list" aria-label="Items">
      <?php foreach ($activeShop['inventory'] as $item): ?>
        <button type="button" class="pizza-item-card selectable" data-item-id="<?= $item['item_id'] ?>"
                data-price="<?= $item['price'] ?>" data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>">
          <figure>
            <div class="pizza-item-thumb">
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>" loading="lazy">
            </div>
            <figcaption>
              <strong><?= htmlspecialchars($item['name']) ?></strong>
              <span class="muted"><?= number_format($item['price'], 2) ?> <?= CURRENCY ?></span>
            </figcaption>
          </figure>
        </button>
      <?php endforeach; ?>
    </div>
  </div>

  <aside class="card glass pizza-cart" aria-label="Basket">
    <div class="pizza-cart__header">
      <h2>Your Basket</h2>
      <p class="muted">Click items to add them, then haggle for the lot.</p>
    </div>
    <div class="pizza-cart__body">
      <p class="pizza-cart__empty" data-cart-empty>Select an item to begin.</p>
      <ul class="pizza-cart__list" data-cart-list></ul>
    </div>
    <footer class="pizza-cart__footer">
      <div class="pizza-cart__total"><span>Asking total</span><strong data-cart-total>0.00</strong></div>
      <div class="pizza-cart__actions">
        <button type="button" class="btn ghost" data-cart-clear>Clear</button>
        <button type="button" class="btn" data-cart-haggle disabled>Haggle 🤝</button>
      </div>
    </footer>
  </aside>
</div>

<!-- Haggle modal -->
<div class="haggle-overlay" data-haggle-overlay>
  <div class="haggle-box" role="dialog" aria-modal="true" aria-label="Haggle with the shopkeeper">
    <div class="keeper">
      <div class="keeper-face" data-keeper-face>🧔‍♂️</div>
      <div class="keeper-line">
        <p class="who">The Shopkeeper</p>
        <p class="keeper-bubble" data-keeper-bubble>So, what's your offer for this little pile?</p>
      </div>
    </div>
    <div class="haggle-figures">
      <span class="asking">They're asking: <strong data-asking>0.00</strong> <?= CURRENCY ?></span>
      <span class="floor muted" data-floor-hint></span>
    </div>
    <div class="haggle-offer-row">
      <input type="number" min="0" step="1" inputmode="numeric" placeholder="Your offer…" data-offer-input>
      <button type="button" class="btn" data-offer-send>Offer</button>
    </div>
    <div class="haggle-actions">
      <button type="button" class="btn ghost" data-haggle-walk>Walk away</button>
    </div>
    <p class="round-pill" data-round-pill>Round 1</p>
  </div>
</div>

<script>
const SHOP_ID = <?= (int) $activeShopId ?>;
const ITEMS = <?= json_encode($jsItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const CURRENCY = <?= json_encode(CURRENCY) ?>;
const itemMap = new Map(ITEMS.map(i => [String(i.item_id), i]));
const cart = new Map(); // id -> qty

const $ = sel => document.querySelector(sel);
const cartList = $('[data-cart-list]');
const cartEmpty = $('[data-cart-empty]');
const cartTotal = $('[data-cart-total]');
const haggleBtn = $('[data-cart-haggle]');

const fmt = v => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
function cartSum() {
  let t = 0;
  for (const [id, qty] of cart) t += Number(itemMap.get(id).price) * qty;
  return t;
}

function renderCart() {
  cartList.innerHTML = '';
  cartEmpty.hidden = cart.size > 0;
  haggleBtn.disabled = cart.size === 0;
  for (const [id, qty] of cart) {
    const it = itemMap.get(id);
    const li = document.createElement('li');
    li.className = 'pizza-cart__item';
    li.innerHTML = `<div class="pizza-cart__item-info"><strong>${it.name}</strong>
        <span class="muted">${fmt(it.price)} each</span></div>
      <div class="pizza-cart__item-controls">
        <button type="button" class="qty-btn" data-act="dec" data-id="${id}">−</button>
        <span class="qty-display">${qty}</span>
        <button type="button" class="qty-btn" data-act="inc" data-id="${id}">+</button>
        <button type="button" class="qty-remove" data-act="rm" data-id="${id}">Remove</button>
      </div>`;
    cartList.appendChild(li);
  }
  cartTotal.textContent = fmt(cartSum());
  document.querySelectorAll('.pizza-item-card').forEach(b =>
    b.classList.toggle('selected', cart.has(b.dataset.itemId)));
}

document.querySelectorAll('.pizza-item-card').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.itemId;
    cart.set(id, (cart.get(id) || 0) + 1);
    renderCart();
  });
});

cartList.addEventListener('click', e => {
  const b = e.target.closest('button'); if (!b) return;
  const id = b.dataset.id, act = b.dataset.act;
  if (act === 'inc') cart.set(id, cart.get(id) + 1);
  else if (act === 'dec') { const n = cart.get(id) - 1; n <= 0 ? cart.delete(id) : cart.set(id, n); }
  else if (act === 'rm') cart.delete(id);
  renderCart();
});

$('[data-cart-clear]').addEventListener('click', () => { cart.clear(); renderCart(); });

/* ---- Haggle modal flow ---------------------------------------------- */
const overlay = $('[data-haggle-overlay]');
const bubble = $('[data-keeper-bubble]');
const face = $('[data-keeper-face]');
const askingEl = $('[data-asking]');
const floorHint = $('[data-floor-hint]');
const offerInput = $('[data-offer-input]');
const offerSend = $('[data-offer-send]');
const roundPill = $('[data-round-pill]');
let round = 1;
let asking = 0;
let busy = false;

function openHaggle() {
  if (cart.size === 0) return;
  round = 1;
  asking = cartSum();
  askingEl.textContent = fmt(asking);
  floorHint.textContent = '';
  bubble.className = 'keeper-bubble';
  bubble.textContent = "So, what's your offer for this little pile?";
  face.textContent = '🧔‍♂️';
  roundPill.textContent = 'Round 1';
  offerInput.value = '';
  offerInput.disabled = false;
  offerSend.disabled = false;
  overlay.classList.add('open');
  setTimeout(() => offerInput.focus(), 50);
}
function closeHaggle() { overlay.classList.remove('open'); }

function cartPayload() {
  const o = {};
  for (const [id, qty] of cart) o[id] = qty;
  return JSON.stringify(o);
}

async function sendOffer() {
  if (busy) return;
  const offer = Number(offerInput.value);
  if (!offerInput.value || offer < 0 || Number.isNaN(offer)) {
    bubble.className = 'keeper-bubble counter';
    bubble.textContent = 'Give me a real number, friend.';
    return;
  }
  busy = true; offerSend.disabled = true;
  bubble.className = 'keeper-bubble';
  bubble.textContent = '…';

  const body = new URLSearchParams({
    action: 'haggle', shop_id: SHOP_ID, offer: String(offer),
    round: String(round), cart: cartPayload()
  });

  let data;
  try {
    const res = await fetch(window.location.pathname, { method: 'POST', body });
    data = await res.json();
  } catch (err) {
    bubble.className = 'keeper-bubble critfail';
    bubble.textContent = 'The shopkeeper got distracted. Try again.';
    busy = false; offerSend.disabled = false;
    return;
  }

  bubble.className = 'keeper-bubble ' + (data.result === 'error' ? 'counter' : data.result);
  bubble.textContent = data.message || '…';
  busy = false;

  if (data.result === 'accept') {
    face.textContent = '🤝';
    floorHint.textContent = '';
    offerInput.disabled = true; offerSend.disabled = true;
    roundPill.textContent = `Deal struck for ${fmt(data.charged)} ${CURRENCY} — saved ${fmt(asking - data.charged)}!`;
    cart.clear(); renderCart();
    setTimeout(closeHaggle, 2600);
  } else if (data.result === 'critfail') {
    face.textContent = '🤬';
    offerInput.disabled = true; offerSend.disabled = true;
    roundPill.textContent = 'Booted from the shop!';
    setTimeout(closeHaggle, 2600);
  } else if (data.result === 'counter') {
    face.textContent = '😤';
    round++;
    roundPill.textContent = 'Round ' + round;
    if (data.npc_min) floorHint.textContent = 'Hint: try ≥ ' + fmt(data.npc_min);
    offerSend.disabled = false;
    offerInput.focus(); offerInput.select();
  } else {
    offerSend.disabled = false;
  }
}

haggleBtn.addEventListener('click', openHaggle);
offerSend.addEventListener('click', sendOffer);
offerInput.addEventListener('keydown', e => { if (e.key === 'Enter') sendOffer(); });
$('[data-haggle-walk]').addEventListener('click', closeHaggle);
overlay.addEventListener('click', e => { if (e.target === overlay) closeHaggle(); });

renderCart();
</script>
</body>
</html>
