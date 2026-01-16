<?php
require_once __DIR__.'/../db.php';
require_once __DIR__.'/temp_user.php';
require_once __DIR__.'/shops.php';

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

function get_pet_cosmetics(int $pet_id): array {
    if ($pet_id <= 0) {
        return [];
    }
    $rows = q(
        "SELECT pc.item_id, pc.xcoord, pc.ycoord, pc.size, pc.rotation, i.item_name\n"
        ."FROM pet_cosmetics pc\n"
        ."JOIN items i ON i.item_id = pc.item_id\n"
        ."WHERE pc.pet_instance_id = ?\n"
        ."ORDER BY pc.id",
        [$pet_id]
    )->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($rows as $row) {
        $imageFile = shop_find_item_image($row['item_name']);
        $items[] = [
            'item_id' => (int) $row['item_id'],
            'name' => $row['item_name'],
            'image' => 'images/items/'.rawurlencode($imageFile),
            'x' => (int) $row['xcoord'],
            'y' => (int) $row['ycoord'],
            'size' => (int) $row['size'],
            'rotation' => (int) $row['rotation'],
        ];
    }

    return $items;
}

function render_pet_thumbnail(array $pet, string $class = 'thumb', string $alt = ''): string {
    $species_name = $pet['species_name'] ?? '';
    $color_name = $pet['color_name'] ?? null;
    $pet_image = pet_image_url($species_name, $color_name);
    if ($alt !== '') {
        $alt_text = $alt;
    } elseif (!empty($pet['nickname'])) {
        $alt_text = $pet['nickname'];
    } elseif ($species_name !== '') {
        $alt_text = $species_name;
    } else {
        $alt_text = 'Pet';
    }
    $pet_id = (int)($pet['pet_instance_id'] ?? $pet['creature_id'] ?? 0);

    $cosmetics = $pet_id > 0 ? get_pet_cosmetics($pet_id) : [];
    $base_path = __DIR__ . '/../' . $pet_image;
    if (!$cosmetics || !is_file($base_path)) {
        return sprintf(
            '<img class="%s" src="%s" alt="%s">',
            htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($pet_image, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($alt_text, ENT_QUOTES, 'UTF-8')
        );
    }

    $base_size = @getimagesize($base_path);
    if (!$base_size) {
        return sprintf(
            '<img class="%s" src="%s" alt="%s">',
            htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($pet_image, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($alt_text, ENT_QUOTES, 'UTF-8')
        );
    }
    $base_width = (float) $base_size[0];
    $base_height = (float) $base_size[1];

    $layers = [];
    foreach ($cosmetics as $item) {
        $item_path = __DIR__ . '/../' . rawurldecode($item['image']);
        if (!is_file($item_path)) {
            continue;
        }
        $item_size = @getimagesize($item_path);
        if (!$item_size) {
            continue;
        }
        $scale = max(0.01, ($item['size'] ?? 100) / 100);
        $item_width = $item_size[0] * $scale;
        $item_height = $item_size[1] * $scale;
        if ($base_width <= 0 || $base_height <= 0) {
            continue;
        }
        $left = ($item['x'] / $base_width) * 100;
        $top = ($item['y'] / $base_height) * 100;
        $width = ($item_width / $base_width) * 100;
        $height = ($item_height / $base_height) * 100;
        $layers[] = sprintf(
            '<img class="pet-thumb-cosmetic" src="%s" alt="" style="left: %.3f%%; top: %.3f%%; width: %.3f%%; height: %.3f%%;">',
            htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'),
            $left,
            $top,
            $width,
            $height
        );
    }

    if (!$layers) {
        return sprintf(
            '<img class="%s" src="%s" alt="%s">',
            htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($pet_image, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($alt_text, ENT_QUOTES, 'UTF-8')
        );
    }

    return sprintf(
        '<span class="pet-thumb-stack %s"><img class="pet-thumb-base" src="%s" alt="%s">%s</span>',
        htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($pet_image, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($alt_text, ENT_QUOTES, 'UTF-8'),
        implode('', $layers)
    );
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

function get_abandoned_pets(?int $user_id = null): array {
    $params = [];
    $where = "WHERE ap.creature_id IS NOT NULL";
    if ($user_id !== null) {
        $where .= " AND (ap.old_player_id IS NULL OR ap.old_player_id <> ?)";
        $params[] = $user_id;
    }
    return q(
        "SELECT ap.ap_id,
                ap.creature_name,
                ap.abandoned_at,
                ap.old_player_id,
                pi.pet_instance_id AS creature_id,
                u.username AS old_player_name,
                ps.species_name,
                pc.color_name
           FROM abandoned_pets ap
           JOIN pet_instances pi ON pi.pet_instance_id = ap.creature_id
           LEFT JOIN users u ON u.user_id = ap.old_player_id
           JOIN pet_species ps ON ps.species_id = pi.species_id
           LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
          {$where}
       ORDER BY ap.abandoned_at DESC",
        $params
    )->fetchAll(PDO::FETCH_ASSOC);
}
