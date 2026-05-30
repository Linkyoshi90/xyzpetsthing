<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 15,
    'fallback_name' => 'Solvine Plaza Kiosk',
    'intro' => 'Bright plaza snacks, riverwalk keepsakes, and city goods from Solvine.',
    'aria_label' => 'Available Solvine Plaza goods',
]);
