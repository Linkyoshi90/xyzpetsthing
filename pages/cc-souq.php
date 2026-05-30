<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 11,
    'fallback_name' => 'Souq al-Hilal Provisions',
    'intro' => 'Caravan provisions, cooling drinks, lanterns, and moonlit bazaar trinkets.',
    'aria_label' => 'Available Souq al-Hilal goods',
]);
