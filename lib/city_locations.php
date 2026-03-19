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
        'bm_paint_shack' => 'Baharamandal',
        'bretonreach' => 'Bretonreach',
        'br-everything-store' => 'Bretonreach',
        'br_paint_shack' => 'Bretonreach',
        'cc' => 'Crescent Caliphate',
        'cc_paint_shack' => 'Crescent Caliphate',
        'esd' => 'Eagle Serpent Dominion',
        'esd_paint_shack' => 'Eagle Serpent Dominion',
        'esl' => 'Eretz-Shalem League',
        'esl_paint_shack' => 'Eretz-Shalem League',
        'gc' => 'Gran Columbia',
        'gc_paint_shack' => 'Gran Columbia',
        'hammurabia' => 'Hammurabia',
        'h_paint_shack' => 'Hammurabia',
        'ie' => 'Itzam Empire',
        'ie_paint_shack' => 'Itzam Empire',
        'kemet' => 'Kemet',
        'k_paint_shack' => 'Kemet',
        'ldk' => 'Lotus-Dragon Kingdom',
        'ldk_paint_shack' => 'Lotus-Dragon Kingdom',
        'nornheim' => 'Nornheim',
        'nh_paint_shack' => 'Nornheim',
        'aeonstep' => 'Aeonstep Plateau',
        'rsc' => 'Red Sun Commonwealth',
        'rsc-wof' => 'Red Sun Commonwealth',
        'rsc_paint_shack' => 'Red Sun Commonwealth',
        'rheinland' => 'Rheinland',
        'fom' => 'Rheinland',
        'fom-fishing' => 'Rheinland',
        'rl_paint_shack' => 'Rheinland',
        'rt' => 'Rodinian Tsardom',
        'rt_paint_shack' => 'Rodinian Tsardom',
        'sie' => 'Sapa Inti Empire',
        'sie_paint_shack' => 'Sapa Inti Empire',
        'sc' => 'Sila Council',
        'sc_paint_shack' => 'Sila Council',
        'stap' => 'Sovereign Tribes of the Ancestral Plains',
        'stap_paint_shack' => 'Sovereign Tribes of the Ancestral Plains',
        'srl' => 'Spice Route League',
        'pelagora' => 'Spice Route League',
        'srl_paint_shack' => 'Spice Route League',
        'urb' => 'United free Republic of Borealia',
        'urb_paint_shack' => 'United free Republic of Borealia',
        'xochimex' => 'Xochimex',
        'xm_paint_shack' => 'Xochimex',
        'yamanokubo' => 'Yamanokubo',
        'ynk-adventure' => 'Yamanokubo',
        'ynk-adventure2' => 'Yamanokubo',
        'ynk_paint_shack' => 'Yamanokubo',
        'ynk-ramen' => 'Yamanokubo',
        'yn' => 'Yara Nations',
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
        'bm_paint_shack' => 'baharamandal',
        'bm_pt' => 'baharamandal',
        'bretonreach' => 'bretonreach',
        'br-everything-store' => 'bretonreach',
        'br_paint_shack' => 'bretonreach',
        'cc' => 'cc',
        'cc_paint_shack' => 'cc',
        'esd' => 'esd',
        'esd_paint_shack' => 'esd',
        'esl' => 'esl',
        'esl_paint_shack' => 'esl',
        'fom' => 'fom',
        'fom-fishing' => 'fom',
        'gc' => 'gc',
        'gc_paint_shack' => 'gc',
        'hammurabia' => 'hammurabia',
        'h_paint_shack' => 'hammurabia',
        'ie' => 'ie',
        'ie_paint_shack' => 'ie',
        'kemet' => 'kemet',
        'k-adventure' => 'kemet',
        'k_paint_shack' => 'kemet',
        'k_shelter' => 'kemet',
        'ldk' => 'ldk',
        'ldk_breeding' => 'ldk',
        'ldk_paint_shack' => 'ldk',
        'nornheim' => 'nornheim',
        'nh_paint_shack' => 'nornheim',
        'pelagora' => 'pelagora',
        'rheinland' => 'rheinland',
        'rl_ff' => 'rheinland',
        'rl_paint_shack' => 'rheinland',
        'rsc' => 'rsc',
        'rsc-wof' => 'rsc',
        'rsc_paint_shack' => 'rsc',
        'rt' => 'rt',
        'rt_paint_shack' => 'rt',
        'sc' => 'sc',
        'sc_paint_shack' => 'sc',
        'sie' => 'sie',
        'sie_paint_shack' => 'sie',
        'srl' => 'srl',
        'srl_paint_shack' => 'srl',
        'stap' => 'stap',
        'stap_paint_shack' => 'stap',
        'urb' => 'urb',
        'urb-adventure' => 'urb',
        'urb-adventure2' => 'urb',
        'urb_paint_shack' => 'urb',
        'xm_paint_shack' => 'xochimex',
        'xochimex' => 'xochimex',
        'yamanokubo' => 'yamanokubo',
        'yn' => 'yn',
        'yn_paint_shack' => 'yn',
        'ynk-adventure' => 'yamanokubo',
        'ynk-adventure2' => 'yamanokubo',
        'ynk-ramen' => 'yamanokubo',
        'ynk_paint_shack' => 'yamanokubo',
    ];
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

    return [
        'href' => '?pg=' . $parentPage,
        'label' => '← Back to ' . $parentLocation['nation'],
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
