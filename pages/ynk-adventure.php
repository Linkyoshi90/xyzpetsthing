<section class="adventure" aria-labelledby="shrine-visit-heading">
  <header class="adventure-header">
    <h1 id="shrine-visit-heading">Lotus Terrace Shrine Visit</h1>
    <p class="muted">A quick, daily shrine stop in Amatera that stacks luck the longer you keep coming back. A side path hints at a deeper quest beyond the ridgeline.</p>
  </header>
  <div class="adventure-grid">
    <div class="card glass adventure-stage" id="adventure-stage" aria-live="polite">
      <div class="adventure-scene" id="adventure-scene">
        <h2 id="adventure-scene-title"></h2>
        <div id="adventure-scene-body" class="adventure-scene-body"></div>
      </div>
      <div class="adventure-choices" id="adventure-choices" aria-label="Available choices"></div>
    </div>
    <aside class="card glass adventure-timeline" aria-label="Shrine visit log">
      <h3>Visit Log</h3>
      <p class="muted">Trace your steps and keep your luck chain steady. Each consecutive day raises your luck chance until you miss.</p>
      <ol id="adventure-history" class="adventure-history"></ol>
    </aside>
  </div>
</section>

<script id="adventure-data" type="application/json">
{
  "start": "torii-approach",
  "nodes": {
    "torii-approach": {
      "title": "Torii Approach",
      "body": [
        "Stone steps climb through cedar shade toward a vermilion torii. Lanterns flicker even in daylight, fed by offerings from the morning crowd.",
        "A wooden placard notes: \"Daily prayers grant small luck. Keep visiting and the blessing stacks—miss a day and it calms to zero.\""
      ],
      "choices": [
        { "text": "Rinse hands at the basin", "target": "temizuya" },
        { "text": "Head straight to the offertory box", "target": "offertory" },
        { "text": "Follow the cedar path beyond the shrine", "target": "cedar-path" }
      ]
    },
    "temizuya": {
      "title": "Temizuya",
      "body": [
        "You take the ladle, wash left hand, right hand, then lift a little water to your lips. Cool cedar breath steadies your thoughts.",
        "A monk stamps a small card: \"Day counted. Luck climbs when the stamp line stays unbroken.\""
      ],
      "choices": [
        { "text": "Step to the offertory box", "target": "offertory" },
        { "text": "Ask the monk about the chain", "target": "chain-tip" }
      ]
    },
    "chain-tip": {
      "title": "Luck Chain Tip",
      "body": [
        "He taps the card. \"One day is +3% luck. Each day after rises by two, up to a gentle cap. Miss once, and it sleeps back to +0%. Simple, honest.\"",
        "He smiles. \"Luck likes routine more than spectacle.\""
      ],
      "choices": [
        { "text": "Return to the offertory", "target": "offertory" },
        { "text": "Pocket the reminder and explore the path", "target": "cedar-path" }
      ]
    },
    "offertory": {
      "title": "Offertory Box",
      "body": [
        "Coins ring softly against cedar. You bow twice, clap once, and offer a short prayer for steady steps and brighter draws.",
        "You feel the day's luck hum to life—another link added to your chain."
      ],
      "choices": [
        { "text": "Draw an omikuji fortune", "target": "omikuji" },
        { "text": "Thank the shrine and promise to return tomorrow", "target": "daily-wrap", "restart": true }
      ]
    },
    "omikuji": {
      "title": "Omikuji Stand",
      "body": [
        "You shake the box, draw a slim stick, and match the number to a tiny drawer. Inside: a fortune that rides on today's luck chain.",
        "The note reads, \"Keep your streak and your luck rises. Break it, and the blessing resets to calm.\""
      ],
      "choices": [
        { "text": "Tie a bad fortune to the pine, keep a good one", "target": "daily-wrap", "restart": true },
        { "text": "Ask for a quest blessing", "target": "quest-blessing" }
      ]
    },
    "quest-blessing": {
      "title": "Quest Blessing",
      "body": [
        "A shrine attendant offers a paper charm stamped with a moon crest. \"Carry this if you leave the city. It glows near old tunnels east of here,\" she whispers.",
        "She nods toward a ridge trail. \"Some travelers chase a lamia's riddle beyond that path. Your daily luck makes the road kinder.\""
      ],
      "choices": [
        { "text": "Follow the ridge trail", "target": "ridge-trail" },
        { "text": "Save the charm and finish the visit", "target": "daily-wrap", "restart": true }
      ]
    },
    "cedar-path": {
      "title": "Cedar Path",
      "body": [
        "The path skirts the shrine, pine needles soft underfoot. Wind carries incense and the distant clink of coins.",
        "A signboard notes an old pilgrimage road leading to sunken ruins—travelers compare its riddles to a desert adventure abroad."
      ],
      "choices": [
        { "text": "Keep your visit short and return to pray", "target": "offertory" },
        { "text": "Take the pilgrimage road", "target": "ridge-trail" }
      ]
    },
    "ridge-trail": {
      "title": "Ridge Trail Marker",
      "body": [
        "Stone jizō statues guard a fork. One arrow points back to Amatera; another points toward a distant city of fallen arches.",
        "A traveler scribble reads: \"For choices and catacombs, visit the Heliodora pocket-quest.\""
      ],
      "choices": [
        { "text": "Head toward the Heliodora tales", "target": "helio-link" },
        { "text": "Turn back and keep today's luck", "target": "daily-wrap", "restart": true }
      ]
    },
    "helio-link": {
      "title": "Whispers of Heliodora",
      "body": [
        "You pause at a trail shrine. A painted tile shows a lamia among ruins. An arrow-shaped charm points to a portal back in Dawnmarch.",
        "If curiosity wins, you could step toward Heliodora now and seek the sunken catacombs."
      ],
      "choices": [
        { "text": "Travel to the Heliodora pocket-quest", "target": "daily-wrap", "note": "Opens the adventure in Heliodora.", "link": "?pg=aa-adventure" },
        { "text": "Pocket the tile and return to Lotus Terrace", "target": "daily-wrap", "restart": true }
      ]
    },
    "daily-wrap": {
      "title": "Visit Complete",
      "body": [
        "You leave the shrine with incense on your sleeves and luck stitched into your day. Return tomorrow to keep the chain alive.",
        "The city hums with small blessings when you make a habit of them."
      ],
      "choices": [
        { "text": "Restart your shrine visit", "target": "torii-approach", "restart": true }
      ]
    }
  }
}
</script>

<script type="module" src="assets/js/adventure.js"></script>