<?php
require_once __DIR__.'/auth.php';
require_once __DIR__.'/db.php';
require_once __DIR__.'/lib/temp_user.php';
require_login();

const GACHA_CURRENCY_ID = 1;
const GACHA_COST = 100;

function gacha_json_response(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }

    $flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE;
    $json = json_encode($payload, $flags);

    if ($json === false) {
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo '{"success":false,"error":"Unable to encode the gacha response."}';
        exit;
    }

    echo $json;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    gacha_json_response(['success' => false, 'error' => 'Method not allowed.'], 405);
}

ini_set('display_errors', '0');
while (ob_get_level() > 0) {
    ob_end_clean();
}

$cost = GACHA_COST;
$itemSql = "SELECT item_id, item_name, base_price FROM items WHERE item_name NOT LIKE '%Paint%' AND item_id NOT IN (4,6,7,8,9,10,11,12,13,23,24) ORDER BY item_id";
$items = q($itemSql)->fetchAll(PDO::FETCH_ASSOC);

if (!$items) {
    gacha_json_response(['success' => false, 'error' => 'No gacha items are configured right now.'], 400);
}

$awardedItem = null;
$balances = [
    'cash' => (int)($_SESSION['user']['cash'] ?? 0),
    'gems' => (int)($_SESSION['user']['gems'] ?? 0),
];

try {
    $awardedItem = $items[array_rand($items)];
    $awardedItemId = (int)$awardedItem['item_id'];
    $uid = (int)(current_user()['id'] ?? 0);

    if (is_temp_user()) {
        $currentBalance = (float)temp_user_balance('cash');
        if ($currentBalance < $cost) {
            gacha_json_response([
                'success' => false,
                'error' => 'You need '.number_format($cost).' '.APP_CURRENCY_LONG_NAME.' to spin the gacha machine.',
            ], 400);
        }

        temp_user_adjust_balance('cash', -$cost);
        temp_user_add_inventory_item($awardedItemId, 1);
        $balances['cash'] = (int)temp_user_balance('cash');
        $balances['gems'] = (int)temp_user_balance('gems');
    } else {
        $pdo = db();
        if (!$pdo) {
            throw new RuntimeException('Database unavailable.');
        }

        $pdo->beginTransaction();

        $balanceStmt = $pdo->prepare('SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = ? FOR UPDATE');
        $balanceStmt->execute([$uid, GACHA_CURRENCY_ID]);
        $balanceRow = $balanceStmt->fetch(PDO::FETCH_ASSOC);
        $currentBalance = $balanceRow ? (float)$balanceRow['balance'] : 0.0;

        if ($currentBalance < $cost) {
            $pdo->rollBack();
            gacha_json_response([
                'success' => false,
                'error' => 'You need '.number_format($cost).' '.APP_CURRENCY_LONG_NAME.' to spin the gacha machine.',
            ], 400);
        }

        $deductStmt = $pdo->prepare('UPDATE user_balances SET balance = balance - ? WHERE user_id = ? AND currency_id = ?');
        $deductStmt->execute([$cost, $uid, GACHA_CURRENCY_ID]);
        if ($deductStmt->rowCount() === 0) {
            $pdo->rollBack();
            gacha_json_response([
                'success' => false,
                'error' => 'You need '.number_format($cost).' '.APP_CURRENCY_LONG_NAME.' to spin the gacha machine.',
            ], 400);
        }

        $ledgerStmt = $pdo->prepare('INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata) VALUES (?, ?, ?, ?, ?)');
        $ledgerStmt->execute([
            $uid,
            GACHA_CURRENCY_ID,
            -$cost,
            'yamanokubo_gacha_spin',
            json_encode(['cost' => $cost, 'item_id' => $awardedItemId]),
        ]);

        $itemStmt = $pdo->prepare('INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1');
        $itemStmt->execute([$uid, $awardedItemId]);
        if ($itemStmt->rowCount() === 0) {
            throw new RuntimeException('Inventory update failed.');
        }

        $pdo->commit();

        $balanceRows = q('SELECT currency_id, balance FROM user_balances WHERE user_id = ? AND currency_id IN (1,2)', [$uid])->fetchAll(PDO::FETCH_ASSOC);
        foreach ($balanceRows as $row) {
            if ((int)$row['currency_id'] === 1) {
                $balances['cash'] = (int)$row['balance'];
            } elseif ((int)$row['currency_id'] === 2) {
                $balances['gems'] = (int)$row['balance'];
            }
        }
        $_SESSION['user']['cash'] = $balances['cash'];
        $_SESSION['user']['gems'] = $balances['gems'];
    }

    gacha_json_response([
        'success' => true,
        'item' => [
            'item_id' => $awardedItemId,
            'item_name' => $awardedItem['item_name'],
            'base_price' => $awardedItem['base_price'],
        ],
        'balances' => $balances,
        'message' => 'You got '.$awardedItem['item_name'].'!',
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    gacha_json_response(['success' => false, 'error' => 'Unable to complete the gacha spin.'], 500);
}
