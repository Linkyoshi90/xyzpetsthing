<?php
require_login();
require_once __DIR__.'/../lib/chat.php';
require_once __DIR__.'/../lib/input.php';

$user = current_user();
$uid = $user['id'];

$friends = get_user_friend_list($uid);
$activeFriendId = input_int($_GET['friend'] ?? 0, 1);
if ($activeFriendId === 0) {
    $activeFriendId = null;
}
if ($activeFriendId && !isset($friends[$activeFriendId])) {
    $activeFriendId = null;
}
if (!$activeFriendId && $friends) {
    $keys = array_keys($friends);
    $activeFriendId = reset($keys);
}
$activeFriend = $activeFriendId ? $friends[$activeFriendId] : null;
$messages = $activeFriendId ? get_conversation($uid, $activeFriendId, 200) : [];
$chatActionUrl = $GLOBALS['app_chat_action_path'] ?? 'user_chat_action.php';
?>
<h1>Direct Messages</h1>
<div class="chat-window card">
    <aside class="chat-sidebar">
        <details class="chat-friend-toggle" open>
            <summary>Your Friends</summary>
            <?php if ($friends): ?>
            <ul class="chat-friend-list">
                <?php foreach ($friends as $friend): ?>
                <li>
                    <button
                        type="button"
                        class="chat-friend-btn<?= ($friend['id'] === $activeFriendId) ? ' active' : '' ?>"
                        data-friend-id="<?= (int)$friend['id'] ?>"
                        data-friend-name="<?= htmlspecialchars($friend['username']) ?>"
                    >
                        <?= htmlspecialchars($friend['username']) ?>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="chat-empty">You have no friends yet. Add some to start chatting!</p>
            <?php endif; ?>
        </details>
    </aside>
    <section class="chat-panel" data-active-friend="<?= $activeFriendId ? (int)$activeFriendId : '' ?>">
        <?php if ($activeFriendId): ?>
        <header class="chat-header">
            <h2>Chatting with <?= htmlspecialchars($activeFriend['username']) ?></h2>
        </header>
        <p class="chat-error" role="alert" hidden></p>
        <div class="chat-history" id="chat-history" aria-live="polite" data-empty="No messages yet. Say hello!">
            <?php if ($messages): ?>
                <?php foreach ($messages as $msg): ?>
                <article class="chat-message <?= htmlspecialchars($msg['direction']) ?>" data-message-id="<?= (int)$msg['id'] ?>">
                    <p class="chat-message-body"><?= nl2br(htmlspecialchars($msg['body'])) ?></p>
                    <span class="chat-message-time"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($msg['created_at']))) ?></span>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
            <p class="chat-history-empty">No messages yet. Say hello!</p>
            <?php endif; ?>
        </div>
        <form class="chat-form" id="chat-form" method="post" action="<?= htmlspecialchars($chatActionUrl) ?>" autocomplete="off">
            <input type="hidden" name="action" value="send">
            <input type="hidden" name="friend_id" value="<?= (int)$activeFriendId ?>" id="chat-friend-id">
            <label for="chat-input" class="sr-only">Type your message</label>
            <textarea id="chat-input" name="message" rows="2" placeholder="Type your message" required></textarea>
            <div class="chat-actions">
                <button class="btn" type="submit">Send</button>
            </div>
        </form>
        <?php else: ?>
        <div class="chat-placeholder">
            <p>Select a friend from the list to start chatting.</p>
        </div>
        <?php endif; ?>
    </section>
</div>
<script defer src="assets/js/chat.js"></script>
