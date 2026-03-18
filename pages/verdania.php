<?php
require_login();

$verdaniaMapAreas = [
    [
        'id' => 'gc',
        'name' => 'Gran Columbia',
        'description' => 'River cities, bright plazas, and modern jungle trade.',
        'action' => 'Explore',
        'href' => '?pg=gc',
        'color' => '#22d3ee',
        'coords' => '1167,256,1214,259,1258,282,1311,347,1373,403,1412,486,1463,518,1511,533,1552,526,1642,587,1715,699,1720,1055,1714,1164,1656,1210,921,1239,49,1170,6,696,110,515',
    ],
    [
        'id' => 'sie',
        'name' => 'Sapa Inti Empire',
        'description' => 'High terraces, sun temples, and mountain roads.',
        'action' => 'Explore',
        'href' => '?pg=sie',
        'color' => '#f59e0b',
        'coords' => '1341,5,1193,242,1266,277,1313,336,1378,402,1413,454,1428,494,1460,501,1499,523,1515,523,1546,505,1578,534,1615,568,1666,573,1797,402,1800,319,1793,216,1690,116,1629,56,1410,47',
    ],
];

$cityCards = [
    ['name' => 'Gran Columbia', 'description' => 'Cocaine.', 'href' => '?pg=gc'],
    ['name' => 'Sapa Inti Empire', 'description' => 'Land of the gaucho.', 'href' => '?pg=sie'],
];
?>

<div class="country-map-page">
    <div class="country-map-header">
        <h1>Verdania Map</h1>
        <p class="subtitle">Pick a highlighted region to open its local map.</p>
        <p class="hint">Hover to preview, click to select, then jump in.</p>
    </div>

    <div class="country-map-wrap verdania-map-wrap">
        <a class="country-map-back-link" href="?pg=map">← Back to world map</a>

        <div class="country-map-media" id="verdaniaMapMedia" style="aspect-ratio: 1810 / 1248;">
            <img src="images/maps/harmontide-verdania.webp" alt="Verdania map" class="country-map-image" draggable="false">

            <svg viewBox="0 0 1810 1248" preserveAspectRatio="none" class="country-map-overlay">
                <?php foreach ($verdaniaMapAreas as $index => $area):
                    $pairs = explode(',', $area['coords']);
                    $points = [];
                    for ($i = 0; $i < count($pairs); $i += 2) {
                        $points[] = $pairs[$i] . ',' . $pairs[$i + 1];
                    }
                ?>
                    <polygon
                        class="country-map-area"
                        points="<?= htmlspecialchars(implode(' ', $points)) ?>"
                        style="--area-color: <?= htmlspecialchars($area['color']) ?>;"
                        data-id="<?= htmlspecialchars((string) $index) ?>"
                        data-name="<?= htmlspecialchars($area['name']) ?>"
                        data-description="<?= htmlspecialchars($area['description']) ?>"
                        data-action="<?= htmlspecialchars($area['action']) ?>"
                        data-href="<?= htmlspecialchars($area['href']) ?>"
                    ></polygon>
                <?php endforeach; ?>
            </svg>

            <div class="country-map-tooltip" id="verdaniaMapTooltip"></div>

            <div class="country-map-panel" id="verdaniaMapPanel">
                <h3 id="verdaniaPanelTitle"></h3>
                <p id="verdaniaPanelDescription"></p>
                <a href="#" class="btn" id="verdaniaPanelLink"></a>
            </div>
        </div>
    </div>

    <div class="country-map-legend">
        <?php foreach ($verdaniaMapAreas as $index => $area): ?>
            <button
                type="button"
                class="country-map-legend-item"
                data-area-id="<?= htmlspecialchars((string) $index) ?>"
                style="--legend-color: <?= htmlspecialchars($area['color']) ?>;"
            >
                <span></span><?= htmlspecialchars($area['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<div class="verdania-cards">
    <?php foreach ($cityCards as $card): ?>
        <div class="card glass">
            <h3><?= htmlspecialchars($card['name']) ?></h3>
            <p class="muted"><?= htmlspecialchars($card['description']) ?></p>
            <a class="btn" href="<?= htmlspecialchars($card['href']) ?>">Explore</a>
        </div>
    <?php endforeach; ?>
    <div class="card glass">
        <h3>Back to world map</h3>
        <a class="btn" href="?pg=map">Explore</a>
    </div>
</div>

<style>
.country-map-page { margin: 0 auto 1rem; max-width: 1100px; }
.country-map-header { text-align: center; margin: 1rem 0 1.25rem; }
.country-map-header h1 { margin-bottom: .35rem; }
.country-map-header .subtitle,
.country-map-header .hint { margin: 0; opacity: .8; }
.country-map-header .hint { font-size: .9rem; margin-top: .4rem; opacity: .7; }
.country-map-wrap { position: relative; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 45px rgba(0,0,0,.35); background: #060913; }
.verdania-map-wrap { max-width: 860px; margin: 0 auto; }
.country-map-back-link { position: absolute; top: 14px; left: 14px; z-index: 15; padding: .35rem .65rem; border-radius: 999px; border: 1px solid rgba(255,255,255,.25); background: rgba(5,10,24,.9); color: #fff; text-decoration: none; }
.country-map-back-link:hover { background: rgba(21,28,48,.95); }
.country-map-media { position: relative; width: 100%; }
.country-map-image { width: 100%; height: auto; display: block; }
.country-map-overlay { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 5; }
.country-map-area { fill: color-mix(in srgb, var(--area-color) 15%, transparent); stroke: color-mix(in srgb, var(--area-color) 75%, #fff); stroke-width: .55; cursor: pointer; transition: fill .15s ease, filter .15s ease; }
.country-map-area:hover,
.country-map-area.active { fill: color-mix(in srgb, var(--area-color) 40%, transparent); filter: drop-shadow(0 0 9px color-mix(in srgb, var(--area-color) 70%, #fff)); }
.country-map-tooltip { position: absolute; pointer-events: none; padding: .4rem .55rem; border-radius: 8px; background: rgba(5,10,25,.94); border: 1px solid rgba(255,255,255,.12); color: #fff; font-size: .86rem; opacity: 0; transform: translate(-50%, -120%); transition: .15s; z-index: 12; }
.country-map-tooltip.visible { opacity: 1; }
.country-map-panel { position: absolute; left: 1rem; right: 1rem; bottom: 1rem; background: rgba(4,8,20,.88); border: 1px solid rgba(255,255,255,.16); border-radius: 12px; padding: .95rem 1rem; z-index: 11; display: none; }
.country-map-panel.visible { display: block; }
.country-map-panel h3 { margin: 0 0 .2rem; }
.country-map-panel p { margin: 0 0 .65rem; font-size: .94rem; }
.country-map-legend { margin: .75rem auto 0; display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center; max-width: 900px; }
.country-map-legend-item { border: 1px solid rgba(255,255,255,.18); background: rgba(15,20,36,.8); color: #fff; border-radius: 999px; padding: .4rem .8rem; cursor: pointer; }
.country-map-legend-item span { width: .65rem; height: .65rem; display: inline-block; border-radius: 50%; background: var(--legend-color); margin-right: .45rem; }
.country-map-legend-item.active { border-color: color-mix(in srgb, var(--legend-color) 70%, white); box-shadow: 0 0 0 1px color-mix(in srgb, var(--legend-color) 30%, transparent) inset; }
.verdania-cards { margin-top: 1rem; }
</style>

<script>
(function () {
    const mapMedia = document.getElementById('verdaniaMapMedia');
    if (!mapMedia) return;

    const tooltip = document.getElementById('verdaniaMapTooltip');
    const panel = document.getElementById('verdaniaMapPanel');
    const panelTitle = document.getElementById('verdaniaPanelTitle');
    const panelDescription = document.getElementById('verdaniaPanelDescription');
    const panelLink = document.getElementById('verdaniaPanelLink');
    const areas = document.querySelectorAll('.country-map-area');
    const legendItems = document.querySelectorAll('.country-map-legend-item');

    let activeAreaId = null;

    function updateLegend() {
        legendItems.forEach((item) => {
            item.classList.toggle('active', item.dataset.areaId === activeAreaId);
        });
    }

    function selectArea(area) {
        areas.forEach((node) => node.classList.remove('active'));
        area.classList.add('active');
        activeAreaId = area.dataset.id;

        panelTitle.textContent = area.dataset.name;
        panelDescription.textContent = area.dataset.description;
        panelLink.href = area.dataset.href;
        panelLink.textContent = area.dataset.action;
        panel.classList.add('visible');
        updateLegend();
    }

    function clearSelection() {
        areas.forEach((node) => node.classList.remove('active'));
        activeAreaId = null;
        panel.classList.remove('visible');
        updateLegend();
    }

    areas.forEach((area) => {
        area.addEventListener('mousemove', (event) => {
            tooltip.textContent = area.dataset.name;
            const rect = mapMedia.getBoundingClientRect();
            tooltip.style.left = `${event.clientX - rect.left}px`;
            tooltip.style.top = `${event.clientY - rect.top}px`;
            tooltip.classList.add('visible');
        });

        area.addEventListener('mouseleave', () => tooltip.classList.remove('visible'));

        area.addEventListener('click', (event) => {
            event.stopPropagation();
            if (activeAreaId === area.dataset.id) {
                clearSelection();
                return;
            }
            selectArea(area);
        });

        area.addEventListener('dblclick', () => {
            window.location.href = area.dataset.href;
        });
    });

    legendItems.forEach((item) => {
        item.addEventListener('click', () => {
            const area = Array.from(areas).find((node) => node.dataset.id === item.dataset.areaId);
            if (!area) return;
            if (activeAreaId === area.dataset.id) {
                clearSelection();
                return;
            }
            selectArea(area);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') clearSelection();
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.country-map-area') && !event.target.closest('.country-map-panel') && !event.target.closest('.country-map-legend-item')) {
            clearSelection();
        }
    });
})();
</script>
