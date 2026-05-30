<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 21,
    'fallback_name' => 'Redwind Roadhouse Store',
    'intro' => 'Road pies, sun hats, enamel mugs, trail maps, carnival tickets, and travel kits.',
    'aria_label' => 'Available Redwind Roadhouse goods',
]);
