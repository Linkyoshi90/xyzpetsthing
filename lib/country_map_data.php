<?php

require_once __DIR__.'/country_interactive_map.php';

function get_country_map_config(string $slug): ?array {
    $w = 1531;
    $h = 811;

    $configs = [
        'baharamandal' => [
            'title' => 'Baharamandal - Padmanagara',
            'subtitle' => 'City of the Lotus',
            'image' => 'images/harmontide-padmanagara.webp',
            'lore' => 'Padmanagara is a river-city of temples, scholarship, and market craft where beauty is treated as civic duty.',
            'back_label' => 'Back to Dawnmarch',
            'back_href' => '?pg=dawnmarch',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Paint your local pets with Baharamandal palettes.', 'action' => 'Explore', 'href' => '?pg=bm_paint_shack', 'color' => '#8b5cf6', 'points' => country_map_rect_points(190, 520, 260, 175, $w, $h)],
                ['name' => 'Picnic Tree', 'description' => 'Pick up random goodies from Padmanagara\'s community tree.', 'action' => 'Visit', 'href' => '?pg=bm_pt', 'color' => '#22c55e', 'points' => country_map_rect_points(520, 420, 240, 160, $w, $h)],
            ],
        ],
        'bretonreach' => [
            'title' => 'Bretonreach - Avalore',
            'subtitle' => 'The Bastion of Stormwalls',
            'image' => 'images/harmontide-br.webp',
            'lore' => 'Avalore is a fortress-port where guild law, sea trade, and watchful walls shape daily life.',
            'back_label' => 'Back to Auronia',
            'back_href' => '?pg=auronia',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Apply Bretonreach paints and heraldic finishes.', 'action' => 'Explore', 'href' => '?pg=br_paint_shack', 'color' => '#3b82f6', 'points' => country_map_rect_points(240, 470, 230, 170, $w, $h)],
                ['name' => 'Everything Store', 'description' => 'A famous all-purpose market in Avalore.', 'action' => 'Enter', 'href' => '?pg=br-everything-store', 'color' => '#f59e0b', 'points' => country_map_rect_points(680, 360, 260, 180, $w, $h)],
            ],
        ],
        'cc' => [
            'title' => 'Crescent Caliphate - Ansurah',
            'subtitle' => 'Moonlit Markets and Minarets',
            'image' => 'images/harmontide-cc.webp',
            'lore' => 'Ansurah is known for scholarship, caravans, and cosmopolitan bazaars under crescent towers.',
            'back_label' => 'Back to Orienthem',
            'back_href' => '?pg=orienthem',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Caliphate-inspired pigments and motifs.', 'action' => 'Explore', 'href' => '?pg=cc_paint_shack', 'color' => '#06b6d4', 'points' => country_map_rect_points(260, 500, 250, 180, $w, $h)],
            ],
        ],
        'esd' => [
            'title' => 'Eagle Serpent Dominion - Coatlxochi',
            'subtitle' => 'Feathers, Stone, and Sun',
            'image' => 'images/harmontide-esd.webp',
            'lore' => 'Coatlxochi blends ceremonial plazas with martial tradition and serpent-eagle iconography.',
            'back_label' => 'Back to Gulfbelt',
            'back_href' => '?pg=gulfbelt',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Color your companions with dominion palettes.', 'action' => 'Explore', 'href' => '?pg=esd_paint_shack', 'color' => '#ef4444', 'points' => country_map_rect_points(260, 470, 220, 170, $w, $h)],
            ],
        ],
        'esl' => [
            'title' => 'Eretz-Shalem League - Shalemdor',
            'subtitle' => 'Covenants and Citadels',
            'image' => 'images/harmontide-esl.webp',
            'lore' => 'Shalemdor is a covenant city where law, trade, and pilgrimage routes converge.',
            'back_label' => 'Back to Orienthem',
            'back_href' => '?pg=orienthem',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Paint your pets in Shalemdor style.', 'action' => 'Explore', 'href' => '?pg=esl_paint_shack', 'color' => '#a855f7', 'points' => country_map_rect_points(220, 520, 230, 160, $w, $h)],
            ],
        ],
        'fom' => [
            'title' => 'Frankenondermeer',
            'subtitle' => 'Dikes, windmills, and low-country canals',
            'image' => 'images/harmontide-fom.webp',
            'lore' => 'Frankenondermeer is Rheinland\'s low-country sibling: wind, canals, and flood-smart towns built on stubborn engineering.',
            'back_label' => 'Back to Rheingard',
            'back_href' => '?pg=rheinland',
            'areas' => [
                ['name' => 'Grachten Fishing', 'description' => 'Head to the canal event site for your daily cast.', 'action' => 'Go Fishing', 'href' => '?pg=fom-fishing', 'color' => '#22d3ee', 'points' => country_map_rect_points(670, 360, 280, 170, $w, $h)],
            ],
        ],
        'gc' => [
            'title' => 'Gran Columbia - Solvine',
            'subtitle' => 'City of Sun-Winds',
            'image' => 'images/harmontide-gc.webp',
            'lore' => 'Solvine is a bright trading city with strong civic plazas and highland-coast culture.',
            'back_label' => 'Back to Verdania',
            'back_href' => '?pg=verdania',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Gran Columbia regional colors and themes.', 'action' => 'Explore', 'href' => '?pg=gc_paint_shack', 'color' => '#f97316', 'points' => country_map_rect_points(250, 480, 230, 170, $w, $h)],
            ],
        ],
        'hammurabia' => [
            'title' => 'Hammurabia - Ziggurab',
            'subtitle' => 'Steps of Bronze Law',
            'image' => 'images/harmontide-hammurabia.webp',
            'lore' => 'Ziggurab rises in terraced districts where scribes and merchants keep meticulous records.',
            'back_label' => 'Back to Orienthem',
            'back_href' => '?pg=orienthem',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Regional paints from the workshops of Ziggurab.', 'action' => 'Explore', 'href' => '?pg=h_paint_shack', 'color' => '#84cc16', 'points' => country_map_rect_points(230, 510, 250, 170, $w, $h)],
            ],
        ],
        'ie' => [
            'title' => 'Itzam Empire - Itzankaan',
            'subtitle' => 'Jade Courts and Canals',
            'image' => 'images/harmontide-ie.webp',
            'lore' => 'Itzankaan pairs imperial ceremony with vibrant waterway life and star-calendar observances.',
            'back_label' => 'Back to Gulfbelt',
            'back_href' => '?pg=gulfbelt',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Empire colors for local creatures.', 'action' => 'Explore', 'href' => '?pg=ie_paint_shack', 'color' => '#14b8a6', 'points' => country_map_rect_points(230, 500, 245, 175, $w, $h)],
            ],
        ],
        'kemet' => [
            'title' => 'Kemet - Ankhmeru',
            'subtitle' => 'River Crown of Sand and Stone',
            'image' => 'images/harmontide-k.webp',
            'lore' => 'Ankhmeru is a Nile-like capital of monuments, scholars, and river-borne markets.',
            'back_label' => 'Back to Saharene',
            'back_href' => '?pg=saharene',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Kemet-inspired patterns and paint.', 'action' => 'Explore', 'href' => '?pg=k_paint_shack', 'color' => '#eab308', 'points' => country_map_rect_points(250, 480, 250, 170, $w, $h)],
                ['name' => 'Shelter Adventure', 'description' => 'Investigate strange happenings in the local shelter.', 'action' => 'Begin', 'href' => '?pg=k-adventure', 'color' => '#6366f1', 'points' => country_map_rect_points(720, 320, 250, 170, $w, $h)],
            ],
        ],
        'ldk' => [
            'title' => 'Lotus-Dragon Kingdom - Shenhedu',
            'subtitle' => 'Silk, Steel, and Lanterns',
            'image' => 'images/harmontide-ldk.webp',
            'lore' => 'Shenhedu balances imperial ritual, artisan guilds, and riverfront commerce.',
            'back_label' => 'Back to Dawnmarch',
            'back_href' => '?pg=dawnmarch',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Lotus-Dragon paints and motifs.', 'action' => 'Explore', 'href' => '?pg=ldk_paint_shack', 'color' => '#ec4899', 'points' => country_map_rect_points(250, 500, 230, 165, $w, $h)],
                ['name' => 'Breeding Pavilion', 'description' => 'Visit the breeding pavilion of Shenhedu.', 'action' => 'Visit', 'href' => '?pg=ldk_breeding', 'color' => '#06b6d4', 'points' => country_map_rect_points(700, 350, 250, 170, $w, $h)],
            ],
        ],
        'rheinland' => [
            'title' => 'Rheinland - Rheingard',
            'subtitle' => 'Iron Rivers, Ordered Streets',
            'image' => 'images/harmontide-rheingard.webp',
            'lore' => 'Rheingard is an industrial capital of strict timetables, workshops, and fortress bridges.',
            'back_label' => 'Back to Auronia',
            'back_href' => '?pg=auronia',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Rheinland palette and trims.', 'action' => 'Explore', 'href' => '?pg=rl_paint_shack', 'color' => '#64748b', 'points' => country_map_rect_points(235, 500, 250, 170, $w, $h)],
                ['name' => 'Frankenondermeer', 'description' => 'Visit the low-country canals of Frankenondermeer.', 'action' => 'Explore', 'href' => '?pg=fom', 'color' => '#14b8a6', 'points' => country_map_rect_points(620, 360, 280, 170, $w, $h)],
                ['name' => 'Fairy Fountain', 'description' => 'A moonlit cave where coins wake a shy river fairy.', 'action' => 'Enter the cave', 'href' => '?pg=rl_ff', 'color' => '#38bdf8', 'points' => country_map_rect_points(980, 250, 230, 150, $w, $h)],
            ],
        ],
        'rsc' => [
            'title' => 'Red Sun Commonwealth - Redwind',
            'subtitle' => 'Crimson Towers and Coin-Fairs',
            'image' => 'images/harmontide-rsc.webp',
            'lore' => 'Redwind is a proud commonwealth capital known for market games and showman culture.',
            'back_label' => 'Back to Uluru',
            'back_href' => '?pg=uluru',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Paint local pets with Redwind tones.', 'action' => 'Explore', 'href' => '?pg=rsc_paint_shack', 'color' => '#ef4444', 'points' => country_map_rect_points(250, 500, 240, 170, $w, $h)],
                ['name' => 'Wheel of Fate', 'description' => 'Spin for rewards in the city carnival district.', 'action' => 'Spin', 'href' => '?pg=rsc-wof', 'color' => '#22c55e', 'points' => country_map_rect_points(700, 330, 240, 170, $w, $h)],
            ],
        ],
        'rt' => [
            'title' => 'Rodinian Tsardom - Velesgrad',
            'subtitle' => 'Frostdomes and Gilded Courts',
            'image' => 'images/harmontide-rt.webp',
            'lore' => 'Velesgrad is a tsardom capital of grand avenues, onion domes, and winter festivals.',
            'back_label' => 'Back to Auronia',
            'back_href' => '?pg=auronia',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Tsardom-inspired coat colors.', 'action' => 'Explore', 'href' => '?pg=rt_paint_shack', 'color' => '#60a5fa', 'points' => country_map_rect_points(230, 500, 250, 170, $w, $h)],
            ],
        ],
        'sc' => [
            'title' => 'Sila Council - Qilaktuk',
            'subtitle' => 'Aurora Council at the Ice Edge',
            'image' => 'images/harmontide-sc.webp',
            'lore' => 'Qilaktuk is a northern council-city shaped by sea ice, seasonal travel, and shared stewardship.',
            'back_label' => 'Back to Tundria',
            'back_href' => '?pg=tundria',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Icy colors and arctic motifs.', 'action' => 'Explore', 'href' => '?pg=sc_paint_shack', 'color' => '#22d3ee', 'points' => country_map_rect_points(250, 500, 240, 170, $w, $h)],
            ],
        ],
        'sie' => [
            'title' => 'Sapa Inti Empire - Intirumi',
            'subtitle' => 'The High Sun Seat',
            'image' => 'images/harmontide-sie.webp',
            'lore' => 'Intirumi perches in mountain terraces where ritual roads and market stairs intertwine.',
            'back_label' => 'Back to Verdania',
            'back_href' => '?pg=verdania',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Sapa Inti pet paints.', 'action' => 'Explore', 'href' => '?pg=sie_paint_shack', 'color' => '#f59e0b', 'points' => country_map_rect_points(250, 500, 245, 165, $w, $h)],
            ],
        ],
        'srl' => [
            'title' => 'Spice Route League - Navakai',
            'subtitle' => 'Harbor of Sails and Spice',
            'image' => 'images/harmontide-srl.webp',
            'lore' => 'Navakai thrives on sea trade, spice guilds, and vibrant dockside markets.',
            'back_label' => 'Back to Moana Crown',
            'back_href' => '?pg=moana_crown',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Spice Route League custom colors.', 'action' => 'Explore', 'href' => '?pg=srl_paint_shack', 'color' => '#f43f5e', 'points' => country_map_rect_points(250, 500, 250, 170, $w, $h)],
            ],
        ],
        'stap' => [
            'title' => 'Sovereign Tribes - Turtlestar',
            'subtitle' => 'Circle Fires and Open Plains',
            'image' => 'images/harmontide-stap.webp',
            'lore' => 'Turtlestar is a plains capital organized around council circles, trade paths, and sky festivals.',
            'back_label' => 'Back to Borealia',
            'back_href' => '?pg=borealia',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Ancestral Plains region paints.', 'action' => 'Explore', 'href' => '?pg=stap_paint_shack', 'color' => '#f97316', 'points' => country_map_rect_points(250, 510, 250, 165, $w, $h)],
            ],
        ],
        'urb' => [
            'title' => 'United free Republic of Borealia - Meridian Arc',
            'subtitle' => 'Free Ports and Arc-Lights',
            'image' => 'images/harmontide-urb.webp',
            'lore' => 'Meridian Arc is a republican metropolis balancing trade rights, free districts, and civic debate.',
            'back_label' => 'Back to Borealia',
            'back_href' => '?pg=borealia',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Borealia regional paints.', 'action' => 'Explore', 'href' => '?pg=urb_paint_shack', 'color' => '#3b82f6', 'points' => country_map_rect_points(240, 500, 250, 170, $w, $h)],
                ['name' => 'Adventure District', 'description' => 'Take on adventures around Meridian Arc.', 'action' => 'Begin', 'href' => '?pg=urb-adventure', 'color' => '#a855f7', 'points' => country_map_rect_points(690, 340, 250, 170, $w, $h)],
                ['name' => 'Adventure District II', 'description' => 'Continue with higher stakes adventures.', 'action' => 'Continue', 'href' => '?pg=urb-adventure2', 'color' => '#fb7185', 'points' => country_map_rect_points(980, 260, 220, 150, $w, $h)],
            ],
        ],
        'xochimex' => [
            'title' => 'Xochimex - Xochival',
            'subtitle' => 'Festival Canals and Flowers',
            'image' => 'images/harmontide-xochimex.webp',
            'lore' => 'Xochival is a festival-rich city of flower markets, music, and floating neighborhoods.',
            'back_label' => 'Back to Gulfbelt',
            'back_href' => '?pg=gulfbelt',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Xochimex colorways and patterns.', 'action' => 'Explore', 'href' => '?pg=xm_paint_shack', 'color' => '#10b981', 'points' => country_map_rect_points(240, 500, 250, 165, $w, $h)],
            ],
        ],
        'yamanokubo' => [
            'title' => 'Yamanokubo - Amatera',
            'subtitle' => 'Neon Lanes and Shrine Hills',
            'image' => 'images/harmontide-yamanokubo.webp',
            'lore' => 'Amatera blends old shrine hills with high-energy nightlife and story-rich backstreets.',
            'back_label' => 'Back to Dawnmarch',
            'back_href' => '?pg=dawnmarch',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Yamanokubo painting station.', 'action' => 'Explore', 'href' => '?pg=ynk_paint_shack', 'color' => '#8b5cf6', 'points' => country_map_rect_points(250, 500, 250, 170, $w, $h)],
                ['name' => 'Ramen District', 'description' => 'Warm up at the city ramen hall.', 'action' => 'Eat', 'href' => '?pg=ynk-ramen', 'color' => '#f97316', 'points' => country_map_rect_points(700, 350, 230, 170, $w, $h)],
                ['name' => 'Adventure Gate', 'description' => 'Begin a local adventure route.', 'action' => 'Begin', 'href' => '?pg=ynk-adventure', 'color' => '#22c55e', 'points' => country_map_rect_points(980, 260, 220, 150, $w, $h)],
            ],
        ],
        'yn' => [
            'title' => 'Yara Nations - Warraluma',
            'subtitle' => 'Where River Meets Song',
            'image' => 'images/harmontide-yn.webp',
            'lore' => 'Warraluma stands between river and escarpment, with Country-led stewardship and gathering grounds.',
            'back_label' => 'Back to Uluru',
            'back_href' => '?pg=uluru',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Yara Nations custom paint station.', 'action' => 'Explore', 'href' => '?pg=yn_paint_shack', 'color' => '#06b6d4', 'points' => country_map_rect_points(250, 500, 250, 170, $w, $h)],
            ],
        ],
    ];

    if (!isset($configs[$slug])) {
        return null;
    }

    return $configs[$slug] + ['width' => $w, 'height' => $h];
}
