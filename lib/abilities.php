<?php
require_once __DIR__ . '/errors.php';
require_once __DIR__ . '/../db.php';

const ABILITIES_DATA_FILE = __DIR__ . '/../data/abilities.json';

function abilities_catalog(): array
{
    static $catalog = null;
    if ($catalog !== null) {
        return $catalog;
    }

    $catalog = [
        'by_id' => [],
        'by_species' => [],
    ];

    $json = @file_get_contents(ABILITIES_DATA_FILE);
    if ($json === false) {
        app_add_error('Ability data could not be read.');
        return $catalog;
    }

    try {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    } catch (Throwable $err) {
        app_add_error_from_exception($err, 'Ability data is invalid:');
        return $catalog;
    }

    foreach (($data['abilities'] ?? []) as $ability) {
        $ability_id = (int)($ability['id'] ?? 0);
        $species_id = (int)($ability['species']['id'] ?? 0);
        if ($ability_id <= 0 || $species_id <= 0 || trim((string)($ability['name'] ?? '')) === '') {
            continue;
        }

        $catalog['by_id'][$ability_id] = $ability;
        $catalog['by_species'][$species_id][] = $ability;
    }

    return $catalog;
}

function abilities_for_species(int $species_id): array
{
    if ($species_id <= 0) {
        return [];
    }

    $catalog = abilities_catalog();
    return $catalog['by_species'][$species_id] ?? [];
}

function ability_id_for_species(int $species_id, bool $random = true): ?int
{
    $abilities = abilities_for_species($species_id);
    if (!$abilities) {
        return null;
    }

    $index = $random ? array_rand($abilities) : 0;
    return (int)$abilities[$index]['id'];
}

/**
 * Resolve a random, species-valid ability id for a brand-new pet (hatch or
 * creation). Prefers the JSON catalog so the stored id matches what the battle
 * interpreter reads, but falls back to the database catalog (`pet_has_ability`)
 * when the JSON file cannot be read at runtime. The DB fallback is what keeps
 * birth/creation assigning an ability even if `data/abilities.json` was not
 * deployed alongside the PHP changes.
 */
function ability_id_for_new_pet(int $species_id): ?int
{
    if ($species_id <= 0) {
        return null;
    }

    $id = ability_id_for_species($species_id);
    if ($id !== null) {
        return $id;
    }

    $abilityId = q(
        "SELECT ability_id FROM pet_has_ability WHERE species_id = ? ORDER BY RAND() LIMIT 1",
        [$species_id]
    )->fetchColumn();

    return $abilityId !== false ? (int)$abilityId : null;
}

function ability_for_pet(array $pet): ?array
{
    $species_id = (int)($pet['species_id'] ?? $pet['speciesId'] ?? 0);
    $ability_id = (int)($pet['ability_id'] ?? $pet['abilityId'] ?? 0);
    $catalog = abilities_catalog();

    if ($ability_id > 0 && isset($catalog['by_id'][$ability_id])) {
        $ability = $catalog['by_id'][$ability_id];
        if ((int)($ability['species']['id'] ?? 0) === $species_id) {
            return $ability;
        }
    }

    return $catalog['by_species'][$species_id][0] ?? null;
}

function ability_battle_payload(?array $ability): ?array
{
    if (!$ability) {
        return null;
    }

    return [
        'id' => (int)$ability['id'],
        'key' => (string)($ability['key'] ?? ''),
        'name' => (string)$ability['name'],
        'description' => (string)($ability['description'] ?? ''),
        'battle' => [
            'summary' => (string)($ability['battle']['summary'] ?? ''),
            'instructions' => array_values($ability['battle']['instructions'] ?? []),
        ],
    ];
}
