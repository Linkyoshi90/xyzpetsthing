<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 16,
    'fallback_name' => 'Ziggurat Ledger House',
    'intro' => 'Scribe tools, clay tablets, bronze seals, lapis beads, and date wine.',
    'aria_label' => 'Available Ziggurat Ledger goods',
]);
