<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 27,
    'fallback_name' => 'Meridian Corner Mart',
    'intro' => 'Coffee, commuter snacks, neon keychains, city stickers, transit cards, and sunglasses.',
    'aria_label' => 'Available Meridian Corner Mart goods',
]);
