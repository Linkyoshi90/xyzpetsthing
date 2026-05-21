<?php
require_once __DIR__.'/../auth.php';
require_login();
require_once __DIR__.'/../lib/pets.php';

$pets = get_user_pets((int)current_user()['id']);
$player_pet = $pets ? $pets[array_rand($pets)] : null;
$player_image = $player_pet ? pet_image_url((string)$player_pet['species_name'], $player_pet['color_name'] ?? null) : 'images/creatures/tengu_f_blue.webp';
if ($player_image === 'images/creatures/tengu_f_blue.png' || !is_file(__DIR__.'/../'.$player_image)) {
    $player_image = 'images/creatures/tengu_f_blue.webp';
}
$player_name = $player_pet
    ? (string)($player_pet['nickname'] ?: $player_pet['species_name'])
    : 'Harmonflap flyer';
?>
<link rel="stylesheet" href="assets/css/harmonflap.css">
<script>
window.harmonflapConfig = <?= json_encode([
    'playerImage' => $player_image,
    'playerName' => $player_name,
    'fallbackImage' => 'images/creatures/tengu_f_blue.webp',
    'backgroundImage' => '/images/bg/flap1.png',
    'sfx' => [
        'flap' => ['assets/sfx/Flap.wav', 'assets/sfx/playerfire.mp3'],
        'death' => ['assets/sfx/death.wav', 'assets/sfx/enemyfire.wav'],
        'pass' => ['assets/sfx/pass.wav', 'assets/sfx/fruitlineclear.wav'],
    ],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script defer src="assets/js/harmonflap.js"></script>

<h1>Harmonflap</h1>
<p class="muted" id="harmonflap-pilot"><?= htmlspecialchars($player_name, ENT_QUOTES, 'UTF-8') ?></p>
<div class="grid two" aria-live="polite">
  <div class="card glass">Score <strong id="harmonflap-score">0</strong></div>
  <div class="card glass">Best <strong id="harmonflap-best">0</strong></div>
</div>

<div class="harmonflap-stage" id="harmonflap-stage" tabindex="0" aria-label="Harmonflap play field">
  <div class="harmonflap-cloud harmonflap-cloud--one"></div>
  <div class="harmonflap-cloud harmonflap-cloud--two"></div>
  <div class="harmonflap-cloud harmonflap-cloud--three"></div>

  <div class="harmonflap-player" id="harmonflap-player" data-wing-flap="0">
    <span class="harmonflap-wing harmonflap-wing--left" aria-hidden="true"></span>
    <img id="harmonflap-creature" src="<?= htmlspecialchars($player_image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($player_name, ENT_QUOTES, 'UTF-8') ?>">
    <span class="harmonflap-wing harmonflap-wing--right" aria-hidden="true"></span>
  </div>

  <div class="harmonflap-overlay" id="harmonflap-overlay">
    <div class="card glass" role="status" aria-live="polite">
      <h2 id="harmonflap-overlay-title">Harmonflap</h2>
      <p id="harmonflap-overlay-copy">Guide your creature through the tide pipes.</p>
      <div>
        <button class="btn" type="button" id="harmonflap-start">Start Flight</button>
        <button class="btn ghost" type="button" id="harmonflap-submit" hidden>Submit Score</button>
      </div>
      <p class="muted" id="harmonflap-submit-status"></p>
    </div>
  </div>
</div>

<div class="card glass">
  <label for="harmonflap-sfx-volume">SFX volume <output id="harmonflap-sfx-volume-value" for="harmonflap-sfx-volume">72%</output></label>
  <input id="harmonflap-sfx-volume" type="range" min="0" max="100" step="1" value="72">
</div>
