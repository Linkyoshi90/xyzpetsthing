<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 22,
    'fallback_name' => 'Velesgrad Winter Pantry',
    'intro' => 'Rye bread, preserves, lacquer boxes, mitts, painted eggs, and samovar tea.',
    'aria_label' => 'Available Velesgrad Pantry goods',
]);
