<?php

declare(strict_types=1);

require_once __DIR__.'/../db.php';
require_once __DIR__.'/friendships.php';

const GIFT_STATE_PENDING  = 0;
const GIFT_STATE_ACCEPTED = 1;
const GIFT_STATE_REJECTED = 2;
const GIFT_NOTIFICATION_LIMIT = 20;

/**
 * Make sure the item_send table exists. On the live host the DB user is DML-only
 * (no CREATE), so the table is applied by hand from sql/item_send.sql and the
 * CREATE below is a harmless no-op there (q() swallows the privilege error). On a
 * local dev box this bootstraps the table automatically. Mirrors the
 * friendship_ensure_schema() pattern.
 */
function gift_ensure_schema(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    if (!db()) {
        $ready = false;
        return false;
    }

    $exists = q(
        "SELECT 1
           FROM INFORMATION_SCHEMA.TABLES
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'item_send'
          LIMIT 1"
    )->fetchColumn();

    if (!$exists) {
        q(
            "CREATE TABLE IF NOT EXISTS item_send (
                gift_id     BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
                sender_id   BIGINT UNSIGNED  NOT NULL,
                receiver_id BIGINT UNSIGNED  NOT NULL,
                item_id     BIGINT UNSIGNED  NOT NULL,
                received    TINYINT UNSIGNED NOT NULL DEFAULT 0,
                created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                resolved_at TIMESTAMP        NULL     DEFAULT NULL,
                PRIMARY KEY (gift_id),
                KEY ix_gift_receiver_state (receiver_id, received),
                KEY ix_gift_sender (sender_id),
                KEY fk_gift_item (item_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci"
        );
    }

    $ready = true;
    return true;
}

/**
 * Tradable items the sender currently owns (quantity > 0), for the gift picker.
 */
function gift_sender_inventory(int $senderId): array
{
    if ($senderId <= 0 || !gift_ensure_schema()) {
        return [];
    }

    return q(
        "SELECT i.item_id,
                i.item_name,
                ui.quantity,
                ic.category_name
           FROM user_inventory ui
           JOIN items i ON i.item_id = ui.item_id
           LEFT JOIN item_categories ic ON ic.category_id = i.category_id
          WHERE ui.user_id = ?
            AND ui.quantity > 0
            AND i.tradable = 1
          ORDER BY i.item_name ASC",
        [$senderId]
    )->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Send one of $itemId from $senderId to $receiverId. Escrow model: the item is
 * removed from the sender's bag now and a pending gift row is created, both inside
 * one transaction so the item can never be lost or duplicated.
 *
 * @return array{ok:bool, error?:string, item_name?:string, receiver_name?:string}
 */
function gift_send(int $senderId, int $receiverId, int $itemId): array
{
    if ($senderId <= 0 || $receiverId <= 0 || $itemId <= 0) {
        return ['ok' => false, 'error' => 'Invalid gift request.'];
    }
    if ($senderId === $receiverId) {
        return ['ok' => false, 'error' => 'You cannot gift yourself.'];
    }
    if (!gift_ensure_schema()) {
        return ['ok' => false, 'error' => 'Gifts are unavailable right now.'];
    }
    if (!friendship_users_are_friends($senderId, $receiverId)) {
        return ['ok' => false, 'error' => 'You can only gift your friends.'];
    }

    $item = q(
        "SELECT item_name, tradable FROM items WHERE item_id = ? LIMIT 1",
        [$itemId]
    )->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        return ['ok' => false, 'error' => 'That item does not exist.'];
    }
    if ((int)$item['tradable'] !== 1) {
        return ['ok' => false, 'error' => 'That item cannot be gifted.'];
    }

    $receiver = q(
        "SELECT username FROM users WHERE user_id = ? LIMIT 1",
        [$receiverId]
    )->fetch(PDO::FETCH_ASSOC);
    if (!$receiver) {
        return ['ok' => false, 'error' => 'That player no longer exists.'];
    }

    $pdo = db();
    if (!$pdo) {
        return ['ok' => false, 'error' => 'Gifts are unavailable right now.'];
    }

    try {
        $pdo->beginTransaction();

        // Remove one from the sender's stack, but only if they actually have it.
        $dec = $pdo->prepare(
            "UPDATE user_inventory
                SET quantity = quantity - 1
              WHERE user_id = ? AND item_id = ? AND quantity >= 1"
        );
        $dec->execute([$senderId, $itemId]);
        if ($dec->rowCount() !== 1) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'You do not have that item anymore.'];
        }

        // Keep the bag tidy: drop the row once a stack hits zero.
        $pdo->prepare(
            "DELETE FROM user_inventory WHERE user_id = ? AND item_id = ? AND quantity = 0"
        )->execute([$senderId, $itemId]);

        $pdo->prepare(
            "INSERT INTO item_send (sender_id, receiver_id, item_id, received)
             VALUES (?, ?, ?, ".GIFT_STATE_PENDING.")"
        )->execute([$senderId, $receiverId, $itemId]);
        $giftId = (int)$pdo->lastInsertId();

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        app_add_error('Gift send failed: '.$e->getMessage());
        return ['ok' => false, 'error' => 'The gift could not be sent.'];
    }

    return [
        'ok' => true,
        'gift_id' => $giftId,
        'item_name' => (string)$item['item_name'],
        'receiver_name' => (string)$receiver['username'],
    ];
}

// ---------------------------------------------------------------------------
// Chat integration
//
// A gift shows up inside a direct-message thread as a normal (encrypted) message
// whose body is a marker pointing at the escrow row. The marker is wrapped in STX
// control bytes (0x02): input_string() strips control chars from anything a user
// types, so a gift card can only ever come from our own save path — a player can't
// forge one by typing the marker. gift_chat_payload() additionally checks that the
// escrow row's parties match the message's parties as defence in depth.
// ---------------------------------------------------------------------------

function gift_message_marker(int $giftId): string
{
    return "\x02gift:".$giftId."\x02";
}

function gift_message_parse(string $body): ?int
{
    if (preg_match('/^\x02gift:(\d+)\x02$/', $body, $m)) {
        return (int)$m[1];
    }
    return null;
}

/**
 * Build the view payload for a gift card, or null if the marker does not resolve
 * to a real gift exchanged between these two message participants.
 *
 * @return array{gift_id:int,item_name:string,sender_name:string,state:int,can_act:bool,status_label:string}|null
 */
function gift_chat_payload(int $giftId, int $messageSenderId, int $messageRecipientId, int $viewerId): ?array
{
    if ($giftId <= 0 || !gift_ensure_schema()) {
        return null;
    }

    $row = q(
        "SELECT g.sender_id, g.receiver_id, g.received,
                i.item_name, s.username AS sender_name
           FROM item_send g
           JOIN items i ON i.item_id = g.item_id
           JOIN users s ON s.user_id = g.sender_id
          WHERE g.gift_id = ?
          LIMIT 1",
        [$giftId]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    $senderId = (int)$row['sender_id'];
    $receiverId = (int)$row['receiver_id'];
    // Anti-forgery: the marker must describe a gift actually sent between the two
    // people in this conversation.
    if ($senderId !== $messageSenderId || $receiverId !== $messageRecipientId) {
        return null;
    }

    $state = (int)$row['received'];
    $canAct = $viewerId === $receiverId && $state === GIFT_STATE_PENDING;

    if ($state === GIFT_STATE_ACCEPTED) {
        $label = 'Accepted ✓';
    } elseif ($state === GIFT_STATE_REJECTED) {
        $label = 'Declined ✗';
    } elseif ($viewerId === $receiverId) {
        $label = 'Pending — your move';
    } else {
        $label = 'Waiting for a response…';
    }

    return [
        'gift_id' => $giftId,
        'item_name' => (string)$row['item_name'],
        'sender_name' => (string)$row['sender_name'],
        'state' => $state,
        'can_act' => $canAct,
        'status_label' => $label,
    ];
}

/**
 * If $msg is a gift marker, attach a 'type' => 'gift' and 'gift' payload to it.
 * Otherwise returns the message unchanged. $msg needs id/sender_id/recipient_id/body.
 */
function gift_decorate_message(array $msg, int $viewerId): array
{
    $giftId = gift_message_parse((string)($msg['body'] ?? ''));
    if ($giftId === null) {
        return $msg;
    }
    $payload = gift_chat_payload(
        $giftId,
        (int)($msg['sender_id'] ?? 0),
        (int)($msg['recipient_id'] ?? 0),
        $viewerId
    );
    if ($payload === null) {
        // Marker present but unresolved (e.g. row deleted) — don't leak control bytes.
        $msg['body'] = '🎁 Gift';
        return $msg;
    }
    $msg['type'] = 'gift';
    $msg['gift'] = $payload;
    return $msg;
}

/**
 * Server-side HTML for a gift card, kept in lockstep with the JS builder in
 * assets/js/chat.js so first paint and polled messages look identical.
 */
function gift_chat_card_html(int $messageId, string $direction, array $gift, string $timestamp): string
{
    $classes = 'chat-message '.($direction === 'outgoing' ? 'outgoing' : 'incoming').' chat-gift';
    $actions = '';
    if (!empty($gift['can_act'])) {
        $actions = '<div class="chat-gift__actions">'
            .'<button type="button" class="btn" data-gift-card-action="accept_gift">Accept</button>'
            .'<button type="button" class="btn btn-ghost" data-gift-card-action="decline_gift">Decline</button>'
            .'</div>';
    }

    return '<article class="'.htmlspecialchars($classes, ENT_QUOTES, 'UTF-8').'"'
        .' data-message-id="'.(int)$messageId.'"'
        .' data-gift-id="'.(int)$gift['gift_id'].'"'
        .' data-gift-state="'.(int)$gift['state'].'">'
        .'<div class="chat-gift__card">'
        .'<span class="chat-gift__icon" aria-hidden="true">🎁</span>'
        .'<div class="chat-gift__info">'
        .'<strong class="chat-gift__title">'.htmlspecialchars((string)$gift['item_name'], ENT_QUOTES, 'UTF-8').'</strong>'
        .'<span class="chat-gift__status">'.htmlspecialchars((string)$gift['status_label'], ENT_QUOTES, 'UTF-8').'</span>'
        .'</div>'
        .$actions
        .'</div>'
        .'<span class="chat-message-time">'.htmlspecialchars($timestamp, ENT_QUOTES, 'UTF-8').'</span>'
        .'</article>';
}

/**
 * Settle a pending gift: state 1 delivers to the receiver, state 2 refunds the
 * sender. The gift row is claimed first with a conditional UPDATE so two clicks
 * (or a race) can only resolve it once.
 */
function gift_resolve(int $giftId, int $userId, int $newState): array
{
    if ($giftId <= 0 || $userId <= 0 || !gift_ensure_schema()) {
        return ['ok' => false, 'error' => 'Invalid gift.'];
    }
    if ($newState !== GIFT_STATE_ACCEPTED && $newState !== GIFT_STATE_REJECTED) {
        return ['ok' => false, 'error' => 'Invalid gift action.'];
    }

    $gift = q(
        "SELECT g.gift_id, g.sender_id, g.receiver_id, g.item_id, g.received,
                i.item_name, s.username AS sender_name
           FROM item_send g
           JOIN items i ON i.item_id = g.item_id
           JOIN users s ON s.user_id = g.sender_id
          WHERE g.gift_id = ?
          LIMIT 1",
        [$giftId]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$gift) {
        return ['ok' => false, 'error' => 'Gift not found.'];
    }
    if ((int)$gift['receiver_id'] !== $userId) {
        return ['ok' => false, 'error' => 'This gift is not addressed to you.'];
    }
    if ((int)$gift['received'] !== GIFT_STATE_PENDING) {
        return ['ok' => false, 'error' => 'This gift has already been handled.'];
    }

    // Accepted -> the receiver keeps it; rejected -> refund the original sender.
    $beneficiary = $newState === GIFT_STATE_ACCEPTED
        ? (int)$gift['receiver_id']
        : (int)$gift['sender_id'];

    $pdo = db();
    if (!$pdo) {
        return ['ok' => false, 'error' => 'Gifts are unavailable right now.'];
    }

    try {
        $pdo->beginTransaction();

        // Claim the gift; bail if someone already resolved it.
        $claim = $pdo->prepare(
            "UPDATE item_send
                SET received = ?, resolved_at = NOW()
              WHERE gift_id = ? AND received = ".GIFT_STATE_PENDING
        );
        $claim->execute([$newState, $giftId]);
        if ($claim->rowCount() !== 1) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'This gift has already been handled.'];
        }

        $pdo->prepare(
            "INSERT INTO user_inventory (user_id, item_id, quantity)
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE quantity = quantity + 1"
        )->execute([$beneficiary, (int)$gift['item_id']]);

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        app_add_error('Gift resolve failed: '.$e->getMessage());
        return ['ok' => false, 'error' => 'The gift could not be processed.'];
    }

    return [
        'ok' => true,
        'item_name' => (string)$gift['item_name'],
        'sender_name' => (string)$gift['sender_name'],
        'accepted' => $newState === GIFT_STATE_ACCEPTED,
    ];
}

function gift_accept(int $giftId, int $userId): array
{
    return gift_resolve($giftId, $userId, GIFT_STATE_ACCEPTED);
}

function gift_decline(int $giftId, int $userId): array
{
    return gift_resolve($giftId, $userId, GIFT_STATE_REJECTED);
}

function gift_pending_count(int $userId): int
{
    if ($userId <= 0 || !gift_ensure_schema()) {
        return 0;
    }

    return (int)q(
        "SELECT COUNT(*)
           FROM item_send
          WHERE receiver_id = ? AND received = ".GIFT_STATE_PENDING,
        [$userId]
    )->fetchColumn();
}

/**
 * One notification per pending incoming gift, each carrying Accept / Decline
 * buttons. Same shape the notification panel already renders for friend requests.
 */
function gift_notifications(int $userId, int $limit = GIFT_NOTIFICATION_LIMIT): array
{
    if ($userId <= 0 || !gift_ensure_schema()) {
        return [];
    }

    $limit = max(1, min(100, $limit));
    $st = db()->prepare(
        "SELECT g.gift_id,
                g.created_at,
                i.item_name,
                s.username AS sender_name
           FROM item_send g
           JOIN items i ON i.item_id = g.item_id
           JOIN users s ON s.user_id = g.sender_id
          WHERE g.receiver_id = :uid
            AND g.received = ".GIFT_STATE_PENDING."
          ORDER BY g.created_at DESC, g.gift_id DESC
          LIMIT :limit"
    );
    $st->bindValue(':uid', $userId, PDO::PARAM_INT);
    $st->bindValue(':limit', $limit, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    $notifications = [];
    foreach ($rows as $row) {
        $giftId = (int)$row['gift_id'];
        $sender = (string)($row['sender_name'] ?? 'Someone');
        $itemName = (string)($row['item_name'] ?? 'a gift');
        $createdAt = strtotime((string)($row['created_at'] ?? ''));
        $notifications[] = [
            'id' => 'gift-'.$giftId,
            'type' => 'gift',
            'variant' => 'gift',
            'title' => 'Gift from '.$sender,
            'message' => $sender.' sent a '.$itemName.' to you.',
            'time_label' => $createdAt ? date('H:i', $createdAt) : '',
            'dismissible' => false,
            'actions' => [
                [
                    'label' => 'Accept',
                    'post_action' => 'accept_gift',
                    'gift_id' => $giftId,
                ],
                [
                    'label' => 'Decline',
                    'post_action' => 'decline_gift',
                    'gift_id' => $giftId,
                ],
            ],
        ];
    }

    return $notifications;
}
