<?php
require_once __DIR__.'/../auth.php';
require_login();

const WHEEL_OF_FATE_CURRENCY_ID = 1;
const WHEEL_OF_FATE_SPIN_COST = 1000;
const WHEEL_OF_FATE_SPIN_COOLDOWN_SECONDS = 72;
const WHEEL_OF_FATE_BASE_SEGMENT_LIMIT = 8;
const WHEEL_OF_FATE_PRIZE_ITEM_IDS = [
    14,
    15,
    16,
    17,
    18,
    19,
    20,
    21,
    25,
    26,
    27,
    28,
    29,
    30,
    31,
    32,
    33,
    34,
    35,
    36,
    37,
    39,
    43,
    44,
    62,
    86,
];

if (!function_exists('auto_detect_locale')) {
    /**
     * Basic locale bootstrapper used by the wheel page.
     *
     * The wider app normally wires this up elsewhere, but if that helper is
     * missing we fall back to setting a sensible default instead of blowing up
     * the page.
     */
    function auto_detect_locale(): void
    {
        $locale = locale_get_default();
        if (!$locale) {
            $locale = 'en_US.UTF-8';
        }
        setlocale(LC_ALL, $locale);
    }
}

auto_detect_locale();

function wheel_of_fate_quantity_for_item(int $itemId, string $seed): int {
    $hash = hash('sha256', $seed.'|qty|'.$itemId);
    $value = hexdec(substr($hash, 0, 8));
    return ($value % 10) + 1; // 1-10 prizes
}

function wheel_of_fate_weight_for_quantity(int $quantity): int {
    $weight = 11 - $quantity; // higher quantity => lower weight
    return $weight > 0 ? $weight : 1;
}

function wheel_of_fate_prize_pool(): array {
    if (empty(WHEEL_OF_FATE_PRIZE_ITEM_IDS)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count(WHEEL_OF_FATE_PRIZE_ITEM_IDS), '?'));
    $order = implode(',', WHEEL_OF_FATE_PRIZE_ITEM_IDS);

    return q(
        "SELECT item_id, item_name FROM items WHERE item_id IN ($placeholders) ORDER BY FIELD(item_id, $order)",
        WHEEL_OF_FATE_PRIZE_ITEM_IDS
    )->fetchAll(PDO::FETCH_ASSOC);
}

function wheel_of_fate_segments_data(): array {
    $items = wheel_of_fate_prize_pool();

    if (!$items) {
        return [
            'segments' => [],
            'item_count' => 0,
            'currency_slots' => 0,
        ];
    }

    $seed = hash('sha256', date('Y-m-d').'|aa-wof');
    usort($items, static function (array $a, array $b) use ($seed): int {
        $aKey = hash('sha256', $seed.'-'.$a['item_id']);
        $bKey = hash('sha256', $seed.'-'.$b['item_id']);
        return $aKey <=> $bKey;
    });

    $selected = array_slice($items, 0, WHEEL_OF_FATE_BASE_SEGMENT_LIMIT);

    $segments = [];
    foreach ($selected as $row) {
        $quantity = wheel_of_fate_quantity_for_item((int)$row['item_id'], $seed);
        $weight = wheel_of_fate_weight_for_quantity($quantity);
        $segment = [
            'type' => 'item',
            'item_id' => (int)$row['item_id'],
            'quantity' => $quantity,
            'label' => $quantity.'x '.$row['item_name'],
        ];
        for ($i = 0; $i < $weight; $i++) {
            $segments[] = $segment;
        }
    }

    return [
        'segments' => $segments,
        'item_count' => count($selected),
        'currency_slots' => 0,
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
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'error' => 'Wheel is unavailable because no eligible prizes are configured.',
            'cooldownRemaining' => 0,
        ]);
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
            'type' => 'item',
            'label' => $segment['label'],
        ];

        $itemId = (int)$segment['item_id'];
        $quantity = (int)($segment['quantity'] ?? 1);
        $itemStmt = $pdo->prepare('INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)');
        $itemStmt->execute([$uid, $itemId, $quantity]);
        $reward['itemId'] = $itemId;
        $reward['quantity'] = $quantity;

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
$segmentCount = count($segments);
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
<p class="muted">Spin the wheel for a haul of <?= $itemCount ?> featured prizes, each worth 1–10 items.</p>
<p class="muted small">Today's wheel holds <?= $segmentCount ?> weighted slices drawn from <?= $itemCount ?> items.</p>

<div class="wheel-of-fate-layout">
    <div class="wheel-stage">
        <canvas id="wheel-canvas" width="420" height="420" aria-label="Wheel of Fate"></canvas>
        <div class="wheel-pointer" aria-hidden="true"></div>
    </div>
    <div class="wheel-controls">
        <button id="spin-button" class="btn primary" <?= empty($segments) ? 'disabled aria-disabled="true"' : '' ?>>Spin the Wheel</button>
        <div class="spin-cost muted">Cost: <?= number_format(WHEEL_OF_FATE_SPIN_COST) ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?></div>
        <div class="spin-cooldown muted">Next spin in: <span id="spin-cooldown"><?= $cooldownRemaining > 0 ? gmdate('H:i:s', $cooldownRemaining) : 'Ready' ?></span></div>
        <div class="spin-timer muted">Stopping in: <span id="spin-timer">--</span>s</div>
        <div id="spin-result" class="spin-result muted" role="status"></div>
        <h2 class="prize-heading">Today's segments</h2>
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
