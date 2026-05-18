<?php
require_login();
require_once __DIR__.'/../lib/simple_shop_page.php';

$shopId = 10;
$shop = shop_get($shopId) ?? ['shop_id' => $shopId, 'shop_name' => 'Tideglass Aquatics'];
$keywords = ['Aquatic', 'Pelagoric', 'Pelagora', 'Underwater'];

$inventory = simple_shop_filter_inventory_by_keywords(shop_inventory($shopId), $keywords);
$notice = simple_shop_handle_purchase($shopId, $inventory, [
    'empty_shop' => 'The tideglass shelves are bare until the next diver boat comes in.',
    'missing_item' => 'That aquatic good slipped out of stock.',
    'stock_limit' => 'You can only buy up to %d x %s before the tide bell rings.',
    'checkout_failed' => 'The shell register would not settle the order. Please try again.',
    'success' => 'You bought %d x %s for %s %s.',
]);

if ($notice) {
    $inventory = simple_shop_filter_inventory_by_keywords(shop_inventory($shopId), $keywords);
}

render_simple_shop_page([
    'shop' => $shop,
    'inventory' => $inventory,
    'notice' => $notice,
    'fallback_name' => 'Tideglass Aquatics',
    'intro' => 'An airy ring-quay shop for things that survive salt, pressure, and old city pride.',
    'section_title' => 'Aquatic Goods',
    'empty_text' => 'No Aquatic, Pelagoric, Pelagora, or Underwater goods are stocked right now.',
    'aria_label' => 'Available aquatic goods',
    'submit_label' => 'Buy',
]);
