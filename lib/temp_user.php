<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';

function temp_user_add_pet(array $pet): array {
    $store = &temp_user_data();
    if (!isset($store['next_pet_id'])) {
        $store['next_pet_id'] = 1;
    }
    $pet['pet_instance_id'] = $store['next_pet_id'];
    $store['next_pet_id']++;
    $store['pets'][] = $pet;
    return $pet;
}

function temp_user_get_pets(): array {
    $store = temp_user_data();
    return array_values($store['pets']);
}

function temp_user_add_inventory_item(int $itemId, int $quantity): void {
    if ($itemId <= 0 || $quantity === 0) {
        return;
    }
    $store = &temp_user_data();
    if (!isset($store['inventory'][$itemId])) {
        $store['inventory'][$itemId] = 0;
    }
    $store['inventory'][$itemId] = max(0, $store['inventory'][$itemId] + $quantity);
}

function temp_user_inventory_rows(): array {
    $store = temp_user_data();
    if (empty($store['inventory'])) {
        return [];
    }
    $itemIds = array_keys(array_filter($store['inventory'], static fn($qty) => $qty > 0));
    if (!$itemIds) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
    $rows = q(
        "SELECT i.item_id, i.item_name, ic.category_name FROM items i"
        . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
        . " WHERE i.item_id IN ($placeholders)",
        $itemIds
    )->fetchAll(PDO::FETCH_ASSOC);
    $meta = [];
    foreach ($rows as $row) {
        $meta[(int)$row['item_id']] = $row;
    }
    $result = [];
    foreach ($itemIds as $itemId) {
        $qty = (int)($store['inventory'][$itemId] ?? 0);
        if ($qty <= 0) {
            continue;
        }
        $info = $meta[$itemId] ?? ['item_name' => "Item #$itemId", 'category_name' => null];
        $result[] = [
            'item_name' => $info['item_name'],
            'category_name' => $info['category_name'],
            'quantity' => $qty,
        ];
    }
    return $result;
}

function temp_user_adjust_balance(string $type, float $delta): int {
    $key = $type === 'gems' ? 'gems' : 'cash';
    $store = &temp_user_data();
    if (!isset($store['balances'][$key])) {
        $store['balances'][$key] = 0.0;
    }
    $store['balances'][$key] = round($store['balances'][$key] + $delta, 2);
    if ($store['balances'][$key] < 0) {
        $store['balances'][$key] = 0.0;
    }
    $_SESSION['user'][$key] = (int)round($store['balances'][$key]);
    return $_SESSION['user'][$key];
}

function temp_user_balance(string $type): float {
    $key = $type === 'gems' ? 'gems' : 'cash';
    $store = temp_user_data();
    return (float)($store['balances'][$key] ?? 0.0);
}

function temp_user_reset_score_counter_if_needed(): void {
    $store = &temp_user_data();
    $today = date('Y-m-d');
    if (($store['score_exchange']['date'] ?? null) !== $today) {
        $store['score_exchange']['date'] = $today;
        $store['score_exchange']['count'] = 0;
    }
}

function temp_user_increment_score_counter(): int {
    $store = &temp_user_data();
    $store['score_exchange']['count'] = (int)($store['score_exchange']['count'] ?? 0) + 1;
    return $store['score_exchange']['count'];
}

function temp_user_score_counter(): int {
    $store = temp_user_data();
    return (int)($store['score_exchange']['count'] ?? 0);
}

function &temp_user_bank_data(): array {
    $store = &temp_user_data();
    if (!isset($store['bank'])) {
        $store['bank'] = ['has_account' => false, 'balance' => 0.0, 'interest' => 0];
    }
    return $store['bank'];
}