<?php
$u = current_user();
$header_pet = null;
$random_event = null;
$page_location = null;
$speech_dialogues = [];
$pet_location_like = null;
if ($u) {
    require_once __DIR__.'/../lib/pets.php';
    require_once __DIR__.'/../lib/city_locations.php';
    require_once __DIR__.'/../lib/pet_preferences.php';
    $pets = get_user_pets($u['id']);
    if ($pets) {
        $header_pet = $pets[array_rand($pets)];
    }
    if ((int)$u['id'] !== 0) {
        require_once __DIR__.'/../lib/random_events.php';
        $random_event = maybe_trigger_random_event($u);
    }
    $page_location = get_page_location($pg ?? '');
    $speech_dialogues = load_speech_dialogues();
    if ($header_pet && $page_location) {
        $pet_location_like = get_pet_location_like_value($header_pet, $page_location);
    }
}
?>
<!doctype html><html data-theme="light"><head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title><?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php if (($pg ?? '') === 'encyclopedia'): ?>
<link rel="stylesheet" href="assets/css/encyclopedia.css">
<?php endif; ?>
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
<script>
    window.appCurrency = Object.assign({}, window.appCurrency, {
        code: <?= json_encode(APP_CURRENCY_CODE, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        shortName: <?= json_encode(APP_CURRENCY_SHORT_NAME, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        longName: <?= json_encode(APP_CURRENCY_LONG_NAME, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
    });
</script>
<?php if($u): ?>
<script>
    window.appLocation = <?= json_encode($page_location, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.appActiveCreature = <?= json_encode($header_pet, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.appSpeechDialogues = <?= json_encode($speech_dialogues, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.appPetLocationPreference = <?= json_encode($pet_location_like, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<?php endif; ?>
<?php
$game_pages = ['fruitstack', 'garden-invaderz', 'runngunner', 'wanted-alive', 'paddle-panic', 'blackjack', 'wheel-of-fate'];
$no_bubble_pages = array_merge($game_pages, ['encyclopedia']);
if (!in_array($pg ?? '', $no_bubble_pages, true)):
?>
<?php if($pg === 'map'): ?>
<script defer src="assets/js/world-map.js"></script>
<?php endif; ?>
<script defer src="assets/js/bubbles.js"></script>
<script defer src="assets/js/speech-bubble.js"></script>
<?php endif; ?>
</head><body>
<header class="nav">
    <div class="nav-left">
        <?php if($u): ?>
        <a href="?pg=inventory">
            <span class="user-name"><?= htmlspecialchars($u['username']) ?></span>
        </a>
        <div class="pet-thumb-wrapper">
            <a href="?pg=pet">
                <?php if($header_pet): ?>
                    <?= render_pet_thumbnail($header_pet, 'pet-thumb', 'Active pet') ?>
                <?php else: ?>
                    <img src="/assets/creatures/placeholder.png" alt="No pet" class="pet-thumb" />
                <?php endif; ?>
            </a>
            <div id="pet-speech-bubble" class="pet-speech-bubble" role="status" aria-live="polite" hidden></div>
        </div>
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
    <a class="btn" href="?pg=user-guide" aria-label="User guide">❓</a>
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
    <?php if (!empty($random_event['actions'])): ?>
    <div class="random-event-actions">
      <?php foreach ($random_event['actions'] as $action): ?>
        <a class="btn" href="<?= htmlspecialchars($action['url'] ?? '#') ?>"><?= htmlspecialchars($action['label'] ?? 'Continue') ?></a>
      <?php endforeach; ?>
    </div>
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
