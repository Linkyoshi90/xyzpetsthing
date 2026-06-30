<?php

function item_effect_allowed_pet_stats(): array
{
    return [
        'hp_current' => ['column' => 'hp_current', 'label' => 'health', 'cap_column' => 'hp_max'],
        'hp_max' => ['column' => 'hp_max', 'label' => 'max health'],
        'atk' => ['column' => 'atk', 'label' => 'attack'],
        'def' => ['column' => 'def', 'label' => 'defense'],
        'initiative' => ['column' => 'initiative', 'label' => 'initiative'],
        'intelligence' => ['column' => 'intelligence', 'label' => 'intelligence'],
        'happiness' => ['column' => 'happiness', 'label' => 'happiness', 'cap_value' => 100],
        'hunger' => ['column' => 'hunger', 'label' => 'hunger', 'cap_value' => 100],
    ];
}

function item_effect_table_exists(PDO $pdo): bool
{
    static $exists = null;
    if ($exists !== null) {
        return $exists;
    }

    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'item_effects'");
        $exists = (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        app_add_error_from_exception($e, 'Could not inspect item_effects table:');
        $exists = false;
    }

    return $exists;
}

function item_effect_stat_label(string $target_stat): string
{
    $allowed = item_effect_allowed_pet_stats();
    return $allowed[$target_stat]['label'] ?? str_replace('_', ' ', $target_stat);
}

function item_effect_format_effect(array $effect): string
{
    $amount = max(0, (int)($effect['amount'] ?? 0));
    $label = item_effect_stat_label((string)($effect['target_stat'] ?? ''));
    $type = strtolower((string)($effect['effect_type'] ?? 'increase'));

    if ($type === 'cure' && (string)($effect['target_stat'] ?? '') === 'sickness') {
        $sickness_name = trim((string)($effect['sickness_name'] ?? ''));
        return $sickness_name !== '' ? 'Cures ' . $sickness_name : 'Cures sickness';
    }

    if ($type === 'increase') {
        return '+' . $amount . ' ' . $label;
    }

    return ucfirst($type) . ' ' . $amount . ' ' . $label;
}

function item_effect_format_effects(array $effects): string
{
    $parts = [];
    foreach ($effects as $effect) {
        if (!is_array($effect)) {
            continue;
        }
        $parts[] = item_effect_format_effect($effect);
    }

    return implode(', ', $parts);
}

function item_effect_inventory_for_user(PDO $pdo, int $user_id): array
{
    if (!item_effect_table_exists($pdo)) {
        return [];
    }

    $stmt = $pdo->prepare(
        "SELECT ui.item_id,
                ui.quantity,
                i.item_name,
                COALESCE(i.replenish, 0) AS replenish,
                ie.item_effect_id,
                ie.effect_type,
                ie.target_stat,
                ie.amount,
                s.sick_name AS sickness_name
           FROM user_inventory ui
           JOIN items i ON i.item_id = ui.item_id
           JOIN item_effects ie ON ie.item_id = i.item_id
           LEFT JOIN sickness s
             ON ie.effect_type = 'cure'
            AND ie.target_stat = 'sickness'
            AND s.sick_id = ie.amount
          WHERE ui.user_id = ?
            AND ui.quantity > 0
          ORDER BY i.item_name, ie.item_effect_id"
    );
    $stmt->execute([$user_id]);

    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $item_id = (int)$row['item_id'];
        if (!isset($items[$item_id])) {
            $items[$item_id] = [
                'item_id' => $item_id,
                'item_name' => (string)$row['item_name'],
                'quantity' => (int)$row['quantity'],
                'replenish' => (int)$row['replenish'],
                'effects' => [],
            ];
        }

        $items[$item_id]['effects'][] = [
            'item_effect_id' => (int)$row['item_effect_id'],
            'effect_type' => (string)$row['effect_type'],
            'target_stat' => (string)$row['target_stat'],
            'amount' => (int)$row['amount'],
            'sickness_name' => (string)($row['sickness_name'] ?? ''),
        ];
    }

    return array_values($items);
}

function item_effect_fetch_inventory_item(PDO $pdo, int $user_id, int $item_id): ?array
{
    if (!item_effect_table_exists($pdo)) {
        return null;
    }

    $stmt = $pdo->prepare(
        "SELECT ui.item_id,
                ui.quantity,
                i.item_name,
                COALESCE(i.replenish, 0) AS replenish
           FROM user_inventory ui
           JOIN items i ON i.item_id = ui.item_id
          WHERE ui.user_id = ?
            AND ui.item_id = ?
            AND ui.quantity > 0
          LIMIT 1
          FOR UPDATE"
    );
    $stmt->execute([$user_id, $item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        return null;
    }

    $effects = $pdo->prepare(
        "SELECT ie.item_effect_id,
                ie.effect_type,
                ie.target_stat,
                ie.amount,
                s.sick_name AS sickness_name
           FROM item_effects ie
           LEFT JOIN sickness s
             ON ie.effect_type = 'cure'
            AND ie.target_stat = 'sickness'
            AND s.sick_id = ie.amount
          WHERE ie.item_id = ?
          ORDER BY ie.item_effect_id"
    );
    $effects->execute([$item_id]);
    $item['effects'] = $effects->fetchAll(PDO::FETCH_ASSOC);

    return $item;
}

function item_effect_consume_inventory_item(PDO $pdo, int $user_id, int $item_id, int $current_quantity): int
{
    $next_quantity = max(0, $current_quantity - 1);
    if ($next_quantity > 0) {
        $stmt = $pdo->prepare(
            "UPDATE user_inventory SET quantity = ? WHERE user_id = ? AND item_id = ?"
        );
        $stmt->execute([$next_quantity, $user_id, $item_id]);
    } else {
        $stmt = $pdo->prepare(
            "DELETE FROM user_inventory WHERE user_id = ? AND item_id = ?"
        );
        $stmt->execute([$user_id, $item_id]);
    }

    return $next_quantity;
}

function item_effect_apply_to_pet(PDO $pdo, int $user_id, int $pet_id, int $item_id): array
{
    if ($user_id <= 0 || $pet_id <= 0 || $item_id <= 0) {
        return ['ok' => false, 'message' => 'That item could not be used.'];
    }

    $allowed = item_effect_allowed_pet_stats();

    try {
        $pdo->beginTransaction();

        $pet_stmt = $pdo->prepare(
            "SELECT pi.pet_instance_id,
                    pi.owner_user_id,
                    pi.nickname,
                    pi.hunger,
                    pi.happiness,
                    pi.intelligence,
                    pi.hp_current,
                    pi.hp_max,
                    pi.atk,
                    pi.def,
                    pi.initiative,
                    pi.sickness,
                    s.sick_name AS sickness_name,
                    ps.species_name
               FROM pet_instances pi
               JOIN pet_species ps ON ps.species_id = pi.species_id
               LEFT JOIN sickness s ON s.sick_id = pi.sickness
              WHERE pi.pet_instance_id = ?
                AND pi.owner_user_id = ?
              FOR UPDATE"
        );
        $pet_stmt->execute([$pet_id, $user_id]);
        $pet = $pet_stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pet) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'That pet is not available.'];
        }

        $item = item_effect_fetch_inventory_item($pdo, $user_id, $item_id);
        if (!$item || (int)($item['quantity'] ?? 0) <= 0) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'That item is no longer available.', 'pet' => $pet];
        }

        $applied = [];
        $next_values = [];
        foreach (($item['effects'] ?? []) as $effect) {
            $effect_type = strtolower((string)($effect['effect_type'] ?? ''));
            $target_stat = (string)($effect['target_stat'] ?? '');
            $amount = max(0, (int)($effect['amount'] ?? 0));

            if ($effect_type === 'cure' && $target_stat === 'sickness' && $amount > 0) {
                $before = max(0, (int)($pet['sickness'] ?? 0));
                if ($before !== $amount) {
                    continue;
                }

                $pet['sickness'] = 0;
                $next_values['sickness'] = 0;
                $applied[] = [
                    'effect_type' => 'cure',
                    'target_stat' => 'sickness',
                    'amount' => $amount,
                    'sickness_name' => (string)($pet['sickness_name'] ?? $effect['sickness_name'] ?? ''),
                    'before' => $before,
                    'after' => 0,
                ];
                continue;
            }

            if ($effect_type !== 'increase' || $amount <= 0 || !isset($allowed[$target_stat])) {
                continue;
            }

            $meta = $allowed[$target_stat];
            $column = $meta['column'];
            $before = max(0, (int)($pet[$column] ?? 0));
            $after = $before + $amount;

            if (isset($meta['cap_column'])) {
                $cap = max(0, (int)($pet[$meta['cap_column']] ?? 0));
                $after = min($cap, $after);
            } elseif (isset($meta['cap_value'])) {
                $after = min((int)$meta['cap_value'], $after);
            }

            if ($after === $before) {
                continue;
            }

            $pet[$column] = $after;
            $next_values[$column] = $after;
            $applied[] = [
                'target_stat' => $target_stat,
                'label' => $meta['label'],
                'amount' => $after - $before,
                'before' => $before,
                'after' => $after,
            ];
        }

        if (!$applied) {
            $pdo->rollBack();
            $pet_name = ($pet['nickname'] ?? '') !== '' ? (string)$pet['nickname'] : (string)$pet['species_name'];
            return [
                'ok' => false,
                'message' => $pet_name . ' cannot benefit from ' . $item['item_name'] . ' right now.',
                'pet' => $pet,
            ];
        }

        $set_parts = [];
        $params = [];
        foreach ($next_values as $column => $value) {
            $set_parts[] = "{$column} = ?";
            $params[] = $value;
        }
        $params[] = $pet_id;
        $params[] = $user_id;

        $update = $pdo->prepare(
            "UPDATE pet_instances
                SET " . implode(', ', $set_parts) . "
              WHERE pet_instance_id = ?
                AND owner_user_id = ?"
        );
        $update->execute($params);

        $next_quantity = item_effect_consume_inventory_item($pdo, $user_id, $item_id, (int)$item['quantity']);

        $pdo->commit();

        $pet_name = ($pet['nickname'] ?? '') !== '' ? (string)$pet['nickname'] : (string)$pet['species_name'];

        $message = $pet_name . ' gained ' . item_effect_format_effects($applied) . '.';
        if (count($applied) === 1
            && ($applied[0]['effect_type'] ?? '') === 'cure'
            && ($applied[0]['target_stat'] ?? '') === 'sickness') {
            $cured_name = trim((string)($applied[0]['sickness_name'] ?? ''));
            $message = $cured_name !== ''
                ? $pet_name . ' was cured of ' . $cured_name . '.'
                : $pet_name . ' was cured.';
        }

        return [
            'ok' => true,
            'pet' => $pet,
            'item' => ['id' => $item_id, 'quantity' => $next_quantity],
            'effects' => ['applied' => $applied],
            'message' => $message,
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        app_add_error_from_exception($e, 'Item effect application failed:');
        return ['ok' => false, 'message' => 'That item could not be used.'];
    }
}
