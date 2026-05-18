<?php
require_login();

$uid = (int)(current_user()['id'] ?? 0);
$today = date('Y-m-d');
$dailyCompleted = false;
$reward = null;
$miss = false;
$dailyError = null;
$isTempUser = is_temp_user();

if (!$isTempUser) {
    try {
        $dailyRow = q(
            'SELECT caught_item_id FROM daily_pelagora_fishing_runs WHERE user_id = ? AND run_date = ?',
            [$uid, $today]
        )->fetch(PDO::FETCH_ASSOC);
        $dailyCompleted = (bool)$dailyRow;
    } catch (Throwable $err) {
        $dailyError = 'The Heart Mirror fishing ledger is not ready yet. Run sql/pelagora_content.sql before casting here.';
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && !$isTempUser
    && !$dailyCompleted
    && $dailyError === null
    && isset($_POST['pelagora_daily_fishing'])
) {
    try {
        $pdo = db();
        $pdo->beginTransaction();

        $checkStmt = $pdo->prepare(
            'SELECT caught_item_id FROM daily_pelagora_fishing_runs WHERE user_id = ? AND run_date = ? FOR UPDATE'
        );
        $checkStmt->execute([$uid, $today]);
        if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->rollBack();
            $dailyCompleted = true;
        } else {
            $caught = random_int(1, 100) <= 70;
            $caughtItem = null;

            if ($caught) {
                $itemStmt = $pdo->query(
                    "SELECT item_id, item_name, item_description FROM items "
                    . "WHERE (item_name LIKE '%Mirrorfish%' OR item_name LIKE '%Bellfish%' OR item_name LIKE '%Ring Eel%') "
                    . "AND (item_name LIKE '%Aquatic%' OR item_name LIKE '%Pelagoric%' OR item_name LIKE '%Pelagora%' OR item_name LIKE '%Underwater%') "
                    . "ORDER BY RAND() LIMIT 1"
                );
                $caughtItem = $itemStmt->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($caughtItem) {
                    $inventoryStmt = $pdo->prepare(
                        'INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, 1) '
                        . 'ON DUPLICATE KEY UPDATE quantity = quantity + 1'
                    );
                    $inventoryStmt->execute([$uid, $caughtItem['item_id']]);

                    $reward = [
                        'name' => $caughtItem['item_name'],
                        'description' => $caughtItem['item_description'] ?? '',
                    ];
                } else {
                    $caught = false;
                }
            }

            $logStmt = $pdo->prepare(
                'INSERT INTO daily_pelagora_fishing_runs (user_id, run_date, caught_item_id) VALUES (?, ?, ?)'
            );
            $logStmt->execute([$uid, $today, $caughtItem['item_id'] ?? null]);

            $pdo->commit();
            $dailyCompleted = true;
            if (!$caught) {
                $miss = true;
            }
        }
    } catch (Throwable $err) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $dailyError = 'The Heart Mirror goes cloudy before your line settles. Try again later.';
    }
}
?>
<section class="pizza-shop">
    <header class="pizza-shop__header">
        <h1>Heart Mirror Fishing (Daily)</h1>
        <p class="muted">Cast into the calm central lagoon where old streets glimmer under the water.</p>
    </header>

    <div class="card glass">
        <img src="images/maps/harmontide-pelagora.webp" alt="Pelagora Heart Mirror lagoon" class="world-map" />

        <?php if ($isTempUser): ?>
            <p class="muted">Create a full account to take part in daily Heart Mirror fishing.</p>
        <?php elseif ($dailyCompleted): ?>
            <p class="muted">You have already cast your line in the Heart Mirror today. Come back tomorrow.</p>
        <?php elseif ($dailyError): ?>
            <p class="muted"><?= htmlspecialchars($dailyError) ?></p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="pelagora_daily_fishing" value="1" />
                <button class="btn" type="submit">Cast a line</button>
            </form>
        <?php endif; ?>

        <?php if ($miss): ?>
            <p class="muted">The hook returns with only bright lagoon weed and a drip of green light.</p>
        <?php endif; ?>
    </div>
</section>

<?php if ($reward): ?>
<dialog id="pelagora-fish-dialog">
    <div class="card glass">
        <h3>You caught something!</h3>
        <p><strong><?= htmlspecialchars($reward['name']) ?></strong></p>
        <?php if ($reward['description'] !== ''): ?>
            <p class="muted"><?= htmlspecialchars($reward['description']) ?></p>
        <?php else: ?>
            <p class="muted">A slick lagoon catch, cold from streets that sleep below the tide.</p>
        <?php endif; ?>
        <button class="btn" type="button" id="pelagora-fish-close">Close</button>
    </div>
</dialog>
<script>
(function() {
    const dialog = document.getElementById('pelagora-fish-dialog');
    const closeBtn = document.getElementById('pelagora-fish-close');
    if (!dialog || !closeBtn) {
        return;
    }
    if (!dialog.open && dialog.showModal) {
        dialog.showModal();
    }
    closeBtn.addEventListener('click', () => {
        dialog.close();
    });
})();
</script>
<?php endif; ?>
