<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 31,
    'fallback_name' => 'Moon-and-Mortar Apothecary',
    'intro' => 'Measured draughts, fragrant remedies, and carefully labeled cures prepared beneath Ansurah\'s crescent lamps.',
    'section_title' => 'Remedies',
    'empty_text' => 'The remedy shelves are empty while the apothecary grinds a fresh batch.',
    'aria_label' => 'Available Moon-and-Mortar remedies',
    'cart_title' => 'Your Remedy Basket',
    'cart_help' => 'Choose the cure that matches your pet\'s sickness before paying at the counter.',
    'cart_empty' => 'Select a remedy to begin filling your basket.',
    'cart_aria_label' => 'Apothecary remedy basket',
    'checkout_success' => 'The apothecary wraps your remedies and seals each label with crescent-blue wax.',
    'sending_text' => 'Bringing your remedy basket to the apothecary...',
]);
