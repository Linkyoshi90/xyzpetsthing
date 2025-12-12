<?php
require_once __DIR__.'/../auth.php';
require_login();
?>
<link rel="stylesheet" href="assets/css/garden-invaderz.css">
<script defer src="assets/js/garden-invaderz.js"></script>
<h1>Garden Invaderz</h1>
<canvas id="game" width="400" height="600"></canvas>
<p class="muted">Move with A/D keys, W to shoot.</p>
<div id="score">Score: <span id="scoreVal">0</span></div>