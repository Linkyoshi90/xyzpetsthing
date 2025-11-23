<?php

declare(strict_types=1);

require_once __DIR__.'/../db.php';

/**
 * Fetch a shop row by id.
 */
function shop_get(int $shopId): ?array
{
    $stmt = q('SELECT shop_id, shop_name FROM shops WHERE shop_id = ?', [$shopId]);
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
                    throw new RuntimeException(sprintf('Only %d × %s remain in stock.', $stock, $orderItem['name']));
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