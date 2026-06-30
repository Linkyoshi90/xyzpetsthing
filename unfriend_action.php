<?php
require_once __DIR__.'/auth.php';
require_login();
require_once __DIR__.'/lib/input.php';

function unfriend_return_with_notice(string $message): void
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
    unfriend_return_with_notice('The unfriend request expired. Please try again.');
}
if ((string)($_POST['confirmed'] ?? '') !== '1') {
    unfriend_return_with_notice('Please confirm before removing a friend.');
}

$uid = (int)(current_user()['id'] ?? 0);
$friendId = input_int($_POST['friend_id'] ?? 0, 1);
if ($uid <= 0 || $friendId <= 0 || $uid === $friendId) {
    unfriend_return_with_notice('That friendship could not be removed.');
}

$pdo = db();
if (!$pdo instanceof PDO) {
    unfriend_return_with_notice('That friendship could not be removed. Please try again.');
}

try {
    $friendStatement = $pdo->prepare('SELECT username FROM users WHERE user_id = ? LIMIT 1');
    $friendStatement->execute([$friendId]);
    $friendName = (string)($friendStatement->fetchColumn() ?: 'That user');

    $deleteStatement = $pdo->prepare(
        "DELETE FROM user_friends
          WHERE status = 'accepted'
            AND ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?))"
    );
    $deleteStatement->execute([$uid, $friendId, $friendId, $uid]);
} catch (Throwable $error) {
    error_log('Unfriend action failed: '.$error->getMessage());
    unfriend_return_with_notice('That friendship could not be removed. Please try again.');
}

if ($deleteStatement->rowCount() < 1) {
    unfriend_return_with_notice('That friendship was already removed.');
}

unfriend_return_with_notice('You and '.$friendName.' are no longer friends.');
