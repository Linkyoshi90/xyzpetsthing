<?php
require_login();

$dropGameRoot = realpath(__DIR__ . '/..');
$dropGameItemDir = $dropGameRoot ? realpath($dropGameRoot . '/images/items') : false;
$dropGameTierTuning = [
    [
        'radius' => 19,
        'scale' => 1.0,
        'spawnWeight' => 34,
        'dropPoints' => 2,
        'mergePoints' => 10,
        'mass' => 0.64,
        'friction' => 0.78,
        'restitution' => 0.035,
        'collider' => [
            'circleScale' => 0.84,
            'normalSides' => 10,
            'preciseSides' => 18,
            'padding' => -1.0,
        ],
    ],
    [
        'radius' => 24,
        'scale' => 1.0,
        'spawnWeight' => 26,
        'dropPoints' => 3,
        'mergePoints' => 22,
        'mass' => 0.78,
        'friction' => 0.79,
        'restitution' => 0.032,
        'collider' => [
            'circleScale' => 0.87,
            'normalSides' => 12,
            'preciseSides' => 20,
            'padding' => -0.5,
        ],
    ],
    [
        'radius' => 30,
        'scale' => 1.0,
        'spawnWeight' => 18,
        'dropPoints' => 4,
        'mergePoints' => 38,
        'mass' => 0.95,
        'friction' => 0.8,
        'restitution' => 0.03,
        'collider' => [
            'circleScale' => 0.9,
            'normalSides' => 12,
            'preciseSides' => 22,
            'padding' => 0.0,
        ],
    ],
    [
        'radius' => 37,
        'scale' => 1.0,
        'spawnWeight' => 12,
        'dropPoints' => 5,
        'mergePoints' => 62,
        'mass' => 1.14,
        'friction' => 0.81,
        'restitution' => 0.028,
        'collider' => [
            'circleScale' => 0.9,
            'normalSides' => 12,
            'preciseSides' => 24,
            'padding' => 0.0,
        ],
    ],
    [
        'radius' => 45,
        'scale' => 1.0,
        'spawnWeight' => 7,
        'dropPoints' => 6,
        'mergePoints' => 98,
        'mass' => 1.36,
        'friction' => 0.82,
        'restitution' => 0.026,
        'collider' => [
            'circleScale' => 0.91,
            'normalSides' => 14,
            'preciseSides' => 26,
            'padding' => 0.2,
        ],
    ],
    [
        'radius' => 54,
        'scale' => 1.0,
        'spawnWeight' => 3,
        'dropPoints' => 8,
        'mergePoints' => 148,
        'mass' => 1.64,
        'friction' => 0.83,
        'restitution' => 0.024,
        'collider' => [
            'circleScale' => 0.91,
            'normalSides' => 14,
            'preciseSides' => 28,
            'padding' => 0.0,
        ],
    ],
    [
        'radius' => 64,
        'scale' => 1.0,
        'spawnWeight' => 0,
        'dropPoints' => 0,
        'mergePoints' => 218,
        'mass' => 1.98,
        'friction' => 0.84,
        'restitution' => 0.022,
        'collider' => [
            'circleScale' => 0.92,
            'normalSides' => 16,
            'preciseSides' => 28,
            'padding' => 0.4,
        ],
    ],
    [
        'radius' => 76,
        'scale' => 1.0,
        'spawnWeight' => 0,
        'dropPoints' => 0,
        'mergePoints' => 318,
        'mass' => 2.38,
        'friction' => 0.85,
        'restitution' => 0.02,
        'collider' => [
            'circleScale' => 0.91,
            'normalSides' => 16,
            'preciseSides' => 30,
            'padding' => 0.5,
        ],
    ],
    [
        'radius' => 90,
        'scale' => 1.0,
        'spawnWeight' => 0,
        'dropPoints' => 0,
        'mergePoints' => 460,
        'mass' => 2.82,
        'friction' => 0.86,
        'restitution' => 0.018,
        'collider' => [
            'circleScale' => 0.92,
            'normalSides' => 18,
            'preciseSides' => 32,
            'padding' => 0.5,
        ],
    ],
    [
        'radius' => 106,
        'scale' => 1.0,
        'spawnWeight' => 0,
        'dropPoints' => 0,
        'mergePoints' => 660,
        'mass' => 3.36,
        'friction' => 0.87,
        'restitution' => 0.016,
        'collider' => [
            'circleScale' => 0.93,
            'normalSides' => 18,
            'preciseSides' => 34,
            'padding' => 0.8,
        ],
    ],
    [
        'radius' => 124,
        'scale' => 1.0,
        'spawnWeight' => 0,
        'dropPoints' => 0,
        'mergePoints' => 940,
        'mass' => 4.05,
        'friction' => 0.88,
        'restitution' => 0.014,
        'collider' => [
            'circleScale' => 0.93,
            'normalSides' => 20,
            'preciseSides' => 36,
            'padding' => 1.0,
        ],
    ],
];

$dropGameThemeDefinitions = [
    [
        'id' => 'food',
        'name' => 'Food',
        'accent' => '#dc5b46',
        'board' => [
            'top' => '#eaf7ff',
            'mid' => '#f7fbf6',
            'bottom' => '#ffe7df',
            'wall' => '#2d3a3f',
        ],
        'items' => [
            ['name' => 'White Strawberry', 'file' => 'White Strawberry.webp', 'material' => 'food'],
            ['name' => 'Chocopear', 'file' => 'Chocopear.webp', 'material' => 'food'],
            ['name' => 'Milkmelon Slice', 'file' => 'Milkmelon Slice.webp', 'material' => 'food'],
            ['name' => 'Waffle', 'file' => 'Waffle.webp', 'material' => 'food'],
            ['name' => 'Macarons', 'file' => 'Macarons.webp', 'material' => 'food'],
            ['name' => 'Deluxe Peach', 'file' => 'Deluxe Peach.webp', 'material' => 'food'],
            ['name' => 'Cream Puffs', 'file' => 'Cream Puffs.webp', 'material' => 'plush'],
            ['name' => 'Deluxe Strawberry', 'file' => 'Deluxe Strawberry.webp', 'material' => 'food'],
            ['name' => 'Deluxe Watermelon', 'file' => 'Deluxe Watermelon.webp', 'material' => 'food'],
            ['name' => 'Baumkuchen', 'file' => 'Baumkuchen.webp', 'material' => 'food'],
            ['name' => 'Milkmelon', 'file' => 'Milkmelon.webp', 'material' => 'food'],
        ],
    ],
    [
        'id' => 'fish',
        'name' => 'Fish',
        'accent' => '#2386a8',
        'board' => [
            'top' => '#e9fbff',
            'mid' => '#eff8f4',
            'bottom' => '#f1eadf',
            'wall' => '#243c47',
        ],
        'items' => [
            ['name' => 'Tadpolefish', 'file' => 'Tadpolefish.webp', 'material' => 'aquatic'],
            ['name' => 'Silverfish', 'file' => 'Silverfish.webp', 'material' => 'aquatic'],
            ['name' => 'Goldfish', 'file' => 'Goldfish.webp', 'material' => 'aquatic'],
            ['name' => 'Bassfish', 'file' => 'Bassfish.webp', 'material' => 'aquatic'],
            ['name' => 'Baconfish', 'file' => 'Baconfish.webp', 'material' => 'food'],
            ['name' => 'Jellyfish', 'file' => 'Jellyfish.webp', 'material' => 'glass'],
            ['name' => 'Parrotfish', 'file' => 'Parrotfish.webp', 'material' => 'aquatic'],
            ['name' => 'Catfish', 'file' => 'Catfish.webp', 'material' => 'aquatic'],
            ['name' => 'Energy Fish', 'file' => 'Energy Fish.webp', 'material' => 'magic'],
            ['name' => 'Fishwitch', 'file' => 'Fishwitch.webp', 'material' => 'magic'],
            ['name' => 'Zombie Fish', 'file' => 'Zombie Fish.webp', 'material' => 'stone'],
        ],
    ],
    [
        'id' => 'paints',
        'name' => 'Paints',
        'accent' => '#8b5a2b',
        'board' => [
            'top' => '#fff8ed',
            'mid' => '#eff7f2',
            'bottom' => '#e6f1ff',
            'wall' => '#3a342f',
        ],
        'items' => [
            ['name' => 'Baby Paint', 'file' => 'Baby Paint.webp', 'material' => 'plush'],
            ['name' => 'White Paint', 'file' => 'White Paint.webp', 'material' => 'food'],
            ['name' => 'Yellow Paint', 'file' => 'Yellow Paint.webp', 'material' => 'food'],
            ['name' => 'Green Paint', 'file' => 'Green Paint.webp', 'material' => 'stone'],
            ['name' => 'Blue Paint', 'file' => 'Blue Paint.webp', 'material' => 'stone'],
            ['name' => 'Purple Paint', 'file' => 'Purple Paint.webp', 'material' => 'stone'],
            ['name' => 'Rainbow Paint', 'file' => 'Rainbow Paint.webp', 'material' => 'magic'],
            ['name' => 'Silver Paint', 'file' => 'Silver Paint.webp', 'material' => 'metal'],
            ['name' => 'Gold Paint', 'file' => 'Gold Paint.webp', 'material' => 'metal'],
            ['name' => 'Relic Paint', 'file' => 'Relic Paint.webp', 'material' => 'stone'],
            ['name' => 'Crystal Shard', 'file' => 'Crystal Shard.webp', 'material' => 'glass'],
        ],
    ],
    [
        'id' => 'objects',
        'name' => 'Objects',
        'accent' => '#277967',
        'board' => [
            'top' => '#f4fbf4',
            'mid' => '#f7f0e7',
            'bottom' => '#e8f1ff',
            'wall' => '#263d37',
        ],
        'items' => [
            ['name' => 'Healing Potion', 'file' => 'Healing Potion.webp', 'material' => 'glass'],
            ['name' => 'Sunglasses', 'file' => 'Sunglasses.webp', 'material' => 'glass'],
            ['name' => 'Wizard Hat', 'file' => 'Wizard Hat.webp', 'material' => 'plush'],
            ['name' => 'Iron Sword', 'file' => 'Iron Sword.webp', 'material' => 'metal'],
            ['name' => 'Mana Elixir', 'file' => 'Mana Elixir.webp', 'material' => 'glass'],
            ['name' => 'Golden Shovel', 'file' => 'Golden Shovel.webp', 'material' => 'metal'],
            ['name' => 'Ramen Glass', 'file' => 'Ramen Glass.webp', 'material' => 'glass'],
            ['name' => 'Plush Burger', 'file' => 'Plush Burger.webp', 'material' => 'plush'],
            ['name' => 'Granite Paint', 'file' => 'Granite Paint.webp', 'material' => 'stone'],
            ['name' => 'Luxurious Water', 'file' => 'Luxurious Water.webp', 'material' => 'glass'],
            ['name' => 'Realistic Paint', 'file' => 'Realistic Paint.webp', 'material' => 'stone'],
        ],
    ],
];

$dropGameThemes = [];
$dropGameMissingThemes = [];
$normalizedItemDir = $dropGameItemDir ? str_replace('\\', '/', $dropGameItemDir) : '';

foreach ($dropGameThemeDefinitions as $themeDefinition) {
    $themeItems = [];
    $missingFiles = [];

    foreach ($themeDefinition['items'] as $tier => $themeItem) {
        $file = $themeItem['file'];
        $candidateFiles = [$file];
        if (preg_match('/\.webp$/i', $file)) {
            $candidateFiles[] = preg_replace('/\.webp$/i', '.wepb', $file);
        } elseif (preg_match('/\.wepb$/i', $file)) {
            $candidateFiles[] = preg_replace('/\.wepb$/i', '.webp', $file);
        }
        $fullPath = false;
        foreach ($candidateFiles as $candidateFile) {
            $candidatePath = $dropGameItemDir ? realpath($dropGameItemDir . DIRECTORY_SEPARATOR . $candidateFile) : false;
            if ($candidatePath) {
                $file = $candidateFile;
                $fullPath = $candidatePath;
                break;
            }
        }
        $normalizedPath = $fullPath ? str_replace('\\', '/', $fullPath) : '';
        $insideItemDir = $normalizedItemDir !== '' && strpos($normalizedPath, $normalizedItemDir . '/') === 0;
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $isSupportedImage = in_array($extension, ['webp', 'wepb'], true);

        if (!$fullPath || !$insideItemDir || !$isSupportedImage || !is_file($fullPath)) {
            $missingFiles[] = $file;
            continue;
        }

        $themeItem['file'] = $file;
        $item = array_merge($dropGameTierTuning[$tier], $themeItem);
        $item['tier'] = $tier;
        $item['themeId'] = $themeDefinition['id'];
        $item['image'] = 'images/items/' . rawurlencode($file);
        $item['sourceBytes'] = filesize($fullPath) ?: 0;
        $item['sourceModified'] = filemtime($fullPath) ?: 0;
        $themeItems[] = $item;
    }

    if ($missingFiles) {
        $dropGameMissingThemes[] = [
            'name' => $themeDefinition['name'],
            'files' => $missingFiles,
        ];
        continue;
    }

    $dropGameThemes[] = [
        'id' => $themeDefinition['id'],
        'name' => $themeDefinition['name'],
        'accent' => $themeDefinition['accent'],
        'board' => $themeDefinition['board'],
        'items' => $themeItems,
    ];
}

$dropGameReady = count($dropGameThemes) > 0;
$dropGameDefaultTheme = $dropGameThemes[0] ?? ['items' => []];
$dropGameDefaultItem = $dropGameDefaultTheme['items'][0] ?? ['image' => '', 'name' => ''];
?>

<section class="drop-game-page">
    <div class="drop-game-heading">
        <div>
            <p class="muted drop-game-kicker">Proof of concept</p>
            <h1>Drop Game</h1>
        </div>
        <a class="btn ghost" href="?pg=games">Back to Games</a>
    </div>

    <?php if (!$dropGameReady): ?>
        <div class="card glass drop-game-empty" role="alert">
            <h2>Missing item graphics</h2>
            <p class="muted">The drop game needs at least one complete 11-item .webp set in images/items.</p>
            <?php foreach ($dropGameMissingThemes as $missingTheme): ?>
                <h3><?= htmlspecialchars($missingTheme['name']) ?></h3>
                <ul>
                    <?php foreach ($missingTheme['files'] as $missingFile): ?>
                        <li><?= htmlspecialchars($missingFile) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="drop-game-shell">
            <div class="drop-game-stage glass">
                <canvas id="drop-game-canvas" width="420" height="620" aria-label="Drop game play area"></canvas>
                <div class="drop-game-cooldown" aria-hidden="true">
                    <span id="drop-game-cooldown-fill"></span>
                </div>
            </div>

            <aside class="drop-game-panel card glass" aria-label="Drop game controls">
                <div class="drop-game-score-row">
                    <div>
                        <span class="muted">Score</span>
                        <strong id="drop-game-score">0</strong>
                    </div>
                    <div>
                        <span class="muted">Combo</span>
                        <strong id="drop-game-combo">x1</strong>
                    </div>
                    <div>
                        <span class="muted">Best</span>
                        <strong id="drop-game-best-score">0</strong>
                    </div>
                </div>

                <div class="drop-game-next">
                    <span class="muted">Next</span>
                    <div class="drop-game-next-preview">
                        <img id="drop-game-next-image" alt="" src="<?= htmlspecialchars($dropGameDefaultItem['image']) ?>">
                        <div>
                            <strong id="drop-game-next-name"><?= htmlspecialchars($dropGameDefaultItem['name']) ?></strong>
                            <span class="drop-game-chip" id="drop-game-next-material">Food</span>
                        </div>
                    </div>
                </div>

                <details class="drop-game-lab drop-game-queue" aria-label="Upcoming items" open>
                    <summary>Queue</summary>
                    <div class="drop-game-queue-list" id="drop-game-queue-list"></div>
                </details>

                <details class="drop-game-lab" open>
                    <summary>Item Tiers</summary>
                    <div class="drop-game-tier-list" id="drop-game-tier-list" aria-label="Item tiers"></div>
                </details>

                <div class="drop-game-danger" aria-label="Danger line timer">
                    <div class="drop-game-danger-label">
                        <span class="muted">Danger</span>
                        <strong id="drop-game-warning">Clear</strong>
                    </div>
                    <span class="drop-game-danger-track"><span id="drop-game-danger-fill"></span></span>
                </div>

                <div class="drop-game-seed-row">
                    <span class="drop-game-chip" id="drop-game-mode-label">Practice</span>
                    <span class="muted" id="drop-game-seed-label">Seed ready</span>
                </div>

                <div class="drop-game-controls">
                    <button class="btn" type="button" id="drop-game-left" title="Move left">&lt;</button>
                    <button class="btn" type="button" id="drop-game-drop">Drop</button>
                    <button class="btn" type="button" id="drop-game-right" title="Move right">&gt;</button>
                    <button class="btn ghost" type="button" id="drop-game-pause">Pause</button>
                    <button class="btn ghost" type="button" id="drop-game-restart">Restart</button>
                </div>

                <details class="drop-game-lab" open>
                    <summary>Game Setup</summary>
                    <div class="drop-game-settings">
                        <label>
                            <span class="muted">Theme</span>
                            <select id="drop-game-theme" aria-label="Item theme"></select>
                        </label>
                        <label>
                            <span class="muted">Startup</span>
                            <select id="drop-game-startup-pack" aria-label="Startup item pack">
                                <option value="random">Random pack</option>
                                <option value="remember">Remember pack</option>
                            </select>
                        </label>
                        <label>
                            <span class="muted">Mode</span>
                            <select id="drop-game-mode" aria-label="Seed mode">
                                <option value="practice">Practice</option>
                                <option value="daily">Daily</option>
                            </select>
                        </label>
                        <label>
                            <span class="muted">Challenge</span>
                            <select id="drop-game-challenge" aria-label="Challenge mode">
                                <option value="standard">Standard</option>
                                <option value="timed">Timed</option>
                                <option value="tiny">Tiny bin</option>
                                <option value="chaos">Chaos physics</option>
                                <option value="fixed">Fixed sequence</option>
                            </select>
                        </label>
                        <label>
                            <span class="muted">Colliders</span>
                            <select id="drop-game-quality" aria-label="Collider quality">
                                <option value="simple">Simple</option>
                                <option value="normal" selected>Normal</option>
                                <option value="precise">Precise</option>
                            </select>
                        </label>
                        <label>
                            <span class="muted">Debug</span>
                            <select id="drop-game-debug" aria-label="Collider debug">
                                <option value="off">Off</option>
                                <option value="shape">Shape</option>
                                <option value="centers">Centers</option>
                                <option value="bounds">Bounds</option>
                            </select>
                        </label>
                        <label>
                            <span class="muted">Volume</span>
                            <input id="drop-game-volume" type="range" min="0" max="1" step="0.05" value="0.75" aria-label="Volume">
                        </label>
                        <label>
                            <span class="muted">Pointer</span>
                            <select id="drop-game-pointer-mode" aria-label="Pointer control">
                                <option value="tap">Tap drops</option>
                                <option value="aim">Aim only</option>
                            </select>
                        </label>
                        <label>
                            <span class="muted">UI scale</span>
                            <input id="drop-game-ui-scale" type="range" min="0.9" max="1.2" step="0.05" value="1" aria-label="UI scale">
                        </label>
                    </div>

                    <div class="drop-game-toggle-grid">
                        <label class="drop-game-toggle">
                            <input type="checkbox" id="drop-game-sound" checked>
                            <span>Sound</span>
                        </label>
                        <label class="drop-game-toggle">
                            <input type="checkbox" id="drop-game-motion">
                            <span>Reduced motion</span>
                        </label>
                        <label class="drop-game-toggle">
                            <input type="checkbox" id="drop-game-color-safe" checked>
                            <span>Color-safe warnings</span>
                        </label>
                    </div>
                </details>

                <details class="drop-game-lab" open>
                    <summary>Leaderboards</summary>
                    <div class="drop-game-leaderboard-head">
                        <select id="drop-game-leaderboard-scope" aria-label="Leaderboard scope">
                            <option value="current">Current setup</option>
                            <option value="all">All local scores</option>
                        </select>
                        <button class="btn ghost" type="button" id="drop-game-clear-leaderboard">Clear</button>
                    </div>
                    <ol class="drop-game-leaderboard" id="drop-game-leaderboard-list"></ol>
                </details>

                <details class="drop-game-lab">
                    <summary>Replays</summary>
                    <div class="drop-game-replay-tools">
                        <select id="drop-game-replay-select" aria-label="Saved replay"></select>
                        <button class="btn ghost" type="button" id="drop-game-play-replay">Play</button>
                        <button class="btn ghost" type="button" id="drop-game-export-replay">Export</button>
                        <button class="btn ghost" type="button" id="drop-game-import-replay">Import</button>
                        <textarea id="drop-game-replay-json" rows="4" spellcheck="false" aria-label="Replay JSON"></textarea>
                    </div>
                </details>

                <p class="muted drop-game-status" id="drop-game-status" role="status" aria-live="polite">Loading items...</p>
            </aside>
        </div>
        <div class="drop-game-gameover" id="drop-game-gameover" hidden>
            <div class="card glass drop-game-gameover-card" role="dialog" aria-modal="true" aria-labelledby="drop-game-gameover-title">
                <h2 id="drop-game-gameover-title">Game Over</h2>
                <div class="drop-game-gameover-scores">
                    <div>
                        <span class="muted">Final</span>
                        <strong id="drop-game-final-score">0</strong>
                    </div>
                    <div>
                        <span class="muted">Best</span>
                        <strong id="drop-game-final-best">0</strong>
                    </div>
                </div>
                <div class="drop-game-gameover-actions">
                    <button class="btn" type="button" id="drop-game-send-score">Send Score</button>
                    <button class="btn ghost" type="button" id="drop-game-retry">Retry</button>
                </div>
                <p class="muted drop-game-exchange-status" id="drop-game-exchange-status" role="status" aria-live="polite"></p>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if ($dropGameReady): ?>
<script>
window.dropGameThemes = <?=
    json_encode($dropGameThemes, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
?>;
window.dropGameToday = <?= json_encode(date('Y-m-d')) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/matter-js@0.19.0/build/matter.min.js"></script>
<script>
(function () {
    'use strict';

    const themes = Array.isArray(window.dropGameThemes) ? window.dropGameThemes : [];
    const themeById = new Map(themes.map(theme => [theme.id, theme]));
    const todaySeed = String(window.dropGameToday || new Date().toISOString().slice(0, 10));
    const canvas = document.getElementById('drop-game-canvas');
    const scoreEl = document.getElementById('drop-game-score');
    const comboEl = document.getElementById('drop-game-combo');
    const bestScoreEl = document.getElementById('drop-game-best-score');
    const nextImageEl = document.getElementById('drop-game-next-image');
    const nextNameEl = document.getElementById('drop-game-next-name');
    const nextMaterialEl = document.getElementById('drop-game-next-material');
    const queueListEl = document.getElementById('drop-game-queue-list');
    const warningEl = document.getElementById('drop-game-warning');
    const dangerFillEl = document.getElementById('drop-game-danger-fill');
    const cooldownFillEl = document.getElementById('drop-game-cooldown-fill');
    const modeLabelEl = document.getElementById('drop-game-mode-label');
    const seedLabelEl = document.getElementById('drop-game-seed-label');
    const statusEl = document.getElementById('drop-game-status');
    const tierListEl = document.getElementById('drop-game-tier-list');
    const themeEl = document.getElementById('drop-game-theme');
    const startupPackEl = document.getElementById('drop-game-startup-pack');
    const modeEl = document.getElementById('drop-game-mode');
    const challengeEl = document.getElementById('drop-game-challenge');
    const qualityEl = document.getElementById('drop-game-quality');
    const debugEl = document.getElementById('drop-game-debug');
    const volumeEl = document.getElementById('drop-game-volume');
    const pointerModeEl = document.getElementById('drop-game-pointer-mode');
    const uiScaleEl = document.getElementById('drop-game-ui-scale');
    const soundEl = document.getElementById('drop-game-sound');
    const motionEl = document.getElementById('drop-game-motion');
    const colorSafeEl = document.getElementById('drop-game-color-safe');
    const leaderboardScopeEl = document.getElementById('drop-game-leaderboard-scope');
    const leaderboardListEl = document.getElementById('drop-game-leaderboard-list');
    const clearLeaderboardBtn = document.getElementById('drop-game-clear-leaderboard');
    const replaySelectEl = document.getElementById('drop-game-replay-select');
    const playReplayBtn = document.getElementById('drop-game-play-replay');
    const exportReplayBtn = document.getElementById('drop-game-export-replay');
    const importReplayBtn = document.getElementById('drop-game-import-replay');
    const replayJsonEl = document.getElementById('drop-game-replay-json');
    const leftBtn = document.getElementById('drop-game-left');
    const rightBtn = document.getElementById('drop-game-right');
    const dropBtn = document.getElementById('drop-game-drop');
    const pauseBtn = document.getElementById('drop-game-pause');
    const restartBtn = document.getElementById('drop-game-restart');
    const gameOverEl = document.getElementById('drop-game-gameover');
    const finalScoreEl = document.getElementById('drop-game-final-score');
    const finalBestEl = document.getElementById('drop-game-final-best');
    const sendScoreBtn = document.getElementById('drop-game-send-score');
    const exchangeStatusEl = document.getElementById('drop-game-exchange-status');
    const retryBtn = document.getElementById('drop-game-retry');

    if (!canvas || !themes.length) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const SETTINGS_KEY = 'harmontide.dropGame.settings.v2';
    const LEADERBOARD_KEY = 'harmontide.dropGame.leaderboards.v1';
    const REPLAYS_KEY = 'harmontide.dropGame.replays.v1';
    const SCORE_EXCHANGE_ENDPOINT = 'score_exchange.php';
    const REPLAY_VERSION = 1;

    const BUILT_IN_THEME_COUNT = themes.length;
    const BASE_WORLD = {
        width: 420,
        height: 620,
        wall: 14,
        sideInset: 0,
        floorY: 606,
        spawnY: 82,
        dangerY: 126,
        dangerGraceMs: 2600,
        cooldownMs: 470,
        comboGraceMs: 1450,
        queueSize: 4
    };

    const CHALLENGES = {
        standard: {
            label: 'Standard',
            scoreMultiplier: 1,
            timeLimitMs: 0,
            gravityY: 1,
            gravityScale: 0.00112
        },
        timed: {
            label: 'Timed',
            scoreMultiplier: 1.18,
            timeLimitMs: 90000,
            gravityY: 1,
            gravityScale: 0.00112
        },
        tiny: {
            label: 'Tiny bin',
            scoreMultiplier: 1.28,
            timeLimitMs: 0,
            gravityY: 1,
            gravityScale: 0.00112,
            world: {
                sideInset: 42,
                dangerGraceMs: 2200
            }
        },
        chaos: {
            label: 'Chaos physics',
            scoreMultiplier: 1.35,
            timeLimitMs: 0,
            gravityY: 0.92,
            gravityScale: 0.00118,
            chaos: true,
            world: {
                cooldownMs: 420,
                dangerGraceMs: 2400
            }
        },
        fixed: {
            label: 'Fixed sequence',
            scoreMultiplier: 1.12,
            timeLimitMs: 0,
            gravityY: 1,
            gravityScale: 0.00112,
            fixedSequence: [0, 1, 0, 2, 1, 0, 3, 2, 0, 1, 4, 2, 1, 0, 3, 5, 2, 1]
        }
    };

    const RARE_VARIANTS = [
        {
            id: 'gilded',
            label: 'Gilded',
            chance: 0.028,
            scoreMultiplier: 2,
            aura: '#f0b33b'
        },
        {
            id: 'starlit',
            label: 'Starlit',
            chance: 0.018,
            scoreMultiplier: 1.65,
            aura: '#72a8ff'
        }
    ];

    const MATERIALS = {
        food: {
            label: 'Food',
            color: '#dc5b46',
            massScale: 1,
            frictionOffset: 0.02,
            restitutionOffset: -0.004,
            tone: [260, 0.045, 'triangle']
        },
        aquatic: {
            label: 'Aquatic',
            color: '#2386a8',
            massScale: 0.96,
            frictionOffset: -0.02,
            restitutionOffset: 0.006,
            tone: [190, 0.038, 'sine']
        },
        glass: {
            label: 'Glass',
            color: '#6b9fbf',
            massScale: 0.9,
            frictionOffset: -0.06,
            restitutionOffset: 0.05,
            tone: [720, 0.032, 'sine']
        },
        metal: {
            label: 'Metal',
            color: '#8a8f96',
            massScale: 1.28,
            frictionOffset: -0.035,
            restitutionOffset: 0.018,
            tone: [420, 0.04, 'square']
        },
        plush: {
            label: 'Plush',
            color: '#bb6d92',
            massScale: 0.82,
            frictionOffset: 0.08,
            restitutionOffset: -0.012,
            tone: [150, 0.055, 'triangle']
        },
        stone: {
            label: 'Stone',
            color: '#77736a',
            massScale: 1.18,
            frictionOffset: 0.04,
            restitutionOffset: -0.014,
            tone: [96, 0.05, 'square']
        },
        magic: {
            label: 'Magic',
            color: '#7d6ccf',
            massScale: 0.98,
            frictionOffset: 0,
            restitutionOffset: 0.026,
            tone: [540, 0.046, 'triangle']
        }
    };

    const WORLD = Object.assign({}, BASE_WORLD);

    const state = {
        engine: null,
        theme: themes[0],
        preparedThemeCache: new Map(),
        assets: [],
        assetsByTier: [],
        lowTierBag: [],
        nextAsset: null,
        queue: [],
        queueRng: Math.random,
        physicsRng: Math.random,
        seed: '',
        mode: 'practice',
        startupPack: 'random',
        challenge: 'standard',
        challengeEndsAt: 0,
        fixedSequenceIndex: 0,
        score: 0,
        bestScore: 0,
        leaderboards: {},
        replays: [],
        runStartedAt: 0,
        recordedInputs: [],
        pendingReplay: null,
        replayData: null,
        replayIndex: 0,
        replaying: false,
        scoreSent: false,
        scoreSending: false,
        combo: 1,
        comboUntil: 0,
        lastDropAt: -Infinity,
        dropX: WORLD.width / 2,
        dangerProgress: 0,
        paused: false,
        gameOver: false,
        loading: true,
        keys: Object.create(null),
        holdDirection: 0,
        pointerActive: false,
        pointerMode: 'tap',
        debugMode: 'off',
        colliderQuality: 'normal',
        colliderPipeline: {
            alphaThreshold: 36,
            simplifyTolerance: 2,
            padding: 0,
            maxVertices: 24
        },
        raf: 0,
        lastFrameAt: 0,
        pendingMerges: [],
        mergeTimer: 0,
        effects: [],
        shakeUntil: 0,
        shakePower: 0,
        lastCollisionSoundAt: 0,
        lastWarningSoundAt: 0,
        lastStatus: '',
        reducedMotion: false,
        colorSafeWarnings: true,
        uiScale: 1,
        audio: {
            enabled: true,
            volume: 0.75,
            ctx: null
        }
    };

    if (!window.Matter) {
        setStatus('Matter.js could not be loaded.');
        state.loading = false;
        render();
        return;
    }

    const Matter = window.Matter;
    const Engine = Matter.Engine;
    const Bodies = Matter.Bodies;
    const Body = Matter.Body;
    const Bounds = Matter.Bounds;
    const Composite = Matter.Composite;
    const Events = Matter.Events;

    function setStatus(message) {
        state.lastStatus = message;
        if (statusEl) {
            statusEl.textContent = message;
        }
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function gameRandom() {
        return state.physicsRng ? state.physicsRng() : Math.random();
    }

    function visualRandom() {
        return Math.random();
    }

    function formatScore(value) {
        return Math.round(value).toLocaleString();
    }

    function parseStoredJson(key, fallback) {
        try {
            if (!window.localStorage) {
                return fallback;
            }
            const raw = window.localStorage.getItem(key);
            return raw ? JSON.parse(raw) : fallback;
        } catch (error) {
            return fallback;
        }
    }

    function storeJson(key, value) {
        try {
            if (window.localStorage) {
                window.localStorage.setItem(key, JSON.stringify(value));
            }
        } catch (error) {
            // Settings are a convenience only; blocked storage should not stop play.
        }
    }

    function fnv1aHash(text) {
        let hash = 2166136261;
        for (let i = 0; i < text.length; i += 1) {
            hash ^= text.charCodeAt(i);
            hash = Math.imul(hash, 16777619);
        }
        return hash >>> 0;
    }

    function mulberry32(seed) {
        let value = seed >>> 0;
        return function () {
            value += 0x6D2B79F5;
            let t = value;
            t = Math.imul(t ^ (t >>> 15), t | 1);
            t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
            return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
        };
    }

    function currentChallenge() {
        return CHALLENGES[state.challenge] || CHALLENGES.standard;
    }

    function challengeLabel() {
        return currentChallenge().label;
    }

    function applyChallengeWorld() {
        const challenge = currentChallenge();
        Object.assign(WORLD, BASE_WORLD, challenge.world || {});
    }

    function configureRng(seedOverride) {
        if (seedOverride) {
            state.seed = String(seedOverride);
        } else if (state.mode === 'daily') {
            state.seed = todaySeed + ':' + (state.theme ? state.theme.id : 'theme') + ':p1';
        } else {
            state.seed = 'practice-' + Date.now().toString(36) + '-' + Math.floor(Math.random() * 0xFFFF).toString(36);
        }
        state.queueRng = mulberry32(fnv1aHash(state.seed + ':' + state.challenge + ':queue'));
        state.physicsRng = mulberry32(fnv1aHash(state.seed + ':' + state.challenge + ':physics'));
        syncSeedUi();
    }

    function materialFor(key) {
        return MATERIALS[key] || MATERIALS.food;
    }

    function randomBuiltInTheme() {
        const count = Math.max(1, BUILT_IN_THEME_COUNT);
        let index = Math.floor(Math.random() * count);
        if (window.crypto && window.crypto.getRandomValues) {
            const values = new Uint32Array(1);
            window.crypto.getRandomValues(values);
            index = values[0] % count;
        }
        return themes[index] || themes[0];
    }

    function saveSettings() {
        storeJson(SETTINGS_KEY, {
            themeId: state.theme ? state.theme.id : themes[0].id,
            startupPack: state.startupPack,
            mode: state.mode,
            challenge: state.challenge,
            colliderQuality: state.colliderQuality,
            debugMode: state.debugMode,
            sound: state.audio.enabled,
            volume: state.audio.volume,
            pointerMode: state.pointerMode,
            reducedMotion: state.reducedMotion,
            colorSafeWarnings: state.colorSafeWarnings,
            uiScale: state.uiScale
        });
    }

    function loadSettings() {
        const stored = parseStoredJson(SETTINGS_KEY, {});
        const storedTheme = themeById.get(stored.themeId) || themes[0];
        state.startupPack = stored.startupPack === 'remember' ? 'remember' : 'random';
        state.theme = state.startupPack === 'random' ? randomBuiltInTheme() : storedTheme;
        state.mode = stored.mode === 'daily' ? 'daily' : 'practice';
        state.challenge = CHALLENGES[stored.challenge] ? stored.challenge : 'standard';
        state.colliderQuality = ['simple', 'normal', 'precise'].includes(stored.colliderQuality) ? stored.colliderQuality : 'normal';
        state.debugMode = ['off', 'shape', 'centers', 'bounds'].includes(stored.debugMode) ? stored.debugMode : 'off';
        state.audio.enabled = stored.sound !== false;
        state.audio.volume = clamp(Number(stored.volume == null ? 0.75 : stored.volume), 0, 1);
        state.pointerMode = stored.pointerMode === 'aim' ? 'aim' : 'tap';
        state.reducedMotion = stored.reducedMotion == null
            ? Boolean(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)
            : Boolean(stored.reducedMotion);
        state.colorSafeWarnings = stored.colorSafeWarnings !== false;
        state.uiScale = clamp(Number(stored.uiScale == null ? 1 : stored.uiScale), 0.9, 1.2);

        state.leaderboards = parseStoredJson(LEADERBOARD_KEY, {});
        state.replays = sanitizeReplayList(parseStoredJson(REPLAYS_KEY, []));
    }

    function loadBestScore() {
        try {
            const stored = window.localStorage ? window.localStorage.getItem('harmontide.dropGame.bestScore') : null;
            const value = Number(stored || 0);
            return Number.isFinite(value) && value > 0 ? Math.round(value) : 0;
        } catch (error) {
            return 0;
        }
    }

    function saveBestScore() {
        if (state.score <= state.bestScore) {
            return;
        }
        state.bestScore = Math.round(state.score);
        try {
            if (window.localStorage) {
                window.localStorage.setItem('harmontide.dropGame.bestScore', String(state.bestScore));
            }
        } catch (error) {
            // Best score is a convenience only; gameplay should continue if storage is blocked.
        }
    }

    function sanitizeReplayList(value) {
        if (!Array.isArray(value)) {
            return [];
        }
        return value
            .filter(replay => replay && typeof replay === 'object' && Array.isArray(replay.inputs) && replay.seed)
            .slice(0, 16)
            .map(replay => Object.assign({}, replay, {
                version: Number(replay.version || REPLAY_VERSION),
                id: String(replay.id || ('replay-' + Date.now().toString(36))),
                score: Math.max(0, Math.round(Number(replay.score || 0))),
                inputs: replay.inputs
                    .map(input => ({
                        t: Math.max(0, Math.round(Number(input.t || 0))),
                        x: Number(input.x)
                    }))
                    .filter(input => Number.isFinite(input.x))
                    .slice(0, 500)
            }));
    }

    function saveReplays() {
        state.replays = sanitizeReplayList(state.replays)
            .sort((a, b) => Number(b.savedAt || 0) - Number(a.savedAt || 0))
            .slice(0, 16);
        storeJson(REPLAYS_KEY, state.replays);
        renderReplaySelect();
    }

    function replayLabel(replay) {
        const date = replay.savedAt ? new Date(replay.savedAt).toLocaleDateString() : 'Saved run';
        return formatScore(replay.score || 0) + ' - ' + (replay.themeName || 'Pack') + ' - ' + (replay.challengeLabel || replay.challenge || 'Standard') + ' - ' + date;
    }

    function buildReplayData() {
        return {
            version: REPLAY_VERSION,
            id: 'replay-' + Date.now().toString(36) + '-' + Math.floor(Math.random() * 0xFFFF).toString(36),
            savedAt: Date.now(),
            score: Math.round(state.score),
            themeId: state.theme ? state.theme.id : '',
            themeName: state.theme ? state.theme.name : '',
            mode: state.mode,
            challenge: state.challenge,
            challengeLabel: challengeLabel(),
            seed: state.seed,
            inputs: state.recordedInputs.slice(0, 500)
        };
    }

    function saveRunReplay() {
        if (state.replaying || state.score <= 0 || !state.recordedInputs.length) {
            return null;
        }
        const replay = buildReplayData();
        state.replays.unshift(replay);
        saveReplays();
        return replay;
    }

    function renderReplaySelect() {
        if (!replaySelectEl) {
            return;
        }
        replaySelectEl.innerHTML = '';
        if (!state.replays.length) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No saved replays';
            replaySelectEl.appendChild(option);
            return;
        }
        state.replays.forEach(replay => {
            const option = document.createElement('option');
            option.value = replay.id;
            option.textContent = replayLabel(replay);
            replaySelectEl.appendChild(option);
        });
    }

    function selectedReplay() {
        const id = replaySelectEl ? replaySelectEl.value : '';
        return state.replays.find(replay => replay.id === id) || state.replays[0] || null;
    }

    function leaderboardScopeForEntry(entry) {
        return [entry.themeId || 'unknown', entry.mode || 'practice', entry.challenge || 'standard'].join('|');
    }

    function currentLeaderboardScope() {
        return [state.theme ? state.theme.id : 'unknown', state.mode, state.challenge].join('|');
    }

    function flattenLeaderboards() {
        return Object.keys(state.leaderboards || {}).reduce((items, key) => {
            const entries = Array.isArray(state.leaderboards[key]) ? state.leaderboards[key] : [];
            return items.concat(entries);
        }, []);
    }

    function saveLeaderboards() {
        storeJson(LEADERBOARD_KEY, state.leaderboards);
        renderLeaderboard();
    }

    function recordLeaderboard(replay) {
        if (state.replaying || state.score <= 0) {
            return;
        }
        const entry = {
            id: 'score-' + Date.now().toString(36) + '-' + Math.floor(Math.random() * 0xFFFF).toString(36),
            score: Math.round(state.score),
            at: Date.now(),
            themeId: state.theme ? state.theme.id : '',
            themeName: state.theme ? state.theme.name : '',
            mode: state.mode,
            challenge: state.challenge,
            challengeLabel: challengeLabel(),
            seed: state.seed,
            replayId: replay ? replay.id : ''
        };
        const scope = leaderboardScopeForEntry(entry);
        const entries = Array.isArray(state.leaderboards[scope]) ? state.leaderboards[scope] : [];
        entries.push(entry);
        state.leaderboards[scope] = entries.sort((a, b) => b.score - a.score).slice(0, 10);
        saveLeaderboards();
    }

    function renderLeaderboard() {
        if (!leaderboardListEl) {
            return;
        }
        const showAll = leaderboardScopeEl && leaderboardScopeEl.value === 'all';
        const entries = showAll
            ? flattenLeaderboards().sort((a, b) => b.score - a.score).slice(0, 10)
            : (state.leaderboards[currentLeaderboardScope()] || []);
        leaderboardListEl.innerHTML = '';
        if (!entries.length) {
            const empty = document.createElement('li');
            empty.className = 'drop-game-empty-row';
            empty.textContent = 'No local scores yet';
            leaderboardListEl.appendChild(empty);
            return;
        }
        entries.forEach(entry => {
            const item = document.createElement('li');
            const score = document.createElement('strong');
            const meta = document.createElement('span');
            const date = entry.at ? new Date(entry.at).toLocaleDateString() : '';
            score.textContent = formatScore(entry.score);
            meta.textContent = (entry.themeName || 'Pack') + ' - ' + (entry.challengeLabel || entry.challenge || 'Standard') + (date ? ' - ' + date : '');
            item.appendChild(score);
            item.appendChild(meta);
            leaderboardListEl.appendChild(item);
        });
    }

    function startReplay(replay) {
        const clean = sanitizeReplayList([replay])[0];
        if (!clean) {
            setStatus('Replay data is not valid.');
            return;
        }
        const replayTheme = themeById.get(clean.themeId) || state.theme || themes[0];
        state.pendingReplay = clean;
        state.mode = clean.mode === 'daily' ? 'daily' : 'practice';
        state.challenge = CHALLENGES[clean.challenge] ? clean.challenge : 'standard';
        state.startupPack = 'remember';
        syncSettingsUi();
        loadThemeById(replayTheme.id, 'Replay loaded.').catch(reportLoadError);
    }

    function importReplayFromText() {
        if (!replayJsonEl) {
            return;
        }
        try {
            const parsed = JSON.parse(replayJsonEl.value);
            const clean = sanitizeReplayList([parsed])[0];
            if (!clean) {
                throw new Error('Replay JSON is missing seed or inputs.');
            }
            clean.id = 'replay-' + Date.now().toString(36) + '-import';
            clean.savedAt = Date.now();
            state.replays.unshift(clean);
            saveReplays();
            if (replaySelectEl) {
                replaySelectEl.value = clean.id;
            }
            setStatus('Replay imported.');
        } catch (error) {
            setStatus(error && error.message ? error.message : 'Replay import failed.');
        }
    }

    function loadImage(src) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            image.onload = () => resolve(image);
            image.onerror = () => reject(new Error('Could not load ' + src));
            image.src = src;
        });
    }

    function makeCanvas(width, height) {
        const temp = document.createElement('canvas');
        temp.width = Math.max(1, Math.round(width));
        temp.height = Math.max(1, Math.round(height));
        return temp;
    }

    function alphaBounds(image, alphaThreshold) {
        const scan = makeCanvas(image.naturalWidth || image.width, image.naturalHeight || image.height);
        const scanCtx = scan.getContext('2d', { willReadFrequently: true });
        scanCtx.drawImage(image, 0, 0);

        const width = scan.width;
        const height = scan.height;
        const pixels = scanCtx.getImageData(0, 0, width, height).data;
        let minX = width;
        let minY = height;
        let maxX = -1;
        let maxY = -1;

        for (let y = 0; y < height; y += 1) {
            for (let x = 0; x < width; x += 1) {
                if (pixels[((y * width + x) * 4) + 3] > alphaThreshold) {
                    if (x < minX) {
                        minX = x;
                    }
                    if (y < minY) {
                        minY = y;
                    }
                    if (x > maxX) {
                        maxX = x;
                    }
                    if (y > maxY) {
                        maxY = y;
                    }
                }
            }
        }

        if (maxX < minX || maxY < minY) {
            return { x: 0, y: 0, width: width, height: height };
        }

        minX = Math.max(0, minX - 2);
        minY = Math.max(0, minY - 2);
        maxX = Math.min(width - 1, maxX + 2);
        maxY = Math.min(height - 1, maxY + 2);

        return {
            x: minX,
            y: minY,
            width: maxX - minX + 1,
            height: maxY - minY + 1
        };
    }

    function drawCroppedImageToCanvas(image, crop, width, height) {
        const temp = makeCanvas(width, height);
        const tempCtx = temp.getContext('2d', { willReadFrequently: true });
        tempCtx.drawImage(
            image,
            crop.x,
            crop.y,
            crop.width,
            crop.height,
            0,
            0,
            temp.width,
            temp.height
        );
        return temp;
    }

    function ellipseVertices(width, height, sides, padding) {
        const count = Math.max(8, sides || 12);
        const radiusX = Math.max(8, (width * 0.5 * 0.9) + padding);
        const radiusY = Math.max(8, (height * 0.5 * 0.9) + padding);
        const points = [];

        for (let i = 0; i < count; i += 1) {
            const angle = (Math.PI * 2 * i) / count;
            points.push({
                x: Math.cos(angle) * radiusX,
                y: Math.sin(angle) * radiusY
            });
        }

        return points;
    }

    function distanceToSegment(point, start, end) {
        const dx = end.x - start.x;
        const dy = end.y - start.y;
        if (!dx && !dy) {
            return Math.hypot(point.x - start.x, point.y - start.y);
        }
        const t = clamp(((point.x - start.x) * dx + (point.y - start.y) * dy) / (dx * dx + dy * dy), 0, 1);
        return Math.hypot(point.x - (start.x + dx * t), point.y - (start.y + dy * t));
    }

    function simplifyVertices(points, tolerance) {
        if (!points || points.length <= 8 || tolerance <= 0) {
            return points || [];
        }

        let simplified = points.slice();
        let changed = true;
        while (changed && simplified.length > 8) {
            changed = false;
            for (let i = 0; i < simplified.length && simplified.length > 8; i += 1) {
                const previous = simplified[(i - 1 + simplified.length) % simplified.length];
                const current = simplified[i];
                const next = simplified[(i + 1) % simplified.length];
                if (distanceToSegment(current, previous, next) <= tolerance) {
                    simplified.splice(i, 1);
                    changed = true;
                    i -= 1;
                }
            }
        }
        return simplified;
    }

    function alphaSupportVertices(image, crop, width, height, sides, padding, alphaThreshold, simplifyTolerance, maxVertices) {
        const requested = Math.min(Math.max(12, sides || 24), Math.max(12, maxVertices || 24));
        const count = Math.max(12, requested);
        const temp = drawCroppedImageToCanvas(image, crop, width, height);
        const tempCtx = temp.getContext('2d', { willReadFrequently: true });
        const pixels = tempCtx.getImageData(0, 0, temp.width, temp.height).data;
        const cx = temp.width / 2;
        const cy = temp.height / 2;
        const points = [];

        for (let i = 0; i < count; i += 1) {
            const angle = (Math.PI * 2 * i) / count;
            const dx = Math.cos(angle);
            const dy = Math.sin(angle);
            let best = 0;

            for (let y = 0; y < temp.height; y += 2) {
                for (let x = 0; x < temp.width; x += 2) {
                    if (pixels[((y * temp.width + x) * 4) + 3] <= alphaThreshold) {
                        continue;
                    }
                    const projection = ((x + 0.5) - cx) * dx + ((y + 0.5) - cy) * dy;
                    if (projection > best) {
                        best = projection;
                    }
                }
            }

            const radius = Math.max(8, best + padding);
            points.push({
                x: dx * radius,
                y: dy * radius
            });
        }

        return simplifyVertices(points, simplifyTolerance || 0);
    }

    function prepareAsset(entry, image) {
        const pipeline = state.colliderPipeline;
        const alphaThreshold = pipeline.alphaThreshold;
        const crop = alphaBounds(image, alphaThreshold);
        const manualScale = 1;
        const targetDiameter = Math.max(24, Number(entry.radius || 24) * 2 * Number(entry.scale || 1));
        const scale = (targetDiameter * manualScale) / Math.max(crop.width, crop.height);
        const width = Math.max(24, Math.round(crop.width * scale));
        const height = Math.max(24, Math.round(crop.height * scale));
        const collider = entry.collider || {};
        const padding = Number(collider.padding || 0) + Number(pipeline.padding || 0);
        const normalSides = Number(collider.normalSides || 12);
        const preciseSides = Number(collider.preciseSides || 24);
        const maxVertices = Math.max(14, Math.round(Number(pipeline.maxVertices || 24)));
        const materialKey = entry.material || 'food';
        const material = materialFor(materialKey);
        const normalVertices = ellipseVertices(width, height, Math.min(normalSides, maxVertices), padding);
        const preciseVertices = alphaSupportVertices(
            image,
            crop,
            width,
            height,
            preciseSides,
            padding,
            alphaThreshold,
            Number(pipeline.simplifyTolerance || 0),
            maxVertices
        );

        return Object.assign({}, entry, {
            imageElement: image,
            crop: crop,
            width: width,
            height: height,
            simpleRadius: Math.max(9, Math.max(width, height) * 0.5 * Number(collider.circleScale || 0.9)),
            normalVertices: normalVertices,
            preciseVertices: preciseVertices,
            manualVertices: false,
            material: materialKey,
            materialLabel: material.label,
            materialColor: material.color,
            mass: Math.max(0.1, Number(entry.mass || 1) * material.massScale),
            friction: clamp(Number(entry.friction || 0.75) + material.frictionOffset, 0, 1),
            restitution: clamp(Number(entry.restitution || 0.04) + material.restitutionOffset, 0, 1)
        });
    }

    function buildLowTierBag() {
        state.lowTierBag = [];
        state.assets.forEach(asset => {
            const weight = Number(asset.spawnWeight || 0);
            for (let i = 0; i < weight; i += 1) {
                state.lowTierBag.push(asset.tier);
            }
        });
        if (!state.lowTierBag.length) {
            state.lowTierBag = [0, 0, 1, 1, 2];
        }
    }

    function chooseNextTier() {
        const challenge = currentChallenge();
        if (Array.isArray(challenge.fixedSequence) && challenge.fixedSequence.length) {
            const tier = challenge.fixedSequence[state.fixedSequenceIndex % challenge.fixedSequence.length];
            state.fixedSequenceIndex += 1;
            return tier;
        }
        return state.lowTierBag[Math.floor(state.queueRng() * state.lowTierBag.length)];
    }

    function rollRareVariant(asset, boost) {
        if (!asset) {
            return asset;
        }
        const chanceBoost = Number(boost || 1);
        for (let i = 0; i < RARE_VARIANTS.length; i += 1) {
            const variant = RARE_VARIANTS[i];
            if (state.queueRng() < variant.chance * chanceBoost) {
                return Object.assign({}, asset, {
                    variant: variant,
                    variantId: variant.id,
                    variantLabel: variant.label,
                    dropPoints: Math.round((asset.dropPoints || 1) * variant.scoreMultiplier),
                    mergePoints: Math.round((asset.mergePoints || 10) * variant.scoreMultiplier)
                });
            }
        }
        return asset;
    }

    function randomNextAsset() {
        const tier = chooseNextTier();
        return rollRareVariant(state.assetsByTier[tier] || state.assetsByTier[0], 1);
    }

    function fillQueue() {
        while (state.queue.length < WORLD.queueSize) {
            state.queue.push(randomNextAsset());
        }
    }

    function advanceQueue() {
        fillQueue();
        state.nextAsset = state.queue.shift() || randomNextAsset();
        fillQueue();
        syncQueueUi();
    }

    function syncQueueUi() {
        state.dropX = clampDropX(state.dropX, state.nextAsset);
        if (nextImageEl && state.nextAsset) {
            nextImageEl.src = state.nextAsset.image;
        }
        if (nextNameEl && state.nextAsset) {
            nextNameEl.textContent = state.nextAsset.name;
        }
        if (nextMaterialEl && state.nextAsset) {
            nextMaterialEl.textContent = state.nextAsset.variantLabel || state.nextAsset.materialLabel;
            nextMaterialEl.style.borderColor = state.nextAsset.variant ? state.nextAsset.variant.aura : state.nextAsset.materialColor;
            nextMaterialEl.style.color = state.nextAsset.variant ? state.nextAsset.variant.aura : state.nextAsset.materialColor;
        }
        if (!queueListEl) {
            return;
        }

        queueListEl.innerHTML = '';
        state.queue.forEach((asset, index) => {
            const item = document.createElement('div');
            const image = document.createElement('img');
            const label = document.createElement('span');
            item.className = 'drop-game-queue-item';
            item.title = asset.name;
            image.alt = '';
            image.src = asset.image;
            label.textContent = index + 1 + ' ' + (asset.variantLabel || asset.materialLabel);
            if (asset.variant) {
                item.classList.add('is-rare');
            }
            item.appendChild(image);
            item.appendChild(label);
            queueListEl.appendChild(item);
        });
    }

    function renderTierList() {
        if (!tierListEl) {
            return;
        }

        tierListEl.innerHTML = '';
        state.assets.forEach(asset => {
            const item = document.createElement('div');
            const image = document.createElement('img');
            const body = document.createElement('div');
            const text = document.createElement('span');
            const material = document.createElement('em');
            item.className = 'drop-game-tier';
            image.alt = '';
            image.src = asset.image;
            text.textContent = 'T' + (asset.tier + 1) + ' ' + asset.name;
            material.textContent = asset.materialLabel + (asset.manualVertices ? ' custom' : '');
            material.style.color = asset.materialColor;
            body.appendChild(text);
            body.appendChild(material);
            item.appendChild(image);
            item.appendChild(body);
            tierListEl.appendChild(item);
        });
    }

    function syncSeedUi() {
        if (modeLabelEl) {
            modeLabelEl.textContent = state.replaying ? 'Replay' : (state.mode === 'daily' ? 'Daily' : 'Practice');
        }
        if (seedLabelEl) {
            const seedText = state.mode === 'daily' ? todaySeed : state.seed.slice(0, 18);
            const timerText = state.challengeEndsAt > 0 && !state.gameOver
                ? ' - ' + Math.max(0, Math.ceil((state.challengeEndsAt - performance.now()) / 1000)) + 's'
                : '';
            seedLabelEl.textContent = challengeLabel() + ' - ' + seedText + timerText;
        }
    }

    function populateThemeSelect() {
        if (!themeEl) {
            return;
        }
        themeEl.innerHTML = '';
        themes.forEach(theme => {
            const option = document.createElement('option');
            option.value = theme.id;
            option.textContent = theme.name;
            themeEl.appendChild(option);
        });
    }

    function syncSettingsUi() {
        if (themeEl && state.theme) {
            themeEl.value = state.theme.id;
        }
        if (startupPackEl) {
            startupPackEl.value = state.startupPack;
        }
        if (modeEl) {
            modeEl.value = state.mode;
        }
        if (challengeEl) {
            challengeEl.value = state.challenge;
        }
        if (qualityEl) {
            qualityEl.value = state.colliderQuality;
        }
        if (debugEl) {
            debugEl.value = state.debugMode;
        }
        if (volumeEl) {
            volumeEl.value = String(state.audio.volume);
        }
        if (pointerModeEl) {
            pointerModeEl.value = state.pointerMode;
        }
        if (uiScaleEl) {
            uiScaleEl.value = String(state.uiScale);
        }
        if (soundEl) {
            soundEl.checked = state.audio.enabled;
        }
        if (motionEl) {
            motionEl.checked = state.reducedMotion;
        }
        if (colorSafeEl) {
            colorSafeEl.checked = state.colorSafeWarnings;
        }
        applyAccessibilityUi();
        syncSeedUi();
    }

    function updateHud(now) {
        if (scoreEl) {
            scoreEl.textContent = formatScore(state.score);
        }
        if (comboEl) {
            comboEl.textContent = 'x' + Math.max(1, state.combo);
        }
        if (bestScoreEl) {
            bestScoreEl.textContent = formatScore(state.bestScore);
        }
        if (warningEl) {
            warningEl.textContent = state.gameOver ? 'Game over' : (state.dangerProgress > 0 ? 'Warning' : 'Clear');
        }
        if (dangerFillEl) {
            dangerFillEl.style.transform = 'scaleX(' + clamp(state.dangerProgress, 0, 1).toFixed(3) + ')';
        }
        if (cooldownFillEl) {
            const elapsed = now ? now - state.lastDropAt : WORLD.cooldownMs;
            const ready = state.loading || state.gameOver || state.paused ? 0 : clamp(elapsed / WORLD.cooldownMs, 0, 1);
            cooldownFillEl.style.transform = 'scaleX(' + ready.toFixed(3) + ')';
        }
        if (dropBtn) {
            dropBtn.disabled = state.loading || state.paused || state.gameOver || state.replaying || !isDropReady(performance.now());
        }
        if (pauseBtn) {
            pauseBtn.disabled = state.loading || state.gameOver;
            pauseBtn.textContent = state.paused ? 'Resume' : 'Pause';
        }
        syncScoreExchangeUi();
        syncSeedUi();
    }

    function syncGameOverPanel(show) {
        if (!gameOverEl) {
            return;
        }
        gameOverEl.hidden = !show;
        if (finalScoreEl) {
            finalScoreEl.textContent = formatScore(state.score);
        }
        if (finalBestEl) {
            finalBestEl.textContent = formatScore(state.bestScore);
        }
        if (exchangeStatusEl && !show) {
            exchangeStatusEl.textContent = '';
        }
        syncScoreExchangeUi();
    }

    function syncScoreExchangeUi() {
        if (!sendScoreBtn) {
            return;
        }
        const canSend = state.gameOver && !state.replaying && state.score > 0 && !state.scoreSent && !state.scoreSending;
        sendScoreBtn.disabled = !canSend;
        sendScoreBtn.textContent = state.scoreSending ? 'Sending...' : (state.scoreSent ? 'Score Sent' : 'Send Score');
    }

    async function sendScoreExchange() {
        if (state.scoreSending || state.scoreSent || !state.gameOver || state.replaying || state.score <= 0) {
            syncScoreExchangeUi();
            return;
        }

        state.scoreSending = true;
        syncScoreExchangeUi();
        if (exchangeStatusEl) {
            exchangeStatusEl.textContent = 'Sending score...';
        }

        try {
            const response = await fetch(SCORE_EXCHANGE_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    game: 'dropgame',
                    score: Math.max(0, Math.round(state.score))
                })
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(payload.error || 'Score exchange failed.');
            }
            state.scoreSent = true;
            if (typeof window.updateCurrencyDisplay === 'function') {
                window.updateCurrencyDisplay(payload);
            }
            if (exchangeStatusEl) {
                exchangeStatusEl.textContent = payload.cash == null
                    ? 'Score sent.'
                    : 'Score sent. Cash balance: ' + payload.cash + '.';
            }
        } catch (error) {
            if (exchangeStatusEl) {
                exchangeStatusEl.textContent = error && error.message ? error.message : 'Score exchange failed.';
            }
        } finally {
            state.scoreSending = false;
            syncScoreExchangeUi();
        }
    }

    function addScore(points) {
        const multiplier = currentChallenge().scoreMultiplier || 1;
        state.score += Math.max(0, Math.round(points * multiplier));
        if (!state.replaying) {
            saveBestScore();
        }
        updateHud(performance.now());
    }

    function ensureAudio() {
        if (!state.audio.enabled) {
            return null;
        }
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) {
            return null;
        }
        if (!state.audio.ctx) {
            state.audio.ctx = new AudioContext();
        }
        if (state.audio.ctx.state === 'suspended') {
            state.audio.ctx.resume();
        }
        return state.audio.ctx;
    }

    function playTone(frequency, duration, type, gainValue, delay) {
        const audioCtx = ensureAudio();
        if (!audioCtx) {
            return;
        }

        const startAt = audioCtx.currentTime + (delay || 0);
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = type || 'sine';
        osc.frequency.setValueAtTime(frequency, startAt);
        gain.gain.setValueAtTime(0.0001, startAt);
        gain.gain.exponentialRampToValueAtTime((gainValue || 0.08) * state.audio.volume, startAt + 0.012);
        gain.gain.exponentialRampToValueAtTime(0.0001, startAt + duration);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start(startAt);
        osc.stop(startAt + duration + 0.02);
    }

    function playSfx(name, materialKey) {
        if (!state.audio.enabled) {
            return;
        }
        const material = materialFor(materialKey);
        if (name === 'drop') {
            playTone(material.tone[0] * 0.82, 0.08, material.tone[2], 0.06, 0);
            playTone(150, 0.07, 'sine', 0.045, 0.04);
        } else if (name === 'collision') {
            playTone(material.tone[0], material.tone[1], material.tone[2], 0.026, 0);
        } else if (name === 'merge') {
            playTone(material.tone[0] * 1.4, 0.08, 'sine', 0.075, 0);
            playTone(material.tone[0] * 1.86, 0.1, 'triangle', 0.07, 0.055);
        } else if (name === 'warning') {
            playTone(620, 0.1, 'sawtooth', 0.045, 0);
            playTone(420, 0.1, 'sawtooth', 0.035, 0.11);
        } else if (name === 'gameover') {
            playTone(220, 0.16, 'triangle', 0.08, 0);
            playTone(150, 0.22, 'triangle', 0.07, 0.14);
            playTone(95, 0.3, 'sine', 0.065, 0.32);
        }
    }

    function binLeft() {
        return WORLD.wall + (WORLD.sideInset || 0);
    }

    function binRight() {
        return WORLD.width - WORLD.wall - (WORLD.sideInset || 0);
    }

    function clampDropX(value, asset) {
        const current = asset || state.nextAsset || state.assetsByTier[0];
        const half = current ? Math.max(current.width, current.height) * 0.5 : 28;
        const min = binLeft() + half + 2;
        const max = binRight() - half - 2;
        return clamp(value, min, Math.max(min, max));
    }

    function itemPlugin(body) {
        return body && body.plugin && body.plugin.dropGame ? body.plugin.dropGame : null;
    }

    function worldItems() {
        if (!state.engine) {
            return [];
        }
        return Composite.allBodies(state.engine.world).filter(body => !body.isStatic && itemPlugin(body));
    }

    function makeWalls() {
        const wallOptions = {
            isStatic: true,
            label: 'drop-game-wall',
            friction: 0.85,
            restitution: 0.02,
            render: { visible: false }
        };
        const inset = WORLD.sideInset || 0;
        Composite.add(state.engine.world, [
            Bodies.rectangle(WORLD.width / 2, WORLD.floorY + 24, WORLD.width + 48, 48, wallOptions),
            Bodies.rectangle(inset, WORLD.height / 2, 28, WORLD.height * 2, wallOptions),
            Bodies.rectangle(WORLD.width - inset, WORLD.height / 2, 28, WORLD.height * 2, wallOptions)
        ]);
    }

    function bodyOptions(asset) {
        return {
            label: 'drop-game-item:tier-' + asset.tier,
            friction: asset.friction,
            frictionStatic: Math.min(1, asset.friction + 0.12),
            frictionAir: 0.004,
            restitution: asset.restitution,
            density: 0.0018,
            slop: 0.022
        };
    }

    function makeItemBody(asset, x, y, source) {
        const options = bodyOptions(asset);
        let body = null;

        if (state.colliderQuality === 'simple') {
            body = Bodies.circle(x, y, asset.simpleRadius, options, 18);
        } else {
            const vertices = state.colliderQuality === 'precise' ? asset.preciseVertices : asset.normalVertices;
            try {
                body = Bodies.fromVertices(x, y, [vertices], options, true);
            } catch (error) {
                body = Bodies.circle(x, y, asset.simpleRadius, options, 18);
            }
        }

        Body.setMass(body, asset.mass);
        Body.setAngle(body, (gameRandom() - 0.5) * 0.16);
        Body.setAngularVelocity(body, (gameRandom() - 0.5) * 0.025);
        body.plugin = body.plugin || {};
        body.plugin.dropGame = {
            asset: asset,
            tier: asset.tier,
            source: source || 'drop',
            spawnedAt: performance.now(),
            dangerSince: 0,
            merging: false,
            glowUntil: source === 'merge' ? performance.now() + 560 : 0
        };
        Composite.add(state.engine.world, body);
        return body;
    }

    function spawnBounds(asset, x, y) {
        const halfW = (asset.width * 0.5) + 3;
        const halfH = (asset.height * 0.5) + 3;
        return {
            min: { x: x - halfW, y: y - halfH },
            max: { x: x + halfW, y: y + halfH }
        };
    }

    function spawnIsClear(asset, x, y) {
        const bounds = spawnBounds(asset, x, y);
        if (bounds.min.x < binLeft() || bounds.max.x > binRight()) {
            return false;
        }

        return !worldItems().some(body => Bounds.overlaps(bounds, body.bounds));
    }

    function isDropReady(now, force) {
        return !state.loading && !state.paused && !state.gameOver && (force || now - state.lastDropAt >= WORLD.cooldownMs);
    }

    function dropCurrentItem(options) {
        const opts = options || {};
        const now = performance.now();
        if (!opts.fromReplay) {
            ensureAudio();
        }

        if (!state.nextAsset || !isDropReady(now, opts.force)) {
            return;
        }

        const asset = state.nextAsset;
        state.dropX = clampDropX(state.dropX, asset);
        if (!spawnIsClear(asset, state.dropX, WORLD.spawnY)) {
            setStatus('Spawn lane blocked.');
            playSfx('warning');
            return;
        }

        const body = makeItemBody(asset, state.dropX, WORLD.spawnY, 'drop');
        Body.setVelocity(body, { x: 0, y: 0.35 });
        state.lastDropAt = now;
        if (!opts.fromReplay && !state.replaying) {
            state.recordedInputs.push({
                t: Math.max(0, Math.round(now - state.runStartedAt)),
                x: Math.round(state.dropX * 10) / 10
            });
        }
        addScore(asset.dropPoints || 1);
        advanceQueue();
        setStatus('Dropped ' + asset.name + '.');
        playSfx('drop', asset.material);
    }

    function bodyStillInWorld(body) {
        return state.engine && Composite.allBodies(state.engine.world).indexOf(body) !== -1;
    }

    function queueMerge(bodyA, bodyB) {
        const pluginA = itemPlugin(bodyA);
        const pluginB = itemPlugin(bodyB);
        if (!pluginA || !pluginB || pluginA.merging || pluginB.merging) {
            return;
        }
        if (pluginA.tier !== pluginB.tier || pluginA.tier >= state.assets.length - 1) {
            return;
        }

        pluginA.merging = true;
        pluginB.merging = true;
        state.pendingMerges.push([bodyA, bodyB]);

        if (!state.mergeTimer) {
            state.mergeTimer = window.setTimeout(processMerges, 0);
        }
    }

    function processMerges() {
        state.mergeTimer = 0;
        const merges = state.pendingMerges.splice(0);

        merges.forEach(pair => {
            const bodyA = pair[0];
            const bodyB = pair[1];
            const pluginA = itemPlugin(bodyA);
            const pluginB = itemPlugin(bodyB);
            if (!pluginA || !pluginB || !bodyStillInWorld(bodyA) || !bodyStillInWorld(bodyB)) {
                return;
            }

            const baseNextAsset = state.assetsByTier[pluginA.tier + 1];
            const inheritedRare = pluginA.asset.variant || pluginB.asset.variant;
            const nextAsset = rollRareVariant(baseNextAsset, inheritedRare ? 10 : 1);
            if (!nextAsset) {
                pluginA.merging = false;
                pluginB.merging = false;
                return;
            }

            const x = clamp(
                (bodyA.position.x + bodyB.position.x) / 2,
                binLeft() + nextAsset.width * 0.5 + 2,
                binRight() - nextAsset.width * 0.5 - 2
            );
            const y = clamp(
                (bodyA.position.y + bodyB.position.y) / 2,
                WORLD.dangerY + nextAsset.height * 0.35,
                WORLD.floorY - nextAsset.height * 0.5
            );
            const velocity = {
                x: clamp((bodyA.velocity.x + bodyB.velocity.x) * 0.38 + (gameRandom() - 0.5) * 0.9, -3.2, 3.2),
                y: clamp((bodyA.velocity.y + bodyB.velocity.y) * 0.36 - 1.15, -3.8, 1.7)
            };

            Composite.remove(state.engine.world, bodyA);
            Composite.remove(state.engine.world, bodyB);

            const merged = makeItemBody(nextAsset, x, y, 'merge');
            Body.setVelocity(merged, velocity);
            Body.setAngularVelocity(merged, clamp((bodyA.angularVelocity + bodyB.angularVelocity) * 0.45, -0.08, 0.08));
            registerMerge(nextAsset, { x: x, y: y });
        });
    }

    function createMergeEffect(position, asset) {
        const now = performance.now();
        const colors = ['#ffffff', '#1e86ff', '#f2b84b', '#21a784'];
        const particles = [];
        const particleCount = state.reducedMotion ? 0 : 10 + Math.min(8, asset.tier * 2);

        for (let i = 0; i < particleCount; i += 1) {
            const angle = (Math.PI * 2 * i) / particleCount + (visualRandom() - 0.5) * 0.34;
            const speed = 1.2 + visualRandom() * 2.2 + asset.tier * 0.12;
            particles.push({
                x: position.x,
                y: position.y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed - 0.65,
                radius: 2.4 + visualRandom() * 2.6,
                color: colors[i % colors.length]
            });
        }

        state.effects.push({
            x: position.x,
            y: position.y,
            radius: Math.max(asset.width, asset.height) * 0.42,
            startedAt: now,
            duration: 420,
            particles: particles
        });
    }

    function registerMerge(nextAsset, position) {
        const now = performance.now();
        state.combo = now <= state.comboUntil ? Math.min(9, state.combo + 1) : 1;
        state.comboUntil = now + WORLD.comboGraceMs;
        createMergeEffect(position, nextAsset);
        addScore((nextAsset.mergePoints || 10) * state.combo);
        setStatus('Merged into ' + nextAsset.name + '.');
        addShake(5 + nextAsset.tier * 0.35, 190);
        playSfx('merge', nextAsset.material);
    }

    function handleCollisionStart(event) {
        let collisionSound = false;
        let collisionMaterial = 'stone';
        event.pairs.forEach(pair => {
            const bodyA = pair.bodyA;
            const bodyB = pair.bodyB;
            const pluginA = itemPlugin(bodyA);
            const pluginB = itemPlugin(bodyB);

            if (pluginA && pluginB && pluginA.tier === pluginB.tier && pluginA.tier < state.assets.length - 1) {
                queueMerge(bodyA, bodyB);
                return;
            }

            const relativeSpeed = Math.hypot(bodyA.velocity.x - bodyB.velocity.x, bodyA.velocity.y - bodyB.velocity.y);
            if (relativeSpeed > 1.35 && (pluginA || pluginB)) {
                collisionSound = true;
                collisionMaterial = (pluginA && pluginA.asset.material) || (pluginB && pluginB.asset.material) || collisionMaterial;
                if (relativeSpeed > 2.8) {
                    addShake(Math.min(3.8, relativeSpeed * 0.75), 90);
                }
            }
        });

        const now = performance.now();
        if (collisionSound && now - state.lastCollisionSoundAt > 90) {
            state.lastCollisionSoundAt = now;
            playSfx('collision', collisionMaterial);
        }
    }

    function updateDanger(now) {
        let maxProgress = 0;
        worldItems().forEach(body => {
            const plugin = itemPlugin(body);
            if (!plugin || plugin.merging || now - plugin.spawnedAt < 900) {
                return;
            }

            if (body.bounds.min.y < WORLD.dangerY) {
                if (!plugin.dangerSince) {
                    plugin.dangerSince = now;
                }
                maxProgress = Math.max(maxProgress, (now - plugin.dangerSince) / WORLD.dangerGraceMs);
            } else {
                plugin.dangerSince = 0;
            }
        });

        state.dangerProgress = clamp(maxProgress, 0, 1);

        if (state.dangerProgress > 0.55 && now - state.lastWarningSoundAt > 720) {
            state.lastWarningSoundAt = now;
            playSfx('warning');
        }

        if (state.dangerProgress >= 1) {
            endGame();
        }
    }

    function endGame() {
        if (state.gameOver) {
            return;
        }
        if (!state.replaying) {
            saveBestScore();
        }
        const replay = saveRunReplay();
        recordLeaderboard(replay);
        state.gameOver = true;
        state.paused = false;
        setStatus('Game over. Final score: ' + formatScore(state.score) + '.');
        syncGameOverPanel(true);
        addShake(7, 260);
        playSfx('gameover');
        updateHud(performance.now());
    }

    function playReplayInputs(now) {
        if (!state.replaying || !state.replayData || !Array.isArray(state.replayData.inputs)) {
            return;
        }
        const elapsed = now - state.runStartedAt;
        while (state.replayIndex < state.replayData.inputs.length && state.replayData.inputs[state.replayIndex].t <= elapsed + 12) {
            const input = state.replayData.inputs[state.replayIndex];
            state.dropX = clampDropX(Number(input.x), state.nextAsset);
            dropCurrentItem({ fromReplay: true, force: true });
            state.replayIndex += 1;
        }
        if (state.replayIndex >= state.replayData.inputs.length && state.lastStatus !== 'Replay inputs complete.') {
            setStatus('Replay inputs complete.');
        }
    }

    function updateGame(now, delta) {
        const direction = (state.keys.ArrowRight || state.keys.d ? 1 : 0) - (state.keys.ArrowLeft || state.keys.a ? 1 : 0);
        const heldDirection = state.holdDirection || direction;
        if (heldDirection && !state.replaying) {
            state.dropX = clampDropX(state.dropX + heldDirection * 5.8 * (delta / 16.67), state.nextAsset);
        }

        playReplayInputs(now);

        if (state.combo > 1 && now > state.comboUntil) {
            state.combo = 1;
        }

        const challenge = currentChallenge();
        if (challenge.chaos && state.engine) {
            state.engine.gravity.x = Math.sin(now / 720) * 0.24;
            state.engine.gravity.y = challenge.gravityY + Math.sin(now / 940) * 0.12;
        }
        if (state.challengeEndsAt > 0 && now >= state.challengeEndsAt) {
            endGame();
            return;
        }

        updateDanger(now);
        updateHud(now);
    }

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        const dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
        const width = Math.max(1, Math.round(rect.width * dpr));
        const height = Math.max(1, Math.round(rect.height * dpr));
        if (canvas.width !== width || canvas.height !== height) {
            canvas.width = width;
            canvas.height = height;
        }
    }

    function drawBoard() {
        const board = state.theme && state.theme.board ? state.theme.board : {};
        const background = ctx.createLinearGradient(0, 0, 0, WORLD.height);
        background.addColorStop(0, board.top || '#eaf7ff');
        background.addColorStop(0.56, board.mid || '#f7fbf6');
        background.addColorStop(1, board.bottom || '#ffe7df');
        ctx.fillStyle = background;
        ctx.fillRect(0, 0, WORLD.width, WORLD.height);

        ctx.fillStyle = board.wall || '#2d3a3f';
        const inset = WORLD.sideInset || 0;
        ctx.fillRect(0, 0, inset + WORLD.wall, WORLD.height);
        ctx.fillRect(WORLD.width - inset - WORLD.wall, 0, inset + WORLD.wall, WORLD.height);
        ctx.fillRect(0, WORLD.floorY, WORLD.width, WORLD.height - WORLD.floorY);

        ctx.save();
        const pulse = state.dangerProgress > 0 && !state.reducedMotion ? 0.5 + Math.sin(performance.now() / 90) * 0.5 : 0;
        ctx.strokeStyle = state.dangerProgress > 0 ? 'rgba(210, 70, 62, ' + (0.72 + pulse * 0.22).toFixed(2) + ')' : 'rgba(210, 70, 62, 0.42)';
        ctx.lineWidth = 2 + state.dangerProgress * 2.4;
        ctx.setLineDash([8, 8]);
        ctx.beginPath();
        ctx.moveTo(binLeft() + 7, WORLD.dangerY);
        ctx.lineTo(binRight() - 7, WORLD.dangerY);
        ctx.stroke();
        if (state.colorSafeWarnings) {
            ctx.setLineDash([]);
            ctx.fillStyle = state.dangerProgress > 0 ? 'rgba(255, 255, 255, 0.82)' : 'rgba(255, 255, 255, 0.46)';
            for (let x = binLeft() + 10; x < binRight() - 16; x += 18) {
                ctx.fillRect(x, WORLD.dangerY - 5, 9, 10);
            }
            ctx.fillStyle = state.dangerProgress > 0 ? 'rgba(30, 34, 38, 0.86)' : 'rgba(30, 34, 38, 0.52)';
            ctx.font = '700 11px Inter, system-ui, sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'bottom';
            ctx.fillText('DANGER LINE', binRight() - 10, WORLD.dangerY - 7);
        }
        ctx.restore();
    }

    function drawAsset(asset, position, angle, alpha) {
        ctx.save();
        ctx.globalAlpha = alpha == null ? 1 : alpha;
        ctx.translate(position.x, position.y);
        ctx.rotate(angle || 0);
        ctx.drawImage(
            asset.imageElement,
            asset.crop.x,
            asset.crop.y,
            asset.crop.width,
            asset.crop.height,
            -asset.width / 2,
            -asset.height / 2,
            asset.width,
            asset.height
        );
        ctx.restore();
    }

    function drawVariantAura(asset, position, alpha) {
        if (!asset || !asset.variant) {
            return;
        }
        const pulse = state.reducedMotion ? 0.5 : 0.5 + Math.sin(performance.now() / 130) * 0.5;
        const radius = Math.max(asset.width, asset.height) * (0.55 + pulse * 0.05);
        ctx.save();
        ctx.globalAlpha = alpha == null ? 0.5 : alpha;
        ctx.strokeStyle = asset.variant.aura;
        ctx.lineWidth = 3;
        ctx.setLineDash([6, 5]);
        ctx.beginPath();
        ctx.arc(position.x, position.y, radius, 0, Math.PI * 2);
        ctx.stroke();
        ctx.restore();
    }

    function drawColliderVertices(vertices, position, angle, color) {
        if (!vertices || !vertices.length) {
            return;
        }
        const cos = Math.cos(angle || 0);
        const sin = Math.sin(angle || 0);
        ctx.save();
        ctx.strokeStyle = color;
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        vertices.forEach((point, index) => {
            const x = position.x + point.x * cos - point.y * sin;
            const y = position.y + point.x * sin + point.y * cos;
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        ctx.closePath();
        ctx.stroke();
        ctx.restore();
    }

    function drawCircleCollider(radius, position, color) {
        ctx.save();
        ctx.strokeStyle = color;
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.arc(position.x, position.y, radius, 0, Math.PI * 2);
        ctx.stroke();
        ctx.restore();
    }

    function drawBodyCollider(body) {
        const parts = body.parts.length > 1 ? body.parts.slice(1) : [body];
        ctx.save();
        ctx.strokeStyle = 'rgba(28, 125, 118, 0.88)';
        ctx.lineWidth = 1.4;
        parts.forEach(part => {
            if (!part.vertices.length) {
                return;
            }
            ctx.beginPath();
            ctx.moveTo(part.vertices[0].x, part.vertices[0].y);
            for (let i = 1; i < part.vertices.length; i += 1) {
                ctx.lineTo(part.vertices[i].x, part.vertices[i].y);
            }
            ctx.closePath();
            ctx.stroke();
        });
        ctx.restore();
    }

    function drawBodyBounds(body) {
        ctx.save();
        ctx.strokeStyle = 'rgba(210, 70, 62, 0.72)';
        ctx.lineWidth = 1.2;
        ctx.setLineDash([4, 4]);
        ctx.strokeRect(
            body.bounds.min.x,
            body.bounds.min.y,
            body.bounds.max.x - body.bounds.min.x,
            body.bounds.max.y - body.bounds.min.y
        );
        ctx.restore();
    }

    function drawBodyCenter(body) {
        ctx.save();
        ctx.strokeStyle = '#ffffff';
        ctx.fillStyle = 'rgba(14, 23, 38, 0.72)';
        ctx.lineWidth = 1.2;
        ctx.beginPath();
        ctx.arc(body.position.x, body.position.y, 4.5, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(body.position.x - 8, body.position.y);
        ctx.lineTo(body.position.x + 8, body.position.y);
        ctx.moveTo(body.position.x, body.position.y - 8);
        ctx.lineTo(body.position.x, body.position.y + 8);
        ctx.stroke();
        ctx.restore();
    }

    function drawDebugForBody(body) {
        if (state.debugMode === 'off') {
            return;
        }
        drawBodyCollider(body);
        if (state.debugMode === 'centers' || state.debugMode === 'bounds') {
            drawBodyCenter(body);
        }
        if (state.debugMode === 'bounds') {
            drawBodyBounds(body);
        }
    }

    function drawHeldPreview() {
        if (!state.nextAsset || state.gameOver) {
            return;
        }

        const ready = isDropReady(performance.now()) && !state.paused;
        ctx.save();
        ctx.globalAlpha = ready ? 1 : 0.42;
        ctx.strokeStyle = 'rgba(45, 58, 63, 0.24)';
        ctx.lineWidth = 1.2;
        ctx.beginPath();
        ctx.moveTo(state.dropX, 20);
        ctx.lineTo(state.dropX, WORLD.floorY - 2);
        ctx.stroke();
        ctx.restore();

        drawVariantAura(state.nextAsset, { x: state.dropX, y: WORLD.spawnY }, ready ? 0.44 : 0.22);
        drawAsset(state.nextAsset, { x: state.dropX, y: WORLD.spawnY }, 0, ready ? 0.54 : 0.32);
        if (state.debugMode !== 'off') {
            if (state.colliderQuality === 'simple') {
                drawCircleCollider(state.nextAsset.simpleRadius, { x: state.dropX, y: WORLD.spawnY }, 'rgba(28, 125, 118, 0.58)');
            } else {
                const vertices = state.colliderQuality === 'precise' ? state.nextAsset.preciseVertices : state.nextAsset.normalVertices;
                drawColliderVertices(vertices, { x: state.dropX, y: WORLD.spawnY }, 0, 'rgba(28, 125, 118, 0.58)');
            }
            if (state.debugMode === 'centers' || state.debugMode === 'bounds') {
                drawBodyCenter({ position: { x: state.dropX, y: WORLD.spawnY } });
            }
        }
    }

    function drawBodies() {
        const now = performance.now();
        worldItems().forEach(body => {
            const plugin = itemPlugin(body);
            if (plugin.glowUntil && plugin.glowUntil > now && !state.reducedMotion) {
                const progress = clamp((plugin.glowUntil - now) / 560, 0, 1);
                ctx.save();
                ctx.globalAlpha = progress * 0.34;
                ctx.fillStyle = plugin.asset.materialColor;
                ctx.beginPath();
                ctx.arc(body.position.x, body.position.y, Math.max(plugin.asset.width, plugin.asset.height) * (0.56 + (1 - progress) * 0.24), 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            }
            drawVariantAura(plugin.asset, body.position, 0.42);
            drawAsset(plugin.asset, body.position, body.angle, 1);
        });

        if (state.debugMode === 'off') {
            return;
        }

        worldItems().forEach(drawDebugForBody);
    }

    function drawMergeEffects(now) {
        if (!state.effects.length) {
            return;
        }

        state.effects = state.effects.filter(effect => {
            const age = now - effect.startedAt;
            const progress = clamp(age / effect.duration, 0, 1);
            if (progress >= 1) {
                return false;
            }

            const easeOut = 1 - Math.pow(1 - progress, 3);
            ctx.save();
            ctx.globalAlpha = 1 - progress;
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.92)';
            ctx.lineWidth = 4 - progress * 2;
            ctx.beginPath();
            ctx.arc(effect.x, effect.y, effect.radius + easeOut * 36, 0, Math.PI * 2);
            ctx.stroke();

            ctx.globalAlpha = 0.36 * (1 - progress);
            ctx.fillStyle = '#ffffff';
            ctx.beginPath();
            ctx.arc(effect.x, effect.y, effect.radius * (0.55 + easeOut * 0.45), 0, Math.PI * 2);
            ctx.fill();

            effect.particles.forEach(particle => {
                const px = particle.x + particle.vx * age * 0.065;
                const py = particle.y + particle.vy * age * 0.065 + 0.0009 * age * age;
                ctx.globalAlpha = 0.9 * (1 - progress);
                ctx.fillStyle = particle.color;
                ctx.beginPath();
                ctx.arc(px, py, particle.radius * (1 - progress * 0.45), 0, Math.PI * 2);
                ctx.fill();
            });
            ctx.restore();
            return true;
        });
    }

    function addShake(power, duration) {
        if (state.reducedMotion) {
            return;
        }
        state.shakePower = Math.max(state.shakePower, power || 0);
        state.shakeUntil = Math.max(state.shakeUntil, performance.now() + (duration || 160));
    }

    function drawOverlay() {
        if (!state.paused && !state.gameOver && !state.loading) {
            return;
        }
        const label = state.loading ? 'Loading' : (state.gameOver ? 'Game Over' : 'Paused');
        ctx.save();
        ctx.fillStyle = 'rgba(14, 23, 38, 0.44)';
        ctx.fillRect(0, 0, WORLD.width, WORLD.height);
        ctx.fillStyle = '#ffffff';
        ctx.font = '700 32px Inter, system-ui, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(label, WORLD.width / 2, WORLD.height / 2);
        ctx.restore();
    }

    function render() {
        resizeCanvas();
        ctx.setTransform(canvas.width / WORLD.width, 0, 0, canvas.height / WORLD.height, 0, 0);
        ctx.clearRect(0, 0, WORLD.width, WORLD.height);
        if (state.shakeUntil > performance.now() && !state.reducedMotion) {
            const remaining = (state.shakeUntil - performance.now()) / 220;
            const power = Math.max(0, state.shakePower * remaining);
            ctx.translate((visualRandom() - 0.5) * power, (visualRandom() - 0.5) * power);
        } else {
            state.shakePower = 0;
        }
        drawBoard();
        drawHeldPreview();
        drawBodies();
        drawMergeEffects(performance.now());
        drawOverlay();
    }

    function gameLoop(now) {
        state.raf = requestAnimationFrame(gameLoop);
        if (!state.lastFrameAt) {
            state.lastFrameAt = now;
        }
        const delta = clamp(now - state.lastFrameAt, 0, 33.34);
        state.lastFrameAt = now;

        if (!state.loading && !state.paused && !state.gameOver && state.engine) {
            Engine.update(state.engine, delta || 16.67);
            updateGame(now, delta || 16.67);
        } else {
            updateHud(now);
        }
        render();
    }

    function applyThemeLook(theme) {
        const host = document.querySelector('.drop-game-page');
        if (host && theme) {
            host.style.setProperty('--drop-game-accent', theme.accent || '#277967');
        }
    }

    function applyAccessibilityUi() {
        const host = document.querySelector('.drop-game-page');
        if (!host) {
            return;
        }
        host.style.setProperty('--drop-game-ui-scale', String(state.uiScale || 1));
        host.classList.toggle('drop-game-color-safe', Boolean(state.colorSafeWarnings));
    }

    async function loadThemeById(themeId, message) {
        const theme = themeById.get(themeId) || themes[0];
        state.theme = theme;
        state.loading = true;
        state.paused = false;
        state.gameOver = false;
        resetRunProgress();
        syncSettingsUi();
        applyThemeLook(theme);
        setStatus('Loading ' + theme.name + '...');
        render();

        let loaded = state.preparedThemeCache.get(theme.id);
        if (!loaded) {
            loaded = [];
            for (let i = 0; i < theme.items.length; i += 1) {
                const image = await loadImage(theme.items[i].image);
                loaded.push(prepareAsset(theme.items[i], image));
                setStatus('Loading ' + theme.name + ' ' + loaded.length + '/' + theme.items.length + '...');
            }
            loaded.sort((a, b) => a.tier - b.tier);
            state.preparedThemeCache.set(theme.id, loaded);
        }

        state.assets = loaded;
        state.assetsByTier = [];
        state.assets.forEach(asset => {
            state.assetsByTier[asset.tier] = asset;
        });
        buildLowTierBag();
        renderTierList();
        renderLeaderboard();
        saveSettings();
        resetWorld(message || theme.name + ' ready.');
    }

    function resetWorld(message) {
        if (!state.assets.length) {
            return;
        }
        const replay = state.pendingReplay;
        state.pendingReplay = null;
        applyChallengeWorld();
        if (state.raf) {
            cancelAnimationFrame(state.raf);
            state.raf = 0;
        }

        const challenge = currentChallenge();
        state.engine = Engine.create();
        state.engine.enableSleeping = true;
        state.engine.positionIterations = 10;
        state.engine.velocityIterations = 8;
        state.engine.constraintIterations = 3;
        state.engine.gravity.y = challenge.gravityY || 1;
        state.engine.gravity.x = 0;
        state.engine.gravity.scale = challenge.gravityScale || 0.00112;
        state.paused = false;
        state.gameOver = false;
        state.loading = false;
        state.runStartedAt = performance.now();
        resetRunProgress();
        state.replayData = replay || null;
        state.replayIndex = 0;
        state.replaying = Boolean(replay);
        state.fixedSequenceIndex = 0;
        state.challengeEndsAt = challenge.timeLimitMs ? state.runStartedAt + challenge.timeLimitMs : 0;
        syncGameOverPanel(false);
        state.dropX = clampDropX(WORLD.width / 2, state.nextAsset);
        configureRng(replay ? replay.seed : null);

        makeWalls();
        Events.on(state.engine, 'collisionStart', handleCollisionStart);
        state.queue = [];
        advanceQueue();
        setStatus(message || 'Ready.');
        renderLeaderboard();
        updateHud(performance.now());
        state.lastFrameAt = 0;
        state.raf = requestAnimationFrame(gameLoop);
    }

    function setPaused(paused) {
        if (state.loading || state.gameOver) {
            return;
        }
        state.paused = paused;
        setStatus(paused ? 'Paused.' : 'Resumed.');
        updateHud(performance.now());
    }

    function pointerToWorldX(event) {
        const rect = canvas.getBoundingClientRect();
        return ((event.clientX - rect.left) / rect.width) * WORLD.width;
    }

    function reportLoadError(error) {
        state.loading = false;
        setStatus(error && error.message ? error.message : 'Drop game failed to update.');
        render();
    }

    function clearMergeTimer() {
        if (state.mergeTimer) {
            window.clearTimeout(state.mergeTimer);
            state.mergeTimer = 0;
        }
    }

    function resetRunProgress() {
        clearMergeTimer();
        state.pendingMerges = [];
        state.effects = [];
        state.score = 0;
        state.combo = 1;
        state.comboUntil = 0;
        state.lastDropAt = -Infinity;
        state.dangerProgress = 0;
        state.scoreSent = false;
        state.scoreSending = false;
        state.recordedInputs = [];
        if (exchangeStatusEl) {
            exchangeStatusEl.textContent = '';
        }
        updateHud(performance.now());
        syncGameOverPanel(false);
    }

    function rebuildCurrentTheme(message) {
        if (!state.theme) {
            return;
        }
        state.preparedThemeCache.delete(state.theme.id);
        loadThemeById(state.theme.id, message).catch(reportLoadError);
    }

    function bindControls() {
        window.addEventListener('resize', resizeCanvas);
        window.addEventListener('keydown', event => {
            const key = event.key.length === 1 ? event.key.toLowerCase() : event.key;
            if (key === 'ArrowLeft' || key === 'ArrowRight' || key === 'a' || key === 'd') {
                state.keys[key] = true;
                event.preventDefault();
            }
            if (key === ' ' || key === 'Enter' || key === 'ArrowDown' || key === 's') {
                if (!state.replaying) {
                    dropCurrentItem();
                }
                event.preventDefault();
            }
            if (key === 'p') {
                setPaused(!state.paused);
                event.preventDefault();
            }
        });
        window.addEventListener('keyup', event => {
            const key = event.key.length === 1 ? event.key.toLowerCase() : event.key;
            state.keys[key] = false;
        });

        canvas.addEventListener('pointerdown', event => {
            ensureAudio();
            state.pointerActive = true;
            state.dropX = clampDropX(pointerToWorldX(event), state.nextAsset);
            canvas.setPointerCapture(event.pointerId);
            event.preventDefault();
        });
        canvas.addEventListener('pointermove', event => {
            if (event.pointerType === 'mouse' || state.pointerActive) {
                state.dropX = clampDropX(pointerToWorldX(event), state.nextAsset);
            }
        });
        canvas.addEventListener('pointerup', event => {
            if (state.pointerActive) {
                state.dropX = clampDropX(pointerToWorldX(event), state.nextAsset);
                if (state.pointerMode === 'tap' && !state.replaying) {
                    dropCurrentItem();
                }
            }
            state.pointerActive = false;
            event.preventDefault();
        });
        canvas.addEventListener('pointercancel', () => {
            state.pointerActive = false;
        });

        const bindHold = function (button, direction) {
            if (!button) {
                return;
            }
            button.addEventListener('pointerdown', () => {
                ensureAudio();
                state.holdDirection = direction;
            });
            ['pointerup', 'pointercancel', 'pointerleave'].forEach(name => {
                button.addEventListener(name, () => {
                    if (state.holdDirection === direction) {
                        state.holdDirection = 0;
                    }
                });
            });
        };

        bindHold(leftBtn, -1);
        bindHold(rightBtn, 1);

        if (dropBtn) {
            dropBtn.addEventListener('click', () => {
                if (!state.replaying) {
                    dropCurrentItem();
                }
            });
        }
        if (pauseBtn) {
            pauseBtn.addEventListener('click', () => setPaused(!state.paused));
        }
        if (restartBtn) {
            restartBtn.addEventListener('click', () => resetWorld('Restarted.'));
        }
        if (sendScoreBtn) {
            sendScoreBtn.addEventListener('click', sendScoreExchange);
        }
        if (retryBtn) {
            retryBtn.addEventListener('click', () => resetWorld('Ready.'));
        }
        if (themeEl) {
            themeEl.addEventListener('change', () => {
                state.startupPack = 'remember';
                loadThemeById(themeEl.value, 'Theme changed.').catch(reportLoadError);
            });
        }
        if (startupPackEl) {
            startupPackEl.addEventListener('change', () => {
                state.startupPack = startupPackEl.value === 'remember' ? 'remember' : 'random';
                saveSettings();
                setStatus(state.startupPack === 'random' ? 'Random startup pack enabled.' : 'Startup pack will be remembered.');
            });
        }
        if (modeEl) {
            modeEl.addEventListener('change', () => {
                state.mode = modeEl.value === 'daily' ? 'daily' : 'practice';
                saveSettings();
                resetWorld(state.mode === 'daily' ? 'Daily seed loaded.' : 'Practice seed loaded.');
            });
        }
        if (challengeEl) {
            challengeEl.addEventListener('change', () => {
                state.challenge = CHALLENGES[challengeEl.value] ? challengeEl.value : 'standard';
                saveSettings();
                resetWorld(challengeLabel() + ' challenge loaded.');
            });
        }
        if (qualityEl) {
            qualityEl.addEventListener('change', () => {
                state.colliderQuality = qualityEl.value;
                saveSettings();
                resetWorld('Collider quality set to ' + qualityEl.options[qualityEl.selectedIndex].text + '.');
            });
        }
        if (debugEl) {
            debugEl.addEventListener('change', () => {
                state.debugMode = debugEl.value;
                saveSettings();
            });
        }
        if (volumeEl) {
            volumeEl.addEventListener('input', () => {
                state.audio.volume = clamp(Number(volumeEl.value), 0, 1);
                saveSettings();
            });
        }
        if (pointerModeEl) {
            pointerModeEl.addEventListener('change', () => {
                state.pointerMode = pointerModeEl.value === 'aim' ? 'aim' : 'tap';
                saveSettings();
                setStatus(state.pointerMode === 'aim' ? 'Pointer aim mode.' : 'Pointer tap mode.');
            });
        }
        if (uiScaleEl) {
            uiScaleEl.addEventListener('input', () => {
                state.uiScale = clamp(Number(uiScaleEl.value), 0.9, 1.2);
                applyAccessibilityUi();
                saveSettings();
            });
        }
        if (soundEl) {
            soundEl.addEventListener('change', () => {
                state.audio.enabled = soundEl.checked;
                if (state.audio.enabled) {
                    ensureAudio();
                }
                saveSettings();
            });
        }
        if (motionEl) {
            motionEl.addEventListener('change', () => {
                state.reducedMotion = motionEl.checked;
                saveSettings();
            });
        }
        if (colorSafeEl) {
            colorSafeEl.addEventListener('change', () => {
                state.colorSafeWarnings = colorSafeEl.checked;
                applyAccessibilityUi();
                saveSettings();
            });
        }
        if (leaderboardScopeEl) {
            leaderboardScopeEl.addEventListener('change', renderLeaderboard);
        }
        if (clearLeaderboardBtn) {
            clearLeaderboardBtn.addEventListener('click', () => {
                if (leaderboardScopeEl && leaderboardScopeEl.value === 'all') {
                    state.leaderboards = {};
                } else {
                    delete state.leaderboards[currentLeaderboardScope()];
                }
                saveLeaderboards();
                setStatus('Local leaderboard cleared.');
            });
        }
        if (playReplayBtn) {
            playReplayBtn.addEventListener('click', () => {
                const replay = selectedReplay();
                if (replay) {
                    startReplay(replay);
                }
            });
        }
        if (exportReplayBtn) {
            exportReplayBtn.addEventListener('click', () => {
                const replay = selectedReplay();
                if (replay && replayJsonEl) {
                    replayJsonEl.value = JSON.stringify(replay, null, 2);
                    replayJsonEl.focus();
                    replayJsonEl.select();
                }
            });
        }
        if (importReplayBtn) {
            importReplayBtn.addEventListener('click', importReplayFromText);
        }
    }

    async function boot() {
        try {
            loadSettings();
            populateThemeSelect();
            bindControls();
            resizeCanvas();
            state.bestScore = loadBestScore();
            syncSettingsUi();
            renderReplaySelect();
            renderLeaderboard();
            updateHud(performance.now());
            await loadThemeById(state.theme.id, 'Ready.');
        } catch (error) {
            state.loading = false;
            setStatus(error && error.message ? error.message : 'Drop game failed to start.');
            render();
        }
    }

    boot();
})();
</script>
<?php endif; ?>

<style>
main.container {
    max-width: min(1120px, calc(100% - 24px));
}

.drop-game-page {
    --drop-game-accent: #277967;
    --drop-game-ui-scale: 1;
    display: grid;
    font-size: calc(1rem * var(--drop-game-ui-scale));
    gap: 18px;
}

.drop-game-heading {
    align-items: start;
    display: flex;
    gap: 16px;
    justify-content: space-between;
}

.drop-game-heading h1 {
    margin: 0;
}

.drop-game-kicker {
    font-weight: 700;
    letter-spacing: 0;
    margin: 0 0 4px;
    text-transform: uppercase;
}

.drop-game-shell {
    align-items: start;
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(300px, 460px) minmax(286px, 1fr);
}

.drop-game-stage {
    background: rgba(255, 255, 255, 0.48);
    border: 1px solid rgba(39, 55, 67, 0.16);
    border-radius: 8px;
    display: grid;
    gap: 10px;
    justify-items: center;
    overflow: hidden;
    padding: 10px;
}

#drop-game-canvas {
    aspect-ratio: 420 / 620;
    border-radius: 6px;
    display: block;
    inline-size: 100%;
    max-inline-size: 420px;
    touch-action: none;
}

.drop-game-cooldown,
.drop-game-danger-track {
    background: rgba(45, 58, 63, 0.14);
    border-radius: 999px;
    block-size: 8px;
    display: block;
    inline-size: 100%;
    overflow: hidden;
}

.drop-game-cooldown span,
.drop-game-danger-track span {
    block-size: 100%;
    display: block;
    transform: scaleX(0);
    transform-origin: left center;
    transition: transform 80ms linear;
}

.drop-game-cooldown span {
    background: var(--drop-game-accent);
}

.drop-game-danger-track span {
    background: #d84b3f;
}

.drop-game-panel {
    display: grid;
    gap: 14px;
}

.drop-game-score-row {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.drop-game-score-row div,
.drop-game-next,
.drop-game-queue,
.drop-game-danger,
.drop-game-seed-row,
.drop-game-lab {
    background: rgba(255, 255, 255, 0.42);
    border: 1px solid rgba(39, 55, 67, 0.12);
    border-radius: 8px;
    display: grid;
    gap: 7px;
    padding: 10px;
}

.drop-game-score-row span,
.drop-game-next > span,
.drop-game-queue > span,
.drop-game-danger-label span,
.drop-game-settings span,
.drop-game-lab span {
    font-size: 0.84rem;
}

.drop-game-score-row strong {
    display: block;
    font-size: 1.55rem;
    line-height: 1.1;
}

.drop-game-next-preview {
    align-items: center;
    display: grid;
    gap: 10px;
    grid-template-columns: 54px 1fr;
}

.drop-game-next-preview img {
    block-size: 54px;
    inline-size: 54px;
    object-fit: contain;
}

.drop-game-next-preview strong,
.drop-game-queue-item span,
.drop-game-tier span {
    display: block;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.drop-game-chip {
    border: 1px solid currentColor;
    border-radius: 999px;
    display: inline-flex;
    font-size: 0.74rem;
    font-weight: 800;
    inline-size: fit-content;
    line-height: 1;
    padding: 4px 7px;
}

.drop-game-queue-list {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.drop-game-queue-item {
    align-items: center;
    background: rgba(255, 255, 255, 0.42);
    border: 1px solid rgba(39, 55, 67, 0.1);
    border-radius: 8px;
    display: grid;
    gap: 4px;
    justify-items: center;
    min-block-size: 62px;
    padding: 6px;
}

.drop-game-queue-item img {
    block-size: 36px;
    inline-size: 36px;
    object-fit: contain;
}

.drop-game-queue-item span {
    color: var(--muted);
    font-size: 0.74rem;
    font-weight: 700;
}

.drop-game-queue-item.is-rare {
    border-color: rgba(216, 150, 36, 0.68);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.46), 0 0 0 2px rgba(216, 150, 36, 0.12);
}

.drop-game-danger-label {
    align-items: center;
    display: flex;
    justify-content: space-between;
}

.drop-game-seed-row {
    align-items: center;
    display: flex;
    gap: 8px;
    justify-content: space-between;
}

.drop-game-controls {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.drop-game-controls .btn {
    min-block-size: 42px;
}

.drop-game-controls #drop-game-pause {
    grid-column: 1 / 2;
}

.drop-game-controls #drop-game-restart {
    grid-column: 2 / 4;
}

.drop-game-settings {
    align-items: end;
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.drop-game-settings label,
.drop-game-lab label {
    display: grid;
    gap: 4px;
    min-width: 0;
}

.drop-game-settings select,
.drop-game-settings input,
.drop-game-lab select,
.drop-game-lab input,
.drop-game-replay-tools textarea {
    margin: 0;
    min-width: 0;
    width: 100%;
}

.drop-game-toggle-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.drop-game-toggle {
    align-items: center;
    display: flex !important;
    gap: 7px;
    min-block-size: 40px;
}

.drop-game-toggle input {
    block-size: 18px;
    inline-size: 18px;
    margin: 0;
}

.drop-game-lab {
    display: grid;
    gap: 10px;
}

.drop-game-lab summary {
    cursor: pointer;
    font-weight: 800;
}

.drop-game-replay-tools {
    display: grid;
    gap: 9px;
}

.drop-game-replay-tools {
    grid-template-columns: minmax(140px, 1fr) repeat(3, minmax(72px, 0.5fr));
}

.drop-game-replay-tools textarea {
    background: rgba(255, 255, 255, 0.58);
    border: 1px solid rgba(39, 55, 67, 0.16);
    border-radius: 8px;
    color: inherit;
    font: 0.78rem/1.35 Consolas, "Courier New", monospace;
    grid-column: 1 / -1;
    padding: 8px;
    resize: vertical;
}

.drop-game-leaderboard-head {
    display: grid;
    gap: 8px;
    grid-template-columns: 1fr minmax(80px, 0.35fr);
}

.drop-game-leaderboard {
    display: grid;
    gap: 6px;
    list-style: decimal;
    margin: 0;
    padding-left: 22px;
}

.drop-game-leaderboard li {
    align-items: baseline;
    border-bottom: 1px solid rgba(39, 55, 67, 0.1);
    display: grid;
    gap: 6px;
    grid-template-columns: minmax(58px, 0.35fr) 1fr;
    padding: 5px 0;
}

.drop-game-leaderboard li::marker {
    color: var(--drop-game-accent);
    font-weight: 800;
}

.drop-game-leaderboard span {
    color: var(--muted);
    font-size: 0.78rem;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.drop-game-empty-row {
    color: var(--muted);
    display: block !important;
    font-size: 0.86rem;
}

.drop-game-tier-list {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(auto-fit, minmax(132px, 1fr));
}

.drop-game-tier {
    align-items: center;
    background: rgba(255, 255, 255, 0.36);
    border: 1px solid rgba(39, 55, 67, 0.11);
    border-radius: 8px;
    display: grid;
    gap: 8px;
    grid-template-columns: 34px 1fr;
    min-width: 0;
    padding: 7px;
}

.drop-game-tier img {
    block-size: 34px;
    inline-size: 34px;
    object-fit: contain;
}

.drop-game-tier div {
    min-width: 0;
}

.drop-game-tier em {
    display: block;
    font-size: 0.72rem;
    font-style: normal;
    font-weight: 800;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.drop-game-status {
    margin: 0;
}

.drop-game-empty {
    max-inline-size: 680px;
}

.drop-game-gameover[hidden] {
    display: none;
}

.drop-game-gameover {
    align-items: center;
    background: rgba(14, 23, 38, 0.42);
    border-radius: 8px;
    display: flex;
    inset: 0;
    justify-content: center;
    padding: 18px;
    position: fixed;
    z-index: 1200;
}

.drop-game-gameover-card {
    display: grid;
    gap: 16px;
    inline-size: min(360px, calc(100vw - 32px));
    text-align: center;
}

.drop-game-gameover-card h2 {
    margin: 0;
}

.drop-game-gameover-scores {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.drop-game-gameover-scores div {
    background: rgba(255, 255, 255, 0.42);
    border: 1px solid rgba(39, 55, 67, 0.12);
    border-radius: 8px;
    display: grid;
    gap: 5px;
    padding: 10px;
}

.drop-game-gameover-scores strong {
    font-size: 1.45rem;
}

.drop-game-gameover-actions {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.drop-game-exchange-status {
    margin: 0;
    min-block-size: 1.2em;
}

html[data-theme="dark"] .drop-game-stage,
html[data-theme="dark"] .drop-game-score-row div,
html[data-theme="dark"] .drop-game-next,
html[data-theme="dark"] .drop-game-queue,
html[data-theme="dark"] .drop-game-danger,
html[data-theme="dark"] .drop-game-seed-row,
html[data-theme="dark"] .drop-game-lab,
html[data-theme="dark"] .drop-game-tier,
html[data-theme="dark"] .drop-game-queue-item,
html[data-theme="dark"] .drop-game-gameover-scores div {
    background: rgba(16, 28, 42, 0.52);
    border-color: rgba(255, 255, 255, 0.1);
}

html[data-theme="dark"] .drop-game-replay-tools textarea {
    background: rgba(9, 16, 24, 0.72);
    border-color: rgba(255, 255, 255, 0.12);
}

@media (max-width: 860px) {
    main.container {
        margin-top: 132px;
    }

    .drop-game-heading {
        align-items: stretch;
        flex-direction: column;
    }

    .drop-game-shell {
        grid-template-columns: 1fr;
    }

    .drop-game-stage {
        justify-self: center;
        max-inline-size: 440px;
        inline-size: 100%;
    }
}

@media (max-width: 540px) {
    main.container {
        max-width: calc(100% - 16px);
        padding-inline: 10px;
    }

    .drop-game-score-row,
    .drop-game-settings,
    .drop-game-replay-tools,
    .drop-game-leaderboard-head,
    .drop-game-toggle-grid {
        grid-template-columns: 1fr;
    }

    .drop-game-gameover-actions {
        grid-template-columns: 1fr;
    }

    .drop-game-toggle {
        justify-content: space-between;
    }
}

@media (pointer: coarse) {
    .drop-game-controls .btn {
        min-block-size: 48px;
    }

    #drop-game-canvas {
        max-block-size: calc(100vh - 250px);
    }
}
</style>
