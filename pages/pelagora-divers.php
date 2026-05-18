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
            'SELECT salvaged_item_id FROM daily_pelagora_salvage_runs WHERE user_id = ? AND run_date = ?',
            [$uid, $today]
        )->fetch(PDO::FETCH_ASSOC);
        $dailyCompleted = (bool)$dailyRow;
    } catch (Throwable $err) {
        $dailyError = 'The diver register is not ready yet. Run sql/pelagora_content.sql before booking a dive.';
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && !$isTempUser
    && !$dailyCompleted
    && $dailyError === null
    && isset($_POST['pelagora_daily_salvage'])
) {
    try {
        $pdo = db();
        $pdo->beginTransaction();

        $checkStmt = $pdo->prepare(
            'SELECT salvaged_item_id FROM daily_pelagora_salvage_runs WHERE user_id = ? AND run_date = ? FOR UPDATE'
        );
        $checkStmt->execute([$uid, $today]);
        if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->rollBack();
            $dailyCompleted = true;
        } else {
            $found = random_int(1, 100) <= 60;
            $salvagedItem = null;

            if ($found) {
                $itemStmt = $pdo->query(
                    "SELECT item_id, item_name, item_description FROM items "
                    . "WHERE category_id IN (4, 6) "
                    . "AND (item_name LIKE '%Aquatic%' OR item_name LIKE '%Pelagoric%' OR item_name LIKE '%Pelagora%' OR item_name LIKE '%Underwater%') "
                    . "ORDER BY RAND() LIMIT 1"
                );
                $salvagedItem = $itemStmt->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($salvagedItem) {
                    $inventoryStmt = $pdo->prepare(
                        'INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, 1) '
                        . 'ON DUPLICATE KEY UPDATE quantity = quantity + 1'
                    );
                    $inventoryStmt->execute([$uid, $salvagedItem['item_id']]);

                    $reward = [
                        'name' => $salvagedItem['item_name'],
                        'description' => $salvagedItem['item_description'] ?? '',
                    ];
                } else {
                    $found = false;
                }
            }

            $logStmt = $pdo->prepare(
                'INSERT INTO daily_pelagora_salvage_runs (user_id, run_date, salvaged_item_id) VALUES (?, ?, ?)'
            );
            $logStmt->execute([$uid, $today, $salvagedItem['item_id'] ?? null]);

            $pdo->commit();
            $dailyCompleted = true;
            if (!$found) {
                $miss = true;
            }
        }
    } catch (Throwable $err) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $dailyError = 'A swell crosses the floodstairs before the dive can begin. Try again later.';
    }
}
?>
<section class="pizza-shop">
    <header class="pizza-shop__header">
        <h1>Floodstairs Diver Guild (Daily)</h1>
        <p class="muted">Hire a licensed diver to search the lower galleries before the next tide turns.</p>
    </header>

    <div class="card glass">
        <img src="images/maps/harmontide-pelagora.webp" alt="Pelagora flooded lower galleries" class="world-map" />

        <?php if ($isTempUser): ?>
            <p class="muted">Create a full account to join the daily diver register.</p>
        <?php elseif ($dailyCompleted): ?>
            <p class="muted">The guild has already sent a diver for you today. Come back tomorrow.</p>
        <?php elseif ($dailyError): ?>
            <p class="muted"><?= htmlspecialchars($dailyError) ?></p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="pelagora_daily_salvage" value="1" />
                <button class="btn" type="submit">Book a dive</button>
            </form>
        <?php endif; ?>

        <?php if ($miss): ?>
            <p class="muted">The diver returns with empty hands, a careful map note, and salt in every seam.</p>
        <?php endif; ?>
    </div>
</section>

<?php if ($reward): ?>
<dialog id="pelagora-salvage-dialog">
    <div class="card glass">
        <h3>The diver found something!</h3>
        <p><strong><?= htmlspecialchars($reward['name']) ?></strong></p>
        <?php if ($reward['description'] !== ''): ?>
            <p class="muted"><?= htmlspecialchars($reward['description']) ?></p>
        <?php else: ?>
            <p class="muted">A salt-bright relic lifted from a flooded gallery.</p>
        <?php endif; ?>
        <button class="btn" type="button" id="pelagora-salvage-close">Close</button>
    </div>
</dialog>
<script>
(function() {
    const dialog = document.getElementById('pelagora-salvage-dialog');
    const closeBtn = document.getElementById('pelagora-salvage-close');
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
