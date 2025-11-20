<?php
require_once __DIR__.'/../auth.php';
require_login();
?>
<link rel="stylesheet" href="assets/css/style.css">
<script defer src="assets/js/fishing.js"></script>
<h1>Fishing</h1>
<div class="fishing-layout">
    <div class="fishing-wrapper">
        <canvas id="fishing-ui" width="200" height="600"></canvas>
        <canvas id="fishing-game" width="600" height="600"></canvas>
    </div>
    <p class="muted fishing-instructions">Controls: <strong>W / S</strong> to raise or lower the hook, <strong>A / D</strong> to move left or right.</p>
    <div class="fishing-actions">
        <button id="fishing-exchange-btn" class="btn" hidden>Convert score to dosh</button>
        <p class="muted" id="fishing-finish-status"></p>
    </div>
</div>