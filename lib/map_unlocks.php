<?php
require_once __DIR__.'/../db.php';

const MAP_UNLOCK_TABLE = 'user_map_unlocks';

function ensure_map_unlock_table(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    q(
        'CREATE TABLE IF NOT EXISTS '.MAP_UNLOCK_TABLE.' ('
        . ' user_id BIGINT UNSIGNED NOT NULL,'
        . ' map_key VARCHAR(80) NOT NULL,'
        . ' unlocked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,'
        . ' PRIMARY KEY (user_id, map_key),'
        . ' KEY ix_map_unlock_map_key (map_key),'
        . ' CONSTRAINT fk_map_unlock_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $ready = true;
}

function has_map_unlock(int $userId, string $mapKey): bool
{
    if ($userId <= 0 || $mapKey === '') {
        return false;
    }
    ensure_map_unlock_table();
    $found = q(
        'SELECT 1 FROM '.MAP_UNLOCK_TABLE.' WHERE user_id = ? AND map_key = ? LIMIT 1',
        [$userId, $mapKey]
    )->fetchColumn();
    return (bool)$found;
}

function grant_map_unlock(int $userId, string $mapKey): bool
{
    if ($userId <= 0 || $mapKey === '') {
        return false;
    }
    ensure_map_unlock_table();
    q(
        'INSERT IGNORE INTO '.MAP_UNLOCK_TABLE.' (user_id, map_key) VALUES (?, ?)',
        [$userId, $mapKey]
    );
    return has_map_unlock($userId, $mapKey);
}
