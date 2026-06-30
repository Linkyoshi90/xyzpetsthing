<?php

declare(strict_types=1);

require_once __DIR__.'/../db.php';

/**
 * Fetch a shop row by id.
 */
function shop_get(int $shopId): ?array
{
    $stmt = q('SELECT shop_id, shop_name, region_id FROM shops WHERE shop_id = ?', [$shopId]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);
    return $shop ?: null;
}

/**
 * Locate an image for an item name using several naming conventions.
 */
function shop_find_item_image(string $name): string
{
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
    $variants = array_values(array_unique(array_filter($variants, static function ($v) {
        return $v !== null && $v !== '';
    })));

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

/**
 * Fetch inventory rows for a shop with resolved price, stock, and image information.
 */
function shop_inventory(int $shopId): array
{
    $rows = q(
        "SELECT si.item_id, si.price, si.stock, i.item_name, i.base_price, i.item_description\n"
        ."FROM shop_inventory si\n"
        ."JOIN items i ON i.item_id = si.item_id\n"
        ."WHERE si.shop_id = ?\n"
        ."ORDER BY i.item_name",
        [$shopId]
    )->fetchAll(PDO::FETCH_ASSOC);

    $inventory = [];
    foreach ($rows as $row) {
        $price = $row['price'];
        if ($price === null) {
            $price = $row['base_price'];
        }
        $price = (float) $price;
        $stock = $row['stock'];
        $stock = $stock === null ? null : (int) $stock;
        $imageFile = shop_find_item_image($row['item_name']);
        $inventory[] = [
            'item_id' => (int) $row['item_id'],
            'name' => $row['item_name'],
            'price' => $price,
            'stock' => $stock,
            'description' => $row['item_description'],
            'image' => 'images/items/'.rawurlencode($imageFile),
        ];
    }

    return $inventory;
}

/**
 * Fetch a read-only catalog of every item in the game with price and imagery.
 */
function shop_full_catalog(): array
{
    $rows = q(
        "SELECT item_id, item_name, item_description, base_price\n"
        ."FROM items\n"
        ."ORDER BY item_name"
    )->fetchAll(PDO::FETCH_ASSOC);

    $catalog = [];
    foreach ($rows as $row) {
        $imageFile = shop_find_item_image($row['item_name']);
        $catalog[] = [
            'item_id' => (int) $row['item_id'],
            'name' => $row['item_name'],
            'price' => (float) $row['base_price'],
            'stock' => null,
            'description' => $row['item_description'],
            'image' => 'images/items/'.rawurlencode($imageFile),
        ];
    }

    return $catalog;
}

/**
 * Index inventory by item id for quick lookups.
 */
function shop_inventory_indexed(array $inventory): array
{
    $indexed = [];
    foreach ($inventory as $item) {
        $indexed[$item['item_id']] = $item;
    }
    return $indexed;
}

/**
 * Normalise cart entries coming from a HTML form payload.
 *
 * @return array<int, array{item_id:int, quantity:int}>
 */
function shop_normalize_cart_payload($payload): array
{
    if (!is_array($payload)) {
        return [];
    }

    $normalized = [];
    foreach ($payload as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $itemId = isset($entry['item_id']) ? (int) $entry['item_id'] : 0;
        $quantity = isset($entry['quantity']) ? (int) $entry['quantity'] : 0;
        if ($itemId <= 0 || $quantity <= 0) {
            continue;
        }
        $normalized[] = [
            'item_id' => $itemId,
            'quantity' => $quantity,
        ];
    }

    return $normalized;
}

/**
 * Execute a checkout for a given shop and cart payload.
 *
 * @return array{items: array<int, array<string,mixed>>, total: float, stock: array<int,int|null>}
 * @throws RuntimeException when validation fails.
 */
function shop_checkout(int $shopId, int $userId, array $orderItems): array
{
    if ($userId <= 0) {
        throw new RuntimeException('You must be signed in to place an order.');
    }

    if (!$orderItems) {
        throw new RuntimeException('Your cart is empty.');
    }

    $pdo = db();
    $total = 0.0;
    $updatedStock = [];

    try {
        $pdo->beginTransaction();

        foreach ($orderItems as &$orderItem) {
            $itemId = $orderItem['item_id'];
            $stmt = $pdo->prepare(
                'SELECT si.stock, si.price, i.base_price, i.item_name '
                .'FROM shop_inventory si '
                .'JOIN items i ON i.item_id = si.item_id '
                .'WHERE si.shop_id = ? AND si.item_id = ? FOR UPDATE'
            );
            $stmt->execute([$shopId, $itemId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new RuntimeException('One of the items is no longer available. Please refresh and try again.');
            }

            $price = $row['price'];
            if ($price === null) {
                $price = $row['base_price'];
            }
            $price = (float) $price;
            $orderItem['name'] = $row['item_name'];
            $orderItem['price'] = round($price, 2);

            $quantity = $orderItem['quantity'];
            $stock = $row['stock'];
            $stock = $stock === null ? null : (int) $stock;

            if ($stock !== null) {
                if ($quantity > $stock) {
                    throw new RuntimeException(sprintf('Only %d � %s remain in stock.', $stock, $orderItem['name']));
                }
                $newStock = $stock - $quantity;
                $updateStmt = $pdo->prepare('UPDATE shop_inventory SET stock = ? WHERE shop_id = ? AND item_id = ?');
                $updateStmt->execute([$newStock, $shopId, $itemId]);
            } else {
                $newStock = null;
            }

            $invStmt = $pdo->prepare(
                'INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, ?) '
                .'ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)'
            );
            $invStmt->execute([$userId, $itemId, $quantity]);

            $lineTotal = round($orderItem['price'] * $quantity, 2);
            $orderItem['line_total'] = $lineTotal;
            $total += $lineTotal;
            $updatedStock[$itemId] = $newStock;
        }
        unset($orderItem);

        $pdo->commit();
    } catch (Throwable $err) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $err;
    }

    $stockPayload = [];
    foreach ($updatedStock as $itemId => $stockValue) {
        $stockPayload[$itemId] = $stockValue === null ? null : (int) $stockValue;
    }

    return [
        'items' => array_values($orderItems),
        'total' => round($total, 2),
        'stock' => $stockPayload,
    ];
}

/**
 * Build a structured error array that can be logged on the client side.
 */
function shop_error_payload(string $message, array $context = []): array
{
    return [
        'ok' => false,
        'error' => $message,
        'context' => $context,
    ];
}

/* =========================================================================
 * Haggling system
 *
 * Shops are negotiated, Neopets-style: the player names a price, the clerk
 * either takes it, counters with a floor, or (if insulted) boots them. The
 * whole loop runs over fetch()/JSON so the page never hard-refreshes. See
 * simple_shop_handle_haggle() in simple_shop_page.php for the request wiring.
 * ====================================================================== */

/**
 * Map a region id to the short initials used for clerk art slugs (and for the
 * existing shop-page filename prefixes, e.g. cc-souq, ynk-ramen). Edit freely
 * as regions are added.
 */
function shop_region_initials(int $regionId): string
{
    static $map = [
        1 => 'aa',    // Aegia Aeterna
        2 => 'nh',    // Nornheim
        3 => 'rl',    // Rheinland
        4 => 'rt',    // Rodinian Tsardom
        5 => 'ldk',   // Lotus-Dragon Kingdom
        6 => 'bm',    // Baharamandal
        7 => 'ynk',   // Yamanokubo
        8 => 'xm',    // Xochimex
        9 => 'esd',   // Eagle Serpent Dominion
        10 => 'ie',   // Itzam Empire
        11 => 'srl',  // Spice Route League
        12 => 'cc',   // Crescent Caliphate
        13 => 'h',    // Hammurabia
        14 => 'esl',  // Eretz-Shalem League
        15 => 'km',   // Kemet
        16 => 'sc',   // Sila Council
        17 => 'rsc',  // Red Sun Commonwealth
        18 => 'yn',   // Yara Nations
        19 => 'gc',   // Gran Columbia
        20 => 'sie',  // Sapa Inti Empire
        22 => 'urb',  // United free Republic of Borealia
        25 => 'stap', // Sovereign Tribes of the Ancestral Plains
        26 => 'br',   // Bretonreach
        27 => 'aest', // Aeonstep Plateau
        28 => 'plg',  // Pelagora
    ];

    return $map[$regionId] ?? 'gen';
}

/**
 * Pick a shop-clerk portrait for a region.
 *
 * Scans images/creatures/ for files named `shopklerk_<gender>_<region><n>.<ext>`
 * (gender = m|f, region = the initials from shop_region_initials, n optional),
 * e.g. shopklerk_m_cc1.webp or shopklerk_f_ynk.png. One match is chosen at
 * random. If the region has no art it falls back to any clerk tagged `gen`, then
 * to any clerk at all; if nothing is found it returns null so the UI can show an
 * emoji placeholder.
 *
 * @return array{image:string, gender:string}|null
 */
function shop_pick_clerk(int $regionId): ?array
{
    $dir = __DIR__.'/../images/creatures';
    if (!is_dir($dir)) {
        return null;
    }

    $initials = shop_region_initials($regionId);

    $byRegion = [];
    $generic = [];
    $any = [];

    foreach (glob($dir.'/shopklerk_*') as $path) {
        $file = basename($path);
        if (!preg_match('/^shopklerk_([mf])_([a-z]+?)(\d*)\.(png|webp|jpg|jpeg|gif)$/i', $file, $m)) {
            continue;
        }
        $entry = [
            'image' => 'images/creatures/'.rawurlencode($file),
            'gender' => strtolower($m[1]) === 'f' ? 'f' : 'm',
        ];
        $region = strtolower($m[2]);
        $any[] = $entry;
        if ($region === $initials) {
            $byRegion[] = $entry;
        } elseif ($region === 'gen') {
            $generic[] = $entry;
        }
    }

    $pool = $byRegion ?: ($generic ?: $any);
    if (!$pool) {
        return null;
    }

    return $pool[array_rand($pool)];
}

/**
 * Resolve a raw cart payload against a shop's live inventory and return the
 * authoritative asking total plus the validated order lines. Prices come from
 * the inventory (never the client) so the haggle can't be gamed.
 *
 * @return array{total: float, items: array<int, array{item_id:int, quantity:int}>, error: ?string}
 */
function shop_resolve_cart(array $inventory, array $cartPayload): array
{
    $indexed = shop_inventory_indexed($inventory);
    $items = [];
    $total = 0.0;

    foreach ($cartPayload as $entry) {
        $itemId = (int) ($entry['item_id'] ?? 0);
        $quantity = (int) ($entry['quantity'] ?? 0);
        if ($itemId <= 0 || $quantity <= 0 || !isset($indexed[$itemId])) {
            return ['total' => 0.0, 'items' => [], 'error' => 'One of the items is no longer sold here.'];
        }

        $item = $indexed[$itemId];
        $stock = $item['stock'];
        if ($stock !== null && $quantity > $stock) {
            return [
                'total' => 0.0,
                'items' => [],
                'error' => sprintf('You can only buy up to %d x %s.', $stock, $item['name']),
            ];
        }

        if (isset($items[$itemId])) {
            $items[$itemId]['quantity'] += $quantity;
        } else {
            $items[$itemId] = ['item_id' => $itemId, 'quantity' => $quantity];
        }
        $total += (float) $item['price'] * $quantity;
    }

    if (!$items) {
        return ['total' => 0.0, 'items' => [], 'error' => 'Your basket is empty.'];
    }

    return ['total' => round($total, 2), 'items' => array_values($items), 'error' => null];
}

function shop_haggle_clamp(float $v, float $lo, float $hi): float
{
    return max($lo, min($hi, $v));
}

function shop_haggle_pick(array $opts): string
{
    return $opts[array_rand($opts)];
}

/**
 * Run a single round of negotiation.
 *
 * @param float $required asking price for the whole basket
 * @param float $offer    the player's perceived value
 * @param int   $round    1-based round counter (the clerk softens as it climbs)
 * @return array{result:string, charged?:float, npc_min?:float, message:string}
 *         result is one of: accept | counter | critfail
 */
function shop_haggle_round(float $required, float $offer, int $round): array
{
    $offer = max(0.0, round($offer, 2));
    $round = max(1, $round);

    // At or above asking → instant yes, and never overpay.
    if ($offer >= $required) {
        return [
            'result' => 'accept',
            'charged' => round($required, 2),
            'message' => shop_haggle_pick([
                "Pleasure doing business with you!",
                "A fair price! It's yours.",
                "Sold! You drive a generous bargain.",
            ]),
        ];
    }

    // How far below asking the offer sits: 0 = at price, 1 = offering nothing.
    $gap = ($required - $offer) / $required;

    // The clerk grows more flexible the longer the player stays at the table.
    $roundBonus = min(0.10 * ($round - 1), 0.30);

    // Acceptance chance falls as the gap widens; lowballs ≈ no chance.
    $acceptChance = shop_haggle_clamp(1.0 - ($gap * 1.7) + $roundBonus, 0.0, 0.95);

    // Critical-fail (insult) chance only bites once you lowball hard.
    $critChance = shop_haggle_clamp(($gap - 0.45) * 0.9, 0.0, 0.65);

    $roll = mt_rand() / mt_getrandmax();

    // Above the acceptance threshold → take the player's number.
    if ($roll < $acceptChance) {
        return [
            'result' => 'accept',
            'charged' => $offer,
            'message' => shop_haggle_pick([
                "Hnng... fine. Robbery, but FINE. Take it.",
                "You're killing me here. Deal.",
                "Alright, alright - sold, before I change my mind.",
            ]),
        ];
    }

    // Critical fail → booted from the shop.
    if ($roll > 1.0 - $critChance) {
        return [
            'result' => 'critfail',
            'message' => shop_haggle_pick([
                "This is an outrage! Get out, you thief!",
                "GET OUT. And don't come back, you swindler!",
                "How DARE you insult my wares?! Out! OUT!",
            ]),
        ];
    }

    // Otherwise counter with a floor that drifts from near-asking toward an
    // absolute floor as the rounds go on, giving the player a moving target.
    $absoluteFloor = $required * 0.55;
    $startMin = $required * 0.92;
    $npcMin = max($absoluteFloor, $startMin - ($startMin - $absoluteFloor) * min(($round - 1) / 4, 1.0));
    $npcMin = max($npcMin, $offer + 0.01);
    $npcMin = round($npcMin, 2);

    return [
        'result' => 'counter',
        'npc_min' => $npcMin,
        'message' => sprintf(
            shop_haggle_pick([
                "Come on, I've got mouths to feed. I won't go below %s %s!",
                "Be reasonable! %s %s is the lowest I'll stomach.",
                "You wound me. Not a coin under %s %s, and that's mercy.",
            ]),
            number_format($npcMin, 2),
            APP_CURRENCY_SHORT_NAME
        ),
    ];
}