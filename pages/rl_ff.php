<?php
require_login();
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/temp_user.php';

$uid = (int)current_user()['id'];
$isTemp = is_temp_user();
$today = date('Y-m-d');
$appearanceChance = 68; // percent
$messages = [];
$rewardVisualKey = null;

$rewardImages = [
    'heal_full_party' => 'Fairy mending every creature at once',
    'heal_full_single' => 'Fairy placing a hand on one creature to fully heal it',
    'heal_plus5_single' => 'Fairy passing a +5 HP glow to one creature',
    'heal_plus5_party' => 'Fairy shimmering all creatures for +5 HP',
    'heal_plus1_party' => 'Fairy giving a faint +1 HP blessing to the group',
    'heal_plus1_single' => 'Fairy whispering a +1 HP tip to one creature',
    'feed_party_full' => 'Fairy overflowing every food bowl',
    'feed_single_full' => 'Fairy filling one creature’s food bowl',
    'give_magic_potion' => 'Fairy presenting a bottled magical potion',
    'give_golden_shovel' => 'Fairy offering a gleaming golden shovel',
    'nothing' => 'Fairy fading without leaving a gift',
];

function fairy_fountain_visit_row(int $uid, string $today, bool $isTemp): ?array
{
    if ($isTemp) {
        $store = temp_user_data();
        return $store['fairy_fountain'][$today] ?? null;
    }
    return q(
        "SELECT deposited_amount, reward_key, reward_note, created_at"
        . " FROM fairy_fountain_visits WHERE user_id = ? AND visit_date = ?",
        [$uid, $today]
    )->fetch(PDO::FETCH_ASSOC) ?: null;
}

function fairy_fountain_record_visit(int $uid, string $today, float $amount, ?string $rewardKey, ?string $note, bool $isTemp): void
{
    if ($isTemp) {
        $store = &temp_user_data();
        if (!isset($store['fairy_fountain'])) {
            $store['fairy_fountain'] = [];
        }
        $store['fairy_fountain'][$today] = [
            'deposited_amount' => $amount,
            'reward_key' => $rewardKey,
            'reward_note' => $note,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        return;
    }
    q(
        "INSERT INTO fairy_fountain_visits (user_id, visit_date, deposited_amount, reward_key, reward_note)"
        . " VALUES (?,?,?,?,?)"
        . " ON DUPLICATE KEY UPDATE deposited_amount = VALUES(deposited_amount), reward_key = VALUES(reward_key), reward_note = VALUES(reward_note)",
        [$uid, $today, $amount, $rewardKey, $note]
    );
}

function fairy_fountain_adjust_balance(int $uid, float $amount): bool
{
    if ($uid === 0) {
        $cash = temp_user_balance('cash');
        if ($cash < $amount) {
            return false;
        }
        temp_user_adjust_balance('cash', -$amount);
        return true;
    }

    $pdo = db();
    if (!$pdo) {
        return false;
    }
    try {
        $pdo->beginTransaction();
        $balanceStmt = $pdo->prepare('SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = 1 FOR UPDATE');
        $balanceStmt->execute([$uid]);
        $balance = $balanceStmt->fetchColumn();
        if ($balance === false || (float)$balance < $amount) {
            $pdo->rollBack();
            return false;
        }
        $update = $pdo->prepare('UPDATE user_balances SET balance = balance - ? WHERE user_id = ? AND currency_id = 1');
        $update->execute([$amount, $uid]);
        $ledger = $pdo->prepare('INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata) VALUES (?,?,?,?,?)');
        $ledger->execute([$uid, 1, -$amount, 'fairy_fountain_deposit', json_encode(['deposit' => $amount])]);
        $pdo->commit();
        $_SESSION['user']['cash'] = (int)max(0, round((float)$balance - $amount));
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        app_add_error_from_exception($e, 'Could not process deposit:');
        return false;
    }
}

function fairy_fountain_add_item_if_exists(int $uid, string $name): string
{
    $itemId = q("SELECT item_id FROM items WHERE item_name = ? LIMIT 1", [$name])->fetchColumn();
    if (!$itemId) {
        return "$name (will be delivered once the item exists in the database).";
    }
    if ($uid === 0) {
        temp_user_add_inventory_item((int)$itemId, 1);
        return "You receive 1x $name.";
    }
    q(
        "INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, 1)"
        . " ON DUPLICATE KEY UPDATE quantity = quantity + 1",
        [$uid, (int)$itemId]
    );
    return "You receive 1x $name.";
}

function fairy_fountain_fill_food(array $pets, int $uid, bool $all, bool $isTemp): array
{
    $lines = [];
    if ($isTemp) {
        $store = &temp_user_data();
        if ($all) {
            foreach ($store['pets'] as &$pet) {
                $pet['hunger'] = 100;
                $name = $pet['nickname'] ?: $pet['species_name'];
                $lines[] = "$name enjoys a full meal.";
            }
            unset($pet);
        } elseif ($store['pets']) {
            $idx = array_rand($store['pets']);
            $store['pets'][$idx]['hunger'] = 100;
            $pet = $store['pets'][$idx];
            $lines[] = ($pet['nickname'] ?: $pet['species_name'])." enjoys a full meal.";
        }
        return $lines ?: ['No creatures are with you to feed.'];
    }

    if ($all) {
        q("UPDATE pet_instances SET hunger = 100 WHERE owner_user_id = ?", [$uid]);
        foreach ($pets as $pet) {
            $name = $pet['nickname'] ?: $pet['species_name'];
            $lines[] = "$name is completely fed.";
        }
        return $lines ?: ['No creatures are with you to feed.'];
    }
    if ($pets) {
        $pet = $pets[array_rand($pets)];
        q("UPDATE pet_instances SET hunger = 100 WHERE pet_instance_id = ? AND owner_user_id = ?", [(int)$pet['pet_instance_id'], $uid]);
        $lines[] = ($pet['nickname'] ?: $pet['species_name'])." is completely fed.";
        return $lines;
    }
    return ['No creatures are with you to feed.'];
}

function fairy_fountain_heal(array $pets, int $uid, bool $all, int $amount, bool $setFull, bool $isTemp): array
{
    $lines = [];
    if ($isTemp) {
        $store = &temp_user_data();
        $targets = [];
        if ($all) {
            $targets = array_keys($store['pets']);
        } elseif ($store['pets']) {
            $targets[] = array_rand($store['pets']);
        }
        foreach ($targets as $idx) {
            $pet = &$store['pets'][$idx];
            $maxHp = $pet['hp_max'] ?? $pet['hp_current'];
            if ($setFull) {
                $pet['hp_current'] = $maxHp;
                $lines[] = ($pet['nickname'] ?: $pet['species_name'])." is fully healed.";
            } else {
                $pet['hp_current'] = min($maxHp, (int)$pet['hp_current'] + $amount);
                $lines[] = ($pet['nickname'] ?: $pet['species_name'])." recovers {$amount} HP.";
            }
            unset($pet);
        }
        return $lines ?: ['No creatures are with you to heal.'];
    }

    if ($all) {
        if ($setFull) {
            q("UPDATE pet_instances SET hp_current = COALESCE(hp_max, hp_current) WHERE owner_user_id = ?", [$uid]);
            foreach ($pets as $pet) {
                $lines[] = ($pet['nickname'] ?: $pet['species_name'])." is fully healed.";
            }
        } else {
            q(
                "UPDATE pet_instances"
                . " SET hp_current = LEAST(COALESCE(hp_max, hp_current), hp_current + ?)"
                . " WHERE owner_user_id = ?",
                [$amount, $uid]
            );
            foreach ($pets as $pet) {
                $lines[] = ($pet['nickname'] ?: $pet['species_name'])." recovers {$amount} HP.";
            }
        }
        return $lines ?: ['No creatures are with you to heal.'];
    }

    if ($pets) {
        $pet = $pets[array_rand($pets)];
        if ($setFull) {
            q("UPDATE pet_instances SET hp_current = COALESCE(hp_max, hp_current) WHERE pet_instance_id = ? AND owner_user_id = ?", [(int)$pet['pet_instance_id'], $uid]);
            $lines[] = ($pet['nickname'] ?: $pet['species_name'])." is fully healed.";
        } else {
            q(
                "UPDATE pet_instances"
                . " SET hp_current = LEAST(COALESCE(hp_max, hp_current), hp_current + ?)"
                . " WHERE pet_instance_id = ? AND owner_user_id = ?",
                [$amount, (int)$pet['pet_instance_id'], $uid]
            );
            $lines[] = ($pet['nickname'] ?: $pet['species_name'])." recovers {$amount} HP.";
        }
        return $lines;
    }
    return ['No creatures are with you to heal.'];
}

$todayVisit = fairy_fountain_visit_row($uid, $today, $isTemp);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($todayVisit) {
        $messages[] = 'You already visited today. The fountain sleeps until tomorrow.';
    } else {
        $amount = round((float)($_POST['deposit'] ?? 0), 2);
        if ($amount <= 0) {
            $messages[] = 'Offer at least 0.01 coins to wake the fountain.';
        } else {
            $paid = fairy_fountain_adjust_balance($uid, $amount);
            if (!$paid) {
                $messages[] = 'You do not have enough coins for that offering.';
            } else {
                $rolled = random_int(1, 100);
                $fairyAppears = $rolled <= $appearanceChance;
                $rewardKey = null;
                $rewardNote = null;
                $pets = get_user_pets($uid);

                if ($fairyAppears) {
                    $rewardPool = [
                        'heal_full_party',
                        'heal_full_single',
                        'heal_plus5_single',
                        'heal_plus5_party',
                        'heal_plus1_party',
                        'heal_plus1_single',
                        'feed_party_full',
                        'feed_single_full',
                        'give_magic_potion',
                        'give_golden_shovel',
                        'nothing',
                    ];
                    $rewardKey = $rewardPool[array_rand($rewardPool)];
                    $rewardVisualKey = $rewardKey;
                    switch ($rewardKey) {
                        case 'heal_full_party':
                            $messages = array_merge($messages, fairy_fountain_heal($pets, $uid, true, 0, true, $isTemp));
                            $rewardNote = 'All creatures were fully healed.';
                            break;
                        case 'heal_full_single':
                            $messages = array_merge($messages, fairy_fountain_heal($pets, $uid, false, 0, true, $isTemp));
                            $rewardNote = 'One creature was fully healed.';
                            break;
                        case 'heal_plus5_single':
                            $messages = array_merge($messages, fairy_fountain_heal($pets, $uid, false, 5, false, $isTemp));
                            $rewardNote = '+5 HP to one creature.';
                            break;
                        case 'heal_plus5_party':
                            $messages = array_merge($messages, fairy_fountain_heal($pets, $uid, true, 5, false, $isTemp));
                            $rewardNote = '+5 HP to every creature.';
                            break;
                        case 'heal_plus1_party':
                            $messages = array_merge($messages, fairy_fountain_heal($pets, $uid, true, 1, false, $isTemp));
                            $rewardNote = '+1 HP to every creature.';
                            break;
                        case 'heal_plus1_single':
                            $messages = array_merge($messages, fairy_fountain_heal($pets, $uid, false, 1, false, $isTemp));
                            $rewardNote = '+1 HP to one creature.';
                            break;
                        case 'feed_party_full':
                            $messages = array_merge($messages, fairy_fountain_fill_food($pets, $uid, true, $isTemp));
                            $rewardNote = 'Every creature is now fed.';
                            break;
                        case 'feed_single_full':
                            $messages = array_merge($messages, fairy_fountain_fill_food($pets, $uid, false, $isTemp));
                            $rewardNote = 'One creature is now fed.';
                            break;
                        case 'give_magic_potion':
                            $rewardNote = fairy_fountain_add_item_if_exists($uid, 'Magical Potion');
                            $messages[] = $rewardNote;
                            break;
                        case 'give_golden_shovel':
                            $rewardNote = fairy_fountain_add_item_if_exists($uid, 'Golden Shovel');
                            $messages[] = $rewardNote;
                            break;
                        default:
                            $rewardNote = 'The fairy only smiled this time.';
                            $messages[] = $rewardNote;
                            break;
                    }
                } else {
                    $rewardKey = 'none';
                    $rewardNote = 'The coins sink quietly. No fairy answered today.';
                    $messages[] = $rewardNote;
                }

                fairy_fountain_record_visit($uid, $today, $amount, $rewardKey, $rewardNote, $isTemp);
                $todayVisit = fairy_fountain_visit_row($uid, $today, $isTemp);
            }
        }
    }
}
?>

<h1>Rheinland - Fairy Fountain</h1>
<section class="card glass">
  <p class="muted">A moss-lit cavern hides a basin where the river thins to a silver thread. Locals swear a shy fairy trades coin for quiet blessings once per day.</p>
  <ul>
    <li>Offer any amount of coins once per local day.</li>
    <li><?= htmlspecialchars($appearanceChance) ?>% chance the fairy appears after your offering.</li>
    <li>Blessings can heal creatures, nudge their HP, refill food, grant a potion or shovel, or sometimes nothing.</li>
  </ul>
  <?php if ($todayVisit): ?>
    <div class="alert success">
      <strong>Today's visit logged.</strong>
      <div>Offered: <?= number_format((float)$todayVisit['deposited_amount'], 2) ?> Cash-Dosh.</div>
      <?php if (!empty($todayVisit['reward_note'])): ?>
        <div>Outcome: <?= htmlspecialchars($todayVisit['reward_note']) ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if ($messages): ?>
    <div class="alert">
      <?php foreach ($messages as $m): ?>
        <div><?= htmlspecialchars($m) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!$todayVisit): ?>
    <form method="post" class="form">
      <label for="deposit">Coins to offer</label>
      <input id="deposit" name="deposit" type="number" step="0.01" min="0.01" required>
      <button class="btn" type="submit">Offer coins</button>
    </form>
  <?php else: ?>
    <p class="muted">You have already made today's offering. The fountain will listen again tomorrow.</p>
  <?php endif; ?>
</section>

<section class="card glass">
  <h2>Fairy rewards</h2>
  <p class="muted">These placeholders mark where you can swap in illustrations later.</p>
  <div class="fairy-reward-grid">
    <?php foreach ($rewardImages as $key => $desc): ?>
      <div class="fairy-reward">
        <div class="fairy-image-placeholder" data-reward="<?= htmlspecialchars($key) ?>">
          🧚‍♀️ <strong><?= htmlspecialchars(str_replace('_', ' ', ucfirst($key))) ?></strong>
          <div class="muted"><?= htmlspecialchars($desc) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($rewardVisualKey && isset($rewardImages[$rewardVisualKey])): ?>
    <div class="alert success">
      <strong>Image callout for this blessing:</strong>
      <div class="fairy-image-placeholder active" data-reward="<?= htmlspecialchars($rewardVisualKey) ?>">
        🧚‍♀️ <?= htmlspecialchars($rewardImages[$rewardVisualKey]) ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<style>
.fairy-reward-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
}
.fairy-reward {
  padding: 0.75rem;
  border: 1px dashed rgba(255,255,255,0.2);
  border-radius: 8px;
}
.fairy-image-placeholder {
  padding: 0.75rem;
  background: rgba(255,255,255,0.04);
  border-radius: 6px;
}
.fairy-image-placeholder.active {
  border: 1px solid #ffb3ff;
  box-shadow: 0 0 12px rgba(255, 179, 255, 0.4);
}
</style>