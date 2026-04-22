<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../lib/pets.php';

require_login();

function battle_json_response(array $payload): void {
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function battle_normalize_pet(array $pet): array {
    $level = max(1, (int)($pet['level'] ?? 1));
    $max_hp = (int)($pet['hp_max'] ?? 0);
    if ($max_hp <= 0) {
        $max_hp = max(10, ((int)($pet['base_hp'] ?? 10) * 5) + ($level * 4));
    }
    $current_hp = (int)($pet['hp_current'] ?? 0);
    if ($current_hp <= 0 || $current_hp > $max_hp) {
        $current_hp = $max_hp;
    }
    $name = trim((string)($pet['nickname'] ?? ''));
    if ($name === '') {
        $name = (string)($pet['species_name'] ?? 'Creature');
    }

    return [
        'id' => (int)($pet['pet_instance_id'] ?? 0),
        'name' => $name,
        'species' => (string)($pet['species_name'] ?? 'Creature'),
        'level' => $level,
        'hp' => $current_hp,
        'maxHp' => $max_hp,
        'attack' => (int)($pet['atk'] ?? $pet['base_atk'] ?? 8),
        'defense' => (int)($pet['def'] ?? $pet['base_def'] ?? 5),
        'speed' => (int)($pet['initiative'] ?? $pet['base_init'] ?? 5),
        'elements' => array_values(array_map('intval', $pet['elements'] ?? [])),
        'image' => pet_image_url((string)($pet['species_name'] ?? ''), $pet['color_name'] ?? null),
    ];
}

function battle_load_team_for_user(int $user_id): array {
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

    $species_ids = array_values(array_unique(array_map(static fn($row) => (int)$row['species_id'], $rows)));
    $elements_by_species = battle_load_species_elements($species_ids);

    return array_map(static function ($row) use ($elements_by_species) {
        $row['elements'] = $elements_by_species[(int)$row['species_id']] ?? [];
        return battle_normalize_pet($row);
    }, $rows);
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

    return [
        'id' => (int)$trainer['trainer_id'],
        'className' => (string)$trainer['class_name'],
        'name' => (string)$trainer['trainer_name'],
        'encounterLine' => (string)$trainer['encounter_line'],
        'defeatLine' => (string)$trainer['defeat_line'],
        'defeatCurrency' => max(0, (int)$trainer['defeat_currency']),
    ];
}

function battle_load_trainer_team(int $trainer_id): array {
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

    $species_ids = array_values(array_unique(array_map(static fn($row) => (int)$row['species_id'], $rows)));
    $elements_by_species = battle_load_species_elements($species_ids);

    return array_map(static function ($row) use ($elements_by_species) {
        $row['elements'] = $elements_by_species[(int)$row['species_id']] ?? [];
        return battle_normalize_pet($row);
    }, $rows);
}

function battle_load_attacks(): array {
    $rows = q(
        "SELECT move_id AS id,
                move_name AS name,
                power,
                element_id
           FROM moves
          WHERE power IS NOT NULL
            AND category <> 'status'
          ORDER BY move_id
          LIMIT 24"
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        $rows = [
            ['id' => 1, 'name' => 'Strike', 'power' => 35, 'element_id' => 1],
            ['id' => 2, 'name' => 'Heavy Blow', 'power' => 55, 'element_id' => 1],
        ];
    }

    return array_map(static fn($row) => [
        'id' => (int)$row['id'],
        'name' => (string)$row['name'],
        'power' => max(1, (int)$row['power']),
        'elementId' => (int)$row['element_id'],
    ], $rows);
}

function battle_load_effectiveness(): array {
    $rows = q("SELECT element_id, target_element_id, effectiveness FROM element_calc")->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($rows as $row) {
        $out[(int)$row['element_id'] . ':' . (int)$row['target_element_id']] = (float)$row['effectiveness'];
    }
    return $out;
}

function battle_load_battle_items(int $user_id): array {
    if ($user_id === 0) {
        return [];
    }
    $rows = q(
        "SELECT ui.item_id, ui.quantity, i.item_name, i.item_description, i.replenish
           FROM user_inventory ui
           JOIN items i ON i.item_id = ui.item_id
          WHERE ui.user_id = ?
            AND ui.quantity > 0
            AND (
                LOWER(i.item_name) LIKE '%berry%'
                OR LOWER(i.item_name) LIKE '%potion%'
                OR LOWER(i.item_name) LIKE '%heal%'
                OR LOWER(i.item_description) LIKE '%restore%'
                OR LOWER(i.item_description) LIKE '%hp%'
            )
          ORDER BY i.item_name
          LIMIT 20",
        [$user_id]
    )->fetchAll(PDO::FETCH_ASSOC);

    return array_map(static fn($row) => [
        'id' => (int)$row['item_id'],
        'name' => (string)$row['item_name'],
        'description' => (string)($row['item_description'] ?? ''),
        'quantity' => (int)$row['quantity'],
        'heal' => max(1, (int)($row['replenish'] ?? 20)),
    ], $rows);
}

function battle_award_victory(int $user_id, int $trainer_id, string $token): void {
    $battle = $_SESSION['battle_minigame'] ?? null;
    if (!$battle || !hash_equals((string)($battle['token'] ?? ''), $token) || (int)($battle['trainer_id'] ?? 0) !== $trainer_id) {
        battle_json_response(['ok' => false, 'message' => 'The battle reward could not be verified.']);
    }

    $reward = max(0, (int)($battle['reward'] ?? 0));
    if (!empty($battle['awarded'])) {
        battle_json_response(['ok' => true, 'message' => 'Reward already collected.', 'cash' => (int)(current_user()['cash'] ?? 0)]);
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
              WHERE user_id = ? AND currency_id = 1",
            [$user_id]
        )->fetchColumn();
        $_SESSION['user']['cash'] = (int)$new_cash;
    } else {
        $_SESSION['user']['cash'] = (int)(($_SESSION['user']['cash'] ?? 0) + $reward);
    }

    $_SESSION['battle_minigame']['awarded'] = true;
    battle_json_response(['ok' => true, 'message' => 'Reward collected.', 'cash' => (int)($_SESSION['user']['cash'] ?? 0)]);
}

$user = current_user();
$user_id = (int)($user['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['battle_action'] ?? '') === 'award_victory') {
    battle_award_victory($user_id, (int)($_POST['trainer_id'] ?? 0), (string)($_POST['battle_token'] ?? ''));
}

$trainer = battle_load_random_trainer();
$player_team = battle_load_team_for_user($user_id);
$trainer_team = $trainer ? battle_load_trainer_team($trainer['id']) : [];
$attacks = battle_load_attacks();
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
    'ready' => $battle_ready,
    'trainer' => $trainer,
    'playerTeam' => $player_team,
    'trainerTeam' => $trainer_team,
    'attacks' => $attacks,
    'items' => $items,
    'effectiveness' => $effectiveness,
    'token' => $battle_token,
    'returnUrl' => 'index.php?pg=games',
];
?>

<style>
.battle-shell {
  min-height: 72vh;
  display: grid;
  align-items: stretch;
  gap: 14px;
}
.battle-stage {
  position: relative;
  overflow: hidden;
  min-height: 430px;
  border: 1px solid rgba(90, 105, 120, .22);
  border-radius: 8px;
  background:
    linear-gradient(180deg, rgba(187, 218, 239, .72) 0%, rgba(222, 239, 229, .74) 48%, rgba(128, 166, 122, .72) 49%, rgba(91, 130, 91, .78) 100%);
}
.battle-intro {
  position: absolute;
  inset: 0;
  z-index: 8;
  display: grid;
  place-items: center;
  padding: 24px;
  background: rgba(11, 18, 27, .76);
  color: #fff;
  text-align: center;
  transition: opacity .35s ease, transform .35s ease;
}
.battle-intro.is-hidden {
  opacity: 0;
  transform: translateY(-12px);
  pointer-events: none;
}
.battle-intro h1 {
  margin: 0 0 8px;
}
.battle-intro p {
  max-width: 620px;
  margin: 0 auto 18px;
}
.battle-field {
  position: absolute;
  inset: 0;
}
.battle-platform {
  position: absolute;
  width: min(36vw, 360px);
  height: 70px;
  border-radius: 50%;
  background: rgba(42, 75, 48, .28);
  filter: blur(.2px);
}
.battle-platform.npc {
  top: 152px;
  right: 8%;
}
.battle-platform.player {
  bottom: 74px;
  left: 7%;
}
.battle-combatant {
  position: absolute;
  display: grid;
  justify-items: center;
  width: min(34vw, 280px);
  transition: transform .28s ease, opacity .28s ease;
}
.battle-combatant.npc {
  top: 54px;
  right: 8%;
  transform: translateX(36px);
}
.battle-combatant.player {
  bottom: 98px;
  left: 8%;
  transform: translateX(-36px);
}
.battle-combatant.is-summoned {
  animation: battleSummon .55s ease forwards;
}
.battle-combatant.is-hit {
  animation: battleHit .34s ease;
}
.battle-combatant.is-acting {
  transform: translateX(0) scale(1.05);
}
.battle-creature {
  max-width: min(30vw, 250px);
  max-height: 230px;
  object-fit: contain;
  filter: drop-shadow(0 12px 18px rgba(16, 24, 30, .25));
}
.battle-status {
  width: min(260px, 90%);
  padding: 8px 10px;
  border-radius: 8px;
  background: rgba(255, 255, 255, .86);
  color: #17202a;
  box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
}
.battle-status strong,
.battle-status span {
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.battle-hpbar {
  height: 8px;
  margin: 6px 0;
  overflow: hidden;
  border-radius: 999px;
  background: rgba(30, 45, 55, .14);
}
.battle-hpbar-fill {
  width: 100%;
  height: 100%;
  background: #4caf50;
  transition: width .35s ease, background .25s ease;
}
.damage-pop {
  position: absolute;
  z-index: 6;
  min-width: 46px;
  padding: 4px 8px;
  border-radius: 999px;
  background: rgba(19, 23, 30, .84);
  color: #fff;
  font-weight: 800;
  text-align: center;
  animation: damageFloat .8s ease forwards;
}
.battle-panel {
  display: grid;
  grid-template-columns: minmax(0, 1.2fr) minmax(260px, .8fr);
  gap: 14px;
}
.battle-log,
.battle-menu {
  min-height: 164px;
  padding: 14px;
  border: 1px solid rgba(90, 105, 120, .22);
  border-radius: 8px;
  background: var(--glass-bg);
}
.battle-log {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}
.battle-log p {
  margin: 4px 0;
}
.battle-menu-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}
.battle-menu button {
  width: 100%;
  min-height: 42px;
  text-align: left;
}
.battle-menu button.is-selected,
.battle-menu button:focus {
  outline: 2px solid rgba(69, 132, 191, .78);
  outline-offset: 2px;
}
.battle-menu .quit-option {
  grid-column: 1 / -1;
}
.battle-small {
  display: block;
  margin-top: 2px;
  color: var(--muted);
  font-size: .86rem;
}
.battle-blocker {
  padding: 18px;
}
@keyframes battleSummon {
  from { opacity: 0; transform: translateY(18px) scale(.86); }
  to { opacity: 1; transform: translateX(0) translateY(0) scale(1); }
}
@keyframes battleHit {
  0%, 100% { filter: none; transform: translateX(0); }
  25% { filter: brightness(1.4); transform: translateX(-8px); }
  50% { transform: translateX(8px); }
}
@keyframes damageFloat {
  from { opacity: 1; transform: translateY(0) scale(1); }
  to { opacity: 0; transform: translateY(-48px) scale(1.14); }
}
@media (max-width: 760px) {
  .battle-stage {
    min-height: 360px;
  }
  .battle-panel {
    grid-template-columns: 1fr;
  }
  .battle-combatant {
    width: 42vw;
  }
  .battle-creature {
    max-width: 40vw;
    max-height: 168px;
  }
  .battle-platform.npc {
    right: 3%;
  }
  .battle-platform.player {
    left: 2%;
  }
}
</style>

<?php if (!$battle_ready): ?>
<section class="battle-blocker glass">
  <h1>Battle Minigame</h1>
  <p class="muted">
    The battle screen is ready, but it needs a logged-in team, at least one trainer, and at least one trainer roster entry.
  </p>
  <a class="btn" href="index.php?pg=games">Back to Games</a>
</section>
<?php else: ?>
<section class="battle-shell" id="battle-app" aria-live="polite">
  <div class="battle-stage">
    <div class="battle-intro" id="battle-intro">
      <div>
        <p>Trainer encounter</p>
        <h1 id="intro-title"></h1>
        <p id="intro-line"></p>
        <button class="btn" id="intro-start" type="button">Begin Battle</button>
      </div>
    </div>

    <div class="battle-field">
      <div class="battle-platform npc"></div>
      <div class="battle-platform player"></div>

      <div class="battle-combatant npc" id="npc-combatant">
        <div class="battle-status">
          <strong id="npc-name"></strong>
          <div class="battle-hpbar"><div class="battle-hpbar-fill" id="npc-hp-fill"></div></div>
          <span id="npc-hp-text"></span>
        </div>
        <img class="battle-creature" id="npc-image" src="" alt="">
      </div>

      <div class="battle-combatant player" id="player-combatant">
        <div class="battle-status">
          <strong id="player-name"></strong>
          <div class="battle-hpbar"><div class="battle-hpbar-fill" id="player-hp-fill"></div></div>
          <span id="player-hp-text"></span>
        </div>
        <img class="battle-creature" id="player-image" src="" alt="">
      </div>
    </div>
  </div>

  <div class="battle-panel">
    <div class="battle-log" id="battle-log"></div>
    <div class="battle-menu">
      <div id="battle-menu"></div>
    </div>
  </div>
</section>

<script>
(() => {
  const data = <?= json_encode($battle_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  const state = {
    playerTeam: data.playerTeam.map(p => ({...p, fainted: false})),
    trainerTeam: data.trainerTeam.map(p => ({...p, fainted: false})),
    playerIndex: 0,
    trainerIndex: 0,
    locked: true,
    menu: 'root',
    selected: 0,
    awarded: false,
  };

  const el = {
    intro: document.getElementById('battle-intro'),
    introTitle: document.getElementById('intro-title'),
    introLine: document.getElementById('intro-line'),
    introStart: document.getElementById('intro-start'),
    menu: document.getElementById('battle-menu'),
    log: document.getElementById('battle-log'),
    npc: document.getElementById('npc-combatant'),
    player: document.getElementById('player-combatant'),
    npcName: document.getElementById('npc-name'),
    playerName: document.getElementById('player-name'),
    npcHpText: document.getElementById('npc-hp-text'),
    playerHpText: document.getElementById('player-hp-text'),
    npcHpFill: document.getElementById('npc-hp-fill'),
    playerHpFill: document.getElementById('player-hp-fill'),
    npcImage: document.getElementById('npc-image'),
    playerImage: document.getElementById('player-image'),
  };

  const wait = ms => new Promise(resolve => window.setTimeout(resolve, ms));
  const currentPlayer = () => state.playerTeam[state.playerIndex];
  const currentNpc = () => state.trainerTeam[state.trainerIndex];
  const alivePlayer = () => state.playerTeam.some(p => p.hp > 0);
  const aliveNpc = () => state.trainerTeam.some(p => p.hp > 0);
  const clamp = (num, min, max) => Math.max(min, Math.min(max, num));

  function log(line) {
    const p = document.createElement('p');
    p.textContent = line;
    el.log.appendChild(p);
    while (el.log.children.length > 5) el.log.firstElementChild.remove();
  }

  function setHpColor(fill, pct) {
    fill.style.background = pct <= 25 ? '#d94c4c' : pct <= 50 ? '#d6a23b' : '#4caf50';
  }

  function renderCombatants() {
    const player = currentPlayer();
    const npc = currentNpc();
    el.playerName.textContent = `${player.name} Lv.${player.level}`;
    el.npcName.textContent = `${npc.name} Lv.${npc.level}`;
    el.playerImage.src = player.image;
    el.npcImage.src = npc.image;
    el.playerImage.alt = player.name;
    el.npcImage.alt = npc.name;
    updateHp(player, 'player', false);
    updateHp(npc, 'npc', false);
  }

  function updateHp(creature, side, animate = true) {
    const text = side === 'player' ? el.playerHpText : el.npcHpText;
    const fill = side === 'player' ? el.playerHpFill : el.npcHpFill;
    const pct = creature.maxHp > 0 ? clamp((creature.hp / creature.maxHp) * 100, 0, 100) : 0;
    fill.style.width = `${pct}%`;
    setHpColor(fill, pct);
    if (!animate) {
      text.textContent = `${creature.hp}/${creature.maxHp} HP`;
      return Promise.resolve();
    }

    const start = Number(text.dataset.hp || creature.maxHp);
    const end = creature.hp;
    text.dataset.hp = String(end);
    const duration = 520;
    const started = performance.now();
    return new Promise(resolve => {
      function tick(now) {
        const progress = clamp((now - started) / duration, 0, 1);
        const value = Math.round(start + ((end - start) * progress));
        text.textContent = `${value}/${creature.maxHp} HP`;
        if (progress < 1) requestAnimationFrame(tick);
        else resolve();
      }
      requestAnimationFrame(tick);
    });
  }

  function effectivenessFor(attack, target) {
    if (!target.elements.length) return 1;
    return target.elements.reduce((total, elementId) => {
      const key = `${attack.elementId}:${elementId}`;
      return total * Number(data.effectiveness[key] ?? 1);
    }, 1);
  }

  function calculateDamage(attack, target) {
    const scaled = attack.power * effectivenessFor(attack, target);
    return Math.max(1, Math.round(scaled - target.defense));
  }

  function popDamage(side, amount) {
    const host = side === 'player' ? el.player : el.npc;
    const bubble = document.createElement('div');
    bubble.className = 'damage-pop';
    bubble.textContent = `-${amount}`;
    bubble.style.left = '46%';
    bubble.style.top = '34%';
    host.appendChild(bubble);
    bubble.addEventListener('animationend', () => bubble.remove(), {once: true});
  }

  async function performAttack(attacker, target, attack, attackerSide) {
    const targetSide = attackerSide === 'player' ? 'npc' : 'player';
    const attackerEl = attackerSide === 'player' ? el.player : el.npc;
    const targetEl = targetSide === 'player' ? el.player : el.npc;
    state.locked = true;
    attackerEl.classList.add('is-acting');
    log(`${attacker.name} used ${attack.name}.`);
    await wait(260);
    attackerEl.classList.remove('is-acting');
    const damage = calculateDamage(attack, target);
    target.hp = Math.max(0, target.hp - damage);
    targetEl.classList.add('is-hit');
    popDamage(targetSide, damage);
    await updateHp(target, targetSide, true);
    await wait(220);
    targetEl.classList.remove('is-hit');
    if (target.hp <= 0) {
      target.fainted = true;
      log(`${target.name} is down.`);
      await handleFaint(targetSide);
    }
  }

  function randomNpcAttack() {
    const choices = data.attacks.filter(a => a.power > 0);
    return choices[Math.floor(Math.random() * choices.length)] || data.attacks[0];
  }

  async function handleFaint(side) {
    if (side === 'npc') {
      const next = state.trainerTeam.findIndex(p => p.hp > 0);
      if (next === -1) {
        await winBattle();
        return;
      }
      state.trainerIndex = next;
      await wait(450);
      renderCombatants();
      el.npc.classList.remove('is-summoned');
      void el.npc.offsetWidth;
      el.npc.classList.add('is-summoned');
      log(`${data.trainer.name} sent out ${currentNpc().name}.`);
      await wait(500);
      openRoot();
      return;
    }

    if (!alivePlayer()) {
      log('Your team can no longer battle.');
      openPrompt('Send another creature?', [
        {label: 'Creatures', action: () => openCreatures(true)},
        {label: 'Flee', action: fleeBattle},
      ]);
      return;
    }

    openPrompt(`${currentPlayer().name} is down. Send another creature?`, [
      {label: 'Yes', action: () => openCreatures(true)},
      {label: 'No', action: fleeBattle},
    ]);
  }

  async function playerTurn(attack) {
    if (state.locked) return;
    const player = currentPlayer();
    const npc = currentNpc();
    const npcAttack = randomNpcAttack();
    const playerFirst = player.speed >= npc.speed;

    state.locked = true;
    if (playerFirst) {
      await performAttack(player, npc, attack, 'player');
      if (aliveNpc() && npc.hp > 0) await performAttack(npc, player, npcAttack, 'npc');
    } else {
      await performAttack(npc, player, npcAttack, 'npc');
      if (alivePlayer() && player.hp > 0) await performAttack(player, npc, attack, 'player');
    }

    if (alivePlayer() && aliveNpc() && currentPlayer().hp > 0 && currentNpc().hp > 0) {
      openRoot();
    }
  }

  function button(label, action, description = '') {
    return {label, action, description};
  }

  function renderMenu(options) {
    el.menu.innerHTML = '';
    const grid = document.createElement('div');
    grid.className = 'battle-menu-grid';
    options.forEach((option, index) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = option.quit ? 'btn ghost quit-option' : 'btn';
      btn.innerHTML = option.description
        ? `${option.label}<span class="battle-small">${option.description}</span>`
        : option.label;
      btn.addEventListener('click', () => option.action());
      grid.appendChild(btn);
      if (index === 0) window.setTimeout(() => btn.focus(), 0);
    });
    el.menu.appendChild(grid);
    state.locked = false;
  }

  function openRoot() {
    state.menu = 'root';
    renderMenu([
      button('Fight', openFight),
      button('Item', openItems),
      button('Creatures', () => openCreatures(false)),
      button('Flee', fleeBattle),
    ]);
  }

  function openFight() {
    const attacks = data.attacks.slice(0, 4).map(attack => button(
      attack.name,
      () => playerTurn(attack),
      `${attack.power} power`
    ));
    attacks.push({...button('Quit', openRoot), quit: true});
    renderMenu(attacks);
  }

  function openItems() {
    const usable = data.items.filter(item => item.quantity > 0);
    if (!usable.length) {
      renderMenu([
        button('No battle items', () => {}, 'Nothing usable right now.'),
        {...button('Quit', openRoot), quit: true},
      ]);
      return;
    }
    const options = usable.map(item => button(
      item.name,
      () => useItem(item),
      `${item.quantity} left, heals ${item.heal} HP`
    ));
    options.push({...button('Quit', openRoot), quit: true});
    renderMenu(options);
  }

  async function useItem(item) {
    const target = currentPlayer();
    if (target.hp >= target.maxHp) {
      log(`${target.name} is already at full HP.`);
      openItems();
      return;
    }
    item.quantity -= 1;
    target.hp = Math.min(target.maxHp, target.hp + item.heal);
    log(`You used ${item.name} on ${target.name}.`);
    await updateHp(target, 'player', true);
    const npc = currentNpc();
    if (npc.hp > 0) {
      await performAttack(npc, target, randomNpcAttack(), 'npc');
    }
    if (alivePlayer() && aliveNpc() && currentPlayer().hp > 0) openRoot();
  }

  function openCreatures(forceSwitch) {
    const options = state.playerTeam.map((creature, index) => {
      const current = index === state.playerIndex;
      const description = `${creature.hp}/${creature.maxHp} HP | Atk ${creature.attack} Def ${creature.defense} Spd ${creature.speed}`;
      return button(
        `${creature.name}${current ? ' (active)' : ''}`,
        () => switchCreature(index, forceSwitch),
        creature.hp <= 0 ? 'Unable to battle' : description
      );
    });
    options.push({...button('Quit', forceSwitch ? fleeBattle : openRoot), quit: true});
    renderMenu(options);
  }

  async function switchCreature(index, forceSwitch) {
    const creature = state.playerTeam[index];
    if (!creature || creature.hp <= 0) {
      log('That creature cannot battle.');
      openCreatures(forceSwitch);
      return;
    }
    if (index === state.playerIndex && !forceSwitch) {
      log(`${creature.name} is already on the field.`);
      openRoot();
      return;
    }
    state.playerIndex = index;
    renderCombatants();
    el.player.classList.remove('is-summoned');
    void el.player.offsetWidth;
    el.player.classList.add('is-summoned');
    log(`You sent out ${creature.name}.`);
    await wait(450);
    openRoot();
  }

  function openPrompt(message, options) {
    log(message);
    renderMenu(options);
  }

  function fleeBattle() {
    state.locked = true;
    log('You fled the encounter.');
    window.setTimeout(() => window.location.href = data.returnUrl, 850);
  }

  async function winBattle() {
    state.locked = true;
    log(`${data.trainer.className} ${data.trainer.name}: ${data.trainer.defeatLine}`);
    log(`You received ${data.trainer.defeatCurrency} Dosh.`);
    if (!state.awarded) {
      state.awarded = true;
      const body = new URLSearchParams({
        battle_action: 'award_victory',
        trainer_id: String(data.trainer.id),
        battle_token: data.token,
      });
      try {
        const response = await fetch(window.location.href, {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body,
        });
        const result = await response.json();
        if (result.ok && typeof window.updateCurrencyDisplay === 'function') {
          window.updateCurrencyDisplay({cash: result.cash});
        }
      } catch (error) {
        log('Reward sync failed. Refreshing may be needed.');
      }
    }
    renderMenu([button('Return to Games', () => window.location.href = data.returnUrl)]);
    window.setTimeout(() => window.location.href = data.returnUrl, 2600);
  }

  function boot() {
    el.introTitle.textContent = `${data.trainer.className} ${data.trainer.name}`;
    el.introLine.textContent = data.trainer.encounterLine;
    renderCombatants();
    el.introStart.addEventListener('click', async () => {
      el.intro.classList.add('is-hidden');
      await wait(320);
      log(`${data.trainer.className} ${data.trainer.name} challenges you.`);
      el.npc.classList.add('is-summoned');
      await wait(320);
      log(`${data.trainer.name} sent out ${currentNpc().name}.`);
      el.player.classList.add('is-summoned');
      await wait(420);
      log(`Go, ${currentPlayer().name}.`);
      openRoot();
    });
  }

  boot();
})();
</script>
<?php endif; ?>
