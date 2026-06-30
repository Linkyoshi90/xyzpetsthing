<?php
require_once __DIR__.'/auth.php';
require_login();
require_once __DIR__.'/lib/input.php';
require_once __DIR__.'/lib/friendships.php';
require_once __DIR__.'/lib/pets.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function friend_pets_json_error(string $message, int $status = 400): void
{
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

$uid = (int)(current_user()['id'] ?? 0);
$friendId = input_int($_GET['friend_id'] ?? 0, 1);

if ($uid <= 0 || $friendId <= 0) {
    friend_pets_json_error('Invalid friend.', 400);
}

if (!friendship_users_are_friends($uid, $friendId)) {
    friend_pets_json_error('You can only view the pets of your friends.', 403);
}

$friend = q(
    'SELECT username FROM users WHERE user_id = ? LIMIT 1',
    [$friendId]
)->fetch(PDO::FETCH_ASSOC);

if (!$friend) {
    friend_pets_json_error('Friend not found.', 404);
}

$pets = array_map(static function (array $pet): array {
    $name = (string)($pet['nickname'] ?: $pet['species_name']);
    $details = [(string)$pet['species_name']];
    if (!empty($pet['color_name'])) {
        $details[] = (string)$pet['color_name'];
    }
    if (isset($pet['level'])) {
        $details[] = 'Level '.(int)$pet['level'];
    }

    return [
        'name' => $name,
        'details' => implode(' · ', $details),
        'thumbnail_html' => render_pet_thumbnail($pet, 'thumb', $name),
    ];
}, get_user_pets($friendId));

echo json_encode([
    'ok' => true,
    'friend' => [
        'id' => $friendId,
        'username' => (string)$friend['username'],
    ],
    'pets' => $pets,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
