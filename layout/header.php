<?php
$u = current_user();
$header_pet = null;
$random_event = null;
$notification_events = [];
$page_location = null;
$page_back_to_country = null;
$speech_dialogues = [];
$pet_location_like = null;
if ($u) {
    require_once __DIR__.'/../lib/pets.php';
    require_once __DIR__.'/../lib/city_locations.php';
    require_once __DIR__.'/../lib/pet_preferences.php';
    require_once __DIR__.'/../lib/friendships.php';
    $pets = get_user_pets($u['id']);
    if ($pets) {
        $header_pet = $pets[array_rand($pets)];
    }
    if ((int)$u['id'] !== 0) {
        require_once __DIR__.'/../lib/random_events.php';
        $random_event = maybe_trigger_random_event($u, (string)($pg ?? ''));
        $notification_events = array_merge(
            friendship_request_notifications((int)$u['id']),
            random_event_notifications()
        );
    }
    $page_location = get_page_location($pg ?? '');
    $page_back_to_country = get_page_back_to_country_map($pg ?? '');
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
<?php if (($pg ?? '') === 'battle_minigame'): ?>
<?php $battle_css_version = is_file(__DIR__.'/../assets/css/battle-minigame.css') ? filemtime(__DIR__.'/../assets/css/battle-minigame.css') : 1; ?>
<link rel="stylesheet" href="assets/css/battle-minigame.css?v=<?= $battle_css_version ?>">
<?php endif; ?>
<script defer src="assets/js/theme.js"></script>
<script defer src="assets/js/user-menu.js"></script>
<script defer src="assets/js/currency.js"></script>
<?php if (($pg ?? '') === 'battle_minigame'): ?>
<?php $battle_js_version = is_file(__DIR__.'/../assets/js/battle-minigame.js') ? filemtime(__DIR__.'/../assets/js/battle-minigame.js') : 1; ?>
<script defer src="assets/js/battle-minigame.js?v=<?= $battle_js_version ?>"></script>
<?php endif; ?>
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
    if ($scriptDir !== '' && $scriptDir !== '.' && $scriptDir !== '/') {
        $basePath = $scriptDir;
    }
}
$chatActionPath = ($basePath === '') ? '/user_chat_action.php' : $basePath.'/user_chat_action.php';
$notificationActionPath = ($basePath === '') ? '/notification_action.php' : $basePath.'/notification_action.php';
$GLOBALS['app_chat_action_path'] = $chatActionPath;
$appJsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
    $appJsonFlags |= JSON_INVALID_UTF8_SUBSTITUTE;
}
?>
<script>
    window.appPaths = Object.assign({}, window.appPaths, {
        chatAction: <?= json_encode($chatActionPath, $appJsonFlags) ?>,
        notificationAction: <?= json_encode($notificationActionPath, $appJsonFlags) ?>
    });
</script>
<script>
    window.appCurrency = Object.assign({}, window.appCurrency, {
        code: <?= json_encode(APP_CURRENCY_CODE, $appJsonFlags) ?>,
        shortName: <?= json_encode(APP_CURRENCY_SHORT_NAME, $appJsonFlags) ?>,
        longName: <?= json_encode(APP_CURRENCY_LONG_NAME, $appJsonFlags) ?>
    });
</script>
<?php if($u): ?>
<script>
    window.appLocation = <?= json_encode($page_location, $appJsonFlags) ?>;
    window.appActiveCreature = <?= json_encode($header_pet, $appJsonFlags) ?>;
    window.appSpeechDialogues = <?= json_encode($speech_dialogues, $appJsonFlags) ?>;
    window.appPetLocationPreference = <?= json_encode($pet_location_like, $appJsonFlags) ?>;
</script>
<?php endif; ?>
<?php
$game_pages = ['fruitstack', 'harmonflap', 'kid-puzzle', 'garden-invaderz', 'runngunner', 'wanted-alive', 'paddle-panic', 'blackjack', 'cups-and-balls', 'wheel-of-fate', 'battle_minigame', 'drop_game'];
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
                    <img src="images/creatures/tengu_f_blue.webp" alt="No pet" class="pet-thumb" />
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
<?php $notification_count = count($notification_events); ?>
<section id="notifications-panel" class="notifications-panel" aria-label="Notifications" aria-hidden="true">
  <div class="notifications-panel__header">
    <span class="notifications-panel__title">Notifications</span>
    <span class="notifications-panel__tools">
      <span class="notifications-panel__status <?= $notification_count > 0 ? 'notifications-panel__status--active' : '' ?>">
        <?= $notification_count > 0 ? htmlspecialchars($notification_count.' logged') : 'All clear' ?>
      </span>
      <button id="notifications-close" class="notifications-panel__close" type="button" aria-label="Close notification panel">×</button>
    </span>
  </div>
  <div class="notifications-panel__body" role="list">
    <?php if ($notification_events): ?>
      <?php foreach ($notification_events as $index => $notification): ?>
      <?php $notification_id = (string)($notification['id'] ?? ''); ?>
      <?php $notification_title = (string)($notification['title'] ?? 'Random Encounter'); ?>
      <?php $notification_dismissible = array_key_exists('dismissible', $notification) ? (bool)$notification['dismissible'] : true; ?>
      <?php
        $notification_variant = strtolower((string)($notification['variant'] ?? ''));
        $notification_classes = ['notification-item'];
        if ($index === 0) {
            $notification_classes[] = 'notification-item--fresh';
        }
        if ($notification_variant === 'map_reveal') {
            $notification_classes[] = 'notification-item--map-reveal';
        }
      ?>
      <article
        class="<?= htmlspecialchars(implode(' ', $notification_classes)) ?>"
        role="listitem"
        data-notification-id="<?= htmlspecialchars($notification_id) ?>"
        <?php if ($notification_variant !== ''): ?>data-notification-variant="<?= htmlspecialchars($notification_variant) ?>"<?php endif; ?>
      >
        <?php if ($notification_dismissible): ?>
        <button
          class="notification-item__dismiss"
          type="button"
          data-notification-dismiss="<?= htmlspecialchars($notification_id) ?>"
          aria-label="Dismiss notification: <?= htmlspecialchars($notification_title) ?>"
        >×</button>
        <?php endif; ?>
        <span class="notification-item__icon" aria-hidden="true">✦</span>
        <div class="notification-item__copy">
          <strong><?= htmlspecialchars($notification_title) ?></strong>
          <small>
            <?php if (!empty($notification['time_label'])): ?>
              <span class="notification-item__time"><?= htmlspecialchars($notification['time_label']) ?></span>
            <?php endif; ?>
            <?= htmlspecialchars($notification['message'] ?? '') ?>
          </small>
          <?php if (!empty($notification['details']) && is_array($notification['details'])): ?>
          <ul class="notification-item__details">
            <?php foreach ($notification['details'] as $detail): ?>
            <li><?= htmlspecialchars((string)$detail) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
          <?php if (!empty($notification['actions']) && is_array($notification['actions'])): ?>
          <span class="notification-item__actions">
            <?php foreach ($notification['actions'] as $action): ?>
            <?php if (!empty($action['post_action'])): ?>
            <button
              class="notification-item__action"
              type="button"
              data-notification-action="<?= htmlspecialchars((string)$action['post_action']) ?>"
              <?php if (!empty($action['request_id'])): ?>data-friend-request-id="<?= (int)$action['request_id'] ?>"<?php endif; ?>
            ><?= htmlspecialchars($action['label'] ?? 'Open') ?></button>
            <?php else: ?>
            <a
              href="<?= htmlspecialchars($action['url'] ?? '#') ?>"
              <?php if (!empty($action['dismiss_on_click'])): ?>data-notification-dismiss-on-click="<?= htmlspecialchars($notification_id) ?>"<?php endif; ?>
            ><?= htmlspecialchars($action['label'] ?? 'Open') ?></a>
            <?php endif; ?>
            <?php endforeach; ?>
          </span>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    <?php else: ?>
    <article class="notification-item notification-item--empty" role="listitem">
      <span class="notification-item__icon" aria-hidden="true">◌</span>
      <div class="notification-item__copy">
        <strong>No notifications yet</strong>
        <small>The harbor wire is quiet for now.</small>
      </div>
    </article>
    <?php endif; ?>
  </div>
</section>
<div class="notifications-dock">
  <button
    id="notifications-toggle"
    class="notifications-toggle"
    type="button"
    aria-label="Open notifications"
    aria-expanded="false"
    aria-controls="notifications-panel"
  >
    <span class="notifications-bell" aria-hidden="true">🔔</span>
    <?php if ($notification_count > 0): ?>
    <span class="notifications-count" aria-hidden="true"><?= (int)$notification_count ?></span>
    <?php endif; ?>
  </button>
</div>
<?php if(!empty($random_event)): ?>
<?php
  $random_event_variant = strtolower((string)($random_event['variant'] ?? ''));
  $random_event_overlay_classes = ['random-event-overlay'];
  $random_event_modal_classes = ['random-event-modal'];
  if ($random_event_variant === 'map_reveal') {
      $random_event_overlay_classes[] = 'random-event-overlay--map-reveal';
      $random_event_modal_classes[] = 'random-event-modal--map-reveal';
  }
?>
<div
  class="<?= htmlspecialchars(implode(' ', $random_event_overlay_classes)) ?>"
  id="random-event-overlay"
  role="dialog"
  aria-modal="true"
  data-random-event-notification-id="<?= htmlspecialchars((string)($random_event['notification_id'] ?? '')) ?>"
  <?php if ($random_event_variant !== ''): ?>data-random-event-variant="<?= htmlspecialchars($random_event_variant) ?>"<?php endif; ?>
>
  <div class="<?= htmlspecialchars(implode(' ', $random_event_modal_classes)) ?>">
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
        <a
          class="btn"
          href="<?= htmlspecialchars($action['url'] ?? '#') ?>"
          <?php if (!empty($action['dismiss_on_click'])): ?>data-random-event-dismiss-on-click="1"<?php endif; ?>
        ><?= htmlspecialchars($action['label'] ?? 'Continue') ?></a>
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
  const notificationId = overlay.getAttribute('data-random-event-notification-id') || '';
  let dismissRequested = false;
  const dismissEventNotification = () => {
    if (!notificationId || dismissRequested) return Promise.resolve(null);
    dismissRequested = true;
    if (typeof window.appDismissNotification === 'function') {
      return window.appDismissNotification(notificationId).catch((error) => {
        if (typeof window.reportAppError === 'function') {
          window.reportAppError(error && error.message ? error.message : 'Notification could not be dismissed.');
        }
        return null;
      });
    }

    const actionPath = (window.appPaths && window.appPaths.notificationAction) || 'notification_action.php';
    return fetch(actionPath, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Accept': 'application/json'
      },
      body: new URLSearchParams({
        action: 'dismiss',
        id: notificationId
      })
    }).catch((error) => {
      if (typeof window.reportAppError === 'function') {
        window.reportAppError(error && error.message ? error.message : 'Notification could not be dismissed.');
      }
      return null;
    });
  };
  const dismiss = () => {
    dismissEventNotification();
    overlay.remove();
  };
  if (closeBtn) closeBtn.addEventListener('click', dismiss);
  overlay.querySelectorAll('[data-random-event-dismiss-on-click]').forEach((link) => {
    link.addEventListener('click', (ev) => {
      const href = link.href;
      if (!href || href.endsWith('#')) return;
      ev.preventDefault();
      dismissEventNotification().finally(() => {
        overlay.remove();
        window.location.assign(href);
      });
    });
  });
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
<main class="container <?= ($pg === 'map') ? 'map-container' : '' ?> <?= ($pg === 'cups-and-balls') ? 'cups-page-container' : '' ?>">
<?php if ($page_back_to_country): ?>
<div class="country-subpage-back-wrap">
  <a class="country-subpage-back-link" href="<?= htmlspecialchars($page_back_to_country['href']) ?>">
    <?= htmlspecialchars($page_back_to_country['label']) ?>
  </a>
</div>
<?php endif; ?>
