<section class="adventure" aria-labelledby="hanako-night-heading">
  <header class="adventure-header">
    <h1 id="hanako-night-heading">Hanako Mayoi's Night School</h1>
    <p class="muted">A horror-novel adventure set in an abandoned school outside Amatera. Follow the whispers of a ghost girl who never truly left the halls.</p>
  </header>
  <div class="adventure-grid">
    <div class="card glass adventure-stage" id="adventure-stage" aria-live="polite">
      <div class="adventure-scene" id="adventure-scene">
        <h2 id="adventure-scene-title"></h2>
        <div id="adventure-scene-body" class="adventure-scene-body"></div>
      </div>
      <div id="adventure-flash" class="adventure-flash" aria-live="polite" hidden></div>
      <div class="adventure-choices" id="adventure-choices" aria-label="Available choices"></div>
    </div>
    <aside class="card glass adventure-timeline" aria-label="Night log">
      <h3>Night Log</h3>
      <p class="muted">Each choice records how you found her story. Even when the paths change, the ghost waits.</p>
      <ol id="adventure-history" class="adventure-history"></ol>
    </aside>
  </div>
</section>

<!-- Local-time gate: allow only 20:00-07:59 local time -->
<script>
  (function gateHanakoNightWalk() {
    const now = new Date();
    const hour = now.getHours();
    const allowed = hour >= 20 || hour < 8;

    if (allowed) {
      return;
    }

    window.__ADVENTURE_LOCKED__ = true;
    const section = document.querySelector('.adventure');
    if (!section) return;

    const prevPos = getComputedStyle(section).position;
    if (prevPos === 'static') section.style.position = 'relative';

    const overlay = document.createElement('div');
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.style.position = 'absolute';
    overlay.style.inset = '0';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.backdropFilter = 'blur(4px)';
    overlay.style.background = 'rgba(0,0,0,0.6)';
    overlay.style.zIndex = '9999';
    overlay.style.pointerEvents = 'auto';

    overlay.innerHTML = `
      <div class="card glass" style="max-width: 560px; padding: 1.25rem; text-align: center;">
        <h2 style="margin: 0 0 .25rem 0;">The School Sleeps</h2>
        <p class="muted" style="margin: 0 0 .75rem 0;">
          The story only opens from 20:00 to 08:00 your local time.
        </p>
        <p style="margin: 0 0 .5rem 0;">
          It's currently <strong>${now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</strong> for you.
        </p>
        <p class="muted" style="margin: 0;">(This gate follows your device's clock.)</p>
        <br>
        <a class="btn" href="?pg=yamanokubo">Return to Yamanokubo</a>
      </div>
    `;

    section.style.pointerEvents = 'none';
    overlay.style.pointerEvents = 'auto';
    section.appendChild(overlay);
  })();
</script>

<script id="adventure-data" type="application/json">
{
  "start": "village-dusk",
  "nodes": {
    "village-dusk": {
      "title": "Village at Dusk",
      "body": [
        "The village sits a short ride from Amatera's capital gates. Lanterns flicker on porches, and the air smells of rain and rice steam.",
        "Locals warn of an abandoned school on the hill. They whisper of a ghost girl, Hanako Mayoi, who appears when the school bell tries to ring by itself.",
        "A fisherman mutters, \"If you go, go after sundown. She doesn't walk in daylight.\""
      ],
      "choices": [
        { "text": "Head toward the school immediately", "target": "school-gate" },
        { "text": "Ask the elders for the old story", "target": "elders-story" },
        { "text": "Visit the roadside shrine for clues", "target": "roadside-shrine" }
      ]
    },
    "elders-story": {
      "title": "Tea With the Elders",
      "body": [
        "In a low house, elders pour hot tea and speak in careful turns. The school was shut after a flood cracked the foundation.",
        "\"Hanako Mayoi\" is the name that clings to the halls, they say, though it might not be her true name. \"She is the one who got lost between the bell and the door.\""
      ],
      "choices": [
        { "text": "Follow the hill road with their lantern", "target": "school-gate" },
        { "text": "Ask about the bell itself", "target": "bell-legend" }
      ]
    },
    "bell-legend": {
      "title": "The Bell's Lament",
      "body": [
        "The village bellkeeper says the school bell rings without wind when storms press low. \"She's calling herself home,\" he says.",
        "He points to a cracked service road that ends behind the classrooms. \"Follow the bell. It knows her.\""
      ],
      "choices": [
        { "text": "Take the service road", "target": "rear-entrance" },
        { "text": "Circle back to the main gate", "target": "school-gate" }
      ]
    },
    "roadside-shrine": {
      "title": "Roadside Shrine",
      "body": [
        "A stone snail sits atop the shrine slab, polished by hands that left tiny offerings. Someone has left a bouquet of white irises.",
        "An old prayer slip mentions a local snail goddess, a gentle guardian of slow travelers. It ends with a date: Mother's Day, the year of the storm."
      ],
      "choices": [
        { "text": "Offer incense and ask for protection", "target": "snail-goddess" },
        { "text": "Take the hill path to the school", "target": "school-gate" }
      ]
    },
    "snail-goddess": {
      "title": "The Snail Goddess Tale",
      "body": [
        "A shrine attendant steps from the shadows. \"She was a guardian spirit,\" the attendant says. \"She crossed the road on Mother's Day and was struck by a carriage. She returned as a yurei with her shell cracked.\"",
        "\"If you see Hanako Mayoi, ease her weariness. She may leave you a keepsake, but do not rush her pace.\""
      ],
      "choices": [
        { "text": "Carry the tale to the school", "target": "school-gate" },
        { "text": "Take the service road behind the school", "target": "rear-entrance" }
      ]
    },
    "school-gate": {
      "title": "Abandoned School Gate",
      "body": [
        "The gate is chained, but the fence has a gap where ivy swallows metal. Windows stare like dark eyes.",
        "Inside, the courtyard is a pond of moonlight. The bell tower leans as if listening."
      ],
      "choices": [
        { "text": "Cross the courtyard to the main doors", "target": "main-hall" },
        { "text": "Climb the bell tower stairs", "target": "bell-tower" },
        { "text": "Slip around to the rear entrance", "target": "rear-entrance" }
      ]
    },
    "rear-entrance": {
      "title": "Rear Entrance",
      "body": [
        "A broken delivery bay opens into the gym. The floorboards creak with old rain.",
        "A chalk line on the wall reads: \"Hanako Mayoi waited here when the bell wouldn't ring.\""
      ],
      "choices": [
        { "text": "Follow the chalk line to the hallway", "target": "main-hall" },
        { "text": "Search the gym stage curtains", "target": "stage-curtain" }
      ]
    },
    "stage-curtain": {
      "title": "Stage Curtain",
      "body": [
        "Behind the curtain rests a tiny altar, candles melted into shells. The air smells of wet stone and sweet rice.",
        "A whisper brushes your ear: \"Slow down.\""
      ],
      "choices": [
        { "text": "Answer the whisper and step into the hallway", "target": "main-hall" },
        { "text": "Bow and promise to be gentle", "target": "gentle-promise" }
      ]
    },
    "gentle-promise": {
      "title": "Gentle Promise",
      "body": [
        "You bow to the shell altar. A chill rolls across the gym, then fades into a calm, patient quiet.",
        "Footsteps appear in the dust, leading you toward the lit hallway."
      ],
      "choices": [
        { "text": "Follow the new footprints", "target": "main-hall" }
      ]
    },
    "main-hall": {
      "title": "Main Hallway",
      "body": [
        "The hallway stretches like a tunnel of lockers. Moonlight pools at the far end, where a classroom door stands ajar.",
        "Every few steps, a damp footprint appears beside your own. The footsteps never quite catch up."
      ],
      "choices": [
        { "text": "Open the classroom door", "target": "classroom" },
        { "text": "Follow the footprints to the stairwell", "target": "stairwell" }
      ]
    },
    "bell-tower": {
      "title": "Bell Tower",
      "body": [
        "The bell is cracked, bound in ropes to stop its sway. When you touch it, a cold vibration hums up your wrist.",
        "Below, a pale figure crosses the courtyard. She glances up, then vanishes into the main hall."
      ],
      "choices": [
        { "text": "Chase her into the hall", "target": "main-hall" },
        { "text": "Climb down slowly and follow the echo", "target": "stairwell" }
      ]
    },
    "stairwell": {
      "title": "Stairwell Shadows",
      "body": [
        "The stairwell smells of damp plaster. Each step creaks like a whisper forming words.",
        "A glow gathers at the landing, where a girl in a sailor uniform waits with her head bowed."
      ],
      "choices": [
        { "text": "Speak her name softly", "target": "hanako-meet" },
        { "text": "Offer the snail shrine tale", "target": "snail-truth" }
      ]
    },
    "classroom": {
      "title": "Empty Classroom",
      "body": [
        "Dusty desks face the chalkboard. A single seat is cleaned, as if someone sits there every night.",
        "The chalk reads: \"Hanako Mayoi. Not lost, just slow.\""
      ],
      "choices": [
        { "text": "Sit in the cleaned seat", "target": "hanako-meet" },
        { "text": "Call out to the hallway", "target": "stairwell" }
      ]
    },
    "snail-truth": {
      "title": "Truth of the Snail Goddess",
      "body": [
        "<div class=\"scene-media\"><figure class=\"scene-portrait\"><img src=\"images/yurei_f_realistic.webp\" alt=\"Hanako Mayoi, the yurei of the night school\" loading=\"lazy\" decoding=\"async\"><figcaption class=\"muted\">A gentle yurei with a cracked shell charm.</figcaption></figure></div>",
        "You tell her about the shrine: the snail goddess who crossed the road on Mother's Day, struck and returned as a yurei.",
        "She lifts her face. Her eyes are calm, the gentlest blue. \"Hanako Mayoi is a name that helps people remember me,\" she says. \"I was slow. I still am.\""
      ],
      "choices": [
        { "text": "Promise to ease her weariness", "target": "ease-weariness" },
        { "text": "Ask how she wants to be remembered", "target": "hanako-meet" }
      ]
    },
    "hanako-meet": {
      "title": "Hanako Mayoi",
      "body": [
        "<div class=\"scene-media\"><figure class=\"scene-portrait\"><img src=\"images/yurei_f_realistic.webp\" alt=\"Hanako Mayoi, ghost girl of the abandoned school\" loading=\"lazy\" decoding=\"async\"><figcaption class=\"muted\">Hanako Mayoi appears in the stairwell glow.</figcaption></figure></div>",
        "She steps from the shadow, a ghost girl with a cracked shell charm at her belt. The hallway quiets, waiting.",
        "\"I wander when the bell forgets me,\" Hanako whispers. \"Thank you for coming so late.\""
      ],
      "choices": [
        { "text": "Ask how to ease her weariness", "target": "ease-weariness" },
        { "text": "Listen in silence", "target": "quiet-closure" }
      ]
    },
    "ease-weariness": {
      "title": "Easing Weariness",
      "body": [
        "You offer a slow bow and a promise to tell her story kindly. The air warms, and Hanako smiles as if a bell finally rang.",
        "She presses something cool into your hand, a keepsake from her careful path."
      ],
      "choices": [
        { "text": "Accept the keepsake", "target": "quiet-closure", "rewardNotePool": [
          "a shell-bead charm threaded with river twine",
          "a chalk nub wrapped in red cord",
          "a silver bell clapper that never rings",
          "a smooth stone painted with a slow spiral",
          "a pressed iris tucked in a book of names"
        ] }
      ]
    },
    "quiet-closure": {
      "title": "Quiet Closure",
      "body": [
        "Hanako Mayoi fades back into the stairwell. The bell does not ring, but the air feels lighter than before.",
        "When you leave the school, the village lanterns are still lit, and the night feels less lonely."
      ],
      "choices": [
        { "text": "Return to Yamanokubo", "target": "village-dusk", "link": "?pg=yamanokubo" }
      ]
    }
  }
}
</script>

<script type="module" src="assets/js/adventure.js"></script>
