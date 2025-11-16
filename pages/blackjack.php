<?php
require_once __DIR__.'/../auth.php';
require_login();
require_once __DIR__.'/../lib/blackjack.php';

$uid = current_user()['id'];
$state = blackjack_get_state();
$errors = blackjack_pop_errors();
$cash_balance = blackjack_get_cash_balance($uid);
$playerTotal = $state['player'] ? blackjack_hand_value($state['player']) : 0;
$dealerTotal = $state['dealer'] ? blackjack_hand_value($state['dealer']) : 0;
$dealerHidden = $state['status'] === 'player_turn';
$betValue = $state['bet'] > 0 ? $state['bet'] : 10;
?>
<link rel="stylesheet" href="assets/css/blackjack.css">
<h1>Blackjack</h1>
<p class="muted">Beat the dealer without going over 21. The game only ends when you run out of <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?>.</p>

<div class="blackjack-info">
    <div class="blackjack-balance">💰 <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?>: <strong><?= (int)$cash_balance ?></strong></div>
    <?php if ($state['bet'] > 0): ?>
    <div class="blackjack-bet">Current bet: <?= (int)$state['bet'] ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?></div>
    <?php endif; ?>
</div>

<?php if ($errors): ?>
<div class="alert alert-error">
    <ul>
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if ($state['message']): ?>
<p class="blackjack-message"><?= htmlspecialchars($state['message'], ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<div class="blackjack-table">
    <section class="hand dealer">
        <h2>Dealer</h2>
        <div class="cards">
            <?php foreach ($state['dealer'] as $index => $card): ?>
                <?php if ($dealerHidden && $index === 1): ?>
                    <span class="card hidden">?</span>
                <?php else: ?>
                    <span class="card"><?= htmlspecialchars(blackjack_card_label($card), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!$state['dealer']): ?>
                <span class="card placeholder">?</span>
            <?php endif; ?>
        </div>
        <p class="total">Total: <?= $dealerHidden ? '??' : (int)$dealerTotal ?></p>
    </section>
    <section class="hand player">
        <h2>You</h2>
        <div class="cards">
            <?php foreach ($state['player'] as $card): ?>
                <span class="card"><?= htmlspecialchars(blackjack_card_label($card), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endforeach; ?>
            <?php if (!$state['player']): ?>
                <span class="card placeholder">?</span>
            <?php endif; ?>
        </div>
        <p class="total">Total: <?= $state['player'] ? (int)$playerTotal : '--' ?></p>
    </section>
</div>

<?php if ($state['status'] === 'player_turn'): ?>
<div class="action-row">
    <form method="post" action="index.php?pg=blackjack">
        <input type="hidden" name="action" value="hit">
        <button type="submit" class="btn">Hit</button>
    </form>
    <form method="post" action="index.php?pg=blackjack">
        <input type="hidden" name="action" value="stand">
        <button type="submit" class="btn">Stand</button>
    </form>
</div>
<?php else: ?>
    <?php if ($cash_balance > 0): ?>
    <form method="post" action="index.php?pg=blackjack" class="bet-form">
        <input type="hidden" name="action" value="start">
        <label for="bet-amount">Bet amount</label>
        <input id="bet-amount" name="bet" type="number" min="1" max="<?= (int)$cash_balance ?>" value="<?= min((int)$betValue, (int)$cash_balance) ?>" required>
        <button type="submit" class="btn">Deal</button>
    </form>
    <?php else: ?>
    <div class="alert alert-error">You're out of <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?>. Better luck next time!</div>
    <?php endif; ?>
    <?php if ($state['status'] === 'round_over' && $cash_balance > 0): ?>
    <form method="post" action="index.php?pg=blackjack" class="reset-form">
        <input type="hidden" name="action" value="reset">
        <button type="submit" class="btn ghost">Clear table</button>
    </form>
    <?php endif; ?>
<?php endif; ?>