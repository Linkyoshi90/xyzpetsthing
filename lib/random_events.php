<?php
require_once __DIR__.'/../db.php';
require_once __DIR__.'/pets.php';

const RANDOM_EVENT_FILE = __DIR__ . '/../data/random_events.json';
const RANDOM_EVENT_CHANCE = 0.12; // 12% per page load

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
    if ($rolled || !$user) {
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
            default:
                $detail = null;
                break;
        }
        if ($detail) {
            if (is_array($detail)) {
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
    $name = $currency === 'DOSH' ? 'coins' : 'gems';
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
        return sprintf('You receive %d× %s.', $absQty, $item['item_name']);
    }
    return sprintf('%d× %s disappear from your pack.', $absQty, $item['item_name']);
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