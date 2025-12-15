<?php
require_once __DIR__.'/../auth.php';
require_login();

define('AA_WOF_CURRENCY_ID', 1);
define('AA_WOF_SPIN_COST', 100);
define('AA_WOF_BASE_SEGMENT_LIMIT', 8);

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

function aa_wof_quantity_for_item(int $itemId, string $seed): int {
    $hash = hash('sha256', $seed.'|qty|'.$itemId);
    $value = hexdec(substr($hash, 0, 8));
    return ($value % 8) + 1; // 1-8 pizzas
}

function aa_wof_weight_for_quantity(int $quantity): int {
    $weight = 9 - $quantity; // higher quantity => lower weight
    return $weight > 0 ? $weight : 1;
}

function aa_wof_segments(): array {
    $items = q(
        "SELECT item_id, item_name FROM items WHERE LOWER(item_name) LIKE '%pizza%' ORDER BY item_id ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        return [];
    }

    $seed = hash('sha256', date('Y-m-d').'|aa-wof');
    usort($items, static function (array $a, array $b) use ($seed): int {
        $aKey = hash('sha256', $seed.'-'.$a['item_id']);
        $bKey = hash('sha256', $seed.'-'.$b['item_id']);
        return $aKey <=> $bKey;
    });

    $selected = array_slice($items, 0, AA_WOF_BASE_SEGMENT_LIMIT);

    $segments = [];
    foreach ($selected as $row) {
        $quantity = aa_wof_quantity_for_item((int)$row['item_id'], $seed);
        $weight = aa_wof_weight_for_quantity($quantity);
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

    return $segments;
}

$segments = aa_wof_segments();
$uid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (empty($segments)) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'error' => 'Wheel is unavailable because no eligible pizza prizes are configured.',
            'cooldownRemaining' => 0,
        ]);
        exit;
    }

    try {
        $pdo = db();
        $pdo->beginTransaction();

        $balanceStmt = $pdo->prepare('SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = ? FOR UPDATE');
        $balanceStmt->execute([$uid, AA_WOF_CURRENCY_ID]);
        $balanceRow = $balanceStmt->fetch(PDO::FETCH_ASSOC);
        $currentBalance = $balanceRow ? (float)$balanceRow['balance'] : 0.0;
        if ($currentBalance < AA_WOF_SPIN_COST) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'You need '.number_format(AA_WOF_SPIN_COST).' '.APP_CURRENCY_LONG_NAME.' to spin the wheel.',
                'cooldownRemaining' => 0,
            ]);
            exit;
        }

        $deductStmt = $pdo->prepare('UPDATE user_balances SET balance = balance - ? WHERE user_id = ? AND currency_id = ?');
        $deductStmt->execute([AA_WOF_SPIN_COST, $uid, AA_WOF_CURRENCY_ID]);
        if ($deductStmt->rowCount() === 0) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'You need '.number_format(AA_WOF_SPIN_COST).' '.APP_CURRENCY_LONG_NAME.' to spin the wheel.',
                'cooldownRemaining' => 0,
            ]);
            exit;
        }

        $ledgerStmt = $pdo->prepare('INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata) VALUES (?,?,?,?,?)');
        $ledgerStmt->execute([
            $uid,
            AA_WOF_CURRENCY_ID,
            -AA_WOF_SPIN_COST,
            'aa_wof_spin',
            json_encode(['cost' => AA_WOF_SPIN_COST]),
        ]);

        $segmentIndex = random_int(0, count($segments) - 1);
        $segment = $segments[$segmentIndex];
        $itemId = (int)$segment['item_id'];
        $quantity = (int)$segment['quantity'];

        $itemStmt = $pdo->prepare('INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)');
        $itemStmt->execute([$uid, $itemId, $quantity]);

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
                'quantity' => $quantity,
            ],
            'balances' => $balances,
            'cooldownRemaining' => 0,
        ]);
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        error_log('[aa-wof] spin failed: '.$e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Unable to complete spin: '.$e->getMessage(),
        ]);
    }
    exit;
}
?>
<link rel="stylesheet" href="assets/css/wheel-of-fate.css">
<script>
window.WHEEL_OF_FATE_ENDPOINT = 'index.php?pg=aa-wof';
window.WHEEL_OF_FATE_SEGMENTS = <?= json_encode($segments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
window.WHEEL_OF_FATE_STATE = <?= json_encode([
    'cost' => AA_WOF_SPIN_COST,
    'cooldownSeconds' => 0,
    'cooldownRemaining' => 0,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<script defer src="assets/js/wheel-of-fate.js"></script>

<h1>Wheel of Pizza Wheels</h1>
<p class="muted">Spin the wheel for a pizza haul. Each spin costs <?= number_format(AA_WOF_SPIN_COST) ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?>.</p>
<p class="muted small">Each slice of the wheel holds 1–8 pizzas. Bigger stacks weigh less on the wheel, so jackpots are rarer.</p>

<div class="wheel-of-fate-layout">
    <div class="wheel-stage">
        <canvas id="wheel-canvas" width="420" height="420" aria-label="Wheel of Fortune"></canvas>
        <div class="wheel-pointer" aria-hidden="true"></div>
    </div>
    <div class="wheel-controls">
        <button id="spin-button" class="btn primary" <?= empty($segments) ? 'disabled aria-disabled="true"' : '' ?>>Spin the Wheel</button>
        <div class="spin-cost muted">Cost: <?= number_format(AA_WOF_SPIN_COST) ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?></div>
        <div class="spin-cooldown muted">Next spin in: <span id="spin-cooldown">Ready</span></div>
        <div class="spin-timer muted">Stopping in: <span id="spin-timer">--</span>s</div>
        <div id="spin-result" class="spin-result muted" role="status"></div>
        <h2 class="prize-heading">Today's pizza prizes</h2>
        <ul class="prize-list">
            <?php foreach ($segments as $segment): ?>
                <li>
                    <span class="prize-type item">🍕</span>
                    <span class="prize-label"><?= htmlspecialchars($segment['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if (empty($segments)): ?>
            <p class="muted">Wheel unavailable: no eligible pizza prizes are configured right now.</p>
        <?php endif; ?>
    </div>
</div>
