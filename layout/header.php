<?php
$u = current_user();
$header_pet = null;
if ($u) {
    require_once __DIR__.'/../lib/pets.php';
    $pets = get_user_pets($u['id']);
    if ($pets) {
        $header_pet = $pets[array_rand($pets)];
    }
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
<main class="container">