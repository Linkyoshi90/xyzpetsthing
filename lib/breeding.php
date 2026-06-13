<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/pets.php';

const BREEDING_MAX_ACTIVE_PAIRS_PER_USER = 1;

function breeding_limit_message(): string
{
    return 'You already have a breeding session in progress. Finish it before starting another.';
}

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
           LEFT JOIN abandoned_pets ap ON ap.creature_id = pi.pet_instance_id
          WHERE pi.pet_instance_id = ? AND pi.owner_user_id = ?
            AND ap.creature_id IS NULL",
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

function breeding_active_pair_count(int $user_id): int
{
    if ($user_id <= 0) {
        return 0;
    }

    return (int)q(
        "SELECT COUNT(*)
           FROM breeding
          WHERE owner_user_id = ?",
        [$user_id]
    )->fetchColumn();
}

function breeding_start_pair(int $user_id, int $mother_id, ?int $father_id = null): array
{
    if (breeding_active_pair_count($user_id) >= BREEDING_MAX_ACTIVE_PAIRS_PER_USER) {
        return ['ok' => false, 'message' => breeding_limit_message()];
    }

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

    $pdo = db();
    if (!$pdo) {
        return ['ok' => false, 'message' => 'Daycare is unavailable right now.'];
    }

    try {
        $pdo->beginTransaction();

        $slotStmt = $pdo->prepare(
            "SELECT COUNT(*)
               FROM breeding
              WHERE owner_user_id = ?"
        );
        $slotStmt->execute([$user_id]);
        if ((int)$slotStmt->fetchColumn() >= BREEDING_MAX_ACTIVE_PAIRS_PER_USER) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => breeding_limit_message()];
        }

        $insertStmt = $pdo->prepare(
            "INSERT INTO breeding (owner_user_id, father, mother, egg_creature_id, time_to_hatch, egg_count, father_best_stat, mother_best_stat)"
            . " VALUES (?,?,?,?,0,0,?,?)"
        );
        $insertStmt->execute([$user_id, $father_id, $mother_id, $eggSpeciesId, $fatherBestStat, $motherBestStat]);

        $parentIds = array_values(array_filter([$mother_id, $father_id], static fn($id) => (int)$id > 0));
        $placeholders = implode(',', array_fill(0, count($parentIds), '?'));
        $updateStmt = $pdo->prepare(
            "UPDATE pet_instances
                SET inactive = 1
              WHERE pet_instance_id IN ($placeholders)
                AND owner_user_id = ?
                AND COALESCE(inactive, 0) = 0"
        );
        $updateStmt->execute(array_merge($parentIds, [$user_id]));
        if ($updateStmt->rowCount() !== count($parentIds)) {
            throw new RuntimeException('One of the selected creatures is no longer available.');
        }

        $pdo->commit();
    } catch (Throwable $err) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($err instanceof PDOException && (string)$err->getCode() === '23000') {
            return ['ok' => false, 'message' => breeding_limit_message()];
        }
        app_add_error_from_exception($err, 'Could not start breeding session:');
        return ['ok' => false, 'message' => 'Could not start that breeding session. Please try again.'];
    }

    return [
        'ok' => true,
        'message' => sprintf(
            '%s and %s are now at the daycare.',
            $mother['nickname'] ?: ($mother['species_name'] ?? 'Mother'),
            $fatherName
        ),
    ];
}

function breeding_return_pair(int $user_id, int $breed_instance_id): array
{
    $pair = q(
        "SELECT b.*,
                pf.nickname AS father_name,
                psf.species_name AS father_species_name,
                pm.nickname AS mother_name,
                psm.species_name AS mother_species_name
           FROM breeding b
           LEFT JOIN pet_instances pf ON pf.pet_instance_id = b.father
           LEFT JOIN pet_species psf ON psf.species_id = pf.species_id
           LEFT JOIN pet_instances pm ON pm.pet_instance_id = b.mother
           LEFT JOIN pet_species psm ON psm.species_id = pm.species_id
          WHERE b.breed_instance_id = ? AND b.owner_user_id = ?",
        [$breed_instance_id, $user_id]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$pair) {
        return ['ok' => false, 'message' => 'That daycare session was not found.'];
    }

    if ((int)($pair['egg_count'] ?? 0) > 0) {
        if ((int)($pair['time_to_hatch'] ?? 0) <= 0) {
            return ['ok' => false, 'message' => 'This pair has an egg ready. Collect the hatched egg before taking the parents back.'];
        }
        return ['ok' => false, 'message' => 'This pair has an egg in progress. Wait for it to hatch before taking the parents back.'];
    }

    $parentIds = array_values(array_filter([
        (int)($pair['mother'] ?? 0),
        (int)($pair['father'] ?? 0),
    ], static fn($id) => $id > 0));

    if ($parentIds) {
        $placeholders = implode(',', array_fill(0, count($parentIds), '?'));
        q(
            "UPDATE pet_instances
                SET inactive = 0
              WHERE pet_instance_id IN ($placeholders)
                AND owner_user_id = ?
                AND NOT EXISTS (
                    SELECT 1 FROM abandoned_pets ap WHERE ap.creature_id = pet_instances.pet_instance_id
                )",
            array_merge($parentIds, [$user_id])
        );
    }

    q(
        "DELETE FROM breeding WHERE breed_instance_id = ? AND owner_user_id = ?",
        [$breed_instance_id, $user_id]
    );

    $motherName = $pair['mother_name'] ?: ($pair['mother_species_name'] ?? 'Mother');
    $fatherId = (int)($pair['father'] ?? 0);
    if ($fatherId > 0) {
        $fatherName = $pair['father_name'] ?: ($pair['father_species_name'] ?? 'Father');
        $message = sprintf('%s and %s returned from daycare.', $motherName, $fatherName);
    } else {
        $message = sprintf('%s returned from daycare.', $motherName);
    }

    return ['ok' => true, 'message' => $message];
}

function breeding_return_inactive_pet(int $user_id, int $pet_id): array
{
    $pet = breeding_fetch_pet($user_id, $pet_id);
    if (!$pet) {
        return ['ok' => false, 'message' => 'That creature was not found.'];
    }
    if ((int)($pet['inactive'] ?? 0) === 0) {
        return ['ok' => false, 'message' => 'That creature is already active.'];
    }

    $activePairCount = (int)q(
        "SELECT COUNT(*)
           FROM breeding
          WHERE owner_user_id = ? AND (mother = ? OR father = ?)",
        [$user_id, $pet_id, $pet_id]
    )->fetchColumn();

    if ($activePairCount > 0) {
        return ['ok' => false, 'message' => 'That creature is part of an active breeding pair. Use the pair return button.'];
    }

    q(
        "UPDATE pet_instances
            SET inactive = 0
          WHERE pet_instance_id = ?
            AND owner_user_id = ?
            AND NOT EXISTS (
                SELECT 1 FROM abandoned_pets ap WHERE ap.creature_id = pet_instances.pet_instance_id
            )",
        [$pet_id, $user_id]
    );

    $name = $pet['nickname'] ?: ($pet['species_name'] ?? 'Creature');
    return ['ok' => true, 'message' => sprintf('%s returned from daycare.', $name)];
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
                "UPDATE pet_instances
                    SET inactive = 0
                  WHERE pet_instance_id IN ($placeholders)
                    AND owner_user_id = ?
                    AND NOT EXISTS (
                        SELECT 1 FROM abandoned_pets ap WHERE ap.creature_id = pet_instances.pet_instance_id
                    )",
                array_merge($parentIds, [$user_id])
            );
        }

        q("DELETE FROM breeding WHERE breed_instance_id = ?", [$row['breed_instance_id']]);
    }

    return $details;
}
