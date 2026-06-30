<?php
require_once __DIR__.'/auth.php';
require_login();
require_once __DIR__.'/lib/chat.php';
require_once __DIR__.'/lib/gifts.php';
require_once __DIR__.'/lib/input.php';

header('Content-Type: application/json');

$uid = current_user()['id'];
$action = input_string($_POST['action'] ?? $_GET['action'] ?? '', 20);

function json_error(string $message, int $status = 400): void
{
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

if ($action === 'fetch') {
    $friendId = input_int($_GET['friend_id'] ?? 0, 1);
    if ($friendId <= 0) {
        json_error('Invalid friend selected.');
    }
    if (!users_are_friends($uid, $friendId)) {
        json_error('You are not friends with this user.', 403);
    }
    $afterId = input_int($_GET['after_id'] ?? 0, 0);
    $messages = get_conversation($uid, $friendId, 200, $afterId);
    // Viewing the thread (initial load, or a poll that pulled something new) marks
    // it read so it stops showing up as an unread notification.
    if ($afterId === 0 || !empty($messages)) {
        mark_conversation_read($uid, $friendId);
    }
    echo json_encode([
        'ok' => true,
        'messages' => array_map(static function ($msg) use ($uid) {
            return chat_message_view($msg, (int)$uid);
        }, $messages),
    ]);
    exit;
}

if ($action === 'send') {
    $friendId = input_int($_POST['friend_id'] ?? 0, 1);
    $message = input_string($_POST['message'] ?? '', 1000);
    if ($friendId <= 0) {
        json_error('Invalid friend selected.');
    }
    if ($message === '') {
        json_error('Message cannot be empty.');
    }
    if (!users_are_friends($uid, $friendId)) {
        json_error('You are not friends with this user.', 403);
    }
    $message = mb_substr($message, 0, 1000);

    // Gift command: "/gift <item_id>". A bare "/gift" is handled by the client
    // (it opens the item picker) and should never reach here, but guard anyway.
    if (preg_match('/^\/gift\b\s*(\d*)\s*$/i', $message, $m)) {
        $itemId = (int)($m[1] ?? 0);
        if ($itemId <= 0) {
            json_error('Use the Gift button to pick an item, or type /gift <item id>.');
        }
        $result = gift_send((int)$uid, $friendId, $itemId);
        if (empty($result['ok'])) {
            json_error($result['error'] ?? 'The gift could not be sent.');
        }
        // The stored message is the marker, not the typed command.
        $saved = save_chat_message((int)$uid, $friendId, gift_message_marker((int)$result['gift_id']));
        echo json_encode([
            'ok' => true,
            'message' => chat_message_view($saved, (int)$uid),
        ]);
        exit;
    }

    $saved = save_chat_message((int)$uid, $friendId, $message);
    echo json_encode([
        'ok' => true,
        'message' => chat_message_view($saved, (int)$uid),
    ]);
    exit;
}

json_error('Unsupported action.', 400);
