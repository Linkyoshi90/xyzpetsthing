<?php
/**
 * Interactive Game Map - Heliadora
 * A modern, responsive interactive map with hover effects and animations
 * All CSS and JS embedded in this single file
 * 
 * To use: Place this file in your PHP project and update the image path
 */

// If you need authentication, uncomment and adjust:
// require_login();
// require_once __DIR__.'/../lib/shops.php';

// Map areas configuration - coordinates converted to percentages for responsive scaling
// Original image dimensions (adjust these to match your actual image)
$ORIGINAL_WIDTH = 1531;
$ORIGINAL_HEIGHT = 811;

/**
 * Convert absolute coordinates to percentage-based points
 * @param array $coords Flat array of x,y coordinates [x1, y1, x2, y2, ...]
 * @param int $width Original image width
 * @param int $height Original image height
 * @return string Space-separated percentage points "x1,y1 x2,y2 ..."
 */
function toPercentPoints(array $coords, int $width, int $height): string {
    $points = [];
    for ($i = 0; $i < count($coords); $i += 2) {
        $x = round(($coords[$i] / $width) * 100, 2);
        $y = round(($coords[$i + 1] / $height) * 100, 2);
        $points[] = "{$x},{$y}";
    }
    return implode(' ', $points);
}

// Define all interactive areas with their properties
$mapAreas = [
    [
        'id' => 'paint_shack',
        'name' => 'Paint Shack',
        'description' => 'Here you can paint your creatures from this region. Local artisans have perfected techniques passed down through generations of Aegian craftsmen.',
        'action' => 'Explore',
        'href' => '?pg=aa_paint_shack',
        'points' => toPercentPoints([443, 453, 208, 555, 193, 714, 333, 811, 619, 668, 558, 627, 570, 510], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#8b5cf6' // Purple
    ],
    [
        'id' => 'aa-adventure',
        'name' => 'Abandoned Ruins',
        'description' => 'Explore the ancient ruins of Heliadora, where crumbling marble columns tell tales of a civilization that valued democracy and civic virtue above all.',
        'action' => 'Begin Adventure',
        'href' => '?pg=aa-adventure',
        'points' => toPercentPoints([1052, 2, 1051, 36, 1303, 120, 1316, 160, 1349, 151, 1405, 169, 1531, 199, 1531, 2], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#f59e0b' // Amber
    ],
    [
        'id' => 'aa-pizza',
        'name' => 'Pizzeria Sol Invicta',
        'description' => 'Brick-fired pies, sun-braised olives, and red sauce argued over by civic poets. The famous Nonna Slice with sun-basil and charred crust is not to be missed.',
        'action' => 'Enter',
        'href' => '?pg=aa-pizza',
        'points' => toPercentPoints([333, 337, 326, 209, 486, 148, 593, 183, 588, 299, 642, 326, 631, 369, 470, 434, 415, 413], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#ef4444' // Red
    ],
    [
        'id' => 'aa-wof',
        'name' => 'Wheel of Pizza Wheels',
        'description' => 'Spin for stacked pizza prizes! Only 50 coins to play. Winners have been known to leave with a month\'s supply of margherita.',
        'action' => 'Spin',
        'href' => '?pg=aa-wof',
        'points' => toPercentPoints([552, 99, 647, 135, 716, 59, 703, 6, 582, 15], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#22c55e' // Green
    ],
    [
        'id' => 'aa-library',
        'name' => 'Library Annex',
        'description' => 'A marble reading hall where scribes loan illuminated epics and civic treatises. Home to the famous Vow-Stones where oaths are sworn.',
        'action' => 'Browse',
        'href' => '?pg=aa-library',
        'points' => toPercentPoints([249, 367, 321, 326, 320, 206, 489, 140, 542, 160, 545, 25, 448, 2, 404, 10, 119, 102, 122, 239, 86, 285], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#3b82f6' // Blue
    ]
];

// Generate particles for ambient animation
function generateParticles(int $count = 20): array {
    $particles = [];
    for ($i = 0; $i < $count; $i++) {
        $particles[] = [
            'left' => rand(0, 100) . '%',
            'top' => (70 + rand(0, 30)) . '%',
            'delay' => (rand(0, 100) / 10) . 's',
            'duration' => (8 + rand(0, 60) / 10) . 's',
            'sparkle' => rand(0, 100) > 70
        ];
    }
    return $particles;
}

$particles = generateParticles(20);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heliadora - Interactive Map</title>
    <style>
/* ========================================
   INTERACTIVE MAP STYLES
   Modern, responsive, and animated
   ======================================== */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.map-container {
    position: relative;
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    overflow: hidden;
    border-radius: 16px;
    box-shadow: 
        0 0 0 1px rgba(255, 255, 255, 0.1),
        0 25px 50px -12px rgba(0, 0, 0, 0.5),
        0 0 100px -20px rgba(139, 92, 246, 0.3);
}

.map-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: <?= $ORIGINAL_WIDTH ?> / <?= $ORIGINAL_HEIGHT ?>;
}

.map-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    animation: mapFadeIn 1s ease-out;
}

@keyframes mapFadeIn {
    from {
        opacity: 0;
        transform: scale(1.02);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* ========================================
   SVG OVERLAY FOR INTERACTIVE AREAS
   Uses percentage-based coordinates for responsive scaling
   ======================================== */

.map-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.map-overlay svg {
    width: 100%;
    height: 100%;
    pointer-events: all;
}

/* ========================================
   AREA POLYGON STYLES
   Hover effects with glowing animations
   ======================================== */

.map-area {
    fill: transparent;
    stroke: transparent;
    stroke-width: 0.3;
    cursor: pointer;
    transition: all 0.3s ease;
    filter: drop-shadow(0 0 0 transparent);
}

.map-area:hover,
.map-area.active {
    fill: var(--area-color);
    fill-opacity: 0.25;
    stroke: var(--area-color);
    stroke-width: 0.5;
    stroke-opacity: 0.9;
    animation: areaPulse 2s ease-in-out infinite;
}

.map-area.active {
    fill-opacity: 0.4;
    stroke-width: 0.8;
}

@keyframes areaPulse {
    0%, 100% {
        fill-opacity: 0.25;
        filter: drop-shadow(0 0 6px var(--area-color));
    }
    50% {
        fill-opacity: 0.45;
        filter: drop-shadow(0 0 18px var(--area-color));
    }
}

/* ========================================
   HOVER TOOLTIP LABEL
   Follows mouse cursor
   ======================================== */

.area-label {
    position: absolute;
    pointer-events: none;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(20, 20, 30, 0.85));
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    color: white;
    white-space: nowrap;
    transform: translate(-50%, -100%);
    opacity: 0;
    transition: all 0.15s ease;
    z-index: 100;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
    margin-top: -10px;
}

.area-label.visible {
    opacity: 1;
}

.area-label::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 50%;
    transform: translateX(-50%);
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 6px solid rgba(0, 0, 0, 0.9);
}

/* ========================================
   INFO PANEL (Bottom)
   Shows details for selected area
   ======================================== */

.info-panel {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: linear-gradient(145deg, rgba(15, 15, 25, 0.97), rgba(25, 25, 40, 0.95));
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 20px 28px;
    min-width: 320px;
    max-width: 420px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 50;
    box-shadow: 
        0 25px 50px rgba(0, 0, 0, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.info-panel.visible {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.info-panel-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
}

.info-panel-title::before {
    content: '';
    display: inline-block;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--area-color);
    box-shadow: 0 0 15px var(--area-color);
    animation: dotPulse 1.5s ease-in-out infinite;
}

@keyframes dotPulse {
    0%, 100% { box-shadow: 0 0 10px var(--area-color); transform: scale(1); }
    50% { box-shadow: 0 0 20px var(--area-color); transform: scale(1.1); }
}

.info-panel-description {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 18px;
}

.info-panel-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, var(--area-color), color-mix(in srgb, var(--area-color) 65%, black));
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    text-decoration: none;
    font-size: 0.95rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.info-panel-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4), 0 0 25px color-mix(in srgb, var(--area-color) 50%, transparent);
}

.info-panel-close {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: rgba(255, 255, 255, 0.6);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.2s ease;
}

.info-panel-close:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* ========================================
   ANIMATED PARTICLES
   Ambient atmosphere effect
   ======================================== */

.map-particles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    overflow: hidden;
}

.particle {
    position: absolute;
    width: 3px;
    height: 3px;
    background: radial-gradient(circle, rgba(255, 215, 0, 0.9), rgba(255, 180, 0, 0.3) 40%, transparent 70%);
    border-radius: 50%;
    animation: floatUp 10s ease-in-out infinite;
}

@keyframes floatUp {
    0% {
        transform: translateY(0) translateX(0) scale(0.5);
        opacity: 0;
    }
    15% {
        opacity: 0.8;
        transform: translateY(-20px) translateX(5px) scale(1);
    }
    85% {
        opacity: 0.6;
    }
    100% {
        transform: translateY(-120px) translateX(-10px) scale(0.3);
        opacity: 0;
    }
}

.particle.sparkle {
    background: radial-gradient(circle, rgba(255, 255, 255, 0.9), rgba(200, 200, 255, 0.3) 40%, transparent 70%);
    animation: sparkle 8s ease-in-out infinite;
}

@keyframes sparkle {
    0%, 100% {
        opacity: 0;
        transform: scale(0);
    }
    50% {
        opacity: 1;
        transform: scale(1);
    }
}

/* ========================================
   ZOOM CONTROLS
   ======================================== */

.zoom-controls {
    position: absolute;
    top: 20px;
    right: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 30;
}

.zoom-button {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: white;
    font-size: 1.4rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-weight: 300;
}

.zoom-button:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: scale(1.05);
    border-color: rgba(255, 255, 255, 0.3);
}

.zoom-button:active {
    transform: scale(0.95);
}

.zoom-level {
    position: absolute;
    top: 20px;
    left: 20px;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(10px);
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 30;
}

/* ========================================
   MAP CONTAINER WITH ZOOM
   ======================================== */

.map-scroll-container {
    width: 100%;
    overflow: hidden;
    border-radius: 16px;
    background: #0a0a12;
}

.map-inner {
    transform-origin: center center;
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ========================================
   LEGEND
   ======================================== */

.map-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
    padding: 16px 20px;
    background: linear-gradient(135deg, rgba(20, 20, 30, 0.8), rgba(30, 30, 45, 0.7));
    border-radius: 14px;
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 24px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.legend-item:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: var(--legend-color);
    transform: translateY(-1px);
}

.legend-item.active {
    background: rgba(255, 255, 255, 0.1);
    border-color: var(--legend-color);
    box-shadow: 0 0 15px color-mix(in srgb, var(--legend-color) 30%, transparent);
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--legend-color);
    box-shadow: 0 0 10px var(--legend-color);
}

.legend-text {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 500;
}

/* ========================================
   LORE SECTION
   ======================================== */

.lore-section {
    max-width: 1400px;
    margin: 40px auto 0;
    display: grid;
    gap: 20px;
}

.lore-card {
    background: linear-gradient(145deg, rgba(20, 20, 35, 0.9), rgba(30, 30, 50, 0.85));
    border-radius: 16px;
    padding: 24px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
    transition: all 0.3s ease;
}

.lore-card:hover {
    border-color: rgba(255, 215, 0, 0.2);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.lore-card h2 {
    margin: 0 0 16px 0;
    font-size: 1.4rem;
    background: linear-gradient(135deg, #ffd700, #ff9500);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.lore-card p {
    color: rgba(255, 255, 255, 0.75);
    line-height: 1.7;
    margin: 0 0 12px 0;
}

.lore-card ul {
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.8;
    margin: 0;
    padding-left: 24px;
}

.lore-card li {
    margin-bottom: 8px;
}

.lore-card li strong {
    color: rgba(255, 215, 0, 0.9);
}

.lore-card em {
    color: rgba(180, 180, 255, 0.85);
    font-style: italic;
}

.lore-card hr {
    border: none;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
    margin: 20px 0;
}

/* ========================================
   RESPONSIVE STYLES
   ======================================== */

@media (max-width: 768px) {
    .info-panel {
        left: 10px;
        right: 10px;
        transform: translateX(0) translateY(20px);
        min-width: auto;
        max-width: none;
        bottom: 10px;
        padding: 16px 20px;
    }
    
    .info-panel.visible {
        transform: translateX(0) translateY(0);
    }
    
    .zoom-controls {
        top: 10px;
        right: 10px;
    }
    
    .zoom-button {
        width: 38px;
        height: 38px;
        font-size: 1.2rem;
    }
    
    .zoom-level {
        top: 10px;
        left: 10px;
        font-size: 0.75rem;
        padding: 6px 10px;
    }
    
    .map-legend {
        gap: 8px;
        padding: 12px;
    }
    
    .legend-item {
        padding: 6px 12px;
    }
    
    .legend-text {
        font-size: 0.8rem;
    }

    .lore-card {
        padding: 18px;
    }

    .lore-card h2 {
        font-size: 1.2rem;
    }
}

/* ========================================
   PAGE THEME
   Dark fantasy aesthetic
   ======================================== */

.page-container {
    min-height: 100vh;
    background: linear-gradient(180deg, #0a0a12 0%, #12121f 40%, #0d0d18 100%);
    padding: 24px;
}

.page-header {
    text-align: center;
    margin-bottom: 28px;
}

.page-header h1 {
    font-size: 2.8rem;
    background: linear-gradient(135deg, #ffd700 0%, #ff8c00 50%, #ffd700 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 0 60px rgba(255, 215, 0, 0.2);
    margin: 0 0 8px 0;
    letter-spacing: 1px;
}

.page-header .subtitle {
    color: rgba(255, 255, 255, 0.5);
    font-style: italic;
    font-size: 1.1rem;
    margin: 0;
}

.page-header .hint {
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.9rem;
    margin-top: 12px;
}

@media (max-width: 768px) {
    .page-container {
        padding: 16px;
    }

    .page-header h1 {
        font-size: 2rem;
    }

    .page-header .subtitle {
        font-size: 1rem;
    }
}
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Aegia Aeterna - Heliadora</h1>
            <p class="subtitle">"Gift of the Sun"</p>
            <p class="hint">Click on highlighted areas to explore</p>
        </div>

        <!-- Main Map Container -->
        <div class="map-container">
            <div class="map-scroll-container">
                <div class="map-inner" id="mapInner">
                    <!-- Map Image -->
                    <div class="map-wrapper" id="mapWrapper">
                        <!-- UPDATE THIS PATH TO YOUR IMAGE -->
                        <img
                            src="images/harmontide-aa.webp"
                            alt="World map of Heliadora"
                            class="map-image"
                            draggable="false"
                        >

                        <!-- Floating Particles -->
                        <div class="map-particles">
                            <?php foreach ($particles as $p): ?>
                            <div
                                class="particle <?= $p['sparkle'] ? 'sparkle' : '' ?>"
                                style="left: <?= $p['left'] ?>; top: <?= $p['top'] ?>; animation-delay: <?= $p['delay'] ?>; animation-duration: <?= $p['duration'] ?>;"
                            ></div>
                            <?php endforeach; ?>
                        </div>

                        <!-- SVG Overlay for Interactive Areas -->
                        <div class="map-overlay">
                            <svg viewBox="0 0 100 100" preserveAspectRatio="none" style="width: 100%; height: 100%;">
                                <defs>
                                    <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
                                        <feGaussianBlur stdDeviation="1" result="coloredBlur" />
                                        <feMerge>
                                            <feMergeNode in="coloredBlur" />
                                            <feMergeNode in="SourceGraphic" />
                                        </feMerge>
                                    </filter>
                                </defs>

                                <?php foreach ($mapAreas as $area): ?>
                                <polygon
                                    class="map-area"
                                    data-id="<?= htmlspecialchars($area['id']) ?>"
                                    data-name="<?= htmlspecialchars($area['name']) ?>"
                                    data-description="<?= htmlspecialchars($area['description']) ?>"
                                    data-action="<?= htmlspecialchars($area['action']) ?>"
                                    data-href="<?= htmlspecialchars($area['href']) ?>"
                                    data-color="<?= htmlspecialchars($area['color']) ?>"
                                    points="<?= $area['points'] ?>"
                                    style="--area-color: <?= $area['color'] ?>;"
                                ></polygon>
                                <?php endforeach; ?>
                            </svg>
                        </div>

                        <!-- Hover Label -->
                        <div class="area-label" id="areaLabel"></div>
                    </div>
                </div>
            </div>

            <!-- Zoom Controls -->
            <div class="zoom-controls">
                <button class="zoom-button" onclick="zoomIn()" title="Zoom In" aria-label="Zoom In">+</button>
                <button class="zoom-button" onclick="resetZoom()" title="Reset Zoom" aria-label="Reset Zoom">⌂</button>
                <button class="zoom-button" onclick="zoomOut()" title="Zoom Out" aria-label="Zoom Out">−</button>
            </div>

            <!-- Zoom Level Indicator -->
            <div class="zoom-level" id="zoomLevel">100%</div>

            <!-- Info Panel -->
            <div class="info-panel" id="infoPanel">
                <button class="info-panel-close" onclick="closeInfoPanel()" aria-label="Close">×</button>
                <h3 class="info-panel-title" id="infoPanelTitle"></h3>
                <p class="info-panel-description" id="infoPanelDescription"></p>
                <a href="#" class="info-panel-button" id="infoPanelButton">
                    <span id="infoPanelAction"></span>
                    <span>→</span>
                </a>
            </div>
        </div>

        <!-- Legend -->
        <div class="map-legend">
            <?php foreach ($mapAreas as $area): ?>
            <div
                class="legend-item"
                data-area-id="<?= htmlspecialchars($area['id']) ?>"
                style="--legend-color: <?= $area['color'] ?>;"
                onclick="selectAreaById('<?= htmlspecialchars($area['id']) ?>')"
            >
                <div class="legend-dot"></div>
                <span class="legend-text"><?= htmlspecialchars($area['name']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Lore Section -->
        <div class="lore-section">
            <div class="lore-card">
                <h2>Heliadora - The City of Sun and Stone</h2>
                <p>
                    <strong>Heliadora</strong> is the antique capital of democracy in Aegia Aeterna—marble forums, 
                    vow-stones that hum under your palm, and boulevards laid to a ruler's line. The old stories say 
                    the city was founded where a sunbeam struck the sea and turned to stone; the people have kept 
                    those stones <em>meticulously</em> ever since. Mistreat a monument and you'll learn how quickly 
                    a smile becomes a summons—<strong>fines in the millions</strong> are not a myth.
                </p>
                <p>
                    The city's spirit is a braid of <strong>Aegian civic pride</strong> and a lively 
                    <strong>diaspora return</strong>: Aegians who grew up across the ocean brought back street 
                    music, quick talk, and a fierce love of red sauce. You'll hear orators rehearsing in the 
                    <em>Forum Aeternum</em> at dawn, then stumble past a lamplit trattoria where a Nonna argues 
                    with a poet about olive oil like it's law.
                </p>
            </div>

            <div class="lore-card">
                <h2>Districts & Landmarks</h2>
                <ul>
                    <li><strong>Forum Aeternum</strong> - The marble heart of Heliadora. Citizens debate beneath 
                    bronze laurels; the <em>Speaker's Circle</em> amplifies truth and shrinks boastfulness.</li>
                    <li><strong>Curia Solaris</strong> - Senate Hill. Murals of the <em>Dii Aeterni</em> 
                    (everyday folk call them the <em>Capitolympians</em>) watch over deliberations. Oaths here 
                    are sworn on <strong>vow-stones</strong>; break one and the stone will hairline crack for all to see.</li>
                    <li><strong>Aegis Causeways</strong> - Ancient bridges seeded with justice runes. If public 
                    hubris spikes during a festival, the causeways ring like shields until the crowd cools.</li>
                    <li><strong>Baths of Alba</strong> - White-stone baths that hold the city's whispered deals 
                    and civic gossip in equal measure. Mind your volume; even tiles have memories here.</li>
                    <li><strong>Sunmarket</strong> - Arcades of citrus, olives, parchment, and street theater. 
                    Watch for pickpockets with immaculate manners—they'll apologize as they hand your purse back 
                    (after a lecture on situational awareness).</li>
                </ul>
            </div>

            <div class="lore-card">
                <h2>Food & Culture</h2>
                <p>
                    <strong>Pizza is a civic virtue.</strong> The export and the return shaped each other; 
                    Aegians abroad invented new styles, and Heliadora welcomed them home like prodigal slices. 
                    Swing by <strong>Capitolo</strong>—touristy, yes, and priced like a senatorial toga, but 
                    the <em>Nonna Slice</em> with sun-basil and charred crust may convert you.
                </p>
                <p>
                    Other musts: <em>garum-chili oil</em> (don't ask, just drizzle), <em>lemon-ricotta sfoglia</em> 
                    after dusk, and a paper cone of fried anchovies sold on the Aegis steps—sacrilege to some, 
                    sacrament to others.
                </p>
                <hr>
                <h2>Order & Underworld</h2>
                <p>
                    Heliadora loves laws almost as much as it loves loopholes. The city watch—<strong>the Marble 
                    Ward</strong>—keeps the forums civil, but rumors persist of "<strong>Capitoline Families</strong>," 
                    oath-tight crews that grew from old guilds into velvet-gloved protection rackets. They call 
                    their quiet code the <em>Silent Concord</em>, and they <em>do not</em> appreciate outsiders 
                    using the word "mafia."
                </p>
                <p>
                    Still, the sun sees everything. The Ward's <strong>Vigil of Vows</strong> has been unpicking 
                    those shadow ledgers for years, seizing ghost accounts and shunting the recovered coin into 
                    public fountains, schools, and monument care. In Heliadora, even the underworld gets a civic audit.
                </p>
            </div>

            <div class="lore-card">
                <h2>Etiquette & Practicalities</h2>
                <ul>
                    <li><strong>At vow-stones:</strong> palm, breath, <em>then</em> speak. Boasts are considered litter.</li>
                    <li><strong>On the steps:</strong> never sit on roped stairs; those are reserved for witnesses and elders.</li>
                    <li><strong>Festival nights:</strong> the Aegis Causeways chime at "pride peak." When they do, 
                    cheer softly or the Ward will "invite" you to help scrub mosaics at sunrise.</li>
                    <li><strong>Prices:</strong> tourist strips run 5-10x normal. Ask for a <em>civic cut</em>—some 
                    vendors honor it if you can name the current Speaker.</li>
                </ul>
                <hr>
                <h2>Traveler's Note</h2>
                <p>
                    Heliadora is a city that wants you to <em>stand up straight and say something worth the echo</em>. 
                    Do that, treat the stone kindly, and the sun will lend you its good side. And if you must 
                    overpay for a slice at <strong>Capitolo</strong>? Call it a tax on nostalgia—and tip like you mean it.
                </p>
            </div>
        </div>
    </div>

    <script>
/* ========================================
   INTERACTIVE MAP JAVASCRIPT
   Handles hover effects, zoom, and info panel
   ======================================== */

// State
let currentZoom = 1;
let activeAreaId = null;

// DOM Elements
const mapInner = document.getElementById('mapInner');
const mapWrapper = document.getElementById('mapWrapper');
const areaLabel = document.getElementById('areaLabel');
const infoPanel = document.getElementById('infoPanel');
const infoPanelTitle = document.getElementById('infoPanelTitle');
const infoPanelDescription = document.getElementById('infoPanelDescription');
const infoPanelButton = document.getElementById('infoPanelButton');
const infoPanelAction = document.getElementById('infoPanelAction');
const zoomLevelDisplay = document.getElementById('zoomLevel');

// Get all polygon areas
const mapAreas = document.querySelectorAll('.map-area');
const legendItems = document.querySelectorAll('.legend-item');

// ========================================
// ZOOM FUNCTIONS
// ========================================

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
    updateZoom();
}

function updateZoom() {
    mapInner.style.transform = `scale(${currentZoom})`;
    zoomLevelDisplay.textContent = `${Math.round(currentZoom * 100)}%`;
}

// ========================================
// INFO PANEL FUNCTIONS
// ========================================

function showInfoPanel(areaId) {
    const area = document.querySelector(`.map-area[data-id="${areaId}"]`);
    if (!area) return;

    const name = area.dataset.name;
    const description = area.dataset.description;
    const action = area.dataset.action;
    const href = area.dataset.href;
    const color = area.dataset.color;

    infoPanelTitle.textContent = name;
    infoPanelDescription.textContent = description;
    infoPanelAction.textContent = action;
    infoPanelButton.href = href;
    infoPanel.style.setProperty('--area-color', color);
    
    infoPanel.classList.add('visible');
    activeAreaId = areaId;

    // Update legend
    updateLegend();
}

function closeInfoPanel() {
    infoPanel.classList.remove('visible');
    
    // Remove active class from polygon
    if (activeAreaId) {
        const area = document.querySelector(`.map-area[data-id="${activeAreaId}"]`);
        if (area) area.classList.remove('active');
    }
    
    activeAreaId = null;
    updateLegend();
}

function selectAreaById(areaId) {
    // Toggle if same area
    if (activeAreaId === areaId) {
        closeInfoPanel();
        return;
    }

    // Remove active from previous
    if (activeAreaId) {
        const prevArea = document.querySelector(`.map-area[data-id="${activeAreaId}"]`);
        if (prevArea) prevArea.classList.remove('active');
    }

    // Add active to new
    const area = document.querySelector(`.map-area[data-id="${areaId}"]`);
    if (area) {
        area.classList.add('active');
        showInfoPanel(areaId);
    }
}

function updateLegend() {
    legendItems.forEach(item => {
        if (item.dataset.areaId === activeAreaId) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
}

// ========================================
// EVENT LISTENERS FOR AREAS
// ========================================

mapAreas.forEach(area => {
    // Hover enter
    area.addEventListener('mouseenter', function(e) {
        if (activeAreaId === this.dataset.id) return;
        
        areaLabel.textContent = this.dataset.name;
        areaLabel.style.setProperty('--area-color', this.dataset.color);
        areaLabel.classList.add('visible');
    });

    // Hover move
    area.addEventListener('mousemove', function(e) {
        if (activeAreaId === this.dataset.id) return;

        const rect = mapWrapper.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        areaLabel.style.left = `${x}%`;
        areaLabel.style.top = `${y}%`;
    });

    // Hover leave
    area.addEventListener('mouseleave', function() {
        areaLabel.classList.remove('visible');
    });

    // Click
    area.addEventListener('click', function(e) {
        e.stopPropagation();
        
        // Toggle if clicking same area
        if (activeAreaId === this.dataset.id) {
            closeInfoPanel();
            return;
        }

        // Remove active from previous
        if (activeAreaId) {
            const prevArea = document.querySelector(`.map-area[data-id="${activeAreaId}"]`);
            if (prevArea) prevArea.classList.remove('active');
        }

        // Set new active
        this.classList.add('active');
        showInfoPanel(this.dataset.id);
        
        // Hide hover label
        areaLabel.classList.remove('visible');
    });
});

// Close info panel when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.map-area') && 
        !e.target.closest('.info-panel') && 
        !e.target.closest('.legend-item')) {
        closeInfoPanel();
    }
});

// ========================================
// KEYBOARD NAVIGATION
// ========================================

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeInfoPanel();
    }
    if (e.key === '+' || e.key === '=') {
        zoomIn();
    }
    if (e.key === '-') {
        zoomOut();
    }
    if (e.key === '0') {
        resetZoom();
    }
});

// ========================================
// TOUCH SUPPORT
// ========================================

let touchStartDistance = 0;

mapWrapper.addEventListener('touchstart', function(e) {
    if (e.touches.length === 2) {
        touchStartDistance = Math.hypot(
            e.touches[0].clientX - e.touches[1].clientX,
            e.touches[0].clientY - e.touches[1].clientY
        );
    }
}, { passive: true });

mapWrapper.addEventListener('touchmove', function(e) {
    if (e.touches.length === 2) {
        const currentDistance = Math.hypot(
            e.touches[0].clientX - e.touches[1].clientX,
            e.touches[0].clientY - e.touches[1].clientY
        );
        
        const scale = currentDistance / touchStartDistance;
        currentZoom = Math.min(Math.max(currentZoom * scale, 0.5), 2.5);
        updateZoom();
        touchStartDistance = currentDistance;
    }
}, { passive: true });
    </script>
</body>
</html>
