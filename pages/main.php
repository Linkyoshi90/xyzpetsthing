<?php
require_login();
require_once __DIR__.'/../lib/pets.php';

$uid = current_user()['id'];
$pets = get_user_pets($uid);
$featured_pet = $pets[0] ?? null;

$pet_count = count($pets);
$can_create_pet = $pet_count < 4;
$featured_name = $featured_pet ? ($featured_pet['nickname'] ?: $featured_pet['species_name']) : 'No companion yet';
$featured_species = $featured_pet ? ($featured_pet['species_name'] ?? 'Creature') : 'Create your first creature';
$featured_level = (int)($featured_pet['level'] ?? 0);
$featured_hp_current = (int)($featured_pet['hp_current'] ?? 0);
$featured_hp_max = (int)($featured_pet['hp_max'] ?? ($featured_pet['hp_current'] ?? 0));
$featured_hunger = max(0, min(100, (int)($featured_pet['hunger'] ?? 0)));
$featured_happiness = max(0, min(100, (int)($featured_pet['happiness'] ?? 0)));
$featured_intelligence = max(0, min(100, (int)($featured_pet['intelligence'] ?? 0)));
$featured_hp_percent = $featured_hp_max > 0 ? max(0, min(100, (int)round(($featured_hp_current / $featured_hp_max) * 100))) : 0;
?>

<section class="aero-dashboard">
  <section class="aero-hero">
    <img class="aero-hero__map" src="images/maps/harmontide.png" alt="Harmontide world map">
    <div class="aero-hero__copy">
      <span class="aero-kicker">Harbor dashboard</span>
      <h1>Morning Tide</h1>
      <p>Check your active creature, jump into daily routes, and keep the whole Harmontide loop close at hand.</p>
      <div class="aero-action-strip">
        <a class="btn aero-primary" href="?pg=map">Explore map</a>
        <a class="btn" href="?pg=games">Play games</a>
        <a class="btn ghost" href="?pg=inventory">Open bag</a>
      </div>
    </div>
  </section>

  <section class="aero-dashboard-grid">
    <article class="aero-featured-pet glass">
      <div class="aero-pet-stage">
        <?php if ($featured_pet): ?>
          <?= render_pet_thumbnail($featured_pet, 'aero-pet-art', $featured_name) ?>
        <?php else: ?>
          <img class="aero-pet-art" src="images/creatures/tengu_f_blue.webp" alt="Creature placeholder">
        <?php endif; ?>
      </div>
      <div class="aero-panel-heading">
        <div>
          <span class="aero-label">Active creature</span>
          <h2><?= htmlspecialchars($featured_name) ?></h2>
          <p><?= htmlspecialchars($featured_species) ?></p>
        </div>
        <?php if ($featured_pet): ?>
          <span class="aero-chip">Level <?= $featured_level ?></span>
        <?php endif; ?>
      </div>
      <?php if ($featured_pet): ?>
      <div class="aero-meter-list">
        <div class="aero-meter">
          <span><strong>HP</strong><em><?= $featured_hp_current ?> / <?= $featured_hp_max ?></em></span>
          <i style="--value: <?= $featured_hp_percent ?>%"></i>
        </div>
        <div class="aero-meter">
          <span><strong>Hunger</strong><em><?= $featured_hunger ?>%</em></span>
          <i style="--value: <?= $featured_hunger ?>%"></i>
        </div>
        <div class="aero-meter">
          <span><strong>Happiness</strong><em><?= $featured_happiness ?>%</em></span>
          <i style="--value: <?= $featured_happiness ?>%"></i>
        </div>
        <div class="aero-meter">
          <span><strong>Intelligence</strong><em><?= $featured_intelligence ?>%</em></span>
          <i style="--value: <?= $featured_intelligence ?>%"></i>
        </div>
      </div>
      <div class="aero-quick-actions">
        <a href="?pg=pet" aria-label="Open creature page"><span>+</span><strong>Care</strong><small>Stats</small></a>
        <a href="?pg=dress" aria-label="Open dress page"><span>*</span><strong>Dress</strong><small>Style</small></a>
        <a href="?pg=petting&id=<?= (int)$featured_pet['pet_instance_id'] ?>" aria-label="Open petting mode"><span>&gt;</span><strong>Pet</strong><small>Bond</small></a>
      </div>
      <?php else: ?>
      <p class="muted">Create your first companion to fill this glassy little dock.</p>
      <a class="btn aero-primary" href="?pg=create_pet">Create pet</a>
      <?php endif; ?>
    </article>

    <section class="aero-home-stack">
      <div class="aero-stat-grid">
        <article class="aero-stat-card">
          <span class="aero-label">Companions</span>
          <strong><?= $pet_count ?> / 4</strong>
          <small><?= $can_create_pet ? 'Room for a new friend' : 'Stable is full' ?></small>
        </article>
        <article class="aero-stat-card">
          <span class="aero-label">Daily games</span>
          <strong>Ready</strong>
          <small>Fruitstack, cards, puzzles, and more</small>
        </article>
        <article class="aero-stat-card">
          <span class="aero-label">Routes</span>
          <strong>Open</strong>
          <small>Map, shops, libraries, and regional stories</small>
        </article>
      </div>

      <section class="aero-card-grid">
        <article class="aero-nav-card">
          <span class="aero-icon">></span>
          <h3>Explore Harmontide</h3>
          <p>Jump from the world map into regions, country pages, shops, fishing spots, and adventures.</p>
          <a class="btn" href="?pg=map">Open map</a>
        </article>
        <article class="aero-nav-card">
          <span class="aero-icon">*</span>
          <h3>Arcade Loop</h3>
          <p>Keep the game hub scan-friendly with reward paths and play actions right up front.</p>
          <a class="btn" href="?pg=games">Open games</a>
        </article>
        <article class="aero-nav-card">
          <span class="aero-icon">$</span>
          <h3>Bank & Bag</h3>
          <p>Check balances, inventory finds, and useful items before you head back out.</p>
          <a class="btn" href="?pg=bank">Open bank</a>
        </article>
      </section>
    </section>
  </section>

  <section class="aero-companion-strip">
    <div class="aero-section-heading">
      <div>
        <span class="aero-label">Your pets</span>
        <h2>Creature Dock</h2>
      </div>
      <?php if ($can_create_pet): ?>
        <a class="btn ghost" href="?pg=create_pet">Create pet</a>
      <?php endif; ?>
    </div>
    <?php if ($pets): ?>
    <div class="grid three aero-pet-grid">
      <?php foreach ($pets as $p): ?>
        <?php
          $pet_name = $p['nickname'] ?: $p['species_name'];
          $pet_hp_current = (int)($p['hp_current'] ?? 0);
          $pet_hp_max = (int)($p['hp_max'] ?? ($p['hp_current'] ?? 0));
        ?>
        <a class="card glass aero-pet-card" href="?pg=pet&id=<?= (int)$p['pet_instance_id'] ?>">
          <?= render_pet_thumbnail($p, 'thumb', $pet_name) ?>
          <h3><?= htmlspecialchars($pet_name) ?></h3>
          <p><?= htmlspecialchars($p['species_name']) ?></p>
          <span class="aero-chip">Level <?= (int)$p['level'] ?> / HP <?= $pet_hp_current ?>-<?= $pet_hp_max ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p class="muted">No pets yet. Create your first companion to begin.</p>
    <?php endif; ?>
  </section>
</section>
