<?php
require_once __DIR__.'/../auth.php';
require_login();
?>
<link rel="stylesheet" href="assets/css/galaxian.css">
<script defer src="assets/js/galaxian.js"></script>
<h1>Galaxian Clone</h1>
<canvas id="game" width="400" height="600"></canvas>
<p class="muted">Move with arrow keys, space to shoot.</p>
<div id="score">Score: <span id="scoreVal">0</span></div>