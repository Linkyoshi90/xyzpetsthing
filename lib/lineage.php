<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/pets.php';
require_once __DIR__ . '/pet_parentage.php';

function lineage_for_user(int $user_id, int $pet_id): ?array
{
    if ($user_id <= 0 || $pet_id <= 0) {
        return null;
    }

    $root = q(
        "SELECT pet_instance_id
           FROM pet_instances
          WHERE pet_instance_id = ? AND owner_user_id = ?",
        [$pet_id, $user_id]
    )->fetch(PDO::FETCH_ASSOC);
    if (!$root) {
        return null;
    }

    $parentage = pet_parentage_ancestry_map($pet_id);
    $pet_ids = array_keys($parentage);
    $pets = [];

    foreach (array_chunk($pet_ids, 500) as $id_chunk) {
        $placeholders = implode(',', array_fill(0, count($id_chunk), '?'));
        $rows = q(
            "SELECT pi.pet_instance_id,
                    pi.nickname,
                    pi.gender,
                    ps.species_name,
                    pc.color_name
               FROM pet_instances pi
               JOIN pet_species ps ON ps.species_id = pi.species_id
               LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
              WHERE pi.pet_instance_id IN ($placeholders)",
            $id_chunk
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $id = (int)$row['pet_instance_id'];
            $parents = $parentage[$id] ?? [];
            $name = trim((string)($row['nickname'] ?? ''));
            if ($name === '') {
                $name = (string)$row['species_name'];
            }
            $pets[] = [
                'id' => $id,
                'name' => $name,
                'species' => (string)$row['species_name'],
                'color' => (string)($row['color_name'] ?? ''),
                'gender' => (string)($row['gender'] ?? 'U'),
                'image' => pet_image_url(
                    (string)$row['species_name'],
                    isset($row['color_name']) ? (string)$row['color_name'] : null
                ),
                'motherId' => isset($parents['motherid']) ? (int)$parents['motherid'] : null,
                'fatherId' => isset($parents['fatherid']) ? (int)$parents['fatherid'] : null,
                'parentageRecorded' => !empty($parents['recorded']),
            ];
        }
    }

    return [
        'rootId' => $pet_id,
        'pets' => $pets,
    ];
}
