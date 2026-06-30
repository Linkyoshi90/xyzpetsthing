<?php
require_once __DIR__.'/../db.php';
require_once __DIR__.'/friendships.php';
require_once __DIR__.'/gifts.php';

const CHAT_CIPHER_METHOD = 'aes-256-gcm';
const CHAT_SEPARATOR = ':';

function chat_encryption_key(): string
{
    $key = CHAT_ENCRYPTION_KEY ?? '';
    if ($key === '') {
        throw new RuntimeException('Chat encryption key is not configured.');
    }
    return hash('sha256', $key, true);
}

function encrypt_chat_message(string $message): string
{
    $cipher = CHAT_CIPHER_METHOD;
    $key = chat_encryption_key();
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = random_bytes($ivLength);
    $tag = '';
    $ciphertext = openssl_encrypt($message, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($ciphertext === false) {
        throw new RuntimeException('Unable to encrypt chat message.');
    }
    return base64_encode($iv) . CHAT_SEPARATOR . base64_encode($tag) . CHAT_SEPARATOR . base64_encode($ciphertext);
}

function decrypt_chat_message(string $payload): ?string
{
    $parts = explode(CHAT_SEPARATOR, $payload);
    if (count($parts) !== 3) {
        return null;
    }
    [$ivB64, $tagB64, $cipherB64] = $parts;
    $iv = base64_decode($ivB64, true);
    $tag = base64_decode($tagB64, true);
    $ciphertext = base64_decode($cipherB64, true);
    if ($iv === false || $tag === false || $ciphertext === false) {
        return null;
    }
    $plaintext = openssl_decrypt($ciphertext, CHAT_CIPHER_METHOD, chat_encryption_key(), OPENSSL_RAW_DATA, $iv, $tag);
    return $plaintext === false ? null : $plaintext;
}

function get_user_friend_list(int $userId): array
{
    return friendship_get_friend_list($userId);
}

function users_are_friends(int $userId, int $friendId): bool
{
    return friendship_users_are_friends($userId, $friendId);
}

function get_conversation(int $userId, int $friendId, int $limit = 100, int $afterId = 0): array
{
    $afterClause = $afterId > 0 ? 'AND message_id > :after ' : '';
    $sql = "SELECT message_id, sender_id, recipient_id, message_ciphertext, created_at
              FROM user_direct_messages
             WHERE ((sender_id = :user AND recipient_id = :friend)
                 OR (sender_id = :friend AND recipient_id = :user))
               {$afterClause}
          ORDER BY created_at ASC, message_id ASC
             LIMIT :limit";
    $st = db()->prepare($sql);
    $st->bindValue(':user', $userId, PDO::PARAM_INT);
    $st->bindValue(':friend', $friendId, PDO::PARAM_INT);
    if ($afterId > 0) {
        $st->bindValue(':after', $afterId, PDO::PARAM_INT);
    }
    $st->bindValue(':limit', $limit, PDO::PARAM_INT);
    $st->execute();
    $messages = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $body = decrypt_chat_message($row['message_ciphertext']);
        if ($body === null) {
            continue;
        }
        $messages[] = [
            'id' => (int)$row['message_id'],
            'sender_id' => (int)$row['sender_id'],
            'recipient_id' => (int)$row['recipient_id'],
            'body' => $body,
            'created_at' => $row['created_at'],
            'direction' => ((int)$row['sender_id'] === $userId) ? 'outgoing' : 'incoming',
        ];
    }
    return $messages;
}

// Normalize a raw conversation row (from get_conversation/save_chat_message) into
// the shape the UI renders. Gift markers become a structured 'gift' payload; plain
// messages get an HTML-escaped body. Shared by the page's first paint and the JSON
// action endpoint so both render identically.
function chat_message_view(array $msg, int $viewerId): array
{
    $decorated = gift_decorate_message($msg, $viewerId);
    $view = [
        'id' => (int)($msg['id'] ?? 0),
        'direction' => (string)($msg['direction'] ?? 'incoming'),
        'timestamp' => date('M j, Y g:i A', strtotime((string)($msg['created_at'] ?? 'now'))),
    ];
    if (($decorated['type'] ?? '') === 'gift') {
        $view['type'] = 'gift';
        $view['gift'] = $decorated['gift'];
    } else {
        $view['type'] = 'text';
        $view['body'] = nl2br(htmlspecialchars((string)($decorated['body'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }
    return $view;
}

function save_chat_message(int $senderId, int $recipientId, string $message): array
{
    $ciphertext = encrypt_chat_message($message);
    $sql = "INSERT INTO user_direct_messages (sender_id, recipient_id, message_ciphertext) VALUES (?, ?, ?)";
    q($sql, [$senderId, $recipientId, $ciphertext]);
    $messageId = (int)db()->lastInsertId();
    $createdAt = q("SELECT created_at FROM user_direct_messages WHERE message_id = ?", [$messageId])->fetchColumn();
    return [
        'id' => $messageId,
        'sender_id' => $senderId,
        'recipient_id' => $recipientId,
        'body' => $message,
        'created_at' => $createdAt ?: date('Y-m-d H:i:s'),
        'direction' => 'outgoing',
    ];
}

// Per-conversation read state lives in its own table so the messages table stays
// untouched. The live DB user only has DML rights (no CREATE/ALTER), so we just
// detect the table via INFORMATION_SCHEMA — a SELECT — and degrade quietly if it
// is missing. The table ships in sql/user_chat_reads.sql; create it once by hand.
function chat_ensure_reads_schema(): bool
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
            AND TABLE_NAME = 'user_chat_reads'
          LIMIT 1"
    )->fetchColumn();
    $ready = (bool) $exists;
    return $ready;
}

// Marks every message $friendId has sent to $userId as read, by remembering the
// newest message id in that direction.
function mark_conversation_read(int $userId, int $friendId): void
{
    if ($userId <= 0 || $friendId <= 0 || !chat_ensure_reads_schema()) {
        return;
    }
    $latest = (int) q(
        "SELECT COALESCE(MAX(message_id), 0)
           FROM user_direct_messages
          WHERE sender_id = ? AND recipient_id = ?",
        [$friendId, $userId]
    )->fetchColumn();
    if ($latest <= 0) {
        return;
    }
    q(
        "INSERT INTO user_chat_reads (user_id, friend_id, last_read_message_id)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE last_read_message_id = GREATEST(last_read_message_id, VALUES(last_read_message_id))",
        [$userId, $friendId, $latest]
    );
}

// Number of distinct friends who have unread messages waiting for $userId.
// Used for the header badge (one notification card per sender).
function chat_unread_sender_count(int $userId): int
{
    if ($userId <= 0 || !chat_ensure_reads_schema()) {
        return 0;
    }
    return (int) q(
        "SELECT COUNT(DISTINCT m.sender_id)
           FROM user_direct_messages m
           LEFT JOIN user_chat_reads r
             ON r.user_id = m.recipient_id AND r.friend_id = m.sender_id
          WHERE m.recipient_id = ?
            AND m.message_id > COALESCE(r.last_read_message_id, 0)",
        [$userId]
    )->fetchColumn();
}

// One notification per friend with unread messages, each carrying an "Open chat"
// link straight to that conversation.
function chat_message_notifications(int $userId): array
{
    if ($userId <= 0 || !chat_ensure_reads_schema()) {
        return [];
    }
    $rows = q(
        "SELECT m.sender_id,
                u.username,
                COUNT(*)            AS unread_count,
                MAX(m.created_at)   AS latest_at
           FROM user_direct_messages m
           LEFT JOIN user_chat_reads r
             ON r.user_id = m.recipient_id AND r.friend_id = m.sender_id
           JOIN users u ON u.user_id = m.sender_id
          WHERE m.recipient_id = ?
            AND m.message_id > COALESCE(r.last_read_message_id, 0)
          GROUP BY m.sender_id, u.username
          ORDER BY latest_at DESC",
        [$userId]
    )->fetchAll(PDO::FETCH_ASSOC);

    $notifications = [];
    foreach ($rows as $row) {
        $senderId = (int) $row['sender_id'];
        $username = (string) ($row['username'] ?? 'Someone');
        $unread = (int) $row['unread_count'];
        $latestAt = strtotime((string) ($row['latest_at'] ?? ''));
        $notifications[] = [
            'id' => 'chat-message-'.$senderId,
            'type' => 'chat_message',
            'variant' => 'chat_message',
            'title' => 'Message from '.$username,
            'message' => $unread > 1 ? ($unread.' new messages.') : 'New message.',
            'time_label' => $latestAt ? date('H:i', $latestAt) : '',
            'dismissible' => false,
            'actions' => [
                [
                    'label' => 'Open chat',
                    'url' => 'index.php?pg=user-chat&friend='.$senderId,
                ],
            ],
        ];
    }
    return $notifications;
}
