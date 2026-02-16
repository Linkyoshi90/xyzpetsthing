<?php
require_login();

$uid = (int)(current_user()['id'] ?? 0);
$today = date('Y-m-d');
$dailyCompleted = false;
$fishReward = null;
$fishMiss = false;
$dailyError = null;
$isTempUser = is_temp_user();

if (!$isTempUser) {
    $dailyRow = q(
        'SELECT caught_item_id FROM daily_fom_fishing_runs WHERE user_id = ? AND run_date = ?',
        [$uid, $today]
    )->fetch(PDO::FETCH_ASSOC);
    $dailyCompleted = (bool)$dailyRow;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && !$isTempUser
    && !$dailyCompleted
    && isset($_POST['fom_daily_fishing'])
) {
    try {
        $pdo = db();
        $pdo->beginTransaction();

        $checkStmt = $pdo->prepare(
            'SELECT caught_item_id FROM daily_fom_fishing_runs WHERE user_id = ? AND run_date = ? FOR UPDATE'
        );
        $checkStmt->execute([$uid, $today]);
        if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->rollBack();
            $dailyCompleted = true;
        } else {
            $caught = random_int(0, 1) === 1;
            $caughtItem = null;

            if ($caught) {
                $itemStmt = $pdo->query(
                    "SELECT item_id, item_name, item_description FROM items WHERE item_name LIKE '%Fish%' ORDER BY RAND() LIMIT 1"
                );
                $caughtItem = $itemStmt->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($caughtItem) {
                    $inventoryStmt = $pdo->prepare(
                        'INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, 1) '
                        . 'ON DUPLICATE KEY UPDATE quantity = quantity + 1'
                    );
                    $inventoryStmt->execute([$uid, $caughtItem['item_id']]);

                    $fishReward = [
                        'name' => $caughtItem['item_name'],
                        'description' => $caughtItem['item_description'] ?? '',
                    ];
                } else {
                    $caught = false;
                }
            }

            $logStmt = $pdo->prepare(
                'INSERT INTO daily_fom_fishing_runs (user_id, run_date, caught_item_id) VALUES (?, ?, ?)'
            );
            $logStmt->execute([$uid, $today, $caughtItem['item_id'] ?? null]);

            $pdo->commit();
            $dailyCompleted = true;
            if (!$caught) {
                $fishMiss = true;
            }
        }
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $dailyError = 'The grachten are restless right now. Try again later.';
    }
}
?>
<section class="fom-fishing-page">
    <div class="fom-fishing-toplinks">
        <a class="btn" href="?pg=fom">← Back to Frankenondermeer</a>
        <a class="btn" href="?pg=rheinland">Back to Rheingard</a>
    </div>

    <div class="card glass">
        <h1>Grachten Fishing (Daily)</h1>
        <p class="muted">A free daily fishing session in the overflow moats of Frankenondermeer.</p>
        <img src="images/harmontide-fom-grachten.webp" alt="View of the grachten" class="world-map" />

        <?php if ($isTempUser): ?>
            <p class="muted">Create a full account to take part in daily grachten fishing.</p>
        <?php elseif ($dailyCompleted): ?>
            <p class="muted">You have already cast your line in the grachten today. Come back tomorrow.</p>
        <?php elseif ($dailyError): ?>
            <p class="muted"><?php echo htmlspecialchars($dailyError); ?></p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="fom_daily_fishing" value="1" />
                <button class="btn" type="submit">Cast a line</button>
            </form>
        <?php endif; ?>

        <?php if ($fishMiss): ?>
            <p class="muted">The line comes up empty this time, but the grachten whisper that tomorrow will be different.</p>
        <?php endif; ?>
    </div>
</section>

<?php if ($fishReward): ?>
<dialog id="grachten-fish-dialog">
    <div class="card glass">
        <h3>You caught a fish!</h3>
        <p><strong><?php echo htmlspecialchars($fishReward['name']); ?></strong></p>
        <?php if ($fishReward['description'] !== ''): ?>
            <p class="muted"><?php echo htmlspecialchars($fishReward['description']); ?></p>
        <?php else: ?>
            <p class="muted">A fresh catch from the grachten, slick with canal mist.</p>
        <?php endif; ?>
        <button class="btn" type="button" id="grachten-fish-close">Close</button>
    </div>
</dialog>
<script>
(function() {
    const dialog = document.getElementById('grachten-fish-dialog');
    const closeBtn = document.getElementById('grachten-fish-close');
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

<style>
.fom-fishing-page { max-width: 1100px; margin: 0 auto; }
.fom-fishing-toplinks { display: flex; gap: .6rem; flex-wrap: wrap; margin: .5rem 0 1rem; }
</style>
