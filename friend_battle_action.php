<?php
require_once __DIR__.'/auth.php';
require_login();
require_once __DIR__.'/lib/input.php';
require_once __DIR__.'/lib/friendships.php';

const FRIEND_BATTLE_CLASS_NAME = 'Harmontide User';
const FRIEND_BATTLE_TEAM_LIMIT = 4;

function friend_battle_return_with_notice(string $message): void
{
    $_SESSION['friend_action_notice'] = $message;
    header('Location: index.php?pg=friends');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

$csrfToken = input_string($_POST['csrf_token'] ?? '', 128);
$sessionToken = (string)($_SESSION['friend_action_csrf'] ?? '');
if ($csrfToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    friend_battle_return_with_notice('The battle request expired. Please try again.');
}

$uid = (int)(current_user()['id'] ?? 0);
$friendId = input_int($_POST['friend_id'] ?? 0, 1);

if ($uid <= 0 || $friendId <= 0 || !friendship_users_are_friends($uid, $friendId)) {
    friend_battle_return_with_notice('You can only battle users on your friends list.');
}

$friend = q(
    'SELECT username FROM users WHERE user_id = ? LIMIT 1',
    [$friendId]
)->fetch(PDO::FETCH_ASSOC);

if (!$friend) {
    friend_battle_return_with_notice('That friend could not be found.');
}

$username = (string)$friend['username'];
$pdo = db();
if (!$pdo instanceof PDO) {
    friend_battle_return_with_notice('The battle roster could not be prepared. Please try again.');
}

try {
    $pdo->beginTransaction();

    $trainerStatement = $pdo->prepare(
        'SELECT trainer_id
           FROM trainers
          WHERE class_name = ?
            AND trainer_name = ?
          ORDER BY trainer_id
          LIMIT 1
          FOR UPDATE'
    );
    $trainerStatement->execute([FRIEND_BATTLE_CLASS_NAME, $username]);
    $trainerId = (int)$trainerStatement->fetchColumn();

    if ($trainerId <= 0) {
        $createTrainer = $pdo->prepare(
            'INSERT INTO trainers
                (class_name, trainer_name, encounter_line, defeat_line, defeat_currency)
             VALUES (?, ?, ?, ?, ?)',
        );
        $createTrainer->execute([
            FRIEND_BATTLE_CLASS_NAME,
            $username,
            $username.' challenges you to a friendly battle!',
            'Good match! '.$username.' will be ready for a rematch.',
            0,
        ]);
        $trainerId = (int)$pdo->lastInsertId();
    }

    $petStatement = $pdo->prepare(
        'SELECT pi.pet_instance_id
           FROM pet_instances pi
           LEFT JOIN abandoned_pets ap ON ap.creature_id = pi.pet_instance_id
          WHERE pi.owner_user_id = ?
            AND COALESCE(pi.inactive, 0) = 0
            AND ap.creature_id IS NULL
          ORDER BY pi.pet_instance_id
          LIMIT '.FRIEND_BATTLE_TEAM_LIMIT
    );
    $petStatement->execute([$friendId]);
    $petIds = $petStatement->fetchAll(PDO::FETCH_COLUMN);

    // The roster is a current mirror. Pet stats remain live through the battle
    // query's join to pet_instances, so only membership and order are synced here.
    $clearRoster = $pdo->prepare('DELETE FROM trainer_roster WHERE trainer_id = ?');
    $clearRoster->execute([$trainerId]);
    $addRosterMember = $pdo->prepare(
        'INSERT INTO trainer_roster (trainer_id, pet_instance_id, roster_position)
         VALUES (?, ?, ?)'
    );
    foreach ($petIds as $index => $petId) {
        $addRosterMember->execute([$trainerId, (int)$petId, $index + 1]);
    }

    $pdo->commit();
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Friend battle synchronization failed: '.$error->getMessage());
    friend_battle_return_with_notice('The battle roster could not be prepared. Please try again.');
}

if (!$petIds) {
    friend_battle_return_with_notice($username.' has no battle-ready pets.');
}

$battleUrl = 'index.php?'.http_build_query([
    'pg' => 'battle_minigame',
    'trainer_id' => $trainerId,
    'return_to' => 'index.php?pg=friends',
]);

header('Location: '.$battleUrl);
exit;
