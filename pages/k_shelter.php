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
        $confirmed = input_string($_POST['abandon_confirmed'] ?? '', 1) === '1';
        if (!$confirmed) {
            $errors[] = 'Please confirm the adoption warning before giving up a pet.';
        }

        $pet = $confirmed ? get_owned_pet($uid, $pet_id) : null;
        if (!$pet) {
            if ($confirmed) {
                $errors[] = 'That pet is not available to abandon.';
            }
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
                $deactivate = $pdo->prepare("UPDATE pet_instances SET inactive = 1 WHERE pet_instance_id = ? AND owner_user_id = ?");
                $deactivate->execute([$pet_id, $uid]);
                if ($deactivate->rowCount() !== 1) {
                    throw new RuntimeException('Selected pet could not be marked inactive.');
                }
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
        <input type="hidden" name="abandon_confirmed" value="0">
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

<div class="shelter-confirm-overlay" id="abandon-confirm-overlay" role="presentation" hidden>
  <article
    class="notification-item shelter-confirm-notification"
    role="dialog"
    aria-modal="true"
    aria-labelledby="abandon-confirm-title"
    aria-describedby="abandon-confirm-message"
  >
    <span class="notification-item__icon" aria-hidden="true">!</span>
    <div class="notification-item__copy">
      <strong id="abandon-confirm-title">Give up this pet?</strong>
      <small id="abandon-confirm-message">
        <span id="abandon-confirm-pet-name">This pet</span> will be placed up for adoption. You will not be able to rescue them again.
      </small>
      <span class="notification-item__actions shelter-confirm-actions">
        <button type="button" class="notification-item__action" id="abandon-confirm-submit">Confirm</button>
        <button type="button" class="notification-item__action" id="abandon-confirm-cancel">Cancel</button>
      </span>
    </div>
  </article>
</div>

<style>
  .shelter-confirm-overlay {
    position: fixed;
    inset: 0;
    z-index: 1200;
    display: grid;
    place-items: center;
    padding: 18px;
    background: rgba(14, 23, 38, 0.42);
    backdrop-filter: blur(4px);
  }

  .shelter-confirm-overlay[hidden] {
    display: none;
  }

  .shelter-confirm-notification {
    width: min(430px, 100%);
    padding-right: 10px;
  }

  .shelter-confirm-actions {
    margin-top: 8px;
  }
</style>

<script>
const abandonForm = document.getElementById('abandon-form');
if (abandonForm) {
  const overlay = document.getElementById('abandon-confirm-overlay');
  const selectedPetName = document.getElementById('abandon-confirm-pet-name');
  const confirmButton = document.getElementById('abandon-confirm-submit');
  const cancelButton = document.getElementById('abandon-confirm-cancel');
  const confirmedInput = abandonForm.querySelector('input[name="abandon_confirmed"]');
  const select = abandonForm.querySelector('select[name="pet_id"]');
  let pendingPetId = '';

  const closeAbandonNotification = () => {
    if (!overlay) return;
    overlay.hidden = true;
    pendingPetId = '';
    if (confirmedInput) {
      confirmedInput.value = '0';
    }
  };

  abandonForm.addEventListener('submit', (ev) => {
    if (confirmedInput && confirmedInput.value === '1') {
      return;
    }

    ev.preventDefault();
    if (!overlay || !select || !selectedPetName) {
      return;
    }

    const selectedOption = select.options[select.selectedIndex];
    pendingPetId = selectedOption ? selectedOption.value : '';
    selectedPetName.textContent = selectedOption ? selectedOption.textContent.trim() : 'This pet';
    overlay.hidden = false;
    if (confirmButton) {
      confirmButton.focus();
    }
  });

  if (confirmButton) {
    confirmButton.addEventListener('click', () => {
      if (!pendingPetId || !select || !confirmedInput) {
        closeAbandonNotification();
        return;
      }

      select.value = pendingPetId;
      confirmedInput.value = '1';
      if (typeof abandonForm.requestSubmit === 'function') {
        abandonForm.requestSubmit();
      } else {
        abandonForm.submit();
      }
    });
  }

  if (cancelButton) {
    cancelButton.addEventListener('click', closeAbandonNotification);
  }

  if (overlay) {
    overlay.addEventListener('click', (ev) => {
      if (ev.target === overlay) {
        ev.preventDefault();
      }
    });

    overlay.addEventListener('keydown', (ev) => {
      if (overlay.hidden) {
        return;
      }
      if (ev.key === 'Escape') {
        ev.preventDefault();
        return;
      }
      if (ev.key !== 'Tab' || !confirmButton || !cancelButton) {
        return;
      }

      const first = confirmButton;
      const last = cancelButton;
      if (ev.shiftKey && document.activeElement === first) {
        ev.preventDefault();
        last.focus();
      } else if (!ev.shiftKey && document.activeElement === last) {
        ev.preventDefault();
        first.focus();
      }
    });
  }
}
</script>
