<?php
require_login();

$orienthemMapAreas = [
    [
        'id' => 'esl',
        'name' => 'Eretz-Shalem League',
        'description' => 'Covenants, citadels, and caravan crossroads.',
        'action' => 'Explore',
        'href' => '?pg=esl',
        'color' => '#22d3ee',
        'coords' => '606,259,567,409,488,489,489,520,437,522,409,552,398,575,376,597,338,588,324,617,268,617,232,622,206,643,174,648,163,667,136,695,116,675,128,597,128,566,95,538,96,503,77,459,30,475,4,431,2,359,54,349,32,281,42,233,80,185,111,143,158,143,217,127,261,109,314,94,353,93,372,35,415,23,436,23,454,1,490,9',
    ],
    [
        'id' => 'hammurabia',
        'name' => 'Hammurabia',
        'description' => 'Bronze-law cities and ancient ziggurat courts.',
        'action' => 'Explore',
        'href' => '?pg=hammurabia',
        'color' => '#f59e0b',
        'coords' => '610,258,571,413,502,483,479,528,439,525,414,556,386,597,344,594,327,624,255,625,203,647,152,685,125,698,110,662,120,598,111,572,84,496,69,469,4,488,16,766,207,1000,248,977,258,926,275,912,298,894,329,911,350,873,384,870,411,854,420,827,468,829,494,815,528,825,554,807,580,797,618,787,650,755,705,727,732,735,770,729,803,728,837,725,866,717,892,715,926,700,962,707,993,712,1016,714,1008,653,950,549,988,492,989,455,808,369,758,320,734,221',
    ],
    [
        'id' => 'cc',
        'name' => 'Crescent Caliphate',
        'description' => 'Moonlit markets, caravans, and desert trade roads.',
        'action' => 'Explore',
        'href' => '?pg=cc',
        'color' => '#a78bfa',
        'coords' => '1050,733,927,705,716,742,702,733,651,763,618,791,566,807,532,825,497,820,466,836,426,829,412,860,382,875,355,877,332,912,296,897,262,929,262,1016,315,1041,374,1081,401,1143,448,1223,482,1251,531,1257,592,1217,632,1228,636,1251,671,1255,696,1229,733,1209,775,1186,828,1181,862,1181,950,1150,1098,1191,1119,1135,1118,1065,1118,787',
    ],
];

$cityCards = [
    ['name' => 'Crescent Caliphate', 'description' => 'Sheikhs and oil.', 'href' => '?pg=cc'],
    ['name' => 'Hammurabia', 'description' => 'Ziggurats, scribes, and old empires.', 'href' => '?pg=hammurabia'],
    ['name' => 'Eretz-Shalem League', 'description' => 'A state wrapped in controversy.', 'href' => '?pg=esl'],
];
?>

<div class="country-map-page">
    <div class="country-map-header">
        <h1>Orienthem</h1>
        <p class="subtitle">Hover and click to preview each realm before traveling.</p>
        <p class="hint">Double-click a highlighted area to jump straight in.</p>
    </div>

    <div class="country-map-wrap continent-map-wrap">
        <a class="country-map-back-link" href="?pg=map">← Back to world map</a>

        <div class="country-map-media" id="continentMapMedia" style="aspect-ratio: 1123 / 1262;">
            <img src="images/harmontide-orienthem.png" alt="Orienthem map" class="country-map-image" draggable="false">

            <svg viewBox="0 0 1123 1262" preserveAspectRatio="none" class="country-map-overlay">
                <?php foreach ($orienthemMapAreas as $index => $area):
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
                        data-id="<?= htmlspecialchars((string)$index) ?>"
                        data-name="<?= htmlspecialchars($area['name']) ?>"
                        data-description="<?= htmlspecialchars($area['description']) ?>"
                        data-action="<?= htmlspecialchars($area['action']) ?>"
                        data-href="<?= htmlspecialchars($area['href']) ?>"
                    ></polygon>
                <?php endforeach; ?>
            </svg>

            <div class="country-map-tooltip" id="continentMapTooltip"></div>

            <div class="country-map-panel" id="continentMapPanel">
                <h3 id="continentPanelTitle"></h3>
                <p id="continentPanelDescription"></p>
                <a href="#" class="btn" id="continentPanelLink"></a>
            </div>
        </div>
    </div>

    <div class="country-map-legend">
        <?php foreach ($orienthemMapAreas as $index => $area): ?>
            <button
                type="button"
                class="country-map-legend-item"
                data-area-id="<?= htmlspecialchars((string)$index) ?>"
                style="--legend-color: <?= htmlspecialchars($area['color']) ?>;"
            >
                <span></span><?= htmlspecialchars($area['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<div class="continent-cards">
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
.continent-map-wrap { max-width: 860px; margin: 0 auto; }
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
.continent-cards { margin-top: 1rem; }
</style>

<script>
(function () {
    const mapMedia = document.getElementById('continentMapMedia');
    if (!mapMedia) return;

    const tooltip = document.getElementById('continentMapTooltip');
    const panel = document.getElementById('continentMapPanel');
    const panelTitle = document.getElementById('continentPanelTitle');
    const panelDescription = document.getElementById('continentPanelDescription');
    const panelLink = document.getElementById('continentPanelLink');
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
