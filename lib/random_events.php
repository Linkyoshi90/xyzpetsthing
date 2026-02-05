<?php
require_once __DIR__.'/../db.php';
require_once __DIR__.'/pets.php';
require_once __DIR__.'/breeding.php';

const RANDOM_EVENT_FILE = __DIR__ . '/../data/random_events.json';
const RANDOM_EVENT_CHANCE = 0.005; // x% per page load

function random_event_catalog(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    if (!is_file(RANDOM_EVENT_FILE)) {
        $cache = [];
        return $cache;
    }
    $json = file_get_contents(RANDOM_EVENT_FILE);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        $cache = [];
        return $cache;
    }
    $cache = array_values(array_filter($data, static function ($row) {
        return isset($row['weight']) && (int)$row['weight'] > 0;
    }));
    return $cache;
}

function maybe_trigger_random_event(array $user): ?array
{
    static $rolled = false;
    if ($rolled || !$user || (int)($user['id'] ?? 0) === 0) {
        return null;
    }
    $rolled = true;
    $events = random_event_catalog();
    if (!$events) {
        return null;
    }
    if (mt_rand(1, 100) > (int)(RANDOM_EVENT_CHANCE * 100)) {
        return null;
    }
    $event = pick_weighted_event($events);
    if (!$event) {
        return null;
    }
    return apply_random_event_effects($event, $user);
}

function pick_weighted_event(array $events): ?array
{
    $total = 0;
    foreach ($events as $event) {
        $total += (int)($event['weight'] ?? 0);
    }
    if ($total <= 0) {
        return null;
    }
    $roll = mt_rand(1, $total);
    $accum = 0;
    foreach ($events as $event) {
        $accum += (int)($event['weight'] ?? 0);
        if ($roll <= $accum) {
            return $event;
        }
    }
    return $events[array_key_first($events)] ?? null;
}

function apply_random_event_effects(array $event, array $user): ?array
{
    $effects = $event['effects'] ?? [];
    if (!$effects || !is_array($effects)) {
        return null;
    }
    $result = [
        'title' => $event['title'] ?? 'Random Encounter',
        'message' => $event['message'] ?? '',
        'details' => [],
        'actions' => [],
    ];
    $balances = [];
    foreach ($effects as $effect) {
        if (!is_array($effect) || empty($effect['type'])) {
            continue;
        }
        switch ($effect['type']) {
            case 'currency':
                $detail = handle_event_currency_effect($user['id'], $effect, $balances);
                break;
            case 'item':
                $detail = handle_event_item_effect($user['id'], $effect);
                break;
            case 'pet_damage':
                $detail = handle_event_pet_damage_effect($user['id'], $effect);
                break;
            case 'pet_stat':
                $detail = handle_event_pet_stat_effect($user['id'], $effect);
                break;
            case 'sickness':
                $detail = handle_event_sickness_effect($user['id'], $effect);
                break;
            case 'breeding_deposit':
                $detail = handle_event_breeding_deposit_effect($user['id'], $effect);
                break;
            case 'breeding_tick':
                $detail = handle_event_breeding_tick_effect($user['id'], $effect);
                break;
            case 'unlock_species':
                $detail = handle_event_unlock_species_effect($user['id'], $effect);
                break;
            default:
                $detail = null;
                break;
        }
        if ($detail) {
            if (is_array($detail) && (isset($detail['details']) || isset($detail['actions']))) {
                if (!empty($detail['details'])) {
                    $result['details'] = array_merge($result['details'], $detail['details']);
                }
                if (!empty($detail['actions'])) {
                    $result['actions'] = array_merge($result['actions'], $detail['actions']);
                }
            } elseif (is_array($detail)) {
                $result['details'] = array_merge($result['details'], $detail);
            } else {
                $result['details'][] = $detail;
            }
        }
    }
    if ($balances) {
        $result['balances'] = $balances;
    }
    if (!$result['details']) {
        $result['details'][] = 'Nothing much happens this time.';
    }
    return $result;
}

function handle_event_currency_effect(int $user_id, array $effect, array &$balances)
{
    $currency = strtoupper($effect['currency'] ?? '');
    $amount = (int)($effect['amount'] ?? 0);
    if ($currency === '' || $amount === 0) {
        return null;
    }
    $currencyId = null;
    $label = '';
    if ($currency === 'DOSH') {
        $currencyId = 1;
        $label = 'cash';
    } elseif ($currency === 'GEM') {
        $currencyId = 2;
        $label = 'gems';
    }
    if ($currencyId === null) {
        return null;
    }
    q(
        "INSERT INTO user_balances (user_id, currency_id, balance) VALUES (?,?,?)"
        . " ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)",
        [$user_id, $currencyId, $amount]
    );
    $balance = (int)q(
        "SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = ?",
        [$user_id, $currencyId]
    )->fetchColumn();
    if (isset($_SESSION['user'])) {
        if ($currencyId === 1) {
            $_SESSION['user']['cash'] = $balance;
        } elseif ($currencyId === 2) {
            $_SESSION['user']['gems'] = $balance;
        }
    }
    $balances[$label] = $balance;
    $verb = $amount >= 0 ? 'gain' : 'lose';
    $absAmount = abs($amount);
    $name = $currency === 'DOSH' ? APP_CURRENCY_LONG_NAME : 'gems';
    return sprintf('You %s %d %s.', $verb, $absAmount, $name);
}

function handle_event_item_effect(int $user_id, array $effect)
{
    $itemId = (int)($effect['item_id'] ?? 0);
    $quantity = (int)($effect['quantity'] ?? 0);
    if ($itemId <= 0 || $quantity === 0) {
        return null;
    }
    $item = q(
        "SELECT item_name FROM items WHERE item_id = ?",
        [$itemId]
    )->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        return null;
    }
    q(
        "INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?,?,?)"
        . " ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)",
        [$user_id, $itemId, $quantity]
    );
    $absQty = abs($quantity);
    if ($quantity > 0) {
        return sprintf('You receive %dx %s.', $absQty, $item['item_name']);
    }
    return sprintf('%dx %s disappear from your pack.', $absQty, $item['item_name']);
}

function handle_event_pet_damage_effect(int $user_id, array $effect)
{
    $amount = max(0, (int)($effect['amount'] ?? 0));
    if ($amount === 0) {
        return null;
    }
    $scope = $effect['scope'] ?? 'single';
    $applySickness = !empty($effect['apply_sickness']);
    $pets = get_user_pets($user_id);
    if (!$pets) {
        return 'You have no pets with you, so the danger passes harmlessly.';
    }
    $targets = [];
    if ($scope === 'party') {
        $targets = $pets;
    } else {
        $targets[] = $pets[array_rand($pets)];
    }
    $details = [];
    foreach ($targets as $pet) {
        $petId = (int)$pet['pet_instance_id'];
        q(
            "UPDATE pet_instances"
            . " SET hp_current = GREATEST(0, hp_current - ?)"
            . ($applySickness ? ", sickness = 1" : '')
            . " WHERE pet_instance_id = ? AND owner_user_id = ?",
            [$amount, $petId, $user_id]
        );
        $after = q(
            "SELECT hp_current, COALESCE(hp_max, hp_current) AS hp_max, sickness"
            . " FROM pet_instances WHERE pet_instance_id = ?",
            [$petId]
        )->fetch(PDO::FETCH_ASSOC);
        $name = $pet['nickname'] ?: $pet['species_name'];
        $details[] = sprintf(
            '%s takes %d damage (%d/%d HP%s).',
            $name,
            $amount,
            (int)$after['hp_current'],
            (int)$after['hp_max'],
            !empty($after['sickness']) ? ', now feeling under the weather' : ''
        );
    }
    return $details;
}

function handle_event_pet_stat_effect(int $user_id, array $effect)
{
    $stat = $effect['stat'] ?? '';
    $delta = (int)($effect['delta'] ?? 0);
    if ($delta === 0) {
        return null;
    }
    $allowed = [
        'atk' => 'atk',
        'def' => 'def',
        'initiative' => 'initiative',
        'intelligence' => 'intelligence',
        'happiness' => 'happiness',
    ];
    if (!isset($allowed[$stat])) {
        return null;
    }
    $pets = get_user_pets($user_id);
    if (!$pets) {
        return 'No pets are present to be affected.';
    }
    $scope = $effect['scope'] ?? 'single';
    $targets = [];
    if ($scope === 'party') {
        $targets = $pets;
    } else {
        $targets[] = $pets[array_rand($pets)];
    }
    $details = [];
    foreach ($targets as $pet) {
        $petId = (int)$pet['pet_instance_id'];
        $column = $allowed[$stat];
        q(
            "UPDATE pet_instances"
            . " SET {$column} = GREATEST(0, COALESCE({$column}, 0) + ?)"
            . " WHERE pet_instance_id = ? AND owner_user_id = ?",
            [$delta, $petId, $user_id]
        );
        $after = q(
            "SELECT {$column} AS value FROM pet_instances WHERE pet_instance_id = ?",
            [$petId]
        )->fetch(PDO::FETCH_ASSOC);
        $name = $pet['nickname'] ?: $pet['species_name'];
        $word = $delta > 0 ? 'increases' : 'decreases';
        $details[] = sprintf(
            "%s's %s %s to %d.",
            $name,
            ucfirst($column),
            $word,
            (int)$after['value']
        );
    }
    return $details;
}

function handle_event_sickness_effect(int $user_id, array $effect)
{
    $value = (int)($effect['value'] ?? 0);
    $scope = $effect['scope'] ?? 'single';
    $pets = get_user_pets($user_id);
    if (!$pets) {
        return 'The air feels odd, but you are alone with no pets to be affected.';
    }
    $targets = [];
    if ($scope === 'party') {
        $targets = $pets;
    } else {
        $targets[] = $pets[array_rand($pets)];
    }
    $value = max(0, min(255, $value));
    $details = [];
    foreach ($targets as $pet) {
        $petId = (int)$pet['pet_instance_id'];
        q(
            "UPDATE pet_instances SET sickness = ? WHERE pet_instance_id = ? AND owner_user_id = ?",
            [$value, $petId, $user_id]
        );
        $name = $pet['nickname'] ?: $pet['species_name'];
        if ($value > 0) {
            $details[] = sprintf('%s looks a little sickly.', $name);
        } else {
            $details[] = sprintf('%s shakes off any lingering sickness.', $name);
        }
    }
    return $details;
}

function handle_event_breeding_deposit_effect(int $user_id, array $effect): ?array
{
    $rows = breeding_active_pairs($user_id);
    if (!$rows) {
        return null;
    }
    $details = [];
    foreach ($rows as $row) {
        if ((int)($row['egg_count'] ?? 0) > 0) {
            continue;
        }
        $speciesOptions = array_values(array_filter([
            (int)($row['egg_creature_id'] ?? 0),
            (int)($row['mother_species_id'] ?? 0),
            (int)($row['father_species_id'] ?? 0),
        ], static fn($val) => $val > 0));
        if (!$speciesOptions) {
            continue;
        }
        $chosenSpecies = $speciesOptions[array_rand($speciesOptions)];
        $time = mt_rand(3, 5);
        q(
            "UPDATE breeding SET egg_count = egg_count + 1, egg_creature_id = ?, time_to_hatch = ? WHERE breed_instance_id = ?",
            [$chosenSpecies, $time, $row['breed_instance_id']]
        );
        $details[] = sprintf(
            '%s and %s laid an egg! It will hatch in %d days.',
            $row['mother_name'] ?: ($row['mother_species_name'] ?? 'Mother'),
            $row['father_name'] ?: ($row['father_species_name'] ?? 'Daycare Stallion'),
            $time
        );
    }
    if (!$details) {
        return null;
    }
    return [
        'details' => $details,
        'actions' => [
            ['label' => 'Visit daycare', 'url' => '?pg=breeding'],
        ],
    ];
}

function handle_event_breeding_tick_effect(int $user_id, array $effect): ?array
{
    $chance = (int)($effect['chance'] ?? 10);
    $rows = breeding_active_pairs($user_id);
    if (!$rows) {
        return null;
    }
    $details = [];
    foreach ($rows as $row) {
        if ((int)($row['egg_count'] ?? 0) <= 0 || (int)($row['time_to_hatch'] ?? 0) <= 0) {
            continue;
        }
        if (mt_rand(1, 10) > $chance) {
            continue;
        }
        $newTime = max(0, (int)$row['time_to_hatch'] - 1);
        q(
            "UPDATE breeding SET time_to_hatch = ? WHERE breed_instance_id = ?",
            [$newTime, $row['breed_instance_id']]
        );
        if ($newTime === 0) {
            $details[] = sprintf(
                'An egg from %s and %s is ready to hatch!',
                $row['mother_name'] ?: ($row['mother_species_name'] ?? 'Mother'),
                $row['father_name'] ?: ($row['father_species_name'] ?? 'Daycare Stallion')
            );
            $hatched = breeding_hatch_ready_eggs($user_id);
            if ($hatched) {
                $details = array_merge($details, $hatched);
            }
        } else {
            $details[] = sprintf(
                'An egg in daycare now has %d days until it hatches.',
                $newTime
            );
        }
    }
    if (!$details) {
        return null;
    }
    return [
        'details' => $details,
        'actions' => [
            ['label' => 'Visit daycare', 'url' => '?pg=breeding'],
            ['label' => 'View pets', 'url' => '?pg=pet'],
        ],
    ];
}


function handle_event_unlock_species_effect(int $user_id, array $effect)
{
    $allowedSpecies = [];
    $file = __DIR__ . '/../data-readonly/available_creatures.txt';
    if (is_file($file)) {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            $allowedSpecies[] = $line;
        }
    }

    if (!$allowedSpecies) {
        return null;
    }

    $placeholders = implode(',', array_fill(0, count($allowedSpecies), '?'));
    $regionFirstPlaceholders = implode(',', array_fill(0, count($allowedSpecies), '?'));
    $params = array_merge([$user_id], $allowedSpecies, $allowedSpecies);
    $species = q(
        "SELECT ps.species_id, ps.species_name, r.region_name "
        . "FROM pet_species ps "
        . "LEFT JOIN regions r ON r.region_id = ps.region_id "
        . "LEFT JOIN player_unlocked_species pus "
        . "  ON pus.player_id = ? AND pus.unlocked_species_id = ps.species_id "
        . "LEFT JOIN ( "
        . "  SELECT MIN(ps2.species_id) AS species_id "
        . "  FROM pet_species ps2 "
        . "  WHERE ps2.species_name IN ($regionFirstPlaceholders) "
        . "  GROUP BY ps2.region_id "
        . ") region_defaults ON region_defaults.species_id = ps.species_id "
        . "WHERE ps.species_name IN ($placeholders) "
        . "  AND pus.entryId IS NULL "
        . "  AND region_defaults.species_id IS NULL",
        $params
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!$species) {
        return 'You feel like you have already met every creature the world has to offer.';
    }

    $newUnlock = $species[array_rand($species)];
    q(
        "INSERT INTO player_unlocked_species (player_id, unlocked_species_id) VALUES (?,?) "
        . "ON DUPLICATE KEY UPDATE unlocked_species_id = unlocked_species_id",
        [$user_id, (int)$newUnlock['species_id']]
    );

    $regionName = $newUnlock['region_name'] ?: 'an unknown region';
    return sprintf(
        'You discovered %s from %s! You can now pick this creature when creating a pet.',
        $newUnlock['species_name'],
        $regionName
    );
}
