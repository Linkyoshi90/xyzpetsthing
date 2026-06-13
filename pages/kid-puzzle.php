<?php
require_login();

$kidPuzzleCssVersion = is_file(__DIR__.'/../assets/css/kid-puzzle.css') ? filemtime(__DIR__.'/../assets/css/kid-puzzle.css') : 1;
$kidPuzzleJsVersion = is_file(__DIR__.'/../assets/js/kid-puzzle.js') ? filemtime(__DIR__.'/../assets/js/kid-puzzle.js') : 1;
$kidPuzzleJsonFlags = JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
?>
<link rel="stylesheet" href="assets/css/kid-puzzle.css?v=<?= $kidPuzzleCssVersion ?>">
<script>
window.kidPuzzleConfig = <?= json_encode([
    'image' => '/images/games/kid.webp',
    'music' => [
        '/assets/music/kid1.wav',
        '/assets/music/kid2.wav',
        '/assets/music/kid3.wav',
        '/assets/music/kid4.wav',
    ],
    'pieces' => [6, 12, 36, 80, 200, 1600],
    'defaultPieces' => 12,
], $kidPuzzleJsonFlags) ?>;
</script>
<script defer src="assets/js/kid-puzzle.js?v=<?= $kidPuzzleJsVersion ?>"></script>

<h1>Picture Puzzle</h1>
<div class="kid-puzzle-shell">
  <div class="kid-puzzle-topbar">
    <div class="kid-puzzle-piece-control" role="group" aria-label="Pieces">
      <span class="kid-puzzle-label">Pieces</span>
      <div class="kid-puzzle-piece-buttons">
        <button class="btn ghost" type="button" data-kid-puzzle-pieces="6">6</button>
        <button class="btn ghost" type="button" data-kid-puzzle-pieces="12">12</button>
        <button class="btn ghost" type="button" data-kid-puzzle-pieces="36">36</button>
        <button class="btn ghost" type="button" data-kid-puzzle-pieces="80">80</button>
        <button class="btn ghost" type="button" data-kid-puzzle-pieces="200">200</button>
        <button class="btn ghost" type="button" data-kid-puzzle-pieces="1600">1600</button>
      </div>
    </div>
    <div class="kid-puzzle-actions">
      <button class="btn" type="button" id="kid-puzzle-shuffle">Shuffle</button>
      <button class="btn ghost" type="button" id="kid-puzzle-preview-toggle" aria-pressed="false">Preview</button>
    </div>
  </div>

  <div class="kid-puzzle-stats" aria-live="polite">
    <div class="card glass">Moves <strong id="kid-puzzle-moves">0</strong></div>
    <div class="card glass">Pieces <strong id="kid-puzzle-piece-count">12</strong></div>
    <div class="card glass">Best <strong id="kid-puzzle-best">-</strong></div>
  </div>

  <div class="kid-puzzle-layout">
    <div class="kid-puzzle-board-wrap">
      <canvas id="kid-puzzle-board" tabindex="0" aria-label="Picture puzzle board"></canvas>
      <div class="kid-puzzle-board-message" id="kid-puzzle-message" role="status">Loading...</div>
    </div>
    <div class="kid-puzzle-preview" id="kid-puzzle-preview" hidden>
      <img src="images/games/kid.webp" alt="Puzzle preview" id="kid-puzzle-preview-image">
    </div>
  </div>

  <div class="card glass kid-puzzle-audio">
    <button class="btn ghost" type="button" id="kid-puzzle-music-toggle" aria-pressed="true">Music On</button>
    <label for="kid-puzzle-volume">Music volume <output id="kid-puzzle-volume-value" for="kid-puzzle-volume">45%</output></label>
    <input id="kid-puzzle-volume" type="range" min="0" max="100" step="1" value="45">
  </div>
</div>
