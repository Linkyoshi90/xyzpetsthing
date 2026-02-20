<?php
$ORIGINAL_WIDTH = 1600;
$ORIGINAL_HEIGHT = 900;

function toPercentPoints(array $coords, int $width, int $height): string {
    $points = [];
    for ($i = 0; $i < count($coords); $i += 2) {
        $x = round(($coords[$i] / $width) * 100, 2);
        $y = round(($coords[$i + 1] / $height) * 100, 2);
        $points[] = "{$x},{$y}";
    }
    return implode(' ', $points);
}

$mapAreas = [
    [
        'id' => 'fangroot-canyon',
        'name' => 'Fangroot Canyon',
        'description' => 'A root-hung chasm where feathered hunters and climbing herbivores test each other on ledges of sun-yellow stone.',
        'action' => 'Read Field Notes',
        'href' => '#fangroot-canyon',
        'points' => toPercentPoints([275, 220, 510, 170, 650, 260, 600, 360, 370, 390, 250, 310], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#f97316'
    ],
    [
        'id' => 'emberfen',
        'name' => 'Emberfen',
        'description' => 'Warm black mud, giant dragonflies, and spore-light nights over steaming reed pools.',
        'action' => 'Survey Wetlands',
        'href' => '#emberfen',
        'points' => toPercentPoints([770, 420, 980, 380, 1120, 500, 1085, 650, 850, 690, 710, 560], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#14b8a6'
    ],
    [
        'id' => 'skycrow-ledge',
        'name' => 'Skycrow Ledge',
        'description' => 'The broken outer rim where gliding birds ride updrafts and Stonebond sky-watchers hang rope nests.',
        'action' => 'Watch the Rim',
        'href' => '#skycrow-ledge',
        'points' => toPercentPoints([1060, 160, 1450, 110, 1545, 250, 1500, 420, 1230, 360], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#38bdf8'
    ],
    [
        'id' => 'sunprint-basin',
        'name' => 'Sunprint Basin',
        'description' => 'A clay basin where tracks appear overnight as if the plateau remembers every footfall.',
        'action' => 'Inspect Tracks',
        'href' => '#sunprint-basin',
        'points' => toPercentPoints([560, 560, 725, 510, 840, 580, 805, 760, 640, 810, 505, 700], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#facc15'
    ],
    [
        'id' => 'bone-chorus-cave',
        'name' => 'Bone Chorus Cave',
        'description' => 'A rib-lined ritual cave whose fossil walls ring like bells when struck.',
        'action' => 'Hear the Chorus',
        'href' => '#bone-chorus-cave',
        'points' => toPercentPoints([325, 480, 470, 450, 555, 515, 520, 615, 360, 650, 290, 565], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#c084fc'
    ],
    [
        'id' => 'echo-grottos',
        'name' => 'Echo Grottos (Sealed)',
        'description' => 'Transient cave gates whispered to open from hidden Sapa Inti cave routes. Currently dormant and inaccessible.',
        'action' => 'Gate Locked',
        'href' => '#access',
        'points' => toPercentPoints([30, 30, 290, 20, 360, 135, 260, 230, 80, 210], $ORIGINAL_WIDTH, $ORIGINAL_HEIGHT),
        'color' => '#64748b'
    ],
];
?>

<div class="map-shell aeonstep-shell">
    <div class="map-container aeonstep-map">
        <a class="map-back-link" href="?pg=sie">← Back to Sapa Inti Empire</a>

        <div class="map-wrapper">
            <img src="images/harmontide-smol.png" alt="Aeonstep Plateau concept map" class="map-image" />
            <div class="map-tint"></div>

            <div class="map-overlay">
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                    <?php foreach ($mapAreas as $area): ?>
                        <polygon
                            class="map-area"
                            data-id="<?= htmlspecialchars($area['id']) ?>"
                            data-name="<?= htmlspecialchars($area['name']) ?>"
                            data-description="<?= htmlspecialchars($area['description']) ?>"
                            data-action="<?= htmlspecialchars($area['action']) ?>"
                            data-href="<?= htmlspecialchars($area['href']) ?>"
                            points="<?= htmlspecialchars($area['points']) ?>"
                            style="--area-color: <?= htmlspecialchars($area['color']) ?>"
                            tabindex="0"
                            role="button"
                            aria-label="<?= htmlspecialchars($area['name']) ?>"
                        ></polygon>
                    <?php endforeach; ?>
                </svg>
            </div>

            <div class="area-label" id="areaLabel"></div>

            <div class="info-panel" id="infoPanel">
                <h3 id="panelTitle">Aeonstep Plateau</h3>
                <p id="panelDescription">The Stillplate waits above the cloud line. Select a landmark to view lore notes.</p>
                <a id="panelAction" class="panel-action" href="#overview">Read Overview</a>
            </div>
        </div>
    </div>

    <section class="aeonstep-copy" id="overview">
        <h1>Aeonstep Plateau</h1>
        <p class="subtitle">The Stillplate • The Land Before Noon • The Skyroot Table</p>
        <p>Hidden high above the cloud line, Aeonstep is a broken table of stone where time moves strangely. Fern forests fill sinkholes, rivers cut blue scars through yellow rock, and ancient calls roll like drums through warm air heavy with sap and dust.</p>
        <p>It is not frozen in time—seasons turn and storms still come—but ages do not. Stone age clans, thunder-lizards, giant birds, and shaggy megafauna share one visible, living web where nothing feels obsolete.</p>
    </section>

    <section class="aeonstep-lore-grid" id="access">
        <article class="lore-card">
            <h2>Access &amp; Echo Grottos</h2>
            <p>Echo Grottos are rumored to appear in old cave systems tied to the Sapa Inti highlands when fear and hunger spike in the outer world. Rock shifts, pale sky appears, and the passage opens further than it should.</p>
            <p><strong>Current status:</strong> sealed. The gate network is dormant, so this area is intentionally inaccessible for now.</p>
            <a href="?pg=sie">Return to Sapa Inti Empire routes</a>
        </article>
        <article class="lore-card" id="fangroot-canyon">
            <h2>Fangroot Canyon &amp; Shellplain</h2>
            <p>Fangroot Canyon hangs with impossible roots and returning echoes that come back as roars. Nearby, Shellplain hosts stoneback herds and beaked scavengers among fossil-littered flats.</p>
            <a href="#overview">Return to regional brief</a>
        </article>
        <article class="lore-card" id="emberfen">
            <h2>Emberfen &amp; Sunprint Basin</h2>
            <p>Emberfen bubbles with hot springs, dragonflies, mirelurks, and reedbiters that recycle decay into life. Sunprint Basin bakes tracks in clay—and somehow records prints no one saw being made.</p>
            <a href="#sunprint-basin">Track reports</a>
        </article>
        <article class="lore-card" id="skycrow-ledge">
            <h2>Skycrow Ledge &amp; Bone Chorus Cave</h2>
            <p>At the cliff rim, giant skycrows ride the wind while Stonebond sky-watchers keep rope nests and chimes. Deeper in, Bone Chorus Cave is used for rituals and warnings: fossil ribs ring like bells.</p>
            <a href="#bone-chorus-cave">Ritual notes</a>
        </article>
        <article class="lore-card" id="inhabitants">
            <h2>Stonebond &amp; Creature Web</h2>
            <p>Stonebond clans bond to pieces of the food web: root-gatherers, egg-keepers, carrion-speakers, and sky-watchers. Thunder grazers, fangfeather packs, shellbacks, and skycrows keep the system tense but balanced.</p>
            <a href="#harmony">Read harmony doctrine</a>
        </article>
        <article class="lore-card" id="harmony">
            <h2>Harmony &amp; Emotion Weather</h2>
            <p>On Aeonstep, fear, hunger, and relief cycle like weather. When greed enters, imbalance answers: stampedes, failed hunts, boiling fenwater, and treacherous rim winds. The Plateau Hunger punishes hoarding first.</p>
            <a href="?pg=map">Back to world map</a>
        </article>
    </section>
</div>

<style>
.aeonstep-shell { color: #eef2ff; }
.aeonstep-map {
    position: relative;
    max-width: 1400px;
    margin: 0 auto;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 70px -30px rgba(0,0,0,.8), 0 0 0 1px rgba(255,255,255,.1);
}
.map-wrapper { position: relative; width: 100%; aspect-ratio: <?= $ORIGINAL_WIDTH ?> / <?= $ORIGINAL_HEIGHT ?>; }
.map-image { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; filter: saturate(0.6) contrast(1.1); }
.map-tint { position:absolute; inset:0; background: radial-gradient(circle at 20% 20%, rgba(250,204,21,.15), transparent 40%), linear-gradient(135deg, rgba(8,47,73,.78), rgba(63,12,37,.68)); }
.map-overlay, .map-overlay svg { position:absolute; inset:0; width:100%; height:100%; }
.map-overlay { pointer-events:none; }
.map-overlay svg { pointer-events:all; }

.map-back-link {
    position:absolute; z-index:4; top:16px; left:16px; color:#fff; text-decoration:none;
    background:rgba(2,6,23,.82); border:1px solid rgba(255,255,255,.24); border-radius:8px; padding:7px 11px;
}

.map-area { fill: transparent; stroke: transparent; stroke-width: .25; cursor: pointer; transition: all .25s ease; }
.map-area:hover, .map-area.active, .map-area:focus {
    fill: var(--area-color); fill-opacity: .28; stroke: var(--area-color); stroke-opacity: .95; stroke-width: .6;
    filter: drop-shadow(0 0 12px var(--area-color));
}

.area-label {
    position:absolute; pointer-events:none; opacity:0; transform:translate(-50%, -120%); transition:opacity .16s ease;
    background: rgba(2,6,23,.92); border:1px solid rgba(255,255,255,.2); border-radius:8px; padding:6px 10px; font-size:.85rem; z-index:6;
}
.area-label.visible { opacity:1; }

.info-panel {
    position:absolute; right:20px; bottom:20px; z-index:5; max-width:380px;
    background: rgba(2,6,23,.84); border:1px solid rgba(255,255,255,.18); border-radius:12px; padding:14px 16px;
}
.info-panel h3 { margin:0 0 6px; font-size:1.1rem; }
.info-panel p { margin:0 0 10px; font-size:.92rem; line-height:1.45; color:#dbeafe; }
.panel-action { display:inline-block; color:#fde68a; text-decoration:none; font-weight:700; }

.aeonstep-copy { max-width:1000px; margin:24px auto 0; }
.subtitle { color:#cbd5e1; margin-top:-4px; }
.aeonstep-lore-grid {
    max-width:1100px; margin:18px auto 20px; display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:12px;
}
.lore-card { background:#0f172acc; border:1px solid #334155; border-radius:12px; padding:14px; }
.lore-card h2 { margin:0 0 8px; font-size:1rem; color:#fef08a; }
.lore-card p { margin:0 0 10px; color:#dbeafe; font-size:.93rem; line-height:1.42; }
.lore-card a { color:#93c5fd; text-decoration:none; font-weight:600; }
</style>

<script>
(() => {
    const areas = Array.from(document.querySelectorAll('.map-area'));
    const label = document.getElementById('areaLabel');
    const panelTitle = document.getElementById('panelTitle');
    const panelDescription = document.getElementById('panelDescription');
    const panelAction = document.getElementById('panelAction');

    function setPanel(area) {
        panelTitle.textContent = area.dataset.name;
        panelDescription.textContent = area.dataset.description;
        panelAction.textContent = area.dataset.action;
        panelAction.href = area.dataset.href;
    }

    function activate(area) {
        areas.forEach(el => el.classList.remove('active'));
        area.classList.add('active');
        setPanel(area);
    }

    function placeLabel(area, event) {
        const rect = event.currentTarget.ownerSVGElement.getBoundingClientRect();
        label.textContent = area.dataset.name;
        label.style.left = `${event.clientX - rect.left}px`;
        label.style.top = `${event.clientY - rect.top}px`;
        label.classList.add('visible');
    }

    areas.forEach(area => {
        area.addEventListener('mouseenter', (event) => placeLabel(area, event));
        area.addEventListener('mousemove', (event) => placeLabel(area, event));
        area.addEventListener('mouseleave', () => label.classList.remove('visible'));
        area.addEventListener('focus', () => activate(area));
        area.addEventListener('click', () => activate(area));
        area.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                activate(area);
                window.location.hash = area.dataset.href.replace('#', '');
            }
        });
    });
})();
</script>
