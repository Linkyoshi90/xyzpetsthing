<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../lib/pets.php';

require_login();

function battle_json_response(array $payload): void {
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function battle_name_initials(string $value): string {
    $value = trim($value);
    if ($value === '') {
        return 'HT';
    }

    $parts = preg_split('/\s+/', $value) ?: [];
    $letters = '';
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        $letters .= strtoupper(substr($part, 0, 1));
        if (strlen($letters) >= 2) {
            break;
        }
    }

    if ($letters === '') {
        $letters = strtoupper(substr($value, 0, 2));
    }

    return substr($letters, 0, 2);
}

function battle_load_element_lookup(): array {
    $rows = q(
        "SELECT element_id, element_name
           FROM elements
          ORDER BY element_id"
    )->fetchAll(PDO::FETCH_ASSOC);

    $lookup = [];
    foreach ($rows as $row) {
        $lookup[(int)$row['element_id']] = (string)$row['element_name'];
    }

    return $lookup;
}

function battle_load_species_elements(array $species_ids): array {
    if (!$species_ids) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($species_ids), '?'));
    $rows = q(
        "SELECT species_id, element_id
           FROM species_elements
          WHERE species_id IN ({$placeholders})
          ORDER BY species_id, element_id",
        $species_ids
    )->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach ($rows as $row) {
        $out[(int)$row['species_id']][] = (int)$row['element_id'];
    }

    return $out;
}

function battle_normalize_pet(array $pet, array $element_lookup): array {
    $level = max(1, (int)($pet['level'] ?? 1));
    $max_hp = (int)($pet['hp_max'] ?? 0);
    if ($max_hp <= 0) {
        $max_hp = max(16, ((int)($pet['base_hp'] ?? 10) * 5) + ($level * 4));
    }

    $current_hp = (int)($pet['hp_current'] ?? 0);
    if ($current_hp <= 0 || $current_hp > $max_hp) {
        $current_hp = $max_hp;
    }

    $name = trim((string)($pet['nickname'] ?? ''));
    if ($name === '') {
        $name = (string)($pet['species_name'] ?? 'Creature');
    }

    $element_ids = array_values(array_map('intval', $pet['elements'] ?? []));
    $element_names = array_values(array_map(
        static fn(int $element_id): string => $element_lookup[$element_id] ?? ('Element ' . $element_id),
        $element_ids
    ));

    return [
        'id' => (int)($pet['pet_instance_id'] ?? 0),
        'speciesId' => (int)($pet['species_id'] ?? 0),
        'name' => $name,
        'species' => (string)($pet['species_name'] ?? 'Creature'),
        'level' => $level,
        'hp' => $current_hp,
        'maxHp' => $max_hp,
        'attack' => (int)($pet['atk'] ?? $pet['base_atk'] ?? 8),
        'defense' => (int)($pet['def'] ?? $pet['base_def'] ?? 5),
        'speed' => (int)($pet['initiative'] ?? $pet['base_init'] ?? 5),
        'elements' => $element_ids,
        'elementNames' => $element_names,
        'image' => pet_image_url((string)($pet['species_name'] ?? ''), $pet['color_name'] ?? null),
        'moves' => [],
    ];
}

function battle_load_team_for_user(int $user_id, array $element_lookup): array {
    $rows = q(
        "SELECT pi.pet_instance_id, pi.species_id, pi.nickname, pi.color_id, pi.level,
                pi.hp_current, pi.hp_max, pi.atk, pi.def, pi.initiative,
                ps.species_name, ps.base_hp, ps.base_atk, ps.base_def, ps.base_init,
                pc.color_name
           FROM pet_instances pi
           JOIN pet_species ps ON ps.species_id = pi.species_id
           LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
          WHERE pi.owner_user_id = ?
            AND COALESCE(pi.inactive, 0) = 0
          ORDER BY pi.pet_instance_id",
        [$user_id]
    )->fetchAll(PDO::FETCH_ASSOC);

    $species_ids = array_values(array_unique(array_map(static fn(array $row): int => (int)$row['species_id'], $rows)));
    $elements_by_species = battle_load_species_elements($species_ids);

    return array_map(static function (array $row) use ($elements_by_species, $element_lookup): array {
        $row['elements'] = $elements_by_species[(int)$row['species_id']] ?? [];
        return battle_normalize_pet($row, $element_lookup);
    }, $rows);
}

function battle_load_random_trainer(): ?array {
    $trainer = q(
        "SELECT trainer_id,
                class_name,
                trainer_name,
                encounter_line,
                defeat_line,
                defeat_currency
           FROM trainers
          ORDER BY RAND()
          LIMIT 1"
    )->fetch(PDO::FETCH_ASSOC);

    if (!$trainer) {
        return null;
    }

    $class_name = trim((string)$trainer['class_name']);
    $trainer_name = trim((string)$trainer['trainer_name']);

    return [
        'id' => (int)$trainer['trainer_id'],
        'className' => $class_name,
        'name' => $trainer_name,
        'displayName' => trim($class_name . ' ' . $trainer_name),
        'encounterLine' => (string)$trainer['encounter_line'],
        'defeatLine' => (string)$trainer['defeat_line'],
        'defeatCurrency' => max(0, (int)$trainer['defeat_currency']),
        'initials' => battle_name_initials(trim($class_name . ' ' . $trainer_name)),
    ];
}

function battle_load_trainer_team(int $trainer_id, array $element_lookup): array {
    $rows = q(
        "SELECT pi.pet_instance_id, pi.species_id, pi.nickname, pi.color_id, pi.level,
                pi.hp_current, pi.hp_max, pi.atk, pi.def, pi.initiative,
                ps.species_name, ps.base_hp, ps.base_atk, ps.base_def, ps.base_init,
                pc.color_name
           FROM trainer_roster tr
           JOIN pet_instances pi ON pi.pet_instance_id = tr.pet_instance_id
           JOIN pet_species ps ON ps.species_id = pi.species_id
           LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
          WHERE tr.trainer_id = ?
          ORDER BY tr.roster_position, tr.pet_instance_id",
        [$trainer_id]
    )->fetchAll(PDO::FETCH_ASSOC);

    $species_ids = array_values(array_unique(array_map(static fn(array $row): int => (int)$row['species_id'], $rows)));
    $elements_by_species = battle_load_species_elements($species_ids);

    return array_map(static function (array $row) use ($elements_by_species, $element_lookup): array {
        $row['elements'] = $elements_by_species[(int)$row['species_id']] ?? [];
        return battle_normalize_pet($row, $element_lookup);
    }, $rows);
}

function battle_load_attack_pool(array $element_lookup): array {
    $rows = q(
        "SELECT move_id AS id,
                move_name AS name,
                power,
                element_id,
                accuracy_percent,
                priority
           FROM moves
          WHERE power IS NOT NULL
            AND category <> 'status'
          ORDER BY power, move_id"
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        $rows = q(
            "SELECT attack_id AS id,
                    attack_name AS name,
                    base_damage AS power,
                    element_id,
                    100.00 AS accuracy_percent,
                    0 AS priority
               FROM attacks
              ORDER BY base_damage, attack_id"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    if (!$rows) {
        $rows = [
            ['id' => 1, 'name' => 'Tackle', 'power' => 40, 'element_id' => 1, 'accuracy_percent' => 100, 'priority' => 0],
            ['id' => 2, 'name' => 'Quick Attack', 'power' => 40, 'element_id' => 1, 'accuracy_percent' => 100, 'priority' => 1],
            ['id' => 3, 'name' => 'Ember', 'power' => 40, 'element_id' => 2, 'accuracy_percent' => 100, 'priority' => 0],
            ['id' => 4, 'name' => 'Water Gun', 'power' => 40, 'element_id' => 3, 'accuracy_percent' => 100, 'priority' => 0],
        ];
    }

    return array_map(static function (array $row) use ($element_lookup): array {
        $element_id = (int)($row['element_id'] ?? 1);
        return [
            'id' => (int)($row['id'] ?? 0),
            'name' => (string)($row['name'] ?? 'Strike'),
            'power' => max(1, (int)($row['power'] ?? 1)),
            'elementId' => $element_id,
            'elementName' => $element_lookup[$element_id] ?? ('Element ' . $element_id),
            'accuracy' => (float)($row['accuracy_percent'] ?? 100),
            'priority' => (int)($row['priority'] ?? 0),
        ];
    }, $rows);
}

function battle_pick_moves_for_pet(array $pet, array $attack_pool): array {
    if (!$attack_pool) {
        return [];
    }

    $matching = [];
    $neutral = [];
    $other = [];
    foreach ($attack_pool as $move) {
        if (in_array((int)$move['elementId'], $pet['elements'], true)) {
            $matching[] = $move;
        } elseif ((int)$move['elementId'] === 1) {
            $neutral[] = $move;
        } else {
            $other[] = $move;
        }
    }

    $pool = array_merge($matching, $neutral, $other);
    $count = count($pool);
    if ($count === 0) {
        return [];
    }

    $offset = (($pet['id'] ?? 0) + ($pet['speciesId'] ?? 0) + ($pet['level'] ?? 1)) % $count;
    $rotated = array_merge(array_slice($pool, $offset), array_slice($pool, 0, $offset));

    $picked = [];
    $seen = [];
    foreach ($rotated as $move) {
        $move_id = (int)$move['id'];
        if (isset($seen[$move_id])) {
            continue;
        }
        $seen[$move_id] = true;
        $picked[] = $move;
        if (count($picked) === 4) {
            break;
        }
    }

    return $picked ?: array_slice($attack_pool, 0, 4);
}

function battle_assign_moves(array $team, array $attack_pool): array {
    foreach ($team as &$pet) {
        $pet['moves'] = battle_pick_moves_for_pet($pet, $attack_pool);
    }
    unset($pet);

    return $team;
}

function battle_load_effectiveness(): array {
    $rows = q(
        "SELECT element_id, target_element_id, effectiveness
           FROM element_calc"
    )->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach ($rows as $row) {
        $out[(int)$row['element_id'] . ':' . (int)$row['target_element_id']] = (float)$row['effectiveness'];
    }

    return $out;
}

function battle_is_battle_item_row(array $row): bool {
    $name = strtolower((string)($row['item_name'] ?? ''));
    $description = strtolower((string)($row['item_description'] ?? ''));
    return str_contains($name, 'berry')
        || str_contains($name, 'potion')
        || str_contains($name, 'heal')
        || str_contains($description, 'restore')
        || str_contains($description, 'hp');
}

function battle_load_battle_items(int $user_id): array {
    if ($user_id <= 0) {
        return [];
    }

    $rows = q(
        "SELECT ui.item_id, ui.quantity, i.item_name, i.item_description, i.replenish
           FROM user_inventory ui
           JOIN items i ON i.item_id = ui.item_id
          WHERE ui.user_id = ?
            AND ui.quantity > 0
          ORDER BY i.item_name",
        [$user_id]
    )->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($rows as $row) {
        if (!battle_is_battle_item_row($row)) {
            continue;
        }

        $items[] = [
            'id' => (int)$row['item_id'],
            'name' => (string)$row['item_name'],
            'description' => (string)($row['item_description'] ?? ''),
            'quantity' => (int)$row['quantity'],
            'heal' => max(1, (int)($row['replenish'] ?? 20)),
        ];
    }

    return $items;
}

function battle_require_active_session(string $token, int $trainer_id = 0): array {
    $battle = $_SESSION['battle_minigame'] ?? null;
    if (!is_array($battle) || !hash_equals((string)($battle['token'] ?? ''), $token)) {
        battle_json_response(['ok' => false, 'message' => 'This battle session is no longer valid.']);
    }

    if ($trainer_id > 0 && (int)($battle['trainer_id'] ?? 0) !== $trainer_id) {
        battle_json_response(['ok' => false, 'message' => 'The trainer encounter could not be verified.']);
    }

    return $battle;
}

function battle_consume_item(int $user_id, int $item_id, string $token): void {
    if ($user_id <= 0 || $item_id <= 0) {
        battle_json_response(['ok' => false, 'message' => 'That item could not be used.']);
    }

    battle_require_active_session($token);

    $row = q(
        "SELECT ui.quantity, i.item_name, i.item_description, i.replenish
           FROM user_inventory ui
           JOIN items i ON i.item_id = ui.item_id
          WHERE ui.user_id = ?
            AND ui.item_id = ?
            AND ui.quantity > 0
          LIMIT 1",
        [$user_id, $item_id]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$row || !battle_is_battle_item_row($row)) {
        battle_json_response(['ok' => false, 'message' => 'That battle item is no longer available.']);
    }

    q(
        "UPDATE user_inventory
            SET quantity = quantity - 1
          WHERE user_id = ?
            AND item_id = ?
            AND quantity > 0",
        [$user_id, $item_id]
    );

    q(
        "DELETE FROM user_inventory
          WHERE user_id = ?
            AND item_id = ?
            AND quantity <= 0",
        [$user_id, $item_id]
    );

    $quantity = q(
        "SELECT quantity
           FROM user_inventory
          WHERE user_id = ?
            AND item_id = ?",
        [$user_id, $item_id]
    )->fetchColumn();

    battle_json_response([
        'ok' => true,
        'itemId' => $item_id,
        'quantity' => max(0, (int)$quantity),
        'heal' => max(1, (int)($row['replenish'] ?? 20)),
        'message' => 'Item used.',
    ]);
}

function battle_award_victory(int $user_id, int $trainer_id, string $token): void {
    $battle = battle_require_active_session($token, $trainer_id);
    $reward = max(0, (int)($battle['reward'] ?? 0));

    if (!empty($battle['awarded'])) {
        battle_json_response([
            'ok' => true,
            'message' => 'Reward already collected.',
            'cash' => (int)(current_user()['cash'] ?? 0),
        ]);
    }

    if ($reward > 0 && $user_id > 0) {
        q(
            "INSERT INTO user_balances(user_id, currency_id, balance)
             VALUES(?, 1, ?)
             ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)",
            [$user_id, $reward]
        );

        $new_cash = q(
            "SELECT COALESCE(balance, 0)
               FROM user_balances
              WHERE user_id = ?
                AND currency_id = 1",
            [$user_id]
        )->fetchColumn();

        $_SESSION['user']['cash'] = (int)$new_cash;
    } else {
        $_SESSION['user']['cash'] = (int)(($_SESSION['user']['cash'] ?? 0) + $reward);
    }

    $_SESSION['battle_minigame']['awarded'] = true;

    battle_json_response([
        'ok' => true,
        'message' => 'Reward collected.',
        'cash' => (int)($_SESSION['user']['cash'] ?? 0),
    ]);
}

$user = current_user();
$user_id = (int)($user['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['battle_action'] ?? '');
    if ($action === 'award_victory') {
        battle_award_victory(
            $user_id,
            (int)($_POST['trainer_id'] ?? 0),
            (string)($_POST['battle_token'] ?? '')
        );
    }

    if ($action === 'use_item') {
        battle_consume_item(
            $user_id,
            (int)($_POST['item_id'] ?? 0),
            (string)($_POST['battle_token'] ?? '')
        );
    }
}

$element_lookup = battle_load_element_lookup();
$trainer = battle_load_random_trainer();
$attack_pool = battle_load_attack_pool($element_lookup);
$player_team = battle_load_team_for_user($user_id, $element_lookup);
$trainer_team = $trainer ? battle_load_trainer_team($trainer['id'], $element_lookup) : [];
$player_team = battle_assign_moves($player_team, $attack_pool);
$trainer_team = battle_assign_moves($trainer_team, $attack_pool);
$items = battle_load_battle_items($user_id);
$effectiveness = battle_load_effectiveness();
$battle_ready = $trainer && $player_team && $trainer_team;

if ($battle_ready) {
    $battle_token = bin2hex(random_bytes(16));
    $_SESSION['battle_minigame'] = [
        'token' => $battle_token,
        'trainer_id' => $trainer['id'],
        'reward' => $trainer['defeatCurrency'],
        'awarded' => false,
    ];
} else {
    $battle_token = '';
}

$battle_payload = [
    'ready' => (bool)$battle_ready,
    'trainer' => $trainer,
    'playerTeam' => $player_team,
    'trainerTeam' => $trainer_team,
    'items' => $items,
    'effectiveness' => $effectiveness,
    'token' => $battle_token,
    'returnUrl' => 'index.php?pg=games',
    'currencyLabel' => APP_CURRENCY_SHORT_NAME,
];
?>

<?php if (!$battle_ready): ?>
<section class="battle-blocker glass">
  <h1>Trainer Battle</h1>
  <p class="muted">
    This encounter needs one logged-in creature, one trainer entry, and a trainer roster before the battle can begin.
  </p>
  <a class="btn" href="index.php?pg=games">Back to Games</a>
</section>
<?php else: ?>
<section class="battle-shell" id="battle-app" aria-live="polite">
  <div class="battle-stage">
    <div class="battle-stage-backdrop"></div>
    <div class="battle-stage-grid"></div>
    <div class="battle-stage-flare battle-stage-flare-left"></div>
    <div class="battle-stage-flare battle-stage-flare-right"></div>

    <div class="battle-intro" id="battle-intro">
      <div class="battle-intro-panel">
        <p class="battle-intro-kicker">Random Encounter</p>
        <div class="battle-intro-crest" id="intro-crest"><?= htmlspecialchars($trainer['initials'] ?? 'HT') ?></div>
        <h1 id="intro-title"><?= htmlspecialchars($trainer['displayName'] ?? '') ?></h1>
        <p class="battle-intro-line" id="intro-line"><?= htmlspecialchars($trainer['encounterLine'] ?? '') ?></p>
        <button class="btn battle-intro-start" id="intro-start" type="button">Start Battle</button>
      </div>
    </div>

    <div class="battle-encounter-banner" id="battle-banner">
      <div class="battle-encounter-copy">
        <span class="battle-banner-label"><?= htmlspecialchars($trainer['className'] ?? 'Trainer') ?></span>
        <strong><?= htmlspecialchars($trainer['name'] ?? 'Unknown') ?></strong>
      </div>
      <div class="battle-encounter-crest"><?= htmlspecialchars($trainer['initials'] ?? 'HT') ?></div>
    </div>

    <div class="battle-announcer" id="battle-announcer" aria-live="assertive"></div>

    <div class="battle-field">
      <div class="battle-platform npc"></div>
      <div class="battle-platform player"></div>

      <div class="battle-combatant npc" id="npc-combatant">
        <div class="battle-status-card">
          <div class="battle-status-topline">
            <strong id="npc-name"></strong>
            <span class="battle-level" id="npc-level"></span>
          </div>
          <div class="battle-elements" id="npc-elements"></div>
          <div class="battle-hp-meta">
            <span class="battle-hp-label">HP</span>
            <span class="battle-hp-text" id="npc-hp-text">0/0</span>
          </div>
          <div class="battle-hpbar">
            <div class="battle-hpbar-fill" id="npc-hp-fill"></div>
          </div>
        </div>
        <div class="battle-creature-shell">
          <div class="battle-summon-ring"></div>
          <img class="battle-creature" id="npc-image" src="" alt="">
        </div>
      </div>

      <div class="battle-combatant player" id="player-combatant">
        <div class="battle-status-card">
          <div class="battle-status-topline">
            <strong id="player-name"></strong>
            <span class="battle-level" id="player-level"></span>
          </div>
          <div class="battle-elements" id="player-elements"></div>
          <div class="battle-hp-meta">
            <span class="battle-hp-label">HP</span>
            <span class="battle-hp-text" id="player-hp-text">0/0</span>
          </div>
          <div class="battle-hpbar">
            <div class="battle-hpbar-fill" id="player-hp-fill"></div>
          </div>
        </div>
        <div class="battle-creature-shell">
          <div class="battle-summon-ring"></div>
          <img class="battle-creature" id="player-image" src="" alt="">
        </div>
      </div>
    </div>
  </div>

  <div class="battle-panel">
    <div class="battle-feed glass">
      <div class="battle-feed-header">
        <span class="battle-feed-label">Combat Feed</span>
        <strong id="battle-turn-indicator">Awaiting clash</strong>
      </div>
      <div class="battle-log" id="battle-log"></div>
    </div>

    <div class="battle-menu-panel glass">
      <div class="battle-menu-header">
        <div>
          <span class="battle-feed-label" id="battle-menu-kicker">Battle Menu</span>
          <strong id="battle-menu-title">Choose a command</strong>
        </div>
        <span class="battle-menu-hint">Mouse, arrows, enter, esc</span>
      </div>
      <div class="battle-menu" id="battle-menu"></div>
      <div class="battle-detail-card" id="battle-detail-card">
        <p class="battle-detail-empty">Your active creature and the opposing creature are ready. Pick the next move.</p>
      </div>
    </div>
  </div>

  <script id="battle-payload" type="application/json"><?= json_encode($battle_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
</section>
<?php endif; ?>
