<?php

function get_city_definitions(): array {
    static $definitions = null;
    if ($definitions !== null) {
        return $definitions;
    }

    $definitions = [];
    $path = __DIR__.'/../data-readonly/city-names.txt';
    if (!is_readable($path)) {
        return $definitions;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        if (!preg_match('/^\s*(.+?)\s*-\s*(.+?)(?:\s*\((.*)\))?\s*$/', $line, $matches)) {
            continue;
        }
        $nation = trim($matches[1]);
        $city = trim($matches[2]);
        $description = isset($matches[3]) ? trim($matches[3]) : '';
        $definitions[$nation] = [
            'city' => $city,
            'description' => $description,
        ];
    }

    return $definitions;
}

function get_page_location_map(): array {
    return [
        'aa' => 'Aegia Aeterna',
        'aa-adventure' => 'Aegia Aeterna',
        'aa-library' => 'Aegia Aeterna',
        'aa-pizza' => 'Aegia Aeterna',
        'aa_paint_shack' => 'Aegia Aeterna',
        'aa-wof' => 'Aegia Aeterna',
        'baharamandal' => 'Baharamandal',
        'bm-market' => 'Baharamandal',
        'bm_paint_shack' => 'Baharamandal',
        'bretonreach' => 'Bretonreach',
        'br-everything-store' => 'Bretonreach',
        'br_paint_shack' => 'Bretonreach',
        'cc' => 'Crescent Caliphate',
        'cc-souq' => 'Crescent Caliphate',
        'cc_paint_shack' => 'Crescent Caliphate',
        'esd' => 'Eagle Serpent Dominion',
        'esd-feather-flint' => 'Eagle Serpent Dominion',
        'esd_paint_shack' => 'Eagle Serpent Dominion',
        'esl' => 'Eretz-Shalem League',
        'esl-olive-lamp' => 'Eretz-Shalem League',
        'esl_paint_shack' => 'Eretz-Shalem League',
        'gc' => 'Gran Columbia',
        'gc-plaza-kiosk' => 'Gran Columbia',
        'gc_paint_shack' => 'Gran Columbia',
        'hammurabia' => 'Hammurabia',
        'h-ledger-house' => 'Hammurabia',
        'h_paint_shack' => 'Hammurabia',
        'ie' => 'Itzam Empire',
        'ie-canopy-relic' => 'Itzam Empire',
        'ie_paint_shack' => 'Itzam Empire',
        'kemet' => 'Kemet',
        'k-bazaar-tent' => 'Kemet',
        'k-bazaar-goods' => 'Kemet',
        'k_paint_shack' => 'Kemet',
        'cups-and-balls' => 'Kemet',
        'ldk' => 'Lotus-Dragon Kingdom',
        'ldk-tea-trinkets' => 'Lotus-Dragon Kingdom',
        'ldk_paint_shack' => 'Lotus-Dragon Kingdom',
        'nornheim' => 'Nornheim',
        'nh-frostmarket' => 'Nornheim',
        'nh_paint_shack' => 'Nornheim',
        'aeonstep' => 'Aeonstep Plateau',
        'rsc' => 'Red Sun Commonwealth',
        'rsc-roadhouse' => 'Red Sun Commonwealth',
        'rsc-wof' => 'Red Sun Commonwealth',
        'rsc_paint_shack' => 'Red Sun Commonwealth',
        'rheinland' => 'Rheinland',
        'fom' => 'Rheinland',
        'fom-fishing' => 'Rheinland',
        'fom-lockside-shop' => 'Rheinland',
        'rl_paint_shack' => 'Rheinland',
        'rt' => 'Rodinian Tsardom',
        'rt-winter-pantry' => 'Rodinian Tsardom',
        'rt_paint_shack' => 'Rodinian Tsardom',
        'sie' => 'Sapa Inti Empire',
        'sie-sun-terrace' => 'Sapa Inti Empire',
        'sie_paint_shack' => 'Sapa Inti Empire',
        'sc' => 'Sila Council',
        'sc-ice-cache' => 'Sila Council',
        'sc_paint_shack' => 'Sila Council',
        'stap' => 'Sovereign Tribes of the Ancestral Plains',
        'stap-trading-blanket' => 'Sovereign Tribes of the Ancestral Plains',
        'stap_paint_shack' => 'Sovereign Tribes of the Ancestral Plains',
        'srl' => 'Spice Route League',
        'srl-spice-dock' => 'Spice Route League',
        'pelagora' => 'Pelagora',
        'pelagora-shop' => 'Pelagora',
        'pelagora-fishing' => 'Pelagora',
        'pelagora-library' => 'Pelagora',
        'pelagora-divers' => 'Pelagora',
        'srl_paint_shack' => 'Spice Route League',
        'urb' => 'United free Republic of Borealia',
        'urb-adventure' => 'United free Republic of Borealia',
        'urb-adventure2' => 'United free Republic of Borealia',
        'urb-corner-mart' => 'United free Republic of Borealia',
        'urb_paint_shack' => 'United free Republic of Borealia',
        'stillwater-hollow' => 'Stillwater Hollow',
        'stcr-shop' => 'Stillwater Hollow',
        'xochimex' => 'Xochimex',
        'xm-flower-market' => 'Xochimex',
        'xm_paint_shack' => 'Xochimex',
        'yamanokubo' => 'Yamanokubo',
        'ynk-adventure' => 'Yamanokubo',
        'ynk-adventure2' => 'Yamanokubo',
        'ynk_paint_shack' => 'Yamanokubo',
        'ynk-ramen' => 'Yamanokubo',
        'yn' => 'Yara Nations',
        'yn-keeping-place-shop' => 'Yara Nations',
        'yn_paint_shack' => 'Yara Nations',
    ];
}


function get_page_parent_map_map(): array {
    return [
        'aa' => 'aa',
        'aa-adventure' => 'aa',
        'aa-library' => 'aa',
        'aa-pizza' => 'aa',
        'aa_paint_shack' => 'aa',
        'aa-wof' => 'aa',
        'aest-shop' => 'aeonstep',
        'aeonstep' => 'aeonstep',
        'baharamandal' => 'baharamandal',
        'bm-market' => 'baharamandal',
        'bm_paint_shack' => 'baharamandal',
        'bm_pt' => 'baharamandal',
        'bretonreach' => 'bretonreach',
        'br-everything-store' => 'bretonreach',
        'br_paint_shack' => 'bretonreach',
        'cc' => 'cc',
        'cc-souq' => 'cc',
        'cc_paint_shack' => 'cc',
        'esd' => 'esd',
        'esd-feather-flint' => 'esd',
        'esd_paint_shack' => 'esd',
        'esl' => 'esl',
        'esl-olive-lamp' => 'esl',
        'esl_paint_shack' => 'esl',
        'fom' => 'fom',
        'fom-fishing' => 'fom',
        'fom-lockside-shop' => 'fom',
        'gc' => 'gc',
        'gc-plaza-kiosk' => 'gc',
        'gc_paint_shack' => 'gc',
        'hammurabia' => 'hammurabia',
        'h-ledger-house' => 'hammurabia',
        'h_paint_shack' => 'hammurabia',
        'ie' => 'ie',
        'ie-canopy-relic' => 'ie',
        'ie_paint_shack' => 'ie',
        'kemet' => 'kemet',
        'k-adventure' => 'kemet',
        'k-bazaar-tent' => 'kemet',
        'k-bazaar-goods' => 'k-bazaar-tent',
        'k_paint_shack' => 'kemet',
        'k_shelter' => 'kemet',
        'cups-and-balls' => 'k-bazaar-tent',
        'ldk' => 'ldk',
        'ldk_breeding' => 'ldk',
        'ldk-tea-trinkets' => 'ldk',
        'ldk_paint_shack' => 'ldk',
        'nornheim' => 'nornheim',
        'nh-frostmarket' => 'nornheim',
        'nh_paint_shack' => 'nornheim',
        'pelagora' => 'pelagora',
        'pelagora-shop' => 'pelagora',
        'pelagora-fishing' => 'pelagora',
        'pelagora-library' => 'pelagora',
        'pelagora-divers' => 'pelagora',
        'rheinland' => 'rheinland',
        'rl_ff' => 'rheinland',
        'rl_paint_shack' => 'rheinland',
        'rsc' => 'rsc',
        'rsc-roadhouse' => 'rsc',
        'rsc-wof' => 'rsc',
        'rsc_paint_shack' => 'rsc',
        'rt' => 'rt',
        'rt-winter-pantry' => 'rt',
        'rt_paint_shack' => 'rt',
        'sc' => 'sc',
        'sc-ice-cache' => 'sc',
        'sc_paint_shack' => 'sc',
        'sie' => 'sie',
        'sie-sun-terrace' => 'sie',
        'sie_paint_shack' => 'sie',
        'srl' => 'srl',
        'srl-spice-dock' => 'srl',
        'srl_paint_shack' => 'srl',
        'stap' => 'stap',
        'stap-trading-blanket' => 'stap',
        'stap_paint_shack' => 'stap',
        'urb' => 'urb',
        'urb-adventure' => 'urb',
        'urb-adventure2' => 'urb',
        'urb-corner-mart' => 'urb',
        'urb_paint_shack' => 'urb',
        'stillwater-hollow' => 'urb',
        'stcr-shop' => 'stillwater-hollow',
        'xm-flower-market' => 'xochimex',
        'xm_paint_shack' => 'xochimex',
        'xochimex' => 'xochimex',
        'yamanokubo' => 'yamanokubo',
        'yn' => 'yn',
        'yn-keeping-place-shop' => 'yn',
        'yn_paint_shack' => 'yn',
        'ynk-adventure' => 'yamanokubo',
        'ynk-adventure2' => 'yamanokubo',
        'ynk-ramen' => 'yamanokubo',
        'ynk_paint_shack' => 'yamanokubo',
    ];
}

function get_page_display_name(string $pg, ?array $location = null): string {
    $labels = [
        'k-bazaar-tent' => 'Kemet Bazaar Tent',
        'k-bazaar-goods' => 'Bazaar Goods',
        'cups-and-balls' => 'Cups and Balls',
    ];

    if (isset($labels[$pg])) {
        return $labels[$pg];
    }

    return $location['nation'] ?? $pg;
}

function get_page_back_to_country_map(string $pg): ?array {
    $pageParents = get_page_parent_map_map();
    if (!isset($pageParents[$pg])) {
        return null;
    }

    $parentPage = $pageParents[$pg];
    if ($parentPage === $pg) {
        return null;
    }

    $parentLocation = get_page_location($parentPage);
    if ($parentLocation === null) {
        return null;
    }

    $parentLabel = get_page_display_name($parentPage, $parentLocation);

    return [
        'href' => '?pg=' . $parentPage,
        'label' => '← Back to ' . $parentLabel,
        'nation' => $parentLocation['nation'],
        'page' => $parentPage,
    ];
}

function get_page_location(string $pg): ?array {
    $pageToNation = get_page_location_map();
    if (!isset($pageToNation[$pg])) {
        return null;
    }

    $nation = $pageToNation[$pg];
    $cities = get_city_definitions();
    $cityDetails = $cities[$nation] ?? null;
    $cityName = $cityDetails['city'] ?? '';

    return [
        'nation' => $nation,
        'city' => $cityName,
        'description' => $cityDetails['description'] ?? '',
        'key' => strtolower($nation.'|'.$cityName),
    ];
}

function load_speech_dialogues(): array {
    $path = __DIR__.'/../data/speech.json';
    if (!is_readable($path)) {
        return [];
    }

    $json = file_get_contents($path);
    if ($json === false) {
        return [];
    }

    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}
