<?php require_login();
require_once __DIR__ . '/../lib/pets.php';
require_once __DIR__ . '/../lib/breeding.php';
require_once __DIR__ . '/../lib/input.php';

$uid = current_user()['id'];
$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = input_string($_POST['action'] ?? '', 20);
    if ($action === 'start') {
        $motherId = input_int($_POST['mother_id'] ?? 0, 1);
        $fatherInput = input_string($_POST['father_id'] ?? '', 10);
        $fatherId = null;
        if ($fatherInput !== '' && $fatherInput !== 'npc') {
            $fatherId = input_int($fatherInput, 1);
            if ($fatherId === 0) {
                $fatherId = null;
            }
        }
        $result = breeding_start_pair($uid, $motherId, $fatherId);
        if ($result['ok']) {
            $messages[] = $result['message'];
        } else {
            $errors[] = $result['message'];
        }
    } elseif ($action === 'hatch') {
        $hatchedNow = breeding_hatch_ready_eggs($uid);
        if ($hatchedNow) {
            $messages = array_merge($messages, $hatchedNow);
        } else {
            $errors[] = 'No eggs are ready to hatch yet.';
        }
    }
}

$hatched = breeding_hatch_ready_eggs($uid);
if ($hatched) {
    $messages = array_merge($messages, $hatched);
}

$allPets = get_user_pets($uid, true);
$activePets = array_values(array_filter($allPets, static function (array $pet): bool {
    return (int)($pet['inactive'] ?? 0) === 0;
}));
$inactivePets = array_values(array_filter($allPets, static function (array $pet): bool {
    return (int)($pet['inactive'] ?? 0) === 1;
}));
$allPairs = breeding_active_pairs($uid);
$pairedPetIds = [];
foreach ($allPairs as $pair) {
    $motherId = (int)($pair['mother'] ?? 0);
    $fatherId = (int)($pair['father'] ?? 0);
    if ($motherId > 0) {
        $pairedPetIds[$motherId] = true;
    }
    if ($fatherId > 0) {
        $pairedPetIds[$fatherId] = true;
    }
}
?>
<h1>Daycare Breeding</h1>

<?php foreach ($messages as $msg): ?>
  <div class="alert success"><?= htmlspecialchars($msg) ?></div>
<?php endforeach; ?>
<?php foreach ($errors as $msg): ?>
  <div class="alert err"><?= htmlspecialchars($msg) ?></div>
<?php endforeach; ?>

<div class="card glass">
  <h2>Begin a Breeding Session</h2>
  <?php if (!$activePets): ?>
    <p>You need at least one active creature to start breeding.</p>
  <?php else: ?>
  <form method="post" class="form">
    <input type="hidden" name="action" value="start">
    <label>
      Mother (required)
      <select name="mother_id" required>
        <option value="">-- choose a mother --</option>
        <?php foreach ($activePets as $pet): ?>
          <option value="<?= (int)$pet['pet_instance_id'] ?>">
            <?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?> (<?= htmlspecialchars($pet['species_name']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Father
      <select name="father_id">
        <option value="npc">Daycare Stallion (all stats 10)</option>
        <?php foreach ($activePets as $pet): ?>
          <option value="<?= (int)$pet['pet_instance_id'] ?>">
            <?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?> (<?= htmlspecialchars($pet['species_name']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <p class="mini">Parents are marked inactive while they stay in daycare.</p>
    <button type="submit" class="btn">Deposit</button>
  </form>
  <?php endif; ?>
</div>

<div class="card glass">
  <h2>Daycare Status</h2>
  <?php if (!$allPairs && !$inactivePets): ?>
    <p>No creatures are currently breeding.</p>
  <?php else: ?>
  <?php if ($allPairs): ?>
    <form method="post">
      <input type="hidden" name="action" value="hatch">
      <button type="submit" class="btn">Collect any hatched eggs</button>
    </form>
    <table class="table">
      <thead>
        <tr>
          <th>Mother</th>
          <th>Father</th>
          <th>Eggs</th>
          <th>Time to hatch</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allPairs as $pair): ?>
          <tr>
            <td><?= htmlspecialchars($pair['mother_name'] ?: ($pair['mother_species_name'] ?? 'Unknown')) ?></td>
            <td><?= htmlspecialchars($pair['father_name'] ?: ($pair['father_species_name'] ?? 'Daycare Stallion')) ?></td>
            <td><?= (int)$pair['egg_count'] ?></td>
            <td>
              <?php if ((int)$pair['egg_count'] <= 0): ?>
                Waiting for an egg
              <?php elseif ((int)$pair['time_to_hatch'] <= 0): ?>
                Ready to hatch
              <?php else: ?>
                <?= (int)$pair['time_to_hatch'] ?> day(s)
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <p>No creatures are currently breeding.</p>
    <?php endif; ?>

    <?php if ($inactivePets): ?>
      <h3>Inactive creatures at daycare</h3>
      <p class="mini">These creatures are marked inactive and cannot join new adventures until they leave daycare.</p>
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Species</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inactivePets as $pet): ?>
            <tr>
              <td><?= htmlspecialchars($pet['nickname'] ?: ($pet['species_name'] ?? 'Unknown')) ?></td>
              <td><?= htmlspecialchars($pet['species_name'] ?? 'Unknown species') ?></td>
              <td>
                <?php if (isset($pairedPetIds[(int)$pet['pet_instance_id']])): ?>
                  Part of a breeding pair
                <?php else: ?>
                  Resting in daycare
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  <?php endif; ?>
</div>
