<?php
require_once __DIR__.'/../auth.php';
require_login();
require_once __DIR__.'/../db.php';

// The whole combat model lives in `elements` / `element_calc`, so the page hands the
// client both tables and the simulation never talks to the server again.
$element_rows = q("SELECT element_id, element_name FROM elements ORDER BY element_id")->fetchAll(PDO::FETCH_ASSOC);
$calc_rows = q("SELECT element_id, target_element_id, effectiveness FROM element_calc")->fetchAll(PDO::FETCH_ASSOC);

$royale_elements = [];
foreach ($element_rows as $row) {
    $royale_elements[] = ['id' => (int)$row['element_id'], 'name' => $row['element_name']];
}

$royale_matrix = [];
foreach ($calc_rows as $row) {
    $royale_matrix[(int)$row['element_id']][(int)$row['target_element_id']] = (float)$row['effectiveness'];
}

// With no database the client falls back to the glyph list and a neutral matrix,
// so the map is still playable locally - every matchup is simply 1.0.
$royale_payload = [
    'elements' => $royale_elements,
    'matrix' => $royale_matrix,
];
?>
<link rel="stylesheet" href="assets/css/element-royale.css">
<?php $element_icons_js_version = is_file(__DIR__.'/../assets/js/element-icons.js') ? filemtime(__DIR__.'/../assets/js/element-icons.js') : 1; ?>
<?php $element_royale_js_version = is_file(__DIR__.'/../assets/js/element-royale.js') ? filemtime(__DIR__.'/../assets/js/element-royale.js') : 1; ?>
<script defer src="assets/js/element-icons.js?v=<?= $element_icons_js_version ?>"></script>
<script defer src="assets/js/element-royale.js?v=<?= $element_royale_js_version ?>"></script>

<h1>Element Royale</h1>
<p class="muted">Pick an element, pick a tile, then roam. Collide with anyone and the winner absorbs the loser's number. Last one standing takes the map.</p>

<div id="er-app" class="er-app">
  <section id="er-setup" class="er-setup">
    <h2>1. Choose your element</h2>
    <div id="er-element-picker" class="er-element-picker"></div>

    <h2>2. Choose your spawn</h2>
    <p class="muted er-setup-hint">Click any open tile on the map below. Grey tiles are roadblocks and trees, blue is water, green is bush &mdash; you can stand in a bush and nobody can see you.</p>
    <div class="er-setup-actions">
      <button type="button" id="er-reroll" class="btn">Reroll map</button>
      <span class="muted">Seed <span id="er-seed">-</span></span>
      <button type="button" id="er-start" class="btn" disabled>Start round</button>
    </div>
  </section>

  <section class="er-stage">
    <div id="er-hud" class="er-hud">
      <div><strong>Your points</strong><span id="er-points">5</span></div>
      <div><strong>Element</strong><span id="er-element">-</span></div>
      <div><strong>Alive</strong><span id="er-alive">0</span></div>
      <div><strong>Step every</strong><span id="er-speed">-</span></div>
    </div>
    <div class="er-board-wrap">
      <div id="er-board" class="er-board is-picking"></div>
      <div id="er-overlay" class="er-overlay" hidden>
        <h2 id="er-overlay-title">Round over</h2>
        <p id="er-overlay-body"></p>
        <button type="button" id="er-again" class="btn">Play again</button>
      </div>
    </div>
    <ul id="er-feed" class="er-feed"></ul>
  </section>
</div>

<p class="instructions">Arrow keys or WASD to move. Your step speed depends on your size &mdash; the more points you carry, the slower you move.</p>

<script id="er-payload" type="application/json"><?= json_encode($royale_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
