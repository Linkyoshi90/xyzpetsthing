<?php
require_login();

$gulfbeltMapAreas = [
    [
        'id' => 'xochimex',
        'name' => 'Xochimex',
        'description' => 'Canals, neon markets, and shadowy streets.',
        'action' => 'Explore',
        'href' => '?pg=xochimex',
        'color' => '#fb7185',
        'coords' => '424,7,354,29,234,400,178,704,219,785,303,791,355,795,414,816,457,813,561,779,628,757,715,698,734,755,789,797,849,771,619,185,594,27',
    ],
    [
        'id' => 'ie',
        'name' => 'Itzam Empire',
        'description' => 'Dense rainforests and old jungle temples.',
        'action' => 'Explore',
        'href' => '?pg=ie',
        'color' => '#22d3ee',
        'coords' => '319,795,217,788,179,722,95,774,23,980,22,1202,92,1199,207,1187,254,1180,303,1128,313,1111,352,1094,414,1030,460,1001,489,1001,507,991,547,985,582,954,597,946,705,884,721,905,768,930,826,844,782,810,754,781,723,767,710,710,627,769,450,828',
    ],
    [
        'id' => 'esd',
        'name' => 'Eagle Serpent Dominion',
        'description' => 'Sunstone cities and feathered war banners.',
        'action' => 'Explore',
        'href' => '?pg=esd',
        'color' => '#fbbf24',
        'coords' => '255,1192,335,1105,419,1033,461,1003,505,995,548,993,610,945,702,894,713,909,755,930,786,945,835,854,875,861,916,1041,908,1142,944,1230,913,1323,773,1376,565,1441,399,1350',
    ],
];
?>

<div class="country-map-page">
    <div class="country-map-header">
        <h1>Gulfbelt Map</h1>
        <p class="subtitle">Pick a highlighted region to open its local map.</p>
        <p class="hint">Hover to preview, click to select, then jump in.</p>
    </div>

    <div class="country-map-wrap gulfbelt-map-wrap">
        <a class="country-map-back-link" href="?pg=map">← Back to world map</a>

        <div class="country-map-media" id="gulfbeltMapMedia" style="aspect-ratio: 954 / 1464;">
            <img src="images/maps/harmontide-gulfbelt.webp" alt="Gulfbelt map" class="country-map-image" draggable="false">

            <svg viewBox="0 0 954 1464" preserveAspectRatio="none" class="country-map-overlay">
                <?php foreach ($gulfbeltMapAreas as $index => $area):
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

            <div class="country-map-tooltip" id="gulfbeltMapTooltip"></div>

            <div class="country-map-panel" id="gulfbeltMapPanel">
                <h3 id="gulfbeltPanelTitle"></h3>
                <p id="gulfbeltPanelDescription"></p>
                <a href="#" class="btn" id="gulfbeltPanelLink"></a>
            </div>
        </div>
    </div>

    <div class="country-map-legend">
        <?php foreach ($gulfbeltMapAreas as $index => $area): ?>
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

<div class="auronia-cards">
    <div class="card glass">
        <h3>Eagle Serpent Dominion</h3>
        <p class="muted">Home of Quetzalcoatl.</p>
        <a class="btn" href="?pg=esd">Explore</a>
    </div>

    <div class="card glass">
        <h3>Xochimex</h3>
        <p class="muted">Drugs and Violence. Weeeee!</p>
        <a class="btn" href="?pg=xochimex">Explore</a>
    </div>

    <div class="card glass">
        <h3>Itzam Empire</h3>
        <p class="muted">Rainforests... Lotsa Rainforests.</p>
        <a class="btn" href="?pg=ie">Explore</a>
    </div>

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
.gulfbelt-map-wrap { max-width: 720px; margin: 0 auto; }
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
.auronia-cards { margin-top: 1rem; }
</style>

<script>
(function () {
    const mapMedia = document.getElementById('gulfbeltMapMedia');
    if (!mapMedia) return;

    const tooltip = document.getElementById('gulfbeltMapTooltip');
    const panel = document.getElementById('gulfbeltMapPanel');
    const panelTitle = document.getElementById('gulfbeltPanelTitle');
    const panelDescription = document.getElementById('gulfbeltPanelDescription');
    const panelLink = document.getElementById('gulfbeltPanelLink');
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
