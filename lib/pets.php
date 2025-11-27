<?php
require_once __DIR__.'/../db.php';
require_once __DIR__.'/temp_user.php';

function get_user_pets(int $user_id): array {
    if ($user_id === 0) {
        return temp_user_get_pets();
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
                ps.region_id,
                ps.species_name,
                r.region_name,
                pc.color_name
           FROM pet_instances pi
           JOIN pet_species ps ON ps.species_id = pi.species_id
           LEFT JOIN regions r ON r.region_id = ps.region_id
           LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
          WHERE pi.owner_user_id = ?",
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