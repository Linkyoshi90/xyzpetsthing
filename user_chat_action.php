<?php
require_once __DIR__.'/auth.php';
require_login();
require_once __DIR__.'/lib/chat.php';
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
    $messages = get_conversation($uid, $friendId, 200);
    echo json_encode([
        'ok' => true,
        'messages' => array_map(function ($msg) {
            return [
                'id' => $msg['id'],
                'direction' => $msg['direction'],
                'body' => nl2br(htmlspecialchars($msg['body'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
                'timestamp' => date('M j, Y g:i A', strtotime($msg['created_at'])),
            ];
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
    $saved = save_chat_message($uid, $friendId, $message);
    echo json_encode([
        'ok' => true,
        'message' => [
            'id' => $saved['id'],
            'direction' => $saved['direction'],
            'body' => nl2br(htmlspecialchars($saved['body'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
            'timestamp' => date('M j, Y g:i A'),
        ],
    ]);
    exit;
}

json_error('Unsupported action.', 400);
