<?php require_login(); ?>
<section class="adventure" aria-labelledby="nh-adventure-heading">
  <header class="adventure-header">
    <h1 id="nh-adventure-heading">Skeldgard: The Weeping Lanes</h1>
    <p class="muted">A dusk-bound nightmare on the edge of Skeldgard, where compassion and survival collide in snow-choked alleys.</p>
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
    <aside class="card glass adventure-timeline" aria-label="Route log">
      <h3>Night Log</h3>
      <p class="muted">Track each judgment call as the village closes in around you.</p>
      <ol id="adventure-history" class="adventure-history"></ol>
    </aside>
  </div>
</section>

<script>
  (function gateAdventureByLocalTime() {
    const now = new Date();
    const hour = now.getHours();
    const allowed = (hour >= 16 && hour < 20);

    if (!allowed) {
      window.__ADVENTURE_LOCKED__ = true;
      const section = document.querySelector('.adventure');
      if (!section) return;

      if (getComputedStyle(section).position === 'static') {
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
        <div class="card glass" style="max-width: 580px; padding: 1.25rem; text-align: center;">
          <h2 style="margin: 0 0 .25rem 0;">The Lanes Are Silent</h2>
          <p class="muted" style="margin: 0 0 .75rem 0;">This adventure may only be entered between <strong>16:00 and 20:00</strong> local time.</p>
          <p style="margin: 0 0 .5rem 0;">Your current local time is <strong>${now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</strong>.</p>
          <p class="muted" style="margin: 0 0 .75rem 0;">(Uses your device clock.)</p>
          <a class="btn" href="?pg=nornheim">Return to Skeldgard</a>
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
  "start": "frost-road",
  "nodes": {
    "frost-road": {
      "title": "Outskirts of Skeldgard",
      "body": [
        "<!-- Prompt: Cinematic Norse-inspired village at dusk near Skeldgard, deserted wooden homes, windblown snow, blue-gray light, ominous atmosphere, ultra-detailed digital painting. --><img src=\"images/nh-adv1.webp\" alt=\"A deserted village road near Skeldgard at dusk\" loading=\"lazy\" decoding=\"async\">",
        "A village lies just beyond Skeldgard's outer watchfires. No chatter, no hearth-song-only the ghostly howl of winter wind threading through empty eaves.",
        "One alley stands out. Fresh drag marks score the snow."
      ],
      "choices": [
        { "text": "Enter the alley with the drag marks", "target": "first-body" },
        { "text": "Circle the village and look for survivors", "target": "second-alley" }
      ]
    },
    "first-body": {
      "title": "The Torn Dead",
      "body": [
        "<!-- Prompt: Dark narrow alley in Nordic settlement, snowfall, a mangled corpse in snow with torn clothing, implied gore, tense horror mood, painterly realism. --><img src=\"images/nh-adv2.webp\" alt=\"A mangled body in a snow-packed alley\" loading=\"lazy\" decoding=\"async\">",
        "A body lies collapsed against a barrel. Clothes are soaked black-red. Flesh has been stripped in ragged patches, as though by starving beasts.",
        "At first it looks like an animal attack-until you notice handprints in blood climbing the wall beside it."
      ],
      "choices": [
        { "text": "Track the bloody handprints", "target": "second-alley" },
        { "text": "Retreat and sprint back toward Skeldgard", "target": "street-flood" }
      ]
    },
    "second-alley": {
      "title": "The Sobbing Woman",
      "body": [
        "<!-- Prompt: Horror close-mid shot of hunched woman in torn furs in snowy Norse alley, half-face injured/gnawed, tears, moonlit fog, cinematic horror fantasy. --><img src=\"images/nh-adv3.webp\" alt=\"A hunched woman sobbing in a frozen alley\" loading=\"lazy\" decoding=\"async\">",
        "In the next lane, someone is alive-a hunched woman rocking and mumbling through sobs.",
        "The air around her burns your nose: ammonia and iron, like blood and rot left in winter hides.",
        "She turns. Half her face is gone. One eye locks on yours as she whispers, \"Help me.\""
      ],
      "choices": [
        { "text": "Step in and try to support her", "target": "bite-1" },
        { "text": "Back away and tell her to stay where she is", "target": "street-flood" }
      ]
    },
    "street-flood": {
      "title": "Crying in Every Doorway",
      "body": [
        "<!-- Prompt: Wide shot of Norse village street suddenly filling with weeping infected townsfolk in varied decay, snowstorm, torchlight, dread, dark fantasy horror illustration. --><img src=\"images/nh-adv4.webp\" alt=\"Weeping figures emerging from homes into the snowy street\" loading=\"lazy\" decoding=\"async\">",
        "A door creaks. Then another. Then ten. The street fills with crying figures, each in a different state of ruin: torn cheeks, blood-caked mouths, frostbitten fingers reaching.",
        "They do not roar. They plead, sob, and call for warmth as they close in."
      ],
      "choices": [
        { "text": "Rush to aid a collapsed child-shaped figure", "target": "bite-2" },
        { "text": "Vault a fence and force your way into the nearest locked house", "target": "safehouse-entry" }
      ]
    },
    "safehouse-entry": {
      "title": "Shelter with No Salvation",
      "body": [
        "<!-- Prompt: Interior of dark wooden Norse house, lone adventurer at doorway, hand on oil lamp, shadows in corners, suspenseful horror mood, cinematic lighting. --><img src=\"images/nh-adv5.webp\" alt=\"A dark house interior as the player lights a lamp\" loading=\"lazy\" decoding=\"async\">",
        "You slam the door, brace it, and fumble for an oil lamp. Light crawls over carved beams and hanging winter cloaks.",
        "From behind chests, under tables, and inside curtained bed-niches, residents unfold themselves from hiding. Too many. Too close."
      ],
      "choices": [
        { "text": "Hold your ground and try to negotiate", "target": "bite-3" },
        { "text": "Try to smash through a back window", "target": "bite-3" }
      ]
    },
    "bite-1": {
      "title": "Dream-Bite I",
      "body": [
        "<!-- Prompt: Stylized horror moment of desperate rescue turning into bite, snowy alley motion blur, PG-13 gore, dramatic composition. --><img src=\"images/nh-adv6.webp\" alt=\"The woman lunging forward as the player is bitten\" loading=\"lazy\" decoding=\"async\">",
        "You touch her shoulder. She surges upward with impossible speed and buries broken teeth into your forearm.",
        "The alley spins white-then black."
      ],
      "choices": [
        { "text": "Wake with a gasp", "target": "inn-wakeup", "rewardNotePool": ["a carved whale-bone charm", "a packet of juniper tea", "a strip of smoked trout", "a wool hand-wrap"] }
      ]
    },
    "bite-2": {
      "title": "Dream-Bite II",
      "body": [
        "<!-- Prompt: Snowy street chaos with crying undead-like villagers swarming, one biting adventurer's shoulder, dramatic Nordic horror art, high detail. --><img src=\"images/nh-adv7.webp\" alt=\"The crowd swarming and biting as snow whips through the street\" loading=\"lazy\" decoding=\"async\">",
        "The small body on the ground snaps upright and clamps onto your hand. Others pile in, sobbing apologies as they tear and bite.",
        "Your scream is swallowed by storm wind."
      ],
      "choices": [
        { "text": "Wake with a gasp", "target": "inn-wakeup", "rewardNotePool": ["a carved whale-bone charm", "a packet of juniper tea", "a strip of smoked trout", "a wool hand-wrap"] }
      ]
    },
    "bite-3": {
      "title": "Dream-Bite III",
      "body": [
        "<!-- Prompt: Claustrophobic Norse longhouse horror, dozens of decayed crying villagers overwhelming lone adventurer, lamp light and shadows, intense dark fantasy scene. --><img src=\"images/nh-adv8.webp\" alt=\"The house residents overwhelming the player in lamplight\" loading=\"lazy\" decoding=\"async\">",
        "You are dragged down in splintered lamplight. Hands clutch, mouths tear, and the room becomes a blur of wet breath, blood, and crying voices begging forgiveness.",
        "Bite after bite after bite-and then nothing."
      ],
      "choices": [
        { "text": "Wake with a gasp", "target": "inn-wakeup", "rewardNotePool": ["a carved whale-bone charm", "a packet of juniper tea", "a strip of smoked trout", "a wool hand-wrap"] }
      ]
    },
    "inn-wakeup": {
      "title": "Three Looms Inn, Morning",
      "body": [
        "<!-- Prompt: Warm Norse inn interior at dawn, shaken adventurer waking in bed, kind innkeeper offering small gift, cozy contrast after nightmare, painterly style. --><img src=\"images/nh-adv9.webp\" alt=\"A warm inn room where the innkeeper offers a small item\" loading=\"lazy\" decoding=\"async\">",
        "You bolt upright in a bed at the Three Looms Inn in Skeldgard. No wounds. Only sweat and the echo of weeping wind.",
        "The innkeeper presses a small gift into your palm and says, \"Bad dreams roam in cold seasons. Keep this on you.\""
      ],
      "choices": [
        { "text": "Step out into Skeldgard's morning streets", "link": "?pg=nornheim" }
      ]
    }
  }
}
</script>

<script type="module" src="assets/js/adventure.js"></script>
