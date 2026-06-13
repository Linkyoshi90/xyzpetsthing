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
        'checkout_success' => $config['checkout_success'] ?? 'The clerk sets your basket aside at the counter.',
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
        'cart_title' => $config['cart_title'] ?? 'Your Basket',
        'cart_help' => $config['cart_help'] ?? 'Adjust quantities or remove items before you pay.',
        'cart_empty' => $config['cart_empty'] ?? 'Select an item to begin filling your basket.',
        'cart_aria_label' => $config['cart_aria_label'] ?? 'Shopping basket',
        'sending_text' => $config['sending_text'] ?? 'Sending your basket to the counter...',
    ]);
}
