<?php
require_login();
require_once __DIR__ . '/../lib/pets.php';
require_once __DIR__ . '/../lib/input.php';
require_once __DIR__ . '/../lib/creature_moves.php';

$uid = (int)(current_user()['id'] ?? 0);
$messages = [];
$errors = [];
$element_lookup = battle_load_element_lookup();
$tables_ready = dojo_tables_ready();

function dojo_load_pet(int $uid, int $pet_id, array $element_lookup): ?array {
    if ($pet_id <= 0) {
        return null;
    }

    $row = q(
        "SELECT pi.pet_instance_id, pi.species_id, pi.nickname, pi.level,
                ps.species_name, pc.color_name
           FROM pet_instances pi
           JOIN pet_species ps ON ps.species_id = pi.species_id
           LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
          WHERE pi.pet_instance_id = ?
            AND pi.owner_user_id = ?
            AND COALESCE(pi.inactive, 0) = 0",
        [$pet_id, $uid]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    $elements = array_map(
        static fn(array $e): int => (int)$e['element_id'],
        q("SELECT element_id FROM species_elements WHERE species_id = ? ORDER BY element_id", [(int)$row['species_id']])->fetchAll(PDO::FETCH_ASSOC)
    );

    $name = trim((string)($row['nickname'] ?? ''));

    return [
        'id' => (int)$row['pet_instance_id'],
        'speciesId' => (int)$row['species_id'],
        'name' => $name !== '' ? $name : (string)$row['species_name'],
        'species' => (string)$row['species_name'],
        'level' => max(1, (int)$row['level']),
        'elements' => $elements,
        'elementNames' => array_map(
            static fn(int $id): string => $element_lookup[$id] ?? ('Element ' . $id),
            $elements
        ),
        'image' => pet_image_url((string)$row['species_name'], $row['color_name'] ?? null, null),
    ];
}

function dojo_balance(int $uid): int {
    $balance = q(
        "SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = 1",
        [$uid]
    )->fetchColumn();

    return $balance === false ? 0 : (int)$balance;
}

function dojo_spend(int $uid, int $amount): bool {
    $balance = dojo_balance($uid);
    if ($balance < $amount) {
        return false;
    }

    q(
        "UPDATE user_balances SET balance = balance - ? WHERE user_id = ? AND currency_id = 1",
        [$amount, $uid]
    );
    $_SESSION['user']['cash'] = $balance - $amount;
    return true;
}

function dojo_move_name(int $move_id): string {
    $name = q("SELECT move_name FROM moves WHERE move_id = ?", [$move_id])->fetchColumn();
    return $name === false ? 'that move' : (string)$name;
}

function dojo_effect_text(array $move): string {
    $effect = strtolower(trim((string)($move['effect'] ?? '')));
    if ($effect === '') {
        return '';
    }

    $chance = (float)($move['effectChance'] ?? 0);
    $chance = $chance > 0 ? $chance : 100.0;
    $chance_text = $chance >= 100 ? '' : sprintf(' (%d%%)', (int)round($chance));

    if ($effect === 'curse') {
        return 'Self: Attack +1, Defense +1, Speed -1';
    }

    if (preg_match('/^(atk|attack|def|defense|spd|speed|init|acc|accuracy)_(up|down)_(\d+)$/', $effect, $m)) {
        $stats = ['atk' => 'Attack', 'attack' => 'Attack', 'def' => 'Defense', 'defense' => 'Defense',
                  'spd' => 'Speed', 'speed' => 'Speed', 'init' => 'Speed', 'acc' => 'Accuracy', 'accuracy' => 'Accuracy'];
        $steps = (int)$m[3];
        $steps = ($steps >= 1 && $steps <= 2) ? $steps : 1;
        return sprintf('%s: %s %s%d%s', $m[2] === 'down' ? 'Foe' : 'Self', $stats[$m[1]], $m[2] === 'down' ? '-' : '+', $steps, $chance_text);
    }

    $ailments = ['poison' => 'Poison', 'venom' => 'Venom', 'burn' => 'Burn', 'paralyze' => 'Paralysis',
                 'paralysis' => 'Paralysis', 'freeze' => 'Freeze', 'sleep' => 'Sleep', 'rage' => 'Rage'];
    $base = preg_replace('/_\d+$/', '', $effect);
    if (isset($ailments[$base])) {
        return 'Inflicts ' . $ailments[$base] . $chance_text;
    }

    return '';
}

$selected_pet_id = input_int($_GET['pet'] ?? ($_POST['pet_id'] ?? 0), 1);
$learn_pick_id = input_int($_GET['learn'] ?? 0, 1);

// POSTs arrive through index.php's pre-layout hook, so we can PRG: process,
// stash the outcome in the session, redirect. A refresh never pays twice.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = input_string($_POST['action'] ?? '', 20);
    $pet = $tables_ready ? dojo_load_pet($uid, input_int($_POST['pet_id'] ?? 0, 1), $element_lookup) : null;

    if (!$tables_ready) {
        $errors[] = 'The dojo ledgers are not set up on this database yet.';
    } elseif (!$pet) {
        $errors[] = 'That creature is not in your care.';
    } elseif ($action === 'learn') {
        $move_id = input_int($_POST['move_id'] ?? 0, 1);
        $slot = input_int($_POST['slot'] ?? 0, 1, 4);
        $moveset = dojo_load_or_adopt_moveset($pet, $element_lookup);
        $known_ids = array_map(static fn(array $m): int => (int)$m['id'], $moveset['moves']);
        $price = dojo_learn_price($pet['level']);

        if ($move_id <= 0 || $slot <= 0) {
            $errors[] = 'Pick a technique and a slot first.';
        } elseif (in_array($move_id, $known_ids, true)) {
            $errors[] = sprintf('%s already knows %s.', $pet['name'], dojo_move_name($move_id));
        } elseif (!dojo_move_is_learnable($pet['speciesId'], $pet['level'], $move_id)) {
            $errors[] = sprintf('%s cannot learn that technique yet.', $pet['name']);
        } elseif (!dojo_spend($uid, $price)) {
            $errors[] = sprintf('Training costs %d and your pouch is too light.', $price);
        } else {
            q(
                "REPLACE INTO pet_instance_moves (pet_instance_id, slot, move_id) VALUES (?, ?, ?)",
                [$pet['id'], $slot, $move_id]
            );
            $messages[] = sprintf('%s learned %s! (-%d)', $pet['name'], dojo_move_name($move_id), $price);
        }
    } elseif ($action === 'unlearn') {
        $slot = input_int($_POST['slot'] ?? 0, 1, 4);
        $moveset = dojo_load_or_adopt_moveset($pet, $element_lookup);
        $in_slot = null;
        foreach ($moveset['moves'] as $move) {
            if ((int)$move['slot'] === $slot) {
                $in_slot = $move;
            }
        }
        $price = dojo_unlearn_price($pet['level']);

        if ($slot <= 0 || !$in_slot) {
            $errors[] = 'There is no technique in that slot.';
        } elseif (count($moveset['moves']) <= 1) {
            $errors[] = sprintf('%s must keep at least one technique.', $pet['name']);
        } elseif (!dojo_spend($uid, $price)) {
            $errors[] = sprintf('Unlearning costs %d and your pouch is too light.', $price);
        } else {
            q(
                "DELETE FROM pet_instance_moves WHERE pet_instance_id = ? AND slot = ?",
                [$pet['id'], $slot]
            );
            $messages[] = sprintf('%s let go of %s. (-%d)', $pet['name'], $in_slot['name'], $price);
        }
    }

    $_SESSION['dojo_flash'] = ['messages' => $messages, 'errors' => $errors];
    $pet_id_post = input_int($_POST['pet_id'] ?? 0, 1);
    header('Location: index.php?pg=yn-dojo' . ($pet_id_post > 0 ? '&pet=' . $pet_id_post : ''));
    exit;
}

if (!empty($_SESSION['dojo_flash'])) {
    $messages = array_merge($messages, (array)($_SESSION['dojo_flash']['messages'] ?? []));
    $errors = array_merge($errors, (array)($_SESSION['dojo_flash']['errors'] ?? []));
    unset($_SESSION['dojo_flash']);
}

$selected_pet = $tables_ready ? dojo_load_pet($uid, $selected_pet_id, $element_lookup) : null;
$moveset = null;
$learnable = [];
$learn_pick = null;

if ($selected_pet) {
    $moveset = dojo_load_or_adopt_moveset($selected_pet, $element_lookup);
    if ($moveset['adopted']) {
        $messages[] = sprintf("The sensei studies %s and records its current techniques in the dojo ledger.", $selected_pet['name']);
    }
    $known_ids = array_map(static fn(array $m): int => (int)$m['id'], $moveset['moves']);
    $learnable = dojo_learnable_moves($selected_pet['speciesId'], $selected_pet['level'], $known_ids, $element_lookup);
    if ($learn_pick_id > 0) {
        foreach ($learnable as $move) {
            if ((int)$move['id'] === $learn_pick_id) {
                $learn_pick = $move;
            }
        }
    }
}

$roster = q(
    "SELECT pi.pet_instance_id, pi.nickname, pi.level, ps.species_name, pc.color_name
       FROM pet_instances pi
       JOIN pet_species ps ON ps.species_id = pi.species_id
       LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
      WHERE pi.owner_user_id = ?
        AND COALESCE(pi.inactive, 0) = 0
      ORDER BY pi.pet_instance_id",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
  .dojo-roster { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
  .dojo-pet-card { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px;
                   background: rgba(255,255,255,0.06); text-decoration: none; color: inherit; border: 1px solid transparent; }
  .dojo-pet-card:hover { border-color: rgba(245,158,11,0.6); }
  .dojo-pet-card.is-selected { border-color: #f59e0b; background: rgba(245,158,11,0.12); }
  .dojo-pet-card img { width: 52px; height: 52px; object-fit: contain; }
  .dojo-slots { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }
  .dojo-slot { padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.06); }
  .dojo-slot.is-empty { border: 1px dashed rgba(255,255,255,0.25); background: transparent; }
  .dojo-slot h4 { margin: 0 0 4px; }
  .dojo-table { width: 100%; border-collapse: collapse; }
  .dojo-table th, .dojo-table td { text-align: left; padding: 7px 10px; border-bottom: 1px solid rgba(255,255,255,0.1); }
  .dojo-price { color: #f59e0b; font-weight: 700; }
  .dojo-inline-form { display: inline; }
</style>

<h1>Yara Dojo</h1>
<p class="muted">
  A shaded training ground above the river bend. The sensei trades technique for coin -
  the wiser the creature, the steeper the fee.
</p>

<?php foreach ($messages as $msg): ?>
  <div class="alert success"><?= htmlspecialchars($msg) ?></div>
<?php endforeach; ?>
<?php foreach ($errors as $msg): ?>
  <div class="alert err"><?= htmlspecialchars($msg) ?></div>
<?php endforeach; ?>

<?php if (!$tables_ready): ?>
<div class="card glass">
  <h2>The dojo is still being raised</h2>
  <p class="muted">The training ledgers (species_movepool / pet_instance_moves tables) are not set up on this database yet.</p>
  <a class="btn" href="?pg=yn">Back to Warraluma</a>
</div>
<?php else: ?>

<div class="card glass">
  <h2>Choose a student</h2>
  <?php if (!$roster): ?>
    <p class="muted">You have no active creatures to train.</p>
  <?php else: ?>
    <div class="dojo-roster">
      <?php foreach ($roster as $row): ?>
        <?php
          $rid = (int)$row['pet_instance_id'];
          $rname = trim((string)($row['nickname'] ?? '')) !== '' ? (string)$row['nickname'] : (string)$row['species_name'];
        ?>
        <a class="dojo-pet-card<?= $rid === ($selected_pet['id'] ?? 0) ? ' is-selected' : '' ?>" href="?pg=yn-dojo&amp;pet=<?= $rid ?>">
          <img src="<?= htmlspecialchars(pet_image_url((string)$row['species_name'], $row['color_name'] ?? null, null)) ?>" alt="">
          <span>
            <strong><?= htmlspecialchars($rname) ?></strong><br>
            <span class="mini"><?= htmlspecialchars((string)$row['species_name']) ?> · Lv. <?= (int)$row['level'] ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php if ($selected_pet): ?>
<?php
  $learn_price = dojo_learn_price($selected_pet['level']);
  $unlearn_price = dojo_unlearn_price($selected_pet['level']);
  $moves_by_slot = [];
  foreach ($moveset['moves'] as $move) {
      $moves_by_slot[(int)$move['slot']] = $move;
  }
  $form_action = '?pg=yn-dojo&amp;pet=' . $selected_pet['id'];
?>

<div class="card glass">
  <h2><?= htmlspecialchars($selected_pet['name']) ?>'s Techniques</h2>
  <p class="muted">
    <?= htmlspecialchars($selected_pet['species']) ?> · Lv. <?= $selected_pet['level'] ?> ·
    <?= htmlspecialchars(implode(' / ', $selected_pet['elementNames']) ?: 'Neutral') ?> ·
    Learning fee <span class="dojo-price"><?= $learn_price ?></span> ·
    Unlearning fee <span class="dojo-price"><?= $unlearn_price ?></span> ·
    Your pouch: <span class="dojo-price"><?= (int)(current_user()['cash'] ?? 0) ?></span>
  </p>
  <div class="dojo-slots">
    <?php for ($slot = 1; $slot <= 4; $slot++): ?>
      <?php $move = $moves_by_slot[$slot] ?? null; ?>
      <?php if ($move): ?>
        <div class="dojo-slot">
          <h4>Slot <?= $slot ?>: <?= htmlspecialchars($move['name']) ?></h4>
          <p class="mini">
            <?= htmlspecialchars($move['elementName']) ?> ·
            <?= $move['power'] > 0 ? ((int)$move['power'] . ' power') : 'Status' ?>
            <?php $fx = dojo_effect_text($move); ?>
            <?= $fx !== '' ? '<br>' . htmlspecialchars($fx) : '' ?>
          </p>
          <?php if (count($moveset['moves']) > 1): ?>
            <form class="dojo-inline-form" method="post" action="<?= $form_action ?>">
              <input type="hidden" name="action" value="unlearn">
              <input type="hidden" name="pet_id" value="<?= $selected_pet['id'] ?>">
              <input type="hidden" name="slot" value="<?= $slot ?>">
              <button class="btn" type="submit">Unlearn (<?= $unlearn_price ?>)</button>
            </form>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="dojo-slot is-empty">
          <h4>Slot <?= $slot ?></h4>
          <p class="mini">Empty</p>
        </div>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
</div>

<?php if ($learn_pick): ?>
<div class="card glass">
  <h2>Where should <?= htmlspecialchars($learn_pick['name']) ?> go?</h2>
  <p class="muted">
    Pick a moveslot for the new technique. Learning over an occupied slot replaces that technique.
    The sensei's fee is <span class="dojo-price"><?= $learn_price ?></span>.
  </p>
  <div class="dojo-slots">
    <?php for ($slot = 1; $slot <= 4; $slot++): ?>
      <?php $move = $moves_by_slot[$slot] ?? null; ?>
      <div class="dojo-slot<?= $move ? '' : ' is-empty' ?>">
        <h4>Slot <?= $slot ?></h4>
        <p class="mini"><?= $move ? 'Replaces ' . htmlspecialchars($move['name']) : 'Empty slot' ?></p>
        <form class="dojo-inline-form" method="post" action="<?= $form_action ?>">
          <input type="hidden" name="action" value="learn">
          <input type="hidden" name="pet_id" value="<?= $selected_pet['id'] ?>">
          <input type="hidden" name="move_id" value="<?= (int)$learn_pick['id'] ?>">
          <input type="hidden" name="slot" value="<?= $slot ?>">
          <button class="btn" type="submit"><?= $move ? 'Replace' : 'Learn here' ?></button>
        </form>
      </div>
    <?php endfor; ?>
  </div>
  <p><a class="btn" href="<?= $form_action ?>">Never mind</a></p>
</div>
<?php endif; ?>

<div class="card glass">
  <h2>Techniques on offer</h2>
  <?php if (!$learnable): ?>
    <p class="muted"><?= htmlspecialchars($selected_pet['name']) ?> has learned everything the sensei can teach at this level. Come back stronger.</p>
  <?php else: ?>
    <table class="dojo-table">
      <tr><th>Technique</th><th>Element</th><th>Power</th><th>Effect</th><th>Unlocked at</th><th></th></tr>
      <?php foreach ($learnable as $move): ?>
        <tr>
          <td><strong><?= htmlspecialchars($move['name']) ?></strong></td>
          <td><?= htmlspecialchars($move['elementName']) ?></td>
          <td><?= $move['power'] > 0 ? (int)$move['power'] : 'Status' ?></td>
          <td class="mini"><?= htmlspecialchars(dojo_effect_text($move)) ?></td>
          <td>Lv. <?= (int)$move['learntAtLevel'] ?></td>
          <td><a class="btn" href="?pg=yn-dojo&amp;pet=<?= $selected_pet['id'] ?>&amp;learn=<?= (int)$move['id'] ?>">Learn (<?= $learn_price ?>)</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
<?php endif; ?>

<p><a class="btn" href="?pg=yn">Back to Warraluma</a></p>
<?php endif; ?>
