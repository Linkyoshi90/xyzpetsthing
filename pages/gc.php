<?php
require_login();
require_once __DIR__.'/../lib/country_map_data.php';

$config = get_country_map_config(basename(__FILE__, '.php'));
if ($config === null) {
    echo '<div class="card glass"><h2>Map unavailable</h2><p class="muted">This region map configuration is missing.</p></div>';
    return;
}

render_country_interactive_map($config);
