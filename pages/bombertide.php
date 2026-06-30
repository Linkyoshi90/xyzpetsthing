<?php
require_once __DIR__.'/../auth.php';
require_login();
require_once __DIR__.'/../lib/pets.php';

$uid = current_user()['id'];

// Player avatar = first creature in their party (fall back to a default sprite).
$pets = get_user_pets($uid);
$playerImg = 'images/creatures/tengu_f_blue.webp';
if (!empty($pets)) {
    $first = $pets[0];
    $playerImg = pet_image_url(
        $first['species_name'] ?? 'tengu',
        $first['color_name'] ?? null
    );
}

// Pick 3 random NPC creatures from the creature art pool (distinct from each other).
$npcImgs = [];
$pool = glob(__DIR__.'/../images/creatures/*_f_blue.webp') ?: [];
if (count($pool) >= 3) {
    $keys = array_rand($pool, 3);
    foreach ($keys as $k) {
        $npcImgs[] = 'images/creatures/'.basename($pool[$k]);
    }
}
while (count($npcImgs) < 3) {
    $npcImgs[] = 'images/creatures/tengu_f_red.webp';
}

$config = [
    'player' => $playerImg,
    'npcs'   => array_values($npcImgs),
    'bomb'   => 'images/games/bomb1.png',
    'items'  => [
        'simb-up'    => 'images/games/simb-up.png',
        'simb-down'  => 'images/games/simb-down.png',
        'exprad-up'  => 'images/games/exprad-up.png',
        'exprad-down'=> 'images/games/exprad-down.png',
    ],
];
?>
<link rel="stylesheet" href="assets/css/bombertide.css">
<h1>Bombertide</h1>
<div id="bt-wrap" data-config='<?php echo htmlspecialchars(json_encode($config), ENT_QUOTES); ?>'>
  <div id="bt-hud">
    <div class="bt-stat">Score: <span id="bt-score">0</span></div>
    <div class="bt-stat">Level: <span id="bt-level">1</span></div>
    <div class="bt-stat" title="Simultaneous bombs">💣 <span id="bt-bombs">1</span></div>
    <div class="bt-stat" title="Explosion radius">🔥 <span id="bt-range">1</span></div>
  </div>
  <div id="bt-board" tabindex="0"></div>
  <p class="muted">WASD / Arrow keys move · <b>Space</b> drops a bomb. Eliminate all 3 rivals to advance.</p>

  <div id="bt-dpad" class="bt-dpad" aria-label="Touch controls">
    <button type="button" class="bt-dpad-btn up"    data-dir="up"    aria-label="Move up">▲</button>
    <button type="button" class="bt-dpad-btn left"  data-dir="left"  aria-label="Move left">◀</button>
    <button type="button" class="bt-dpad-btn bomb"  data-bomb        aria-label="Drop bomb">💣</button>
    <button type="button" class="bt-dpad-btn right" data-dir="right" aria-label="Move right">▶</button>
    <button type="button" class="bt-dpad-btn down"  data-dir="down"  aria-label="Move down">▼</button>
  </div>
</div>

<div id="bt-overlay" class="bt-overlay hidden">
  <div class="bt-overlay-card">
    <h2 id="bt-overlay-title">Boom!</h2>
    <p id="bt-overlay-msg"></p>
    <p class="bt-final">Final score: <span id="bt-final-score">0</span></p>
    <div class="bt-overlay-actions">
      <button id="bt-retry" class="btn">Retry</button>
      <button id="bt-send" class="btn">Send Score</button>
    </div>
  </div>
</div>

<script defer src="assets/js/bombertide.js"></script>
