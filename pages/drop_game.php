<?php
require_login();

$dropGameRoot = realpath(__DIR__ . '/..');
$dropGameItemDir = $dropGameRoot ? realpath($dropGameRoot . '/images/items') : false;
$dropGameItemsByName = [];

if ($dropGameRoot && $dropGameItemDir) {
    $normalizedRoot = str_replace('\\', '/', $dropGameRoot);
    $normalizedItemDir = str_replace('\\', '/', $dropGameItemDir);
    if (strpos($normalizedItemDir, $normalizedRoot . '/images/items') === 0 && is_dir($dropGameItemDir)) {
        foreach (new DirectoryIterator($dropGameItemDir) as $dropGameFile) {
            if (!$dropGameFile->isFile()) {
                continue;
            }
            if (strtolower($dropGameFile->getExtension()) !== 'webp') {
                continue;
            }

            $dropGameFileName = $dropGameFile->getFilename();
            $dropGameItemsByName[$dropGameFileName] = [
                'file' => $dropGameFileName,
                'label' => preg_replace('/\.webp$/i', '', $dropGameFileName),
                'src' => 'images/items/' . rawurlencode($dropGameFileName),
            ];
        }
    }
}

$dropGamePreferredItems = [
    [
        'file' => 'Iron Sword.webp',
        'role' => 'long',
        'label' => 'Iron Sword',
        'maxSize' => 142,
        'friction' => 0.78,
        'restitution' => 0.03,
        'density' => 0.0014,
    ],
    [
        'file' => 'Transparent Pizza.webp',
        'role' => 'oval',
        'label' => 'Transparent Pizza',
        'maxSize' => 104,
        'friction' => 0.72,
        'restitution' => 0.05,
        'density' => 0.0017,
    ],
    [
        'file' => 'Energy Drink.webp',
        'role' => 'tall',
        'label' => 'Energy Drink',
        'maxSize' => 116,
        'friction' => 0.66,
        'restitution' => 0.04,
        'density' => 0.0015,
    ],
    [
        'file' => 'Crystal Shard.webp',
        'role' => 'angular',
        'label' => 'Crystal Shard',
        'maxSize' => 96,
        'friction' => 0.62,
        'restitution' => 0.07,
        'density' => 0.0018,
    ],
    [
        'file' => 'Deluxe Strawberry.webp',
        'role' => 'round',
        'label' => 'Deluxe Strawberry',
        'maxSize' => 86,
        'friction' => 0.74,
        'restitution' => 0.06,
        'density' => 0.0016,
    ],
];

$dropGameAssets = [];
foreach ($dropGamePreferredItems as $dropGamePreferred) {
    $dropGameFile = $dropGamePreferred['file'];
    if (!isset($dropGameItemsByName[$dropGameFile])) {
        continue;
    }

    $dropGameAssets[] = array_merge($dropGameItemsByName[$dropGameFile], $dropGamePreferred);
}

foreach ($dropGameItemsByName as $dropGameFile => $dropGameAsset) {
    if (count($dropGameAssets) >= 5) {
        break;
    }
    $alreadyAdded = false;
    foreach ($dropGameAssets as $existingAsset) {
        if ($existingAsset['file'] === $dropGameFile) {
            $alreadyAdded = true;
            break;
        }
    }
    if ($alreadyAdded) {
        continue;
    }

    $dropGameAssets[] = array_merge($dropGameAsset, [
        'role' => 'fallback',
        'maxSize' => 92,
        'friction' => 0.7,
        'restitution' => 0.05,
        'density' => 0.0016,
    ]);
}
?>

<section class="drop-game-page">
    <div class="drop-game-heading">
        <div>
            <p class="muted drop-game-kicker">Proof of concept</p>
            <h1>Drop Game Collider Lab</h1>
        </div>
        <a class="btn ghost" href="?pg=games">Back to Games</a>
    </div>

    <?php if (!$dropGameAssets): ?>
        <div class="card glass drop-game-empty" role="alert">
            <h2>No item graphics found</h2>
            <p class="muted">Add .webp files to images/items and reload this page.</p>
        </div>
    <?php else: ?>
        <div class="drop-game-shell">
            <div class="drop-game-stage glass">
                <canvas id="drop-game-canvas" width="420" height="620" aria-label="Drop game proof of concept"></canvas>
            </div>

            <aside class="drop-game-panel card glass">
                <div class="drop-game-meter">
                    <span class="muted">Loaded</span>
                    <strong id="drop-game-loaded">0/<?php echo count($dropGameAssets); ?></strong>
                </div>
                <div class="drop-game-meter">
                    <span class="muted">Next</span>
                    <strong id="drop-game-next">...</strong>
                </div>

                <div class="drop-game-controls" aria-label="Drop controls">
                    <button class="btn" type="button" id="drop-game-left" title="Move left">&lt;</button>
                    <button class="btn" type="button" id="drop-game-drop" title="Drop item">Drop</button>
                    <button class="btn" type="button" id="drop-game-right" title="Move right">&gt;</button>
                    <button class="btn ghost" type="button" id="drop-game-restart" title="Restart">Restart</button>
                </div>

                <label class="drop-game-toggle">
                    <input type="checkbox" id="drop-game-debug" checked>
                    <span>Collider overlay</span>
                </label>

                <div class="drop-game-items" id="drop-game-items" aria-label="Loaded test items"></div>
                <p class="muted drop-game-status" id="drop-game-status">Preparing silhouette colliders...</p>
            </aside>
        </div>
    <?php endif; ?>
</section>

<?php if ($dropGameAssets): ?>
<script>
window.dropGameAssetConfig = <?=
    json_encode($dropGameAssets, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/poly-decomp@0.3.0/build/decomp.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/matter-js@0.19.0/build/matter.min.js"></script>
<script>
(function () {
    'use strict';

    const config = Array.isArray(window.dropGameAssetConfig) ? window.dropGameAssetConfig : [];
    const canvas = document.getElementById('drop-game-canvas');
    const loadedEl = document.getElementById('drop-game-loaded');
    const nextEl = document.getElementById('drop-game-next');
    const statusEl = document.getElementById('drop-game-status');
    const debugToggle = document.getElementById('drop-game-debug');
    const itemListEl = document.getElementById('drop-game-items');
    const leftBtn = document.getElementById('drop-game-left');
    const rightBtn = document.getElementById('drop-game-right');
    const dropBtn = document.getElementById('drop-game-drop');
    const restartBtn = document.getElementById('drop-game-restart');

    if (!canvas) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const WORLD = {
        width: 420,
        height: 620,
        wall: 34,
        spawnY: 84,
        minDropX: 46,
        maxDropX: 374
    };
    const COLLIDER = {
        alphaThreshold: 36,
        simplifyStart: 2.6,
        simplifyMax: 9.5,
        maxVertices: 58,
        minVertices: 7
    };
    const state = {
        engine: null,
        runner: null,
        walls: [],
        assets: [],
        nextAsset: null,
        dropX: WORLD.width / 2,
        canDrop: false,
        debug: true,
        keys: Object.create(null),
        raf: 0,
        holdDirection: 0,
        activePointer: false
    };

    function setStatus(message) {
        if (statusEl) {
            statusEl.textContent = message;
        }
    }

    function reportFatal(message) {
        setStatus(message);
        if (typeof window.reportAppError === 'function') {
            window.reportAppError(message);
        }
    }

    if (!window.Matter) {
        reportFatal('Matter.js could not be loaded.');
        return;
    }

    const Matter = window.Matter;
    const Engine = Matter.Engine;
    const Runner = Matter.Runner;
    const Bodies = Matter.Bodies;
    const Body = Matter.Body;
    const Composite = Matter.Composite;
    const Events = Matter.Events;
    const Common = Matter.Common;

    if (window.decomp && typeof Common.setDecomp === 'function') {
        Common.setDecomp(window.decomp);
    }

    function loadImage(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = () => reject(new Error('Could not load ' + src));
            img.src = src;
        });
    }

    function makeCanvas(width, height) {
        const temp = document.createElement('canvas');
        temp.width = Math.max(1, Math.ceil(width));
        temp.height = Math.max(1, Math.ceil(height));
        return temp;
    }

    function alphaBoundsForImage(img) {
        const scan = makeCanvas(img.naturalWidth || img.width, img.naturalHeight || img.height);
        const scanCtx = scan.getContext('2d', { willReadFrequently: true });
        scanCtx.drawImage(img, 0, 0);

        const width = scan.width;
        const height = scan.height;
        const data = scanCtx.getImageData(0, 0, width, height).data;
        let minX = width;
        let minY = height;
        let maxX = -1;
        let maxY = -1;

        for (let y = 0; y < height; y += 1) {
            for (let x = 0; x < width; x += 1) {
                if (data[((y * width + x) * 4) + 3] > COLLIDER.alphaThreshold) {
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

        const padding = 2;
        minX = Math.max(0, minX - padding);
        minY = Math.max(0, minY - padding);
        maxX = Math.min(width - 1, maxX + padding);
        maxY = Math.min(height - 1, maxY + padding);

        return {
            x: minX,
            y: minY,
            width: maxX - minX + 1,
            height: maxY - minY + 1
        };
    }

    function buildSolidMask(imageData, width, height) {
        const source = imageData.data;
        const mask = new Uint8Array(width * height);

        for (let i = 0; i < width * height; i += 1) {
            mask[i] = source[(i * 4) + 3] > COLLIDER.alphaThreshold ? 1 : 0;
        }

        return mask;
    }

    function keepLargestComponent(mask, width, height) {
        const visited = new Uint8Array(mask.length);
        let best = [];
        const queue = [];
        const dirs = [[1, 0], [-1, 0], [0, 1], [0, -1]];

        for (let i = 0; i < mask.length; i += 1) {
            if (!mask[i] || visited[i]) {
                continue;
            }

            const component = [];
            queue.length = 0;
            queue.push(i);
            visited[i] = 1;

            for (let q = 0; q < queue.length; q += 1) {
                const index = queue[q];
                component.push(index);
                const x = index % width;
                const y = Math.floor(index / width);

                for (let d = 0; d < dirs.length; d += 1) {
                    const nx = x + dirs[d][0];
                    const ny = y + dirs[d][1];
                    if (nx < 0 || ny < 0 || nx >= width || ny >= height) {
                        continue;
                    }
                    const nIndex = ny * width + nx;
                    if (mask[nIndex] && !visited[nIndex]) {
                        visited[nIndex] = 1;
                        queue.push(nIndex);
                    }
                }
            }

            if (component.length > best.length) {
                best = component;
            }
        }

        const largest = new Uint8Array(mask.length);
        for (let i = 0; i < best.length; i += 1) {
            largest[best[i]] = 1;
        }
        return largest;
    }

    function hasTransparentNeighbor(mask, width, height, x, y) {
        for (let yy = -1; yy <= 1; yy += 1) {
            for (let xx = -1; xx <= 1; xx += 1) {
                if (xx === 0 && yy === 0) {
                    continue;
                }
                const nx = x + xx;
                const ny = y + yy;
                if (nx < 0 || ny < 0 || nx >= width || ny >= height || !mask[ny * width + nx]) {
                    return true;
                }
            }
        }
        return false;
    }

    function traceBoundary(mask, width, height) {
        let start = null;
        for (let y = 0; y < height && !start; y += 1) {
            for (let x = 0; x < width; x += 1) {
                if (mask[y * width + x] && hasTransparentNeighbor(mask, width, height, x, y)) {
                    start = { x: x, y: y };
                    break;
                }
            }
        }

        if (!start) {
            return [];
        }

        const dirs = [
            [1, 0],
            [1, 1],
            [0, 1],
            [-1, 1],
            [-1, 0],
            [-1, -1],
            [0, -1],
            [1, -1]
        ];
        const dirIndex = function (from, to) {
            const dx = to.x - from.x;
            const dy = to.y - from.y;
            for (let i = 0; i < dirs.length; i += 1) {
                if (dirs[i][0] === dx && dirs[i][1] === dy) {
                    return i;
                }
            }
            return 4;
        };
        const solid = function (x, y) {
            return x >= 0 && y >= 0 && x < width && y < height && mask[y * width + x];
        };

        let current = { x: start.x, y: start.y };
        let backtrack = { x: start.x - 1, y: start.y };
        const startBacktrack = { x: backtrack.x, y: backtrack.y };
        const points = [];
        const maxSteps = width * height * 8;

        for (let guard = 0; guard < maxSteps; guard += 1) {
            points.push({ x: current.x + 0.5, y: current.y + 0.5 });
            const startDir = (dirIndex(current, backtrack) + 1) % 8;
            let next = null;
            let nextBacktrack = null;

            for (let offset = 0; offset < 8; offset += 1) {
                const dir = (startDir + offset) % 8;
                const nx = current.x + dirs[dir][0];
                const ny = current.y + dirs[dir][1];

                if (solid(nx, ny)) {
                    const previousDir = (dir + 7) % 8;
                    next = { x: nx, y: ny };
                    nextBacktrack = {
                        x: current.x + dirs[previousDir][0],
                        y: current.y + dirs[previousDir][1]
                    };
                    break;
                }
            }

            if (!next) {
                break;
            }

            current = next;
            backtrack = nextBacktrack;

            if (
                points.length > 2 &&
                current.x === start.x &&
                current.y === start.y &&
                (
                    points.length > 8 ||
                    (
                        backtrack.x === startBacktrack.x &&
                        backtrack.y === startBacktrack.y
                    )
                )
            ) {
                break;
            }
        }

        return removeNearDuplicates(points, 0.75);
    }

    function removeNearDuplicates(points, minDistance) {
        if (points.length < 2) {
            return points.slice();
        }

        const filtered = [];
        let previous = null;
        const minDistanceSq = minDistance * minDistance;

        for (let i = 0; i < points.length; i += 1) {
            const point = points[i];
            if (!previous || distanceSq(previous, point) >= minDistanceSq) {
                filtered.push(point);
                previous = point;
            }
        }

        if (filtered.length > 2 && distanceSq(filtered[0], filtered[filtered.length - 1]) < minDistanceSq) {
            filtered.pop();
        }

        return filtered;
    }

    function distanceSq(a, b) {
        const dx = a.x - b.x;
        const dy = a.y - b.y;
        return dx * dx + dy * dy;
    }

    function pointLineDistance(point, start, end) {
        const dx = end.x - start.x;
        const dy = end.y - start.y;
        if (dx === 0 && dy === 0) {
            return Math.sqrt(distanceSq(point, start));
        }

        const t = Math.max(0, Math.min(1, ((point.x - start.x) * dx + (point.y - start.y) * dy) / (dx * dx + dy * dy)));
        const projected = {
            x: start.x + t * dx,
            y: start.y + t * dy
        };
        return Math.sqrt(distanceSq(point, projected));
    }

    function simplifyOpen(points, tolerance) {
        if (points.length <= 2) {
            return points.slice();
        }

        let maxDistance = -1;
        let index = -1;
        const first = points[0];
        const last = points[points.length - 1];

        for (let i = 1; i < points.length - 1; i += 1) {
            const distance = pointLineDistance(points[i], first, last);
            if (distance > maxDistance) {
                maxDistance = distance;
                index = i;
            }
        }

        if (maxDistance > tolerance && index > 0) {
            const left = simplifyOpen(points.slice(0, index + 1), tolerance);
            const right = simplifyOpen(points.slice(index), tolerance);
            return left.slice(0, -1).concat(right);
        }

        return [first, last];
    }

    function simplifyClosed(points, tolerance) {
        if (points.length <= COLLIDER.minVertices) {
            return points.slice();
        }

        let split = 1;
        let farthest = 0;
        for (let i = 1; i < points.length; i += 1) {
            const distance = distanceSq(points[0], points[i]);
            if (distance > farthest) {
                farthest = distance;
                split = i;
            }
        }

        const chainA = points.slice(0, split + 1);
        const chainB = points.slice(split).concat([points[0]]);
        const simplified = simplifyOpen(chainA, tolerance)
            .slice(0, -1)
            .concat(simplifyOpen(chainB, tolerance).slice(0, -1));

        return removeNearDuplicates(simplified, 1.2);
    }

    function polygonArea(points) {
        let area = 0;
        for (let i = 0; i < points.length; i += 1) {
            const a = points[i];
            const b = points[(i + 1) % points.length];
            area += (a.x * b.y) - (b.x * a.y);
        }
        return area / 2;
    }

    function polygonCentroid(points) {
        let areaTerm = 0;
        let cx = 0;
        let cy = 0;

        for (let i = 0; i < points.length; i += 1) {
            const a = points[i];
            const b = points[(i + 1) % points.length];
            const cross = (a.x * b.y) - (b.x * a.y);
            areaTerm += cross;
            cx += (a.x + b.x) * cross;
            cy += (a.y + b.y) * cross;
        }

        if (Math.abs(areaTerm) < 0.001) {
            return { x: 0, y: 0 };
        }

        return {
            x: cx / (3 * areaTerm),
            y: cy / (3 * areaTerm)
        };
    }

    function convexHull(points) {
        const sorted = points.slice().sort((a, b) => a.x === b.x ? a.y - b.y : a.x - b.x);
        if (sorted.length <= 3) {
            return sorted;
        }

        const cross = function (o, a, b) {
            return (a.x - o.x) * (b.y - o.y) - (a.y - o.y) * (b.x - o.x);
        };
        const lower = [];
        const upper = [];

        for (let i = 0; i < sorted.length; i += 1) {
            while (lower.length >= 2 && cross(lower[lower.length - 2], lower[lower.length - 1], sorted[i]) <= 0) {
                lower.pop();
            }
            lower.push(sorted[i]);
        }

        for (let i = sorted.length - 1; i >= 0; i -= 1) {
            while (upper.length >= 2 && cross(upper[upper.length - 2], upper[upper.length - 1], sorted[i]) <= 0) {
                upper.pop();
            }
            upper.push(sorted[i]);
        }

        return lower.slice(0, -1).concat(upper.slice(0, -1));
    }

    function fallbackRectangle(width, height) {
        const inset = 2;
        return [
            { x: -width / 2 + inset, y: -height / 2 + inset },
            { x: width / 2 - inset, y: -height / 2 + inset },
            { x: width / 2 - inset, y: height / 2 - inset },
            { x: -width / 2 + inset, y: height / 2 - inset }
        ];
    }

    function createDisplayCanvas(img, crop, width, height) {
        const display = makeCanvas(width, height);
        const displayCtx = display.getContext('2d', { willReadFrequently: true });
        displayCtx.clearRect(0, 0, display.width, display.height);
        displayCtx.drawImage(
            img,
            crop.x,
            crop.y,
            crop.width,
            crop.height,
            0,
            0,
            display.width,
            display.height
        );
        return display;
    }

    function tuneVertices(points, width, height) {
        let simplified = [];
        for (let tolerance = COLLIDER.simplifyStart; tolerance <= COLLIDER.simplifyMax; tolerance += 0.7) {
            simplified = simplifyClosed(points, tolerance);
            if (simplified.length <= COLLIDER.maxVertices) {
                break;
            }
        }

        if (simplified.length < 3) {
            return fallbackRectangle(width, height);
        }

        let centered = simplified.map(point => ({
            x: point.x - (width / 2),
            y: point.y - (height / 2)
        }));

        if (Math.abs(polygonArea(centered)) < 35) {
            centered = fallbackRectangle(width, height);
        }

        if (polygonArea(centered) < 0) {
            centered.reverse();
        }

        return centered;
    }

    async function prepareAsset(assetConfig) {
        const img = await loadImage(assetConfig.src);
        const crop = alphaBoundsForImage(img);
        const maxSize = Number(assetConfig.maxSize) || 96;
        const scale = maxSize / Math.max(crop.width, crop.height);
        const width = Math.max(28, Math.round(crop.width * scale));
        const height = Math.max(28, Math.round(crop.height * scale));
        const display = createDisplayCanvas(img, crop, width, height);
        const displayCtx = display.getContext('2d', { willReadFrequently: true });
        const mask = buildSolidMask(displayCtx.getImageData(0, 0, display.width, display.height), display.width, display.height);
        const largest = keepLargestComponent(mask, display.width, display.height);
        const traced = traceBoundary(largest, display.width, display.height);
        let vertices = traced.length >= 8 ? tuneVertices(traced, display.width, display.height) : fallbackRectangle(display.width, display.height);

        if (vertices.length > COLLIDER.maxVertices) {
            vertices = convexHull(vertices);
        }

        const centroid = polygonCentroid(vertices);
        const area = Math.abs(polygonArea(vertices));

        return Object.assign({}, assetConfig, {
            image: img,
            crop: crop,
            width: display.width,
            height: display.height,
            vertices: vertices,
            centroid: centroid,
            area: area,
            tracedPointCount: traced.length,
            generatedVertexCount: vertices.length,
            friction: Number(assetConfig.friction) || 0.7,
            restitution: Number(assetConfig.restitution) || 0.05,
            density: Number(assetConfig.density) || 0.0016
        });
    }

    function randomAsset() {
        return state.assets[Math.floor(Math.random() * state.assets.length)];
    }

    function clampDropX(value) {
        return Math.max(WORLD.minDropX, Math.min(WORLD.maxDropX, value));
    }

    function makeWalls() {
        const wallOptions = {
            isStatic: true,
            label: 'drop-game-wall',
            render: { visible: false },
            friction: 0.8,
            restitution: 0.02
        };

        state.walls = [
            Bodies.rectangle(WORLD.width / 2, WORLD.height + (WORLD.wall / 2), WORLD.width + WORLD.wall * 2, WORLD.wall, wallOptions),
            Bodies.rectangle(-WORLD.wall / 2, WORLD.height / 2, WORLD.wall, WORLD.height + WORLD.wall * 2, wallOptions),
            Bodies.rectangle(WORLD.width + (WORLD.wall / 2), WORLD.height / 2, WORLD.wall, WORLD.height + WORLD.wall * 2, wallOptions)
        ];
        Composite.add(state.engine.world, state.walls);
    }

    function resetWorld() {
        if (state.runner) {
            Runner.stop(state.runner);
        }
        if (state.raf) {
            cancelAnimationFrame(state.raf);
        }

        state.engine = Engine.create();
        state.engine.gravity.y = 1;
        state.engine.gravity.scale = 0.0012;
        state.runner = Runner.create();
        state.dropX = WORLD.width / 2;
        state.nextAsset = randomAsset();
        state.canDrop = true;
        makeWalls();

        Events.on(state.engine, 'beforeUpdate', function () {
            const direction = (state.keys.ArrowRight || state.keys.d ? 1 : 0) - (state.keys.ArrowLeft || state.keys.a ? 1 : 0);
            const heldDirection = state.holdDirection || direction;
            if (heldDirection) {
                state.dropX = clampDropX(state.dropX + heldDirection * 5.6);
            }
        });

        Runner.run(state.runner, state.engine);
        updateNextLabel();
        render();
    }

    function makeItemBody(asset, x, y) {
        const options = {
            label: 'drop-game-item:' + asset.label,
            friction: asset.friction,
            frictionStatic: Math.min(1, asset.friction + 0.12),
            frictionAir: 0.002,
            restitution: asset.restitution,
            density: asset.density,
            slop: 0.03
        };

        let body = null;
        try {
            body = Bodies.fromVertices(x, y, [asset.vertices], options, true, 0.01, 10, 0.01);
        } catch (error) {
            body = null;
        }

        if (!body) {
            try {
                body = Bodies.fromVertices(x, y, [convexHull(asset.vertices)], options, true);
            } catch (error) {
                body = Bodies.rectangle(x, y, asset.width * 0.82, asset.height * 0.82, options);
            }
        }

        Body.setAngle(body, (Math.random() - 0.5) * 0.24);
        Body.setAngularVelocity(body, (Math.random() - 0.5) * 0.035);
        body.plugin = body.plugin || {};
        body.plugin.dropGame = { asset: asset };
        Composite.add(state.engine.world, body);
        return body;
    }

    function dropCurrentItem() {
        if (!state.canDrop || !state.nextAsset) {
            return;
        }

        const asset = state.nextAsset;
        state.canDrop = false;
        makeItemBody(asset, state.dropX, WORLD.spawnY);
        state.nextAsset = randomAsset();
        updateNextLabel();

        window.setTimeout(function () {
            state.canDrop = true;
        }, 260);
    }

    function updateNextLabel() {
        if (nextEl && state.nextAsset) {
            nextEl.textContent = state.nextAsset.label;
        }
    }

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        const dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
        canvas.width = Math.round(rect.width * dpr);
        canvas.height = Math.round(rect.height * dpr);
    }

    function drawBoard() {
        const grad = ctx.createLinearGradient(0, 0, 0, WORLD.height);
        grad.addColorStop(0, '#f7fbf8');
        grad.addColorStop(0.55, '#eef8f1');
        grad.addColorStop(1, '#ffe8d6');

        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, WORLD.width, WORLD.height);

        ctx.fillStyle = 'rgba(47, 60, 54, 0.9)';
        ctx.fillRect(0, WORLD.height - 16, WORLD.width, 16);
        ctx.fillRect(0, 0, 12, WORLD.height);
        ctx.fillRect(WORLD.width - 12, 0, 12, WORLD.height);

        ctx.strokeStyle = 'rgba(232, 92, 75, 0.42)';
        ctx.lineWidth = 2;
        ctx.setLineDash([8, 9]);
        ctx.beginPath();
        ctx.moveTo(18, 116);
        ctx.lineTo(WORLD.width - 18, 116);
        ctx.stroke();
        ctx.setLineDash([]);
    }

    function rotatePoint(point, angle) {
        const cos = Math.cos(angle);
        const sin = Math.sin(angle);
        return {
            x: point.x * cos - point.y * sin,
            y: point.x * sin + point.y * cos
        };
    }

    function drawAsset(asset, position, angle, alpha) {
        const offset = rotatePoint({ x: -asset.centroid.x, y: -asset.centroid.y }, angle);
        ctx.save();
        ctx.globalAlpha = alpha == null ? 1 : alpha;
        ctx.translate(position.x + offset.x, position.y + offset.y);
        ctx.rotate(angle);
        ctx.drawImage(
            asset.image,
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

    function drawHeldPreview() {
        if (!state.nextAsset || !state.canDrop) {
            return;
        }

        const position = { x: state.dropX, y: WORLD.spawnY };
        ctx.save();
        ctx.strokeStyle = 'rgba(56, 74, 67, 0.34)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(state.dropX, 20);
        ctx.lineTo(state.dropX, WORLD.height - 18);
        ctx.stroke();
        ctx.restore();

        drawAsset(state.nextAsset, position, 0, 0.52);

        if (state.debug) {
            drawLocalVertices(state.nextAsset, position, 0, 'rgba(45, 145, 120, 0.78)', true);
        }
    }

    function drawLocalVertices(asset, position, angle, color, dashed) {
        if (!asset.vertices.length) {
            return;
        }

        ctx.save();
        ctx.strokeStyle = color;
        ctx.lineWidth = 2;
        if (dashed) {
            ctx.setLineDash([5, 5]);
        }
        ctx.beginPath();
        for (let i = 0; i < asset.vertices.length; i += 1) {
            const local = {
                x: asset.vertices[i].x - asset.centroid.x,
                y: asset.vertices[i].y - asset.centroid.y
            };
            const rotated = rotatePoint(local, angle);
            const x = position.x + rotated.x;
            const y = position.y + rotated.y;
            if (i === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        }
        ctx.closePath();
        ctx.stroke();
        ctx.restore();
    }

    function drawBodyCollider(body) {
        const parts = body.parts.length > 1 ? body.parts.slice(1) : [body];
        ctx.save();
        ctx.strokeStyle = 'rgba(21, 132, 108, 0.92)';
        ctx.lineWidth = 2;
        ctx.fillStyle = 'rgba(21, 132, 108, 0.07)';

        for (let i = 0; i < parts.length; i += 1) {
            const vertices = parts[i].vertices;
            if (!vertices.length) {
                continue;
            }
            ctx.beginPath();
            ctx.moveTo(vertices[0].x, vertices[0].y);
            for (let j = 1; j < vertices.length; j += 1) {
                ctx.lineTo(vertices[j].x, vertices[j].y);
            }
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
        }

        ctx.restore();
    }

    function drawBodies() {
        const bodies = Composite.allBodies(state.engine.world);
        for (let i = 0; i < bodies.length; i += 1) {
            const body = bodies[i];
            if (body.isStatic || !body.plugin || !body.plugin.dropGame) {
                continue;
            }
            drawAsset(body.plugin.dropGame.asset, body.position, body.angle, 1);
        }

        if (!state.debug) {
            return;
        }

        for (let i = 0; i < bodies.length; i += 1) {
            const body = bodies[i];
            if (body.isStatic || !body.plugin || !body.plugin.dropGame) {
                continue;
            }
            drawBodyCollider(body);
        }
    }

    function render() {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        ctx.setTransform(scaleX * (rect.width / WORLD.width), 0, 0, scaleY * (rect.height / WORLD.height), 0, 0);
        ctx.clearRect(0, 0, WORLD.width, WORLD.height);
        drawBoard();
        drawHeldPreview();
        drawBodies();
        state.raf = requestAnimationFrame(render);
    }

    function updateItemList() {
        if (!itemListEl) {
            return;
        }

        itemListEl.innerHTML = '';
        state.assets.forEach(asset => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'drop-game-item';
            item.title = asset.label + ' - ' + asset.generatedVertexCount + ' collider points';
            const img = document.createElement('img');
            const label = document.createElement('span');
            const detail = document.createElement('small');
            img.alt = '';
            img.src = asset.src;
            label.textContent = asset.label;
            detail.textContent = asset.generatedVertexCount + ' pts';
            item.appendChild(img);
            item.appendChild(label);
            item.appendChild(detail);
            item.addEventListener('click', () => {
                state.nextAsset = asset;
                updateNextLabel();
            });
            itemListEl.appendChild(item);
        });
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
                dropCurrentItem();
                event.preventDefault();
            }
        });
        window.addEventListener('keyup', event => {
            const key = event.key.length === 1 ? event.key.toLowerCase() : event.key;
            state.keys[key] = false;
        });

        const pointerToWorldX = function (event) {
            const rect = canvas.getBoundingClientRect();
            return clampDropX(((event.clientX - rect.left) / rect.width) * WORLD.width);
        };

        canvas.addEventListener('pointermove', event => {
            state.dropX = pointerToWorldX(event);
        });
        canvas.addEventListener('pointerdown', event => {
            state.activePointer = true;
            state.dropX = pointerToWorldX(event);
            canvas.setPointerCapture(event.pointerId);
        });
        canvas.addEventListener('pointerup', event => {
            if (state.activePointer) {
                state.dropX = pointerToWorldX(event);
                dropCurrentItem();
            }
            state.activePointer = false;
        });

        if (debugToggle) {
            debugToggle.addEventListener('change', () => {
                state.debug = debugToggle.checked;
            });
        }
        if (leftBtn) {
            leftBtn.addEventListener('pointerdown', () => {
                state.holdDirection = -1;
            });
            leftBtn.addEventListener('pointerup', () => {
                state.holdDirection = 0;
            });
            leftBtn.addEventListener('pointerleave', () => {
                state.holdDirection = 0;
            });
        }
        if (rightBtn) {
            rightBtn.addEventListener('pointerdown', () => {
                state.holdDirection = 1;
            });
            rightBtn.addEventListener('pointerup', () => {
                state.holdDirection = 0;
            });
            rightBtn.addEventListener('pointerleave', () => {
                state.holdDirection = 0;
            });
        }
        if (dropBtn) {
            dropBtn.addEventListener('click', dropCurrentItem);
        }
        if (restartBtn) {
            restartBtn.addEventListener('click', resetWorld);
        }
    }

    async function boot() {
        if (!config.length) {
            reportFatal('No drop game assets were provided.');
            return;
        }

        try {
            resizeCanvas();
            bindControls();

            for (let i = 0; i < config.length; i += 1) {
                const asset = await prepareAsset(config[i]);
                state.assets.push(asset);
                if (loadedEl) {
                    loadedEl.textContent = state.assets.length + '/' + config.length;
                }
            }

            updateItemList();
            setStatus('Silhouette colliders ready. ' + (window.decomp ? 'Compound shapes enabled.' : 'Using convex fallback.'));
            resetWorld();
        } catch (error) {
            reportFatal(error && error.message ? error.message : 'Drop game failed to start.');
        }
    }

    boot();
})();
</script>
<?php endif; ?>

<style>
.drop-game-page {
    display: grid;
    gap: 18px;
}

.drop-game-heading {
    align-items: start;
    display: flex;
    gap: 16px;
    justify-content: space-between;
}

.drop-game-heading h1 {
    margin-bottom: 0;
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
    grid-template-columns: minmax(280px, 440px) minmax(240px, 1fr);
}

.drop-game-stage {
    border: 1px solid rgba(48, 67, 58, 0.16);
    border-radius: 8px;
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

.drop-game-panel {
    display: grid;
    gap: 14px;
}

.drop-game-meter {
    align-items: center;
    display: flex;
    justify-content: space-between;
}

.drop-game-meter strong {
    font-size: 1.02rem;
    text-align: right;
}

.drop-game-controls {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.drop-game-controls #drop-game-restart {
    grid-column: 1 / -1;
}

.drop-game-controls .btn {
    min-height: 42px;
}

.drop-game-toggle {
    align-items: center;
    display: flex;
    gap: 8px;
    font-weight: 700;
}

.drop-game-toggle input {
    inline-size: 18px;
    block-size: 18px;
}

.drop-game-items {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(auto-fit, minmax(112px, 1fr));
}

.drop-game-item {
    align-items: center;
    background: rgba(255, 255, 255, 0.68);
    border: 1px solid rgba(48, 67, 58, 0.14);
    border-radius: 8px;
    color: inherit;
    cursor: pointer;
    display: grid;
    gap: 4px;
    grid-template-columns: 34px 1fr;
    min-height: 58px;
    padding: 7px;
    text-align: left;
}

.drop-game-item:hover,
.drop-game-item:focus {
    border-color: rgba(20, 130, 104, 0.58);
    outline: none;
}

.drop-game-item img {
    block-size: 34px;
    inline-size: 34px;
    object-fit: contain;
    grid-row: span 2;
}

.drop-game-item span,
.drop-game-item small {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.drop-game-item span {
    font-weight: 700;
}

.drop-game-item small {
    color: rgba(48, 67, 58, 0.72);
}

.drop-game-status {
    margin: 0;
}

.drop-game-empty {
    max-inline-size: 680px;
}

@media (max-width: 760px) {
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
</style>
