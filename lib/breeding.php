<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/pets.php';

function breeding_best_stat_name(array $pet): string
{
    $stats = [
        'hp_max' => (int)($pet['hp_max'] ?? $pet['hp_current'] ?? 0),
        'atk' => (int)($pet['atk'] ?? 0),
        'def' => (int)($pet['def'] ?? 0),
        'initiative' => (int)($pet['initiative'] ?? 0),
    ];
    arsort($stats);
    return (string)array_key_first($stats);
}

function breeding_fetch_pet(int $user_id, int $pet_id): ?array
{
    $pet = q(
        "SELECT pi.*, ps.species_name
           FROM pet_instances pi
           JOIN pet_species ps ON ps.species_id = pi.species_id
          WHERE pi.pet_instance_id = ? AND pi.owner_user_id = ?",
        [$pet_id, $user_id]
    )->fetch(PDO::FETCH_ASSOC);
    return $pet ?: null;
}

function breeding_active_pairs(int $user_id): array
{
    return q(
        "SELECT b.*,
                pf.nickname AS father_name,
                pf.species_id AS father_species_id,
                psf.species_name AS father_species_name,
                pm.nickname AS mother_name,
                pm.species_id AS mother_species_id,
                psm.species_name AS mother_species_name
           FROM breeding b
           LEFT JOIN pet_instances pf ON pf.pet_instance_id = b.father
           LEFT JOIN pet_species psf ON psf.species_id = pf.species_id
           LEFT JOIN pet_instances pm ON pm.pet_instance_id = b.mother
           LEFT JOIN pet_species psm ON psm.species_id = pm.species_id
          WHERE b.owner_user_id = ?",
        [$user_id]
    )->fetchAll(PDO::FETCH_ASSOC);
}

function breeding_start_pair(int $user_id, int $mother_id, ?int $father_id = null): array
{
    $mother = breeding_fetch_pet($user_id, $mother_id);
    if (!$mother || (int)($mother['inactive'] ?? 0) === 1) {
        return ['ok' => false, 'message' => 'Mother must be an active pet you own.'];
    }
    if ($father_id && $father_id === $mother_id) {
        return ['ok' => false, 'message' => 'Pick two different creatures.'];
    }
    $father = null;
    $fatherSpeciesId = null;
    $fatherName = 'Daycare Stallion';
    $fatherBestStat = 'hp_max';
    if ($father_id) {
        $father = breeding_fetch_pet($user_id, $father_id);
        if (!$father || (int)($father['inactive'] ?? 0) === 1) {
            return ['ok' => false, 'message' => 'Father must be an active pet you own.'];
        }
        $fatherSpeciesId = (int)$father['species_id'];
        $fatherName = $father['nickname'] ?: ($father['species_name'] ?? 'Father');
        $fatherBestStat = breeding_best_stat_name($father);
    }

    $motherBestStat = breeding_best_stat_name($mother);
    $eggSpeciesOptions = array_values(array_filter([
        (int)$mother['species_id'],
        $fatherSpeciesId,
    ], static fn($val) => $val > 0));
    $eggSpeciesId = $eggSpeciesOptions[array_rand($eggSpeciesOptions)] ?? (int)$mother['species_id'];

    q(
        "INSERT INTO breeding (owner_user_id, father, mother, egg_creature_id, time_to_hatch, egg_count, father_best_stat, mother_best_stat)"
        . " VALUES (?,?,?,?,0,0,?,?)",
        [$user_id, $father_id, $mother_id, $eggSpeciesId, $fatherBestStat, $motherBestStat]
    );

    q(
        "UPDATE pet_instances SET inactive = 1 WHERE pet_instance_id IN (?,?) AND owner_user_id = ?",
        [$mother_id, $father_id ?? 0, $user_id]
    );

    return [
        'ok' => true,
        'message' => sprintf(
            '%s and %s are now at the daycare.',
            $mother['nickname'] ?: ($mother['species_name'] ?? 'Mother'),
            $fatherName
        ),
    ];
}

function breeding_random_color_id(): ?int
{
    $colorId = q("SELECT color_id FROM pet_colors ORDER BY RAND() LIMIT 1")->fetchColumn();
    return $colorId !== false ? (int)$colorId : null;
}

function breeding_hatch_ready_eggs(int $user_id): array
{
    $rows = q(
        "SELECT b.*,
                pf.nickname AS father_name,
                pf.pet_instance_id AS father_id,
                pm.nickname AS mother_name,
                pm.pet_instance_id AS mother_id,
                COALESCE(b.egg_creature_id, pm.species_id, pf.species_id) AS hatch_species_id
           FROM breeding b
           LEFT JOIN pet_instances pf ON pf.pet_instance_id = b.father
           LEFT JOIN pet_instances pm ON pm.pet_instance_id = b.mother
          WHERE b.owner_user_id = ? AND b.egg_count > 0 AND b.time_to_hatch <= 0",
        [$user_id]
    )->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        return [];
    }

    $speciesMeta = [];
    $details = [];

    foreach ($rows as $row) {
        $speciesId = (int)($row['hatch_species_id'] ?? 0);
        if ($speciesId <= 0) {
            continue;
        }
        if (!isset($speciesMeta[$speciesId])) {
            $speciesMeta[$speciesId] = q(
                "SELECT species_name, base_hp, base_atk, base_def, base_init FROM pet_species WHERE species_id = ?",
                [$speciesId]
            )->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        $species = $speciesMeta[$speciesId];
        if (!$species) {
            continue;
        }

        $colorId = breeding_random_color_id();
        $eggs = (int)$row['egg_count'];
        for ($i = 0; $i < $eggs; $i++) {
            $hpMax = max(1, (int)$species['base_hp'] + (mt_rand(0, 1) ? 1 : -1) * mt_rand(1, 5));
            $atk = max(1, (int)$species['base_atk'] + (mt_rand(0, 1) ? 1 : -1) * mt_rand(1, 5));
            $def = max(1, (int)$species['base_def'] + (mt_rand(0, 1) ? 1 : -1) * mt_rand(1, 5));
            $initiative = max(1, (int)$species['base_init'] + (mt_rand(0, 1) ? 1 : -1) * mt_rand(1, 5));

            $statBonuses = [
                $row['father_best_stat'] ?? null,
                $row['mother_best_stat'] ?? null,
            ];
            foreach ($statBonuses as $bonusStat) {
                if (!$bonusStat) {
                    continue;
                }
                $bonus = mt_rand(1, 5);
                switch ($bonusStat) {
                    case 'hp_max':
                        $hpMax += $bonus;
                        break;
                    case 'atk':
                        $atk += $bonus;
                        break;
                    case 'def':
                        $def += $bonus;
                        break;
                    case 'initiative':
                        $initiative += $bonus;
                        break;
                }
            }

            q(
                "INSERT INTO pet_instances (owner_user_id, species_id, color_id, gender, level, experience, hp_current, hp_max, atk, def, initiative)"
                . " VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                [
                    $user_id,
                    $speciesId,
                    $colorId,
                    'f',
                    1,
                    0,
                    $hpMax,
                    $hpMax,
                    $atk,
                    $def,
                    $initiative,
                ]
            );
            $details[] = sprintf(
                'An egg hatched into a %s!',
                $species['species_name'] ?? 'mystery creature'
            );
        }

        $parentIds = array_filter([(int)($row['father_id'] ?? 0), (int)($row['mother_id'] ?? 0)]);
        if ($parentIds) {
            $placeholders = implode(',', array_fill(0, count($parentIds), '?'));
            q(
                "UPDATE pet_instances SET inactive = 0 WHERE pet_instance_id IN ($placeholders) AND owner_user_id = ?",
                array_merge($parentIds, [$user_id])
            );
        }

        q("DELETE FROM breeding WHERE breed_instance_id = ?", [$row['breed_instance_id']]);
    }

    return $details;
}
