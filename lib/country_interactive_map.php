<?php

/**
 * Shared renderer for country/town interactive pages.
 */
function country_map_to_percent_points(array $coords, int $width, int $height): string {
    $points = [];
    for ($i = 0; $i < count($coords); $i += 2) {
        $x = round(($coords[$i] / $width) * 100, 2);
        $y = round(($coords[$i + 1] / $height) * 100, 2);
        $points[] = "{$x},{$y}";
    }
    return implode(' ', $points);
}

function country_map_rect_points(int $x, int $y, int $w, int $h, int $width, int $height): string {
    return country_map_to_percent_points([$x, $y, $x + $w, $y, $x + $w, $y + $h, $x, $y + $h], $width, $height);
}

function country_map_particles(int $count = 20): array {
    $particles = [];
    for ($i = 0; $i < $count; $i++) {
        $particles[] = [
            'left' => rand(0, 100) . '%',
            'top' => rand(65, 98) . '%',
            'delay' => (rand(0, 100) / 10) . 's',
            'duration' => (8 + rand(0, 60) / 10) . 's',
        ];
    }
    return $particles;
}

function render_country_interactive_map(array $config): void {
    $width = (int)($config['width'] ?? 1531);
    $height = (int)($config['height'] ?? 811);
    $particles = country_map_particles((int)($config['particle_count'] ?? 16));
    $areas = $config['areas'] ?? [];
    $backLabel = $config['back_label'] ?? null;
    $backHref = $config['back_href'] ?? null;
    ?>
<div class="country-map-page">
    <div class="country-map-header">
        <h1><?= htmlspecialchars($config['title']) ?></h1>
        <p class="subtitle"><?= htmlspecialchars($config['subtitle'] ?? '') ?></p>
        <p class="hint">Click highlighted areas to explore.</p>
    </div>

    <div class="country-map-wrap">
        <?php if ($backLabel && $backHref): ?>
            <a class="country-map-back-link" href="<?= htmlspecialchars($backHref) ?>">← <?= htmlspecialchars($backLabel) ?></a>
        <?php endif; ?>

        <div class="country-map-scroll-container" id="countryMapScrollContainer">
            <div class="country-map-inner" id="countryMapInner">
                <div class="country-map-media" id="countryMapMedia" style="aspect-ratio: <?= $width ?> / <?= $height ?>;">
                    <img src="<?= htmlspecialchars($config['image']) ?>" alt="<?= htmlspecialchars($config['title']) ?> map" class="country-map-image" draggable="false">

                    <div class="country-map-particles">
                        <?php foreach ($particles as $particle): ?>
                            <div class="country-particle" style="left: <?= $particle['left'] ?>; top: <?= $particle['top'] ?>; animation-delay: <?= $particle['delay'] ?>; animation-duration: <?= $particle['duration'] ?>;"></div>
                        <?php endforeach; ?>
                    </div>

                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="country-map-overlay">
                        <?php foreach ($areas as $index => $area): ?>
                            <polygon
                                class="country-map-area"
                                points="<?= htmlspecialchars($area['points']) ?>"
                                style="--area-color: <?= htmlspecialchars($area['color']) ?>;"
                                data-id="<?= htmlspecialchars((string)$index) ?>"
                                data-name="<?= htmlspecialchars($area['name']) ?>"
                                data-description="<?= htmlspecialchars($area['description']) ?>"
                                data-action="<?= htmlspecialchars($area['action']) ?>"
                                data-href="<?= htmlspecialchars($area['href']) ?>"
                            ></polygon>
                        <?php endforeach; ?>
                    </svg>
                    <div class="country-map-tooltip" id="countryMapTooltip"></div>
                </div>
            </div>
        </div>

        <div class="country-map-zoom-controls">
            <button type="button" class="country-map-zoom-button" data-zoom="in" title="Zoom In" aria-label="Zoom In">+</button>
            <button type="button" class="country-map-zoom-button" data-zoom="reset" title="Reset Zoom" aria-label="Reset Zoom">⌂</button>
            <button type="button" class="country-map-zoom-button" data-zoom="out" title="Zoom Out" aria-label="Zoom Out">−</button>
        </div>

        <div class="country-map-zoom-level" id="countryMapZoomLevel">100%</div>

        <div class="country-map-panel" id="countryMapPanel">
            <h3 id="countryMapPanelTitle"></h3>
            <p id="countryMapPanelDescription"></p>
            <a href="#" class="btn" id="countryMapPanelLink"></a>
        </div>
    </div>

    <div class="country-map-legend">
        <?php foreach ($areas as $index => $area): ?>
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

    <?php if (!empty($config['lore_sections']) && is_array($config['lore_sections'])): ?>
        <div class="country-map-lore-section">
            <?php foreach ($config['lore_sections'] as $section): ?>
                <div class="card glass country-map-lore-card" style="margin-top: 1rem;">
                    <?php if (!empty($section['title'])): ?>
                        <h3><?= htmlspecialchars($section['title']) ?></h3>
                    <?php endif; ?>
                    <?php if (!empty($section['html'])): ?>
                        <div class="country-map-lore-rich">
                            <?= $section['html'] ?>
                        </div>
                    <?php elseif (!empty($section['text'])): ?>
                        <p class="muted"><?= htmlspecialchars($section['text']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card glass country-map-lore" style="margin-top: 1rem;">
            <h3><?= htmlspecialchars($config['city_heading'] ?? $config['title']) ?></h3>
            <?php if (!empty($config['lore_html'])): ?>
                <div class="country-map-lore-rich">
                    <?= $config['lore_html'] ?>
                </div>
            <?php else: ?>
                <p class="muted"><?= htmlspecialchars($config['lore']) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.country-map-page { margin: 0 auto; max-width: 1380px; }
.country-map-header { text-align: center; margin: 1rem 0 1.25rem; }
.country-map-header h1 { margin-bottom: 0.35rem; }
.country-map-header .subtitle { opacity: .8; margin: 0; }
.country-map-header .hint { font-size: .9rem; opacity: .7; margin-top: .5rem; }
.country-map-wrap { position: relative; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 45px rgba(0,0,0,.35); background: #060913; }
.country-map-back-link { position: absolute; top: 16px; left: 16px; z-index: 14; text-decoration: none; color: #fff; background: rgba(6,10,24,.82); border: 1px solid rgba(255,255,255,.22); border-radius: 8px; padding: 6px 10px; font-size: .9rem; }
.country-map-back-link:hover { background: rgba(20,28,48,.92); }
.country-map-scroll-container { overflow: auto; cursor: grab; }
.country-map-scroll-container.dragging { cursor: grabbing; }
.country-map-inner { transform-origin: 0 0; transition: transform .2s ease; will-change: transform; }
.country-map-media { position: relative; width: 100%; min-width: 100%; }
.country-map-image { width: 100%; height: 100%; object-fit: cover; display:block; }
.country-map-overlay { position: absolute; inset: 0; width: 100%; height: 100%; }
.country-map-area { fill: transparent; stroke: transparent; stroke-width: 0.5; cursor: pointer; transition: .2s ease; }
.country-map-area:hover, .country-map-area.active { fill: var(--area-color); fill-opacity: 0.28; stroke: var(--area-color); stroke-opacity: .95; }
.country-map-tooltip { position: absolute; pointer-events: none; padding: .4rem .55rem; border-radius: 8px; background: rgba(5,10,25,.94); border: 1px solid rgba(255,255,255,.12); color: #fff; font-size: .86rem; opacity: 0; transform: translate(-50%, -120%); transition: .15s; z-index: 3; }
.country-map-tooltip.visible { opacity: 1; }
.country-map-zoom-controls { position: absolute; right: 16px; top: 16px; z-index: 14; display: flex; flex-direction: column; gap: 8px; }
.country-map-zoom-button { width: 38px; height: 38px; border: 1px solid rgba(255,255,255,.24); border-radius: 10px; background: rgba(6,10,24,.82); color: #fff; font-size: 20px; cursor: pointer; }
.country-map-zoom-button:hover { background: rgba(20,28,48,.92); }
.country-map-zoom-level { position: absolute; right: 16px; top: 150px; z-index: 14; border-radius: 999px; background: rgba(6,10,24,.82); border: 1px solid rgba(255,255,255,.22); color: #fff; font-size: .85rem; padding: .25rem .65rem; min-width: 62px; text-align: center; }
.country-map-panel { position: absolute; left: 1rem; right: 1rem; bottom: 1rem; background: rgba(4,8,20,.88); border: 1px solid rgba(255,255,255,.16); border-radius: 12px; padding: .95rem 1rem; display:none; }
.country-map-panel.visible { display:block; }
.country-map-panel h3 { margin: 0 0 .2rem; }
.country-map-panel p { margin: 0 0 .65rem; font-size: .94rem; }
.country-map-legend { margin-top: .75rem; display: flex; flex-wrap: wrap; gap: .5rem; }
.country-map-legend-item { border: 1px solid rgba(255,255,255,.18); background: rgba(15,20,36,.8); color: #fff; border-radius: 999px; padding: .4rem .8rem; cursor: pointer; }
.country-map-legend-item span { width: .65rem; height: .65rem; display:inline-block; border-radius: 50%; background: var(--legend-color); margin-right: .45rem; }
.country-map-lore-section { margin-top: .25rem; }
.country-map-lore-card h3 { margin-bottom: .45rem; }
.country-map-lore-rich p { margin: .55rem 0; line-height: 1.55; }
.country-map-lore-rich ul { margin: .35rem 0 .9rem 1.1rem; }
.country-map-lore-rich li { margin: .25rem 0; }
.country-map-lore-rich h3 { margin-top: 1rem; margin-bottom: .4rem; }
.country-map-lore-rich hr { border: 0; border-top: 1px solid rgba(255,255,255,.18); margin: .95rem 0; }
.country-map-particles { position: absolute; inset: 0; pointer-events: none; }
.country-particle { position: absolute; width: 4px; height: 4px; border-radius: 50%; background: rgba(255,255,255,.45); animation: countryMapFloat linear infinite; }
@keyframes countryMapFloat { from { transform: translateY(0); opacity: 0; } 25% { opacity: .9; } to { transform: translateY(-18px); opacity: 0; } }
</style>

<script>
(function () {
    const mapScrollContainer = document.getElementById('countryMapScrollContainer');
    const mapInner = document.getElementById('countryMapInner');
    const mapMedia = document.getElementById('countryMapMedia');
    const zoomLevel = document.getElementById('countryMapZoomLevel');
    const tooltip = document.getElementById('countryMapTooltip');
    const panel = document.getElementById('countryMapPanel');
    const panelTitle = document.getElementById('countryMapPanelTitle');
    const panelDescription = document.getElementById('countryMapPanelDescription');
    const panelLink = document.getElementById('countryMapPanelLink');
    const areas = document.querySelectorAll('.country-map-area');
    const legendItems = document.querySelectorAll('.country-map-legend-item');

    let currentZoom = 1;
    let activeAreaId = null;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let scrollLeft = 0;
    let scrollTop = 0;
    let touchStartDistance = 0;

    function updateZoom() {
        mapInner.style.transform = `scale(${currentZoom})`;
        zoomLevel.textContent = `${Math.round(currentZoom * 100)}%`;
    }

    function zoomIn() {
        currentZoom = Math.min(currentZoom + 0.25, 2.5);
        updateZoom();
    }

    function zoomOut() {
        currentZoom = Math.max(currentZoom - 0.25, 0.5);
        updateZoom();
    }

    function resetZoom() {
        currentZoom = 1;
        mapScrollContainer.scrollLeft = 0;
        mapScrollContainer.scrollTop = 0;
        updateZoom();
    }

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

    document.querySelectorAll('.country-map-zoom-button').forEach((button) => {
        button.addEventListener('click', () => {
            const control = button.dataset.zoom;
            if (control === 'in') zoomIn();
            if (control === 'out') zoomOut();
            if (control === 'reset') resetZoom();
        });
    });

    mapScrollContainer.addEventListener('mousedown', (event) => {
        isDragging = true;
        mapScrollContainer.classList.add('dragging');
        dragStartX = event.pageX - mapScrollContainer.offsetLeft;
        dragStartY = event.pageY - mapScrollContainer.offsetTop;
        scrollLeft = mapScrollContainer.scrollLeft;
        scrollTop = mapScrollContainer.scrollTop;
    });

    mapScrollContainer.addEventListener('mouseleave', () => {
        isDragging = false;
        mapScrollContainer.classList.remove('dragging');
    });

    mapScrollContainer.addEventListener('mouseup', () => {
        isDragging = false;
        mapScrollContainer.classList.remove('dragging');
    });

    mapScrollContainer.addEventListener('mousemove', (event) => {
        if (!isDragging) return;
        event.preventDefault();
        const x = event.pageX - mapScrollContainer.offsetLeft;
        const y = event.pageY - mapScrollContainer.offsetTop;
        mapScrollContainer.scrollLeft = scrollLeft - (x - dragStartX);
        mapScrollContainer.scrollTop = scrollTop - (y - dragStartY);
    });

    mapScrollContainer.addEventListener('wheel', (event) => {
        if (!event.ctrlKey) return;
        event.preventDefault();
        if (event.deltaY < 0) zoomIn();
        if (event.deltaY > 0) zoomOut();
    }, { passive: false });

    mapScrollContainer.addEventListener('touchstart', (event) => {
        if (event.touches.length !== 2) return;
        touchStartDistance = Math.hypot(
            event.touches[0].clientX - event.touches[1].clientX,
            event.touches[0].clientY - event.touches[1].clientY
        );
    }, { passive: true });

    mapScrollContainer.addEventListener('touchmove', (event) => {
        if (event.touches.length !== 2 || touchStartDistance === 0) return;
        const currentDistance = Math.hypot(
            event.touches[0].clientX - event.touches[1].clientX,
            event.touches[0].clientY - event.touches[1].clientY
        );
        const scale = currentDistance / touchStartDistance;
        currentZoom = Math.min(Math.max(currentZoom * scale, 0.5), 2.5);
        touchStartDistance = currentDistance;
        updateZoom();
    }, { passive: true });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') clearSelection();
        if (event.key === '+' || event.key === '=') zoomIn();
        if (event.key === '-') zoomOut();
        if (event.key === '0') resetZoom();
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.country-map-area') && !event.target.closest('.country-map-panel') && !event.target.closest('.country-map-legend-item')) {
            clearSelection();
        }
    });

    updateZoom();
})();
</script>
<?php
}
