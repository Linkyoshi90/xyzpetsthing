<?php
require_once __DIR__.'/auth.php';
require_login();
require_once __DIR__.'/lib/input.php';
require_once __DIR__.'/lib/gifts.php';

header('Content-Type: application/json; charset=utf-8');

function gift_json_error(string $message, int $status = 400): void
{
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

$uid = (int)(current_user()['id'] ?? 0);
if ($uid <= 0) {
    // Guest / temp accounts keep their inventory in the session and have no friends,
    // so gifting is a logged-in, persisted-account feature only.
    gift_json_error('You need a registered account to send gifts.', 403);
}

$action = input_string($_POST['action'] ?? $_GET['action'] ?? '', 20);

if ($action === 'inventory') {
    echo json_encode([
        'ok' => true,
        'items' => array_map(static function (array $row): array {
            return [
                'item_id' => (int)$row['item_id'],
                'item_name' => (string)$row['item_name'],
                'quantity' => (int)$row['quantity'],
                'category_name' => (string)($row['category_name'] ?? ''),
            ];
        }, gift_sender_inventory($uid)),
    ]);
    exit;
}

// Sending now flows through the chat "/gift <id>" command (user_chat_action.php),
// so this endpoint only serves the item picker.
gift_json_error('Unknown gift action.');
