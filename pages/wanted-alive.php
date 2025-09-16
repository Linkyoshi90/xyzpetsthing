<?php
require_once __DIR__.'/../auth.php';
require_login();

$creatures_file = __DIR__.'/../available_creatures.txt';
$images_dir = __DIR__.'/../images';

function hunt_slugify(string $name): string {
    $slug = strtolower($name);
    $slug = str_replace([' ', '-', '–', '—'], '_', $slug);
    $slug = str_replace(["'", '’', '“', '”'], '', $slug);
    $slug = preg_replace('/[^a-z0-9_]/', '', $slug);
    $slug = preg_replace('/_+/', '_', $slug);
    return $slug;
}

$creature_names = [];
if (file_exists($creatures_file)) {
    $lines = file($creatures_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed !== '') {
            $creature_names[] = $trimmed;
        }
    }
}

$image_files = [];
if (is_dir($images_dir)) {
    $scan = scandir($images_dir) ?: [];
    foreach ($scan as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (preg_match('/\.(png|jpe?g|webp)$/i', $file)) {
            $image_files[] = $file;
        }
    }
}

$creature_variants = [];
foreach ($creature_names as $name) {
    $slug = hunt_slugify($name);
    if ($slug === '') {
        continue;
    }
    $matches = [];
    foreach ($image_files as $file) {
        $lower = strtolower($file);
        if (strpos($lower, $slug.'_') === 0) {
            $matches[] = 'images/'.$file;
        }
    }
    if ($matches) {
        natsort($matches);
        $creature_variants[$name] = array_values($matches);
    }
}
?>
<link rel="stylesheet" href="assets/css/wanted-alive.css">
<script>
window.wantedAliveData = <?php echo json_encode(['variants' => $creature_variants], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
</script>
<script defer src="assets/js/wanted-alive.js"></script>
<div class="wanted-alive-wrapper">
  <h1>Wanted Alive</h1>
  <p class="hunt-subtitle">Study the wanted poster, spot the creature before the timer runs out, and bank the leftover seconds.</p>
  <div class="hunt-board" id="hunt-board">
    <div class="wanted-poster">
      <p class="wanted-title">WANTED</p>
      <img src="" alt="Wanted creature" class="wanted-image" id="wanted-image">
      <p class="wanted-name" id="wanted-name"></p>
      <div class="hunt-timer" id="hunt-timer-label">--:--</div>
      <div class="timer-bar">
        <div class="timer-progress" id="hunt-timer-progress"></div>
      </div>
    </div>
    <div class="hunt-overlay hidden" id="hunt-overlay"><span></span></div>
  </div>
  <div class="hunt-controls">
    <p class="hunt-round-info" id="hunt-round">Stage 0</p>
    <p class="hunt-score" id="hunt-score">Score: 0.0</p>
    <p class="hunt-status" id="hunt-status">Press start to begin the endless chase.</p>
    <button class="btn" type="button" id="hunt-start">Start Hunt</button>
  </div>
</div>