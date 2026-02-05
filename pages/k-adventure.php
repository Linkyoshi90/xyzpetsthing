<?php require_login(); ?>
<section class="adventure" aria-labelledby="k-pyramid-heading">
  <header class="adventure-header">
    <h1 id="k-pyramid-heading">Kemet Pyramid Delve</h1>
    <p class="muted">Follow torchlit corridors beneath an ancient pyramid, trace the empty pedestals where relics once stood, and meet the tomb's last resident.</p>
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
    <aside class="card glass adventure-timeline" aria-label="Pyramid route log">
      <h3>Delve Log</h3>
      <p class="muted">Mark your route through chambers and crypt passages as you descend to the buried throne room.</p>
      <ol id="adventure-history" class="adventure-history"></ol>
    </aside>
  </div>
</section>

<script id="adventure-data" type="application/json">
{
  "start": "sun-gate",
  "nodes": {
    "sun-gate": {
      "title": "Sun Gate Entrance",
      "image": { "src": "assets/images/adventure/k-adventure/k_adv1.png", "alt": "Sun Gate Entrance with jackal statues and torchlight" },
      "body": [
        "Two weathered statues of jackals guard the cracked doorway. Heat lingers in the stone, but cool air spills out from the darkness within.",
        "A faded inscription reads: \"Honor what was taken, and the old keeper will speak.\""
      ],
      "choices": [
        { "text": "Light a torch and enter the descending hall", "target": "descending-hall" },
        { "text": "Inspect the carved map near the doorway", "target": "entry-map" }
      ]
    },
    "entry-map": {
      "title": "Carved Entry Map",
      "image": { "src": "assets/images/adventure/k-adventure/k_adv2.png", "alt": "Ancient carved map etched into sandstone" },
      "body": [
        "The map marks three ceremonial rooms with tiny pedestal glyphs. Every glyph has been chiseled out, as if someone erased each relic from memory.",
        "At the bottom, a final marker shows a burial throne deep beneath the central shaft."
      ],
      "choices": [
        { "text": "Head into the descending hall", "target": "descending-hall" },
        { "text": "Step back outside", "target": "sun-gate", "restart": true }
      ]
    },
    "descending-hall": {
      "title": "Descending Hall",
      "image": { "src": "assets/images/adventure/k-adventure/k_adv3.png", "alt": "Torchlit descending corridor with painted stars" },
      "body": [
        "The corridor slopes down at a steep angle. Painted stars still cling to the ceiling, while blown sand gathers in each stair corner.",
        "Ahead, the passage splits toward a lotus chamber and a vault lined with broken shelves."
      ],
      "choices": [
        { "text": "Take the lotus-marked passage", "target": "lotus-chamber" },
        { "text": "Enter the broken vault", "target": "broken-vault" }
      ]
    },
    "lotus-chamber": {
      "title": "Lotus Chamber",
      "image": { "src": "assets/images/adventure/k-adventure/k_adv4.png", "alt": "Lotus mural chamber with an empty pedestal" },
      "body": [
        "Blue lotus murals circle the room, their petals fading into the stone. In the center stands a smooth pedestal ringed by old offering bowls.",
        "Only dust remains where a relic once rested, but scrape marks show it was removed with care, not force."
      ],
      "choices": [
        { "text": "Follow fresh footprints toward the archive stairs", "target": "archive-stairs" },
        { "text": "Return to the hall split", "target": "descending-hall" }
      ]
    },
    "broken-vault": {
      "title": "Broken Vault",
      "image": { "src": "assets/images/adventure/k-adventure/k_adv5.png", "alt": "Shattered vault shelves and empty relic alcoves" },
      "body": [
        "Rows of shelves lie shattered, and alabaster jars are piled in corners like fallen teeth. Three alcoves hold empty pedestals with copper labels still attached.",
        "The labels read: River Eye, Sun Coil, and Bread Crown. Each artifact is gone."
      ],
      "choices": [
        { "text": "Search for where the relics were moved", "target": "archive-stairs" },
        { "text": "Backtrack to the lotus chamber", "target": "lotus-chamber" }
      ]
    },
    "archive-stairs": {
      "title": "Archive Stairs",
      "image": { "src": "assets/images/adventure/k-adventure/k_adv6.png", "alt": "Spiral archive stairs carved with reliefs" },
      "body": [
        "A spiral stair curls down around a shaft of stale air. Reliefs on the wall show scribes relocating relics deeper into the tomb during a time of war.",
        "At the base, a doorway of black stone opens into the burial throne room."
      ],
      "choices": [
        { "text": "Enter the burial throne room", "target": "burial-throne" },
        { "text": "Climb back to the hall", "target": "descending-hall" }
      ]
    },
    "burial-throne": {
      "title": "Burial Throne Room",
      "image": { "src": "assets/images/adventure/k-adventure/k_adv7.png", "alt": "Burial throne room with empty pedestals" },
      "body": [
        "Six empty pedestals ring a cracked throne-sarcophagus, exactly matching the erased map symbols. Linen shifts in the shadows, and a mummy slowly rises, voice dry as sand:",
        "\"Who... disturbs... the relic court? State your title, tax district, and preferred weather.\" After a long pause she whispers, \"Sorry. I practiced that for centuries. Did that sound normal?\""
      ],
      "choices": [
        { "text": "Introduce yourself politely and ask about the missing relics", "target": "mummy-chat" },
        { "text": "Bow awkwardly and compliment her dramatic entrance", "target": "mummy-chat" }
      ]
    },
    "mummy-chat": {
      "title": "Awkward Keeper",
      "image": { "src": "assets/images/adventure/k-adventure/k_adv8.png", "alt": "Awkward mummy keeper greeting a traveler" },
      "body": [
        "She tries three different greetings, forgets midway, then waves both hands at once. \"I used to greet priests. Then there were no priests. Now there is... you. Hi.\"",
        "When asked about the relics, she admits she hid them generations ago to protect them and then forgot which hiding puzzle matched which room. She looks relieved someone finally visited."
      ],
      "choices": [
        { "text": "Promise to keep her secret and leave peacefully", "target": "farewell", "rewardNotePool": ["A wrapped date cake", "A jar of pickled onions", "Half a sesame flatbread", "A cone of roasted chickpeas", "A honey fig pastry"] },
        { "text": "Offer to visit again so she can practice conversations", "target": "farewell", "rewardNotePool": ["A wrapped date cake", "A jar of pickled onions", "Half a sesame flatbread", "A cone of roasted chickpeas", "A honey fig pastry"] }
      ]
    },
    "farewell": {
      "title": "Dusty Farewell",
      "image": { "src": "assets/images/adventure/k-adventure/k_adv9.png", "alt": "Nighttime pyramid exit with a shy farewell wave" },
      "body": [
        "The mummy shuffles to a side chest, rummages for an unreasonably long time, and hands you a random snack with ceremonial seriousness.",
        "\"Please enjoy this mortal offering. Also... if we meet again, should I do a handshake? Is that too much?\" She gives a tiny embarrassed wave and lets you walk free into the desert night."
      ],
      "choices": [
        { "text": "Return to Kemet", "link": "?pg=kemet" }
      ]
    }
  }
}
</script>

<script type="module" src="assets/js/adventure.js"></script>
