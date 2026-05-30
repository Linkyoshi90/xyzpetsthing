<?php

declare(strict_types=1);

require_once __DIR__.'/simple_shop_page.php';

function render_regional_shop_front(array $config): void
{
    require_login();

    $shopId = (int) ($config['shop_id'] ?? 0);
    $fallbackName = (string) ($config['fallback_name'] ?? 'Regional Shop');
    $shop = shop_get($shopId) ?? ['shop_id' => $shopId, 'shop_name' => $fallbackName];
    $inventory = shop_inventory($shopId);

    $notice = simple_shop_handle_purchase($shopId, $inventory, [
        'empty_shop' => $config['empty_shop'] ?? 'The shelves are empty until the next restock.',
        'missing_item' => $config['missing_item'] ?? 'That item is no longer sold here.',
        'stock_limit' => $config['stock_limit'] ?? 'You can only buy up to %d x %s today.',
        'checkout_failed' => $config['checkout_failed'] ?? 'The counter could not complete the purchase. Please try again.',
        'success' => $config['success'] ?? 'You bought %d x %s for %s %s.',
    ]);

    if ($notice) {
        $inventory = shop_inventory($shopId);
    }

    render_simple_shop_page([
        'shop' => $shop,
        'inventory' => $inventory,
        'notice' => $notice,
        'fallback_name' => $fallbackName,
        'intro' => $config['intro'] ?? '',
        'section_title' => $config['section_title'] ?? 'Stock',
        'empty_text' => $config['empty_text'] ?? 'Nothing is stocked here right now.',
        'aria_label' => $config['aria_label'] ?? 'Available regional goods',
        'submit_label' => $config['submit_label'] ?? 'Buy',
    ]);
}
