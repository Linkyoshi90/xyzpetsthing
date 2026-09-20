<?php
// Shared move logic for the battle minigame and the Yara Dojo.
//
// A creature's moveset comes from one of two places:
//   1. pet_instance_moves - the persistent, dojo-managed moveset (preferred)
//   2. the generated fallback - element-affinity rotation over the shared
//      move pool plus up to two species_moves signature moves
// The dojo "adopts" the generated set into pet_instance_moves on a
// creature's first visit, so both consumers must produce identical sets.

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

function battle_normalize_move_row(array $row, array $element_lookup): array {
    $element_id = (int)($row['element_id'] ?? 1);
    $category = (string)($row['category'] ?? 'physical');
    $effect_key = trim((string)($row['effect_key'] ?? ''));

    return [
        'id' => (int)($row['id'] ?? 0),
        'key' => (string)($row['move_key'] ?? ''),
        'name' => (string)($row['name'] ?? 'Strike'),
        'category' => $category,
        'power' => $category === 'status' ? 0 : max(1, (int)($row['power'] ?? 1)),
        'elementId' => $element_id,
        'elementName' => $element_lookup[$element_id] ?? ('Element ' . $element_id),
        'accuracy' => (float)($row['accuracy_percent'] ?? 100),
        'priority' => (int)($row['priority'] ?? 0),
        'contact' => !empty($row['contact']),
        'effect' => $effect_key !== '' ? $effect_key : null,
        'effectChance' => (float)($row['effect_chance_percent'] ?? 0),
    ];
}

function battle_load_attack_pool(array $element_lookup): array {
    $rows = q(
        "SELECT move_id AS id,
                move_key,
                move_name AS name,
                category,
                power,
                element_id,
                accuracy_percent,
                priority,
                contact,
                effect_key,
                effect_chance_percent
           FROM moves
          WHERE power IS NOT NULL
            AND category <> 'status'
          ORDER BY power, move_id"
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        $rows = q(
            "SELECT attack_id AS id,
                    LOWER(REPLACE(attack_name, ' ', '_')) AS move_key,
                    attack_name AS name,
                    'physical' AS category,
                    base_damage AS power,
                    element_id,
                    100.00 AS accuracy_percent,
                    0 AS priority,
                    1 AS contact
               FROM attacks
              ORDER BY base_damage, attack_id"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    if (!$rows) {
        $rows = [
            ['id' => 1, 'move_key' => 'tackle', 'name' => 'Tackle', 'category' => 'physical', 'power' => 40, 'element_id' => 1, 'accuracy_percent' => 100, 'priority' => 0, 'contact' => 1],
            ['id' => 2, 'move_key' => 'quick_attack', 'name' => 'Quick Attack', 'category' => 'physical', 'power' => 40, 'element_id' => 1, 'accuracy_percent' => 100, 'priority' => 1, 'contact' => 1],
            ['id' => 3, 'move_key' => 'ember', 'name' => 'Ember', 'category' => 'special', 'power' => 40, 'element_id' => 2, 'accuracy_percent' => 100, 'priority' => 0, 'contact' => 0],
            ['id' => 4, 'move_key' => 'water_gun', 'name' => 'Water Gun', 'category' => 'special', 'power' => 40, 'element_id' => 3, 'accuracy_percent' => 100, 'priority' => 0, 'contact' => 0],
        ];
    }

    return array_map(static fn(array $row): array => battle_normalize_move_row($row, $element_lookup), $rows);
}

function battle_load_species_move_map(array $element_lookup): array {
    try {
        $rows = q(
            "SELECT sm.species_id,
                    m.move_id AS id,
                    m.move_key,
                    m.move_name AS name,
                    m.category,
                    m.power,
                    m.element_id,
                    m.accuracy_percent,
                    m.priority,
                    m.contact,
                    m.effect_key,
                    m.effect_chance_percent
               FROM species_moves sm
               JOIN moves m ON m.move_id = sm.move_id
              ORDER BY sm.species_id, m.move_id"
        )->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $error) {
        // The species_moves table has not been applied to this database yet.
        return [];
    }

    $map = [];
    foreach ($rows as $row) {
        $map[(int)$row['species_id']][] = battle_normalize_move_row($row, $element_lookup);
    }

    return $map;
}

function battle_reserved_move_ids(array $species_move_map): array {
    $reserved = [];
    foreach ($species_move_map as $moves) {
        foreach ($moves as $move) {
            $reserved[(int)$move['id']] = true;
        }
    }

    return $reserved;
}

function battle_load_instance_move_map(array $pet_ids, array $element_lookup): array {
    $pet_ids = array_values(array_filter(array_map('intval', $pet_ids), static fn(int $id): bool => $id > 0));
    if (!$pet_ids) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($pet_ids), '?'));
    try {
        $rows = q(
            "SELECT pim.pet_instance_id,
                    pim.slot,
                    m.move_id AS id,
                    m.move_key,
                    m.move_name AS name,
                    m.category,
                    m.power,
                    m.element_id,
                    m.accuracy_percent,
                    m.priority,
                    m.contact,
                    m.effect_key,
                    m.effect_chance_percent
               FROM pet_instance_moves pim
               JOIN moves m ON m.move_id = pim.move_id
              WHERE pim.pet_instance_id IN ({$placeholders})
              ORDER BY pim.pet_instance_id, pim.slot",
            $pet_ids
        )->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $error) {
        // The pet_instance_moves table has not been applied to this database yet.
        return [];
    }

    $map = [];
    foreach ($rows as $row) {
        $move = battle_normalize_move_row($row, $element_lookup);
        $move['slot'] = (int)$row['slot'];
        $map[(int)$row['pet_instance_id']][] = $move;
    }

    return $map;
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

function battle_generate_moves_for_pet(array $pet, array $attack_pool, array $species_move_map): array {
    // Up to two signature moves from species_moves, rest from the shared pool.
    $moves = array_slice($species_move_map[(int)($pet['speciesId'] ?? 0)] ?? [], 0, 2);
    $seen = [];
    foreach ($moves as $move) {
        $seen[(int)$move['id']] = true;
    }

    foreach (battle_pick_moves_for_pet($pet, $attack_pool) as $move) {
        if (isset($seen[(int)$move['id']]) || count($moves) >= 4) {
            continue;
        }
        $seen[(int)$move['id']] = true;
        $moves[] = $move;
    }

    return $moves;
}

function battle_assign_moves(array $team, array $attack_pool, array $species_move_map = [], array $instance_move_map = []): array {
    foreach ($team as &$pet) {
        $stored = $instance_move_map[(int)($pet['id'] ?? 0)] ?? [];
        $pet['moves'] = $stored
            ? array_slice(array_values($stored), 0, 4)
            : battle_generate_moves_for_pet($pet, $attack_pool, $species_move_map);
    }
    unset($pet);

    return $team;
}

// --- Yara Dojo helpers ------------------------------------------------------

function dojo_learn_price(int $level): int {
    return 100 + (max(1, $level) * 25);
}

function dojo_unlearn_price(int $level): int {
    return 50 + (max(1, $level) * 10);
}

function dojo_tables_ready(): bool {
    try {
        q("SELECT 1 FROM species_movepool LIMIT 1");
        q("SELECT 1 FROM pet_instance_moves LIMIT 1");
        return true;
    } catch (Throwable $error) {
        return false;
    }
}

// Moves this creature could learn right now: species_movepool rows at or
// below its level, minus what it already knows.
function dojo_learnable_moves(int $species_id, int $level, array $known_move_ids, array $element_lookup): array {
    $rows = q(
        "SELECT m.move_id AS id,
                m.move_key,
                m.move_name AS name,
                m.category,
                m.power,
                m.element_id,
                m.accuracy_percent,
                m.priority,
                m.contact,
                m.effect_key,
                m.effect_chance_percent,
                smp.learnt_at_level
           FROM species_movepool smp
           JOIN moves m ON m.move_id = smp.move_id
          WHERE smp.species_id = ?
            AND smp.learnt_at_level <= ?
          ORDER BY smp.learnt_at_level, m.move_name",
        [$species_id, $level]
    )->fetchAll(PDO::FETCH_ASSOC);

    $known = array_fill_keys(array_map('intval', $known_move_ids), true);
    $out = [];
    foreach ($rows as $row) {
        if (isset($known[(int)$row['id']])) {
            continue;
        }
        $move = battle_normalize_move_row($row, $element_lookup);
        $move['learntAtLevel'] = (int)$row['learnt_at_level'];
        $out[] = $move;
    }

    return $out;
}

function dojo_move_is_learnable(int $species_id, int $level, int $move_id): bool {
    $found = q(
        "SELECT 1
           FROM species_movepool
          WHERE species_id = ?
            AND move_id = ?
            AND learnt_at_level <= ?",
        [$species_id, $move_id, $level]
    )->fetchColumn();

    return (bool)$found;
}

// First dojo visit: freeze the creature's currently generated battle moveset
// into pet_instance_moves so the player edits something familiar.
// Returns the slot-ordered moveset plus whether adoption happened just now.
function dojo_load_or_adopt_moveset(array $pet, array $element_lookup): array {
    $pet_id = (int)($pet['id'] ?? 0);
    $stored = battle_load_instance_move_map([$pet_id], $element_lookup);
    if (!empty($stored[$pet_id])) {
        return ['moves' => $stored[$pet_id], 'adopted' => false];
    }

    $species_move_map = battle_load_species_move_map($element_lookup);
    $reserved_move_ids = battle_reserved_move_ids($species_move_map);
    $attack_pool = array_values(array_filter(
        battle_load_attack_pool($element_lookup),
        static fn(array $move): bool => !isset($reserved_move_ids[(int)$move['id']])
    ));

    $generated = array_slice(battle_generate_moves_for_pet($pet, $attack_pool, $species_move_map), 0, 4);
    foreach ($generated as $index => $move) {
        q(
            "INSERT IGNORE INTO pet_instance_moves (pet_instance_id, slot, move_id) VALUES (?, ?, ?)",
            [$pet_id, $index + 1, (int)$move['id']]
        );
    }

    $stored = battle_load_instance_move_map([$pet_id], $element_lookup);
    return ['moves' => $stored[$pet_id] ?? [], 'adopted' => true];
}
