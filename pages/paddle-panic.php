<link rel="stylesheet" href="assets/css/paddle-panic.css">
<script defer src="assets/js/paddle-panic.js"></script>
<h1>Paddle Panic</h1>
<canvas id="pong" width="900" height="540"></canvas>
<div id="modifierNotice" aria-live="assertive" aria-atomic="true"></div>
<div class="hud">
    <div>Score: <span id="scoreVal">0</span></div>
    <div>Hits: <span id="hitVal">0</span></div>
    <div>Missed: <span id="missVal">0</span>/<span id="missLimitVal">10</span></div>
    <div class="upgrade-indicator">Next Upgrade: <span id="upgradeProgress">5</span> hits</div>
</div>
<p class="instructions">Use W/S or the arrow keys to move your paddle. Collect upgrades that drift in after every five successful hits and experiment with the new modifiers.</p>
<div id="gameOverNotice">Final Score: <span id="finalScoreVal">0</span></div>