<?php
require_login();

$shacks = [
    'aa_paint_shack' => 'Aegia Aeterna',
    'bm_paint_shack' => 'Baharamandal',
    'br_paint_shack' => 'Bretonreach',
    'cc_paint_shack' => 'Crescent Caliphate',
    'esd_paint_shack' => 'Eagle Serpent Dominion',
    'esl_paint_shack' => 'Eretz-Shalem League',
    'gc_paint_shack' => 'Gran Columbia',
    'h_paint_shack' => 'Hammurabia',
    'ie_paint_shack' => 'Itzam Empire',
    'k_paint_shack' => 'Kemet',
    'ldk_paint_shack' => 'Lotus-Dragon Kingdom',
    'nh_paint_shack' => 'Nornheim',
    'rsc_paint_shack' => 'Red Sun Commonwealth',
    'rl_paint_shack' => 'Rheinland',
    'rt_paint_shack' => 'Rodinian Tsardom',
    'sie_paint_shack' => 'Sapa Inti Empire',
    'sc_paint_shack' => 'Sila Council',
    'stap_paint_shack' => 'Sovereign Tribes of the Ancestral Plains',
    'srl_paint_shack' => 'Spice Route League',
    'urb_paint_shack' => 'United free Republic of Borealia',
    'xm_paint_shack' => 'Xochimex',
    'ynk_paint_shack' => 'Yamanokubo',
    'yn_paint_shack' => 'Yara Nations',
];
?>
<h1>Paint Shacks</h1>
<p class="muted">Each region keeps its own paint shack. Choose the one that matches your creature's homeland.</p>
<ul class="card-list">
  <?php foreach ($shacks as $page => $name): ?>
    <li><a href="?pg=<?= urlencode($page) ?>"><?= htmlspecialchars($name) ?> Paint Shack</a></li>
  <?php endforeach; ?>
</ul>