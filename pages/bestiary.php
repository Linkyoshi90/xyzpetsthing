<?php
/**
 * Visual Bestiary/Encyclopedia - Standalone PHP
 * 
 * Features:
 * - Book UI with open/close animations
 * - Country-based index from country-names.txt
 * - Creature data from database (pet_species table)
 * - Page flip transitions
 * - Semi-transparent navigation arrows
 * - Scrollable content with text formatting
 */

// ============================================================================
// CONFIGURATION
// ============================================================================

// Try multiple possible locations for the country file
define('COUNTRY_FILE_PATHS', [
    __DIR__ . '/../data-readonly/country-names.txt',
    __DIR__ . '/../data-readonly/country_names.txt',
    __DIR__ . '/../../data-readonly/country-names.txt',
    __DIR__ . '/data-readonly/country-names.txt',
    dirname(__DIR__) . '/data-readonly/country-names.txt',
]);

define('CREATURE_DATA_PATHS', [
    __DIR__ . '/../data/creature_encyclopedia.json',
    __DIR__ . '/../../data/creature_encyclopedia.json',
    dirname(__DIR__) . '/data/creature_encyclopedia.json',
    __DIR__ . '/../data-readonly/creature_encyclopedia.json',
    __DIR__ . '/../../data-readonly/creature_encyclopedia.json',
    dirname(__DIR__) . '/data-readonly/creature_encyclopedia.json',
]);

// Include db.php if it exists (for embedded mode)
$dbPath = __DIR__ . '/../db.php';
if (is_file($dbPath)) {
    require_once $dbPath;
}

// ============================================================================
// DATABASE HELPER
// ============================================================================

/**
 * Check if q() function exists (from db.php)
 */
function hasDb(): bool {
    return function_exists('q');
}

/**
 * Backward-compatible helper for PHP < 8 where str_starts_with() is unavailable.
 */
function startsWith(string $haystack, string $needle): bool {
    if (function_exists('str_starts_with')) {
        return str_starts_with($haystack, $needle);
    }

    if ($needle === '') {
        return true;
    }

    return strpos($haystack, $needle) === 0;
}

/**
 * Get database connection (fallback if q() doesn't exist)
 */
function getDb(): ?PDO {
    static $db = null;
    
    if ($db !== null) {
        return $db;
    }
    
    // Try to find the database file
    $dbPaths = [
        __DIR__ . '/../db/custom.db',
        __DIR__ . '/../../db/custom.db',
        dirname(__DIR__) . '/db/custom.db',
    ];
    
    foreach ($dbPaths as $dbPath) {
        if (is_file($dbPath)) {
            try {
                $db = new PDO('sqlite:' . $dbPath);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                return $db;
            } catch (PDOException $e) {
                continue;
            }
        }
    }
    
    return null;
}

/**
 * Execute a query (wrapper for q() or direct PDO)
 */
function executeQuery(string $sql, array $params = []): ?array {
    // Use q() from db.php if available
    if (function_exists('q')) {
        try {
            return q($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    // Fallback to direct PDO
    $db = getDb();
    if (!$db) {
        return null;
    }
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Check if a table exists (SQLite compatible)
 */
function tableExists(string $tableName): bool {
    if (function_exists('db')) {
        $pdo = db();
        if ($pdo instanceof PDO) {
            try {
                $stmt = $pdo->query('SHOW TABLES LIKE ' . $pdo->quote($tableName));
                return $stmt !== false && $stmt->fetch() !== false;
            } catch (PDOException $e) {
                return false;
            }
        }
    }

    $db = getDb();
    if (!$db) {
        return false;
    }

    try {
        $result = $db->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name=" . $db->quote($tableName)
        );
        return $result && $result->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

// ============================================================================
// DATA LOADING
// ============================================================================

/**
 * Load country names from file
 */
function loadCountries(): array {
    $countries = [];
    
    // Try each possible path
    foreach (COUNTRY_FILE_PATHS as $filePath) {
        if (is_file($filePath) && is_readable($filePath)) {
            $content = file_get_contents($filePath);
            if ($content !== false) {
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    $line = trim($line);
                    // Skip empty lines and comments
                    if ($line !== '' && !startsWith($line, '#')) {
                        $countries[] = $line;
                    }
                }
                
                // If we found countries, break
                if (!empty($countries)) {
                    break;
                }
            }
        }
    }
    
    // Fallback: try to get regions from database
    if (empty($countries)) {
        $regions = getRegions();
        foreach ($regions as $region) {
            if (!empty($region['region_name'])) {
                $countries[] = trim((string)$region['region_name']);
            }
        }
    }
    
    // Final fallback: hardcoded list
    if (empty($countries)) {
        $countries = [
            'Aegia Aeterna',
            'Aeonstep Plateau',
            'Baharamandal',
            'Bretonreach',
            'Crescent Caliphate',
            'Eagle Serpent Dominion',
            'Eretz-Shalem League',
            'Gran Columbia',
            'Hammurabia',
            'Itzam Empire',
            'Kemet',
            'Lotus-Dragon Kingdom',
            'Nornheim',
            'Red Sun Commonwealth',
            'Rheinland',
            'Rodinian Tsardom',
            'Sapa Inti Empire',
            'Sila Council',
            'Sovereign Tribes of the Ancestral Plains',
            'Spice Route League',
            'United free Republic of Borealia',
            'Xochimex',
            'Yamanokubo',
            'Yara Nations',
        ];
    }
    
    // Clean and deduplicate
    $countries = array_values(array_unique(array_filter($countries, fn($c) => trim($c) !== '')));
    sort($countries, SORT_NATURAL | SORT_FLAG_CASE);
    
    return $countries;
}

/**
 * Load creature encyclopedia JSON data
 */
function loadCreatureData(): array {
    $merged = [];

    foreach (CREATURE_DATA_PATHS as $filePath) {
        if (is_file($filePath) && is_readable($filePath)) {
            $content = file_get_contents($filePath);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $key => $creature) {
                        if (!is_array($creature)) {
                            continue;
                        }

                        $name = trim((string)($creature['name'] ?? $key));
                        if ($name === '') {
                            continue;
                        }

                        if (!isset($merged[$name])) {
                            $merged[$name] = [];
                        }

                        // Merge so later files can fill blanks from earlier files while
                        // preserving richer values when already present.
                        foreach ($creature as $field => $value) {
                            if (!array_key_exists($field, $merged[$name]) || $merged[$name][$field] === null || $merged[$name][$field] === '') {
                                $merged[$name][$field] = $value;
                                continue;
                            }

                            if (
                                is_string($value)
                                && is_string($merged[$name][$field])
                                && strlen(trim($value)) > strlen(trim((string)$merged[$name][$field]))
                            ) {
                                $merged[$name][$field] = $value;
                            }

                            if (is_array($value) && empty($merged[$name][$field])) {
                                $merged[$name][$field] = $value;
                            }
                        }

                        $merged[$name]['name'] = $name;
                    }
                }
            }
        }
    }

    return $merged;
}

/**
 * Normalize encyclopedia placeholder values so DB-backed data can be used.
 */
function normalizeEncyclopediaField($value, $fallback) {
    if ($value === null) {
        return $fallback;
    }

    if (is_string($value)) {
        $trimmed = trim($value);
        if ($trimmed === '' || strcasecmp($trimmed, 'unknown') === 0 || strpos($trimmed, 'TODO:') === 0) {
            return $fallback;
        }
        return $trimmed;
    }

    return $value;
}

/**
 * Resolve the bestiary portrait path using the female blue default.
 */
function getCreatureImagePath(string $speciesName): string {
    $speciesSlug = strtolower((string)preg_replace('/[^a-z0-9]+/i', '_', $speciesName));
    $speciesSlug = trim($speciesSlug, '_');

    $candidates = [
        "images/{$speciesSlug}_f_blue.webp",
        "images/{$speciesSlug}_f_blue.png",
        "images/{$speciesSlug}_f_blue.jpg",
        'images/tengu_f_blue.png',
    ];

    foreach ($candidates as $path) {
        if (is_file(__DIR__ . '/../' . $path)) {
            return $path;
        }
    }

    return 'images/tengu_f_blue.png';
}

/**
 * Get regions from database
 */
function getRegions(): array {
    // Check if regions table exists
    if (!tableExists('regions')) {
        return [];
    }
    
    return executeQuery("SELECT region_id, region_name FROM regions ORDER BY region_name") ?? [];
}

/**
 * Get creatures grouped by region
 */
function getCreaturesByRegion(): array {
    $creatureData = loadCreatureData();

    // Check if required tables exist
    $creatures = [];
    if (tableExists('pet_species') && tableExists('regions')) {
        $creatures = executeQuery(" 
            SELECT 
                ps.species_id,
                ps.species_name,
                ps.base_hp,
                ps.base_atk,
                ps.base_def,
                ps.base_init,
                r.region_name,
                r.region_id
            FROM pet_species ps
            INNER JOIN regions r ON r.region_id = ps.region_id
            ORDER BY r.region_name, ps.species_name
        ") ?? [];
    }

    // Fallback when DB is unavailable: group JSON entries directly.
    if (empty($creatures) && !empty($creatureData)) {
        foreach ($creatureData as $name => $entry) {
            $creatures[] = [
                'species_id' => crc32($name),
                'species_name' => $name,
                'base_hp' => (int)($entry['stats']['hp'] ?? 0),
                'base_atk' => (int)($entry['stats']['atk'] ?? 0),
                'base_def' => (int)($entry['stats']['def'] ?? 0),
                'base_init' => (int)($entry['stats']['init'] ?? 0),
                'region_name' => normalizeEncyclopediaField($entry['region'] ?? null, 'Unknown'),
                'region_id' => 0,
            ];
        }
    }

    // DB can be incomplete for encyclopedia-only species, so always merge JSON
    // entries that are missing from DB results.
    $knownSpecies = [];
    foreach ($creatures as $row) {
        $name = trim((string)($row['species_name'] ?? ''));
        if ($name !== '') {
            $knownSpecies[strtolower($name)] = true;
        }
    }

    foreach ($creatureData as $name => $entry) {
        $normalized = strtolower(trim((string)$name));
        if ($normalized === '' || isset($knownSpecies[$normalized])) {
            continue;
        }

        $creatures[] = [
            'species_id' => crc32($name),
            'species_name' => $name,
            'base_hp' => (int)($entry['stats']['hp'] ?? 0),
            'base_atk' => (int)($entry['stats']['atk'] ?? 0),
            'base_def' => (int)($entry['stats']['def'] ?? 0),
            'base_init' => (int)($entry['stats']['init'] ?? 0),
            'region_name' => normalizeEncyclopediaField($entry['region'] ?? null, 'Unknown'),
            'region_id' => 0,
        ];
    }

    if (empty($creatures)) {
        return [];
    }
    
    $grouped = [];
    foreach ($creatures as $creature) {
        $region = $creature['region_name'] ?: 'Unknown';
        
        // Merge with JSON data if available
        $jsonData = $creatureData[$creature['species_name']] ?? [];
        $colors = array_values(array_filter(
            is_array($jsonData['colors'] ?? null) ? $jsonData['colors'] : ['Blue'],
            fn($color) => trim((string)$color) !== ''
        ));
        
        $grouped[$region][] = [
            'id' => $creature['species_id'],
            'name' => $creature['species_name'],
            'stats' => [
                'hp' => (int)(($jsonData['stats']['hp'] ?? 0) > 0 ? $jsonData['stats']['hp'] : $creature['base_hp']),
                'atk' => (int)(($jsonData['stats']['atk'] ?? 0) > 0 ? $jsonData['stats']['atk'] : $creature['base_atk']),
                'def' => (int)(($jsonData['stats']['def'] ?? 0) > 0 ? $jsonData['stats']['def'] : $creature['base_def']),
                'init' => (int)(($jsonData['stats']['init'] ?? 0) > 0 ? $jsonData['stats']['init'] : $creature['base_init']),
            ],
            'colors' => !empty($colors) ? $colors : ['Blue'],
            'image' => getCreatureImagePath((string)$creature['species_name']),
            'description' => normalizeEncyclopediaField($jsonData['description'] ?? null, 'Details are being cataloged by the library staff.'),
            'rarity' => normalizeEncyclopediaField($jsonData['rarity'] ?? null, 'Common'),
            'size' => normalizeEncyclopediaField($jsonData['size'] ?? null, 'Medium'),
            'diet' => normalizeEncyclopediaField($jsonData['diet'] ?? null, 'Omnivore'),
            'region' => $region,
        ];
    }

    foreach ($grouped as &$regionCreatures) {
        usort($regionCreatures, fn($a, $b) => strcasecmp($a['name'], $b['name']));
    }
    unset($regionCreatures);

    ksort($grouped, SORT_NATURAL | SORT_FLAG_CASE);
    
    return $grouped;
}

/**
 * Get country emoji based on name
 */
function getCountryEmoji(string $name): string {
    $emojis = [
        'Aegia Aeterna' => '🏛️',
        'Aeonstep Plateau' => '🏔️',
        'Baharamandal' => '🕌',
        'Bretonreach' => '🏰',
        'Crescent Caliphate' => '🌙',
        'Eagle Serpent Dominion' => '🦅',
        'Eretz-Shalem League' => '⭐',
        'Gran Columbia' => '🌎',
        'Hammurabia' => '📜',
        'Itzam Empire' => '🐍',
        'Kemet' => '🏺',
        'Lotus-Dragon Kingdom' => '🐉',
        'Nornheim' => '❄️',
        'Red Sun Commonwealth' => '☀️',
        'Rheinland' => '⚔️',
        'Rodinian Tsardom' => '🐻',
        'Sapa Inti Empire' => '🌟',
        'Sila Council' => '🤝',
        'Sovereign Tribes of the Ancestral Plains' => '🏇',
        'Spice Route League' => '🧂',
        'United free Republic of Borealia' => '🦫',
        'Xochimex' => '🌺',
        'Yamanokubo' => '⛩️',
        'Yara Nations' => '🌴',
    ];
    
    return $emojis[$name] ?? '🏳️';
}

/**
 * Get creature emoji
 */
function getCreatureEmoji(string $name): string {
    $emojis = [
        'Tengu' => '🦅',
        'Dryad' => '🌳',
        'Treant' => '🌲',
        'Forest Sprite' => '✨',
        'Moss Drake' => '🐉',
        'Stone Golem' => '🗿',
        'Thunder Eagle' => '⚡',
        'Crystal Wyrm' => '💎',
        'Mountain Troll' => '👹',
        'Phoenix' => '🔥',
        'Kraken' => '🦑',
        'Merfolk' => '🧜',
        'Sea Serpent' => '🐍',
        'Siren' => '🧝',
        'Leviathan' => '🐋',
        'Sand Wyrm' => '🏜️',
        'Djinn' => '🧞',
        'Sphinx' => '🦁',
        'Fire Salamander' => '🦎',
        'Mummy Lord' => '💀',
        'Frost Giant' => '❄️',
        'Ice Elemental' => '🧊',
        'Winter Wolf' => '🐺',
        'Snow Owl' => '🦉',
        'Yeti' => '👣',
        'Shadow Wraith' => '👻',
        'Void Walker' => '🌀',
        'Dark Knight' => '⚔️',
        'Necromancer' => '🧙',
        'Blood Demon' => '👹',
    ];
    
    return $emojis[$name] ?? '📜';
}

/**
 * Get rarity color class
 */
function getRarityColor(string $rarity): string {
    $colors = [
        'Common' => 'bg-gray-100 text-gray-700 border border-gray-300',
        'Uncommon' => 'bg-green-100 text-green-700 border border-green-300',
        'Rare' => 'bg-blue-100 text-blue-700 border border-blue-300',
        'Epic' => 'bg-purple-100 text-purple-700 border border-purple-300',
        'Legendary' => 'bg-amber-100 text-amber-700 border border-amber-300',
        'Mythical' => 'bg-rose-100 text-rose-700 border border-rose-300',
    ];
    return $colors[$rarity] ?? $colors['Common'];
}

// ============================================================================
// LOAD DATA
// ============================================================================

$countries = loadCountries();
$creaturesByRegion = getCreaturesByRegion();
$allCreatures = [];
foreach ($creaturesByRegion as $regionCreatures) {
    foreach ($regionCreatures as $creature) {
        $allCreatures[$creature['name']] = $creature;
    }
}

// Build page index mapping
$pages = [];
$pages[] = ['type' => 'index', 'data' => null];

$regionPageIndex = [];
$creaturePageIndex = [];
$idx = 1;

foreach ($countries as $country) {
    $regionCreatures = $creaturesByRegion[$country] ?? [];
    if (!empty($regionCreatures)) {
        $regionPageIndex[$country] = $idx;
        $pages[] = ['type' => 'region', 'data' => $country];
        $idx++;
        
        foreach ($regionCreatures as $creature) {
            $creaturePageIndex[$creature['name']] = $idx;
            $pages[] = ['type' => 'creature', 'data' => $creature['name']];
            $idx++;
        }
    }
}

$totalPages = count($pages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestiary - Encyclopedia of Creatures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap');
        
        :root {
            --book-width: 900px;
            --book-height: 600px;
        }
        
        body {
            font-family: 'Lora', serif;
        }
        
        .font-serif {
            font-family: 'Cinzel', serif;
        }
        
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(217, 160, 82, 0.1);
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(217, 160, 82, 0.4);
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(217, 160, 82, 0.6);
        }
        
        /* Book closed animation */
        @keyframes bookOpen {
            0% { transform: scale(1) rotateY(0deg); }
            100% { transform: scale(1.05) rotateY(0deg); }
        }
        
        /* Page flip animation */
        @keyframes flipPage {
            0% { transform: perspective(1000px) rotateY(0deg); }
            100% { transform: perspective(1000px) rotateY(-180deg); }
        }
        
        .animate-flip-page {
            animation: flipPage 0.3s ease-in-out forwards;
        }
        
        /* Book cover texture */
        .book-cover-texture {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
        }
        
        /* Paper texture */
        .paper-texture {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='paper'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.04' numOctaves='5'/%3E%3CfeDiffuseLighting in='noise' lighting-color='%23f5f0e1' surfaceScale='2'%3E%3CfeDistantLight azimuth='45' elevation='60'/%3E%3C/feDiffuseLighting%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23paper)'/%3E%3C/svg%3E");
        }
        
        /* Navigation arrow transitions */
        .nav-arrow {
            transition: opacity 0.3s ease, background-color 0.2s ease;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-amber-900 via-amber-800 to-amber-900 flex items-center justify-center p-4 md:p-8 overflow-hidden">
    
    <!-- Background decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-0 w-96 h-96 bg-amber-600/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-amber-600/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
        <div class="absolute top-1/2 left-1/2 w-[800px] h-[800px] bg-amber-500/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
    </div>
    
    <!-- Book Container -->
    <div id="book-container" class="relative">
        
        <!-- Book Shadow -->
        <div id="book-shadow" class="absolute inset-0 bg-black/30 rounded-lg blur-xl transition-all duration-700 scale-100"></div>
        
        <!-- Book -->
        <div id="book" class="relative transition-all duration-700 ease-out w-72 md:w-80">
            
            <!-- ================================================================ -->
            <!-- CLOSED BOOK COVER -->
            <!-- ================================================================ -->
            <div id="book-cover" class="relative cursor-pointer transition-all duration-700 ease-out opacity-100 scale-100">
                
                <!-- Book spine shadow when closed -->
                <div class="absolute left-0 top-0 bottom-0 w-4 bg-gradient-to-r from-amber-950 to-transparent z-10"></div>
                
                <!-- Front cover -->
                <div class="relative bg-gradient-to-br from-amber-800 via-amber-700 to-amber-800 rounded-r-lg shadow-2xl p-6 border-2 border-amber-600">
                    
                    <!-- Leather texture -->
                    <div class="absolute inset-0 opacity-20 book-cover-texture"></div>
                    
                    <!-- Decorative border -->
                    <div class="absolute inset-3 border-2 border-amber-500/30 rounded pointer-events-none"></div>
                    <div class="absolute inset-5 border border-amber-400/20 rounded pointer-events-none"></div>
                    
                    <!-- Corner decorations -->
                    <div class="absolute top-3 left-3 w-8 h-8 border-t-2 border-l-2 border-amber-500/40 rounded-tl"></div>
                    <div class="absolute top-3 right-3 w-8 h-8 border-t-2 border-r-2 border-amber-500/40 rounded-tr"></div>
                    <div class="absolute bottom-3 left-3 w-8 h-8 border-b-2 border-l-2 border-amber-500/40 rounded-bl"></div>
                    <div class="absolute bottom-3 right-3 w-8 h-8 border-b-2 border-r-2 border-amber-500/40 rounded-br"></div>
                    
                    <!-- Book title -->
                    <div class="text-center py-12 relative z-10">
                        <div class="text-amber-200/60 text-xs tracking-[0.3em] uppercase mb-2">~ Compendium ~</div>
                        <h1 class="text-3xl md:text-4xl font-bold text-amber-100 font-serif tracking-wide drop-shadow-lg">
                            Bestiary
                        </h1>
                        <div class="text-amber-300/60 text-sm mt-2 font-serif italic">of World Creatures</div>
                        
                        <!-- Decorative divider -->
                        <div class="flex items-center justify-center gap-2 mt-6">
                            <div class="w-12 h-px bg-gradient-to-r from-transparent to-amber-400/40"></div>
                            <div class="text-amber-400/60">✦</div>
                            <div class="w-12 h-px bg-gradient-to-l from-transparent to-amber-400/40"></div>
                        </div>
                        
                        <div class="mt-8 text-amber-200/40 text-xs">
                            <?= count($allCreatures) ?> Creatures • <?= count($countries) ?> Nations
                        </div>
                    </div>
                    
                    <!-- Click to open hint -->
                    <div class="text-center mt-4 relative z-10">
                        <span class="text-amber-300/50 text-sm animate-pulse">Click to open</span>
                    </div>
                    
                    <!-- Book clasp -->
                    <div class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-1">
                        <div class="w-2 h-16 bg-gradient-to-b from-amber-600 via-amber-700 to-amber-600 rounded-r shadow-lg"></div>
                    </div>
                </div>
                
                <!-- Book thickness/pages effect -->
                <div class="absolute right-0 top-1 bottom-1 w-2 bg-gradient-to-l from-amber-200 via-amber-100 to-amber-200 rounded-r-sm shadow-inner"></div>
            </div>
            
            <!-- ================================================================ -->
            <!-- OPEN BOOK -->
            <!-- ================================================================ -->
            <div id="book-open" class="relative transition-all duration-700 ease-out origin-center opacity-0 scale-110 pointer-events-none absolute inset-0">
                
                <!-- Book binding -->
                <div class="absolute left-1/2 top-0 bottom-0 w-6 -translate-x-1/2 z-30">
                    <div class="w-full h-full bg-gradient-to-r from-amber-900 via-amber-800 to-amber-900 shadow-lg"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-black/20 via-transparent to-black/20"></div>
                </div>
                
                <!-- Pages container -->
                <div class="relative bg-gradient-to-br from-amber-50 via-amber-100 to-amber-50 rounded-lg shadow-2xl overflow-hidden flex" style="min-height: 600px; width: var(--book-width);">
                    
                    <!-- Left page -->
                    <div class="flex-1 relative p-6 md:p-8 border-r border-amber-200/50">
                        <!-- Page texture -->
                        <div class="absolute inset-0 opacity-30 paper-texture"></div>
                        
                        <!-- Page shadow from binding -->
                        <div class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-amber-200/50 to-transparent pointer-events-none"></div>
                        
                        <!-- Content -->
                        <div id="left-page-content" class="relative z-10 h-full overflow-y-auto pr-2 custom-scrollbar">
                            <!-- Content loaded via JS -->
                        </div>
                        
                        <!-- Page number -->
                        <div id="left-page-number" class="absolute bottom-2 left-1/2 -translate-x-1/2 text-amber-400 text-xs font-serif">
                            1
                        </div>
                    </div>
                    
                    <!-- Right page -->
                    <div class="flex-1 relative p-6 md:p-8">
                        <!-- Page texture -->
                        <div class="absolute inset-0 opacity-30 paper-texture"></div>
                        
                        <!-- Page shadow from binding -->
                        <div class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-amber-200/50 to-transparent pointer-events-none"></div>
                        
                        <!-- Content -->
                        <div id="right-page-content" class="relative z-10 h-full overflow-y-auto pr-2 custom-scrollbar">
                            <!-- Content loaded via JS -->
                        </div>
                        
                        <!-- Page number -->
                        <div id="right-page-number" class="absolute bottom-2 left-1/2 -translate-x-1/2 text-amber-400 text-xs font-serif">
                            2
                        </div>
                    </div>
                    
                    <!-- Flipping page animation overlay -->
                    <div id="flip-overlay" class="absolute inset-0 z-20 pointer-events-none hidden">
                        <div class="absolute right-1/2 top-0 bottom-0 w-1/2 origin-left bg-amber-50 shadow-2xl">
                            <div class="absolute inset-0 bg-gradient-to-l from-amber-100 to-amber-50"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Arrows -->
                <button id="nav-left" class="nav-arrow absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 md:-translate-x-12 w-8 h-16 md:w-10 md:h-20 flex items-center justify-center bg-amber-800/80 hover:bg-amber-700 text-amber-100 rounded-r-lg shadow-lg border border-amber-600/50 z-40 opacity-0 cursor-pointer">
                    <svg class="w-4 h-4 md:w-6 md:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                
                <button id="nav-right" class="nav-arrow absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 md:translate-x-12 w-8 h-16 md:w-10 md:h-20 flex items-center justify-center bg-amber-800/80 hover:bg-amber-700 text-amber-100 rounded-l-lg shadow-lg border border-amber-600/50 z-40 opacity-0 cursor-pointer">
                    <svg class="w-4 h-4 md:w-6 md:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                
                <!-- Close book button -->
                <button id="close-book-btn" class="absolute -top-2 -right-2 w-8 h-8 bg-amber-700 hover:bg-amber-600 text-amber-100 rounded-full shadow-lg flex items-center justify-center transition-colors z-50 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- ================================================================ -->
    <!-- PAGE TEMPLATES (Hidden) -->
    <!-- ================================================================ -->
    
    <!-- Index Page Template -->
    <template id="template-index">
        <div class="h-full">
            <h2 class="text-2xl font-bold text-amber-900 font-serif mb-2 text-center">Index of Nations</h2>
            <p class="text-amber-600 text-sm text-center mb-4">Select a nation to jump directly to its page</p>
            <ol id="index-list" class="space-y-1 text-sm max-h-64 overflow-y-auto pr-2 custom-scrollbar"></ol>
            <div class="mt-6 pt-4 border-t border-amber-200">
                <h3 class="text-sm font-bold text-amber-800 mb-3">Quick Navigation</h3>
                <div class="grid grid-cols-2 gap-2 text-xs text-amber-600">
                    <div>← → Arrow keys to flip</div>
                    <div>Click entries to jump pages</div>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Region Page Template -->
    <template id="template-region">
        <div class="h-full">
            <div class="flex items-center gap-3 mb-4">
                <span id="region-emoji" class="text-4xl"></span>
                <div>
                    <h2 id="region-name" class="text-2xl font-bold text-amber-900 font-serif"></h2>
                    <p id="region-creature-count" class="text-amber-600 text-sm"></p>
                </div>
            </div>
            <button class="back-to-index mb-4 text-sm text-amber-600 hover:text-amber-800 flex items-center gap-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Index
            </button>
            <div class="border-t border-amber-200 pt-4">
                <h3 class="text-lg font-bold text-amber-800 mb-3">Creatures of this Nation</h3>
                <div id="region-creatures" class="space-y-2"></div>
            </div>
        </div>
    </template>
    
    <!-- Creature Page Template -->
    <template id="template-creature">
        <div class="h-full">
            <div class="flex items-center justify-between mb-4">
                <h2 id="creature-name" class="text-2xl font-bold text-amber-900 font-serif"></h2>
                <span id="creature-rarity" class="px-2 py-1 rounded text-xs font-semibold"></span>
            </div>
            
            <!-- Creature Image -->
            <div class="relative w-full h-40 bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg mb-4 flex items-center justify-center overflow-hidden border-2 border-amber-200">
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-2 left-2 w-16 h-16 border-2 border-amber-400 rounded-full"></div>
                    <div class="absolute bottom-4 right-4 w-12 h-12 border-2 border-amber-400 rounded-full"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 border border-amber-400"></div>
                </div>
                <img id="creature-image" src="" alt="" class="relative z-10 h-full w-full object-contain p-2" loading="lazy" />
            </div>
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-4 gap-2 mb-4">
                <div class="text-center">
                    <div class="text-xs text-amber-700 font-medium mb-1">HP</div>
                    <div class="h-16 bg-amber-50 rounded relative overflow-hidden border border-amber-200">
                        <div id="stat-hp-bar" class="absolute bottom-0 left-0 right-0 bg-red-500 opacity-60 transition-all duration-500"></div>
                        <span id="stat-hp-value" class="relative z-10 font-bold text-amber-900 text-sm" style="line-height: 64px;"></span>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-amber-700 font-medium mb-1">ATK</div>
                    <div class="h-16 bg-amber-50 rounded relative overflow-hidden border border-amber-200">
                        <div id="stat-atk-bar" class="absolute bottom-0 left-0 right-0 bg-orange-500 opacity-60 transition-all duration-500"></div>
                        <span id="stat-atk-value" class="relative z-10 font-bold text-amber-900 text-sm" style="line-height: 64px;"></span>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-amber-700 font-medium mb-1">DEF</div>
                    <div class="h-16 bg-amber-50 rounded relative overflow-hidden border border-amber-200">
                        <div id="stat-def-bar" class="absolute bottom-0 left-0 right-0 bg-blue-500 opacity-60 transition-all duration-500"></div>
                        <span id="stat-def-value" class="relative z-10 font-bold text-amber-900 text-sm" style="line-height: 64px;"></span>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-amber-700 font-medium mb-1">INIT</div>
                    <div class="h-16 bg-amber-50 rounded relative overflow-hidden border border-amber-200">
                        <div id="stat-init-bar" class="absolute bottom-0 left-0 right-0 bg-green-500 opacity-60 transition-all duration-500"></div>
                        <span id="stat-init-value" class="relative z-10 font-bold text-amber-900 text-sm" style="line-height: 64px;"></span>
                    </div>
                </div>
            </div>
            
            <!-- Info Row -->
            <div class="grid grid-cols-3 gap-2 mb-4 text-xs">
                <div class="bg-amber-50 p-2 rounded border border-amber-200">
                    <span class="text-amber-600">Size: </span>
                    <span id="creature-size" class="font-medium text-amber-900"></span>
                </div>
                <div class="bg-amber-50 p-2 rounded border border-amber-200">
                    <span class="text-amber-600">Diet: </span>
                    <span id="creature-diet" class="font-medium text-amber-900"></span>
                </div>
                <div class="bg-amber-50 p-2 rounded border border-amber-200">
                    <span class="text-amber-600">Nation: </span>
                    <span id="creature-region" class="font-medium text-amber-900"></span>
                </div>
            </div>
            
            <!-- Colors -->
            <div class="mb-4">
                <span class="text-xs text-amber-600 block mb-2">Color Variants:</span>
                <div id="creature-colors" class="flex flex-wrap gap-1 max-h-16 overflow-y-auto pr-2 custom-scrollbar"></div>
            </div>
            
            <!-- Description -->
            <div class="bg-amber-50/50 p-3 rounded-lg border border-amber-100">
                <span class="text-xs text-amber-600 block mb-2 font-semibold">Description:</span>
                <div id="creature-description" class="text-amber-800 text-sm leading-relaxed max-h-24 overflow-y-auto pr-2 custom-scrollbar"></div>
            </div>
        </div>
    </template>
    
    <!-- ================================================================ -->
    <!-- JAVASCRIPT -->
    <!-- ================================================================ -->
    <script>
    (function() {
        // ====================================================================
        // DATA (Embedded from PHP)
        // ====================================================================
        
        const pages = <?= json_encode($pages) ?>;
        const creatures = <?= json_encode($allCreatures) ?>;
        const creaturesByRegion = <?= json_encode($creaturesByRegion) ?>;
        const countries = <?= json_encode($countries) ?>;
        const regionPageIndex = <?= json_encode($regionPageIndex) ?>;
        const creaturePageIndex = <?= json_encode($creaturePageIndex) ?>;
        
        // ====================================================================
        // STATE
        // ====================================================================
        
        let isBookOpen = false;
        let isFlipping = false;
        let currentPageIndex = 0;
        
        // ====================================================================
        // DOM ELEMENTS
        // ====================================================================
        
        const bookContainer = document.getElementById('book-container');
        const book = document.getElementById('book');
        const bookCover = document.getElementById('book-cover');
        const bookOpen = document.getElementById('book-open');
        const bookShadow = document.getElementById('book-shadow');
        const leftPageContent = document.getElementById('left-page-content');
        const rightPageContent = document.getElementById('right-page-content');
        const leftPageNumber = document.getElementById('left-page-number');
        const rightPageNumber = document.getElementById('right-page-number');
        const navLeft = document.getElementById('nav-left');
        const navRight = document.getElementById('nav-right');
        const closeBookBtn = document.getElementById('close-book-btn');
        const flipOverlay = document.getElementById('flip-overlay');
        
        // ====================================================================
        // TEXT FORMATTING
        // ====================================================================
        
        function parseFormattedText(text) {
            const lines = text.split('\n');
            
            return lines.map((line, lineIndex) => {
                // Handle bullet points
                if (line.startsWith('- ')) {
                    const content = line.substring(2);
                    return `<div class="flex gap-2 ml-4 my-1">
                        <span class="text-amber-700">•</span>
                        <span>${parseInlineFormatting(content)}</span>
                    </div>`;
                }
                
                // Handle headers (text ending with :)
                if (line.endsWith(':') && !line.startsWith('*') && line.length < 50) {
                    return `<h4 class="font-bold text-amber-900 mt-3 mb-1 text-sm">${parseInlineFormatting(line)}</h4>`;
                }
                
                // Handle empty lines
                if (line.trim() === '') {
                    return '<div class="h-2"></div>';
                }
                
                // Regular text
                return `<p class="my-1 text-sm leading-relaxed">${parseInlineFormatting(line)}</p>`;
            }).join('');
        }
        
        function parseInlineFormatting(text) {
            // Handle **bold** and *italic*
            return text
                .replace(/\*\*(.+?)\*\*/g, '<strong class="font-bold text-amber-900">$1</strong>')
                .replace(/\*(.+?)\*/g, '<em class="italic">$1</em>');
        }
        
        // ====================================================================
        // EMOJI HELPERS
        // ====================================================================
        
        const countryEmojis = <?= json_encode(getCountryEmoji('')) ?>;
        const creatureEmojis = <?= json_encode(getCreatureEmoji('')) ?>;
        
        function getCountryEmoji(name) {
            const emojis = {
                'Aegia Aeterna': '🏛️',
                'Aeonstep Plateau': '🏔️',
                'Baharamandal': '🕌',
                'Bretonreach': '🏰',
                'Crescent Caliphate': '🌙',
                'Eagle Serpent Dominion': '🦅',
                'Eretz-Shalem League': '⭐',
                'Gran Columbia': '🌎',
                'Hammurabia': '📜',
                'Itzam Empire': '🐍',
                'Kemet': '🏺',
                'Lotus-Dragon Kingdom': '🐉',
                'Nornheim': '❄️',
                'Red Sun Commonwealth': '☀️',
                'Rheinland': '⚔️',
                'Rodinian Tsardom': '🐻',
                'Sapa Inti Empire': '🌟',
                'Sila Council': '🤝',
                'Sovereign Tribes of the Ancestral Plains': '🏇',
                'Spice Route League': '🧂',
                'United free Republic of Borealia': '🦫',
                'Xochimex': '🌺',
                'Yamanokubo': '⛩️',
                'Yara Nations': '🌴',
            };
            return emojis[name] || '🏳️';
        }
        
        function getCreatureEmoji(name) {
            const emojis = {
                'Tengu': '🦅', 'Dryad': '🌳', 'Treant': '🌲', 'Forest Sprite': '✨', 'Moss Drake': '🐉',
                'Stone Golem': '🗿', 'Thunder Eagle': '⚡', 'Crystal Wyrm': '💎', 'Mountain Troll': '👹', 'Phoenix': '🔥',
                'Kraken': '🦑', 'Merfolk': '🧜', 'Sea Serpent': '🐍', 'Siren': '🧝', 'Leviathan': '🐋',
                'Sand Wyrm': '🏜️', 'Djinn': '🧞', 'Sphinx': '🦁', 'Fire Salamander': '🦎', 'Mummy Lord': '💀',
                'Frost Giant': '❄️', 'Ice Elemental': '🧊', 'Winter Wolf': '🐺', 'Snow Owl': '🦉', 'Yeti': '👣',
                'Shadow Wraith': '👻', 'Void Walker': '🌀', 'Dark Knight': '⚔️', 'Necromancer': '🧙', 'Blood Demon': '👹',
            };
            return emojis[name] || '📜';
        }
        
        function getRarityColorClass(rarity) {
            const colors = {
                'Common': 'bg-gray-100 text-gray-700 border border-gray-300',
                'Uncommon': 'bg-green-100 text-green-700 border border-green-300',
                'Rare': 'bg-blue-100 text-blue-700 border border-blue-300',
                'Epic': 'bg-purple-100 text-purple-700 border border-purple-300',
                'Legendary': 'bg-amber-100 text-amber-700 border border-amber-300',
                'Mythical': 'bg-rose-100 text-rose-700 border border-rose-300',
            };
            return colors[rarity] || colors['Common'];
        }
        
        // ====================================================================
        // PAGE RENDERING
        // ====================================================================
        
        function renderIndexPage() {
            const template = document.getElementById('template-index');
            const content = template.content.cloneNode(true);
            const list = content.querySelector('#index-list');

            countries.forEach(country => {
                const regionCreatures = creaturesByRegion[country] || [];
                if (regionCreatures.length === 0) return;

                const pageIndex = regionPageIndex[country];
                const pageNumber = pageIndex + 1;

                const row = document.createElement('li');
                const btn = document.createElement('button');
                btn.className = 'w-full text-left flex items-center gap-2 py-1 text-amber-900 hover:text-amber-700 transition-colors cursor-pointer';
                btn.setAttribute('data-page-target', pageIndex);
                btn.innerHTML = `
                    <span class="shrink-0">${getCountryEmoji(country)}</span>
                    <span class="shrink-0">${country}</span>
                    <span class="flex-1 border-b border-dotted border-amber-300 mb-1"></span>
                    <span class="shrink-0 font-semibold">${pageNumber}</span>
                `;
                btn.title = `${regionCreatures.length} creatures`;
                btn.addEventListener('click', () => navigateToPage(pageIndex));

                row.appendChild(btn);
                list.appendChild(row);
            });

            return content;
        }
        
        function renderRegionPage(country) {
            const template = document.getElementById('template-region');
            const content = template.content.cloneNode(true);
            const regionCreatures = creaturesByRegion[country] || [];
            
            content.querySelector('#region-emoji').textContent = getCountryEmoji(country);
            content.querySelector('#region-name').textContent = country;
            content.querySelector('#region-creature-count').textContent = `${regionCreatures.length} creatures documented`;
            
            const creaturesList = content.querySelector('#region-creatures');
            if (regionCreatures.length === 0) {
                creaturesList.innerHTML = '<p class="text-sm text-amber-600 italic">No creatures recorded for this nation yet.</p>';
            }

            regionCreatures.forEach((creature) => {
                const pageIndex = creaturePageIndex[creature.name];
                const btn = document.createElement('button');
                btn.className = 'w-full text-left p-2 bg-amber-50 hover:bg-amber-100 rounded border border-amber-200 transition-all group flex items-center gap-3 cursor-pointer';
                btn.setAttribute('data-page-target', pageIndex);
                btn.innerHTML = `
                    <span class="text-xl">${getCreatureEmoji(creature.name)}</span>
                    <div class="flex-1">
                        <div class="font-medium text-amber-900 group-hover:text-amber-700 text-sm">${creature.name}</div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold ${getRarityColorClass(creature.rarity)}">${creature.rarity}</span>
                            <span class="text-xs text-amber-500">HP: ${creature.stats.hp}</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-amber-300 group-hover:text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                `;
                btn.addEventListener('click', () => navigateToPage(pageIndex));
                creaturesList.appendChild(btn);
            });
            
            // Back to index handler
            content.querySelector('.back-to-index').addEventListener('click', () => navigateToPage(0));
            
            return content;
        }
        
        function renderCreaturePage(creatureName) {
            const creature = creatures[creatureName];
            if (!creature) return document.createTextNode('Creature not found');
            
            const template = document.getElementById('template-creature');
            const content = template.content.cloneNode(true);
            
            content.querySelector('#creature-name').textContent = creature.name;
            const creatureImage = content.querySelector('#creature-image');
            creatureImage.src = creature.image;
            creatureImage.alt = `${creature.name} (female, blue)`;
            creatureImage.onerror = () => {
                creatureImage.src = 'images/tengu_f_blue.png';
            };
            
            const rarityEl = content.querySelector('#creature-rarity');
            rarityEl.textContent = creature.rarity;
            rarityEl.className = `px-2 py-1 rounded text-xs font-semibold ${getRarityColorClass(creature.rarity)}`;
            
            // Stats
            ['hp', 'atk', 'def', 'init'].forEach(stat => {
                const value = creature.stats[stat];
                content.querySelector(`#stat-${stat}-value`).textContent = value;
                content.querySelector(`#stat-${stat}-bar`).style.height = `${Math.min((value / 200) * 100, 100)}%`;
            });
            
            content.querySelector('#creature-size').textContent = creature.size;
            content.querySelector('#creature-diet').textContent = creature.diet;
            content.querySelector('#creature-region').textContent = creature.region;
            
            // Colors
            const colorsEl = content.querySelector('#creature-colors');
            creature.colors.forEach(color => {
                const span = document.createElement('span');
                span.className = 'px-2 py-1 bg-gradient-to-r from-amber-100 to-amber-50 border border-amber-200 rounded text-xs font-medium text-amber-800';
                span.textContent = color;
                colorsEl.appendChild(span);
            });
            
            // Description
            content.querySelector('#creature-description').innerHTML = parseFormattedText(creature.description);
            
            return content;
        }
        
        function renderPage(pageData) {
            if (!pageData) return null;
            
            switch (pageData.type) {
                case 'index':
                    return renderIndexPage();
                case 'region':
                    return renderRegionPage(pageData.data);
                case 'creature':
                    return renderCreaturePage(pageData.data);
                default:
                    return null;
            }
        }
        
        // ====================================================================
        // BOOK CONTROLS
        // ====================================================================
        
        function openBook() {
            if (isBookOpen) return;
            
            isBookOpen = true;
            currentPageIndex = 0;
            
            // Animate book opening
            bookCover.classList.remove('opacity-100', 'scale-100');
            bookCover.classList.add('opacity-0', 'scale-90', 'pointer-events-none', 'absolute');
            
            bookOpen.classList.remove('opacity-0', 'scale-110', 'pointer-events-none');
            bookOpen.classList.add('opacity-100', 'scale-100');
            
            book.style.width = '900px';
            bookShadow.classList.add('scale-105');
            
            // Render initial pages
            updatePages();
        }
        
        function closeBook() {
            if (!isBookOpen) return;
            
            isBookOpen = false;
            currentPageIndex = 0;
            
            // Animate book closing
            bookCover.classList.remove('opacity-0', 'scale-90', 'pointer-events-none', 'absolute');
            bookCover.classList.add('opacity-100', 'scale-100');
            
            bookOpen.classList.remove('opacity-100', 'scale-100');
            bookOpen.classList.add('opacity-0', 'scale-110', 'pointer-events-none');
            
            book.style.width = '';
            bookShadow.classList.remove('scale-105');
        }
        
        function updatePages() {
            const leftContent = renderPage(pages[currentPageIndex]);
            const rightContent = renderPage(pages[currentPageIndex + 1]);
            
            leftPageContent.innerHTML = '';
            rightPageContent.innerHTML = '';
            
            if (leftContent) leftPageContent.appendChild(leftContent);
            if (rightContent) rightPageContent.appendChild(rightContent);
            
            leftPageNumber.textContent = currentPageIndex * 2 + 1;
            rightPageNumber.textContent = currentPageIndex * 2 + 2;
        }
        
        function navigateToPage(targetIndex) {
            if (isFlipping || targetIndex === currentPageIndex) return;
            if (!isBookOpen) openBook();
            
            isFlipping = true;
            
            const step = targetIndex > currentPageIndex ? 1 : -1;
            let current = currentPageIndex;
            
            const flipInterval = setInterval(() => {
                current += step;
                currentPageIndex = current;
                updatePages();
                
                if (current === targetIndex) {
                    clearInterval(flipInterval);
                    setTimeout(() => {
                        isFlipping = false;
                    }, 300);
                }
            }, 150);
        }
        
        function nextPage() {
            if (currentPageIndex < pages.length - 1) {
                navigateToPage(currentPageIndex + 1);
            }
        }
        
        function prevPage() {
            if (currentPageIndex > 0) {
                navigateToPage(currentPageIndex - 1);
            }
        }
        
        // ====================================================================
        // EVENT HANDLERS
        // ====================================================================
        
        // Click to open book
        bookCover.addEventListener('click', openBook);
        
        // Close button
        closeBookBtn.addEventListener('click', closeBook);
        
        // Navigation arrows
        navLeft.addEventListener('click', prevPage);
        navRight.addEventListener('click', nextPage);
        
        // Mouse proximity for arrows
        bookContainer.addEventListener('mousemove', (e) => {
            if (!isBookOpen) return;
            
            const rect = bookContainer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            
            // Left arrow
            const leftDist = x / width;
            const leftOpacity = Math.max(0, 1 - leftDist * 5) * (currentPageIndex > 0 ? 1 : 0);
            navLeft.style.opacity = leftOpacity;
            
            // Right arrow
            const rightDist = (width - x) / width;
            const rightOpacity = Math.max(0, 1 - rightDist * 5) * (currentPageIndex < pages.length - 1 ? 1 : 0);
            navRight.style.opacity = rightOpacity;
        });
        
        bookContainer.addEventListener('mouseleave', () => {
            navLeft.style.opacity = 0;
            navRight.style.opacity = 0;
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!isBookOpen || isFlipping) return;
            
            if (e.key === 'ArrowRight' || e.key === ' ') {
                e.preventDefault();
                nextPage();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                prevPage();
            } else if (e.key === 'Escape') {
                closeBook();
            }
        });
        
    })();
    </script>
</body>
</html>
