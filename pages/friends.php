<?php
require_login();
require_once __DIR__.'/../db.php';
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/input.php';
require_once __DIR__.'/../lib/friendships.php';

$uid = current_user()['id'];

$searchedUser = null;
$searchName = input_string($_POST['username'] ?? '', 40);
$message = '';

if(isset($_POST['add']) && isset($_POST['friend_id'])) {
    $fid = input_int($_POST['friend_id'] ?? 0, 1);
    if($fid !== $uid) {
        $result = friendship_send_request((int)$uid, $fid);
        if ($result === 'request_sent') {
            $message = 'Friend request sent.';
        } elseif ($result === 'accepted_incoming') {
            $message = 'Friend request accepted!';
        } elseif ($result === 'already_friends') {
            $message = 'You are already friends.';
        } elseif ($result === 'request_pending') {
            $message = 'Friend request is already pending.';
        } else {
            $message = 'Friend request could not be sent.';
        }
        $st = q("SELECT user_id, username, created_at FROM users WHERE user_id = ?", [$fid]);
        $searchedUser = $st->fetch(PDO::FETCH_ASSOC);
    }
} elseif(isset($_POST['search'])) {
    if($searchName !== '') {
        $st = q("SELECT user_id, username, created_at FROM users WHERE username = ?", [$searchName]);
        $searchedUser = $st->fetch(PDO::FETCH_ASSOC);
        if(!$searchedUser) {
            $message = 'User not found.';
        }
    }
}

$pets = [];
if($searchedUser) {
    $pets = get_user_pets($searchedUser['user_id']);
}

$searchedFriendship = $searchedUser ? friendship_get_between((int)$uid, (int)$searchedUser['user_id']) : null;
$friends = array_values(friendship_get_friend_list((int)$uid));
$incomingRequests = friendship_pending_requests_for_user((int)$uid);
$outgoingRequests = friendship_outgoing_requests_for_user((int)$uid);
?>
<h1>Friends</h1>
<div class="grid two">
    <div class="card">
        <h2>Your Friends</h2>
        <?php if($friends): ?>
            <?php foreach($friends as $f): ?>
            <details class="card glass">
                <summary><?= htmlspecialchars($f['username']) ?></summary>
                <p></p>
                <a class="btn" target="_blank" rel="noopener" href="?pg=user-chat&friend=<?= (int)$f['friend_id'] ?>">Message</a>
                <a class="btn disabled" href="#">Gift</a>
                <a class="btn disabled" href="#">Pets</a>
                <a class="btn disabled" href="#">Player Auctions</a>
                <a class="btn disabled" href="#">Player Shop</a>
                <a class="btn disabled" href="#">Unfriend</a>
            </details>
            <?php endforeach; ?>
        <?php else: ?>
        <p>You have no friends yet.</p>
        <?php endif; ?>

        <?php if($incomingRequests): ?>
        <h2>Friend Requests</h2>
            <?php foreach($incomingRequests as $request): ?>
            <div class="card glass">
                <p><?= htmlspecialchars($request['requester_username']) ?> wants to be friends.</p>
                <form method="post">
                    <input type="hidden" name="friend_id" value="<?= (int)$request['requested_by_user_id'] ?>">
                    <button class="btn" type="submit" name="add">Accept Request</button>
                </form>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if($outgoingRequests): ?>
        <h2>Sent Requests</h2>
            <?php foreach($outgoingRequests as $request): ?>
            <div class="card glass">
                <p>Waiting on <?= htmlspecialchars($request['recipient_username']) ?>.</p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2>Find Friends</h2>
        <form method="post">
            <label>Username:
                <input type="text" name="username" value="<?= htmlspecialchars($searchName) ?>">
            </label>
            <button class="btn" type="submit" name="search">Search</button>
        </form>
        <?php if($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <?php if($searchedUser): ?>
        <div class="card">
            <h2><?= htmlspecialchars($searchedUser['username']) ?></h2>
            <p>Joined <?= htmlspecialchars($searchedUser['created_at']) ?></p>
            <?php if($pets): ?>
            <div class="grid three">
                <?php foreach($pets as $p): ?>
                <div class="card glass">
                    <?= render_pet_thumbnail($p, 'thumb', $p['nickname'] ?: $p['species_name']) ?>
                    <p><?= htmlspecialchars($p['nickname'] ?: $p['species_name']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p>No pets yet.</p>
            <?php endif; ?>
            <?php if($searchedUser['user_id'] !== $uid): ?>
                <?php if($searchedFriendship): ?>
                    <p><?= htmlspecialchars(friendship_status_label($searchedFriendship, (int)$uid)) ?></p>
                    <?php if(($searchedFriendship['status'] ?? '') === FRIENDSHIP_STATUS_PENDING && (int)($searchedFriendship['requested_by_user_id'] ?? 0) !== (int)$uid): ?>
                    <form method="post">
                        <input type="hidden" name="friend_id" value="<?= (int)$searchedUser['user_id'] ?>">
                        <button class="btn" type="submit" name="add">Accept Request</button>
                    </form>
                    <?php endif; ?>
                <?php else: ?>
                <form method="post">
                    <input type="hidden" name="friend_id" value="<?= (int)$searchedUser['user_id'] ?>">
                    <button class="btn" type="submit" name="add">Send Request</button>
                </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
