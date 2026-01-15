<?php require_login();
require_once __DIR__.'/../db.php';
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/input.php';

$uid = current_user()['id'];
$status = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = input_string($_POST['action'] ?? '', 20);

    if ($action === 'abandon') {
        $pet_id = input_int($_POST['pet_id'] ?? 0, 1);
        $pet = get_owned_pet($uid, $pet_id);
        if (!$pet) {
            $errors[] = 'That pet is not available to abandon.';
        } else {
            $creature_name = $pet['nickname'] ?: $pet['species_name'];
            try {
                $pdo = db();
                if (!$pdo) {
                    throw new RuntimeException('Database unavailable.');
                }
                $pdo->beginTransaction();
                $ins = $pdo->prepare(
                    "INSERT INTO abandoned_pets (creature_id, old_player_id, creature_name) VALUES (?,?,?)"
                );
                $ins->execute([$pet_id, $uid, $creature_name]);
                $deactivate = $pdo->prepare("UPDATE pet_instances SET inactive = 1 WHERE pet_instance_id = ?");
                $deactivate->execute([$pet_id]);
                $pdo->commit();
                $status = sprintf('You abandoned %s.', htmlspecialchars($creature_name, ENT_QUOTES, 'UTF-8'));
            } catch (Throwable $e) {
                if (isset($pdo)) {
                    $pdo->rollBack();
                }
                app_add_error_from_exception($e, 'Could not abandon pet:');
                $errors[] = 'Unable to abandon that pet right now.';
            }
        }
    } elseif ($action === 'rescue') {
        $ap_id = input_int($_POST['ap_id'] ?? 0, 1);
        try {
            $pdo = db();
            if (!$pdo) {
                throw new RuntimeException('Database unavailable.');
            }
            $pdo->beginTransaction();
            $record = $pdo->prepare(
                "SELECT ap_id, creature_id, old_player_id FROM abandoned_pets WHERE ap_id = ? FOR UPDATE"
            );
            $record->execute([$ap_id]);
            $abandoned = $record->fetch(PDO::FETCH_ASSOC);
            if ($abandoned) {
                if (!empty($abandoned['old_player_id']) && (int)$abandoned['old_player_id'] === $uid) {
                    $pdo->rollBack();
                    $errors[] = 'You cannot rescue a pet you abandoned.';
                } else {
                    $update = $pdo->prepare("UPDATE pet_instances SET owner_user_id = ?, inactive = 0 WHERE pet_instance_id = ?");
                    $update->execute([$uid, $abandoned['creature_id']]);
                    $delete = $pdo->prepare("DELETE FROM abandoned_pets WHERE ap_id = ?");
                    $delete->execute([$ap_id]);
                    $pdo->commit();
                    $status = 'You rescued a pet!';
                }
            } else {
                $pdo->rollBack();
                $errors[] = 'That pet has already been rescued.';
            }
        } catch (Throwable $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            app_add_error_from_exception($e, 'Could not rescue pet:');
            $errors[] = 'Unable to rescue that pet right now.';
        }
    }
}

$user_pets = get_user_pets($uid);
$abandoned_pets = get_abandoned_pets($uid);
?>
<h1>Pet Shelter</h1>

<?php if ($status): ?>
  <p class="success"><?= $status ?></p>
<?php endif; ?>
<?php if (!empty($errors)): ?>
  <div class="err">
    <ul>
      <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="grid two">
  <section class="card glass">
    <h2>Abandon a pet</h2>
    <p>Select one of your pets to abandon. Abandoned pets can be rescued by anyone.</p>
    <?php if ($user_pets): ?>
      <form method="post" id="abandon-form">
        <input type="hidden" name="action" value="abandon">
        <label for="pet_id">Choose pet</label>
        <select name="pet_id" id="pet_id" required>
          <?php foreach ($user_pets as $pet): ?>
            <option value="<?= (int)$pet['pet_instance_id'] ?>">
              <?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?> (<?= htmlspecialchars($pet['species_name']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <p><button type="submit">Abandon</button></p>
      </form>
    <?php else: ?>
      <p>You have no pets available to abandon.</p>
    <?php endif; ?>
  </section>

  <section class="card glass">
    <h2>Rescue a pet</h2>
    <p>These pets were abandoned by their previous owners. Give one a new home.</p>
    <?php if ($abandoned_pets): ?>
      <div class="pets-grid">
        <?php foreach ($abandoned_pets as $pet): ?>
          <div class="card glass pet-card">
            <?= render_pet_thumbnail($pet, 'thumb', $pet['creature_name'] ?? $pet['species_name']) ?>
            <h3><?= htmlspecialchars($pet['creature_name']) ?></h3>
            <p class="muted">Species: <?= htmlspecialchars($pet['species_name']) ?></p>
            <p class="muted">Previous owner: <?= htmlspecialchars($pet['old_player_name'] ?? 'Unknown') ?></p>
            <form method="post">
              <input type="hidden" name="action" value="rescue">
              <input type="hidden" name="ap_id" value="<?= (int)$pet['ap_id'] ?>">
              <button type="submit">Rescue</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No pets are waiting for rescue right now.</p>
    <?php endif; ?>
  </section>
</div>

<script>
const abandonForm = document.getElementById('abandon-form');
if (abandonForm) {
  abandonForm.addEventListener('submit', (ev) => {
    const select = abandonForm.querySelector('select[name=\"pet_id\"]');
    const petName = select ? select.options[select.selectedIndex].textContent : 'this pet';
    if (!confirm(`Are you sure you want to abandon ${petName}? You will not be able to rescue them again.`)) {
      ev.preventDefault();
    }
  });
}
</script>
