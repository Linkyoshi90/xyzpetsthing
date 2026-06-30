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
if ($activeFriendId) {
    // Opening a conversation clears its unread notification.
    mark_conversation_read($uid, (int)$activeFriendId);
}
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
                <?php $view = chat_message_view($msg, (int)$uid); ?>
                <?php if (($view['type'] ?? '') === 'gift'): ?>
                <?= gift_chat_card_html((int)$view['id'], (string)$view['direction'], $view['gift'], (string)$view['timestamp']) ?>
                <?php else: ?>
                <article class="chat-message <?= htmlspecialchars($view['direction']) ?>" data-message-id="<?= (int)$view['id'] ?>">
                    <p class="chat-message-body"><?= $view['body'] ?></p>
                    <span class="chat-message-time"><?= htmlspecialchars($view['timestamp']) ?></span>
                </article>
                <?php endif; ?>
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
                <button class="btn btn-ghost" type="button" id="chat-gift-btn" data-gift-open>Gift</button>
                <button class="btn" type="submit">Send</button>
            </div>
        </form>

        <div class="gift-modal" id="gift-modal" hidden aria-hidden="true">
          <div class="gift-modal__backdrop" data-gift-close></div>
          <div class="gift-modal__dialog card glass" role="dialog" aria-modal="true" aria-labelledby="gift-modal-title">
            <button type="button" class="gift-modal__close" data-gift-close aria-label="Close">&times;</button>
            <h2 id="gift-modal-title">Send a gift</h2>
            <p class="gift-modal__lead muted">Choose a tradable item to send to <strong data-gift-friend-name><?= htmlspecialchars($activeFriend['username']) ?></strong>.</p>
            <p class="gift-modal__status" data-gift-status hidden></p>
            <div class="gift-modal__list" data-gift-list>
              <p class="muted">Loading your bag…</p>
            </div>
            <div class="gift-modal__actions">
              <button type="button" class="btn btn-ghost" data-gift-close>Cancel</button>
              <button type="button" class="btn" data-gift-send disabled>Send gift</button>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div class="chat-placeholder">
            <p>Select a friend from the list to start chatting.</p>
        </div>
        <?php endif; ?>
    </section>
</div>
<script defer src="assets/js/chat.js"></script>
