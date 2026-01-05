<?php
require_once __DIR__.'/../db.php';
require_once __DIR__.'/temp_user.php';

function get_user_pets(int $user_id, bool $include_inactive = false): array {
    if ($user_id === 0) {
        return temp_user_get_pets();
    }
    $where = "WHERE pi.owner_user_id = ?";
    if (!$include_inactive) {
        $where .= " AND COALESCE(pi.inactive, 0) = 0";
    }
    return q(
        "SELECT pi.pet_instance_id,
                pi.species_id,
                pi.nickname,
                pi.color_id,
                pi.level,
                pi.hp_current,
                pi.hp_max,
                pi.gender,
                pi.hunger,
                pi.happiness,
                pi.intelligence,
                pi.sickness,
                pi.inactive,
                ps.region_id,
                ps.species_name,
                r.region_name,
                pc.color_name
           FROM pet_instances pi
           JOIN pet_species ps ON ps.species_id = pi.species_id
           LEFT JOIN regions r ON r.region_id = ps.region_id
           LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
          {$where}",
        [$user_id]
    )->fetchAll(PDO::FETCH_ASSOC);
}

function pet_image_url(string $species_name, ?string $color_name): string {
    $species_slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $species_name));
    $color_slug = $color_name ? strtolower(preg_replace('/[^a-z0-9]+/i', '_', $color_name)) : '';
    $path = "images/{$species_slug}_f_{$color_slug}.webp";
    if (!file_exists(__DIR__ . '/../' . $path)) {
        return 'images/tengu_f_blue.png';
    }
    return $path;
}

function get_owned_pet(int $user_id, int $pet_id, bool $include_inactive = false): ?array {
    if ($user_id <= 0 || $pet_id <= 0) {
        return null;
    }
    $inactiveClause = $include_inactive ? '' : ' AND COALESCE(pi.inactive, 0) = 0';
    $pet = q(
        "SELECT pi.pet_instance_id,
                pi.owner_user_id,
                pi.nickname,
                pi.color_id,
                pi.species_id,
                pi.inactive,
                ps.species_name,
                pc.color_name
           FROM pet_instances pi
           JOIN pet_species ps ON ps.species_id = pi.species_id
           LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
          WHERE pi.owner_user_id = ? AND pi.pet_instance_id = ?{$inactiveClause}",
        [$user_id, $pet_id]
    )->fetch(PDO::FETCH_ASSOC);

    return $pet ?: null;
}

function get_abandoned_pets(): array {
    return q(
        "SELECT ap.ap_id,
                ap.creature_name,
                ap.abandoned_at,
                pi.pet_instance_id AS creature_id,
                u.username AS old_player_name,
                ps.species_name,
                pc.color_name
           FROM abandoned_pets ap
           JOIN pet_instances pi ON pi.pet_instance_id = ap.creature_id
           LEFT JOIN users u ON u.user_id = ap.old_player_id
           JOIN pet_species ps ON ps.species_id = pi.species_id
           LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
          WHERE ap.creature_id IS NOT NULL
       ORDER BY ap.abandoned_at DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
}