<?php
require_once __DIR__.'/../db.php';

const MAP_UNLOCK_TABLE = 'user_map_unlocks';

function has_map_unlock(int $userId, string $mapKey): bool
{
    if ($userId <= 0 || $mapKey === '') {
        return false;
    }
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
    q(
        'INSERT IGNORE INTO '.MAP_UNLOCK_TABLE.' (user_id, map_key) VALUES (?, ?)',
        [$userId, $mapKey]
    );
    return has_map_unlock($userId, $mapKey);
}
