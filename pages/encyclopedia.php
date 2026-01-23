<?php
require_login();
require_once __DIR__.'/../db.php';

$allowedSpecies = [];
$file = __DIR__ . '/../data-readonly/available_creatures.txt';
if (is_file($file)) {
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $allowedSpecies[] = $line;
    }
}

$species = [];
if ($allowedSpecies) {
    $placeholders = implode(',', array_fill(0, count($allowedSpecies), '?'));
    $species = q(
        "SELECT ps.species_id, ps.species_name, ps.base_hp, ps.base_atk, ps.base_def, ps.base_init, r.region_name " .
        "FROM pet_species ps " .
        "LEFT JOIN regions r ON r.region_id = ps.region_id " .
        "WHERE ps.species_name IN ($placeholders) " .
        "ORDER BY r.region_name, ps.species_name",
        $allowedSpecies
    )->fetchAll(PDO::FETCH_ASSOC);
}

$groupedSpecies = [];
foreach ($species as $entry) {
    $region = $entry['region_name'] ?: 'Unknown';
    $groupedSpecies[$region][] = $entry;
}
$regionNames = array_keys($groupedSpecies);
sort($regionNames, SORT_NATURAL | SORT_FLAG_CASE);
$sortedSpecies = [];
foreach ($regionNames as $regionName) {
    $entries = $groupedSpecies[$regionName];
    usort($entries, function ($a, $b) {
        return strcasecmp($a['species_name'], $b['species_name']);
    });
    $sortedSpecies[$regionName] = $entries;
}
$groupedSpecies = $sortedSpecies;

$encyclopediaPath = __DIR__ . '/../data/creature_encyclopedia.json';
$encyclopediaData = [];
if (is_file($encyclopediaPath)) {
    $decoded = json_decode(file_get_contents($encyclopediaPath), true);
    if (is_array($decoded)) {
        $encyclopediaData = $decoded;
    }
}

$defaultColors = ['Red', 'Blue', 'Green', 'Yellow', 'Purple'];

function slugify(string $str): string
{
    return strtolower(preg_replace('/[^a-z0-9]+/i', '_', $str));
}

$pageIndex = 0;
$regionPageIndex = [];
$creaturePageIndex = [];
foreach ($regionNames as $regionName) {
    $pageIndex++;
    $regionPageIndex[$regionName] = $pageIndex;
    foreach ($groupedSpecies[$regionName] as $entry) {
        $pageIndex++;
        $creaturePageIndex[$entry['species_name']] = $pageIndex;
    }
}
?>

<section class="encyclopedia">
  <div class="encyclopedia__intro">
    <h1>Creature Encyclopedia</h1>
    <p class="muted">Browse the living index of known creatures. Tap the book to open it, then flip to a country or creature entry.</p>
  </div>

  <div class="encyclopedia-backdrop">
    <div class="book-shell is-closed" id="encyclopedia-book">
      <button class="book-cover" id="encyclopedia-cover" type="button" aria-expanded="false">
        <span class="book-cover__title">Bestiary Index</span>
        <span class="book-cover__subtitle">Tap to open</span>
      </button>

      <div class="book-pages" aria-live="polite">
        <div class="book-page" data-page-index="0" aria-hidden="false">
          <header class="book-page__header">
            <h2>Index of Countries</h2>
            <span class="mini"><?= count($regionNames) ?> regions</span>
          </header>
          <p>Select a region to flip directly to its catalog page.</p>
          <div class="book-page__index">
            <?php foreach ($regionNames as $regionName): ?>
              <button type="button" data-page-target="<?= (int)($regionPageIndex[$regionName] ?? 0) ?>">
                <?= htmlspecialchars($regionName) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <?php foreach ($regionNames as $regionName): ?>
          <div class="book-page" data-page-index="<?= (int)($regionPageIndex[$regionName] ?? 0) ?>" aria-hidden="true">
            <header class="book-page__header">
              <h2><?= htmlspecialchars($regionName) ?></h2>
              <button type="button" class="book-page__link" data-page-target="0">Back to index</button>
            </header>
            <p class="muted">Creatures recorded in <?= htmlspecialchars($regionName) ?>.</p>
            <div class="book-page__index">
              <?php foreach ($groupedSpecies[$regionName] as $entry): ?>
                <?php $creatureIndex = $creaturePageIndex[$entry['species_name']] ?? 0; ?>
                <button type="button" data-page-target="<?= (int)$creatureIndex ?>">
                  <?= htmlspecialchars($entry['species_name']) ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>

          <?php foreach ($groupedSpecies[$regionName] as $entry): ?>
            <?php
              $name = $entry['species_name'];
              $entryData = $encyclopediaData[$name] ?? [];
              $colors = $entryData['colors'] ?? $defaultColors;
              $stats = $entryData['stats'] ?? [];
              $hp = $stats['hp'] ?? (int)$entry['base_hp'];
              $atk = $stats['atk'] ?? (int)$entry['base_atk'];
              $def = $stats['def'] ?? (int)$entry['base_def'];
              $init = $stats['init'] ?? (int)$entry['base_init'];
              $description = $entryData['description'] ?? 'Details are being cataloged by the library staff.';
              $slug = slugify($name);
            ?>
            <div class="book-page" data-page-index="<?= (int)($creaturePageIndex[$name] ?? 0) ?>" aria-hidden="true">
              <header class="book-page__header">
                <h2><?= htmlspecialchars($name) ?></h2>
                <button type="button" class="book-page__link" data-page-target="<?= (int)($regionPageIndex[$regionName] ?? 0) ?>">Back to <?= htmlspecialchars($regionName) ?></button>
              </header>
              <div class="book-page__creature">
                <figure>
                  <h3><?= htmlspecialchars($name) ?></h3>
                  <img src="images/<?= htmlspecialchars($slug) ?>_f_blue.webp" alt="<?= htmlspecialchars($name) ?>" onerror="this.src='images/tengu_f_blue.webp'">
                </figure>
                <div class="book-page__stats">
                  <div>
                    <strong>Colors</strong>
                    <div class="book-page__tags">
                      <?php foreach ($colors as $color): ?>
                        <span class="book-page__tag"><?= htmlspecialchars($color) ?></span>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <div>
                    <strong>Stats</strong>
                    <ul>
                      <li>HP: <?= (int)$hp ?></li>
                      <li>ATK: <?= (int)$atk ?></li>
                      <li>DEF: <?= (int)$def ?></li>
                      <li>INIT: <?= (int)$init ?></li>
                    </ul>
                  </div>
                  <div>
                    <strong>Description</strong>
                    <p><?= htmlspecialchars($description) ?></p>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<script>
(() => {
  const book = document.getElementById('encyclopedia-book');
  const cover = document.getElementById('encyclopedia-cover');
  if (!book || !cover) return;

  const pages = Array.from(document.querySelectorAll('.book-page'));
  const buttons = Array.from(document.querySelectorAll('[data-page-target]'));
  let currentPage = 0;
  let flipTimeout = null;

  function setPage(index) {
    const safeIndex = Math.max(0, Math.min(index, pages.length - 1));
    currentPage = safeIndex;
    pages.forEach((page, idx) => {
      page.classList.toggle('is-flipped', idx < safeIndex);
      page.setAttribute('aria-hidden', idx !== safeIndex ? 'true' : 'false');
    });
    book.classList.add('is-flipping');
    if (flipTimeout) window.clearTimeout(flipTimeout);
    flipTimeout = window.setTimeout(() => book.classList.remove('is-flipping'), 700);
  }

  function openBook() {
    if (!book.classList.contains('is-open')) {
      book.classList.remove('is-closed');
      book.classList.add('is-open');
      cover.setAttribute('aria-expanded', 'true');
    }
    setPage(currentPage);
  }

  cover.addEventListener('click', () => {
    currentPage = 0;
    openBook();
  });

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      const target = Number(button.dataset.pageTarget || 0);
      openBook();
      setPage(target);
    });
  });
})();
</script>
