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
    ?>
<div class="country-map-page">
    <div class="country-map-header">
        <h1><?= htmlspecialchars($config['title']) ?></h1>
        <p class="subtitle"><?= htmlspecialchars($config['subtitle'] ?? '') ?></p>
        <p class="hint">Click highlighted areas to explore.</p>
    </div>

    <div class="country-map-wrap">
        <?php if (!empty($config['back_href']) && !empty($config['back_label'])): ?>
            <a class="country-map-back" href="<?= htmlspecialchars($config['back_href']) ?>">← <?= htmlspecialchars($config['back_label']) ?></a>
        <?php endif; ?>

        <div class="country-map-media" style="aspect-ratio: <?= $width ?> / <?= $height ?>;">
            <img src="<?= htmlspecialchars($config['image']) ?>" alt="<?= htmlspecialchars($config['title']) ?> map" class="country-map-image">

            <div class="country-map-particles">
                <?php foreach ($particles as $particle): ?>
                    <div class="country-particle" style="left: <?= $particle['left'] ?>; top: <?= $particle['top'] ?>; animation-delay: <?= $particle['delay'] ?>; animation-duration: <?= $particle['duration'] ?>;"></div>
                <?php endforeach; ?>
            </div>

            <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="country-map-overlay">
                <?php foreach ($areas as $area): ?>
                    <polygon
                        class="country-map-area"
                        points="<?= htmlspecialchars($area['points']) ?>"
                        style="--area-color: <?= htmlspecialchars($area['color']) ?>;"
                        data-name="<?= htmlspecialchars($area['name']) ?>"
                        data-description="<?= htmlspecialchars($area['description']) ?>"
                        data-action="<?= htmlspecialchars($area['action']) ?>"
                        data-href="<?= htmlspecialchars($area['href']) ?>"
                    ></polygon>
                <?php endforeach; ?>
            </svg>
            <div class="country-map-tooltip" id="countryMapTooltip"></div>
        </div>

        <div class="country-map-panel" id="countryMapPanel">
            <h3 id="countryMapPanelTitle"></h3>
            <p id="countryMapPanelDescription"></p>
            <a href="#" class="btn" id="countryMapPanelLink"></a>
        </div>
    </div>

    <div class="country-map-legend">
        <?php foreach ($areas as $area): ?>
            <button
                type="button"
                class="country-map-legend-item"
                data-name="<?= htmlspecialchars($area['name']) ?>"
                style="--legend-color: <?= htmlspecialchars($area['color']) ?>;"
            >
                <span></span><?= htmlspecialchars($area['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="card glass" style="margin-top: 1rem;">
        <h3><?= htmlspecialchars($config['city_heading'] ?? $config['title']) ?></h3>
        <p class="muted"><?= htmlspecialchars($config['lore']) ?></p>
    </div>
</div>

<style>
.country-map-page { margin: 0 auto; max-width: 1380px; }
.country-map-header { text-align: center; margin: 1rem 0 1.25rem; }
.country-map-header h1 { margin-bottom: 0.35rem; }
.country-map-header .subtitle { opacity: .8; margin: 0; }
.country-map-header .hint { font-size: .9rem; opacity: .7; margin-top: .5rem; }
.country-map-wrap { position: relative; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 45px rgba(0,0,0,.35); background: #060913; }
.country-map-back { position: absolute; top: .85rem; left: .85rem; z-index: 5; text-decoration: none; padding: .35rem .6rem; border-radius: 8px; border: 1px solid rgba(255,255,255,.2); background: rgba(6, 10, 24, .8); color: #fff; font-size: .9rem; }
.country-map-back:hover { background: rgba(18, 24, 44, .9); }
.country-map-media { position: relative; width: 100%; }
.country-map-image { width: 100%; height: 100%; object-fit: cover; display:block; }
.country-map-overlay { position: absolute; inset: 0; width: 100%; height: 100%; }
.country-map-area { fill: transparent; stroke: transparent; stroke-width: 0.5; cursor: pointer; transition: .2s ease; }
.country-map-area:hover, .country-map-area.active { fill: var(--area-color); fill-opacity: 0.28; stroke: var(--area-color); stroke-opacity: .95; }
.country-map-tooltip { position: absolute; pointer-events: none; padding: .4rem .55rem; border-radius: 8px; background: rgba(5,10,25,.94); border: 1px solid rgba(255,255,255,.12); color: #fff; font-size: .86rem; opacity: 0; transform: translate(-50%, -120%); transition: .15s; z-index: 3; }
.country-map-tooltip.visible { opacity: 1; }
.country-map-panel { position: absolute; left: 1rem; right: 1rem; bottom: 1rem; background: rgba(4,8,20,.88); border: 1px solid rgba(255,255,255,.16); border-radius: 12px; padding: .95rem 1rem; display:none; }
.country-map-panel.visible { display:block; }
.country-map-panel h3 { margin: 0 0 .2rem; }
.country-map-panel p { margin: 0 0 .65rem; font-size: .94rem; }
.country-map-legend { margin-top: .75rem; display: flex; flex-wrap: wrap; gap: .5rem; }
.country-map-legend-item { border: 1px solid rgba(255,255,255,.18); background: rgba(15,20,36,.8); color: #fff; border-radius: 999px; padding: .4rem .8rem; cursor: pointer; }
.country-map-legend-item span { width: .65rem; height: .65rem; display:inline-block; border-radius: 50%; background: var(--legend-color); margin-right: .45rem; }
.country-map-particles { position: absolute; inset: 0; pointer-events: none; }
.country-particle { position: absolute; width: 4px; height: 4px; border-radius: 50%; background: rgba(255,255,255,.45); animation: countryMapFloat linear infinite; }
@keyframes countryMapFloat { from { transform: translateY(0); opacity: 0; } 25% { opacity: .9; } to { transform: translateY(-18px); opacity: 0; } }
</style>

<script>
(function () {
    const tooltip = document.getElementById('countryMapTooltip');
    const panel = document.getElementById('countryMapPanel');
    const panelTitle = document.getElementById('countryMapPanelTitle');
    const panelDescription = document.getElementById('countryMapPanelDescription');
    const panelLink = document.getElementById('countryMapPanelLink');

    document.querySelectorAll('.country-map-area').forEach((area) => {
        area.addEventListener('mousemove', (event) => {
            tooltip.textContent = area.dataset.name;
            tooltip.style.left = `${event.offsetX}px`;
            tooltip.style.top = `${event.offsetY}px`;
            tooltip.classList.add('visible');
        });

        area.addEventListener('mouseleave', () => tooltip.classList.remove('visible'));

        area.addEventListener('click', () => {
            document.querySelectorAll('.country-map-area').forEach((node) => node.classList.remove('active'));
            area.classList.add('active');
            panelTitle.textContent = area.dataset.name;
            panelDescription.textContent = area.dataset.description;
            panelLink.href = area.dataset.href;
            panelLink.textContent = area.dataset.action;
            panel.classList.add('visible');
        });
    });
})();
</script>
<?php
}
