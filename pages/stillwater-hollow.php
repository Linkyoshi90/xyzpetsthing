<?php
require_login();
require_once __DIR__.'/../lib/map_unlocks.php';
require_once __DIR__.'/../lib/country_map_data.php';

$user = current_user();
if (!$user || !has_map_unlock((int)$user['id'], 'stillwater_hollow')) {
    echo '<div class="card glass"><h2>Road Not Found</h2><p class="muted">The repaired URB roads do not lead to Stillwater Hollow yet.</p><p><a class="btn" href="?pg=urb">Back to Meridian Arc</a></p></div>';
    return;
}

$config = get_country_map_config('stillwater-hollow');
if ($config === null) {
    echo '<div class="card glass"><h2>Map unavailable</h2><p class="muted">This region map configuration is missing.</p></div>';
    return;
}

render_country_interactive_map($config);
