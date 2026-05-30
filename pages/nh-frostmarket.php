<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 20,
    'fallback_name' => 'Skeldgard Frostmarket',
    'intro' => 'Fjord fish, wool cloaks, rune stones, aurora glass, bone buttons, and berry mead.',
    'aria_label' => 'Available Frostmarket goods',
]);
