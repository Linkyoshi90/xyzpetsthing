<?php
require_once __DIR__.'/../auth.php';
require_login();

const WHEEL_OF_FATE_CURRENCY_ID = 1;
const WHEEL_OF_FATE_MIN_CASH = 200;
const WHEEL_OF_FATE_MAX_CASH = 1000;
const WHEEL_OF_FATE_ITEM_LIMIT = 6;

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
        ? [200, 600, 1000]
        : [200, 400, 700, 1000];

    $currencySegments = array_map(static function (int $amount): array {
        return [
            'type' => 'currency',
            'amount' => $amount,
            'label' => number_format($amount).' Cash',
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

$wheelData = wheel_of_fate_segments_data();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (empty($wheelData['segments'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Wheel is not configured.']);
        exit;
    }

    $uid = current_user()['id'];
    $segments = $wheelData['segments'];

    try {
        $segmentIndex = random_int(0, count($segments) - 1);
        $segment = $segments[$segmentIndex];
        $reward = [
            'type' => $segment['type'],
            'label' => $segment['label'],
        ];

        if ($segment['type'] === 'currency') {
            $amount = (int)$segment['amount'];
            q(
                "INSERT INTO user_balances (user_id, currency_id, balance) VALUES (?, ?, ?)\n                 ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)",
                [$uid, WHEEL_OF_FATE_CURRENCY_ID, $amount]
            );
            q(
                "INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata) VALUES (?,?,?,?,?)",
                [$uid, WHEEL_OF_FATE_CURRENCY_ID, $amount, 'wheel_of_fate', json_encode(['type' => 'currency', 'amount' => $amount])]
            );
            $reward['amount'] = $amount;
        } elseif ($segment['type'] === 'item') {
            $itemId = (int)$segment['item_id'];
            q(
                "INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, 1)\n                 ON DUPLICATE KEY UPDATE quantity = quantity + 1",
                [$uid, $itemId]
            );
            $reward['itemId'] = $itemId;
        }

        $balanceRows = q(
            "SELECT currency_id, balance FROM user_balances WHERE user_id = ? AND currency_id IN (1,2)",
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
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to complete spin.']);
    }
    exit;
}

$segments = $wheelData['segments'];
$itemCount = $wheelData['item_count'];
$currencySlots = $wheelData['currency_slots'];
?>
<link rel="stylesheet" href="assets/css/wheel-of-fate.css">
<script>
window.WHEEL_OF_FATE_SEGMENTS = <?= json_encode($segments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<script defer src="assets/js/wheel-of-fate.js"></script>

<h1>Wheel of Fate</h1>
<p class="muted">Spin the wheel to win between <?= WHEEL_OF_FATE_MIN_CASH ?> and <?= WHEEL_OF_FATE_MAX_CASH ?> Cash or snag one of <?= $itemCount ?> featured items.</p>
<p class="muted small">The wheel is evenly divided across <?= count($segments) ?> prizes (<?= $itemCount ?> items, <?= $currencySlots ?> cash rewards).</p>

<div class="wheel-of-fate-layout">
    <div class="wheel-stage">
        <canvas id="wheel-canvas" width="420" height="420" aria-label="Wheel of Fate"></canvas>
        <div class="wheel-pointer" aria-hidden="true"></div>
    </div>
    <div class="wheel-controls">
        <button id="spin-button" class="btn primary">Spin the Wheel</button>
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