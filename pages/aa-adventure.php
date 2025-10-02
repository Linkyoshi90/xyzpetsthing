<section class="adventure">
  <header class="adventure-header">
    <h1>Whispers of Heliodora</h1>
    <p class="muted">A pocket-quest set in the sunken city of Heliodora. Meet an ancient lamia, brave the catacombs, and chase a treasure that may not want to be found.</p>
  </header>
  <div class="adventure-grid">
    <div class="card glass adventure-stage" id="adventure-stage" aria-live="polite">
      <div class="adventure-scene" id="adventure-scene">
        <h2 id="adventure-scene-title"></h2>
        <div id="adventure-scene-body" class="adventure-scene-body"></div>
      </div>
      <div class="adventure-choices" id="adventure-choices" aria-label="Available choices"></div>
    </div>
    <aside class="card glass adventure-timeline" aria-label="Journey log">
      <h3>Journey Log</h3>
      <p class="muted">Every choice you make is recorded here so you can retrace your steps or share the tale with friends.</p>
      <ol id="adventure-history" class="adventure-history"></ol>
    </aside>
  </div>
</section>

<!-- (1) Local-time gate: allow only 20:00-20:59 local time -->
<script>
  (function gateAdventureByLocalTime() {
    const now = new Date();
    const hour = now.getHours();
    const allowed = (hour === 20); // 20:00-20:59

    if (!allowed) {
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
      overlay.style.background = 'rgba(0,0,0,0.55)';
      overlay.style.zIndex = '9999';
      overlay.style.pointerEvents = 'auto';

      overlay.innerHTML = `
        <div class="card glass" style="max-width: 560px; padding: 1.25rem; text-align: center;">
          <h2 style="margin: 0 0 .25rem 0;">The Ruins Are Asleep</h2>
          <p class="muted" style="margin: 0 0 .75rem 0;">
            Return at a certain time at night to enter.
          </p>
          <p style="margin: 0 0 .5rem 0;">
            It's currently <strong>${now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</strong> for you.
          </p>
          <p class="muted" style="margin: 0;">(The gate follows your device's clock.)</p>
          <br>
          <a class="btn" href="?pg=aa">Back to Heliodora</a>
        </div>
      `;

      section.style.pointerEvents = 'none';
      overlay.style.pointerEvents = 'auto';
      section.appendChild(overlay);
    }
  })();
</script>

<script id="adventure-data" type="application/json">
{
  "start": "sun-gate",
  "nodes": {
    "sun-gate": {
      "title": "Sun Gate Approach",
      "body": [
        "<div class=\"scene-media\"><figure class=\"scene-portrait\"><img src=\"images/lamia_f_oracle.webp\" alt=\"Sythria of the Long Sun, a lamia oracle\" loading=\"lazy\" decoding=\"async\"><figcaption class=\"muted\">Sythria watches from the ruins.</figcaption></figure></div>",
        "You crest a dune to find the tumbled arches of Heliodora, a city once mirrored in gold. Heat shimmers where streets used to be.",
        "Among the fallen columns, a shape coils and straightens-an old lamia with eyes like coins left too long in the sea."
      ],
      "choices": [
        { "text": "Greet the lamia", "target": "lamia-hello" },
        { "text": "Skirt the ruins and scout first", "target": "outer-rim" },
        { "text": "Offer water and sit to listen", "target": "lamia-parley" }
      ]
    },
    "lamia-hello": {
      "title": "Meeting the Elder",
      "body": [
        "<div class=\"scene-media\"><figure class=\"scene-portrait\"><img src=\"images/lamia_f_oracle.webp\" alt=\"Portrait of Sythria, the lamia oracle\" loading=\"lazy\" decoding=\"async\"><figcaption class=\"muted\">Sythria of the Long Sun</figcaption></figure></div>",
        "She names herself Sythria of the Long Sun. Her voice is a sand-soft rattle. 'Treasure sleeps below the catacombs,' she says, 'but the city remembers who takes and who returns.'",
        "She taps a bronze token against your palm-warm, thrumming with a tiny heartbeat."
      ],
      "choices": [
        { "text": "Ask about the token", "target": "sun-token" },
        { "text": "Request a guide to the catacombs", "target": "gatehouse" },
        { "text": "Refuse politely and explore alone", "target": "outer-rim" }
      ]
    },
    "lamia-parley": {
      "title": "Tea in the Shade",
      "body": [
        "<div class=\"scene-media\"><figure class=\"scene-portrait\"><img src=\"images/lamia_f_oracle.webp\" alt=\"Sythria sharing water under an obelisk\" loading=\"lazy\" decoding=\"async\"><figcaption class=\"muted\">A hymn older than the sand.</figcaption></figure></div>",
        "You share water under a leaning obelisk. Sythria hums an old hymn; the obelisk hums back.",
        "'The treasure is a promise made heavy,' she murmurs. 'Down there, promises grow teeth.'"
      ],
      "choices": [
        { "text": "Swear to return what isn't yours", "target": "oathbound" },
        { "text": "Ask for a shortcut to the catacombs", "target": "gatehouse" }
      ]
    },
    "outer-rim": {
      "title": "Outer Rim of Ruins",
      "body": [
        "Wind fingers through cracked mosaics. Jackal-birds flit between lintels. You find two paths: a collapsed atrium with a glinting ladder, and a dry aqueduct tunneled by roots.",
        "Faint singing rises from below-too slow for wind, too fast for stone."
      ],
      "choices": [
        { "text": "Descend the atrium ladder", "target": "gatehouse" },
        { "text": "Crawl the aqueduct", "target": "aqueduct" },
        { "text": "Return to speak with Sythria", "target": "lamia-hello" }
      ]
    },
    "sun-token": {
      "title": "Token of the Long Sun",
      "body": [
        "<div class=\"scene-media\"><figure class=\"scene-portrait\"><img src=\"images/lamia_f_oracle.webp\" alt=\"Sythria presenting a bronze token\" loading=\"lazy\" decoding=\"async\"><figcaption class=\"muted\">The token thrums in your palm.</figcaption></figure></div>",
        "The bronze bears a spiral sun on one side and a coiled tail on the other. When you flip it, the air tastes of citrus and dust.",
        "'It will open what light has forgotten,' Sythria says. 'But it opens you, too.'"
      ],
      "choices": [
        { "text": "Accept and wear it", "target": "oathbound", "note": "May unlock sun-sealed doors." },
        { "text": "Pocket it without a promise", "target": "gatehouse" },
        { "text": "Decline the gift", "target": "outer-rim" }
      ]
    },
    "oathbound": {
      "title": "Oath to the City",
      "body": [
        "You whisper an oath to return what resists being taken. The token warms, then cools. Sythria nods, satisfied.",
        "'Then Heliodora will spare you a warning before a bite,' she says."
      ],
      "choices": [
        { "text": "Enter the catacombs with Sythria", "target": "gatehouse" },
        { "text": "Make one last survey topside", "target": "outer-rim" }
      ]
    },
    "gatehouse": {
      "title": "Collapsed Gatehouse",
      "body": [
        "A cracked lintel frames a stair spiraling into cool dusk. A mural shows a sun shedding scales like petals.",
        "Sythria traces a sigil; stone sighs open. 'Catacombs,' she whispers. 'Keep your promises nearby.'"
      ],
      "choices": [
        { "text": "Descend into the catacombs", "target": "threshold" },
        { "text": "Ask Sythria to lead", "target": "lamia-guide" }
      ]
    },
    "aqueduct": {
      "title": "Dry Aqueduct",
      "body": [
        "Roots rib the ceiling, etched with tally-marks. Someone counted their courage here. The tunnel forks: left smells of citrus; right of iron.",
        "From the left, a warm draft flickers your torch."
      ],
      "choices": [
        { "text": "Follow the citrus draft", "target": "sun-vault" },
        { "text": "Take the iron-scented path", "target": "echo-hall" },
        { "text": "Backtrack to the gatehouse", "target": "gatehouse" }
      ]
    },
    "lamia-guide": {
      "title": "Sythria Leads",
      "body": [
        "<div class=\"scene-media\"><figure class=\"scene-portrait\"><img src=\"images/lamia_f_oracle.webp\" alt=\"Sythria guiding through the catacombs\" loading=\"lazy\" decoding=\"async\"><figcaption class=\"muted\">Follow the heartbeat pace.</figcaption></figure></div>",
        "Her scales whisper over the steps. She sets a pace like a heartbeat: da-dum, da-dum. 'When the city tests you,' she says, 'answer with truth or song.'",
        "The torchlight puddles into the first chamber."
      ],
      "choices": [
        { "text": "Ask what the tests are", "target": "tests" },
        { "text": "Proceed in silence", "target": "threshold" }
      ]
    },
    "tests": {
      "title": "Lessons in Stone",
      "body": [
        "'Three keepers: The Echo, The Tally, The Sunless. Each guards a door; only one hides greed well.'",
        "She smiles thinly. 'The treasure is a mirror. Most see what they bring.'"
      ],
      "choices": [
        { "text": "Head to the catacomb threshold", "target": "threshold" },
        { "text": "Divert through maintenance tunnels", "target": "service-way" }
      ]
    },
    "threshold": {
      "title": "Catacomb Threshold",
      "body": [
        "A door of bone-white limestone shows three reliefs: a mouth, a field of cuts, a blank circle. The token hums, picking a pitch near the blank circle.",
        "Whispers pool at your ankles like cool water."
      ],
      "choices": [
        { "text": "Knock on the mouth (Echo)", "target": "echo-hall" },
        { "text": "Touch the cuts (Tally)", "target": "tally-ward" },
        { "text": "Press the token to the blank (Sunless)", "target": "sun-vault" }
      ]
    },
    "echo-hall": {
      "title": "Hall of Echoes",
      "body": [
        "Every sound returns wrong-brighter, braver. A pedestal holds a cracked bowl. An inscription reads: 'Fill with what you won't miss.'",
        "The echoes chant: 'Coin! Song! Breath!'"
      ],
      "choices": [
        { "text": "Offer a coin", "target": "coin-path" },
        { "text": "Offer a song", "target": "song-path" },
        { "text": "Hold your breath and pour it out", "target": "breath-path" }
      ]
    },
    "tally-ward": {
      "title": "Ward of Tallies",
      "body": [
        "Walls carved by countless scratches. Each mark tallies a taken thing. A stone sentinel wakes, eyes like abaci.",
        "'State your account,' it grinds."
      ],
      "choices": [
        { "text": "Confess past pilfering", "target": "absolve" },
        { "text": "Claim clean hands", "target": "miscount" },
        { "text": "Trade the token for passage", "target": "token-trade" }
      ]
    },
    "sun-vault": {
      "title": "Sunless Vault",
      "body": [
        "Dark like velvet. Your token becomes a soft lantern, revealing a chamber of mirrored sand. In its center: a casket wrapped in sun-cloth.",
        "Reflections show you taking it... and you leaving it."
      ],
      "choices": [
        { "text": "Open the casket", "target": "casket-open" },
        { "text": "Leave it sealed and bow", "target": "casket-leave" },
        { "text": "Replace it with a fair offering", "target": "casket-swap" }
      ]
    },
    "service-way": {
      "title": "Service Way",
      "body": [
        "Narrow ducts stitched with forgotten rope. You find a maintenance sigil shaped like a spiral sun-the same as your token.",
        "A hatch drops toward distant singing."
      ],
      "choices": [
        { "text": "Drop to the singing", "target": "choir-chamber" },
        { "text": "Circle back to the threshold", "target": "threshold" }
      ]
    },
    "coin-path": {
      "title": "The Weight of Coin",
      "body": [
        "Your coin clinks. Echoes swell into a chorus of market cries. A side door slides open, smelling of sun-cloth and citrus.",
        "Some debts answer to silver. Others wait."
      ],
      "choices": [
        { "text": "Enter the new passage", "target": "sun-vault" },
        { "text": "Return to the threshold", "target": "threshold" }
      ]
    },
    "song-path": {
      "title": "The Gift of Song",
      "body": [
        "You sing. The hall sings back better. A lost lullaby returns to you wearing new harmonies.",
        "A mosaic reshapes to point toward a hidden choir chamber."
      ],
      "choices": [
        { "text": "Follow the mosaic", "target": "choir-chamber" },
        { "text": "Hold on to the melody and leave", "target": "threshold" }
      ]
    },
    "breath-path": {
      "title": "The Cost of Breath",
      "body": [
        "You exhale into the bowl; frost webs the rim. The echoes fall silent, listening to your quiet.",
        "A frost-rimmed slit opens to a room that smells of old oaths."
      ],
      "choices": [
        { "text": "Slip through the slit", "target": "oath-room" },
        { "text": "Go back", "target": "threshold" }
      ]
    },
    "absolve": {
      "title": "Confession Ledger",
      "body": [
        "You speak your small thefts, your found-and-kept shinies. The sentinel chisels three marks, then wipes two away.",
        "'Balanced enough,' it says, and steps aside."
      ],
      "choices": [
        { "text": "Proceed deeper", "target": "sun-vault" },
        { "text": "Return to Sythria with the news", "target": "return-topside" }
      ]
    },
    "miscount": {
      "title": "Miscounted",
      "body": [
        "You claim spotless pockets. The sentinel's abaci eyes whirl, clacking to a stop.",
        "'Error detected.' A pit yawns."
      ],
      "eject": true,
      "choices": [
        { "text": "Leap and grab the ledge", "target": "service-way" },
        { "text": "Accept the lesson and slide down", "target": "pit-lesson" }
      ]
    },
    "token-trade": {
      "title": "Trade at the Gate",
      "body": [
        "You place the token on the scales. The sentinel bows. 'A promise pledged is a door paid.'",
        "The wall splits to the vault."
      ],
      "choices": [
        { "text": "Enter the vault", "target": "sun-vault" },
        { "text": "Change your mind and retrieve it", "target": "tally-ward" }
      ]
    },
    "choir-chamber": {
      "title": "Chamber of the Last Choir",
      "body": [
        "Statues of lamia, mouths open in silent hymn. Sythria's younger face is among them.",
        "A pedestal offers a shell trumpet and a ribbon of sun-cloth."
      ],
      "choices": [
        { "text": "Take the trumpet", "target": "trumpet" },
        { "text": "Take the sun-cloth ribbon", "target": "ribbon" },
        { "text": "Leave offerings and move on", "target": "sun-vault" }
      ]
    },
    "oath-room": {
      "title": "Room of Old Oaths",
      "body": [
        "Names are carved in circles, some crossed, some glowing faintly. Your reflection blinks in the stone.",
        "A question forms: 'Will you be kept by the treasure, or keep it?'"
      ],
      "choices": [
        { "text": "Be kept by the treasure", "target": "casket-open" },
        { "text": "Keep nothing that asks to stay", "target": "casket-leave" }
      ]
    },
    "trumpet": {
      "title": "Voice Borrowed",
      "body": [
        "You raise the shell trumpet; it sings Sythria's hymn for you, clear and bright.",
        "Some doors open to borrowed voices."
      ],
      "reward": { "itemId": "shell_trumpet", "label": "Shell Trumpet" },
      "choices": [
        { "text": "Sound it at the vault", "target": "casket-swap" },
        { "text": "Return to the threshold", "target": "threshold" }
      ]
    },
    "ribbon": {
      "title": "Ribbon of Sun-Cloth",
      "body": [
        "Light clings to your fingers. The ribbon is warm and smells faintly of citrus blossoms.",
        "It might replace what you dare not take."
      ],
      "reward": { "itemId": "sun_cloth_ribbon", "label": "Sun-Cloth Ribbon" },
      "choices": [
        { "text": "Use it to swap with the casket", "target": "casket-swap" },
        { "text": "Tie it to your wrist and leave", "target": "threshold" }
      ]
    },
    "pit-lesson": {
      "title": "Lesson in the Dark",
      "body": [
        "You slide onto soft sand. A quiet voice counts to three, then stops. You add 'four.' The room approves; a rope ladder drops.",
        "You climb toward a faint citrus glow."
      ],
      "choices": [
        { "text": "Follow the glow", "target": "sun-vault" }
      ]
    },
    "casket-open": {
      "title": "The Heart of Heliodora",
      "body": [
        "Inside rests a mirror of hammered gold, small enough for a pocket, heavy as a promise. In its surface you see Sythria's eyes... and your own, a little brighter.",
        "The chamber trembles, not angry-awake."
      ],
      "reward": { "itemId": "heliodora_mirror", "label": "Heliodora Mirror" },
      "choices": [
        { "text": "Take the mirror and run", "target": "escape" },
        { "text": "Show the mirror to Sythria", "target": "return-topside" }
      ]
    },
    "casket-leave": {
      "title": "Choosing to Leave",
      "body": [
        "You bow and back away. The vault warms like sun on eyelids. A hidden panel opens to a stair wreathed in lemon-scented air.",
        "Sometimes treasure is letting the city keep itself."
      ],
      "choices": [
        { "text": "Ascend to the surface", "target": "return-topside" },
        { "text": "Begin a new journey in the hub", "target": "hub", "restart": true }
      ]
    },
    "casket-swap": {
      "title": "Fair Exchange",
      "body": [
        "You place the ribbon-your promise, your song, or your coin-on the casket. The mirror rises, weightless now, and the chamber purrs.",
        "A script etches itself along the wall: 'Returned in kind, carried in light.'"
      ],
      "choices": [
        { "text": "Carry the mirror carefully", "target": "return-topside" },
        { "text": "Hide the mirror and explore more", "target": "service-way" }
      ]
    },
    "escape": {
      "title": "Hasty Exit",
      "body": [
        "Sand sluices through seams; statues blink awake. A maintenance chute gapes like a yawn.",
        "You tumble into daylight, heart sprinting, mirror hot against your chest."
      ],
      "eject": true,
      "choices": [
        { "text": "Catch your breath with Sythria", "target": "return-topside" },
        { "text": "Head straight to the city hub", "target": "hub", "restart": true }
      ]
    },
    "return-topside": {
      "title": "Under the Leaning Obelisk",
      "body": [
        "<div class=\"scene-media\"><figure class=\"scene-portrait\"><img src=\"images/lamia_f_oracle.webp\" alt=\"Sythria waiting at the obelisk\" loading=\"lazy\" decoding=\"async\"><figcaption class=\"muted\">'Heliodora remembers.'</figcaption></figure></div>",
        "Sythria waits where you left her. If you carry the mirror, she smiles without teeth. If you left it, she smiles with all of them.",
        "'Heliodora remembers,' she says. 'So will you.'"
      ],
      "choices": [
        { "text": "Record your tale and head to the hub", "target": "hub", "restart": true },
        { "text": "Ask Sythria about other ruins", "target": "sun-gate" }
      ]
    },
    "hub": {
      "title": "Back to the City Hub",
      "body": [
        "Your travelogue updates with sun-etched script. The lamia's token dims-or gleams-according to the choices you made.",
        "Heliodora is quieter now, but not done with you."
      ],
      "choices": [
        { "text": "Start a new path from the hub", "target": "sun-gate", "restart": true }
      ]
    }
  }
}
</script>

<!-- Optional tiny CSS nudge for portraits -->
<style>
  .scene-media { display: grid; place-items: center; margin: .5rem 0 1rem; }
  .scene-portrait { margin: 0; max-width: 240px; width: 100%; }
  .scene-portrait img { width: 100%; height: auto; border-radius: 12px; display: block; }
  .scene-portrait figcaption { font-size: .85rem; margin-top: .35rem; text-align: center; }
</style>

<!-- Your runtime; optional: early-out if window.__ADVENTURE_LOCKED__ is true -->
<script type="module" src="assets/js/adventure.js"></script>
