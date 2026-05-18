<?php
require_login();
require_once __DIR__.'/../lib/simple_shop_page.php';

$shopId = 11;
$shop = shop_get($shopId) ?? ['shop_id' => $shopId, 'shop_name' => 'Drowned Stacks Library'];
$inventory = shop_inventory($shopId);

$notice = simple_shop_handle_purchase($shopId, $inventory, [
    'empty_shop' => 'The archivists are still drying the catalog slips.',
    'missing_item' => 'That book is no longer on the public desk.',
    'stock_limit' => 'Only %d x %s can leave the stacks today.',
    'checkout_failed' => 'The lending ledger would not take the stamp. Please try again.',
    'success' => 'You borrowed %d x %s for %s %s.',
]);

if ($notice) {
    $inventory = shop_inventory($shopId);
}

render_simple_shop_page([
    'shop' => $shop,
    'inventory' => $inventory,
    'notice' => $notice,
    'fallback_name' => 'Drowned Stacks Library',
    'intro' => 'Waterproofed tablets, shell-bound books, and lore copied above the tide line.',
    'section_title' => 'Lore Books',
    'empty_text' => 'The public desk is empty while the librarians reshelve the dry cases.',
    'aria_label' => 'Available Pelagora lore books',
    'submit_label' => 'Borrow',
    'info_cards' => [
        [
            'title' => 'Reading Room',
            'text' => 'Borrowed books appear with your pet items, where they can be read aloud to raise intelligence.',
            'href' => '?pg=pet',
            'action' => 'Read to a creature',
        ],
        [
            'title' => 'Creature Encyclopedia',
            'text' => 'The Drowned Stacks keep a shared bestiary beside the tide ledgers.',
            'href' => '?pg=encyclopedia',
            'action' => 'Open the encyclopedia',
        ],
    ],
]);
