<?php
require_login();
require_once __DIR__.'/../lib/map_unlocks.php';

$user = current_user();
if (!$user || !has_map_unlock((int)$user['id'], 'stillwater_hollow')) {
    echo '<div class="card glass"><h2>Road Not Found</h2><p class="muted">The repaired URB roads do not lead to Stillwater Creek yet.</p><p><a class="btn" href="?pg=urb">Back to Meridian Arc</a></p></div>';
    return;
}

function stcr_adventure_region_id(): int {
    return (int)q(
        "SELECT region_id
           FROM regions
          WHERE region_name = ?
          ORDER BY region_id
          LIMIT 1",
        ['Stillwater Creek']
    )->fetchColumn();
}

function stcr_adventure_first_image(array $paths): string {
    foreach ($paths as $path) {
        if (is_file(__DIR__.'/../'.$path)) {
            return $path;
        }
    }

    return 'images/maps/harmontide-urb.webp';
}

function stcr_adventure_battle_url(int $regionId, string $speciesName = '', string $returnTo = 'index.php?pg=stcr-adventure'): string {
    $query = [
        'pg' => 'battle_minigame',
        'battle' => 'wild',
        'region_id' => $regionId,
        'return_to' => $returnTo,
    ];

    $speciesName = trim($speciesName);
    if ($regionId > 0 && $speciesName !== '') {
        $speciesId = (int)q(
            "SELECT species_id
               FROM pet_species
              WHERE region_id = ?
                AND species_name = ?
              LIMIT 1",
            [$regionId, $speciesName]
        )->fetchColumn();

        if ($speciesId > 0) {
            $query['species_id'] = $speciesId;
        }
    }

    return 'index.php?'.http_build_query($query);
}

$stillwaterRegionId = stcr_adventure_region_id();
$wellBattleUrl = stcr_adventure_battle_url($stillwaterRegionId);
$bloodhoundBattleUrl = stcr_adventure_battle_url($stillwaterRegionId, 'Bloodhound');
$maryBattleUrl = stcr_adventure_battle_url($stillwaterRegionId, 'Mary', 'index.php?pg=stillwater-hollow');

$wellImage = stcr_adventure_first_image([
    'images/maps/harmontide-stillwatercreek.webp',
    'images/maps/harmontide-stillwater-hollow.png',
    'images/maps/harmontide-urb.webp',
]);
$bloodhoundImage = stcr_adventure_first_image([
    'images/creatures/bloodhound_f_brown.webp',
    $wellImage,
]);
$maryImage = stcr_adventure_first_image([
    'images/creatures/mary_f_blue.webp',
    'images/creatures/mary_f_blue.png',
    $wellImage,
]);

$story = [
    'start' => 'town-well',
    'nodes' => [
        'town-well' => [
            'title' => 'Stillwater Town Well',
            'image' => ['src' => $wellImage, 'alt' => 'Stillwater Hollow map and the road toward the old town well'],
            'body' => [
                'The town square is empty, but every porch light is on. They shine through curtains that never move, making the whole hollow look awake behind closed eyes.',
                'For generations the well fed every home in Stillwater Creek. Then the tests in the hills, the dumping, and the quiet repairs no one filed in any ledger turned clear water into a slow gray sludge.',
                'The bucket rises on its own. Something inside the well knocks once from below, like a polite answer.'
            ],
            'choices' => [
                ['text' => 'Read the notice nailed to the church door', 'target' => 'church-notice'],
                ['text' => 'Check the pump house before descending', 'target' => 'pump-house'],
                ['text' => 'Climb down the maintenance rungs', 'target' => 'rung-descent']
            ],
        ],
        'church-notice' => [
            'title' => 'Notice of Water Safety',
            'image' => ['src' => $wellImage, 'alt' => 'A road map of Stillwater Hollow with the well district marked'],
            'body' => [
                'The notice is typed in six different fonts, each line stamped APPROVED by a different office. The newest stamp is older than the newest stain.',
                'DO NOT DRINK FROM PRIVATE WELLS. DO NOT TOUCH SLUDGE. DO NOT RESPOND IF A FAMILIAR VOICE CALLS FROM BELOW.',
                'At the bottom someone has written, in careful pencil: "Mary is thirsty. Mary is everyone."'
            ],
            'choices' => [
                ['text' => 'Pocket the notice and go to the pump house', 'target' => 'pump-house'],
                ['text' => 'Start the descent anyway', 'target' => 'rung-descent'],
                ['text' => 'Return to the Stillwater map', 'link' => '?pg=stillwater-hollow']
            ],
        ],
        'pump-house' => [
            'title' => 'Checkpoint: Pump House',
            'image' => ['src' => $bloodhoundImage, 'alt' => 'A Stillwater creature shape waiting near the pump house'],
            'body' => [
                'Inside the pump house, pressure gauges twitch though the pipes are dry. Rows of old filters hang from hooks, packed with black grit and pale mineral fur.',
                'A maintenance log lists creek tests, failed filters, missing families, and finally only one repeated line: "The water remembers the shape of us."',
                'Something claws behind the brick utility door. Its breathing is too fast for a machine and too patient for an animal.'
            ],
            'choices' => [
                ['text' => 'Force the utility door and fight what waits there', 'link' => $bloodhoundBattleUrl, 'note' => 'Boss fight: Bloodhound from the Stillwater Creek encounter table.'],
                ['text' => 'Open the sluice valve to lower the well water', 'target' => 'sluice-valve'],
                ['text' => 'Leave the pump house and descend the well', 'target' => 'rung-descent']
            ],
        ],
        'sluice-valve' => [
            'title' => 'The Sluice Valve',
            'image' => ['src' => $wellImage, 'alt' => 'Stillwater Creek channels and sluice paths'],
            'body' => [
                'The valve wheel turns with a wet cough. Somewhere below, sludge drops a few feet and exposes a ring of old brickwork.',
                'For a moment the well exhales. The air smells like pennies, pond scum, and a hospital hallway after the lights go out.',
                'A new route opens below the rungs. A checkpoint bell rings once, though the bell rope was cut years ago.'
            ],
            'choices' => [
                ['text' => 'Descend to the exposed brick ring', 'target' => 'ring-gallery'],
                ['text' => 'Search the pump house shelves', 'target' => 'filter-lab'],
                ['text' => 'Return to the square', 'target' => 'town-well', 'restart' => true]
            ],
        ],
        'rung-descent' => [
            'title' => 'Checkpoint: Rung Descent',
            'image' => ['src' => $wellImage, 'alt' => 'Dark descent into the Stillwater town well'],
            'body' => [
                'The rungs are cold through your gloves. Halfway down, family names are carved into the brick, then scratched out, then carved again in smaller handwriting.',
                'The sludge below gives off a dim green sheen. Faces appear in it only when you look away, each one wearing the expression of someone listening at a bedroom door.',
                'A side arch waits just above the waterline. Deeper still, a drain tunnel hums in a voice that almost knows yours.'
            ],
            'choices' => [
                ['text' => 'Step into the side arch', 'target' => 'ring-gallery'],
                ['text' => 'Drop to the drain tunnel', 'target' => 'choir-drain'],
                ['text' => 'Climb back to the pump house', 'target' => 'pump-house']
            ],
        ],
        'ring-gallery' => [
            'title' => 'Checkpoint: The Ring Gallery',
            'image' => ['src' => $wellImage, 'alt' => 'Old brick ring gallery under the Stillwater well'],
            'body' => [
                'A circular walkway wraps the shaft. Rusted doors lead into cistern rooms, each marked with painted numbers and faded schoolhouse stickers.',
                'The walls pulse with condensation. Every drip lands in rhythm: two taps, pause, one tap. It sounds like a town knocking from inside its own bones.',
                'A chalk arrow points to the old testing room. Another points to the nursery cistern. Someone has crossed out the word EXIT.'
            ],
            'choices' => [
                ['text' => 'Enter the testing room', 'target' => 'filter-lab'],
                ['text' => 'Follow the nursery cistern signs', 'target' => 'nursery-cistern'],
                ['text' => 'Listen through the crossed-out exit', 'target' => 'listen-hole']
            ],
        ],
        'filter-lab' => [
            'title' => 'Checkpoint: Testing Room',
            'image' => ['src' => $wellImage, 'alt' => 'A map view used as a placeholder for the underground testing room'],
            'body' => [
                'Lab benches sag under sample jars. Some are labeled CREEK, some WELL, some CHILDREN, and one simply says SORRY.',
                'A slide projector clicks by itself. Each blank frame shows a cleaner town for half a second before the image buckles and runs like wet paint.',
                'Something moves between the shelves, dragging a chain of sample tags. This is not a named guardian. This is whatever the encounter table sends up from Stillwater Creek tonight.'
            ],
            'choices' => [
                ['text' => 'Trigger a wild well encounter', 'link' => $wellBattleUrl, 'note' => 'Random wild fight from Stillwater Creek encounters.'],
                ['text' => 'Take the stair marked MUNICIPAL SUPPLY', 'target' => 'choir-drain'],
                ['text' => 'Retreat to the ring gallery', 'target' => 'ring-gallery']
            ],
        ],
        'nursery-cistern' => [
            'title' => 'The Nursery Cistern',
            'image' => ['src' => $wellImage, 'alt' => 'Stillwater Creek map used for the nursery cistern scene'],
            'body' => [
                'The cistern ceiling is painted with nursery stars. They have been repainted so many times the constellations sag in thick layers.',
                'Tiny chairs sit in a circle around a dry drain. Each chair has a cup. Each cup is full. The water in them does not ripple, even when your steps shake the floor.',
                'A lullaby leaks from the drain in a dozen careful voices. They are not singing to you. They are practicing.'
            ],
            'choices' => [
                ['text' => 'Pour the cups into the dry drain', 'target' => 'choir-drain'],
                ['text' => 'Leave the cups untouched', 'target' => 'listen-hole'],
                ['text' => 'Return to the ring gallery', 'target' => 'ring-gallery']
            ],
        ],
        'listen-hole' => [
            'title' => 'The Listening Hole',
            'image' => ['src' => $wellImage, 'alt' => 'A dark Stillwater map path leading away from the well'],
            'body' => [
                'Beyond the crossed-out exit is a brick wall with a round hole at mouth height. Warm air breathes through it.',
                'When you lean close, the town speaks from the other side: a grocer, a teacher, a child, an old woman, all saying the same sentence with different teeth.',
                '"We only noticed when there was nothing left in us that had not noticed first."'
            ],
            'choices' => [
                ['text' => 'Answer the voices', 'target' => 'echo-verdict'],
                ['text' => 'Seal the hole with a filter pad', 'target' => 'filter-lab'],
                ['text' => 'Back away toward the nursery cistern', 'target' => 'nursery-cistern']
            ],
        ],
        'echo-verdict' => [
            'title' => 'Echo Verdict',
            'image' => ['src' => $wellImage, 'alt' => 'Stillwater Hollow map under low light'],
            'body' => [
                'You tell the voices you are going deeper. The hole goes silent, then sighs with a relief so human it hurts.',
                'A hidden latch opens beneath your palm. The crossed-out exit was never an exit. It is a confession chute.',
                'Below, the choir drain waits with all its mouths closed.'
            ],
            'choices' => [
                ['text' => 'Slide into the confession chute', 'target' => 'choir-drain'],
                ['text' => 'Take one more breath in the ring gallery', 'target' => 'ring-gallery']
            ],
        ],
        'choir-drain' => [
            'title' => 'Checkpoint: Choir Drain',
            'image' => ['src' => $wellImage, 'alt' => 'Stillwater Creek map as the descent reaches the central drain'],
            'body' => [
                'The drain tunnel widens into a brick nave. Pipes descend like organ tubes, each one leaking a different note.',
                'The sludge here is thick enough to hold footprints. Some tracks are human. Some are hoofed. Some are broad, dragging, and uncertain, like a body still voting on its shape.',
                'At the far end, an iron hatch bears one word in careful municipal paint: MARY.'
            ],
            'choices' => [
                ['text' => 'Search the side pipes before the hatch', 'target' => 'side-pipes'],
                ['text' => 'Open the hatch marked MARY', 'target' => 'heart-cistern'],
                ['text' => 'Climb back toward the testing room', 'target' => 'filter-lab']
            ],
        ],
        'side-pipes' => [
            'title' => 'Side Pipes',
            'image' => ['src' => $wellImage, 'alt' => 'Map view standing in for side pipes beneath Stillwater'],
            'body' => [
                'You find lunchboxes, wedding rings, pet collars, and keys bundled in waterproof evidence bags. The town kept proof, then hid the room that proved it.',
                'A warning is carved above the last pipe: SHE LEARNED OUR NAMES FROM THE WATER.',
                'From the hatch, something massive shifts with the slow patience of a sleeping courthouse.'
            ],
            'choices' => [
                ['text' => 'Take the evidence tags and approach the hatch', 'target' => 'heart-cistern'],
                ['text' => 'Trigger one last wild well encounter', 'link' => $wellBattleUrl, 'note' => 'Random wild fight from Stillwater Creek encounters.'],
                ['text' => 'Return to the choir drain', 'target' => 'choir-drain']
            ],
        ],
        'heart-cistern' => [
            'title' => 'The Heart Cistern',
            'image' => ['src' => $maryImage, 'alt' => 'Mary, the final Stillwater well boss'],
            'body' => [
                'The hatch opens into a vast cistern where the town well, creek bed, and old test runoff all meet. The surface below is not water anymore. It is a breathing mirror.',
                'Mary rises from it: a mass of flesh, pipe roots, borrowed voices, and old thirst. She does not roar. She greets you with the voices of everyone who ever said the water tasted fine.',
                'The sludge around her pulls into a crown shape. The final checkpoint bell rings from inside her.'
            ],
            'choices' => [
                ['text' => 'Speak Mary\'s name and begin the final fight', 'target' => 'mary-door'],
                ['text' => 'Throw the church notice into the sludge', 'target' => 'mary-remembers'],
                ['text' => 'Retreat to the choir drain', 'target' => 'choir-drain']
            ],
        ],
        'mary-remembers' => [
            'title' => 'Mary Remembers',
            'image' => ['src' => $maryImage, 'alt' => 'Mary waiting below the Stillwater well'],
            'body' => [
                'The notice lands on the sludge and does not sink. All those approved stamps glow for one faint second.',
                'Mary reads them with a hundred quiet throats. Her borrowed faces tilt, not angry, not forgiving. Just aware.',
                'A path of dry brick appears across the cistern. It leads directly to her.'
            ],
            'choices' => [
                ['text' => 'Cross the dry brick path', 'target' => 'mary-door'],
                ['text' => 'Use the opening to flee upward', 'link' => '?pg=stillwater-hollow']
            ],
        ],
        'mary-door' => [
            'title' => 'Final Boss: Mary',
            'image' => ['src' => $maryImage, 'alt' => 'Mary, a mass of flesh and sludge beneath Stillwater Creek'],
            'body' => [
                'Mary lowers herself until her many voices become one wet whisper: "Drink, and be town."',
                'The well walls close like an audience leaning in. There is nowhere deeper to go.',
                'This is the bottom of Stillwater Creek.'
            ],
            'choices' => [
                ['text' => 'Fight Mary', 'link' => $maryBattleUrl, 'note' => 'Final boss fight: Mary from the Stillwater Creek encounter table.']
            ],
        ],
    ],
];
?>

<section class="adventure" aria-labelledby="stcr-adventure-heading">
  <header class="adventure-header">
    <h1 id="stcr-adventure-heading">Stillwater Creek: Bottom of the Well</h1>
    <p class="muted">Descend beneath the town well, trace the poisoned water back through old tests and municipal silence, and decide how much of Stillwater is still alive down there.</p>
  </header>
  <div class="adventure-grid">
    <div class="card glass adventure-stage" id="adventure-stage" aria-live="polite">
      <div class="adventure-scene" id="adventure-scene">
        <h2 id="adventure-scene-title"></h2>
        <img id="adventure-scene-image" class="adventure-scene-image" src="" alt="" hidden>
        <div id="adventure-scene-body" class="adventure-scene-body"></div>
      </div>
      <div id="adventure-flash" class="adventure-flash" aria-live="polite" hidden></div>
      <div class="adventure-choices" id="adventure-choices" aria-label="Available choices"></div>
    </div>
    <aside class="card glass adventure-timeline" aria-label="Well descent log">
      <h3>Descent Log</h3>
      <p class="muted">Each checkpoint marks how far below the square you have gone, and how much of the town has started answering back.</p>
      <ol id="adventure-history" class="adventure-history"></ol>
    </aside>
  </div>
</section>

<script id="adventure-data" type="application/json"><?= json_encode($story, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>
<script type="module" src="assets/js/adventure.js"></script>
