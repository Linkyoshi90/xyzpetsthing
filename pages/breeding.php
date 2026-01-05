<?php require_login();
require_once __DIR__ . '/../lib/pets.php';
require_once __DIR__ . '/../lib/breeding.php';

$uid = current_user()['id'];
$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'start') {
        $motherId = (int)($_POST['mother_id'] ?? 0);
        $fatherInput = $_POST['father_id'] ?? '';
        $fatherId = ($fatherInput === '' || $fatherInput === 'npc') ? null : (int)$fatherInput;
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

$activePets = get_user_pets($uid);
$allPairs = breeding_active_pairs($uid);
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
  <?php if (!$allPairs): ?>
    <p>No creatures are currently breeding.</p>
  <?php else: ?>
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
  <?php endif; ?>
</div>
