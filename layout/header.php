<?php $u = current_user(); ?>
<!doctype html><html data-theme="light"><head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title><?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<script defer src="assets/js/theme.js"></script>
</head><body>
<header class="nav">
  <a class="brand" href="?pg=<?= $u?'main':'login' ?>">Harmontide</a>
  <div class="nav-right">
    <?php if($u): ?>
    <nav>
      <a href="?pg=main">Pets</a>
      <a href="?pg=create_pet">Create</a>
      <a href="?pg=inventory">Inventory</a>
      <a href="?pg=map">Map</a>
      <a href="?pg=logout">Logout</a>
    </nav>
    <span class="who">hi, <?= htmlspecialchars($u['username']) ?></span>
    <?php endif; ?>
    <button id="theme-toggle" class="btn" type="button">🌓</button>
  </div>
</header>
<main class="container">
