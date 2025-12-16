<?php
require_once __DIR__.'/../auth.php';
require_login();

define('RSC_WOF_CURRENCY_ID', 1);
define('RSC_WOF_SPIN_COST', 1000);
define('RSC_WOF_SEGMENT_LIMIT', 12);
define('RSC_WOF_MAX_PRICE', 55000);
define('RSC_WOF_COOLDOWN_SECONDS', 86400);

function rsc_wheel_segments(): array {
    $items = q(
        'SELECT item_id, item_name FROM items WHERE base_price IS NOT NULL AND base_price <= ? ORDER BY item_id ASC',
        [RSC_WOF_MAX_PRICE]
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        // If no priced items are available, fall back to the earliest catalog entries.
        $items = q(
            'SELECT item_id, item_name FROM items ORDER BY item_id ASC LIMIT '.(int)RSC_WOF_SEGMENT_LIMIT
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    $seed = hash('sha256', date('Y-m-d').'|rsc-wof');
    usort($items, static function (array $a, array $b) use ($seed): int {
        $aKey = hash('sha256', $seed.'-'.$a['item_id']);
        $bKey = hash('sha256', $seed.'-'.$b['item_id']);
        return $aKey <=> $bKey;
    });

    $selected = array_slice($items, 0, RSC_WOF_SEGMENT_LIMIT);

    return array_map(static function (array $row): array {
        return [
            'type' => 'item',
            'item_id' => (int)$row['item_id'],
            'label' => $row['item_name'],
        ];
    }, $selected);
}

function rsc_wheel_remaining_cooldown(?string $timestamp): int {
    if (!$timestamp) {
        return 0;
    }

    $lastSpin = strtotime($timestamp);
    if ($lastSpin === false) {
        return 0;
    }

    $lastSpinDate = date('Y-m-d', $lastSpin);
    $today = date('Y-m-d');
    if ($lastSpinDate !== $today) {
        return 0;
    }

    $resetAt = strtotime('tomorrow midnight');
    if ($resetAt === false) {
        return RSC_WOF_COOLDOWN_SECONDS;
    }

    $remaining = $resetAt - time();
    return $remaining > 0 ? (int)$remaining : 0;
}

$segments = rsc_wheel_segments();
$uid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (empty($segments)) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'error' => 'Wheel is unavailable because no eligible prizes are configured.',
            'cooldownRemaining' => 0,
        ]);
        exit;
    }

    try {
        $pdo = db();
        $pdo->beginTransaction();

        $balanceStmt = $pdo->prepare('SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = ? FOR UPDATE');
        $balanceStmt->execute([$uid, RSC_WOF_CURRENCY_ID]);
        $balanceRow = $balanceStmt->fetch(PDO::FETCH_ASSOC);
        $currentBalance = $balanceRow ? (float)$balanceRow['balance'] : 0.0;
        if ($currentBalance < RSC_WOF_SPIN_COST) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'You need '.number_format(RSC_WOF_SPIN_COST).' '.APP_CURRENCY_LONG_NAME.' to spin the wheel.',
                'cooldownRemaining' => 0,
            ]);
            exit;
        }

        $cooldownStmt = $pdo->prepare('SELECT last_spin_at FROM rsc_wheel_spins WHERE user_id = ? FOR UPDATE');
        $cooldownStmt->execute([$uid]);
        $cooldownRow = $cooldownStmt->fetch(PDO::FETCH_ASSOC);
        $cooldownRemaining = 0;
        if ($cooldownRow && isset($cooldownRow['last_spin_at'])) {
            $cooldownRemaining = rsc_wheel_remaining_cooldown($cooldownRow['last_spin_at']);
        }
        if ($cooldownRemaining > 0) {
            $pdo->rollBack();
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => 'You can only spin this wheel once per day.',
                'cooldownRemaining' => $cooldownRemaining,
            ]);
            exit;
        }

        $deductStmt = $pdo->prepare('UPDATE user_balances SET balance = balance - ? WHERE user_id = ? AND currency_id = ?');
        $deductStmt->execute([RSC_WOF_SPIN_COST, $uid, RSC_WOF_CURRENCY_ID]);
        if ($deductStmt->rowCount() === 0) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'You need '.number_format(RSC_WOF_SPIN_COST).' '.APP_CURRENCY_LONG_NAME.' to spin the wheel.',
                'cooldownRemaining' => 0,
            ]);
            exit;
        }

        $ledgerStmt = $pdo->prepare('INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata) VALUES (?,?,?,?,?)');
        $ledgerStmt->execute([
            $uid,
            RSC_WOF_CURRENCY_ID,
            -RSC_WOF_SPIN_COST,
            'rsc_wheel_spin',
            json_encode(['cost' => RSC_WOF_SPIN_COST]),
        ]);

        $spinLogStmt = $pdo->prepare('INSERT INTO rsc_wheel_spins (user_id, last_spin_at) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_spin_at = VALUES(last_spin_at)');
        $spinLogStmt->execute([$uid]);

        $segmentIndex = random_int(0, count($segments) - 1);
        $segment = $segments[$segmentIndex];
        $itemId = (int)$segment['item_id'];

        $itemStmt = $pdo->prepare('INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1');
        $itemStmt->execute([$uid, $itemId]);

        $pdo->commit();

        $balanceRows = q(
            'SELECT currency_id, balance FROM user_balances WHERE user_id = ? AND currency_id IN (1,2)',
            [$uid]
        )->fetchAll(PDO::FETCH_ASSOC);

        $balances = [
            'cash' => (int)($_SESSION['user']['cash'] ?? 0),
            'gems' => (int)($_SESSION['user']['gems'] ?? 0),
        ];
        foreach ($balanceRows as $row) {
            if ((int)$row['currency_id'] === 1) {
                $balances['cash'] = (int)$row['balance'];
            } elseif ((int)$row['currency_id'] === 2) {
                $balances['gems'] = (int)$row['balance'];
            }
        }
        $_SESSION['user']['cash'] = $balances['cash'];
        $_SESSION['user']['gems'] = $balances['gems'];

        echo json_encode([
            'success' => true,
            'segmentIndex' => $segmentIndex,
            'reward' => [
                'type' => 'item',
                'itemId' => $itemId,
                'label' => $segment['label'],
            ],
            'balances' => $balances,
            'cooldownRemaining' => rsc_wheel_remaining_cooldown(date('Y-m-d H:i:s')),
        ]);
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        error_log('[rsc-wof] spin failed: '.$e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Unable to complete spin: '.$e->getMessage(),
        ]);
    }
    exit;
}

$cooldownRemaining = 0;
try {
    $cooldownRow = q('SELECT last_spin_at FROM rsc_wheel_spins WHERE user_id = ?', [$uid])->fetch(PDO::FETCH_ASSOC);
    if ($cooldownRow && isset($cooldownRow['last_spin_at'])) {
        $cooldownRemaining = rsc_wheel_remaining_cooldown($cooldownRow['last_spin_at']);
    }
} catch (Throwable $ignored) {
    $cooldownRemaining = 0;
}
?>
<link rel="stylesheet" href="assets/css/wheel-of-fate.css">
<script>
window.WHEEL_OF_FATE_ENDPOINT = 'index.php?pg=rsc-wof';
window.WHEEL_OF_FATE_SEGMENTS = <?= json_encode($segments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
window.WHEEL_OF_FATE_STATE = <?= json_encode([
    'cost' => RSC_WOF_SPIN_COST,
    'cooldownSeconds' => RSC_WOF_COOLDOWN_SECONDS,
    'cooldownRemaining' => $cooldownRemaining,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<script defer src="assets/js/wheel-of-fate.js"></script>

<h1>Red Sun Wheel of Fortune</h1>
<p class="muted">Spin once per day for a random regional item. Each spin costs <?= number_format(RSC_WOF_SPIN_COST) ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?>.</p>
<p class="muted small">The wheel pulls <?= RSC_WOF_SEGMENT_LIMIT ?> items priced at <?= number_format(RSC_WOF_MAX_PRICE) ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?> or below each day.</p>

<div class="wheel-of-fate-layout">
    <div class="wheel-stage">
        <canvas id="wheel-canvas" width="420" height="420" aria-label="Wheel of Fortune"></canvas>
        <div class="wheel-pointer" aria-hidden="true"></div>
    </div>
    <div class="wheel-controls">
        <button id="spin-button" class="btn primary" <?= empty($segments) ? 'disabled aria-disabled="true"' : '' ?>>Spin the Wheel</button>
        <div class="spin-cost muted">Cost: <?= number_format(RSC_WOF_SPIN_COST) ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?></div>
        <div class="spin-cooldown muted">Next spin in: <span id="spin-cooldown"><?= $cooldownRemaining > 0 ? gmdate('H:i:s', $cooldownRemaining) : 'Ready' ?></span></div>
        <div class="spin-timer muted">Stopping in: <span id="spin-timer">--</span>s</div>
        <div id="spin-result" class="spin-result muted" role="status"></div>
        <h2 class="prize-heading">Today's items</h2>
        <ul class="prize-list">
            <?php foreach ($segments as $segment): ?>
                <li>
                    <span class="prize-type item">🎁</span>
                    <span class="prize-label"><?= htmlspecialchars($segment['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if (empty($segments)): ?>
            <p class="muted">Wheel unavailable: no eligible prizes are configured right now.</p>
        <?php endif; ?>
    </div>
</div>