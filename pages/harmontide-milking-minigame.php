<?php
require_once __DIR__.'/../auth.php';
require_login();
require_once __DIR__.'/../lib/map_unlocks.php';

$user = current_user();
if (!$user || !has_map_unlock((int)$user['id'], 'stillwater_hollow')) {
    echo '<div class="card glass"><h2>Road Not Found</h2><p class="muted">The repaired URB roads do not lead to Stillwater Creek yet.</p><p><a class="btn" href="?pg=urb">Back to Meridian Arc</a></p></div>';
    return;
}

$milk_presets = [
    [
        'id' => 'jackalope',
        'name' => 'Jackalope',
        'family' => 'URB Folk-Critter',
        'reward' => 'Jackalope Milk',
        'itemId' => 356,
        'surface' => 'antlers',
        'organ' => 'udder',
        'teats' => 2,
        'yield' => 0.00148,
        'bodyColor' => '#caa26b',
        'bodyShade' => '#6f4b2f',
        'organColor' => '#f1b6a6',
        'organShade' => '#c87979',
        'lineColor' => '#4b3025',
        'milkColor' => '#fff1c1',
        'milkShade' => '#d8a85f',
        'accentColor' => '#83c5be',
    ],
    [
        'id' => 'death',
        'name' => 'Death',
        'family' => 'URB Horseman',
        'reward' => 'Death Milk',
        'itemId' => 357,
        'surface' => 'void',
        'organ' => 'spectral-udder',
        'teats' => 4,
        'yield' => 0.00116,
        'bodyColor' => '#2f3345',
        'bodyShade' => '#0e111a',
        'organColor' => '#8a90a8',
        'organShade' => '#51576f',
        'lineColor' => '#141721',
        'milkColor' => '#dfe5ff',
        'milkShade' => '#8994c7',
        'accentColor' => '#c7f9cc',
    ],
    [
        'id' => 'fury',
        'name' => 'Fury',
        'family' => 'URB Horseman',
        'reward' => 'Fury Milk',
        'itemId' => 358,
        'surface' => 'flames',
        'organ' => 'ember-udder',
        'teats' => 2,
        'yield' => 0.00122,
        'bodyColor' => '#a83232',
        'bodyShade' => '#4b1111',
        'organColor' => '#f47b4d',
        'organShade' => '#b6402b',
        'lineColor' => '#421010',
        'milkColor' => '#ff7b39',
        'milkShade' => '#d92d20',
        'accentColor' => '#ffd166',
    ],
    [
        'id' => 'lich',
        'name' => 'Lich',
        'family' => 'URB Undead',
        'reward' => 'Lich Milk',
        'itemId' => 359,
        'surface' => 'runes',
        'organ' => 'spectral-udder',
        'teats' => 1,
        'yield' => 0.00108,
        'bodyColor' => '#7566a6',
        'bodyShade' => '#2d2548',
        'organColor' => '#b8a7df',
        'organShade' => '#7460aa',
        'lineColor' => '#251d3f',
        'milkColor' => '#d7c6ff',
        'milkShade' => '#8f76d6',
        'accentColor' => '#94f7c5',
    ],
    [
        'id' => 'jack-o-lantern',
        'name' => 'Jack-o-Lantern',
        'family' => 'URB Harvest Spirit',
        'reward' => 'Jack-o-Lantern Milk',
        'itemId' => 360,
        'surface' => 'pumpkin',
        'organ' => 'lantern-udder',
        'teats' => 3,
        'yield' => 0.00134,
        'bodyColor' => '#f28c28',
        'bodyShade' => '#7a3f12',
        'organColor' => '#ffb25b',
        'organShade' => '#d46a1f',
        'lineColor' => '#4a270f',
        'milkColor' => '#ffd27a',
        'milkShade' => '#f08a24',
        'accentColor' => '#2ec4b6',
    ],
    [
        'id' => 'pestilence',
        'name' => 'Pestilence',
        'family' => 'URB Horseman',
        'reward' => 'Pestilence Milk',
        'itemId' => 361,
        'surface' => 'miasma',
        'organ' => 'blister-pouch',
        'teats' => 1,
        'yield' => 0.00102,
        'bodyColor' => '#7f9f56',
        'bodyShade' => '#3c5426',
        'organColor' => '#b8d47a',
        'organShade' => '#6f8f3a',
        'lineColor' => '#2c3f1e',
        'milkColor' => '#bfff6a',
        'milkShade' => '#6fbf32',
        'accentColor' => '#d0f4de',
    ],
    [
        'id' => 'dairy-centaur-cynthia',
        'name' => 'Dairy Centaur Cynthia',
        'family' => 'URB Dairy Centaur',
        'reward' => 'Cynthia Milk',
        'itemId' => 90,
        'surface' => 'dairy-centaur',
        'organ' => 'cynthia-udder',
        'teats' => 1,
        'yield' => 0.00138,
        'bodyColor' => '#d8a16b',
        'bodyShade' => '#7b4b2a',
        'organColor' => '#ffd8c8',
        'organShade' => '#f59a92',
        'lineColor' => '#563021',
        'milkColor' => '#fff7df',
        'milkShade' => '#edcf91',
        'accentColor' => '#8fd3ff',
    ],
];

$json_flags = JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

function hmilk_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    exit;
}

function hmilk_presets_by_id(array $milk_presets): array
{
    $by_id = [];
    foreach ($milk_presets as $preset) {
        $by_id[(string)$preset['id']] = $preset;
    }
    return $by_id;
}

function hmilk_ensure_daily_table(?string &$error = null): bool
{
    $error = null;
    $pdo = db();
    if (!$pdo) {
        $error = 'The database connection is unavailable, so the bucket cannot be claimed yet.';
        return false;
    }

    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'daily_stillwater_milking_runs'");
        $stmt->execute();
        if ($stmt->fetchColumn()) {
            return true;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS daily_stillwater_milking_runs (
                user_id bigint UNSIGNED NOT NULL,
                run_date date NOT NULL,
                creature_id varchar(40) NOT NULL,
                item_id bigint UNSIGNED NOT NULL,
                completed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, run_date),
                KEY ix_daily_stillwater_milking_item (item_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $stmt = $pdo->prepare("SHOW TABLES LIKE 'daily_stillwater_milking_runs'");
        $stmt->execute();
        if ($stmt->fetchColumn()) {
            return true;
        }
    } catch (Throwable $e) {
        $error = 'The daily milking ledger could not be created. The milk items can exist while this one-per-day table is still missing.';
        return false;
    }

    $error = 'The daily milking ledger is missing. The milk items can exist while this one-per-day table is still missing.';
    return false;
}

function hmilk_daily_row(int $uid, string $today): ?array
{
    $row = q(
        'SELECT d.creature_id, d.item_id, d.completed_at, i.item_name
           FROM daily_stillwater_milking_runs d
           LEFT JOIN items i ON i.item_id = d.item_id
          WHERE d.user_id = ? AND d.run_date = ?',
        [$uid, $today]
    )->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

$uid = (int)($user['id'] ?? 0);
$today = date('Y-m-d');
$daily_table_ready = false;
$daily_completed = false;
$daily_reward = null;
$daily_error = null;

try {
    $daily_table_ready = hmilk_ensure_daily_table($daily_error);
    if ($daily_table_ready) {
        $daily_reward = hmilk_daily_row($uid, $today);
        $daily_completed = (bool)$daily_reward;
    }
} catch (Throwable $e) {
    $daily_error = 'The daily milking ledger could not be checked. The milk items can exist while this one-per-day table is still missing.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$daily_table_ready) {
        hmilk_json([
            'ok' => false,
            'error' => $daily_error ?: 'The daily milking ledger is not ready yet.',
        ], 503);
    }

    $raw = file_get_contents('php://input') ?: '';
    $payload = json_decode($raw, true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $action = (string)($payload['action'] ?? '');
    $preset_id = (string)($payload['presetId'] ?? '');
    $presets_by_id = hmilk_presets_by_id($milk_presets);

    if ($action !== 'claim_milk' || !isset($presets_by_id[$preset_id])) {
        hmilk_json(['ok' => false, 'error' => 'Invalid milking claim.'], 400);
    }

    $preset = $presets_by_id[$preset_id];
    $item_id = (int)$preset['itemId'];
    $reward_name = (string)$preset['reward'];

    try {
        $pdo = db();
        if (!$pdo) {
            throw new RuntimeException('Database unavailable.');
        }

        $pdo->beginTransaction();

        $check_stmt = $pdo->prepare(
            'SELECT d.creature_id, d.item_id, i.item_name
               FROM daily_stillwater_milking_runs d
               LEFT JOIN items i ON i.item_id = d.item_id
              WHERE d.user_id = ? AND d.run_date = ?
              FOR UPDATE'
        );
        $check_stmt->execute([$uid, $today]);
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $pdo->rollBack();
            hmilk_json([
                'ok' => false,
                'alreadyCollected' => true,
                'creatureId' => $existing['creature_id'],
                'itemId' => (int)$existing['item_id'],
                'rewardName' => (string)($existing['item_name'] ?: 'milk'),
                'message' => 'You have already collected milk today. Come back tomorrow.',
            ], 409);
        }

        $item_stmt = $pdo->prepare('SELECT item_id, item_name FROM items WHERE item_id = ? LIMIT 1');
        $item_stmt->execute([$item_id]);
        $item = $item_stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            $pdo->rollBack();
            hmilk_json([
                'ok' => false,
                'error' => 'Milk item #'.$item_id.' is missing from the database.',
            ], 503);
        }
        $reward_name = (string)($item['item_name'] ?: $reward_name);

        $log_stmt = $pdo->prepare(
            'INSERT INTO daily_stillwater_milking_runs (user_id, run_date, creature_id, item_id)
             VALUES (?, ?, ?, ?)'
        );
        $log_stmt->execute([$uid, $today, $preset_id, $item_id]);

        $inventory_stmt = $pdo->prepare(
            'INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE quantity = quantity + 1'
        );
        $inventory_stmt->execute([$uid, $item_id]);

        $pdo->commit();

        hmilk_json([
            'ok' => true,
            'creatureId' => $preset_id,
            'itemId' => $item_id,
            'rewardName' => $reward_name,
            'message' => $reward_name.' was added to your inventory. Come back tomorrow for more.',
        ]);
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($e->getCode() === '23000') {
            $existing = $daily_table_ready ? hmilk_daily_row($uid, $today) : null;
            hmilk_json([
                'ok' => false,
                'alreadyCollected' => true,
                'creatureId' => (string)($existing['creature_id'] ?? ''),
                'itemId' => (int)($existing['item_id'] ?? 0),
                'rewardName' => (string)($existing['item_name'] ?? 'milk'),
                'message' => 'You have already collected milk today. Come back tomorrow.',
            ], 409);
        }

        hmilk_json(['ok' => false, 'error' => 'The bucket could not be claimed right now. Try again.'], 500);
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        hmilk_json(['ok' => false, 'error' => 'The bucket could not be claimed right now. Try again.'], 500);
    }
}

$daily_config = [
    'completed' => $daily_completed,
    'creatureId' => (string)($daily_reward['creature_id'] ?? ''),
    'itemId' => isset($daily_reward['item_id']) ? (int)$daily_reward['item_id'] : 0,
    'rewardName' => (string)($daily_reward['item_name'] ?? ''),
    'message' => $daily_completed
        ? 'You have already collected '.(string)($daily_reward['item_name'] ?? 'milk').' today. Come back tomorrow.'
        : '',
    'ledgerReady' => $daily_table_ready,
    'error' => $daily_error,
    'claimUrl' => 'index.php?pg=harmontide-milking-minigame',
];
?>
<style>
.hmilk-page {
    display: grid;
    gap: 16px;
}

.hmilk-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.hmilk-heading h1 {
    margin-bottom: 0;
}

.hmilk-heading .muted {
    margin: 4px 0 0;
}

.hmilk-layout {
    display: grid;
    grid-template-columns: minmax(280px, 420px) minmax(240px, 1fr);
    align-items: start;
    gap: 18px;
}

.hmilk-stage-shell {
    position: sticky;
    top: 104px;
    display: grid;
    justify-items: center;
    gap: 10px;
}

.hmilk-stage {
    position: relative;
    width: min(100%, 420px, calc((100vh - 152px) * 9 / 16));
    aspect-ratio: 9 / 16;
    overflow: hidden;
    border: 3px solid rgba(14, 40, 54, 0.88);
    border-radius: 8px;
    background:
        radial-gradient(circle at 28% 9%, rgba(255, 255, 255, 0.95), transparent 20%),
        linear-gradient(180deg, #a6e7f2 0%, #d5f8de 46%, #f6c980 100%);
    box-shadow:
        0 18px 36px rgba(11, 76, 111, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.78);
    touch-action: none;
    user-select: none;
}

.hmilk-stage canvas {
    display: block;
    width: 100%;
    height: 100%;
}

.hmilk-stage:focus-visible {
    outline: none;
    box-shadow:
        0 18px 36px rgba(11, 76, 111, 0.24),
        0 0 0 4px rgba(30, 134, 255, 0.35),
        inset 0 1px 0 rgba(255, 255, 255, 0.78);
}

.hmilk-panel {
    display: grid;
    gap: 14px;
}

.hmilk-status,
.hmilk-preset-grid,
.hmilk-reward {
    border: 1px solid rgba(255, 255, 255, 0.72);
    border-radius: 10px;
    background:
        linear-gradient(135deg, rgba(255, 255, 255, 0.74), rgba(222, 249, 255, 0.44) 48%, rgba(232, 255, 236, 0.42)),
        var(--glass-bg);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.88),
        0 10px 22px rgba(41, 144, 212, 0.12);
}

.hmilk-status {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    padding: 10px;
}

.hmilk-stat {
    display: grid;
    gap: 4px;
    min-height: 58px;
    padding: 9px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.46);
}

.hmilk-stat small {
    color: var(--muted);
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0;
    text-transform: uppercase;
}

.hmilk-stat strong {
    overflow-wrap: anywhere;
    line-height: 1.15;
}

.hmilk-preset-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
    padding: 10px;
}

button.hmilk-preset {
    display: grid;
    grid-template-columns: 28px minmax(0, 1fr);
    align-items: center;
    gap: 8px;
    min-height: 60px;
    padding: 8px;
    border: 1px solid rgba(255, 255, 255, 0.72);
    border-radius: 8px;
    color: #173c4d;
    background:
        radial-gradient(circle at 28% 18%, rgba(255, 255, 255, 0.92), transparent 32%),
        linear-gradient(rgba(255, 255, 255, 0.8), rgba(196, 238, 248, 0.58));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.88),
        0 5px 12px rgba(34, 119, 168, 0.1);
    font: inherit;
    text-align: left;
    cursor: pointer;
    transition: transform 160ms ease, box-shadow 180ms ease, filter 180ms ease;
}

button.hmilk-preset:hover,
button.hmilk-preset:focus-visible,
button.hmilk-preset.is-active {
    transform: translateY(-1px);
    filter: saturate(1.08);
    outline: none;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.98),
        0 9px 18px rgba(34, 119, 168, 0.18),
        0 0 0 3px rgba(137, 230, 255, 0.18);
}

button.hmilk-preset.is-active {
    border-color: rgba(30, 134, 255, 0.48);
}

.hmilk-swatch {
    width: 26px;
    height: 26px;
    border: 2px solid rgba(16, 43, 58, 0.75);
    border-radius: 50%;
    box-shadow: inset 0 3px 0 rgba(255, 255, 255, 0.58);
}

.hmilk-preset-name {
    display: block;
    overflow-wrap: anywhere;
    color: var(--ink);
    font-weight: 900;
    line-height: 1.1;
}

.hmilk-preset-family {
    display: block;
    margin-top: 2px;
    color: var(--muted);
    font-size: 0.76rem;
    font-weight: 750;
}

.hmilk-reward {
    display: grid;
    grid-template-columns: 58px minmax(0, 1fr);
    gap: 12px;
    align-items: center;
    padding: 12px;
}

.hmilk-reward-icon {
    position: relative;
    width: 54px;
    height: 54px;
    border: 2px solid rgba(92, 67, 16, 0.8);
    border-radius: 12px 12px 16px 16px;
    background:
        linear-gradient(90deg, rgba(123, 82, 26, 0.85), rgba(220, 171, 61, 0.95), rgba(123, 82, 26, 0.82));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.45),
        0 8px 14px rgba(41, 71, 92, 0.14);
    overflow: hidden;
}

.hmilk-reward-icon::before {
    content: "";
    position: absolute;
    left: 6px;
    right: 6px;
    bottom: 7px;
    height: var(--fill, 0%);
    border-radius: 50% 50% 9px 9px / 10px 10px 8px 8px;
    background: linear-gradient(var(--milk, #fffaf0), var(--milk-shade, #efdca8));
    transition: height 180ms ease;
}

.hmilk-reward-icon::after {
    content: "";
    position: absolute;
    left: 3px;
    right: 3px;
    top: 7px;
    height: 9px;
    border: 2px solid rgba(92, 67, 16, 0.72);
    border-radius: 50%;
    background: rgba(255, 232, 153, 0.5);
}

.hmilk-reward strong,
.hmilk-reward span {
    display: block;
    overflow-wrap: anywhere;
}

.hmilk-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.hmilk-actions .btn {
    margin: 0;
}

.hmilk-meter {
    width: min(100%, 420px);
    height: 12px;
    border: 1px solid rgba(255, 255, 255, 0.76);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.48);
    box-shadow: inset 0 1px 2px rgba(24, 80, 118, 0.12);
    overflow: hidden;
}

.hmilk-meter span {
    display: block;
    width: var(--fill, 0%);
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, var(--milk, #fffaf0), var(--accent, #75c6ed));
    transition: width 120ms ease;
}

html[data-theme="dark"] .hmilk-status,
html[data-theme="dark"] .hmilk-preset-grid,
html[data-theme="dark"] .hmilk-reward {
    border-color: rgba(219, 250, 255, 0.22);
    background:
        linear-gradient(135deg, rgba(255, 255, 255, 0.14), rgba(94, 207, 236, 0.12)),
        var(--glass-bg);
}

html[data-theme="dark"] button.hmilk-preset {
    color: var(--ink);
    border-color: rgba(219, 250, 255, 0.22);
    background:
        radial-gradient(circle at 28% 18%, rgba(255, 255, 255, 0.18), transparent 32%),
        linear-gradient(rgba(255, 255, 255, 0.12), rgba(77, 159, 189, 0.24));
}

@media (max-width: 1080px) {
    .hmilk-layout {
        grid-template-columns: 1fr;
    }

    .hmilk-stage-shell {
        position: static;
    }

    .hmilk-panel {
        order: -1;
    }
}

@media (max-width: 640px) {
    .hmilk-status,
    .hmilk-preset-grid {
        grid-template-columns: 1fr;
    }

    .hmilk-reward {
        grid-template-columns: 48px minmax(0, 1fr);
    }

    .hmilk-reward-icon {
        width: 46px;
        height: 46px;
    }
}
</style>

<section class="hmilk-page">
  <div class="hmilk-heading">
    <div>
      <h1>Stillwater Creek Milking Station</h1>
      <p class="muted">A deeply normal portrait minigame from the less-repaired edge of the URB.</p>
    </div>
    <a class="btn ghost" href="?pg=stillwater-hollow">Back to Stillwater Creek</a>
  </div>

  <div class="hmilk-layout">
    <div class="hmilk-stage-shell">
      <div class="hmilk-stage" id="hmilk-stage" tabindex="0" aria-label="Harmontide milking minigame">
        <canvas id="hmilk-canvas" width="450" height="800"></canvas>
      </div>
      <div class="hmilk-meter" aria-hidden="true"><span id="hmilk-fill-meter"></span></div>
    </div>

    <div class="hmilk-panel">
      <div class="hmilk-status" aria-live="polite">
        <div class="hmilk-stat">
          <small>Creature</small>
          <strong id="hmilk-current-name"></strong>
        </div>
        <div class="hmilk-stat">
          <small>Lineage</small>
          <strong id="hmilk-current-family"></strong>
        </div>
        <div class="hmilk-stat">
          <small>Bucket</small>
          <strong id="hmilk-current-fill">0%</strong>
        </div>
      </div>

      <div class="hmilk-preset-grid" id="hmilk-presets" aria-label="Creature presets"></div>

      <div class="hmilk-reward">
        <span class="hmilk-reward-icon" id="hmilk-reward-icon" aria-hidden="true"></span>
        <span>
          <strong id="hmilk-reward-name"></strong>
          <span class="muted" id="hmilk-reward-state">Unfilled bucket</span>
        </span>
      </div>

      <div class="hmilk-actions">
        <button class="btn" type="button" id="hmilk-reset">Reset Bucket</button>
        <button class="btn ghost" type="button" id="hmilk-random">Random Preset</button>
      </div>
    </div>
  </div>
</section>

<script>
window.harmontideMilkingPresets = <?= json_encode($milk_presets, $json_flags) ?>;
window.harmontideMilkingDaily = <?= json_encode($daily_config, $json_flags) ?>;
</script>
<script>
(function () {
    const presets = Array.isArray(window.harmontideMilkingPresets) ? window.harmontideMilkingPresets : [];
    const dailyState = Object.assign({
        completed: false,
        creatureId: '',
        itemId: 0,
        rewardName: '',
        message: '',
        ledgerReady: true,
        error: '',
        claimUrl: 'index.php?pg=harmontide-milking-minigame'
    }, window.harmontideMilkingDaily || {});
    const stage = document.getElementById('hmilk-stage');
    const canvas = document.getElementById('hmilk-canvas');
    const presetList = document.getElementById('hmilk-presets');
    const resetButton = document.getElementById('hmilk-reset');
    const randomButton = document.getElementById('hmilk-random');
    const currentName = document.getElementById('hmilk-current-name');
    const currentFamily = document.getElementById('hmilk-current-family');
    const currentFill = document.getElementById('hmilk-current-fill');
    const rewardName = document.getElementById('hmilk-reward-name');
    const rewardState = document.getElementById('hmilk-reward-state');
    const rewardIcon = document.getElementById('hmilk-reward-icon');
    const fillMeter = document.getElementById('hmilk-fill-meter');

    if (!presets.length || !stage || !canvas || !presetList) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const W = canvas.width;
    const H = canvas.height;
    const bucket = { x: W / 2, y: 616, topW: 156, bottomW: 112, h: 118 };
    const gravity = 0.22;
    const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
    const lerp = (a, b, t) => a + (b - a) * t;
    const distance = (a, b, x, y) => Math.hypot(a - x, b - y);

    let activeIndex = 0;
    let fill = 0;
    let pulls = 0;
    let mode = 'ready';
    let t = 0;
    let lastFrame = 0;
    let particles = [];
    let splashes = [];
    let confetti = [];
    let hand = { x: W * 0.58, y: H * 0.44, inside: false };
    let drag = { active: false, teat: -1, lastY: 0, stretch: 0, pressure: 0 };
    let teatOffsets = [];
    let teatVelocity = [];
    let claimStatus = dailyState.completed ? 'claimed' : (dailyState.ledgerReady ? 'idle' : 'blocked');
    let claimMessage = dailyState.message || dailyState.error || '';
    let claimRequested = false;

    function preset() {
        return presets[activeIndex] || presets[0];
    }

    function stationLocked() {
        return dailyState.completed || claimStatus === 'pending';
    }

    function controlsLocked() {
        return stationLocked() || (mode === 'complete' && claimStatus !== 'error');
    }

    function setDailyCompleted(data) {
        dailyState.completed = true;
        dailyState.creatureId = data.creatureId || dailyState.creatureId || preset().id;
        dailyState.itemId = data.itemId || dailyState.itemId || preset().itemId || 0;
        dailyState.rewardName = data.rewardName || dailyState.rewardName || preset().reward;
        dailyState.message = data.message || ('You collected ' + dailyState.rewardName + '. Come back tomorrow.');
        claimStatus = 'claimed';
        claimMessage = dailyState.message;
        mode = 'claimed';
        fill = 1;
        const claimedIndex = presets.findIndex((candidate) => candidate.id === dailyState.creatureId);
        if (claimedIndex >= 0) {
            activeIndex = claimedIndex;
            resetTeats();
        }
    }

    function setCssVars(c) {
        const fillPct = Math.round(fill * 100) + '%';
        [rewardIcon, fillMeter].forEach((node) => {
            if (!node) return;
            node.style.setProperty('--fill', fillPct);
            node.style.setProperty('--milk', c.milkColor);
            node.style.setProperty('--milk-shade', c.milkShade);
            node.style.setProperty('--accent', c.accentColor);
        });
    }

    function updatePanel() {
        const c = preset();
        currentName.textContent = c.name;
        currentFamily.textContent = c.family;
        currentFill.textContent = dailyState.completed ? 'Claimed' : Math.round(fill * 100) + '%';
        rewardName.textContent = dailyState.completed && dailyState.rewardName
            ? 'Collected: ' + dailyState.rewardName
            : 'Bucket of ' + c.reward;
        if (claimStatus === 'pending') {
            rewardState.textContent = 'Adding milk to inventory...';
        } else if (claimStatus === 'claimed') {
            rewardState.textContent = claimMessage || 'Come back tomorrow.';
        } else if (claimStatus === 'error' || claimStatus === 'blocked') {
            rewardState.textContent = claimMessage || 'The milking ledger is not ready.';
        } else {
            rewardState.textContent = fill >= 1 ? 'Ready to claim' : (fill > 0 ? 'Filling' : 'Unfilled bucket');
        }
        setCssVars(c);
        presetList.querySelectorAll('.hmilk-preset').forEach((button, index) => {
            button.classList.toggle('is-active', index === activeIndex);
            button.setAttribute('aria-pressed', index === activeIndex ? 'true' : 'false');
            button.disabled = controlsLocked();
        });
        if (resetButton) resetButton.disabled = controlsLocked();
        if (randomButton) randomButton.disabled = controlsLocked();
    }

    function buildPresetButtons() {
        presetList.innerHTML = '';
        presets.forEach((c, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'hmilk-preset';
            button.innerHTML =
                '<span class="hmilk-swatch" aria-hidden="true"></span>' +
                '<span><span class="hmilk-preset-name"></span><span class="hmilk-preset-family"></span></span>';
            button.querySelector('.hmilk-swatch').style.background =
                'linear-gradient(135deg, #fff, ' + c.milkColor + ' 45%, ' + c.milkShade + ')';
            button.querySelector('.hmilk-preset-name').textContent = c.name;
            button.querySelector('.hmilk-preset-family').textContent = c.family;
            button.addEventListener('click', () => selectPreset(index));
            presetList.appendChild(button);
        });
    }

    function resetTeats() {
        const c = preset();
        teatOffsets = Array.from({ length: c.teats }, () => 0);
        teatVelocity = Array.from({ length: c.teats }, () => 0);
    }

    function selectPreset(index) {
        if (controlsLocked()) return;
        activeIndex = ((index % presets.length) + presets.length) % presets.length;
        fill = 0;
        pulls = 0;
        mode = 'ready';
        claimStatus = dailyState.ledgerReady ? 'idle' : 'blocked';
        claimMessage = dailyState.error || '';
        claimRequested = false;
        drag.active = false;
        particles = [];
        splashes = [];
        confetti = [];
        resetTeats();
        updatePanel();
        stage.focus({ preventScroll: true });
    }

    function resetBucket() {
        if (controlsLocked()) return;
        fill = 0;
        pulls = 0;
        mode = 'ready';
        claimStatus = dailyState.ledgerReady ? 'idle' : 'blocked';
        claimMessage = dailyState.error || '';
        claimRequested = false;
        particles = [];
        splashes = [];
        confetti = [];
        resetTeats();
        updatePanel();
    }

    function rand(min, max) {
        return min + Math.random() * (max - min);
    }

    function colorWithAlpha(hex, alpha) {
        const value = hex.replace('#', '');
        const r = parseInt(value.slice(0, 2), 16);
        const g = parseInt(value.slice(2, 4), 16);
        const b = parseInt(value.slice(4, 6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    function roundedRect(x, y, w, h, r) {
        const radius = Math.min(r, w / 2, h / 2);
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.arcTo(x + w, y, x + w, y + h, radius);
        ctx.arcTo(x + w, y + h, x, y + h, radius);
        ctx.arcTo(x, y + h, x, y, radius);
        ctx.arcTo(x, y, x + w, y, radius);
        ctx.closePath();
    }

    function ellipse(x, y, rx, ry, fill, stroke, lineWidth) {
        ctx.beginPath();
        ctx.ellipse(x, y, rx, ry, 0, 0, Math.PI * 2);
        ctx.fillStyle = fill;
        ctx.fill();
        if (stroke) {
            ctx.lineWidth = lineWidth || 3;
            ctx.strokeStyle = stroke;
            ctx.stroke();
        }
    }

    function getCanvasPoint(event) {
        const rect = canvas.getBoundingClientRect();
        const point = event.touches && event.touches.length ? event.touches[0] : event;
        return {
            x: (point.clientX - rect.left) * W / rect.width,
            y: (point.clientY - rect.top) * H / rect.height,
        };
    }

    function teatPositions(c) {
        if (c.organ === 'mantle-siphon') {
            return [{ x: W / 2 + 6, y: 360, r: 25, length: 56, width: 19 }];
        }
        if (c.organ === 'feather-crop') {
            return [{ x: W / 2, y: 357, r: 27, length: 42, width: 16 }];
        }
        if (c.organ === 'ray-vents') {
            return [
                { x: W / 2 - 45, y: 356, r: 25, length: 34, width: 14 },
                { x: W / 2, y: 367, r: 25, length: 38, width: 14 },
                { x: W / 2 + 45, y: 356, r: 25, length: 34, width: 14 },
            ];
        }
        if (c.organ === 'cynthia-udder') {
            return [{ x: W / 2, y: 440, r: 30, length: 32, width: 27 }];
        }

        const spread = c.teats === 1 ? 0 : 98;
        return Array.from({ length: c.teats }, (_, index) => {
            const ratio = c.teats === 1 ? 0.5 : index / (c.teats - 1);
            const x = W / 2 - spread / 2 + spread * ratio;
            const y = 360 + Math.sin(ratio * Math.PI) * 12;
            return {
                x,
                y,
                r: 25,
                length: c.organ === 'gill-pouch' ? 36 : 48,
                width: c.organ === 'scale-pouch' ? 19 : 16,
            };
        });
    }

    function findTeat(point) {
        const c = preset();
        const positions = teatPositions(c);
        let nearest = -1;
        let nearestDistance = Infinity;
        positions.forEach((pos, index) => {
            const currentOffset = teatOffsets[index] || 0;
            const d = distance(point.x, point.y, pos.x, pos.y + pos.length * 0.72 + currentOffset);
            if (d < pos.r && d < nearestDistance) {
                nearest = index;
                nearestDistance = d;
            }
        });
        return nearest;
    }

    function spawnMilk(index, amount) {
        const c = preset();
        const pos = teatPositions(c)[index] || teatPositions(c)[0];
        if (!pos) return;
        const offset = teatOffsets[index] || 0;
        const tipY = pos.y + pos.length + offset;
        const count = clamp(Math.ceil(amount * 22), 2, 8);
        for (let i = 0; i < count; i += 1) {
            particles.push({
                x: pos.x + rand(-4, 4),
                y: tipY + rand(-2, 5),
                vx: rand(-1.6, 1.6),
                vy: rand(3.4, 6.4),
                r: rand(3, 7),
                life: 1,
                color: Math.random() > 0.24 ? c.milkColor : c.milkShade,
            });
        }
    }

    function spawnSplash(x, y) {
        const c = preset();
        for (let i = 0; i < 7; i += 1) {
            splashes.push({
                x,
                y,
                vx: rand(-3.6, 3.6),
                vy: rand(-4.6, -1.4),
                r: rand(2, 5),
                life: 1,
                color: Math.random() > 0.35 ? c.milkColor : c.milkShade,
            });
        }
    }

    function spawnConfetti() {
        const colors = ['#ffcf56', '#69d2e7', '#f06449', '#8ee3b5', '#b99adf', '#fff7de'];
        for (let i = 0; i < 70; i += 1) {
            const angle = rand(-Math.PI, 0);
            const speed = rand(2, 8);
            confetti.push({
                x: W / 2 + rand(-70, 70),
                y: H * 0.48,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed - rand(1, 4),
                r: rand(4, 8),
                rot: rand(0, Math.PI * 2),
                spin: rand(-0.16, 0.16),
                life: 1,
                color: colors[Math.floor(rand(0, colors.length))],
            });
        }
    }

    function claimReward(c) {
        if (dailyState.completed || claimRequested) {
            return;
        }

        claimRequested = true;
        claimStatus = 'pending';
        claimMessage = 'Adding ' + c.reward + ' to your inventory...';
        updatePanel();

        fetch(dailyState.claimUrl || 'index.php?pg=harmontide-milking-minigame', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'claim_milk',
                presetId: c.id
            })
        })
            .then((response) => response.text().then((text) => {
                let data = {};
                try {
                    data = text ? JSON.parse(text) : {};
                } catch (error) {
                    data = { ok: false, error: 'The bucket claim returned an unreadable response. Try again.' };
                }
                return { ok: response.ok, data };
            }))
            .then(({ ok, data }) => {
                if ((ok && data && data.ok) || (data && data.alreadyCollected)) {
                    setDailyCompleted(data || {});
                    return;
                }
                throw new Error((data && data.error) || 'The bucket could not be claimed right now.');
            })
            .catch((error) => {
                claimRequested = false;
                claimStatus = 'error';
                claimMessage = error && error.message ? error.message : 'The bucket could not be claimed right now.';
            })
            .finally(updatePanel);
    }

    function onPointerDown(event) {
        event.preventDefault();
        const point = getCanvasPoint(event);
        hand = { x: point.x, y: point.y, inside: true };
        if (mode === 'complete') {
            if (claimStatus === 'error') {
                claimReward(preset());
            }
            return;
        }
        if (mode === 'claimed' || stationLocked()) {
            return;
        }
        const teat = findTeat(point);
        if (teat < 0) return;
        mode = 'milking';
        drag.active = true;
        drag.teat = teat;
        drag.lastY = point.y;
        drag.stretch = teatOffsets[teat] || 0;
        drag.pressure = 0;
        stage.focus({ preventScroll: true });
    }

    function onPointerMove(event) {
        const point = getCanvasPoint(event);
        hand = { x: point.x, y: point.y, inside: true };
        if (!drag.active || mode === 'complete') return;
        event.preventDefault();

        const dy = point.y - drag.lastY;
        drag.lastY = point.y;
        if (dy > 0) {
            drag.pressure = clamp(drag.pressure + dy, 0, 118);
            drag.stretch = clamp(drag.stretch + dy * 0.74, 0, 76);
            teatOffsets[drag.teat] = drag.stretch;
            teatVelocity[drag.teat] = 0;
            const gain = dy * preset().yield * (0.7 + drag.pressure / 160);
            fill = clamp(fill + gain, 0, 1);
            spawnMilk(drag.teat, dy / 18);
            updatePanel();
            if (fill >= 1 && mode !== 'complete') {
                mode = 'complete';
                drag.active = false;
                pulls += 1;
                spawnConfetti();
                claimReward(preset());
                updatePanel();
            }
        } else if (dy < 0) {
            drag.pressure = clamp(drag.pressure + dy * 0.4, 0, 118);
            drag.stretch = clamp(drag.stretch + dy * 0.24, 0, 76);
            teatOffsets[drag.teat] = drag.stretch;
        }
    }

    function onPointerUp(event) {
        if (event) {
            event.preventDefault();
        }
        if (drag.active) {
            pulls += 1;
            teatVelocity[drag.teat] = -7.2;
        }
        drag.active = false;
        drag.teat = -1;
        drag.pressure = 0;
    }

    function onPointerLeave() {
        hand.inside = false;
        onPointerUp();
    }

    function update(delta) {
        t += delta;
        const c = preset();
        const positions = teatPositions(c);
        positions.forEach((_, index) => {
            if (drag.active && drag.teat === index) return;
            const offset = teatOffsets[index] || 0;
            const spring = -offset * 0.15;
            teatVelocity[index] = (teatVelocity[index] || 0) + spring;
            teatVelocity[index] *= 0.72;
            teatOffsets[index] = offset + teatVelocity[index];
            if (Math.abs(teatOffsets[index]) < 0.1 && Math.abs(teatVelocity[index]) < 0.1) {
                teatOffsets[index] = 0;
                teatVelocity[index] = 0;
            }
        });

        for (let i = particles.length - 1; i >= 0; i -= 1) {
            const p = particles[i];
            p.x += p.vx;
            p.y += p.vy;
            p.vy += gravity;
            p.life -= 0.012;
            if (p.y > bucket.y + 10 && Math.abs(p.x - bucket.x) < bucket.topW / 2) {
                spawnSplash(p.x, bucket.y + 6);
                particles.splice(i, 1);
            } else if (p.life <= 0 || p.y > H + 30) {
                particles.splice(i, 1);
            }
        }

        for (let i = splashes.length - 1; i >= 0; i -= 1) {
            const p = splashes[i];
            p.x += p.vx;
            p.y += p.vy;
            p.vy += 0.18;
            p.life -= 0.035;
            if (p.life <= 0) splashes.splice(i, 1);
        }

        for (let i = confetti.length - 1; i >= 0; i -= 1) {
            const p = confetti[i];
            p.x += p.vx;
            p.y += p.vy;
            p.vy += 0.12;
            p.rot += p.spin;
            p.life -= 0.008;
            if (p.life <= 0 || p.y > H + 40) confetti.splice(i, 1);
        }
    }

    function drawBackground() {
        const sky = ctx.createLinearGradient(0, 0, 0, H);
        sky.addColorStop(0, '#9de7f0');
        sky.addColorStop(0.43, '#d4f7df');
        sky.addColorStop(1, '#f2ba77');
        ctx.fillStyle = sky;
        ctx.fillRect(0, 0, W, H);

        ctx.fillStyle = 'rgba(255, 255, 255, 0.46)';
        ellipse(76, 102, 58, 18, 'rgba(255,255,255,0.58)');
        ellipse(122, 94, 74, 23, 'rgba(255,255,255,0.5)');
        ellipse(338, 86, 66, 20, 'rgba(255,255,255,0.54)');

        ctx.fillStyle = '#82c778';
        ctx.beginPath();
        ctx.moveTo(0, 534);
        ctx.bezierCurveTo(92, 492, 150, 555, 238, 522);
        ctx.bezierCurveTo(320, 490, 372, 526, W, 502);
        ctx.lineTo(W, H);
        ctx.lineTo(0, H);
        ctx.closePath();
        ctx.fill();

        ctx.fillStyle = '#f1cf83';
        ctx.fillRect(0, 582, W, H - 582);
        ctx.strokeStyle = 'rgba(129, 89, 35, 0.3)';
        ctx.lineWidth = 2;
        for (let y = 610; y < H; y += 34) {
            ctx.beginPath();
            ctx.moveTo(0, y + Math.sin(t * 2 + y) * 2);
            ctx.lineTo(W, y - 8 + Math.cos(t * 2 + y) * 2);
            ctx.stroke();
        }
        for (let x = -40; x < W; x += 92) {
            ctx.beginPath();
            ctx.moveTo(x, 582);
            ctx.lineTo(x + 58, H);
            ctx.stroke();
        }
    }

    function drawHud(c) {
        roundedRect(18, 18, W - 36, 58, 16);
        ctx.fillStyle = 'rgba(255,255,255,0.68)';
        ctx.fill();
        ctx.strokeStyle = 'rgba(29,79,104,0.28)';
        ctx.lineWidth = 2;
        ctx.stroke();

        ellipse(48, 47, 16, 16, c.milkColor, c.lineColor, 2);
        ctx.fillStyle = '#173c4d';
        ctx.font = '900 19px Inter, Segoe UI, Arial, sans-serif';
        ctx.textBaseline = 'middle';
        ctx.fillText(c.name, 72, 39);
        ctx.fillStyle = 'rgba(23,60,77,0.68)';
        ctx.font = '800 12px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText(c.family, 72, 59);

        roundedRect(W - 148, 38, 112, 13, 8);
        ctx.fillStyle = 'rgba(80,122,139,0.16)';
        ctx.fill();
        roundedRect(W - 148, 38, 112 * fill, 13, 8);
        const meter = ctx.createLinearGradient(W - 148, 38, W - 36, 38);
        meter.addColorStop(0, c.milkColor);
        meter.addColorStop(1, c.accentColor);
        ctx.fillStyle = meter;
        ctx.fill();
    }

    function drawAnimal(c) {
        const bob = Math.sin(t * 2) * 3;
        const cx = W / 2;
        const bodyY = 214 + bob;

        ctx.save();
        ctx.shadowColor = 'rgba(38, 74, 84, 0.24)';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetY = 8;
        ellipse(cx, bodyY + 14, 174, 116, c.bodyColor, c.lineColor, 5);
        ctx.shadowOffsetY = 0;

        if (c.surface === 'spotted') {
            ellipse(cx - 76, bodyY - 22, 38, 25, c.bodyShade);
            ellipse(cx + 64, bodyY - 36, 42, 29, c.bodyShade);
            ellipse(cx + 8, bodyY + 36, 48, 24, c.bodyShade);
        } else if (c.surface === 'antlers') {
            ctx.strokeStyle = c.lineColor;
            ctx.lineWidth = 8;
            ctx.lineCap = 'round';
            [-1, 1].forEach((side) => {
                ctx.beginPath();
                ctx.moveTo(cx + side * 70, bodyY - 82);
                ctx.lineTo(cx + side * 92, bodyY - 132);
                ctx.lineTo(cx + side * 118, bodyY - 150);
                ctx.moveTo(cx + side * 92, bodyY - 132);
                ctx.lineTo(cx + side * 74, bodyY - 158);
                ctx.moveTo(cx + side * 105, bodyY - 140);
                ctx.lineTo(cx + side * 108, bodyY - 170);
                ctx.stroke();
            });
            ellipse(cx - 62, bodyY - 24, 28, 20, colorWithAlpha('#ffffff', 0.2));
            ellipse(cx + 50, bodyY + 30, 32, 18, colorWithAlpha(c.bodyShade, 0.45));
        } else if (c.surface === 'void') {
            ctx.fillStyle = colorWithAlpha('#ffffff', 0.18);
            for (let i = 0; i < 18; i += 1) {
                const sx = cx - 120 + (i * 43) % 240;
                const sy = bodyY - 74 + (i * 29) % 132;
                ctx.beginPath();
                ctx.arc(sx, sy, 1.6 + (i % 3), 0, Math.PI * 2);
                ctx.fill();
            }
            ctx.strokeStyle = colorWithAlpha(c.accentColor, 0.38);
            ctx.lineWidth = 4;
            ctx.beginPath();
            ctx.arc(cx, bodyY + 4, 74 + Math.sin(t * 2) * 5, 0.18, Math.PI * 1.24);
            ctx.stroke();
        } else if (c.surface === 'flames') {
            for (let i = -4; i <= 4; i += 1) {
                ctx.beginPath();
                ctx.moveTo(cx + i * 28, bodyY - 78 + Math.abs(i) * 4);
                ctx.quadraticCurveTo(cx + i * 28 + 18, bodyY - 122, cx + i * 28 + 4, bodyY - 154 + Math.sin(t * 5 + i) * 8);
                ctx.quadraticCurveTo(cx + i * 28 - 16, bodyY - 116, cx + i * 28, bodyY - 78 + Math.abs(i) * 4);
                ctx.fillStyle = i % 2 ? c.accentColor : '#f25f5c';
                ctx.fill();
                ctx.strokeStyle = c.lineColor;
                ctx.lineWidth = 3;
                ctx.stroke();
            }
        } else if (c.surface === 'runes') {
            ctx.strokeStyle = colorWithAlpha(c.accentColor, 0.62);
            ctx.lineWidth = 3;
            for (let i = 0; i < 8; i += 1) {
                const sx = cx - 98 + i * 28;
                const sy = bodyY - 32 + Math.sin(i) * 44;
                ctx.beginPath();
                ctx.moveTo(sx - 5, sy - 10);
                ctx.lineTo(sx + 5, sy + 10);
                ctx.moveTo(sx + 7, sy - 9);
                ctx.lineTo(sx - 6, sy + 2);
                ctx.stroke();
            }
        } else if (c.surface === 'pumpkin') {
            ctx.strokeStyle = colorWithAlpha(c.bodyShade, 0.52);
            ctx.lineWidth = 5;
            for (let i = -3; i <= 3; i += 1) {
                ctx.beginPath();
                ctx.ellipse(cx + i * 24, bodyY + 4, 34, 98, 0, 0, Math.PI * 2);
                ctx.stroke();
            }
            ctx.fillStyle = c.bodyShade;
            ctx.beginPath();
            ctx.moveTo(cx - 50, bodyY - 22);
            ctx.lineTo(cx - 20, bodyY - 8);
            ctx.lineTo(cx - 48, bodyY + 5);
            ctx.closePath();
            ctx.fill();
            ctx.beginPath();
            ctx.moveTo(cx + 50, bodyY - 22);
            ctx.lineTo(cx + 20, bodyY - 8);
            ctx.lineTo(cx + 48, bodyY + 5);
            ctx.closePath();
            ctx.fill();
            roundedRect(cx - 44, bodyY + 34, 88, 15, 5);
            ctx.fill();
        } else if (c.surface === 'miasma') {
            ctx.fillStyle = colorWithAlpha(c.accentColor, 0.22);
            for (let i = 0; i < 10; i += 1) {
                ellipse(cx - 132 + i * 31, bodyY - 70 + Math.sin(t * 2 + i) * 26, 18, 8, colorWithAlpha(c.accentColor, 0.18));
            }
            for (let i = 0; i < 16; i += 1) {
                const sx = cx - 110 + (i * 37) % 220;
                const sy = bodyY - 54 + (i * 23) % 112;
                ellipse(sx, sy, 8 + (i % 3), 6 + (i % 2), colorWithAlpha(c.bodyShade, 0.3));
            }
        } else if (c.surface === 'dairy-centaur') {
            ctx.fillStyle = colorWithAlpha('#ffffff', 0.34);
            ellipse(cx - 76, bodyY - 24, 42, 24, colorWithAlpha('#fff8ed', 0.42));
            ellipse(cx + 58, bodyY + 20, 48, 28, colorWithAlpha('#fff8ed', 0.34));

            ctx.strokeStyle = c.lineColor;
            ctx.lineWidth = 6;
            ctx.lineCap = 'round';
            ctx.beginPath();
            ctx.moveTo(cx - 92, bodyY - 86);
            ctx.quadraticCurveTo(cx - 126, bodyY - 120, cx - 104, bodyY - 154);
            ctx.moveTo(cx - 70, bodyY - 91);
            ctx.quadraticCurveTo(cx - 92, bodyY - 132, cx - 62, bodyY - 165);
            ctx.stroke();

            ctx.fillStyle = colorWithAlpha(c.accentColor, 0.72);
            roundedRect(cx - 58, bodyY - 79, 116, 18, 9);
            ctx.fill();
            ctx.strokeStyle = colorWithAlpha(c.lineColor, 0.44);
            ctx.lineWidth = 3;
            ctx.stroke();
            ellipse(cx, bodyY - 59, 11, 15, '#ffd166', c.lineColor, 3);
        } else if (c.surface === 'scales') {
            ctx.strokeStyle = colorWithAlpha(c.bodyShade, 0.42);
            ctx.lineWidth = 2;
            for (let y = -62; y < 74; y += 24) {
                for (let x = -116; x < 120; x += 24) {
                    ctx.beginPath();
                    ctx.arc(cx + x + (Math.floor(y / 24) % 2) * 12, bodyY + y, 12, 0, Math.PI);
                    ctx.stroke();
                }
            }
        } else if (c.surface === 'feathers') {
            for (let i = -3; i <= 3; i += 1) {
                ctx.beginPath();
                ctx.ellipse(cx + i * 32, bodyY - 78 + Math.abs(i) * 7, 18, 54, i * 0.13, 0, Math.PI * 2);
                ctx.fillStyle = i % 2 ? c.accentColor : c.bodyShade;
                ctx.fill();
                ctx.strokeStyle = c.lineColor;
                ctx.lineWidth = 3;
                ctx.stroke();
            }
        } else if (c.surface === 'freckles') {
            for (let i = 0; i < 24; i += 1) {
                const x = cx - 115 + (i * 47) % 226;
                const y = bodyY - 54 + (i * 29) % 104;
                ellipse(x, y, 4 + (i % 3), 3 + (i % 2), colorWithAlpha(c.bodyShade, 0.35));
            }
            for (let side = -1; side <= 1; side += 2) {
                for (let i = 0; i < 4; i += 1) {
                    ctx.beginPath();
                    ctx.ellipse(cx + side * (135 + i * 11), bodyY - 44 + i * 16, 12, 36, side * (0.84 + i * 0.06), 0, Math.PI * 2);
                    ctx.fillStyle = c.accentColor;
                    ctx.fill();
                    ctx.strokeStyle = c.lineColor;
                    ctx.lineWidth = 2;
                    ctx.stroke();
                }
            }
        } else if (c.surface === 'waves') {
            ctx.strokeStyle = colorWithAlpha('#ffffff', 0.45);
            ctx.lineWidth = 4;
            for (let y = -44; y <= 48; y += 25) {
                ctx.beginPath();
                for (let x = -124; x <= 124; x += 16) {
                    const wave = Math.sin((x + t * 38) / 22) * 8;
                    if (x === -124) ctx.moveTo(cx + x, bodyY + y + wave);
                    else ctx.lineTo(cx + x, bodyY + y + wave);
                }
                ctx.stroke();
            }
            ctx.beginPath();
            ctx.moveTo(cx - 162, bodyY - 4);
            ctx.quadraticCurveTo(cx - 220, bodyY + 28, cx - 180, bodyY + 82);
            ctx.quadraticCurveTo(cx - 124, bodyY + 66, cx - 92, bodyY + 50);
            ctx.fillStyle = c.bodyColor;
            ctx.fill();
            ctx.strokeStyle = c.lineColor;
            ctx.lineWidth = 4;
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(cx + 162, bodyY - 4);
            ctx.quadraticCurveTo(cx + 220, bodyY + 28, cx + 180, bodyY + 82);
            ctx.quadraticCurveTo(cx + 124, bodyY + 66, cx + 92, bodyY + 50);
            ctx.fill();
            ctx.stroke();
        } else if (c.surface === 'spiral') {
            ctx.beginPath();
            for (let a = 0; a < Math.PI * 5.6; a += 0.18) {
                const r = 5 + a * 6.5;
                const x = cx + Math.cos(a) * r;
                const y = bodyY + Math.sin(a) * r * 0.74;
                if (a === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            }
            ctx.strokeStyle = colorWithAlpha(c.bodyShade, 0.55);
            ctx.lineWidth = 10;
            ctx.lineCap = 'round';
            ctx.stroke();
        }

        ellipse(cx - 74, bodyY + 91, 33, 45, c.bodyShade, c.lineColor, 4);
        ellipse(cx + 74, bodyY + 91, 33, 45, c.bodyShade, c.lineColor, 4);
        ctx.restore();
    }

    function drawOrgan(c) {
        const cx = W / 2;
        const cy = 326 + Math.sin(t * 2.1) * 2;
        const organGrad = ctx.createRadialGradient(cx - 28, cy - 24, 8, cx, cy, 96);
        organGrad.addColorStop(0, colorWithAlpha('#ffffff', 0.42));
        organGrad.addColorStop(0.22, c.organColor);
        organGrad.addColorStop(1, c.organShade);

        if (c.organ === 'spectral-udder') {
            ctx.save();
            ctx.globalAlpha = 0.88;
            ellipse(cx, cy, 86, 58, organGrad, c.lineColor, 5);
            ctx.strokeStyle = colorWithAlpha(c.accentColor, 0.5);
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.arc(cx, cy, 58 + Math.sin(t * 3) * 4, 0, Math.PI * 2);
            ctx.stroke();
            ctx.restore();
        } else if (c.organ === 'ember-udder') {
            ellipse(cx, cy, 86, 58, organGrad, c.lineColor, 5);
            for (let i = -2; i <= 2; i += 1) {
                ctx.beginPath();
                ctx.moveTo(cx + i * 22, cy - 26);
                ctx.quadraticCurveTo(cx + i * 25 + 10, cy - 52, cx + i * 21, cy - 70 + Math.sin(t * 5 + i) * 5);
                ctx.quadraticCurveTo(cx + i * 14 - 8, cy - 47, cx + i * 22, cy - 26);
                ctx.fillStyle = colorWithAlpha(c.accentColor, 0.58);
                ctx.fill();
            }
        } else if (c.organ === 'lantern-udder') {
            ellipse(cx, cy, 90, 60, organGrad, c.lineColor, 5);
            ctx.strokeStyle = colorWithAlpha(c.bodyShade, 0.38);
            ctx.lineWidth = 4;
            for (let i = -2; i <= 2; i += 1) {
                ctx.beginPath();
                ctx.ellipse(cx + i * 20, cy, 23, 55, 0, 0, Math.PI * 2);
                ctx.stroke();
            }
        } else if (c.organ === 'blister-pouch') {
            ellipse(cx, cy, 84, 58, organGrad, c.lineColor, 5);
            for (let i = 0; i < 11; i += 1) {
                const sx = cx - 56 + (i * 27) % 112;
                const sy = cy - 30 + (i * 19) % 62;
                ellipse(sx, sy, 7 + (i % 3), 5 + (i % 2), colorWithAlpha('#ffffff', 0.18), colorWithAlpha(c.lineColor, 0.2), 1);
            }
        } else if (c.organ === 'cynthia-udder') {
            ctx.save();
            const pouch = ctx.createRadialGradient(cx - 34, cy + 26, 10, cx + 8, cy + 88, 136);
            pouch.addColorStop(0, '#fff9da');
            pouch.addColorStop(0.2, c.organColor);
            pouch.addColorStop(0.82, '#ffc2a8');
            pouch.addColorStop(1, c.organShade);

            ctx.beginPath();
            ctx.moveTo(cx - 72, cy - 70);
            ctx.bezierCurveTo(cx - 92, cy - 16, cx - 108, cy + 94, cx - 45, cy + 148);
            ctx.bezierCurveTo(cx - 10, cy + 180, cx + 74, cy + 166, cx + 94, cy + 86);
            ctx.bezierCurveTo(cx + 106, cy + 28, cx + 88, cy - 42, cx + 62, cy - 70);
            ctx.closePath();
            ctx.fillStyle = pouch;
            ctx.fill();
            ctx.strokeStyle = c.lineColor;
            ctx.lineWidth = 4;
            ctx.stroke();

            ctx.globalAlpha = 0.7;
            ellipse(cx - 37, cy + 18, 18, 47, colorWithAlpha('#fffef1', 0.8));
            ctx.globalAlpha = 0.3;
            ellipse(cx - 28, cy + 64, 7, 5, '#ffffff');
            ellipse(cx - 17, cy + 70, 3, 3, '#ffffff');
            ellipse(cx - 42, cy + 76, 3, 2, '#ffffff');
            ctx.globalAlpha = 0.42;
            ellipse(cx - 9, cy + 133, 34, 18, colorWithAlpha(c.organShade, 0.56));
            ctx.restore();
        } else if (c.organ === 'scale-pouch') {
            ellipse(cx, cy, 83, 58, organGrad, c.lineColor, 5);
            ctx.strokeStyle = colorWithAlpha(c.lineColor, 0.34);
            ctx.lineWidth = 2;
            for (let i = -3; i <= 3; i += 1) {
                ctx.beginPath();
                ctx.arc(cx + i * 22, cy + 8, 15, Math.PI, 0);
                ctx.stroke();
            }
        } else if (c.organ === 'feather-crop') {
            ellipse(cx, cy, 68, 55, organGrad, c.lineColor, 5);
            for (let i = -2; i <= 2; i += 1) {
                ctx.beginPath();
                ctx.ellipse(cx + i * 20, cy - 7 + Math.abs(i) * 5, 12, 38, i * 0.12, 0, Math.PI * 2);
                ctx.fillStyle = colorWithAlpha(c.accentColor, 0.46);
                ctx.fill();
                ctx.strokeStyle = colorWithAlpha(c.lineColor, 0.42);
                ctx.lineWidth = 2;
                ctx.stroke();
            }
        } else if (c.organ === 'gill-pouch') {
            ellipse(cx, cy, 92, 49, organGrad, c.lineColor, 5);
            for (let side = -1; side <= 1; side += 2) {
                ctx.strokeStyle = colorWithAlpha(c.lineColor, 0.34);
                ctx.lineWidth = 3;
                for (let i = 0; i < 4; i += 1) {
                    ctx.beginPath();
                    ctx.moveTo(cx + side * 58, cy - 18 + i * 11);
                    ctx.quadraticCurveTo(cx + side * 88, cy - 10 + i * 9, cx + side * 70, cy + 20 + i * 4);
                    ctx.stroke();
                }
            }
        } else if (c.organ === 'ray-vents') {
            ctx.beginPath();
            ctx.moveTo(cx - 112, cy - 2);
            ctx.quadraticCurveTo(cx, cy - 80, cx + 112, cy - 2);
            ctx.quadraticCurveTo(cx + 82, cy + 68, cx, cy + 58);
            ctx.quadraticCurveTo(cx - 82, cy + 68, cx - 112, cy - 2);
            ctx.closePath();
            ctx.fillStyle = organGrad;
            ctx.fill();
            ctx.strokeStyle = c.lineColor;
            ctx.lineWidth = 5;
            ctx.stroke();
            ctx.strokeStyle = colorWithAlpha(c.lineColor, 0.28);
            ctx.lineWidth = 3;
            for (let i = -2; i <= 2; i += 1) {
                ctx.beginPath();
                ctx.moveTo(cx + i * 34, cy - 28);
                ctx.quadraticCurveTo(cx + i * 24, cy + 8, cx + i * 38, cy + 44);
                ctx.stroke();
            }
        } else if (c.organ === 'mantle-siphon') {
            ellipse(cx, cy, 76, 58, organGrad, c.lineColor, 5);
            ctx.beginPath();
            for (let a = 0; a < Math.PI * 4.8; a += 0.2) {
                const r = 4 + a * 4.2;
                const x = cx + Math.cos(a) * r;
                const y = cy + Math.sin(a) * r * 0.76;
                if (a === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            }
            ctx.strokeStyle = colorWithAlpha(c.lineColor, 0.38);
            ctx.lineWidth = 6;
            ctx.lineCap = 'round';
            ctx.stroke();
        } else {
            ellipse(cx, cy, 92, 62, organGrad, c.lineColor, 5);
            ellipse(cx - 30, cy - 22, 18, 11, colorWithAlpha('#ffffff', 0.28));
        }
    }

    function drawTeats(c) {
        teatPositions(c).forEach((pos, index) => {
            const offset = teatOffsets[index] || 0;
            const tipY = pos.y + pos.length + offset;
            const w = pos.width;
            ctx.save();

            if (c.organ === 'cynthia-udder') {
                const baseY = pos.y + pos.length * 0.48 + offset * 0.28;
                ctx.beginPath();
                ctx.moveTo(pos.x - w * 0.56, baseY);
                ctx.bezierCurveTo(pos.x - w * 0.48, baseY + 18, pos.x - w * 0.38, tipY + 10, pos.x, tipY + 11);
                ctx.bezierCurveTo(pos.x + w * 0.38, tipY + 10, pos.x + w * 0.48, baseY + 18, pos.x + w * 0.56, baseY);
                ctx.closePath();
                const capGrad = ctx.createLinearGradient(pos.x - w, baseY, pos.x + w, tipY + 14);
                capGrad.addColorStop(0, colorWithAlpha('#ffffff', 0.38));
                capGrad.addColorStop(0.32, c.organColor);
                capGrad.addColorStop(1, c.organShade);
                ctx.fillStyle = capGrad;
                ctx.fill();
                ctx.strokeStyle = c.lineColor;
                ctx.lineWidth = 3;
                ctx.stroke();
                ellipse(pos.x - 7, tipY + 7, 4, 2, colorWithAlpha('#ffffff', 0.68));
                ctx.restore();
                return;
            }

            ctx.beginPath();
            ctx.moveTo(pos.x - w / 2, pos.y);
            ctx.bezierCurveTo(pos.x - w * 0.72, pos.y + pos.length * 0.42 + offset * 0.24, pos.x - w * 0.38, tipY, pos.x, tipY + 7);
            ctx.bezierCurveTo(pos.x + w * 0.38, tipY, pos.x + w * 0.72, pos.y + pos.length * 0.42 + offset * 0.24, pos.x + w / 2, pos.y);
            ctx.closePath();
            const grad = ctx.createLinearGradient(pos.x - w, pos.y, pos.x + w, tipY);
            grad.addColorStop(0, colorWithAlpha('#ffffff', 0.34));
            grad.addColorStop(0.3, c.organColor);
            grad.addColorStop(1, c.organShade);
            ctx.fillStyle = grad;
            ctx.fill();
            ctx.strokeStyle = c.lineColor;
            ctx.lineWidth = 4;
            ctx.stroke();

            ctx.globalAlpha = 0.28;
            ctx.beginPath();
            ctx.ellipse(pos.x - w * 0.23, pos.y + pos.length * 0.46 + offset * 0.55, 3, Math.max(10, pos.length * 0.25), -0.1, 0, Math.PI * 2);
            ctx.fillStyle = '#fff';
            ctx.fill();
            ctx.restore();
        });
    }

    function drawMilk(c) {
        if (drag.active && drag.teat >= 0 && mode !== 'complete') {
            const pos = teatPositions(c)[drag.teat];
            const offset = teatOffsets[drag.teat] || 0;
            const tipY = pos.y + pos.length + offset + 3;
            const alpha = clamp(drag.pressure / 80, 0.18, 0.72);
            ctx.save();
            ctx.globalAlpha = alpha;
            ctx.beginPath();
            ctx.moveTo(pos.x - 3, tipY);
            for (let y = tipY; y < bucket.y + 10; y += 12) {
                const sway = Math.sin(t * 11 + y * 0.04) * 4 * (1 - (y - tipY) / (bucket.y - tipY + 1));
                ctx.lineTo(pos.x - 3 + sway, y);
            }
            ctx.lineTo(pos.x + 4, bucket.y + 10);
            for (let y = bucket.y + 10; y > tipY; y -= 12) {
                const sway = Math.sin(t * 11 + y * 0.04) * 4 * (1 - (y - tipY) / (bucket.y - tipY + 1));
                ctx.lineTo(pos.x + 4 + sway, y);
            }
            ctx.closePath();
            const grad = ctx.createLinearGradient(pos.x, tipY, pos.x, bucket.y);
            grad.addColorStop(0, c.milkColor);
            grad.addColorStop(1, c.milkShade);
            ctx.fillStyle = grad;
            ctx.fill();
            ctx.restore();
        }

        particles.forEach((p) => {
            ctx.save();
            ctx.globalAlpha = clamp(p.life, 0, 1);
            ellipse(p.x, p.y, p.r * 0.72, p.r, p.color);
            ctx.restore();
        });
        splashes.forEach((p) => {
            ctx.save();
            ctx.globalAlpha = clamp(p.life, 0, 1);
            ellipse(p.x, p.y, p.r, p.r * 0.72, p.color);
            ctx.restore();
        });
    }

    function drawBucket(c) {
        const wobble = drag.active ? Math.sin(t * 16) * 0.018 : 0;
        ctx.save();
        ellipse(bucket.x, bucket.y + bucket.h + 19, 88, 14, 'rgba(54, 63, 48, 0.18)');
        ctx.translate(bucket.x, bucket.y + bucket.h / 2);
        ctx.rotate(wobble);
        ctx.translate(-bucket.x, -(bucket.y + bucket.h / 2));

        ctx.beginPath();
        ctx.moveTo(bucket.x - bucket.topW / 2, bucket.y + 15);
        ctx.lineTo(bucket.x - bucket.bottomW / 2, bucket.y + bucket.h);
        ctx.lineTo(bucket.x + bucket.bottomW / 2, bucket.y + bucket.h);
        ctx.lineTo(bucket.x + bucket.topW / 2, bucket.y + 15);
        ctx.closePath();
        const body = ctx.createLinearGradient(bucket.x - bucket.topW / 2, 0, bucket.x + bucket.topW / 2, 0);
        body.addColorStop(0, '#8b5a25');
        body.addColorStop(0.28, '#c8913b');
        body.addColorStop(0.55, '#f2c96b');
        body.addColorStop(0.78, '#b9782d');
        body.addColorStop(1, '#70441f');
        ctx.fillStyle = body;
        ctx.fill();
        ctx.strokeStyle = '#5a381c';
        ctx.lineWidth = 5;
        ctx.stroke();

        ctx.save();
        ctx.clip();
        const fillHeight = (bucket.h - 20) * fill;
        const fillTop = bucket.y + bucket.h - fillHeight;
        ctx.beginPath();
        ctx.moveTo(bucket.x - bucket.topW / 2 + 7, fillTop);
        for (let x = -bucket.topW / 2 + 7; x <= bucket.topW / 2 - 7; x += 8) {
            const wave = Math.sin(x * 0.08 + t * 5.4) * (drag.active ? 4 : 2);
            ctx.lineTo(bucket.x + x, fillTop + wave);
        }
        ctx.lineTo(bucket.x + bucket.bottomW / 2 - 7, bucket.y + bucket.h - 4);
        ctx.lineTo(bucket.x - bucket.bottomW / 2 + 7, bucket.y + bucket.h - 4);
        ctx.closePath();
        const milk = ctx.createLinearGradient(0, fillTop, 0, bucket.y + bucket.h);
        milk.addColorStop(0, c.milkColor);
        milk.addColorStop(1, c.milkShade);
        ctx.fillStyle = milk;
        ctx.fill();
        ctx.restore();

        ctx.strokeStyle = '#775020';
        ctx.lineWidth = 5;
        [bucket.y + 42, bucket.y + 84].forEach((y) => {
            ctx.beginPath();
            ctx.moveTo(bucket.x - bucket.topW / 2 + 9, y);
            ctx.lineTo(bucket.x + bucket.topW / 2 - 9, y);
            ctx.stroke();
        });

        roundedRect(bucket.x - bucket.topW / 2 - 6, bucket.y, bucket.topW + 12, 22, 8);
        ctx.fillStyle = '#e2b453';
        ctx.fill();
        ctx.strokeStyle = '#5a381c';
        ctx.lineWidth = 5;
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(bucket.x, bucket.y - 16, 50, Math.PI * 1.08, Math.PI * 1.92);
        ctx.strokeStyle = '#86939b';
        ctx.lineWidth = 7;
        ctx.stroke();
        ctx.strokeStyle = '#d7e2e7';
        ctx.lineWidth = 3;
        ctx.stroke();
        ctx.restore();
    }

    function drawHand() {
        if (stationLocked() || mode === 'complete' || mode === 'claimed') return;
        if (!hand.inside && mode !== 'ready') return;
        const hint = !drag.active && mode !== 'complete';
        const x = hint && !hand.inside ? W / 2 + 55 + Math.sin(t * 2.2) * 7 : hand.x;
        const y = hint && !hand.inside ? 418 + Math.sin(t * 2.2) * 24 : hand.y;
        const grabbing = drag.active;
        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(grabbing ? -0.12 : 0.08);
        ctx.globalAlpha = hint && !hand.inside ? 0.66 : 1;
        ctx.shadowColor = 'rgba(22, 47, 59, 0.22)';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetY = 5;

        ctx.fillStyle = '#ffd4bd';
        ctx.strokeStyle = '#68372b';
        ctx.lineWidth = 3;
        ellipse(0, 13, 22, 26, '#ffd4bd', '#68372b', 3);
        for (let i = 0; i < 4; i += 1) {
            const fx = -16 + i * 10;
            const fy = grabbing ? -9 + Math.abs(i - 1.5) * 3 : -20;
            ctx.beginPath();
            ctx.ellipse(fx, fy, 5, grabbing ? 11 : 18, (i - 1.5) * 0.08, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
        }
        ctx.beginPath();
        ctx.ellipse(-24, 8, 7, 16, -0.72, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();

        if (hint && !hand.inside) {
            ctx.globalAlpha = 0.58;
            ctx.strokeStyle = '#173c4d';
            ctx.lineWidth = 5;
            ctx.lineCap = 'round';
            ctx.beginPath();
            ctx.moveTo(-52, -82);
            ctx.lineTo(-52, -22);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(-68, -39);
            ctx.lineTo(-52, -22);
            ctx.lineTo(-36, -39);
            ctx.stroke();
        }
        ctx.restore();
    }

    function drawPrizeBucket(c, x, y) {
        ctx.save();
        ctx.translate(x, y);
        ctx.beginPath();
        ctx.moveTo(-42, -26);
        ctx.lineTo(-29, 42);
        ctx.lineTo(29, 42);
        ctx.lineTo(42, -26);
        ctx.closePath();
        const body = ctx.createLinearGradient(-44, 0, 44, 0);
        body.addColorStop(0, '#8b5a25');
        body.addColorStop(0.32, '#d49c42');
        body.addColorStop(0.62, '#f4cf72');
        body.addColorStop(1, '#70441f');
        ctx.fillStyle = body;
        ctx.fill();
        ctx.strokeStyle = '#5a381c';
        ctx.lineWidth = 4;
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(-36, -12);
        for (let sx = -36; sx <= 36; sx += 6) {
            ctx.lineTo(sx, -12 + Math.sin(sx * 0.18 + t * 4) * 2);
        }
        ctx.lineTo(29, 38);
        ctx.lineTo(-29, 38);
        ctx.closePath();
        const milk = ctx.createLinearGradient(0, -14, 0, 42);
        milk.addColorStop(0, c.milkColor);
        milk.addColorStop(1, c.milkShade);
        ctx.fillStyle = milk;
        ctx.fill();

        roundedRect(-48, -34, 96, 16, 7);
        ctx.fillStyle = '#e2b453';
        ctx.fill();
        ctx.strokeStyle = '#5a381c';
        ctx.lineWidth = 4;
        ctx.stroke();

        ctx.beginPath();
        ctx.arc(0, -43, 32, Math.PI * 1.08, Math.PI * 1.92);
        ctx.strokeStyle = '#9ba8af';
        ctx.lineWidth = 6;
        ctx.stroke();
        ctx.restore();
    }

    function drawCompletion(c) {
        if (mode !== 'complete' && mode !== 'claimed') return;
        const shownReward = dailyState.rewardName || c.reward;
        let title = 'Bucket Filled';
        let detail = 'Bucket of ' + c.reward;
        let footer = 'Adding item to your inventory...';
        if (claimStatus === 'pending') {
            title = 'Collecting...';
            footer = claimMessage || 'Adding item to your inventory...';
        } else if (claimStatus === 'claimed') {
            title = 'Milk Collected';
            detail = shownReward + ' added';
            footer = claimMessage || 'Come back tomorrow.';
        } else if (claimStatus === 'error') {
            title = 'Claim Failed';
            footer = (claimMessage || 'The bucket could not be claimed.') + ' Tap to retry.';
        }

        ctx.save();
        ctx.fillStyle = 'rgba(15, 39, 53, 0.58)';
        ctx.fillRect(0, 0, W, H);
        confetti.forEach((p) => {
            ctx.save();
            ctx.globalAlpha = clamp(p.life, 0, 1);
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rot);
            ctx.fillStyle = p.color;
            roundedRect(-p.r / 2, -p.r / 2, p.r, p.r * 0.64, 2);
            ctx.fill();
            ctx.restore();
        });

        roundedRect(54, 244, W - 108, 292, 18);
        ctx.fillStyle = 'rgba(255,255,255,0.88)';
        ctx.fill();
        ctx.strokeStyle = c.accentColor;
        ctx.lineWidth = 4;
        ctx.stroke();

        ctx.textAlign = 'center';
        ctx.fillStyle = '#173c4d';
        ctx.font = '900 33px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText(title, W / 2, 304);
        ctx.font = '800 18px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillStyle = colorWithAlpha('#173c4d', 0.72);
        ctx.fillText(detail, W / 2, 338);

        drawPrizeBucket(c, W / 2, 424);

        ctx.font = '800 14px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillStyle = colorWithAlpha('#173c4d', 0.68);
        wrapCanvasText(footer, W / 2, 496, 260, 17);
        ctx.restore();
    }

    function wrapCanvasText(text, x, y, maxWidth, lineHeight) {
        const words = String(text || '').split(/\s+/);
        let line = '';
        let lineY = y;
        words.forEach((word) => {
            const test = line ? line + ' ' + word : word;
            if (ctx.measureText(test).width > maxWidth && line) {
                ctx.fillText(line, x, lineY);
                line = word;
                lineY += lineHeight;
            } else {
                line = test;
            }
        });
        if (line) ctx.fillText(line, x, lineY);
    }

    function draw() {
        const c = preset();
        drawBackground();
        drawHud(c);
        drawAnimal(c);
        drawOrgan(c);
        drawTeats(c);
        drawMilk(c);
        drawBucket(c);
        drawHand();
        drawCompletion(c);
    }

    function loop(timestamp) {
        if (!lastFrame) lastFrame = timestamp;
        const delta = Math.min((timestamp - lastFrame) / 1000, 0.034);
        lastFrame = timestamp;
        update(delta);
        draw();
        window.requestAnimationFrame(loop);
    }

    canvas.addEventListener('pointerdown', onPointerDown);
    canvas.addEventListener('pointermove', onPointerMove);
    canvas.addEventListener('pointerup', onPointerUp);
    canvas.addEventListener('pointercancel', onPointerLeave);
    canvas.addEventListener('pointerleave', onPointerLeave);
    canvas.addEventListener('mouseenter', () => { hand.inside = true; });
    canvas.addEventListener('mousemove', (event) => { hand = Object.assign(getCanvasPoint(event), { inside: true }); });
    canvas.style.cursor = 'none';

    resetButton.addEventListener('click', resetBucket);
    randomButton.addEventListener('click', () => {
        let next = activeIndex;
        if (presets.length > 1) {
            while (next === activeIndex) next = Math.floor(Math.random() * presets.length);
        }
        selectPreset(next);
    });

    buildPresetButtons();
    if (dailyState.completed) {
        setDailyCompleted(dailyState);
    } else {
        resetTeats();
    }
    updatePanel();
    window.requestAnimationFrame(loop);
})();
</script>
