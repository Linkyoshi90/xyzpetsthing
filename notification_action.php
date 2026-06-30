<?php
require_once __DIR__.'/lib/input.php';
require_once __DIR__.'/auth.php';
require_once __DIR__.'/lib/random_events.php';
require_once __DIR__.'/lib/friendships.php';
require_once __DIR__.'/lib/chat.php';
require_once __DIR__.'/lib/gifts.php';

header('Content-Type: application/json; charset=utf-8');

function notification_total_count(): int
{
    $count = count(random_event_notifications());
    $user = current_user();
    if ($user && (int)($user['id'] ?? 0) !== 0) {
        $count += friendship_pending_request_count((int)$user['id']);
        $count += chat_unread_sender_count((int)$user['id']);
        $count += gift_pending_count((int)$user['id']);
    }
    return $count;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$action = input_string($_POST['action'] ?? '', 40);

if ($action === 'dismiss') {
    $id = input_string($_POST['id'] ?? '', 32);
    random_event_dismiss_notification($id);
    echo json_encode(['ok' => true, 'count' => notification_total_count()]);
    exit;
}

if ($action === 'dismiss_all') {
    random_event_dismiss_all_notifications();
    echo json_encode(['ok' => true, 'count' => notification_total_count()]);
    exit;
}

if ($action === 'accept_friend_request') {
    $user = current_user();
    if (!$user || (int)($user['id'] ?? 0) === 0) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'You must be logged in to accept friend requests.']);
        exit;
    }

    $requestId = input_int($_POST['request_id'] ?? 0, 1);
    $result = friendship_accept_request($requestId, (int)$user['id']);
    if (empty($result['ok'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'Friend request could not be accepted.']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'count' => notification_total_count(),
        'message' => $result['message'] ?? 'Friend request accepted.',
    ]);
    exit;
}

if ($action === 'accept_gift' || $action === 'decline_gift') {
    $user = current_user();
    if (!$user || (int)($user['id'] ?? 0) === 0) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'You must be logged in to handle gifts.']);
        exit;
    }

    $giftId = input_int($_POST['gift_id'] ?? 0, 1);
    $result = $action === 'accept_gift'
        ? gift_accept($giftId, (int)$user['id'])
        : gift_decline($giftId, (int)$user['id']);

    if (empty($result['ok'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'The gift could not be handled.']);
        exit;
    }

    $message = !empty($result['accepted'])
        ? 'You accepted '.$result['item_name'].' from '.$result['sender_name'].'.'
        : 'You declined '.$result['item_name'].' from '.$result['sender_name'].'.';

    echo json_encode([
        'ok' => true,
        'count' => notification_total_count(),
        'message' => $message,
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Unknown notification action']);
