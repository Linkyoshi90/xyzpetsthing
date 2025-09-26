<?php require_login();
$uid = current_user()['id'];

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
    $species = q(
        "SELECT species_id, species_name, base_hp, base_atk, base_def, base_init FROM pet_species " .
        "WHERE species_name IN ($placeholders) ORDER BY species_name",
        $allowedSpecies
    )->fetchAll(PDO::FETCH_ASSOC);
} else {
    $species = [];
}

// Map of available colors
$colors = [1 => 'red', 2 => 'blue', 3 => 'green', 4 => 'yellow', 5 => 'purple'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sp = (int)($_POST['species_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $color = (int)($_POST['color_id'] ?? 1);
    $gender = ($_POST['gender'] ?? 'f') === 'm' ? 'm' : 'f';

    $row = q(
        "SELECT species_id, species_name, base_hp, base_atk, base_def, base_init FROM pet_species WHERE species_id=?",
        [$sp]
    )->fetch(PDO::FETCH_ASSOC);

    if ($row && $name && isset($colors[$color])) {
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
        header('Location: ?pg=main');
        exit;
    }
    $err = "Pick a species, color and name.";
}

// Helper to slugify names like the JS code
function slugify($str)
{
    return strtolower(preg_replace('/[^a-z0-9]+/i', '_', $str));
}
?>
<h1>Create a Pet</h1>
<?php if (!empty($err)) echo "<p class='err'>$err</p>"; ?>

<form method="post" class="card glass form" id="createForm">
  <input type="hidden" name="species_id" id="species_id" value="<?= $species[0]['species_id'] ?? '' ?>">
  <input type="hidden" name="color_id" id="color_id" value="1">

  <div class="pet-create">
    <!-- Species selector -->
    <div class="species-list">
      <?php foreach ($species as $i => $s): $slug = slugify($s['species_name']); ?>
        <div class="species-option<?= $i===0?' selected':'' ?>" data-id="<?= $s['species_id'] ?>">
          <img src="images/<?= $slug ?>_f_blue.webp" alt="<?= htmlspecialchars($s['species_name']) ?>"
               onerror="this.src='images/tengu_f_blue.webp'" />
          <div class="mini"><?= htmlspecialchars($s['species_name']) ?></div>
        </div>
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

      <button>Create</button>
    </div>

    <!-- Stats display -->
    <div class="stats">
      <h3 id="speciesName">&nbsp;</h3>
      <ul>
        <li>HP: <span id="statHp">&nbsp;</span></li>
        <li>ATK: <span id="statAtk">&nbsp;</span></li>
        <li>DEF: <span id="statDef">&nbsp;</span></li>
        <li>INIT: <span id="statInit">&nbsp;</span></li>
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