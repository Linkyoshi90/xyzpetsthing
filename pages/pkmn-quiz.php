<?php
require_once __DIR__.'/../auth.php';
require_login();
require_once __DIR__.'/../lib/input.php';
require_once __DIR__.'/../db.php';

$quizConfigDir = dirname(__DIR__).'/data-readonly/pkmn';

function pkmn_quiz_config_files(string $dir): array {
    $files = [];
    foreach (glob($dir.'/*.json') as $path) {
        $files[] = basename($path);
    }
    sort($files);
    // harmontide-gen.json is the default config; list it first so it preselects.
    $default = array_search('harmontide-gen.json', $files, true);
    if ($default !== false) {
        array_splice($files, $default, 1);
        array_unshift($files, 'harmontide-gen.json');
    }
    return $files;
}

// "Will-o-Wisp" -> "willowisp" (sprite file name for /images/games/<prefix>/)
function pkmn_quiz_name_key(string $name): string {
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
    if (is_string($ascii) && $ascii !== '') {
        $name = $ascii;
    }
    return preg_replace('/[^a-z0-9]+/', '', strtolower($name));
}

// "Will-o-Wisp" -> "will_o_wisp" (creature image slug in /images/creatures/)
function pkmn_quiz_creature_slug(string $name): string {
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
    if (is_string($ascii) && $ascii !== '') {
        $name = $ascii;
    }
    return trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($name)), '_');
}

function pkmn_quiz_find_creature_image(string $slug): ?string {
    $dir = dirname(__DIR__).'/images/creatures';
    foreach (['blue', 'green', 'grey', 'red'] as $color) {
        if (is_file("$dir/{$slug}_f_{$color}.webp")) {
            return "images/creatures/{$slug}_f_{$color}.webp";
        }
    }
    foreach (['f', 'm'] as $sex) {
        $matches = glob("$dir/{$slug}_{$sex}_*.webp");
        if ($matches) {
            return 'images/creatures/'.basename($matches[0]);
        }
    }
    return null;
}

// The config file prefix (before the first "-") decides where sprites live:
// harmontide-*.json uses the real creature art in /images/creatures/,
// any other prefix (pkmn-, ...) uses /images/games/<prefix>/<name key>.png
function pkmn_quiz_resolve_sprites(array $doc, string $prefix): array {
    foreach ($doc['categories'] as &$cat) {
        foreach ($cat['entries'] as &$entry) {
            if (is_string($entry)) {
                $entry = ['en' => $entry];
            }
            $names = array_diff_key($entry, ['aliases' => 1, 'sprite' => 1, 'spriteUrl' => 1]);
            $en = (string)($entry['en'] ?? (reset($names) ?: ''));
            $base = (string)($entry['sprite'] ?? $en);
            if ($prefix === 'harmontide') {
                $entry['spriteUrl'] = pkmn_quiz_find_creature_image(pkmn_quiz_creature_slug($base));
            } else {
                $entry['spriteUrl'] = 'images/games/'.$prefix.'/'.pkmn_quiz_name_key($base).'.png';
            }
        }
        unset($entry);
    }
    unset($cat);
    return $doc;
}

function pkmn_quiz_entry_count(array $doc): int {
    $count = 0;
    foreach (($doc['categories'] ?? []) as $cat) {
        $count += count($cat['entries'] ?? []);
    }
    return $count;
}

function pkmn_quiz_top_scores(): array {
    return q(
        'SELECT initials, score, timer FROM `pkmn-game` ORDER BY score DESC, timer ASC, scoreId ASC LIMIT 10'
    )->fetchAll(PDO::FETCH_ASSOC);
}

$quizConfigs = [];
foreach (pkmn_quiz_config_files($quizConfigDir) as $file) {
    $doc = json_decode((string)file_get_contents($quizConfigDir.'/'.$file), true);
    if (is_array($doc) && !empty($doc['categories'])) {
        unset($doc['_help']);
        $prefix = preg_replace('/[^a-z0-9_]/', '', strtolower(explode('-', basename($file, '.json'))[0]));
        $quizConfigs[$file] = pkmn_quiz_resolve_sprites($doc, $prefix);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = input_string($_POST['action'] ?? '', 30);
    if ($action !== 'submit_score') {
        echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
        return;
    }
    $initials = mb_strtoupper(input_string($_POST['initials'] ?? '', 10));
    $score = input_int($_POST['score'] ?? 0, 0);
    $timer = input_int($_POST['timer'] ?? 0, 60, 1800);
    $configFile = input_string($_POST['config'] ?? '', 100);
    if ($initials === '' || $timer === 0 || !isset($quizConfigs[$configFile])) {
        echo json_encode(['ok' => false, 'error' => 'Invalid submission.']);
        return;
    }
    $maxScore = pkmn_quiz_entry_count($quizConfigs[$configFile]);
    if ($score > $maxScore) {
        $score = $maxScore;
    }
    q('INSERT INTO `pkmn-game` (initials, score, timer) VALUES (?, ?, ?)', [$initials, $score, $timer]);
    echo json_encode(['ok' => true, 'scores' => pkmn_quiz_top_scores()]);
    return;
}

$quizTopScores = pkmn_quiz_top_scores();
$quizPayload = [
    'configs' => $quizConfigs,
    'defaultConfig' => isset($quizConfigs['harmontide-gen.json']) ? 'harmontide-gen.json' : (string)array_key_first($quizConfigs),
];
?>
<style>
.pkmn-quiz { position: relative; max-width: 900px; margin: 0 auto; }
.pkmn-quiz-timer {
  position: sticky; top: 8px; z-index: 5; width: fit-content; margin: 0 auto 10px;
  padding: 4px 18px; border-radius: 999px; background: #000; color: #fff;
  font-size: 1.5em; font-variant-numeric: tabular-nums; opacity: .5; pointer-events: none;
}
.pkmn-quiz-timer.low { color: #ff6b6b; }
.pkmn-quiz-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 10px; }
.pkmn-quiz-progress { font-weight: bold; }
.pkmn-quiz-cog {
  border: none; background: none; cursor: pointer; font-size: 1.6em; line-height: 1;
  transition: transform .3s;
}
.pkmn-quiz-cog:hover { transform: rotate(60deg); }
.pkmn-quiz-settings {
  position: absolute; top: 40px; right: 0; z-index: 10; width: 280px;
  padding: 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,.25);
  background: rgba(20, 25, 45, .95); box-shadow: 0 8px 24px rgba(0,0,0,.45);
  transform: translateX(110%); opacity: 0; pointer-events: none; transition: transform .25s, opacity .25s;
}
.pkmn-quiz-settings.open { transform: translateX(0); opacity: 1; pointer-events: auto; }
.pkmn-quiz-settings label { display: block; margin: 10px 0 4px; font-size: .9em; }
.pkmn-quiz-settings select, .pkmn-quiz-settings input[type=range] { width: 100%; }
.pkmn-quiz-tables { display: flex; flex-direction: column; gap: 14px; margin-bottom: 12px; }
.pkmn-quiz-cat h3 { margin: 0 0 6px; }
.pkmn-quiz-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 6px 12px;
}
.pkmn-quiz-cell { display: flex; align-items: center; gap: 8px; padding: 3px 6px; border-radius: 8px; }
.pkmn-quiz-cell.cleared { background: rgba(80, 220, 120, .18); }
.pkmn-quiz-dot { width: 50px; height: 50px; border-radius: 50%; background: #000; flex: 0 0 auto; }
.pkmn-quiz-cell img { width: 50px; height: 50px; object-fit: contain; flex: 0 0 auto; }
.pkmn-quiz-name { font-size: .9em; letter-spacing: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pkmn-quiz-chat { display: flex; gap: 8px; position: sticky; bottom: 8px; z-index: 5; }
.pkmn-quiz-chat input {
  flex: 1; padding: 10px 12px; font-size: 1.1em; border-radius: 10px;
  border: 1px solid rgba(255,255,255,.35); background: rgba(0,0,0,.55); color: #fff;
}
.pkmn-quiz-overlay {
  position: absolute; inset: 0; z-index: 20; display: none; align-items: center; justify-content: center;
  background: rgba(0,0,0,.65); border-radius: 12px;
}
.pkmn-quiz-overlay.visible { display: flex; }
.pkmn-quiz-overlay .panel { text-align: center; padding: 24px 32px; border-radius: 12px; background: rgba(20,25,45,.97); }
.pkmn-quiz-overlay input { text-transform: uppercase; text-align: center; width: 130px; font-size: 1.2em; margin: 8px 0; }
.pkmn-quiz-scores td, .pkmn-quiz-scores th { padding: 3px 12px; text-align: left; }
</style>

<h1>Creature Quiz</h1>
<p class="muted">How many creatures can you name from memory before the clock runs out? Type names into the chat bar below.</p>

<div class="pkmn-quiz card glass" id="pkmn-quiz">
  <div class="pkmn-quiz-timer" id="pkmn-quiz-timer">--:--</div>
  <div class="pkmn-quiz-toolbar">
    <span class="pkmn-quiz-progress" id="pkmn-quiz-progress"></span>
    <button class="btn" id="pkmn-quiz-start">Start</button>
    <button class="pkmn-quiz-cog" id="pkmn-quiz-cog" type="button" title="Settings" aria-label="Settings">&#9881;</button>
  </div>
  <div class="pkmn-quiz-settings" id="pkmn-quiz-settings">
    <strong>Settings</strong>
    <label for="pkmn-quiz-config">Loaded config</label>
    <select id="pkmn-quiz-config"></select>
    <label for="pkmn-quiz-lang">Language</label>
    <select id="pkmn-quiz-lang"></select>
    <label for="pkmn-quiz-length">Timer: <span id="pkmn-quiz-length-label"></span></label>
    <input type="range" id="pkmn-quiz-length" min="60" max="1800" step="60" value="300">
  </div>
  <div class="pkmn-quiz-tables" id="pkmn-quiz-tables"></div>
  <form class="pkmn-quiz-chat" id="pkmn-quiz-chat" autocomplete="off" onsubmit="return false;">
    <input type="text" id="pkmn-quiz-input" placeholder="Type a creature name..." maxlength="60" disabled>
  </form>
  <div class="pkmn-quiz-overlay" id="pkmn-quiz-overlay">
    <div class="panel">
      <h2 id="pkmn-quiz-result-title">Time's up!</h2>
      <p id="pkmn-quiz-result-text"></p>
      <div id="pkmn-quiz-submit-block">
        <label for="pkmn-quiz-initials">Your initials</label><br>
        <input type="text" id="pkmn-quiz-initials" maxlength="10" placeholder="AAA"><br>
        <button class="btn" id="pkmn-quiz-submit">Submit score</button>
      </div>
      <p id="pkmn-quiz-submit-msg" class="muted"></p>
      <button class="btn" id="pkmn-quiz-again">Play again</button>
    </div>
  </div>
</div>

<div class="card glass" style="max-width:900px;margin:16px auto 0;">
  <h3>Top 10 Scores</h3>
  <table class="pkmn-quiz-scores" id="pkmn-quiz-scores">
    <thead><tr><th>#</th><th>Initials</th><th>Score</th><th>Timer</th></tr></thead>
    <tbody>
      <?php if (!$quizTopScores): ?>
      <tr><td colspan="4" class="muted">No scores yet - be the first!</td></tr>
      <?php else: foreach ($quizTopScores as $i => $row): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= htmlspecialchars($row['initials'], ENT_QUOTES) ?></td>
        <td><?= (int)$row['score'] ?></td>
        <td><?= floor((int)$row['timer'] / 60) ?>:<?= str_pad((string)((int)$row['timer'] % 60), 2, '0', STR_PAD_LEFT) ?></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<script id="pkmn-quiz-payload" type="application/json"><?= json_encode($quizPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<script src="assets/js/pkmn-quiz.js"></script>
