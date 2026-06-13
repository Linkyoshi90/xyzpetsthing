<?php
require_once __DIR__.'/../lib/map_unlocks.php';
require_once __DIR__.'/../lib/regional_shop_page.php';

require_login();
$user = current_user();
if (!$user || !has_map_unlock((int)$user['id'], 'stillwater_hollow')) {
    echo '<div class="card glass"><h2>Road Not Found</h2><p class="muted">The repaired URB roads do not lead to Stillwater Hollow yet.</p><p><a class="btn" href="?pg=urb">Back to Meridian Arc</a></p></div>';
    return;
}

render_regional_shop_front([
    'shop_id' => 30,
    'fallback_name' => 'Stillwater Crossroads Supply',
    'intro' => 'Dusty jars, well filters, barn hardware, paper talismans, and sealed tins from the last lit counter before the hollow roads go bad.',
    'aria_label' => 'Available Stillwater Crossroads Supply goods',
    'empty_text' => 'The shelves are bare, though the clerk insists they were stocked this morning.',
]);
