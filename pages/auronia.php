<?php
require_login();

$auroniaMapAreas = [
    [
        'id' => 'nornheim',
        'name' => 'Nornheim',
        'description' => 'Snowfields, longhouses, and Aesir war banners.',
        'action' => 'Explore',
        'href' => '?pg=nornheim',
        'color' => '#67e8f9',
        'coords' => '201,8,232,144,262,147,269,168,302,167,302,188,381,237,397,263,445,229,457,157,465,75,468,11',
    ],
    [
        'id' => 'bretonreach',
        'name' => 'Bretonreach',
        'description' => 'Storm-battered coasts, fish and chips, and old keeps.',
        'action' => 'Explore',
        'href' => '?pg=bretonreach',
        'color' => '#34d399',
        'coords' => '391,276,374,235,340,219,301,194,296,168,267,172,254,151,224,144,138,197,34,230,5,336,3,369,13,380,53,328,70,323,95,335,130,343,156,343,171,360,182,372,207,380,238,385,263,398,333,387,379,325',
    ],
    [
        'id' => 'rheinland',
        'name' => 'Rheinland',
        'description' => 'Beer halls, river roads, and market towns.',
        'action' => 'Explore',
        'href' => '?pg=rheinland',
        'color' => '#f59e0b',
        'coords' => '312,403,252,401,184,377,161,356,122,349,64,327,19,380,4,450,9,522,30,521,32,505,48,489,64,492,125,497,139,483,176,492,229,508,245,516,260,538,287,541,395,488,416,440,378,397',
    ],
    [
        'id' => 'aa',
        'name' => 'Aegia Aeterna',
        'description' => 'Marble streets, pizza ovens, and loud piazzas.',
        'action' => 'Explore',
        'href' => '?pg=aa',
        'color' => '#f472b6',
        'coords' => '285,562,248,533,224,508,145,492,120,503,80,498,42,504,11,536,20,576,25,688,78,706,156,715,219,688',
    ],
];

$cityCards = [
    ['name' => 'Aegia Aeterna', 'description' => 'Pizza, pasta, lotsa Ey\'s and O\'s.', 'href' => '?pg=aa'],
    ['name' => 'Bretonreach', 'description' => 'Green hills, fog, bad weather, fish and chips.', 'href' => '?pg=bretonreach'],
    ['name' => 'Nornheim', 'description' => 'Snow, Aesir and war.', 'href' => '?pg=nornheim'],
    ['name' => 'Rheinland', 'description' => 'Beer, Bratwurst and Oktoberfest.', 'href' => '?pg=rheinland'],
    ['name' => 'Rodanian Tsardom', 'description' => 'Rural towns, castles and friendly people.', 'href' => '?pg=rt'],
];
?>

<div class="country-map-page">
    <div class="country-map-header">
        <h1>Auronia Map</h1>
        <p class="subtitle">Pick a highlighted region to open its local map.</p>
        <p class="hint">Hover to preview, click to select, then jump in.</p>
    </div>

    <div class="country-map-wrap auronia-map-wrap">
        <a class="country-map-back-link" href="?pg=map">← Back to world map</a>

        <div class="country-map-media" id="auroniaMapMedia" style="aspect-ratio: 477 / 720;">
            <img src="images/harmontide-auronia-smol.png" alt="Auronia map" class="country-map-image" draggable="false">

            <svg viewBox="0 0 477 720" preserveAspectRatio="none" class="country-map-overlay">
                <?php foreach ($auroniaMapAreas as $index => $area):
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

            <div class="country-map-tooltip" id="auroniaMapTooltip"></div>

            <div class="country-map-panel" id="auroniaMapPanel">
                <h3 id="auroniaPanelTitle"></h3>
                <p id="auroniaPanelDescription"></p>
                <a href="#" class="btn" id="auroniaPanelLink"></a>
            </div>
        </div>
    </div>

    <div class="country-map-legend">
        <?php foreach ($auroniaMapAreas as $index => $area): ?>
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
.auronia-map-wrap { max-width: 720px; margin: 0 auto; }
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
    const mapMedia = document.getElementById('auroniaMapMedia');
    if (!mapMedia) return;

    const tooltip = document.getElementById('auroniaMapTooltip');
    const panel = document.getElementById('auroniaMapPanel');
    const panelTitle = document.getElementById('auroniaPanelTitle');
    const panelDescription = document.getElementById('auroniaPanelDescription');
    const panelLink = document.getElementById('auroniaPanelLink');
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
