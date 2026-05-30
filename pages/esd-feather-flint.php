<?php
require_once __DIR__.'/../lib/regional_shop_page.php';

render_regional_shop_front([
    'shop_id' => 12,
    'fallback_name' => 'Feather and Flint Exchange',
    'intro' => 'Obsidian tools, cacao, feather charms, maize cakes, and ceremonial tokens.',
    'aria_label' => 'Available Feather and Flint goods',
]);
