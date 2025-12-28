<?php require_login(); ?>
<section class="adventure" aria-labelledby="urb-night-farm-heading">
  <header class="adventure-header">
    <h1 id="urb-night-farm-heading">Urb Night Farm Jaunt</h1>
    <p class="muted">A light-hearted nocturnal wander through an abandoned farm outside Meridian Arc. Bring curiosity, a gentle flashlight, and a sense of humor for creaky boards and croaking frogs.</p>
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
    <aside class="card glass adventure-timeline" aria-label="Night walk log">
      <h3>Night Walk Log</h3>
      <p class="muted">Trace your path through the farmstead while the moon is high.</p>
      <ol id="adventure-history" class="adventure-history"></ol>
    </aside>
  </div>
</section>

<!-- Local-time gate: allow 22:00 through 03:59 local time -->
<script>
  (function gateUrbNightWalk() {
    const now = new Date();
    const hour = now.getHours();
    const allowed = hour >= 22 || hour < 4; // 22:00-03:59 local time

    if (!allowed) {
      window.__ADVENTURE_LOCKED__ = true;
      const section = document.querySelector('.adventure');
      if (!section) return;

      const previousPosition = getComputedStyle(section).position;
      if (previousPosition === 'static') {
        section.style.position = 'relative';
      }

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

      overlay.innerHTML = `
        <div class="card glass" style="max-width: 600px; padding: 1.25rem; text-align: center;">
          <h2 style="margin: 0 0 .25rem 0;">The Farm Sleeps</h2>
          <p class="muted" style="margin: 0 0 .75rem 0;">Come back between 10PM and 3:59AM local time for the moonlit jaunt.</p>
          <p style="margin: 0 0 .5rem 0;">It's currently <strong>${now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</strong> for you.</p>
          <p class="muted" style="margin: 0;">(The clock follows your device time.)</p>
          <br>
          <a class="btn" href="?pg=urb">Back to Meridian Arc</a>
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
  "start": "farm-gate",
  "nodes": {
    "farm-gate": {
      "title": "Fence Gap",
      "body": [
        "<!-- Image prompt: A moonlit farm fence with a narrow gap and soft mist hovering over fields. -->",
        "A wired fence sags just enough for you to slip through. Fireflies blink along the posts, and somewhere a frog tests its microphone.",
        "The farmhouse outline is lost to the dark, but the wind smells like hay and cedar."],
      "choices": [
        { "text": "Sidle over to the Bare Barn", "target": "bare-barn" },
        { "text": "Follow the canal toward the mill", "target": "still-water-mill" },
        { "text": "Wave at the neighbor's dark porch", "target": "neighbors-house" }
      ]
    },
    "bare-barn": {
      "title": "Bare Barn",
      "body": [
        "<!-- Image prompt: An empty barn at night, beams of moonlight slicing through missing boards and dust motes. -->",
        "The barn has more gaps than walls. Moonlight stripes the dirt floor and the ghostly moos of long-gone cows roll like someone humming through a pipe.",
        "A rusted ladder leads to a loft, and an old bell dangles from a rope, crusted with time."],
      "choices": [
        { "text": "Climb to the loft and pocket the bell", "target": "barn-loft", "giveItem": 1, "giveItemNote": "Tarnished Barn Bell" },
        { "text": "Trace the moo echoes toward the back door", "target": "fenta-forest" },
        { "text": "Head back to the fence gap", "target": "farm-gate", "restart": true }
      ]
    },
    "barn-loft": {
      "title": "Loft Breeze",
      "body": [
        "<!-- Image prompt: A barn loft with broken slats, rope coils, and moonlight pouring in. -->",
        "Up top, a cool draft threads through the slats. You find the bell surprisingly weighty, as if it remembers every herd that once heard it ring.",
        "From here you can see the pond glitter and the fog rolling over a distant hill."],
      "choices": [
        { "text": "Slide down to the pond path", "target": "dankweed-pond" },
        { "text": "Glide down the ladder and follow hoof prints", "target": "still-water-mill" }
      ]
    },
    "still-water-mill": {
      "title": "Laid Still Water Mill",
      "body": [
        "<!-- Image prompt: An old wooden water mill over a narrow tributary, moonlight on wet wood, wheel frozen mid-turn. -->",
        "The tributary still moves, quietly sloshing against the waterlogged mill wheel. Each creak from the soaked beams sounds like a polite complaint.",
        "Someone left a thermos by the door, long cold. A faded ledger lists wheat deliveries and doodles of smiling loaves."],
      "choices": [
        { "text": "Inspect the wheel and the flowing water", "target": "mill-wheel" },
        { "text": "Leaf through the ledger", "target": "ledger-musing", "giveItem": 1, "giveItemNote": "Waterlogged Ledger Page" },
        { "text": "Cut across the field toward the forest", "target": "fenta-forest" }
      ]
    },
    "mill-wheel": {
      "title": "Wheel Whisper",
      "body": [
        "<!-- Image prompt: Close-up of a still wooden waterwheel in moonlight with trickling water. -->",
        "The wheel shifts just enough to groan, then settles. The tributary feels colder than it should; when you trail a hand, the current hums like an old engine.",
        "A firefly lands on your sleeve as if to say it counted the rotations long ago."],
      "choices": [
        { "text": "Return to the ledger and the door", "target": "still-water-mill" },
        { "text": "Follow the tributary downstream to the pond", "target": "dankweed-pond" }
      ]
    },
    "ledger-musing": {
      "title": "Ledger Doodle",
      "body": [
        "<!-- Image prompt: An open, damp ledger with sketched loaves and wheat fields under lantern light. -->",
        "The ink has blurred into watercolor, but one page stays legible: a thank-you note to the mill from 'everyone up on Ben's Hill' signed with a little guitar sketch.",
        "You tuck the page away; even damp, it feels like a receipt for goodwill."],
      "choices": [
        { "text": "Follow that gratitude up the hill", "target": "bens-hill" },
        { "text": "Step back outside toward the forest", "target": "fenta-forest" }
      ]
    },
    "neighbors-house": {
      "title": "Neighbor's House (Nobody's Home)",
      "body": [
        "<!-- Image prompt: A cobwebbed living room with a glowing TV in an otherwise dark house. -->",
        "The porch swing groans but keeps moving even when you stop it. Inside, cobwebs drape over photo frames, yet the TV hums with static snow and an old late-night commercial.",
        "A remote sits on the arm of a chair; the batteries somehow still work."],
      "choices": [
        { "text": "Mute the TV and pocket the remote", "target": "mute-tv", "giveItem": 1, "giveItemNote": "Uncanny Remote" },
        { "text": "Peek through the kitchen toward the trees", "target": "fenta-forest" },
        { "text": "Leave a polite knock and back away", "target": "farm-gate", "restart": true }
      ]
    },
    "mute-tv": {
      "title": "Static Gone",
      "body": [
        "<!-- Image prompt: A dusty TV going dark while moonlight comes through blinds. -->",
        "The screen slips to black, leaving only the hum of the fridge-that-isn't-plugged-in. When you set the remote down, the TV blinks once, approving.",
        "A cobweb brushes your arm, but the living room smells briefly of popcorn as if someone just left."],
      "choices": [
        { "text": "Step back outside toward the pond glint", "target": "dankweed-pond" },
        { "text": "Cut behind the house toward the mill", "target": "still-water-mill" }
      ]
    },
    "fenta-forest": {
      "title": "Fenta Forest",
      "body": [
        "<!-- Image prompt: A shadowy forest edge with abandoned containers and faint caution tape under moonlight. -->",
        "Trees tangle overhead, and old containers dot the ground with faded warning labels. Needles lie scattered like iron pine needles, and rumor has it nothing good happened here, though no sign of anything new lurks.",
        "Somebody once hung reflective tape between trunks; it flutters in the night breeze."],
      "choices": [
        { "text": "Follow the tape deeper", "target": "forest-clear" },
        { "text": "Stay on the path toward the hill", "target": "bens-hill" },
        { "text": "Backtrack to the mill's open door", "target": "still-water-mill" }
      ]
    },
    "forest-clear": {
      "title": "Caution Clearing",
      "body": [
        "<!-- Image prompt: Moonlit forest clearing with scattered containers and rusted needles, no gore. -->",
        "In the clearing, someone arranged empty containers into a lopsided circle. The center holds only clover and a lost camping mug, rinsed clean by rain.",
        "The silence here is full but not menacing, as if the forest is happy to let the rumors stay rumors."],
      "choices": [
        { "text": "Head toward the fog on Ben's Hill", "target": "bens-hill" },
        { "text": "Return to the safer path", "target": "fenta-forest" }
      ]
    },
    "bens-hill": {
      "title": "Ben's Hill",
      "body": [
        "<!-- Image prompt: A foggy hill at night with a lone figure playing guitar, distant farm lights below. -->",
        "Fog drapes the steep hill like a blanket. A solemn ballad floats down from somewhere above, a guitar keeping slow time.",
        "Between chords, you swear you hear someone chuckle at their own lyrics. The fog beads on your eyelashes."],
      "choices": [
        { "text": "Climb toward the music", "target": "hill-summit" },
        { "text": "Slide back toward the pond lights", "target": "dankweed-pond" }
      ]
    },
    "hill-summit": {
      "title": "Fog Summit",
      "body": [
        "<!-- Image prompt: Silhouette of a guitarist in fog atop a hill with sunrise hints on the horizon. -->",
        "At the top, the guitarist is only a silhouette; they nod and finish the song with a soft flourish. In the pause, the fog thins just enough to show the whole farm glowing silver.",
        "They gesture toward a trail marker leading back down as a pale line shows on the horizon."],
      "choices": [
        { "text": "Follow the marker toward the pond", "target": "dankweed-pond" },
        { "text": "Descend toward the barn's outline", "target": "bare-barn" }
      ]
    },
    "dankweed-pond": {
      "title": "Dankweed Pond",
      "body": [
        "<!-- Image prompt: A serene night-time pond with algae near a creaky dock, frogs calling. -->",
        "Frogs croak and gossip, ignoring the algae-rimmed dock. Empty beer cans clink when the wind sighs. A bench near the embankment is surrounded by discarded cigarettes and a faintly dank smell.",
        "The moon paints the pond silver, and something glimmers near the waterline."],
      "choices": [
        { "text": "Sit on the bench and wait", "target": "pond-bench" },
        { "text": "Collect the glimmer near the dock", "target": "pond-dock", "giveItem": 1, "giveItemNote": "Pond-Polished Trinket" },
        { "text": "Follow the shore toward the mill", "target": "still-water-mill" }
      ]
    },
    "pond-bench": {
      "title": "Bench Lullaby",
      "body": [
        "<!-- Image prompt: A worn bench by a pond under moonlight with scattered cigarette butts. -->",
        "You sit; the bench sighs back. The frogs' chorus softens into a lullaby while the fog over Ben's Hill begins to glow faintly pink.",
        "Somewhere in the trees, a rooster rehearses too early. Dawn is about to sneak in."],
      "choices": [
        { "text": "Watch the sunrise and head back", "target": "sunrise-return", "restart": true }
      ]
    },
    "pond-dock": {
      "title": "Dock Glimmer",
      "body": [
        "<!-- Image prompt: Algae-covered dock with a small glowing trinket by moonlight. -->",
        "The glimmer is a bottle cap polished by the pond, etched with a cartoon frog giving a thumbs-up. You pocket it; the frogs approve with a synchronized ribbit.",
        "Across the water, the first hints of sunrise stir the mist."],
      "choices": [
        { "text": "Walk back toward the main path", "target": "farm-gate", "restart": true },
        { "text": "Linger until sunrise", "target": "sunrise-return", "restart": true }
      ]
    },
    "sunrise-return": {
      "title": "Sunrise Over the Fields",
      "body": [
        "<!-- Image prompt: Sunrise over a quiet farm, mist lifting, warm gold light replacing moonlight. -->",
        "The sun edges over the fields, turning the fog to honey. The nocturnal hush dissolves, leaving only birds and the clink of bottles in your pocket.",
        "You stretch, grateful for the night jaunt. Meridian Arc waits just a short walk away."],
      "choices": [
        { "text": "Head back to URB", "link": "?pg=urb" }
      ]
    }
  }
}
</script>

<script type="module" src="assets/js/adventure.js"></script>
