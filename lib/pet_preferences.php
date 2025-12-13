<?php
require_once __DIR__.'/../db.php';

function find_region_id_by_name(string $region_name): ?int {
    if ($region_name === '') {
        return null;
    }

    $row = q(
        'SELECT region_id FROM regions WHERE LOWER(region_name) = LOWER(?) LIMIT 1',
        [$region_name]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    return (int)($row['region_id'] ?? 0) ?: null;
}

function get_pet_location_like_value(array $pet, ?array $location): ?int {
    $species_id = (int)($pet['species_id'] ?? 0);
    $nation_name = trim($location['nation'] ?? '');
    if ($species_id <= 0 || $nation_name === '') {
        return null;
    }

    $region_id = find_region_id_by_name($nation_name);
    if ($region_id === null) {
        return null;
    }

    $like_value = q(
        'SELECT `like` FROM pet_like_city WHERE pet_id = ? AND country_id = ? LIMIT 1',
        [$species_id, $region_id]
    )->fetchColumn();

    if ($like_value === false) {
        return null;
    }

    return (int)$like_value;
}