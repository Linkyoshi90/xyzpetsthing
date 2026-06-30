<?php
require_login();

function stap_starpath_region_id(): int {
    return (int)q(
        "SELECT region_id
           FROM regions
          WHERE region_name = ?
          LIMIT 1",
        ['Sovereign Tribes of the Ancestral Plains']
    )->fetchColumn();
}

function stap_starpath_species_id(int $regionId, string $speciesName): int {
    if ($regionId <= 0 || trim($speciesName) === '') {
        return 0;
    }

    return (int)q(
        "SELECT species_id
           FROM pet_species
          WHERE region_id = ?
            AND species_name = ?
          LIMIT 1",
        [$regionId, $speciesName]
    )->fetchColumn();
}

function stap_starpath_leader_trainer_id(): int {
    return (int)q(
        "SELECT trainer_id
           FROM trainers
          WHERE class_name = ?
            AND trainer_name = ?
          ORDER BY trainer_id
          LIMIT 1",
        ['Starpath Keeper', 'Sahana Red Sash']
    )->fetchColumn();
}

function stap_starpath_first_image(array $paths): string {
    foreach ($paths as $path) {
        if (is_file(__DIR__.'/../'.$path)) {
            return $path;
        }
    }

    return 'images/maps/harmontide-stap.webp';
}

function stap_starpath_battle_url(int $regionId, string $speciesName = '', string $returnTo = 'index.php?pg=stap-starpath-gym'): string {
    $query = [
        'pg' => 'battle_minigame',
        'battle' => 'wild',
        'region_id' => $regionId,
        'return_to' => $returnTo,
    ];

    $speciesId = stap_starpath_species_id($regionId, $speciesName);
    if ($speciesId > 0) {
        $query['species_id'] = $speciesId;
    }

    return 'index.php?'.http_build_query($query);
}

function stap_starpath_trainer_battle_url(int $trainerId, string $returnTo): string {
    return 'index.php?'.http_build_query([
        'pg' => 'battle_minigame',
        'trainer_id' => $trainerId,
        'return_to' => $returnTo,
    ]);
}

$regionId = stap_starpath_region_id();
$leaderTrainerId = stap_starpath_leader_trainer_id();

$mapImage = stap_starpath_first_image(['images/maps/harmontide-stap.webp']);
$thunderbirdImage = stap_starpath_first_image(['images/creatures/thunderbird_f_regal.webp', 'images/creatures/thunderbird_f_blue.webp']);
$hornedSerpentImage = stap_starpath_first_image(['images/creatures/horned_serpent_uktena_f_blue.webp']);
$kachinaImage = stap_starpath_first_image(['images/creatures/kachina_f_blue.webp']);
$dustbisonImage = stap_starpath_first_image(['images/creatures/dustbison_f_blue.webp']);
$prairhornImage = stap_starpath_first_image(['images/creatures/prairhorn_f_blue.webp']);
$famineImage = stap_starpath_first_image(['images/creatures/famine_f_black.webp', 'images/creatures/famine_f_blue.webp']);
$alisnaporImage = stap_starpath_first_image(['images/creatures/alisnapor_f_brown.webp', 'images/creatures/alisnapor_f_blue.webp']);

$returnBase = 'index.php?pg=stap-starpath-gym';
$prairhornBattleUrl = stap_starpath_battle_url($regionId, 'Prairhorn', $returnBase.'&scene=bison-gate');
$dustbisonBattleUrl = stap_starpath_battle_url($regionId, 'Dustbison', $returnBase.'&scene=council-promise');
$kachinaBattleUrl = stap_starpath_battle_url($regionId, 'Kachina', $returnBase.'&scene=leader-ring');
$thunderbirdBattleUrl = stap_starpath_battle_url($regionId, 'Thunderbird', $returnBase.'&scene=leader-ring');
$hornedSerpentBattleUrl = stap_starpath_battle_url($regionId, 'Horned Serpent Uktena', $returnBase.'&scene=leader-ring');
$famineBattleUrl = stap_starpath_battle_url($regionId, 'Famine', $returnBase.'&scene=shadow-warning');
$alisnaporBattleUrl = stap_starpath_battle_url($regionId, 'Alisnapor', $returnBase.'&scene=winter-count');

$leaderBattleUrl = $leaderTrainerId > 0
    ? stap_starpath_trainer_battle_url($leaderTrainerId, $returnBase.'&scene=badge-ceremony')
    : $thunderbirdBattleUrl;
$leaderBattleNote = $leaderTrainerId > 0
    ? 'Leader battle: Keeper Sahana and her Starpath roster.'
    : 'Fallback battle: Thunderbird guardian. Import sql/stap_starpath_gym.sql to enable Keeper Sahana.';

$story = [
    'start' => 'river-bend',
    'nodes' => [
        'river-bend' => [
            'title' => 'River Bend Market',
            'image' => ['src' => $mapImage, 'alt' => 'Turtlestar map with the Circle of Fires and Star Walk'],
            'body' => [
                'Turtlestar wakes in rings: market blankets outside the council green, drum practice by the river, coffee steam moving through the grass like low weather.',
                'Everyone seems to know why you are here before you say it. The Starpath Proving Ring opens today, and Keeper Sahana Red Sash is taking challengers at the Circle of Fires.',
                'The first rule travels faster than gossip: do not cut through a speaking circle. The second follows close behind: thunder answers people who listen.'
            ],
            'choices' => [
                ['text' => 'Ask the market drummer about Keeper Sahana', 'target' => 'market-drummer'],
                ['text' => 'Visit the trading blanket for battle advice', 'target' => 'blanket-advice'],
                ['text' => 'Walk to the outer ring and register', 'target' => 'outer-ring']
            ],
        ],
        'market-drummer' => [
            'title' => 'The Market Drummer',
            'image' => ['src' => $thunderbirdImage, 'alt' => 'A Thunderbird used as the Starpath ace symbol'],
            'body' => [
                'A young drummer taps a slow pattern on a frame drum painted with storm lines.',
                '"Sahana used to settle route arguments before they became feuds," he says. "She can hear hesitation. If your lead creature is quick but your choice is late, she will know."',
                'He points his drumstick toward the Sky House. "Her ace is a Thunderbird. People think that means force. It means timing."'
            ],
            'choices' => [
                ['text' => 'Ask what counters the Starpath style', 'target' => 'blanket-advice'],
                ['text' => 'Head to the outer ring', 'target' => 'outer-ring'],
                ['text' => 'Return to the market path', 'target' => 'river-bend', 'restart' => true]
            ],
        ],
        'blanket-advice' => [
            'title' => 'Trading Blanket Advice',
            'image' => ['src' => $dustbisonImage, 'alt' => 'A Dustbison representing the defensive lesson of the gym'],
            'body' => [
                'At the trading blanket, an auntie weighs cedar tea, dried berries, and little beadwork charms while listening to three conversations at once.',
                '"Bring patience," she says. "The Starpath does not only test attack. It tests whether you waste strength against a steady wall."',
                'She nods toward the Bison Gate. "A Dustbison teaches that lesson better than a lecture. If you see one, do not panic just because it does not fall fast."'
            ],
            'choices' => [
                ['text' => 'Ask about the Star Walk route', 'target' => 'winter-count'],
                ['text' => 'Register at the outer ring', 'target' => 'outer-ring'],
                ['text' => 'Return to the market path', 'target' => 'river-bend', 'restart' => true]
            ],
        ],
        'outer-ring' => [
            'title' => 'Outer Ring Registration',
            'image' => ['src' => $mapImage, 'alt' => 'The Turtlestar council green and surrounding paths'],
            'body' => [
                'The registrar sits outside the Circle of Fires with a tablet, a red sash, and a cup of coffee going cold.',
                '"Name your team, then walk the Star Walk. You will hear town talk, read the story poles, face a guardian match, and only then enter Keeper Sahana\'s ring."',
                'She pauses until you stop shifting your feet. "The challenge begins when you show you can wait."'
            ],
            'choices' => [
                ['text' => 'Walk outside the circle and wait to be called', 'target' => 'winter-count'],
                ['text' => 'Cut across the circle to save time', 'target' => 'circle-correction'],
                ['text' => 'Step back and prepare at the market', 'target' => 'blanket-advice']
            ],
        ],
        'circle-correction' => [
            'title' => 'The Circle Corrects You',
            'image' => ['src' => $mapImage, 'alt' => 'The Circle of Fires viewed from the outer path'],
            'body' => [
                'Three conversations stop at once. Nobody shouts. That somehow makes it worse.',
                'The registrar lifts one finger and points to the outside path. "Fast feet are not the same as good feet. Again."',
                'A few kids pretend not to grin. You have learned the first gym lesson without losing HP.'
            ],
            'choices' => [
                ['text' => 'Take the outside path properly', 'target' => 'winter-count'],
                ['text' => 'Apologize and ask for the rule again', 'target' => 'outer-ring']
            ],
        ],
        'winter-count' => [
            'title' => 'Winter Count Lodge',
            'image' => ['src' => $prairhornImage, 'alt' => 'A Prairhorn tied to the hoofbeat route'],
            'body' => [
                'Painted hides and canvas panels line the lodge wall. Each panel records a year by one image: flood, frost, first thunder, broken bridge, returned horse, shared fire.',
                'A guide points to four poles waiting beyond the lodge. "Choose a story. Hoofbeat teaches balance. Bison teaches patience. Promise teaches timing. Thunder teaches the leader\'s ace."',
                'A fifth path, marked in black, has been tied shut with red cord.'
            ],
            'choices' => [
                ['text' => 'Take the Hoofbeat Pole path', 'target' => 'hoofbeat-gate'],
                ['text' => 'Take the Thunder Pole path', 'target' => 'thunder-pole'],
                ['text' => 'Ask about the tied shadow path', 'target' => 'shadow-warning'],
                ['text' => 'Ask about river-coil rematches', 'target' => 'river-coil']
            ],
        ],
        'hoofbeat-gate' => [
            'title' => 'Hoofbeat Gate',
            'image' => ['src' => $prairhornImage, 'alt' => 'Prairhorn waiting near the Hoofbeat Gate'],
            'body' => [
                'The Hoofbeat Pole is carved with tracks crossing and recrossing until they become a star. A Prairhorn waits beyond it, steady-eyed and square in the path.',
                'This is the opener lesson: read balance before committing force. The creature is not the strongest thing on the Starpath, but it will punish a lazy first move.',
                'The registrar watches from the grass and marks your route with a strip of red thread.'
            ],
            'choices' => [
                ['text' => 'Face the Prairhorn gate match', 'link' => $prairhornBattleUrl, 'note' => 'Gym test: balanced opener. Returns to the next route station.'],
                ['text' => 'Study the tracks and continue to Bison Gate', 'target' => 'bison-gate'],
                ['text' => 'Return to the Winter Count Lodge', 'target' => 'winter-count']
            ],
        ],
        'bison-gate' => [
            'title' => 'Bison Gate',
            'image' => ['src' => $dustbisonImage, 'alt' => 'Dustbison standing near the Bison Gate'],
            'body' => [
                'The Bison Gate is all shade, fence rails, and patient breathing. A Dustbison stands where the path narrows, its shoulders dusted blue by morning light.',
                'The lesson is not subtle. Some opponents are walls. You can throw yourself at them, or you can change angle, conserve HP, and pick the move that matters.',
                'A ranger by the gate says, "Admire with your eyes. Challenge with your head."'
            ],
            'choices' => [
                ['text' => 'Challenge the Dustbison wall', 'link' => $dustbisonBattleUrl, 'note' => 'Gym test: defensive patience. Returns to the promise station.'],
                ['text' => 'Walk the fence line and continue', 'target' => 'council-promise'],
                ['text' => 'Return to the Hoofbeat Gate', 'target' => 'hoofbeat-gate']
            ],
        ],
        'council-promise' => [
            'title' => 'Promise Pole',
            'image' => ['src' => $kachinaImage, 'alt' => 'Kachina representing the promise and timing test'],
            'body' => [
                'The Promise Pole is wrapped in ribbons, each tied with a small knot instead of a signature. A Kachina steps between the ribbons without stirring them.',
                'This test is about timing. Move too soon and you tangle yourself. Move too late and the chance is gone.',
                'A staff member lowers their voice. "Sahana likes challengers who can switch plans without breaking their promise."'
            ],
            'choices' => [
                ['text' => 'Face the Kachina promise match', 'link' => $kachinaBattleUrl, 'note' => 'Gym test: speed and timing. Returns to the leader ring.'],
                ['text' => 'Tie a clean knot and continue to the leader ring', 'target' => 'leader-ring'],
                ['text' => 'Return to Bison Gate', 'target' => 'bison-gate']
            ],
        ],
        'thunder-pole' => [
            'title' => 'Thunder Pole',
            'image' => ['src' => $thunderbirdImage, 'alt' => 'Thunderbird above the Star Walk'],
            'body' => [
                'The Thunder Pole leans into the wind. Its carvings show clouds with feet, wings with eyes, and one bright line striking a circle without breaking it.',
                'A Thunderbird wheels above the Sky House. The whole Star Walk seems to hold its breath.',
                'This is the ace lesson early: initiative changes everything. If you cannot answer speed, you must answer with planning.'
            ],
            'choices' => [
                ['text' => 'Face the Thunderbird storm match', 'link' => $thunderbirdBattleUrl, 'note' => 'Advanced test: initiative pressure. Returns to the leader ring.'],
                ['text' => 'Take the lesson and head to the leader ring', 'target' => 'leader-ring'],
                ['text' => 'Return to the Winter Count Lodge', 'target' => 'winter-count']
            ],
        ],
        'river-coil' => [
            'title' => 'River-Coil Path',
            'image' => ['src' => $hornedSerpentImage, 'alt' => 'Horned Serpent Uktena by the river-coil path'],
            'body' => [
                'The river-coil path is not part of the first badge route. The guide says it opens fully for rematches, after the leader knows you can handle warnings.',
                'Down by the water, Horned Serpent Uktena lifts its head just enough to show you that wisdom and danger can share one body.',
                'The path is optional today. Nobody will think less of you for waiting.'
            ],
            'choices' => [
                ['text' => 'Attempt the river-coil encounter', 'link' => $hornedSerpentBattleUrl, 'note' => 'Optional hard route. Returns to the leader ring.'],
                ['text' => 'Respect the warning and go to the leader ring', 'target' => 'leader-ring'],
                ['text' => 'Return to the Winter Count Lodge', 'target' => 'winter-count']
            ],
        ],
        'shadow-warning' => [
            'title' => 'Tied Shadow Path',
            'image' => ['src' => $famineImage, 'alt' => 'Famine on the shadow path'],
            'body' => [
                'The black-marked path is tied shut because some stories are for later, and some are for never proving you are brave.',
                'A guide tells you Famine has been seen near that path after cold festivals, while Alisnapor turns shortcuts into wrestling matches with the truth.',
                'The Starpath lets you look. It does not ask you to mistake risk for respect.'
            ],
            'choices' => [
                ['text' => 'Challenge the shadow warning anyway', 'link' => $famineBattleUrl, 'note' => 'Optional risk route. Returns here.'],
                ['text' => 'Look for Alisnapor tracks near the lodge', 'link' => $alisnaporBattleUrl, 'note' => 'Optional trick route. Returns to the lodge.'],
                ['text' => 'Leave the cord tied and return to the lodge', 'target' => 'winter-count']
            ],
        ],
        'leader-ring' => [
            'title' => 'Keeper Sahana\'s Ring',
            'image' => ['src' => $thunderbirdImage, 'alt' => 'Thunderbird symbol above Keeper Sahana\'s leader ring'],
            'body' => [
                'The Circle of Fires opens only after the Star Walk guide nods. People make room without hurrying. The leader ring is quiet enough to hear grass scratch against your boots.',
                'Keeper Sahana Red Sash stands beside the fire with four red threads tied at her wrist: hoofbeat, promise, dust, thunder.',
                '"A battle is a conversation held under pressure," she says. "Let us see what your side says when the wind changes."'
            ],
            'choices' => [
                ['text' => 'Challenge Keeper Sahana', 'link' => $leaderBattleUrl, 'note' => $leaderBattleNote],
                ['text' => 'Ask for one final piece of advice', 'target' => 'leader-advice'],
                ['text' => 'Step back to the Star Walk', 'target' => 'winter-count']
            ],
        ],
        'leader-advice' => [
            'title' => 'Final Advice',
            'image' => ['src' => $mapImage, 'alt' => 'The Circle of Fires before the leader battle'],
            'body' => [
                'Sahana looks over your team without touching a single creature.',
                '"If your first answer fails, do not keep shouting it. Switch. Heal if the rules allow it. Let the opponent show you what they are proud of, then answer the pride."',
                'The fire cracks once, like punctuation.'
            ],
            'choices' => [
                ['text' => 'Begin the leader battle', 'link' => $leaderBattleUrl, 'note' => $leaderBattleNote],
                ['text' => 'Return to the leader ring', 'target' => 'leader-ring']
            ],
        ],
        'badge-ceremony' => [
            'title' => 'Starfire Badge Ceremony',
            'image' => ['src' => $mapImage, 'alt' => 'The Circle of Fires after a Starpath victory'],
            'body' => [
                'When the match ends, the circle stays quiet for one more breath. Then the drummer starts the same pattern you heard at the market, only faster now.',
                'Sahana ties a red thread around a small badge worked with a star and a fork of lightning. "The Starfire Badge is carried as a promise, not a trophy."',
                'Around the outer ring, locals start talking about the battle already: the switch that mattered, the hit that almost ended it, the moment your team listened.'
            ],
            'choices' => [
                ['text' => 'Return to Turtlestar', 'link' => '?pg=stap'],
                ['text' => 'Walk the river-coil rematch path', 'target' => 'river-coil'],
                ['text' => 'Repeat the Starpath from the market', 'target' => 'river-bend', 'restart' => true]
            ],
        ],
    ],
];

$requestedScene = is_scalar($_GET['scene'] ?? null) ? trim((string)$_GET['scene']) : '';
if ($requestedScene !== '' && isset($story['nodes'][$requestedScene])) {
    $story['start'] = $requestedScene;
}

$jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
    $jsonFlags |= JSON_INVALID_UTF8_SUBSTITUTE;
}
?>

<section class="adventure stap-starpath-gym" aria-labelledby="stap-starpath-heading">
  <header class="adventure-header stap-gym-header">
    <p class="stap-gym-kicker">Sovereign Tribes of the Ancestral Plains</p>
    <h1 id="stap-starpath-heading">Turtlestar Starpath Proving Ring</h1>
    <p class="muted">A council-ring gym where town talk, story poles, guardian matches, and Keeper Sahana's leader battle teach timing, patience, and respect for the route.</p>
  </header>

  <div class="adventure-grid">
    <div class="card glass adventure-stage stap-gym-stage" id="adventure-stage" aria-live="polite">
      <div class="adventure-scene" id="adventure-scene">
        <h2 id="adventure-scene-title"></h2>
        <img id="adventure-scene-image" class="adventure-scene-image" src="" alt="" hidden>
        <div id="adventure-scene-body" class="adventure-scene-body"></div>
      </div>
      <div class="adventure-choices" id="adventure-choices" aria-label="Available choices"></div>
    </div>

    <aside class="card glass adventure-timeline stap-gym-record" aria-label="Starpath record">
      <h3>Challenge Thread</h3>
      <p class="muted">The red thread follows the same order locals expect from a proper Starpath challenge.</p>
      <ol class="stap-gym-steps">
        <li><span>Town Talk</span><strong>Hear the leader's reputation.</strong></li>
        <li><span>Outer Ring</span><strong>Register without rushing the circle.</strong></li>
        <li><span>Story Poles</span><strong>Choose the lesson your team needs.</strong></li>
        <li><span>Guardian Match</span><strong>Face a regional encounter.</strong></li>
        <li><span>Leader Ring</span><strong>Challenge Keeper Sahana.</strong></li>
        <li><span>Badge Fire</span><strong>Carry the Starfire Badge as proof.</strong></li>
      </ol>
      <h3>Route Log</h3>
      <ol id="adventure-history" class="adventure-history"></ol>
      <p class="stap-gym-return"><a class="btn" href="?pg=stap">Back to Turtlestar</a></p>
    </aside>
  </div>
</section>

<style>
.stap-starpath-gym {
  --stap-ink: #211a12;
  --stap-red: #9d2f23;
  --stap-gold: #d59c43;
  --stap-sky: #2e6d8f;
}

.stap-gym-header {
  display: grid;
  gap: 6px;
}

.stap-gym-kicker {
  margin: 0;
  color: var(--muted);
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.stap-gym-stage {
  border-color: rgba(157, 47, 35, 0.24);
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.78), rgba(255, 255, 255, 0.58)),
    linear-gradient(135deg, rgba(213, 156, 67, 0.16), rgba(46, 109, 143, 0.12));
}

.stap-gym-stage .adventure-scene-image {
  border-radius: 10px;
  border-color: rgba(157, 47, 35, 0.28);
  box-shadow: 0 12px 24px rgba(33, 26, 18, 0.12);
}

.stap-gym-record {
  border-color: rgba(157, 47, 35, 0.2);
}

.stap-gym-steps {
  display: grid;
  gap: 10px;
  margin: 0 0 14px;
  padding: 0;
  list-style: none;
}

.stap-gym-steps li {
  position: relative;
  padding: 10px 12px 10px 18px;
  border: 1px solid rgba(157, 47, 35, 0.18);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.45);
}

.stap-gym-steps li::before {
  content: "";
  position: absolute;
  left: 7px;
  top: 12px;
  bottom: 12px;
  width: 4px;
  border-radius: 999px;
  background: linear-gradient(180deg, var(--stap-red), var(--stap-gold));
}

.stap-gym-steps span {
  display: block;
  color: var(--stap-red);
  font-size: 0.76rem;
  font-weight: 900;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.stap-gym-steps strong {
  display: block;
  margin-top: 3px;
  color: var(--stap-ink);
  font-size: 0.92rem;
}

.stap-gym-return {
  margin: 8px 0 0;
}
</style>

<script id="adventure-data" type="application/json"><?= json_encode($story, $jsonFlags) ?></script>
<script type="module" src="assets/js/adventure.js"></script>
