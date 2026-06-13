<?php
require_once __DIR__.'/../db.php';

const FRIENDSHIP_STATUS_PENDING = 'pending';
const FRIENDSHIP_STATUS_ACCEPTED = 'accepted';
const FRIENDSHIP_NOTIFICATION_LIMIT = 20;

function friendship_ensure_schema(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    if (!db()) {
        $ready = false;
        return false;
    }

    $rows = q(
        "SELECT COLUMN_NAME
           FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'user_friends'"
    )->fetchAll(PDO::FETCH_ASSOC);
    $columns = [];
    foreach ($rows as $row) {
        $columns[strtolower((string)$row['COLUMN_NAME'])] = true;
    }

    $addColumn = static function (string $sql): void {
        q($sql);
    };

    if (empty($columns['status'])) {
        $addColumn(
            "ALTER TABLE user_friends
             ADD COLUMN status ENUM('pending','accepted') NOT NULL DEFAULT 'accepted' AFTER friend_id"
        );
    }
    if (empty($columns['requested_by_user_id'])) {
        $addColumn(
            "ALTER TABLE user_friends
             ADD COLUMN requested_by_user_id BIGINT UNSIGNED NULL AFTER status"
        );
        q("UPDATE user_friends SET requested_by_user_id = user_id WHERE requested_by_user_id IS NULL");
    }
    if (empty($columns['requested_at'])) {
        $addColumn(
            "ALTER TABLE user_friends
             ADD COLUMN requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER requested_by_user_id"
        );
    }
    if (empty($columns['accepted_at'])) {
        $addColumn(
            "ALTER TABLE user_friends
             ADD COLUMN accepted_at TIMESTAMP NULL DEFAULT NULL AFTER requested_at"
        );
    }

    q("UPDATE user_friends SET accepted_at = NOW() WHERE status = 'accepted' AND accepted_at IS NULL");
    $ready = true;
    return true;
}

function friendship_get_between(int $userId, int $otherUserId): ?array
{
    if ($userId <= 0 || $otherUserId <= 0 || !friendship_ensure_schema()) {
        return null;
    }

    $row = q(
        "SELECT *
           FROM user_friends
          WHERE (user_id = ? AND friend_id = ?)
             OR (user_id = ? AND friend_id = ?)
          ORDER BY status = 'accepted' DESC, connection_id ASC
          LIMIT 1",
        [$userId, $otherUserId, $otherUserId, $userId]
    )->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function friendship_users_are_friends(int $userId, int $friendId): bool
{
    if ($userId <= 0 || $friendId <= 0 || !friendship_ensure_schema()) {
        return false;
    }

    return (bool)q(
        "SELECT 1
           FROM user_friends
          WHERE status = 'accepted'
            AND ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?))
          LIMIT 1",
        [$userId, $friendId, $friendId, $userId]
    )->fetchColumn();
}

function friendship_get_friend_list(int $userId): array
{
    if ($userId <= 0 || !friendship_ensure_schema()) {
        return [];
    }

    $rows = q(
        "SELECT uf.connection_id,
                CASE WHEN uf.user_id = ? THEN uf.friend_id ELSE uf.user_id END AS friend_id,
                u.username,
                u.created_at,
                uf.accepted_at
           FROM user_friends uf
           JOIN users u
             ON u.user_id = CASE WHEN uf.user_id = ? THEN uf.friend_id ELSE uf.user_id END
          WHERE uf.status = 'accepted'
            AND (uf.user_id = ? OR uf.friend_id = ?)
          ORDER BY u.username",
        [$userId, $userId, $userId, $userId]
    )->fetchAll(PDO::FETCH_ASSOC);

    $friends = [];
    foreach ($rows as $row) {
        $friends[(int)$row['friend_id']] = [
            'connection_id' => (int)$row['connection_id'],
            'id' => (int)$row['friend_id'],
            'friend_id' => (int)$row['friend_id'],
            'username' => (string)$row['username'],
            'created_at' => $row['created_at'],
            'accepted_at' => $row['accepted_at'],
        ];
    }

    return $friends;
}

function friendship_pending_requests_for_user(int $userId, int $limit = FRIENDSHIP_NOTIFICATION_LIMIT): array
{
    if ($userId <= 0 || !friendship_ensure_schema()) {
        return [];
    }

    $limit = max(1, min(100, $limit));
    $st = db()->prepare(
        "SELECT uf.connection_id,
                uf.user_id,
                uf.friend_id,
                uf.requested_by_user_id,
                uf.requested_at,
                requester.username AS requester_username
           FROM user_friends uf
           JOIN users requester
             ON requester.user_id = uf.requested_by_user_id
          WHERE uf.status = 'pending'
            AND uf.requested_by_user_id <> :uid
            AND (uf.user_id = :uid OR uf.friend_id = :uid)
          ORDER BY uf.requested_at DESC, uf.connection_id DESC
          LIMIT :limit"
    );
    $st->bindValue(':uid', $userId, PDO::PARAM_INT);
    $st->bindValue(':limit', $limit, PDO::PARAM_INT);
    $st->execute();

    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function friendship_outgoing_requests_for_user(int $userId, int $limit = 50): array
{
    if ($userId <= 0 || !friendship_ensure_schema()) {
        return [];
    }

    $limit = max(1, min(100, $limit));
    $st = db()->prepare(
        "SELECT uf.connection_id,
                uf.user_id,
                uf.friend_id,
                uf.requested_by_user_id,
                uf.requested_at,
                recipient.username AS recipient_username
           FROM user_friends uf
           JOIN users recipient
             ON recipient.user_id = CASE WHEN uf.user_id = :uid THEN uf.friend_id ELSE uf.user_id END
          WHERE uf.status = 'pending'
            AND uf.requested_by_user_id = :uid
          ORDER BY uf.requested_at DESC, uf.connection_id DESC
          LIMIT :limit"
    );
    $st->bindValue(':uid', $userId, PDO::PARAM_INT);
    $st->bindValue(':limit', $limit, PDO::PARAM_INT);
    $st->execute();

    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function friendship_pending_request_count(int $userId): int
{
    if ($userId <= 0 || !friendship_ensure_schema()) {
        return 0;
    }

    return (int)q(
        "SELECT COUNT(*)
           FROM user_friends
          WHERE status = 'pending'
            AND requested_by_user_id <> ?
            AND (user_id = ? OR friend_id = ?)",
        [$userId, $userId, $userId]
    )->fetchColumn();
}

function friendship_request_notifications(int $userId): array
{
    $requests = friendship_pending_requests_for_user($userId);
    $notifications = [];

    foreach ($requests as $request) {
        $requestedAt = strtotime((string)($request['requested_at'] ?? ''));
        $username = (string)($request['requester_username'] ?? 'Someone');
        $notifications[] = [
            'id' => 'friend-request-'.(int)$request['connection_id'],
            'type' => 'friend_request',
            'title' => 'Friend request',
            'message' => $username.' wants to be friends.',
            'details' => ['Accept to add them to your friends list and unlock chat.'],
            'time_label' => $requestedAt ? date('H:i', $requestedAt) : '',
            'dismissible' => false,
            'actions' => [
                [
                    'label' => 'Accept',
                    'post_action' => 'accept_friend_request',
                    'request_id' => (int)$request['connection_id'],
                ],
            ],
        ];
    }

    return $notifications;
}

function friendship_accept_request(int $connectionId, int $acceptingUserId): array
{
    if ($connectionId <= 0 || $acceptingUserId <= 0 || !friendship_ensure_schema()) {
        return ['ok' => false, 'error' => 'Invalid friendship request.'];
    }

    $row = q(
        "SELECT uf.*, requester.username AS requester_username
           FROM user_friends uf
           LEFT JOIN users requester
             ON requester.user_id = uf.requested_by_user_id
          WHERE uf.connection_id = ?
          LIMIT 1",
        [$connectionId]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return ['ok' => false, 'error' => 'Friend request not found.'];
    }

    if ((string)$row['status'] === FRIENDSHIP_STATUS_ACCEPTED) {
        return [
            'ok' => true,
            'message' => 'You are already friends.',
            'username' => (string)($row['requester_username'] ?? ''),
        ];
    }

    $isParticipant = (int)$row['user_id'] === $acceptingUserId || (int)$row['friend_id'] === $acceptingUserId;
    $isRequester = (int)$row['requested_by_user_id'] === $acceptingUserId;
    if (!$isParticipant || $isRequester) {
        return ['ok' => false, 'error' => 'You cannot accept this friend request.'];
    }

    q(
        "UPDATE user_friends
            SET status = 'accepted',
                accepted_at = NOW()
          WHERE connection_id = ?
            AND status = 'pending'",
        [$connectionId]
    );

    return [
        'ok' => true,
        'message' => 'Friend request accepted.',
        'username' => (string)($row['requester_username'] ?? ''),
    ];
}

function friendship_send_request(int $requesterId, int $recipientId): string
{
    if ($requesterId <= 0 || $recipientId <= 0 || $requesterId === $recipientId || !friendship_ensure_schema()) {
        return 'invalid';
    }

    $existing = friendship_get_between($requesterId, $recipientId);
    if ($existing) {
        if ((string)$existing['status'] === FRIENDSHIP_STATUS_ACCEPTED) {
            return 'already_friends';
        }

        if ((int)$existing['requested_by_user_id'] === $requesterId) {
            return 'request_pending';
        }

        $accepted = friendship_accept_request((int)$existing['connection_id'], $requesterId);
        return !empty($accepted['ok']) ? 'accepted_incoming' : 'request_pending';
    }

    q(
        "INSERT INTO user_friends (user_id, friend_id, status, requested_by_user_id, requested_at, accepted_at)
         VALUES (?, ?, 'pending', ?, NOW(), NULL)",
        [$requesterId, $recipientId, $requesterId]
    );

    return 'request_sent';
}

function friendship_status_label(?array $friendship, int $viewerId): string
{
    if (!$friendship) {
        return '';
    }

    if ((string)$friendship['status'] === FRIENDSHIP_STATUS_ACCEPTED) {
        return 'Already friends.';
    }

    if ((int)$friendship['requested_by_user_id'] === $viewerId) {
        return 'Friend request sent.';
    }

    return 'This player already sent you a friend request.';
}
