<?php
require_login();
require_once __DIR__ . '/../lib/country_interactive_map.php';

$worldWidth = 1680;
$worldHeight = 1038;

$worldAreas = [
    [
        'name' => 'Auronia',
        'href' => 'index.php?pg=auronia',
        'coords' => '470,288,456,520,410,548,370,517,329,554,299,590,268,640,272,676,257,705,186,713,168,760,151,781,111,766,30,742,2,609,2,199,257,3,534,10',
        'color' => '#ffd166',
        'description' => 'A broad western land where old imperial roads still knit market towns and watchforts together.',
    ],
    [
        'name' => 'Verdania',
        'href' => 'index.php?pg=verdania',
        'coords' => '410,601,143,809,38,769,7,845,14,998,563,1023,591,823,591,771,612,729,600,646,545,618',
        'color' => '#6ee7b7',
        'description' => 'Green lowlands and old-growth reaches in the southwest, known for rain-fed orchards and marsh songs.',
    ],
    [
        'name' => 'Dawnmarch',
        'href' => 'index.php?pg=dawnmarch',
        'coords' => '778,606,750,622,729,619,716,630,670,649,630,667,624,714,600,789,581,1007,630,1034,1123,1025,1118,970,985,768,927,624',
        'color' => '#fca5a5',
        'description' => 'A hard frontier of cliffs, red marches, and oath-stone keeps across the south-central world.',
    ],
    [
        'name' => 'Gulfbelt',
        'href' => 'index.php?pg=gulfbelt',
        'coords' => '783,590,750,616,731,613,701,630,637,650,577,616,467,582,462,533,498,248,574,166,663,153,682,301,749,438',
        'color' => '#93c5fd',
        'description' => 'A tidebound nexus of bays and sea roads where half the world\'s cargo changes hands.',
    ],
    [
        'name' => 'Orienthem',
        'href' => 'index.php?pg=orienthem',
        'coords' => '780,481,692,288,707,144,853,92,983,175,1054,328,1072,395,1063,506,857,533',
        'color' => '#c4b5fd',
        'description' => 'Mountain passes, shrine roads, and storied cities shape this vibrant eastern region.',
    ],
    [
        'name' => 'Borealia',
        'href' => 'index.php?pg=borealia',
        'coords' => '1387,797,1347,794,1310,793,1286,784,1273,778,1221,783,1179,750,1190,728,1185,713,1157,702,1143,689,1133,661,1133,610,1074,560,967,575,988,732,1143,936,1221,1016,1277,1025,1420,1016,1442,968,1445,921',
        'color' => '#67e8f9',
        'description' => 'Far-northern reaches of frost and wind-carved stone, where summer lasts only a whisper.',
    ],
    [
        'name' => 'Uluru',
        'href' => 'index.php?pg=uluru',
        'coords' => '1381,793,1311,784,1271,773,1224,775,1197,757,1197,720,1184,705,1154,690,1146,663,1152,604,1182,605,1219,542,1366,553,1384,592,1356,644,1369,665,1403,704,1381,744',
        'color' => '#fdba74',
        'description' => 'Dry heartlands and red-earth routes crossing the inner continent under immense desert skies.',
    ],
    [
        'name' => 'Moana Crown',
        'href' => 'index.php?pg=moana_crown',
        'coords' => '1405,582,1369,624,1391,689,1425,710,1504,983,1679,901,1651,892,1676,263,1532,131,1440,249',
        'color' => '#86efac',
        'description' => 'Island chains and ocean kingdoms circling the southeastern seas, guided by reef-lights and star lore.',
    ],
    [
        'name' => 'Saharene',
        'href' => 'index.php?pg=saharene',
        'coords' => '1341,523,1200,538,1178,598,1126,597,1069,532,1093,370,1050,258,1176,169,1264,119,1354,198,1400,272,1385,376',
        'color' => '#f9a8d4',
        'description' => 'Desert winds, ancient dunes, and caravan oathways define this realm of hidden wells.',
    ],
    [
        'name' => 'Tundria',
        'href' => 'index.php?pg=tundria',
        'coords' => '1074,212,857,53,893,10,1231,6,1234,128',
        'color' => '#e5e7eb',
        'description' => 'A glacial crown where snowfields and icebound peaks guard legends older than written calendars.',
    ],
];

$areas = [];
foreach ($worldAreas as $area) {
    $coords = array_map('intval', explode(',', $area['coords']));
    $areas[] = [
        'name' => $area['name'],
        'description' => $area['description'],
        'action' => 'Open region',
        'href' => $area['href'],
        'color' => $area['color'],
        'points' => country_map_to_percent_points($coords, $worldWidth, $worldHeight),
    ];
}

render_country_interactive_map([
    'title' => 'World Map',
    'subtitle' => 'Explore each region of Harmontide from a scalable, interactive world view.',
    'image' => 'images/harmontide-smol.png',
    'lore' => 'A living chart of Harmontide, where each continent keeps its own memory, customs, and age-old paths.',
    'lore_sections' => [
        [
            'title' => 'A Cartographer\'s Tidbit',
            'html' => '<p>Harmontide is said to have been shaped by ten ancient currents, each leaving behind a continent with its own temper: roadbound westlands, singing marshes, oath-marked frontiers, shrine mountains, frost crowns, and sea-ringed island realms. Sailors still say the map is alive - coastlines look the same, but every age redraws what each land means.</p>',
        ],
    ],
    'width' => $worldWidth,
    'height' => $worldHeight,
    'particle_count' => 20,
    'show_panel' => false,
    'areas' => $areas,
]);
