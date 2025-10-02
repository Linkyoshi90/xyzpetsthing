﻿<?php
$u = current_user();
$header_pet = null;
$random_event = null;
if ($u) {
    require_once __DIR__.'/../lib/pets.php';
    require_once __DIR__.'/../lib/random_events.php';
    $pets = get_user_pets($u['id']);
    if ($pets) {
        $header_pet = $pets[array_rand($pets)];
    }
    $random_event = maybe_trigger_random_event($u);
}
?>
<!doctype html><html data-theme="light"><head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title><?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<script defer src="assets/js/theme.js"></script>
<script defer src="assets/js/user-menu.js"></script>
<script defer src="assets/js/currency.js"></script>
<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
$appRoot = realpath(__DIR__.'/..');
$basePath = '';
if ($documentRoot && $appRoot) {
    $normalizedRoot = str_replace('\\', '/', realpath($documentRoot));
    $normalizedApp = str_replace('\\', '/', $appRoot);
    if ($normalizedRoot && strncmp($normalizedApp, $normalizedRoot, strlen($normalizedRoot)) === 0) {
        $relative = trim(substr($normalizedApp, strlen($normalizedRoot)), '/');
        $basePath = $relative === '' ? '' : '/'.$relative;
    }
}
if ($basePath === '') {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = str_replace('\\', '/', rtrim(dirname($scriptName), '/'));
    if ($scriptDir !== '' && $scriptDir !== '.') {
        $basePath = $scriptDir;
    }
}
$chatActionPath = ($basePath === '') ? '/user_chat_action.php' : $basePath.'/user_chat_action.php';
$GLOBALS['app_chat_action_path'] = $chatActionPath;
?>
<script>
    window.appPaths = Object.assign({}, window.appPaths, {
        chatAction: <?= json_encode($chatActionPath, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
    });
</script>
<?php
$game_pages = ['fruitstack', 'garden-invaderz', 'runngunner', 'wanted-alive', 'paddle-panic', 'blackjack', 'wheel-of-fate'];
if (!in_array($pg ?? '', $game_pages)):
?>
<?php if($pg === 'map'): ?>
<script defer src="assets/js/world-map.js"></script>
<?php endif; ?>
<script defer src="assets/js/bubbles.js"></script>
<?php endif; ?>
</head><body>
<header class="nav">
    <div class="nav-left">
        <?php if($u): ?>
        <a href="?pg=inventory">
            <span class="user-name"><?= htmlspecialchars($u['username']) ?></span>
        </a>
        <a href="?pg=pet">
            <?php if($header_pet): ?>
                <img src="<?= htmlspecialchars(pet_image_url($header_pet['species_name'], $header_pet['color_name'])) ?>" alt="Active pet" class="pet-thumb" />
            <?php else: ?>
                <img src="/assets/creatures/placeholder.png" alt="No pet" class="pet-thumb" />
            <?php endif; ?>
        </a>
        <?php else: ?>
        <?php endif; ?>
    <a href="?pg=<?= $u?'main':'login' ?>">
      <img src="images/np-logo-R.svg" alt="Harmontide" class="site-banner" />
    </a>
    <?php if($u): ?>
    <div class="currency-display">
      <span class="currency cash">💰 <span id="cash-balance"><?= (int)($u['cash'] ?? 0) ?></span></span>
      <span class="currency gems">💎 <span id="gems-balance"><?= (int)($u['gems'] ?? 0) ?></span></span>
    </div>
    <?php endif; ?>
  </div>
  <div class="nav-right">
    <nav>
      <a href="?pg=games">🎮 games</a>
      <a href="?pg=bank">🏦 bank</a>
      <a href="?pg=map">🗺️ explore</a>
      <a href="?pg=vote">🗳️ vote</a>
      <a href="?pg=notifications" aria-label="notifications">🔔</a>
    </nav>
    <?php if($u): ?>
    <div class="user-menu">
      <button id="user-menu-toggle" class="btn" type="button">🙂</button>
      <ul id="user-menu" class="user-menu-list">
        <a href="?pg=friends">👥 friends</a>
        <a href="?pg=options">🔧 options</a>
        <a href="?pg=logout">🚪 logout</a>
      </ul>
    </div>
    <?php endif; ?>
    <button id="theme-toggle" class="btn" type="button">🌓</button>
  </div>
</header>
<?php if(!empty($random_event)): ?>
<div class="random-event-overlay" id="random-event-overlay" role="dialog" aria-modal="true">
  <div class="random-event-modal">
    <button type="button" class="random-event-close" aria-label="Close event">✕</button>
    <h2><?= htmlspecialchars($random_event['title']) ?></h2>
    <p><?= nl2br(htmlspecialchars($random_event['message'])) ?></p>
    <?php if (!empty($random_event['details'])): ?>
    <ul>
      <?php foreach ($random_event['details'] as $detail): ?>
      <li><?= htmlspecialchars($detail) ?></li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</div>
<script>
window.addEventListener('DOMContentLoaded', function () {
  const overlay = document.getElementById('random-event-overlay');
  if (!overlay) return;
  const closeBtn = overlay.querySelector('.random-event-close');
  const dismiss = () => overlay.remove();
  if (closeBtn) closeBtn.addEventListener('click', dismiss);
  overlay.addEventListener('click', (ev) => {
    if (ev.target === overlay) {
      dismiss();
    }
  });
  <?php if (!empty($random_event['balances'])): ?>
  if (typeof window.updateCurrencyDisplay === 'function') {
    window.updateCurrencyDisplay(<?= json_encode($random_event['balances']) ?>);
  }
  <?php endif; ?>
});
</script>
<?php endif; ?>
<main class="container <?= ($pg === 'map') ? 'map-container' : '' ?>">