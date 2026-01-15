<?php
require_login();
require_once __DIR__.'/../db.php';
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/input.php';

$uid = current_user()['id'];

$searchedUser = null;
$searchName = input_string($_POST['username'] ?? '', 40);
$message = '';

if(isset($_POST['add']) && isset($_POST['friend_id'])) {
    $fid = input_int($_POST['friend_id'] ?? 0, 1);
    if($fid !== $uid) {
        q("INSERT IGNORE INTO user_friends (user_id, friend_id) VALUES (?, ?)", [$uid, $fid]);
        $message = 'Friend added!';
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

$friends = q("SELECT uf.connection_id, CASE WHEN uf.user_id = ? THEN uf.friend_id ELSE uf.user_id END AS friend_id, u.username, u.created_at FROM user_friends uf JOIN users u ON u.user_id = CASE WHEN uf.user_id = ? THEN uf.friend_id ELSE uf.user_id END WHERE uf.user_id = ? OR uf.friend_id = ?", [$uid, $uid, $uid, $uid])->fetchAll(PDO::FETCH_ASSOC);
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
                    <img class="thumb" src="<?= htmlspecialchars(pet_image_url($p['species_name'], $p['color_name'])) ?>" alt="">
                    <p><?= htmlspecialchars($p['nickname'] ?: $p['species_name']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p>No pets yet.</p>
            <?php endif; ?>
            <?php if($searchedUser['user_id'] !== $uid): ?>
            <form method="post">
                <input type="hidden" name="friend_id" value="<?= (int)$searchedUser['user_id'] ?>">
                <button class="btn" type="submit" name="add">Add</button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
