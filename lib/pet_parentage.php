<?php
require_once __DIR__ . '/../db.php';

/**
 * Return every recorded ancestor of a pet, including the pet itself.
 *
 * The returned map is keyed by pet instance ID. Including the starting pet
 * makes the same data useful for detecting parent/child relationships as well
 * as shared ancestors.
 */
function pet_parentage_ancestry_map(int $pet_id): array
{
    if ($pet_id <= 0) {
        return [];
    }

    $ancestry = [];
    $expanded = [];
    $frontier = [$pet_id];

    while ($frontier) {
        $frontier = array_values(array_unique(array_filter(
            array_map('intval', $frontier),
            static fn(int $id): bool => $id > 0 && !isset($expanded[$id])
        )));
        if (!$frontier) {
            break;
        }

        foreach ($frontier as $id) {
            $expanded[$id] = true;
            $ancestry[$id] = $ancestry[$id] ?? [
                'piid' => $id,
                'motherid' => null,
                'fatherid' => null,
                'recorded' => false,
            ];
        }

        $placeholders = implode(',', array_fill(0, count($frontier), '?'));
        $rows = q(
            "SELECT piid, motherid, fatherid
               FROM pet_parentage
              WHERE piid IN ($placeholders)",
            $frontier
        )->fetchAll(PDO::FETCH_ASSOC);

        $next = [];
        foreach ($rows as $row) {
            $child_id = (int)$row['piid'];
            $mother_id = isset($row['motherid']) ? (int)$row['motherid'] : 0;
            $father_id = isset($row['fatherid']) ? (int)$row['fatherid'] : 0;
            $ancestry[$child_id] = [
                'piid' => $child_id,
                'motherid' => $mother_id > 0 ? $mother_id : null,
                'fatherid' => $father_id > 0 ? $father_id : null,
                'recorded' => true,
            ];

            if ($mother_id > 0 && !isset($expanded[$mother_id])) {
                $next[] = $mother_id;
            }
            if ($father_id > 0 && !isset($expanded[$father_id])) {
                $next[] = $father_id;
            }
        }

        $frontier = $next;
    }

    return $ancestry;
}

function pet_parentage_are_related(int $first_pet_id, int $second_pet_id): bool
{
    if ($first_pet_id <= 0 || $second_pet_id <= 0) {
        return false;
    }

    $first_ancestry = pet_parentage_ancestry_map($first_pet_id);
    $second_ancestry = pet_parentage_ancestry_map($second_pet_id);

    return pet_parentage_maps_are_related($first_ancestry, $second_ancestry);
}

function pet_parentage_maps_are_related(array $first_ancestry, array $second_ancestry): bool
{
    return (bool)array_intersect_key($first_ancestry, $second_ancestry);
}

function pet_parentage_pair_inbreeding_generation(int $mother_id, ?int $father_id): int
{
    if ($mother_id <= 0 || $father_id === null || $father_id <= 0) {
        return 0;
    }

    $parentage = array_replace(
        pet_parentage_ancestry_map($mother_id),
        pet_parentage_ancestry_map($father_id)
    );

    return pet_parentage_pair_inbreeding_generation_from_map(
        $mother_id,
        $father_id,
        $parentage
    );
}

function pet_parentage_pair_inbreeding_generation_from_map(
    int $mother_id,
    int $father_id,
    array $parentage
): int {
    if (!pet_parentage_pair_is_close_from_map($mother_id, $father_id, $parentage)) {
        return 0;
    }

    $memo = [];
    $visiting = [];
    $mother_generation = pet_parentage_pet_inbreeding_generation_from_map(
        $mother_id,
        $parentage,
        $memo,
        $visiting
    );
    $father_generation = pet_parentage_pet_inbreeding_generation_from_map(
        $father_id,
        $parentage,
        $memo,
        $visiting
    );

    return max($mother_generation, $father_generation) + 1;
}

function pet_parentage_pair_is_close_from_map(int $first_id, int $second_id, array $parentage): bool
{
    if ($first_id <= 0 || $second_id <= 0 || $first_id === $second_id) {
        return false;
    }

    $first = $parentage[$first_id] ?? [];
    $second = $parentage[$second_id] ?? [];
    $first_mother = isset($first['motherid']) ? (int)$first['motherid'] : 0;
    $first_father = isset($first['fatherid']) ? (int)$first['fatherid'] : 0;
    $second_mother = isset($second['motherid']) ? (int)$second['motherid'] : 0;
    $second_father = isset($second['fatherid']) ? (int)$second['fatherid'] : 0;

    $are_full_siblings = $first_mother > 0
        && $first_father > 0
        && $first_mother === $second_mother
        && $first_father === $second_father;
    $first_is_parent = $second_mother === $first_id || $second_father === $first_id;
    $second_is_parent = $first_mother === $second_id || $first_father === $second_id;

    return $are_full_siblings || $first_is_parent || $second_is_parent;
}

function pet_parentage_pet_inbreeding_generation_from_map(
    int $pet_id,
    array $parentage,
    array &$memo,
    array &$visiting
): int {
    if (isset($memo[$pet_id])) {
        return $memo[$pet_id];
    }
    if (isset($visiting[$pet_id])) {
        return 0;
    }

    $row = $parentage[$pet_id] ?? [];
    $mother_id = isset($row['motherid']) ? (int)$row['motherid'] : 0;
    $father_id = isset($row['fatherid']) ? (int)$row['fatherid'] : 0;
    if ($mother_id <= 0 || $father_id <= 0
        || !pet_parentage_pair_is_close_from_map($mother_id, $father_id, $parentage)) {
        $memo[$pet_id] = 0;
        return 0;
    }

    $visiting[$pet_id] = true;
    $generation = max(
        pet_parentage_pet_inbreeding_generation_from_map($mother_id, $parentage, $memo, $visiting),
        pet_parentage_pet_inbreeding_generation_from_map($father_id, $parentage, $memo, $visiting)
    ) + 1;
    unset($visiting[$pet_id]);
    $memo[$pet_id] = $generation;

    return $generation;
}

function pet_parentage_insert_values(int $pet_id, int $mother_id, ?int $father_id): array
{
    return [
        $pet_id,
        $mother_id > 0 ? $mother_id : null,
        $father_id !== null && $father_id > 0 ? $father_id : null,
    ];
}

function pet_parentage_record(PDO $pdo, int $pet_id, int $mother_id, ?int $father_id): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO pet_parentage (piid, motherid, fatherid) VALUES (?, ?, ?)'
    );
    $stmt->execute(pet_parentage_insert_values($pet_id, $mother_id, $father_id));
}
