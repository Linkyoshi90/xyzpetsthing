<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 25,
    'fallback_name' => 'Turtlestar Trading Blanket',
    'intro' => 'Beadwork, corn cakes, seed pouches, prairie herbs, story tokens, and trail charms.',
    'aria_label' => 'Available Turtlestar goods',
]);
