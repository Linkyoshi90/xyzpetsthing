<?php require_login();
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/input.php';
$uid = current_user()['id'];
$action = input_string($_POST['action'] ?? '', 20);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'rename') {
    header('Content-Type: application/json');
    $pet_id = input_int($_POST['pet_id'] ?? 0, 1);
    $name = input_string($_POST['name'] ?? '', 40);

    if ($name === '') {
        echo json_encode(['ok' => false, 'message' => 'Pet name cannot be blank.']);
        exit;
    }

    if ((int)$uid === 0) {
        if (temp_user_rename_pet($pet_id, $name)) {
            echo json_encode(['ok' => true, 'name' => $name]);
        } else {
            echo json_encode(['ok' => false, 'message' => 'That pet is not available.']);
        }
        exit;
    }

    $pet = q(
        "SELECT pet_instance_id FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$pet_id, $uid]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$pet) {
        echo json_encode(['ok' => false, 'message' => 'That pet is not available.']);
        exit;
    }

    q(
        "UPDATE pet_instances SET nickname = ? WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$name, $pet_id, $uid]
    );

    echo json_encode(['ok' => true, 'name' => $name]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'play') {
    header('Content-Type: application/json');
    $pet_id = input_int($_POST['pet_id'] ?? 0, 1);
    $pet = q(
        "SELECT happiness FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$pet_id, $uid]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$pet) {
        echo json_encode(['ok' => false, 'message' => 'That pet is not available.']);
        exit;
    }

    $boost = 5;
    q(
        "UPDATE pet_instances SET happiness = LEAST(100, happiness + ?) WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$boost, $pet_id, $uid]
    );

    $happiness = q(
        "SELECT happiness FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$pet_id, $uid]
    )->fetchColumn();

    echo json_encode(['ok' => true, 'happiness' => (int)$happiness]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'feed') {
    $pet_id = input_int($_POST['pet_id'] ?? 0, 1);
    $item_id = input_int($_POST['item_id'] ?? 0, 1);
    $row = q(
        "SELECT ui.quantity, i.replenish FROM user_inventory ui
         JOIN items i ON i.item_id = ui.item_id
         LEFT JOIN item_categories ic ON ic.category_id = i.category_id
         WHERE ui.user_id = ? AND ui.item_id = ? AND ic.category_name = 'Food'",
        [$uid, $item_id]
    )->fetch(PDO::FETCH_ASSOC);
    if ($row && (int)$row['quantity'] > 0) {
        $max_hunger = 100;
        $current_hunger = (int)(q(
            "SELECT hunger FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
            [$pet_id, $uid]
        )->fetchColumn() ?? 0);

        if ($current_hunger < $max_hunger) {
            q("UPDATE pet_instances SET hunger = LEAST(?, hunger + ?) WHERE pet_instance_id = ? AND owner_user_id = ?", [$max_hunger, $row['replenish'], $pet_id, $uid]);
            if ((int)$row['quantity'] > 1) {
                q("UPDATE user_inventory SET quantity = quantity - 1 WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
            } else {
                q("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
            }
        }
        header('Location: ?pg=pet&id=' . $pet_id);
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'heal') {
    $pet_id = input_int($_POST['pet_id'] ?? 0, 1);
    $item_id = input_int($_POST['item_id'] ?? 0, 1);
    $row = q(
        "SELECT ui.quantity, i.replenish FROM user_inventory ui"
        . " JOIN items i ON i.item_id = ui.item_id"
        . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
        . " WHERE ui.user_id = ? AND ui.item_id = ? AND ic.category_name = 'Potion'",
        [$uid, $item_id]
    )->fetch(PDO::FETCH_ASSOC);
    if ($row && (int)$row['quantity'] > 0) {
        $healing = max(0, (int)$row['replenish']);
        if ($healing > 0) {
            q(
                "UPDATE pet_instances SET hp_current = IF(hp_max IS NULL, hp_current + ?, LEAST(hp_max, hp_current + ?)) WHERE pet_instance_id = ? AND owner_user_id = ?",
                [$healing, $healing, $pet_id, $uid]
            );
        }
        if ((int)$row['quantity'] > 1) {
            q("UPDATE user_inventory SET quantity = quantity - 1 WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
        } else {
            q("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
        }
    }
    header('Location: ?pg=pet&id=' . $pet_id);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'read') {
    $pet_id = input_int($_POST['pet_id'] ?? 0, 1);
    $item_id = input_int($_POST['item_id'] ?? 0, 1);
    $row = q(
        "SELECT ui.quantity FROM user_inventory ui"
        . " JOIN items i ON i.item_id = ui.item_id"
        . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
        . " WHERE ui.user_id = ? AND ui.item_id = ? AND (ic.category_name = 'Book' OR i.item_name LIKE '%Book%')",
        [$uid, $item_id]
    )->fetch(PDO::FETCH_ASSOC);
    if ($row && (int)$row['quantity'] > 0) {
        q(
            "UPDATE pet_instances SET intelligence = intelligence + 1 WHERE pet_instance_id = ? AND owner_user_id = ?",
            [$pet_id, $uid]
        );
        if ((int)$row['quantity'] > 1) {
            q("UPDATE user_inventory SET quantity = quantity - 1 WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
        } else {
            q("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ?", [$uid, $item_id]);
        }
    }
    header('Location: ?pg=pet&id=' . $pet_id);
    exit;
}
$pets = get_user_pets($uid);
$food_items = q(
    "SELECT ui.item_id, i.item_name, ui.quantity FROM user_inventory ui"
    . " JOIN items i ON i.item_id = ui.item_id"
    . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
    . " WHERE ui.user_id = ? AND ic.category_name = 'Food'",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);
$healing_items = q(
    "SELECT ui.item_id, i.item_name, ui.quantity, i.replenish FROM user_inventory ui"
    . " JOIN items i ON i.item_id = ui.item_id"
    . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
    . " WHERE ui.user_id = ? AND ic.category_name = 'Potion'",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);
$book_items = q(
    "SELECT ui.item_id, i.item_name, ui.quantity FROM user_inventory ui"
    . " JOIN items i ON i.item_id = ui.item_id"
    . " LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
    . " WHERE ui.user_id = ? AND (ic.category_name = 'Book' OR i.item_name LIKE '%Book%')",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);

$pet = null;
$pid = input_int($_GET['id'] ?? 0, 1);
if ($pid === 0) {
    $pid = null;
}
if ($pid) {
    foreach ($pets as $p) {
        if ((int)$p['pet_instance_id'] === $pid) {
            $pet = $p;
            break;
        }
    }
}
if (!$pet && $pets) {
    $pet = $pets[0];
}

function pet_stat_compare(array $pet, string $value_key, string $base_key): string {
    $has_value = array_key_exists($value_key, $pet) && $pet[$value_key] !== null && $pet[$value_key] !== '';
    $has_base = array_key_exists($base_key, $pet) && $pet[$base_key] !== null && $pet[$base_key] !== '';

    if (!$has_value) {
        return $has_base ? 'Not set (base ' . (int)$pet[$base_key] . ')' : 'Not set';
    }

    $value = (int)$pet[$value_key];
    if (!$has_base) {
        return (string)$value;
    }

    $base = (int)$pet[$base_key];
    $delta = $value - $base;
    $delta_text = $delta >= 0 ? '+ ' . $delta : '- ' . abs($delta);

    return $value . ' (' . $base . ' ' . $delta_text . ')';
}

function pet_stat_value(array $pet, string $key): string {
    if (!array_key_exists($key, $pet) || $pet[$key] === null || $pet[$key] === '') {
        return 'Not set';
    }
    return (string)(int)$pet[$key];
}
?>
<style>
  .pet-details {
    position: relative;
  }

  .pet-name-editor {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin: 8px 0;
  }

  .pet-name-editor h2 {
    margin: 0;
  }

  .pet-name-input {
    min-width: 0;
    max-width: 220px;
    padding: 6px 8px;
    font: inherit;
    font-weight: 700;
    text-align: center;
  }

  .pet-name-actions {
    display: inline-flex;
    gap: 4px;
  }

  .pet-name-actions button {
    display: inline-grid;
    place-items: center;
    width: 30px;
    height: 30px;
    min-width: 30px;
    padding: 0;
    line-height: 1;
  }

  .pet-name-editor.is-editing .pet-name-label,
  .pet-name-editor.is-editing .pet-name-edit,
  .pet-name-editor:not(.is-editing) .pet-name-input,
  .pet-name-editor:not(.is-editing) .pet-name-save,
  .pet-name-editor:not(.is-editing) .pet-name-cancel {
    display: none;
  }

  .pet-stat-list {
    display: grid;
    gap: 4px;
    margin: 10px 0;
  }

  .pet-stat-list p {
    margin: 0;
  }

  .pet-stat-list strong {
    display: inline-block;
    min-width: 110px;
  }

  .smiley-pop {
    position: absolute;
    font-size: 1.6rem;
    pointer-events: none;
    transform: translate(-50%, 0);
    animation: smiley-pop 1s ease-out forwards;
  }

  @keyframes smiley-pop {
    0% {
      opacity: 0;
      transform: translate(-50%, 0) scale(0.6);
    }
    30% {
      opacity: 1;
    }
    100% {
      opacity: 0;
      transform: translate(-50%, -32px) scale(1.2);
    }
  }
</style>
<h1>Your Pets</h1>
<?php if ($pets): ?>
<?php if ($pet): ?>
<a class="btn" href="?pg=petting&id=<?= (int)$pet['pet_instance_id'] ?>">Open petting mode</a>
<?php endif; ?>
<a class="btn" href="?pg=create_pet">Create pet</a>
<p></p>
<div class="pets-grid">
<?php foreach ($pets as $pet): $pet_name = $pet['nickname'] ?: $pet['species_name']; ?>
  <div class="card glass pet-card">
    <?= render_pet_thumbnail($pet, 'thumb', $pet_name) ?>
    <div class="pet-name-editor" data-pet-id="<?= (int)$pet['pet_instance_id'] ?>" data-name="<?= htmlspecialchars($pet_name, ENT_QUOTES, 'UTF-8') ?>">
      <h2 class="pet-name-label"><?= htmlspecialchars($pet_name) ?></h2>
      <input class="pet-name-input" type="text" value="<?= htmlspecialchars($pet_name, ENT_QUOTES, 'UTF-8') ?>" maxlength="40" aria-label="Pet name">
      <span class="pet-name-actions">
        <button type="button" class="pet-name-edit" title="Edit name" aria-label="Edit pet name">&#9998;</button>
        <button type="button" class="pet-name-save" title="Save name" aria-label="Save pet name">&#10003;</button>
        <button type="button" class="pet-name-cancel" title="Cancel" aria-label="Cancel pet name edit">&#10005;</button>
      </span>
    </div>
    <button class="show-details" data-id="<?= (int)$pet['pet_instance_id'] ?>">Details</button>
    <div id="pet-<?= (int)$pet['pet_instance_id'] ?>" class="pet-details" data-pet-id="<?= (int)$pet['pet_instance_id'] ?>" style="display:none;">
      <div class="pet-stat-list">
        <p><strong>Species:</strong> <?= htmlspecialchars($pet['species_name']) ?></p>
        <p><strong>Region:</strong> <?= htmlspecialchars($pet['region_name'] ?? 'Unknown') ?></p>
        <p><strong>Color:</strong> <?= htmlspecialchars($pet['color_name'] ?? 'None') ?></p>
        <p><strong>Gender:</strong> <?= htmlspecialchars($pet['gender']) ?></p>
        <p><strong>Level:</strong> <?= (int)$pet['level'] ?></p>
        <p><strong>Experience:</strong> <?= pet_stat_value($pet, 'experience') ?></p>
        <p><strong>Current HP:</strong> <?= pet_stat_compare($pet, 'hp_current', 'base_hp') ?></p>
        <p><strong>Max HP:</strong> <?= pet_stat_value($pet, 'hp_max') ?></p>
        <p><strong>Attack:</strong> <?= pet_stat_compare($pet, 'atk', 'base_atk') ?></p>
        <p><strong>Defense:</strong> <?= pet_stat_compare($pet, 'def', 'base_def') ?></p>
        <p><strong>Initiative:</strong> <?= pet_stat_compare($pet, 'initiative', 'base_init') ?></p>
        <p><strong>Hunger:</strong> <?= (int)$pet['hunger'] ?></p>
        <p><strong>Happiness:</strong> <span class="happiness-value"><?= (int)$pet['happiness'] ?></span></p>
        <p><strong>Intelligence:</strong> <?= (int)($pet['intelligence'] ?? 0) ?></p>
        <p><strong>Sickness:</strong> <?= !empty($pet['sickness']) ? '😷 Unwell' : '✅ Healthy' ?></p>
      </div>
      <div class="actions">
        <button class="play">Play</button>
        <button class="read">Read</button>
        <button class="dress">Dress up</button>
        <button class="close">Close</button>
      </div>
      <div class="feed-form" style="display:none;">
        <?php if ($food_items): ?>
        <form method="post">
          <input type="hidden" name="action" value="feed">
          <input type="hidden" name="pet_id" value="<?= (int)$pet['pet_instance_id'] ?>">
          <select name="item_id">
            <?php foreach ($food_items as $item): ?>
              <option value="<?= (int)$item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?> (x<?= (int)$item['quantity'] ?>)</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" data-pet-name-template="Feed to %s">Feed to <?= htmlspecialchars($pet_name) ?></button>
        </form>
        <?php else: ?>
          <p>You do not have any food items.</p>
        <?php endif; ?>
      </div>
      <div class="heal-form" style="display:none;">
        <?php if ($healing_items): ?>
        <form method="post">
          <input type="hidden" name="action" value="heal">
          <input type="hidden" name="pet_id" value="<?= (int)$pet['pet_instance_id'] ?>">
          <select name="item_id">
            <?php foreach ($healing_items as $item): ?>
              <option value="<?= (int)$item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?> (heals <?= (int)$item['replenish'] ?> HP, x<?= (int)$item['quantity'] ?>)</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" data-pet-name-template="Heal %s">Heal <?= htmlspecialchars($pet_name) ?></button>
        </form>
        <?php else: ?>
          <p>No healing items available.</p>
        <?php endif; ?>
      </div>
      <div class="read-form" style="display:none;">
        <?php if ($book_items): ?>
        <form method="post">
          <input type="hidden" name="action" value="read">
          <input type="hidden" name="pet_id" value="<?= (int)$pet['pet_instance_id'] ?>">
          <select name="item_id">
            <?php foreach ($book_items as $item): ?>
              <option value="<?= (int)$item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?> (x<?= (int)$item['quantity'] ?>)</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" data-pet-name-template="Read to %s">Read to <?= htmlspecialchars($pet_name) ?></button>
        </form>
        <?php else: ?>
          <p>You do not have any books.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<script>
document.querySelectorAll('.pet-name-editor').forEach(editor => {
  const label = editor.querySelector('.pet-name-label');
  const input = editor.querySelector('.pet-name-input');
  const editBtn = editor.querySelector('.pet-name-edit');
  const saveBtn = editor.querySelector('.pet-name-save');
  const cancelBtn = editor.querySelector('.pet-name-cancel');
  const petId = editor.dataset.petId;

  if (!label || !input || !editBtn || !saveBtn || !cancelBtn || !petId) return;

  const setBusy = (busy) => {
    input.disabled = busy;
    saveBtn.disabled = busy;
    cancelBtn.disabled = busy;
  };

  const startEditing = () => {
    input.value = editor.dataset.name || label.textContent.trim();
    editor.classList.add('is-editing');
    input.focus();
    input.select();
  };

  const stopEditing = () => {
    input.value = editor.dataset.name || label.textContent.trim();
    editor.classList.remove('is-editing');
    setBusy(false);
  };

  const saveName = async () => {
    const nextName = input.value.trim();
    if (!nextName) {
      alert('Pet name cannot be blank.');
      input.focus();
      return;
    }
    if (nextName === editor.dataset.name) {
      stopEditing();
      return;
    }

    const formData = new FormData();
    formData.append('action', 'rename');
    formData.append('pet_id', petId);
    formData.append('name', nextName);

    setBusy(true);
    try {
      const response = await fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await response.json();
      if (!data.ok) {
        alert(data.message || 'Unable to rename this pet right now.');
        setBusy(false);
        input.focus();
        return;
      }

      editor.dataset.name = data.name;
      label.textContent = data.name;
      input.value = data.name;
      const thumbnail = editor.closest('.pet-card')?.querySelector('img.thumb, .thumb img');
      if (thumbnail) {
        thumbnail.alt = data.name;
      }
      editor.closest('.pet-card')?.querySelectorAll('[data-pet-name-template]').forEach(button => {
        button.textContent = button.dataset.petNameTemplate.replace('%s', () => data.name);
      });
      stopEditing();
    } catch (error) {
      alert('Unable to rename this pet right now.');
      setBusy(false);
      input.focus();
    }
  };

  editBtn.addEventListener('click', startEditing);
  saveBtn.addEventListener('click', saveName);
  cancelBtn.addEventListener('click', stopEditing);
  input.addEventListener('keydown', (ev) => {
    if (ev.key === 'Enter') {
      ev.preventDefault();
      saveName();
    }
    if (ev.key === 'Escape') {
      ev.preventDefault();
      stopEditing();
    }
  });
});

document.querySelectorAll('.show-details').forEach(btn => {
  btn.addEventListener('click', () => {
    const details = document.getElementById('pet-' + btn.dataset.id);
    if (details) details.style.display = 'block';
  });
});
document.querySelectorAll('.pet-details .actions .play').forEach(btn => {
  btn.addEventListener('click', async () => {
    const details = btn.closest('.pet-details');
    const petId = details?.dataset.petId;
    const happinessValue = details?.querySelector('.happiness-value');
    if (!petId) return;

    const formData = new FormData();
    formData.append('action', 'play');
    formData.append('pet_id', petId);

    try {
      const response = await fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await response.json();
      if (!data.ok) {
        alert(data.message || 'Unable to play right now.');
        return;
      }
      if (happinessValue) {
        happinessValue.textContent = data.happiness;
      }
      const pop = document.createElement('span');
      pop.className = 'smiley-pop';
      pop.textContent = '😊';
      const buttonRect = btn.getBoundingClientRect();
      const detailsRect = details.getBoundingClientRect();
      pop.style.left = `${buttonRect.left - detailsRect.left + buttonRect.width / 2}px`;
      pop.style.top = `${buttonRect.top - detailsRect.top - 6}px`;
      details.appendChild(pop);
      setTimeout(() => pop.remove(), 1000);
    } catch (error) {
      alert('Unable to play right now.');
    }
  });
});
document.querySelectorAll('.pet-details .actions .heal').forEach(btn => {
  btn.addEventListener('click', () => {
    const form = btn.closest('.pet-details').querySelector('.heal-form');
    if (form) form.style.display = 'block';
  });
});
document.querySelectorAll('.pet-details .actions .read').forEach(btn => {
  btn.addEventListener('click', () => {
    const form = btn.closest('.pet-details').querySelector('.read-form');
    if (form) form.style.display = 'block';
  });
});
document.querySelectorAll('.pet-details .actions .dress').forEach(btn => {
  btn.addEventListener('click', () => {
    const details = btn.closest('.pet-details');
    const petId = details?.dataset.petId;
    if (!petId) return;
    window.location.href = `?pg=dress&id=${petId}`;
  });
});
document.querySelectorAll('.pet-details .close').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.closest('.pet-details').style.display = 'none';
  });
});
document.querySelectorAll('.pet-details .actions .feed').forEach(btn => {
  btn.addEventListener('click', () => {
    const form = btn.closest('.pet-details').querySelector('.feed-form');
    if (form) form.style.display = 'block';
  });
});
document.querySelectorAll('.pet-details .actions button').forEach(btn => {
  if (!btn.classList.contains('close')
    && !btn.classList.contains('feed')
    && !btn.classList.contains('heal')
    && !btn.classList.contains('play')
    && !btn.classList.contains('read')
    && !btn.classList.contains('dress')) {
    btn.addEventListener('click', () => alert('Not implemented'));
  }
});
</script>
<?php else: ?>
<p>No pets yet. <a href="?pg=create_pet">Create one</a>.</p>
<?php endif; ?>
