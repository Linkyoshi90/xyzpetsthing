<?php
/**
 * Interactive Game Map - Skeldgard (Nornheim Capital)
 * A modern, responsive interactive map with hover effects and animations
 * Frost/Nordic theme for the Shield-Yard of the North
 * All CSS and JS embedded in this single file
 * 
 * To use: Place this file in your PHP project and update the image path
 * 
 * UPDATED: Corrected coordinates for Paint Shack and Adventure based on screenshot markings
 */

// If you need authentication, uncomment and adjust:
// require_login();

// Map areas configuration - coordinates converted to percentages for responsive scaling
// Original image dimensions (adjust these to match your actual image)
// Based on coordinate ranges, estimated image size is approximately:
$ORIGINAL_WIDTH = 800;
$ORIGINAL_HEIGHT = 550;

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
// CORRECTED COORDINATES based on screenshot markings:
// - RED markings = Adventure/Weeping Lanes (upper middle area)
// - YELLOW markings = Paint Shack (middle-left area)
$mapAreas = [
    [
        'id' => 'paint_shack',
        'name' => 'Paint Shack',
        'description' => 'Here you can paint your creatures from this region. Nornheim artisans use frost-pigments that shimmer in the northern lights.',
        'action' => 'Explore',
        'href' => '?pg=nh_paint_shack',
        // CORRECTED: Yellow markings - middle-left portion of map
        'points' => toPercentPoints([295, 365, 340, 395, 365, 415, 365, 460, 340, 485, 295, 460, 280, 410], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#06b6d4' // Cyan - ice blue
    ],
    [
        'id' => 'nh-adventure',
        'name' => 'The Weeping Lanes',
        'description' => 'A time-locked horror adventure set in a deserted village near Skeldgard. Enter at dusk, if you dare.',
        'action' => 'Enter at Dusk',
        'href' => '?pg=nh-adventure',
        // CORRECTED: Red markings - upper middle portion of map
        'points' => toPercentPoints([465, 95, 525, 120, 530, 185, 490, 210, 465, 195, 460, 130], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#a855f7' // Purple - mysterious/horror
    ]
];

// Generate particles for ambient animation (aurora/frost theme)
function generateParticles(int $count = 25): array {
    $particles = [];
    for ($i = 0; $i < $count; $i++) {
        $particles[] = [
            'left' => rand(0, 100) . '%',
            'top' => rand(0, 100) . '%',
            'delay' => (rand(0, 100) / 10) . 's',
            'duration' => (6 + rand(0, 80) / 10) . 's',
            'type' => ['snowflake', 'aurora', 'sparkle'][rand(0, 2)]
        ];
    }
    return $particles;
}

$particles = generateParticles(25);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skeldgard - Nornheim Capital</title>
    <style>
/* ========================================
   INTERACTIVE MAP STYLES - SKELDGARD
   Frost/Nordic Theme
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
        0 0 0 1px rgba(100, 200, 255, 0.15),
        0 25px 50px -12px rgba(0, 0, 0, 0.6),
        0 0 100px -20px rgba(6, 182, 212, 0.3),
        inset 0 0 60px rgba(100, 200, 255, 0.03);
}


.map-back-link {
    position: absolute;
    top: 16px;
    left: 16px;
    z-index: 120;
    text-decoration: none;
    color: #fff;
    background: rgba(6, 10, 24, 0.82);
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 0.9rem;
}

.map-back-link:hover {
    background: rgba(20, 28, 48, 0.92);
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
    animation: mapFadeIn 1.2s ease-out;
}

@keyframes mapFadeIn {
    from {
        opacity: 0;
        transform: scale(1.02);
        filter: brightness(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
        filter: brightness(1);
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
   Frost/Ice themed hover effects
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
    stroke-width: 0.6;
    stroke-opacity: 0.9;
    animation: frostPulse 2.5s ease-in-out infinite;
}

.map-area.active {
    fill-opacity: 0.4;
    stroke-width: 0.9;
}

@keyframes frostPulse {
    0%, 100% {
        fill-opacity: 0.25;
        filter: drop-shadow(0 0 8px var(--area-color));
    }
    50% {
        fill-opacity: 0.45;
        filter: drop-shadow(0 0 22px var(--area-color)) drop-shadow(0 0 40px color-mix(in srgb, var(--area-color) 30%, transparent));
    }
}

/* ========================================
   HOVER TOOLTIP LABEL
   Frost-themed styling
   ======================================== */

.area-label {
    position: absolute;
    pointer-events: none;
    background: linear-gradient(135deg, rgba(10, 30, 50, 0.95), rgba(20, 50, 80, 0.9));
    backdrop-filter: blur(12px);
    border: 1px solid rgba(100, 200, 255, 0.25);
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
    box-shadow: 
        0 8px 20px rgba(0, 0, 0, 0.5),
        0 0 20px rgba(100, 200, 255, 0.1);
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
    border-top: 6px solid rgba(10, 30, 50, 0.95);
}

/* ========================================
   INFO PANEL (Bottom)
   Frost-themed panel
   ======================================== */

.info-panel {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: linear-gradient(145deg, rgba(10, 25, 45, 0.97), rgba(15, 40, 70, 0.95));
    backdrop-filter: blur(20px);
    border: 1px solid rgba(100, 200, 255, 0.15);
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
        inset 0 1px 0 rgba(100, 200, 255, 0.1),
        0 0 40px rgba(100, 200, 255, 0.05);
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
    animation: frostDotPulse 1.8s ease-in-out infinite;
}

@keyframes frostDotPulse {
    0%, 100% { 
        box-shadow: 0 0 10px var(--area-color); 
        transform: scale(1); 
    }
    50% { 
        box-shadow: 0 0 25px var(--area-color), 0 0 40px color-mix(in srgb, var(--area-color) 40%, white); 
        transform: scale(1.1); 
    }
}

.info-panel-description {
    color: rgba(200, 220, 240, 0.85);
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 18px;
}

.info-panel-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, var(--area-color), color-mix(in srgb, var(--area-color) 60%, #0a1628));
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
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.4), 
        0 0 30px color-mix(in srgb, var(--area-color) 50%, transparent);
}

.info-panel-close {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(100, 200, 255, 0.1);
    border: none;
    color: rgba(200, 220, 240, 0.6);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.2s ease;
}

.info-panel-close:hover {
    background: rgba(100, 200, 255, 0.2);
    color: white;
}

/* ========================================
   ANIMATED PARTICLES
   Aurora, snowflakes, and frost sparkles
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
    border-radius: 50%;
}

/* Snowflake particles */
.particle.snowflake {
    width: 4px;
    height: 4px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.9), rgba(200, 230, 255, 0.4) 40%, transparent 70%);
    animation: snowfall 12s linear infinite;
}

@keyframes snowfall {
    0% {
        transform: translateY(-10px) translateX(0) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 0.8;
    }
    90% {
        opacity: 0.6;
    }
    100% {
        transform: translateY(calc(100vh)) translateX(30px) rotate(360deg);
        opacity: 0;
    }
}

/* Aurora particles */
.particle.aurora {
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, 
        transparent, 
        rgba(100, 200, 255, 0.3), 
        rgba(150, 100, 255, 0.4), 
        rgba(100, 255, 200, 0.3), 
        transparent
    );
    border-radius: 50%;
    animation: aurora 15s ease-in-out infinite;
    filter: blur(2px);
}

@keyframes aurora {
    0%, 100% {
        transform: translateX(-50%) scaleX(0.5);
        opacity: 0;
    }
    20%, 80% {
        opacity: 0.6;
    }
    50% {
        transform: translateX(50%) scaleX(1.2);
        opacity: 0.8;
    }
}

/* Sparkle particles */
.particle.sparkle {
    width: 3px;
    height: 3px;
    background: radial-gradient(circle, rgba(200, 240, 255, 1), rgba(100, 200, 255, 0.5) 40%, transparent 70%);
    animation: frostSparkle 6s ease-in-out infinite;
}

@keyframes frostSparkle {
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
    background: rgba(10, 30, 50, 0.7);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(100, 200, 255, 0.2);
    color: rgba(200, 230, 255, 0.9);
    font-size: 1.4rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-weight: 300;
}

.zoom-button:hover {
    background: rgba(100, 200, 255, 0.15);
    transform: scale(1.05);
    border-color: rgba(100, 200, 255, 0.4);
    color: white;
}

.zoom-button:active {
    transform: scale(0.95);
}

.zoom-level {
    position: absolute;
    top: 20px;
    left: 20px;
    background: rgba(10, 30, 50, 0.7);
    backdrop-filter: blur(10px);
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.85rem;
    color: rgba(200, 230, 255, 0.85);
    border: 1px solid rgba(100, 200, 255, 0.15);
    z-index: 30;
}

/* ========================================
   MAP CONTAINER WITH ZOOM
   ======================================== */

.map-scroll-container {
    width: 100%;
    overflow: hidden;
    border-radius: 16px;
    background: linear-gradient(180deg, #050a15 0%, #0a1525 100%);
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
    background: linear-gradient(135deg, rgba(10, 25, 50, 0.85), rgba(15, 35, 65, 0.75));
    border-radius: 14px;
    backdrop-filter: blur(12px);
    border: 1px solid rgba(100, 200, 255, 0.1);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    background: rgba(100, 200, 255, 0.03);
    border-radius: 24px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.legend-item:hover {
    background: rgba(100, 200, 255, 0.08);
    border-color: var(--legend-color);
    transform: translateY(-1px);
}

.legend-item.active {
    background: rgba(100, 200, 255, 0.12);
    border-color: var(--legend-color);
    box-shadow: 0 0 20px color-mix(in srgb, var(--legend-color) 35%, transparent);
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--legend-color);
    box-shadow: 0 0 12px var(--legend-color);
}

.legend-text {
    font-size: 0.9rem;
    color: rgba(200, 230, 255, 0.9);
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
    background: linear-gradient(145deg, rgba(10, 25, 50, 0.92), rgba(15, 35, 65, 0.88));
    border-radius: 16px;
    padding: 24px;
    border: 1px solid rgba(100, 200, 255, 0.1);
    backdrop-filter: blur(12px);
    transition: all 0.3s ease;
}

.lore-card:hover {
    border-color: rgba(100, 200, 255, 0.25);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), 0 0 30px rgba(100, 200, 255, 0.05);
}

.lore-card h2 {
    margin: 0 0 16px 0;
    font-size: 1.4rem;
    background: linear-gradient(135deg, #67e8f9, #38bdf8, #818cf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.lore-card h3 {
    margin: 16px 0 10px 0;
    font-size: 1.15rem;
    color: #67e8f9;
}

.lore-card p {
    color: rgba(200, 220, 240, 0.8);
    line-height: 1.7;
    margin: 0 0 12px 0;
}

.lore-card ul {
    color: rgba(200, 220, 240, 0.75);
    line-height: 1.8;
    margin: 0;
    padding-left: 24px;
}

.lore-card li {
    margin-bottom: 8px;
}

.lore-card li strong {
    color: #67e8f9;
}

.lore-card em {
    color: rgba(150, 180, 255, 0.9);
    font-style: italic;
}

.lore-card hr {
    border: none;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(100, 200, 255, 0.2), transparent);
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
   Frost/Nordic aesthetic
   ======================================== */

.page-container {
    min-height: 100vh;
    background: linear-gradient(180deg, #030810 0%, #0a1525 40%, #050a15 100%);
    padding: 24px;
}

.page-header {
    text-align: center;
    margin-bottom: 28px;
}

.page-header h1 {
    font-size: 2.8rem;
    background: linear-gradient(135deg, #67e8f9 0%, #38bdf8 30%, #818cf8 70%, #a78bfa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 0 60px rgba(100, 200, 255, 0.25);
    margin: 0 0 8px 0;
    letter-spacing: 2px;
}

.page-header .subtitle {
    color: rgba(150, 180, 220, 0.6);
    font-style: italic;
    font-size: 1.1rem;
    margin: 0;
}

.page-header .hint {
    color: rgba(150, 180, 220, 0.45);
    font-size: 0.9rem;
    margin-top: 12px;
}

/* Frost overlay effect */
.page-container::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(ellipse at 20% 20%, rgba(100, 200, 255, 0.03) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 80%, rgba(150, 100, 255, 0.02) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

.page-container > * {
    position: relative;
    z-index: 1;
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
            <h1>Nornheim - Skeldgard</h1>
            <p class="subtitle">"Shield-Yard of the North"</p>
            <p class="hint">Click on highlighted areas to explore</p>
        </div>

        <!-- Main Map Container -->
        <div class="map-container">
            <a class="map-back-link" href="?pg=auronia">← Back to Auronia</a>
            <div class="map-scroll-container">
                <div class="map-inner" id="mapInner">
                    <!-- Map Image -->
                    <div class="map-wrapper" id="mapWrapper">
                        <!-- UPDATE THIS PATH TO YOUR IMAGE -->
                        <img
                            src="images/harmontide-skeldgard.webp"
                            alt="Map of Skeldgard, Nornheim"
                            class="map-image"
                            draggable="false"
                        >

                        <!-- Floating Particles -->
                        <div class="map-particles">
                            <?php foreach ($particles as $p): ?>
                            <div
                                class="particle <?= $p['type'] ?>"
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
                <h2>Skeldgard - Shield-Yard of the North</h2>
                <p>
                    <strong>Skeldgard</strong> stands at the head of a blue fjord where wind and glacier bargain. 
                    It is the thingstead-capital of Nornheim—stone circles for law, longhalls for counsel, and 
                    a cliff-side terrace where roots like iron twist from the earth. The city lives by a simple 
                    measure: <em>oaths keep the cold out</em>. Break them, and the cold remembers your name.
                </p>
                <p>
                    At the city's crown rises the <strong>Shieldring</strong>, a broad assembly ground where the 
                    folk gather to speak under the eye of the <em>Nornweave</em>. Three loom-poles stand at the 
                    center. Citizens tie grievance-ribbons to the warp and, before witnesses, weave them into 
                    the public cloth—anger measured and put away, draugr kept from forming. The saga-singers say 
                    the Worldtree once brushed this hill; Skeldgard treats every law as a branch that must flex, 
                    not freeze.
                </p>
            </div>

            <div class="lore-card">
                <h2>Districts & Landmarks</h2>
                <ul>
                    <li><strong>The Shieldring (Thingstead)</strong> - Stone tiers encircle the <em>Law Rock</em>. 
                    Oaths are spoken in low voices; boasting counts as wind and is struck from the record.</li>
                    <li><strong>Worldroot Terrace</strong> - Terraced gardens braided with living roots and the 
                    <em>Public Loom</em>. At dusk the cloth glows faintly—proof that today's quarrels were made small.</li>
                    <li><strong>Whale-Gate Harbor</strong> - Ice-scored piers and a gate carved as a breaching 
                    leviathan. The harbor bell is a whale rib; it tolls when storms or strangers come in numbers.</li>
                    <li><strong>Hall of Runes</strong> - The runographers' college. Runes are cut in patient strokes; 
                    quick cuts turn to curses. Visitors may commission a <em>truth stave</em> (tiny, worn at the throat) 
                    to keep tongues honest.</li>
                    <li><strong>Valkyrie Steps</strong> - A steep stair of black stone leading to the wind-shrine. 
                    Shields hang there, gifts to the storm for safe roads; take one without trade and the gale will 
                    take your hat first, your footing second.</li>
                    <li><strong>Oath-Ice Bridge</strong> - A frostbound span seeded with vow-stone. It sings when a 
                    promise is kept upon it and hairline-cracks when one is not. Couples cross it hand-in-hand; 
                    traders cross it counting.</li>
                    <li><strong>Hearthlane Market</strong> - Night stalls lit by oil and amber. Smoked fish, hot 
                    berry-cakes, brass clasp-work, and quiet bargains over felted mittens.</li>
                </ul>
            </div>

            <div class="lore-card">
                <h2>Table & Tankard</h2>
                <p>
                    Skeldgard hospitality is plain and proud. Bowls of <strong>root stew</strong> arrive with dark 
                    bread and a pat of cultured cream; <strong>fir-smoked trout</strong> flakes beside pickled greens; 
                    and the kettle yields <strong>pine-needle tea</strong> for clear heads or <strong>honey-mead</strong> 
                    for warm boasts. Try the winter specialty: <em>ember-cheese on rye</em>, crisped on a stone and 
                    eaten standing. Outsiders ask for "glory cuts"; locals laugh and serve you the end-slice with 
                    extra char.
                </p>
                <p>
                    If you need a place name to remember, find the longhouse called <strong>Three Looms</strong>. 
                    The stewing pot never empties, and the keeper can recite the last ten oaths sworn at the 
                    Shieldring without peeking at the board. Prices rise with the wind; calmer days are cheaper days.
                </p>
                <hr>
                <h3>Order & Shadow</h3>
                <p>
                    The city watch is the <strong>Shield-Thing</strong>, oath-bound neighbors in wolf-gray cloaks. 
                    They carry <em>weregild ledgers</em> and little patience for "clever law." Skeldgard admits 
                    there are <strong>Ice-Deeds</strong>—unsigned contracts that pass hand-to-hand in the thaw, 
                    binding favors outside the ring. The Shield-Thing doesn't call it a mafia; they call it 
                    <em>frozen pride</em>. When an Ice-Deed harms guest-right, the public loom takes the names 
                    and the draugr go hungry that week.
                </p>
            </div>

            <div class="lore-card">
                <h2>Etiquette & Practicalities</h2>
                <ul>
                    <li><strong>At thresholds:</strong> touch the lintel rune with two fingers, then speak your name. 
                    Names said softly freeze less.</li>
                    <li><strong>Guest-right:</strong> accept bread, salt, and stew before business. Refusal is an 
                    insult; overeager bargaining is worse.</li>
                    <li><strong>Boasts:</strong> speak deeds, not "will-dos." If you must promise, tie a red thread 
                    to your sleeve until it is kept.</li>
                    <li><strong>Weather:</strong> the north wind keeps a tally. Whistling at it adds one to the wrong column.</li>
                    <li><strong>Prices:</strong> harbor stalls mark <em>storm rates</em>. Ask for the <em>calm measure</em> 
                    after the bell; most will oblige.</li>
                </ul>
                <hr>
                <h3>Calendar & Belief</h3>
                <p>
                    Skeldgard keeps the <strong>Weaving Nights</strong> at midwinter. Families bring ribbons of 
                    quarrels and gratitude; the city weaves them into the public cloth while skalds sing the 
                    <em>Wyrd Verses</em>—lines that bend fate toward mercy. In spring, the <strong>Ravenfast</strong> 
                    blesses messengers and road-guards. Temples to the <em>Æsir-Vanir Concord</em> stand shoulder to 
                    shoulder with shrines to river, wind, and hearth; the goðar (priests) share benches with 
                    runographers and Shield-Thing captains. Oath and craft drink from the same horn.
                </p>
            </div>

            <div class="lore-card">
                <h2>Traveler's Note</h2>
                <p>
                    Skeldgard does not care if you are important; it cares if you are <em>kept</em>. Keep your word, 
                    keep your boots by the door, keep a coin for the ferry-boy, keep a story for the night—do these 
                    and the city will keep you. Fail, and you may feel the law like a cold hand on the back of your 
                    neck, guiding you politely to the loom.
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
