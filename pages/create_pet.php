<?php require_login();
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/temp_user.php';
require_once __DIR__.'/../lib/input.php';
$uid = current_user()['id'];
$maxPets = 4;
$existingPets = get_user_pets($uid);
$petCount = count($existingPets);

// Load list of enabled species from file
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

if ($allowedSpecies) {
    $placeholders = implode(',', array_fill(0, count($allowedSpecies), '?'));
    if ($uid === 0) {
        $species = q(
            "SELECT ps.species_id, ps.species_name, ps.base_hp, ps.base_atk, ps.base_def, ps.base_init, ps.region_id, r.region_name " .
            "FROM pet_species ps " .
            "LEFT JOIN regions r ON r.region_id = ps.region_id " .
            "WHERE ps.species_name IN ($placeholders) ORDER BY ps.species_name",
            $allowedSpecies
        )->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $regionFirstPlaceholders = implode(',', array_fill(0, count($allowedSpecies), '?'));
        $params = array_merge([$uid], $allowedSpecies, $allowedSpecies);
        $species = q(
            "SELECT ps.species_id, ps.species_name, ps.base_hp, ps.base_atk, ps.base_def, ps.base_init, ps.region_id, r.region_name " .
            "FROM pet_species ps " .
            "LEFT JOIN regions r ON r.region_id = ps.region_id " .
            "LEFT JOIN player_unlocked_species pus " .
            "  ON pus.player_id = ? AND pus.unlocked_species_id = ps.species_id " .
            "LEFT JOIN ( " .
            "  SELECT MIN(ps2.species_id) AS species_id " .
            "  FROM pet_species ps2 " .
            "  WHERE ps2.species_name IN ($regionFirstPlaceholders) " .
            "  GROUP BY ps2.region_id " .
            ") region_defaults ON region_defaults.species_id = ps.species_id " .
            "WHERE ps.species_name IN ($placeholders) " .
            "  AND (pus.entryId IS NOT NULL OR region_defaults.species_id IS NOT NULL) " .
            "ORDER BY ps.species_name",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $species = [];
}

$speciesById = [];
foreach ($species as $entry) {
    $speciesById[(int)$entry['species_id']] = $entry;
}

$groupedSpecies = [];
foreach ($species as $entry) {
    $region = $entry['region_name'] ?: 'Unknown';
    $groupedSpecies[$region][] = $entry;
}

$countryNamesFile = __DIR__ . '/../data-readonly/country-names.txt';
if (is_file($countryNamesFile)) {
    foreach (file($countryNamesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $countryName) {
        $countryName = trim($countryName);
        if ($countryName === '') {
            continue;
        }
        $groupedSpecies[$countryName] = $groupedSpecies[$countryName] ?? [];
    }
}

$regionNames = array_keys($groupedSpecies);
sort($regionNames, SORT_NATURAL | SORT_FLAG_CASE);
$sortedSpecies = [];
foreach ($regionNames as $regionName) {
    $sortedSpecies[$regionName] = $groupedSpecies[$regionName];
}
$groupedSpecies = $sortedSpecies;
$firstRegion = $regionNames[0] ?? 'Unknown';

// Map of available colors
$colors = [1 => 'red', 2 => 'blue', 3 => 'green', 4 => 'yellow', 5 => 'purple'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($petCount >= $maxPets) {
        $err = "You already have the maximum of {$maxPets} pets.";
    } else {
    $sp = input_int($_POST['species_id'] ?? 0, 1);
    $name = input_string($_POST['name'] ?? '', 40);
    $color = input_int($_POST['color_id'] ?? 1, 1);
    $genderRaw = input_string($_POST['gender'] ?? 'f', 1, true);
    $gender = $genderRaw === 'm' ? 'm' : 'f';

    $row = $speciesById[$sp] ?? null;

    if ($row && $name && isset($colors[$color])) {
        if ($uid === 0) {
            $pet = [
                'owner_user_id' => 0,
                'species_id' => $row['species_id'],
                'species_name' => $row['species_name'],
                'nickname' => $name,
                'color_id' => $color,
                'color_name' => ucfirst($colors[$color]),
                'gender' => $gender,
                'level' => 1,
                'experience' => 0,
                'hp_current' => $row['base_hp'],
                'hp_max' => $row['base_hp'],
                'atk' => $row['base_atk'],
                'def' => $row['base_def'],
                'initiative' => $row['base_init'],
                'hunger' => 0,
                'happiness' => 0,
                'intelligence' => 0,
                'sickness' => 0,
                'region_id' => $row['region_id'],
                'region_name' => $row['region_name'],
            ];
            temp_user_add_pet($pet);
            temp_user_add_inventory_item(1, 1);
            temp_user_add_inventory_item(2, 2);
        } else {
            q(
                "INSERT INTO pet_instances (owner_user_id, species_id, nickname, color_id, gender, level, experience, hp_current, hp_max, atk, def, initiative)"
                . " VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
                [$uid, $sp, $name, $color, $gender, 1, 0, $row['base_hp'], $row['base_hp'], $row['base_atk'], $row['base_def'], $row['base_init']]
            );
            // starter items
            q(
                "INSERT INTO user_inventory(user_id,item_id,quantity) VALUES(?,?,1) ON DUPLICATE KEY UPDATE quantity=quantity+1",
                [$uid, 1]
            );
            q(
                "INSERT INTO user_inventory(user_id,item_id,quantity) VALUES(?,?,2) ON DUPLICATE KEY UPDATE quantity=quantity+2",
                [$uid, 2]
            );
        }
        header('Location: ?pg=main');
        exit;
    }
    $err = "Pick a species, color and name.";
    }
}

// Helper to slugify names like the JS code
function slugify($str)
{
    return strtolower(preg_replace('/[^a-z0-9]+/i', '_', $str));
}
?>
<h1>Create a Pet</h1>
<?php if (!empty($err)) echo "<p class='err'>$err</p>"; ?>

<?php if ($petCount >= $maxPets): ?>
  <p class="err">You already have the maximum of <?= (int)$maxPets ?> pets.</p>
<?php endif; ?>

<form method="post" class="card glass form" id="createForm" <?= $petCount >= $maxPets ? 'aria-disabled="true"' : '' ?>>
  <input type="hidden" name="species_id" id="species_id" value="<?= $species[0]['species_id'] ?? '' ?>">
  <input type="hidden" name="color_id" id="color_id" value="1">

  <div class="pet-create">
    <!-- Species selector -->
    <div class="species-list">
      <?php foreach ($groupedSpecies as $region => $entries): ?>
        <?php $isOpen = $region === $firstRegion; ?>
        <details class="species-group"<?= $isOpen ? ' open' : '' ?>>
          <summary>
            <span><?= htmlspecialchars($region) ?></span>
            <span class="mini"><?= count($entries) ?> species</span>
          </summary>
          <div class="species-grid">
            <?php if ($entries): ?>
              <?php foreach ($entries as $i => $s): $slug = slugify($s['species_name']); ?>
                <div class="species-option<?= ($s['species_id'] === ($species[0]['species_id'] ?? null)) ? ' selected' : '' ?>" data-id="<?= $s['species_id'] ?>">
                  <img src="images/<?= $slug ?>_f_blue.webp" alt="<?= htmlspecialchars($s['species_name']) ?>"
                       onerror="this.src='images/tengu_f_blue.webp'" />
                  <div class="mini"><?= htmlspecialchars($s['species_name']) ?></div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="mini">No species available in this country yet.</p>
            <?php endif; ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>

    <!-- Preview and controls -->
    <div class="preview">
      <img id="previewImage" src="images/<?= slugify($species[0]['species_name'] ?? 'tengu') ?>_f_red.webp"
           onerror="this.src='images/tengu_f_blue.webp'" alt="preview" class="preview-img" />

      <div class="color-selector">
        <?php foreach ($colors as $cid => $cname): ?>
          <button type="button" class="color-btn<?= $cid===1?' selected':'' ?>" data-id="<?= $cid ?>"
                  style="background: <?= $cname ?>;">
            <?= htmlspecialchars(ucfirst($cname)) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <label>Pet Name
        <input name="name" required maxlength="40">
      </label>

      <button <?= $petCount >= $maxPets ? 'disabled' : '' ?>>Create</button>
    </div>

    <!-- Stats display -->
    <div class="stats">
      <h3 id="speciesName">&nbsp;</h3>
      <ul>
        <li>HP: <span id="statHp">&nbsp;</span></li>
        <li>ATK: <span id="statAtk">&nbsp;</span></li>
        <li>DEF: <span id="statDef">&nbsp;</span></li>
        <li>INIT: <span id="statInit">&nbsp;</span></li>
        <li>Home region: <span id="statRegion">&nbsp;</span></li>
      </ul>
    </div>
  </div>
</form>

<script>
const speciesData = <?= json_encode($species) ?>;
const colors = <?= json_encode($colors) ?>;

function slugify(str){
  return str.toLowerCase().replace(/[^a-z0-9]+/g,'_');
}

let selectedSpecies = speciesData[0] || null;
let selectedColor = 1;
let selectedGender = 'f';

function updatePreview(){
  if(!selectedSpecies) return;
  const slug = slugify(selectedSpecies.species_name);
  const colorName = colors[selectedColor];
  const img = document.getElementById('previewImage');
  img.src = `images/${slug}_${selectedGender}_${colorName}.webp`;
  img.onerror = () => { img.onerror = null; img.src = 'images/tengu_f_blue.webp'; };

  document.getElementById('speciesName').textContent = selectedSpecies.species_name;
  document.getElementById('statHp').textContent = selectedSpecies.base_hp;
  document.getElementById('statAtk').textContent = selectedSpecies.base_atk;
  document.getElementById('statDef').textContent = selectedSpecies.base_def;
  document.getElementById('statInit').textContent = selectedSpecies.base_init;
  const regionLabel = selectedSpecies.region_name || (selectedSpecies.region_id ? `Region #${selectedSpecies.region_id}` : 'Unknown');
  document.getElementById('statRegion').textContent = regionLabel;

  document.getElementById('species_id').value = selectedSpecies.species_id;
  document.getElementById('color_id').value = selectedColor;
}

document.querySelectorAll('.species-option').forEach(el => {
  el.addEventListener('click', () => {
    document.querySelectorAll('.species-option').forEach(e=>e.classList.remove('selected'));
    el.classList.add('selected');
    const id = parseInt(el.dataset.id);
    selectedSpecies = speciesData.find(s => s.species_id == id);
    updatePreview();
  });
});

document.querySelectorAll('.color-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.color-btn').forEach(b=>b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedColor = parseInt(btn.dataset.id);
    updatePreview();
  });
});

document.querySelectorAll('input[name="gender"]').forEach(radio => {
  radio.addEventListener('change', () => {
    selectedGender = radio.value;
    updatePreview();
  });
});

updatePreview();
</script>
