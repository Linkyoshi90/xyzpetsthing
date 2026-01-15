<?php
require_login();
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/input.php';

$uid = current_user()['id'];
$regionInitial = $regionInitial ?? '';

$regionDirectory = [
    'aa' => 'Aegia Aeterna',
    'bm' => 'Baharamandal',
    'br' => 'Bretonreach',
    'cc' => 'Crescent Caliphate',
    'esd' => 'Eagle Serpent Dominion',
    'esl' => 'Eretz-Shalem League',
    'gc' => 'Gran Columbia',
    'h' => 'Hammurabia',
    'ie' => 'Itzam Empire',
    'k' => 'Kemet',
    'ldk' => 'Lotus-Dragon Kingdom',
    'nh' => 'Nornheim',
    'rsc' => 'Red Sun Commonwealth',
    'rl' => 'Rheinland',
    'rt' => 'Rodinian Tsardom',
    'sie' => 'Sapa Inti Empire',
    'sc' => 'Sila Council',
    'stap' => 'Sovereign Tribes of the Ancestral Plains',
    'srl' => 'Spice Route League',
    'urb' => 'United free Republic of Borealia',
    'xm' => 'Xochimex',
    'ynk' => 'Yamanokubo',
    'yn' => 'Yara Nations',
];

if (!$regionInitial || !isset($regionDirectory[$regionInitial])) {
    echo '<p class="err">This paint shack could not be found.</p>';
    return;
}

$regionName = $regionDirectory[$regionInitial];
$region = q(
    'SELECT region_id, region_name FROM regions WHERE region_name = ? LIMIT 1',
    [$regionName]
)->fetch(PDO::FETCH_ASSOC);

if (!$region) {
    echo '<p class="err">This paint shack is not available right now.</p>';
    return;
}

$regionId = (int)$region['region_id'];

$messages = ['error' => null, 'success' => null];

$colors = q('SELECT color_id, color_name FROM pet_colors ORDER BY color_id')->fetchAll(PDO::FETCH_ASSOC);
$colorNameToId = [];
foreach ($colors as $c) {
    $colorNameToId[strtolower($c['color_name'])] = (int)$c['color_id'];
}

$paintCategoryId = q(
    "SELECT category_id FROM item_categories WHERE category_name = 'Paint' LIMIT 1"
)->fetchColumn();

$paintItemColors = [];
if ($paintCategoryId) {
    $paintItems = q(
        'SELECT item_name FROM items WHERE category_id = ?',
        [$paintCategoryId]
    )->fetchAll(PDO::FETCH_COLUMN);

    $colorLookup = [];
    foreach ($colors as $c) {
        $colorLookup[strtolower($c['color_name'])] = $c['color_name'];
    }

    foreach ($paintItems as $paintName) {
        $paintKey = strtolower($paintName);
        foreach ($colorLookup as $colorKey => $colorDisplay) {
            if (strpos($paintKey, $colorKey) !== false) {
                $paintItemColors[$paintKey] = $colorDisplay;
                break;
            }
        }
    }
}

$resolvePaintColor = function (string $itemName) use ($paintItemColors, $colorNameToId): ?array {
    $key = strtolower($itemName);
    if (!isset($paintItemColors[$key])) {
        return null;
    }
    $targetName = $paintItemColors[$key];
    $colorId = $colorNameToId[strtolower($targetName)] ?? null;
    if (!$colorId) {
        return null;
    }
    return ['color_id' => $colorId, 'color_name' => $targetName];
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paint_pet'])) {
    $petId = input_int($_POST['pet_id'] ?? 0, 1);
    $itemId = input_int($_POST['item_id'] ?? 0, 1);

    $pet = q(
        "SELECT pi.pet_instance_id, pi.nickname, pi.color_id, ps.species_name, ps.region_id "
        . "FROM pet_instances pi "
        . "JOIN pet_species ps ON ps.species_id = pi.species_id "
        . "WHERE pi.pet_instance_id = ? AND pi.owner_user_id = ?",
        [$petId, $uid]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$pet) {
        $messages['error'] = 'Could not find that pet.';
    } elseif ((int)$pet['region_id'] !== $regionId) {
        $messages['error'] = 'This pet is from a different region.';
    } else {
        $itemRow = q(
            "SELECT ui.quantity, i.item_name, i.category_id FROM user_inventory ui "
            . "JOIN items i ON i.item_id = ui.item_id "
            . "WHERE ui.user_id = ? AND ui.item_id = ?",
            [$uid, $itemId]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$itemRow) {
            $messages['error'] = 'That paint is not in your inventory.';
        } elseif ((int)$itemRow['category_id'] !== (int)$paintCategoryId) {
            $messages['error'] = 'That item is not a valid paint.';
        } elseif ((int)$itemRow['quantity'] <= 0) {
            $messages['error'] = 'You are out of that paint.';
        } else {
            $targetColor = $resolvePaintColor($itemRow['item_name']);
            if (!$targetColor) {
                $messages['error'] = 'This paint cannot be used on pets.';
            } else {
                q(
                    "UPDATE pet_instances SET color_id = ? WHERE pet_instance_id = ? AND owner_user_id = ?",
                    [$targetColor['color_id'], $petId, $uid]
                );
                if ((int)$itemRow['quantity'] > 1) {
                    q(
                        "UPDATE user_inventory SET quantity = quantity - 1 WHERE user_id = ? AND item_id = ?",
                        [$uid, $itemId]
                    );
                } else {
                    q('DELETE FROM user_inventory WHERE user_id = ? AND item_id = ?', [$uid, $itemId]);
                }
                $petName = $pet['nickname'] ?: $pet['species_name'];
                $messages['success'] = sprintf('%s is now painted %s!', $petName, $targetColor['color_name']);
            }
        }
    }
}

$userPaints = [];
if ($paintCategoryId) {
    $userInventory = q(
        "SELECT ui.item_id, ui.quantity, i.item_name, i.category_id FROM user_inventory ui "
        . "JOIN items i ON i.item_id = ui.item_id "
        . "WHERE ui.user_id = ?",
        [$uid]
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($userInventory as $item) {
        if ((int)$item['category_id'] === (int)$paintCategoryId) {
            $userPaints[] = $item;
        }
    }
}

$usablePaints = [];
foreach ($userPaints as $paint) {
    $resolved = $resolvePaintColor($paint['item_name']);
    if ($resolved) {
        $usablePaints[] = array_merge($paint, $resolved);
    }
}

$pets = get_user_pets($uid);
$regionPets = array_values(array_filter(
    $pets,
    static fn($p) => (int)($p['region_id'] ?? 0) === $regionId
));

$previewPet = $regionPets[0] ?? null;
$previewColor = $previewPet['color_name'] ?? 'Blue';
$previewImage = $previewPet ? pet_image_url($previewPet['species_name'], $previewColor) : 'images/tengu_f_blue.png';

$regionCreatures = q(
    'SELECT species_name FROM pet_species WHERE region_id = ? ORDER BY species_name',
    [$regionId]
)->fetchAll(PDO::FETCH_COLUMN);

$slugify = static function (string $str): string {
    return strtolower(preg_replace('/[^a-z0-9]+/i', '_', $str));
};

$creatureVariants = [];
foreach ($regionCreatures as $creatureName) {
    $slug = $slugify($creatureName);
    $variants = [];

    foreach (glob(__DIR__ . '/../images/' . $slug . '_*_*.webp') as $file) {
        $base = basename($file, '.webp');
        if (preg_match('/^' . preg_quote($slug, '/') . '_([mf])_(.+)$/i', $base, $m)) {
            $variants[] = strtolower($m[1] . '_' . $m[2]);
        }
    }

    $variants = array_values(array_unique($variants));
    if (!$variants) {
        $variants[] = 'f_blue';
    }

    $creatureVariants[] = [
        'name' => $creatureName,
        'slug' => $slug,
        'variants' => $variants,
    ];
}
?>
<h1><?= htmlspecialchars($regionName) ?> Paint Shack</h1>
<p class="muted">Only creatures from <?= htmlspecialchars($regionName) ?> can be painted here.</p>

<?php if ($messages['error']): ?>
  <div class="alert error"><?= htmlspecialchars($messages['error']) ?></div>
<?php endif; ?>
<?php if ($messages['success']): ?>
  <div class="alert success"><?= htmlspecialchars($messages['success']) ?></div>
<?php endif; ?>

<?php if (!$regionPets): ?>
  <p>You do not have any creatures from this region.</p>
<?php else: ?>
  <form method="post" class="card glass" id="paintForm">
    <input type="hidden" name="region_initial" value="<?= htmlspecialchars($regionInitial) ?>">
    <div class="form-row">
      <label for="petSelect">Choose a pet</label>
      <select id="petSelect" name="pet_id">
        <?php foreach ($regionPets as $pet): $name = $pet['nickname'] ?: $pet['species_name']; ?>
          <option value="<?= (int)$pet['pet_instance_id'] ?>"
                  data-species="<?= htmlspecialchars($pet['species_name'], ENT_QUOTES) ?>"
                  data-current-color="<?= htmlspecialchars($pet['color_name'] ?? '', ENT_QUOTES) ?>">
            <?= htmlspecialchars($name) ?> (<?= htmlspecialchars($pet['species_name']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-row">
      <label for="paintSelect">Choose paint</label>
      <?php if ($usablePaints): ?>
        <select id="paintSelect" name="item_id">
          <?php foreach ($usablePaints as $paint): ?>
            <option value="<?= (int)$paint['item_id'] ?>"
                    data-color-name="<?= htmlspecialchars($paint['color_name'], ENT_QUOTES) ?>">
              <?= htmlspecialchars($paint['item_name']) ?> (<?= htmlspecialchars($paint['color_name']) ?>, x<?= (int)$paint['quantity'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      <?php else: ?>
        <p class="muted">You do not have any paint items.</p>
      <?php endif; ?>
    </div>

    <div class="preview card glass">
      <img id="petPreview" class="preview-img" src="<?= htmlspecialchars($previewImage) ?>" alt="Pet preview" onerror="this.src='images/tengu_f_blue.webp'">
      <div class="preview-meta">
        <div>Current color: <span id="currentColor">&nbsp;</span></div>
        <div>New color: <span id="newColor">&nbsp;</span></div>
      </div>
    </div>

    <button type="submit" name="paint_pet" value="1" <?= $usablePaints ? '' : 'disabled' ?>>Paint</button>
  </form>
<?php endif; ?>

<?php if ($creatureVariants): ?>
  <section class="card glass creature-showcase">
    <h2>Creatures from <?= htmlspecialchars($regionName) ?></h2>
    <p class="muted">Browse all paintable creatures from this region and preview their available variants.</p>
    <div class="creature-grid">
      <?php foreach ($creatureVariants as $creature): ?>
        <?php $firstVariant = $creature['variants'][0] ?? 'f_blue'; ?>
        <article class="creature-card">
          <h3><?= htmlspecialchars($creature['name']) ?></h3>
          <div class="creature-slideshow" data-slug="<?= htmlspecialchars($creature['slug'], ENT_QUOTES) ?>" data-variants='<?= htmlspecialchars(json_encode($creature['variants']), ENT_QUOTES) ?>'>
            <button type="button" class="variant-nav prev" aria-label="Previous <?= htmlspecialchars($creature['name']) ?> variant">&#9664;</button>
            <img src="images/<?= htmlspecialchars($creature['slug']) ?>_<?= htmlspecialchars($firstVariant) ?>.webp" alt="<?= htmlspecialchars($creature['name']) ?> preview" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='images/tengu_f_blue.webp';">
            <button type="button" class="variant-nav next" aria-label="Next <?= htmlspecialchars($creature['name']) ?> variant">&#9654;</button>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<style>
  .creature-showcase {
    margin-top: 1.5rem;
  }
  .creature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }
  .creature-card {
    text-align: center;
    padding: 0.75rem;
  }
  .creature-slideshow {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
  }
  .creature-slideshow img {
    max-width: 180px;
    width: 100%;
    height: auto;
  }
  .creature-slideshow .variant-nav {
    border: none;
    background: #f0f0f0;
    border-radius: 6px;
    width: 32px;
    height: 32px;
    cursor: pointer;
    font-size: 16px;
  }
  .creature-slideshow .variant-nav:hover {
    background: #e0e0e0;
  }
</style>

<script>
  const pets = <?= json_encode(array_map(function ($pet) {
      $petName = $pet['nickname'] ?: $pet['species_name'];
      return [
          'id' => (int)$pet['pet_instance_id'],
          'name' => $petName,
          'species' => $pet['species_name'],
          'color' => $pet['color_name'] ?? '',
      ];
  }, $regionPets)) ?>;
  const paints = <?= json_encode(array_map(function ($paint) {
      return [
          'id' => (int)$paint['item_id'],
          'name' => $paint['item_name'],
          'color' => $paint['color_name'],
      ];
  }, $usablePaints)) ?>;

  function slugify(str) {
    return (str || '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '') || 'blue';
  }

  const petSelect = document.getElementById('petSelect');
  const paintSelect = document.getElementById('paintSelect');
  const previewImg = document.getElementById('petPreview');
  const currentColorEl = document.getElementById('currentColor');
  const newColorEl = document.getElementById('newColor');

  function findPet(id) {
    return pets.find(p => p.id === id) || null;
  }
  function findPaint(id) {
    return paints.find(p => p.id === id) || null;
  }

  function updatePreview() {
    if (!petSelect) return;
    const petId = parseInt(petSelect.value || '0');
    const pet = findPet(petId);
    if (!pet) return;

    const selectedPaintId = paintSelect ? parseInt(paintSelect.value || '0') : 0;
    const paint = findPaint(selectedPaintId);

    const currentColor = pet.color || 'Blue';
    currentColorEl.textContent = currentColor;
    const targetColor = paint ? paint.color : currentColor;
    newColorEl.textContent = paint ? paint.color : '-';

    const speciesSlug = slugify(pet.species);
    const colorSlug = slugify(targetColor);
    previewImg.src = `images/${speciesSlug}_f_${colorSlug}.webp`;
    previewImg.onerror = () => { previewImg.onerror = null; previewImg.src = 'images/tengu_f_blue.webp'; };
  }

  if (petSelect) {
    petSelect.addEventListener('change', updatePreview);
  }
  if (paintSelect) {
    paintSelect.addEventListener('change', updatePreview);
  }

  updatePreview();

  const FALLBACK = 'images/tengu_f_blue.webp';
  document.querySelectorAll('.creature-slideshow').forEach(slider => {
    const slug = slider.dataset.slug;
    let variants;
    try {
      variants = JSON.parse(slider.dataset.variants || '[]');
    } catch (e) {
      variants = [];
    }
    if (!variants || !variants.length) {
      variants = ['f_blue'];
    }
    let index = 0;
    const img = slider.querySelector('img');
    const prev = slider.querySelector('.variant-nav.prev');
    const next = slider.querySelector('.variant-nav.next');

    const show = () => {
      img.src = `images/${slug}_${variants[index]}.webp`;
      img.onerror = () => { img.onerror = null; img.src = FALLBACK; };
    };

    prev?.addEventListener('click', () => {
      index = (index - 1 + variants.length) % variants.length;
      show();
    });
    next?.addEventListener('click', () => {
      index = (index + 1) % variants.length;
      show();
    });
  });
</script>
