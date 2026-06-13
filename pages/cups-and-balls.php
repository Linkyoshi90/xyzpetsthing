<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../lib/cups_and_balls.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cups_handle_post();
}

$uid = (int)current_user()['id'];
cups_ensure_balance_row($uid);
$cashBalance = cups_refresh_cash($uid);
$state = cups_public_state(cups_get_state());
$stateJson = htmlspecialchars(json_encode($state), ENT_QUOTES, 'UTF-8');
$currencyName = APP_CURRENCY_LONG_NAME;
$currencyAttr = htmlspecialchars($currencyName, ENT_QUOTES, 'UTF-8');
$endpoint = 'index.php?pg=cups-and-balls';
if (defined('SID') && SID !== '') {
    $endpoint .= '&'.SID;
}
$endpointAttr = htmlspecialchars($endpoint, ENT_QUOTES, 'UTF-8');
$initialBet = max(1, min(10, max(1, $cashBalance)));
$cupImagePath = 'images/games/cupballs-cup.webp';
$hasCupImage = is_file(__DIR__.'/../'.$cupImagePath);
?>
<link rel="stylesheet" href="assets/css/cups-and-balls.css">
<script defer src="assets/js/cups-and-balls.js"></script>

<h1>Cups and Balls</h1>

<section
    id="cups-game"
    class="cups-game"
    data-endpoint="<?= $endpointAttr ?>"
    data-balance="<?= (int)$cashBalance ?>"
    data-currency="<?= $currencyAttr ?>"
    data-state="<?= $stateJson ?>"
>
    <div class="cups-hud" aria-label="Game totals">
        <div>
            <span class="cups-hud-label">Balance</span>
            <strong><span id="cups-balance"><?= (int)$cashBalance ?></span> <?= htmlspecialchars(APP_CURRENCY_SHORT_NAME, ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div>
            <span class="cups-hud-label">Bet</span>
            <strong><span id="cups-current-bet">0</span> <?= htmlspecialchars(APP_CURRENCY_SHORT_NAME, ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div>
            <span class="cups-hud-label">Payout</span>
            <strong><span id="cups-payout">0</span> <?= htmlspecialchars(APP_CURRENCY_SHORT_NAME, ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
    </div>

    <div class="cups-layout">
        <div class="cups-stage-wrap">
            <div class="cups-round-track" aria-label="Round progress">
                <span class="cups-round-dot" data-round-dot="1">1</span>
                <span class="cups-round-dot" data-round-dot="2">2</span>
                <span class="cups-round-dot" data-round-dot="3">3</span>
            </div>

            <div class="cups-stage" id="cups-stage">
                <div class="cups-table"></div>
                <div id="cups-ball" class="cups-ball" aria-hidden="true"></div>
                <?php for ($cupIndex = 0; $cupIndex < 3; $cupIndex++): ?>
                <button type="button" class="cup-slot" data-cup="<?= $cupIndex ?>" aria-label="Cup <?= $cupIndex + 1 ?>" disabled>
                    <span class="cup <?= $hasCupImage ? 'cup-has-image' : '' ?>">
                        <?php if ($hasCupImage): ?>
                            <img class="cup-image" src="<?= htmlspecialchars($cupImagePath, ENT_QUOTES, 'UTF-8') ?>" alt="" aria-hidden="true" draggable="false">
                        <?php else: ?>
                            <span class="cup-rim"></span>
                            <span class="cup-body"></span>
                            <span class="cup-foot"></span>
                        <?php endif; ?>
                    </span>
                </button>
                <?php endfor; ?>
            </div>

            <p id="cups-status" class="cups-status" role="status" aria-live="polite"><?= htmlspecialchars($state['message'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="cups-controls">
            <form id="cups-bet-form" class="cups-bet-form">
                <label for="cups-bet">Bet amount</label>
                <div class="cups-bet-stepper">
                    <input
                        id="cups-bet"
                        name="bet"
                        type="number"
                        min="1"
                        max="<?= max(1, (int)$cashBalance) ?>"
                        step="1"
                        value="<?= (int)$initialBet ?>"
                        inputmode="numeric"
                        required
                    >
                    <div class="cups-step-buttons">
                        <button type="button" class="cups-step" data-step="1" aria-label="Increase bet">▲</button>
                        <button type="button" class="cups-step" data-step="-1" aria-label="Decrease bet">▼</button>
                    </div>
                </div>
                <button id="cups-start" type="submit" class="btn">Start</button>
            </form>

            <div class="cups-action-row">
                <button id="cups-next" type="button" class="btn" hidden>Next Round</button>
                <button id="cups-reset" type="button" class="btn ghost" hidden>Clear Table</button>
            </div>
        </div>
    </div>
</section>
