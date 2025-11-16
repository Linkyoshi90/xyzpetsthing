<?php
require_once __DIR__.'/../auth.php';
require_login();

const WHEEL_OF_FATE_CURRENCY_ID = 1;
const WHEEL_OF_FATE_MIN_CASH = 400;
const WHEEL_OF_FATE_MAX_CASH = 1000;
const WHEEL_OF_FATE_ITEM_LIMIT = 6;
const WHEEL_OF_FATE_SPIN_COST = 500;
const WHEEL_OF_FATE_SPIN_COOLDOWN_SECONDS = 7200;

function wheel_of_fate_segments_data(): array {
    $items = q("SELECT item_id, item_name FROM items ORDER BY item_id ASC LIMIT ".WHEEL_OF_FATE_ITEM_LIMIT)->fetchAll(PDO::FETCH_ASSOC);
    $itemSegments = array_map(static function (array $row): array {
        return [
            'type' => 'item',
            'item_id' => (int)$row['item_id'],
            'label' => $row['item_name'],
        ];
    }, $items);

    $itemCount = count($itemSegments);
    $currencySlotCount = 3;
    if (($itemCount + $currencySlotCount) % 2 !== 0) {
        $currencySlotCount = 4;
    }

    $currencyAmounts = $currencySlotCount === 3
        ? [400, 600, 1000]
        : [400, 500, 700, 1000];

    $currencySegments = array_map(static function (int $amount): array {
        return [
            'type' => 'currency',
            'amount' => $amount,
            'label' => number_format($amount).' '.APP_CURRENCY_LONG_NAME,
        ];
    }, $currencyAmounts);

    $segments = [];
    $itemsQueue = $itemSegments;
    $currencyQueue = $currencySegments;
    $total = $itemCount + $currencySlotCount;

    for ($i = 0; $i < $total; $i++) {
        if ($i % 2 === 0) {
            if (!empty($itemsQueue)) {
                $segments[] = array_shift($itemsQueue);
            } elseif (!empty($currencyQueue)) {
                $segments[] = array_shift($currencyQueue);
            }
        } else {
            if (!empty($currencyQueue)) {
                $segments[] = array_shift($currencyQueue);
            } elseif (!empty($itemsQueue)) {
                $segments[] = array_shift($itemsQueue);
            }
        }
    }

    foreach ($itemsQueue as $remaining) {
        $segments[] = $remaining;
    }
    foreach ($currencyQueue as $remaining) {
        $segments[] = $remaining;
    }

    return [
        'segments' => $segments,
        'item_count' => $itemCount,
        'currency_slots' => $currencySlotCount,
    ];
}

function wheel_of_fate_remaining_cooldown(?string $timestamp): int {
    if (!$timestamp) {
        return 0;
    }

    $lastSpin = strtotime($timestamp);
    if ($lastSpin === false) {
        return 0;
    }

    $elapsed = time() - $lastSpin;
    if ($elapsed < 0) {
        $elapsed = 0;
    }

    $remaining = WHEEL_OF_FATE_SPIN_COOLDOWN_SECONDS - $elapsed;
    return $remaining > 0 ? (int)$remaining : 0;
}

$wheelData = wheel_of_fate_segments_data();

$uid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (empty($wheelData['segments'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Wheel is not configured.']);
        exit;
    }

    $segments = $wheelData['segments'];

    try {
        $pdo = db();
        $pdo->beginTransaction();

        $balanceStmt = $pdo->prepare('SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = ? FOR UPDATE');
        $balanceStmt->execute([$uid, WHEEL_OF_FATE_CURRENCY_ID]);
        $balanceRow = $balanceStmt->fetch(PDO::FETCH_ASSOC);
        $currentBalance = $balanceRow ? (float)$balanceRow['balance'] : 0.0;
        if ($currentBalance < WHEEL_OF_FATE_SPIN_COST) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'You need '.number_format(WHEEL_OF_FATE_SPIN_COST).' '.APP_CURRENCY_LONG_NAME.' to spin the wheel.',
                'cooldownRemaining' => 0,
            ]);
            exit;
        }

        $cooldownStmt = $pdo->prepare('SELECT last_spin_at FROM wheel_of_fate_spins WHERE user_id = ? FOR UPDATE');
        $cooldownStmt->execute([$uid]);
        $cooldownRow = $cooldownStmt->fetch(PDO::FETCH_ASSOC);
        $cooldownRemaining = 0;
        if ($cooldownRow && isset($cooldownRow['last_spin_at'])) {
            $cooldownRemaining = wheel_of_fate_remaining_cooldown($cooldownRow['last_spin_at']);
        }
        if ($cooldownRemaining > 0) {
            $pdo->rollBack();
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => 'The wheel is cooling down. Please try again later.',
                'cooldownRemaining' => $cooldownRemaining,
            ]);
            exit;
        }

        $deductStmt = $pdo->prepare('UPDATE user_balances SET balance = balance - ? WHERE user_id = ? AND currency_id = ?');
        $deductStmt->execute([WHEEL_OF_FATE_SPIN_COST, $uid, WHEEL_OF_FATE_CURRENCY_ID]);
        if ($deductStmt->rowCount() === 0) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'You need '.number_format(WHEEL_OF_FATE_SPIN_COST).' '.APP_CURRENCY_LONG_NAME.' to spin the wheel.',
                'cooldownRemaining' => 0,
            ]);
            exit;
        }

        $ledgerStmt = $pdo->prepare('INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata) VALUES (?,?,?,?,?)');
        $ledgerStmt->execute([
            $uid,
            WHEEL_OF_FATE_CURRENCY_ID,
            -WHEEL_OF_FATE_SPIN_COST,
            'wheel_of_fate_spin',
            json_encode(['cost' => WHEEL_OF_FATE_SPIN_COST]),
        ]);

        $spinLogStmt = $pdo->prepare('INSERT INTO wheel_of_fate_spins (user_id, last_spin_at) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_spin_at = VALUES(last_spin_at)');
        $spinLogStmt->execute([$uid]);

        $segmentIndex = random_int(0, count($segments) - 1);
        $segment = $segments[$segmentIndex];
        $reward = [
            'type' => $segment['type'],
            'label' => $segment['label'],
        ];

        if ($segment['type'] === 'currency') {
            $amount = (int)$segment['amount'];
            $currencyStmt = $pdo->prepare('INSERT INTO user_balances (user_id, currency_id, balance) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)');
            $currencyStmt->execute([$uid, WHEEL_OF_FATE_CURRENCY_ID, $amount]);
            $ledgerStmt->execute([
                $uid,
                WHEEL_OF_FATE_CURRENCY_ID,
                $amount,
                'wheel_of_fate',
                json_encode(['type' => 'currency', 'amount' => $amount]),
            ]);
            $reward['amount'] = $amount;
        } elseif ($segment['type'] === 'item') {
            $itemId = (int)$segment['item_id'];
            $itemStmt = $pdo->prepare('INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1');
            $itemStmt->execute([$uid, $itemId]);
            $reward['itemId'] = $itemId;
        }

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
            'reward' => $reward,
            'balances' => $balances,
            'cooldownRemaining' => WHEEL_OF_FATE_SPIN_COOLDOWN_SECONDS,
        ]);
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to complete spin.']);
    }
    exit;
}
$cooldownRemaining = 0;
try {
    $cooldownRow = q('SELECT last_spin_at FROM wheel_of_fate_spins WHERE user_id = ?', [$uid])->fetch(PDO::FETCH_ASSOC);
    if ($cooldownRow && isset($cooldownRow['last_spin_at'])) {
        $cooldownRemaining = wheel_of_fate_remaining_cooldown($cooldownRow['last_spin_at']);
    }
} catch (Throwable $ignored) {
    $cooldownRemaining = 0;
}
$segments = $wheelData['segments'];
$itemCount = $wheelData['item_count'];
$currencySlots = $wheelData['currency_slots'];
?>
<link rel="stylesheet" href="assets/css/wheel-of-fate.css">
<script>
window.WHEEL_OF_FATE_SEGMENTS = <?= json_encode($segments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
window.WHEEL_OF_FATE_STATE = <?= json_encode([
    'cost' => WHEEL_OF_FATE_SPIN_COST,
    'cooldownSeconds' => WHEEL_OF_FATE_SPIN_COOLDOWN_SECONDS,
    'cooldownRemaining' => $cooldownRemaining,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<script defer src="assets/js/wheel-of-fate.js"></script>

<h1>Wheel of Fate</h1>
<p class="muted">Spin the wheel to win between <?= WHEEL_OF_FATE_MIN_CASH ?> and <?= WHEEL_OF_FATE_MAX_CASH ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?> or snag one of <?= $itemCount ?> featured items.</p>
<p class="muted small">The wheel is evenly divided across <?= count($segments) ?> prizes (<?= $itemCount ?> items, <?= $currencySlots ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?> rewards).</p>

<div class="wheel-of-fate-layout">
    <div class="wheel-stage">
        <canvas id="wheel-canvas" width="420" height="420" aria-label="Wheel of Fate"></canvas>
        <div class="wheel-pointer" aria-hidden="true"></div>
    </div>
    <div class="wheel-controls">
        <button id="spin-button" class="btn primary">Spin the Wheel</button>
        <div class="spin-cost muted">Cost: <?= number_format(WHEEL_OF_FATE_SPIN_COST) ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?></div>
        <div class="spin-cooldown muted">Next spin in: <span id="spin-cooldown"><?= $cooldownRemaining > 0 ? gmdate('H:i:s', $cooldownRemaining) : 'Ready' ?></span></div>
        <div class="spin-timer muted">Stopping in: <span id="spin-timer">--</span>s</div>
        <div id="spin-result" class="spin-result muted" role="status"></div>
        <h2 class="prize-heading">Today's segments</h2>
        <ul class="prize-list">
            <?php foreach ($segments as $segment): ?>
                <li>
                    <?php if ($segment['type'] === 'currency'): ?>
                        <span class="prize-type cash">💰</span>
                        <span class="prize-label"><?= htmlspecialchars($segment['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php else: ?>
                        <span class="prize-type item">🎁</span>
                        <span class="prize-label"><?= htmlspecialchars($segment['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>