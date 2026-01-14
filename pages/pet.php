<?php require_login();
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/input.php';
$uid = current_user()['id'];
$action = input_string($_POST['action'] ?? '', 20);
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
?>
<style>
  .pet-details {
    position: relative;
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
<?php foreach ($pets as $pet): ?>
  <div class="card glass pet-card">
    <img class="thumb" src="<?= htmlspecialchars(pet_image_url($pet['species_name'], $pet['color_name'])) ?>" alt="">
    <h2><?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?></h2>
    <button class="show-details" data-id="<?= (int)$pet['pet_instance_id'] ?>">Details</button>
    <div id="pet-<?= (int)$pet['pet_instance_id'] ?>" class="pet-details" data-pet-id="<?= (int)$pet['pet_instance_id'] ?>" style="display:none;">
      <p>Species: <?= htmlspecialchars($pet['species_name']) ?></p>
      <p>Color: <?= htmlspecialchars($pet['color_name'] ?? 'None') ?></p>
      <p>Gender: <?= htmlspecialchars($pet['gender']) ?></p>
      <p>Level: <?= (int)$pet['level'] ?></p>
      <p>HP: <?= (int)($pet['hp_current'] ?? 0) ?> / <?= (int)($pet['hp_max'] ?? ($pet['hp_current'] ?? 0)) ?></p>
      <p>Sickness: <?= !empty($pet['sickness']) ? '😷 Unwell' : '✅ Healthy' ?></p>
      <p>Hunger: <?= (int)$pet['hunger'] ?></p>
      <p>Happiness: <span class="happiness-value"><?= (int)$pet['happiness'] ?></span></p>
      <p>Intelligence: <?= (int)($pet['intelligence'] ?? 0) ?></p>
      <div class="actions">
        <button class="play">Play</button>
        <button class="read">Read</button>
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
          <button type="submit">Feed to <?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?></button>
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
          <button type="submit">Heal <?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?></button>
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
          <button type="submit">Read to <?= htmlspecialchars($pet['nickname'] ?: $pet['species_name']) ?></button>
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
  if (!btn.classList.contains('close') && !btn.classList.contains('feed') && !btn.classList.contains('heal') && !btn.classList.contains('play') && !btn.classList.contains('read')) {
    btn.addEventListener('click', () => alert('Not implemented'));
  }
});
</script>
<?php else: ?>
<p>No pets yet. <a href="?pg=create_pet">Create one</a>.</p>
<?php endif; ?>
