<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';
require_once __DIR__.'/../lib/temp_user.php';

function is_gacha_post_request(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function gacha_prepare_json_request(): void
{
    if (!is_gacha_post_request()) {
        return;
    }

    ini_set('display_errors', '0');

    set_exception_handler(static function (Throwable $e): void {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo '{"success":false,"error":"Unable to complete the gacha spin."}';
        exit;
    });

    register_shutdown_function(static function (): void {
        $error = error_get_last();
        if (!$error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            return;
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo '{"success":false,"error":"Unable to complete the gacha spin."}';
    });
}

gacha_prepare_json_request();
require_login();

const GACHA_CURRENCY_ID = 1;
const GACHA_COST = 100;

$cost = GACHA_COST;
$itemSql = "SELECT item_id, item_name, base_price FROM items WHERE item_name NOT LIKE '%Paint%' AND item_id NOT IN (4,6,7,8,9,10,11,12,13,23,24) ORDER BY base_price descending";
$items = q($itemSql)->fetchAll(PDO::FETCH_ASSOC);
$itemsById = [];
foreach ($items as $item) {
    $itemsById[(int)$item['item_id']] = [
        'item_id' => (int)$item['item_id'],
        'item_name' => $item['item_name'],
        'base_price' => $item['base_price'],
    ];
}

function gacha_json_response(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }

    $flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE;
    $json = json_encode($payload, $flags);

    if ($json === false) {
        $json = '{"success":false,"error":"Unable to encode the gacha response."}';
        if (!headers_sent()) {
            http_response_code(500);
        }
    }

    echo $json;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if (!$itemsById) {
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
}
?>
<link rel="stylesheet" href="assets/css/gacha.css">
<section class="gacha-page">
  <header class="gacha-header">
    <div>
      <h1>Spin the capsule machine</h1>
      <p>Each spin costs <strong><?= number_format($cost) ?> <?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?></strong>. Loaded items come directly from the item pool and are added to your inventory immediately.</p>
    </div>
    <div class="gacha-cost"><?= number_format($cost) ?><span class="currency-label"><?= htmlspecialchars(APP_CURRENCY_LONG_NAME) ?></span></div>
  </header>

  <div class="gacha-stage">
    <div class="gacha-machine" id="gacha-machine" aria-label="Golden gacha machine">
      <div class="machine-top">
        <div class="machine-light"></div>
        <div class="machine-logo">⭐</div>
      </div>
      <div class="machine-window">
        <div class="window-glow"></div>
        <div class="capsule-outlet" id="capsule-outlet"></div>
      </div>
      <div class="machine-slot" aria-hidden="true">
        <div class="coin-slot"></div>
      </div>
      <div class="machine-handle" aria-hidden="true">
        <div class="handle-neck"></div>
        <div class="handle-knob"></div>
      </div>
      <div class="machine-base"></div>
      <div class="gacha-coin" id="gacha-coin" aria-hidden="true"></div>
    </div>

    <div class="gacha-actions">
      <p class="gacha-instruction">Insert a golden coin, watch the machine wiggle, then click the capsule to reveal the exact item that was already added to your inventory.</p>
      <button class="btn btn-primary" id="gacha-spin" type="button">Insert coin &amp; spin</button>
      <div class="capsule-area" id="capsule-area">
        <p class="capsule-hint">Capsules will roll out here. Click one to reveal your prize.</p>
      </div>
      <p id="gacha-status" class="capsule-hint" role="status" aria-live="polite"></p>
    </div>
  </div>

  <section class="gacha-pool">
    <div class="pool-heading">
      <h2>Item pool (preloaded from database)</h2>
      <p>Every spin chooses one of the items below, then awards that exact result to your inventory.</p>
    </div>
    <?php if ($items): ?>
      <ul class="pool-list">
        <?php foreach ($items as $item): ?>
          <li>
            <span class="pool-id">#<?= (int)$item['item_id'] ?></span>
            <span class="pool-name"><?= htmlspecialchars($item['item_name'] ?? 'Unknown item') ?></span>
            <span class="pool-price"><?= is_null($item['base_price']) ? 'No price set' : number_format((float)$item['base_price']) . ' ' . htmlspecialchars(APP_CURRENCY_LONG_NAME) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="pool-empty" role="alert">No items were found in the database for the gacha machine.</div>
    <?php endif; ?>
  </section>
</section>
<?php
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', rtrim(dirname($scriptName), '/'));
$gachaApiPath = ($scriptDir !== '' && $scriptDir !== '.') ? $scriptDir.'/gacha_spin.php' : '/gacha_spin.php';
$gachaScriptPath = __DIR__.'/../assets/js/gacha.js';
$gachaScriptVersion = file_exists($gachaScriptPath) ? (string)filemtime($gachaScriptPath) : '1';
?>
<script>
  window.gachaData = {
    cost: <?= json_encode($cost, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    endpoint: <?= json_encode($gachaApiPath, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    currencyLabel: <?= json_encode(APP_CURRENCY_LONG_NAME, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    items: <?= json_encode(array_values($itemsById), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
  };
</script>
<script defer src="assets/js/gacha.js?v=<?= urlencode($gachaScriptVersion) ?>"></script>
