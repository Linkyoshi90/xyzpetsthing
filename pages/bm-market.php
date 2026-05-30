<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 10,
    'fallback_name' => 'Mandala Market Stall',
    'intro' => 'Temple-bazaar sweets, charms, dyes, and lamp goods from Padmanagara.',
    'aria_label' => 'Available Mandala Market goods',
]);
