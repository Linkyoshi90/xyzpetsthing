<?php
require_once __DIR__.'/../db.php';

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
    $sql = "SELECT CASE WHEN uf.user_id = :uid THEN uf.friend_id ELSE uf.user_id END AS friend_id,
                   u.username
              FROM user_friends uf
              JOIN users u ON u.user_id = CASE WHEN uf.user_id = :uid THEN uf.friend_id ELSE uf.user_id END
             WHERE uf.user_id = :uid OR uf.friend_id = :uid
          ORDER BY u.username";
    $st = db()->prepare($sql);
    $st->execute([':uid' => $userId]);
    $friends = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $friends[(int)$row['friend_id']] = [
            'id' => (int)$row['friend_id'],
            'username' => $row['username'],
        ];
    }
    return $friends;
}

function users_are_friends(int $userId, int $friendId): bool
{
    $sql = "SELECT 1 FROM user_friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?) LIMIT 1";
    $st = q($sql, [$userId, $friendId, $friendId, $userId]);
    return (bool)$st->fetchColumn();
}

function get_conversation(int $userId, int $friendId, int $limit = 100): array
{
    $sql = "SELECT message_id, sender_id, recipient_id, message_ciphertext, created_at
              FROM user_direct_messages
             WHERE (sender_id = :user AND recipient_id = :friend)
                OR (sender_id = :friend AND recipient_id = :user)
          ORDER BY created_at ASC, message_id ASC
             LIMIT :limit";
    $st = db()->prepare($sql);
    $st->bindValue(':user', $userId, PDO::PARAM_INT);
    $st->bindValue(':friend', $friendId, PDO::PARAM_INT);
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