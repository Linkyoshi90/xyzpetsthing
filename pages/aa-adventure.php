<section class="adventure">
  <header class="adventure-header">
    <h1>Legends of Harmontide</h1>
    <p class="muted">Begin a living travelogue that unfolds inside this very page. Follow threads of lore, make choices, and watch the story reshape itself without leaving the comfort of the city hub.</p>
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
<script id="adventure-data" type="application/json">
{
  "start": "harbor",
  "nodes": {
    "harbor": {
      "title": "Harbor of First Light",
      "body": [
        "Azure foam clings to the docks of Harmontide, whispering songs that only travelers seem to hear.",
        "The morning market is waking, the skyships are tethered and restless, and the tide itself gleams with bioluminescent runes that shift with each crashing wave."
      ],
      "choices": [
        { "text": "Study the shimmering tide", "target": "tidepool" },
        { "text": "Seek passage aboard a skyship", "target": "skyship" },
        { "text": "Wander into the market quarter", "target": "market" }
      ]
    },
    "tidepool": {
      "title": "Luminary Tidepool",
      "body": [
        "Kneeling at the edge of the docks you discover the runes aren't random—they align with constellations that only appear during eclipses.",
        "A hidden current coils around your wrist, guiding you toward a sealed grate that descends below the harbor." 
      ],
      "choices": [
        { "text": "Follow the current into the grate", "target": "wharf" },
        { "text": "Collect a vial for the Alchemists' Guild", "target": "alchemists" }
      ]
    },
    "skyship": {
      "title": "Deck of the Wyvernwake",
      "body": [
        "Captain Seris charts the windlines on a crystal pane, her ship humming with stored stormlight.",
        "She offers you a choice: an express run toward Verdania's emerald canopy or a detour to investigate a stormwall that has swallowed three caravans." 
      ],
      "choices": [
        { "text": "Chart a course to Verdania", "target": "verdant-skies" },
        { "text": "Probe the stormwall", "target": "stormwall" }
      ]
    },
    "market": {
      "title": "Bazaar of Many Tongues",
      "body": [
        "Lanterns drip from awnings, each projecting an illusion of the wares sold beneath.",
        "A Lorekeeper unfurls maps inked in living silver while a mercenary recruiter sharpens a blade that hums with desert heat." 
      ],
      "choices": [
        { "text": "Consult the Lorekeeper's map", "target": "lorekeeper" },
        { "text": "Hire a tide-runner for secret passages", "target": "wharf" }
      ]
    },
    "wharf": {
      "title": "Submerged Wharf",
      "body": [
        "The grate opens into a forgotten wharf lit by lantern fish. A tide-runner in coral armor grins, impressed that you found this place without a guide.",
        "She reveals a maintenance skiff and a narrow canal that connects every continent's undercroft." 
      ],
      "choices": [
        { "text": "Sail the undercanal toward Auronia", "target": "undercanal" },
        { "text": "Return to the harbor with newfound knowledge", "target": "harbor" }
      ]
    },
    "alchemists": {
      "title": "Hall of Reactive Arts",
      "body": [
        "The Guildmistress decants your vial into an orrery of liquid constellations. She offers guild favor and a sealed dossier in exchange for more samples from distant shores." 
      ],
      "choices": [
        { "text": "Accept the guild contract", "target": "contract", "note": "Unlocks daily alchemy tasks." },
        { "text": "Politely decline and plan your own expedition", "target": "harbor" }
      ]
    },
    "verdant-skies": {
      "title": "Verdant Skies",
      "body": [
        "Clouds part to reveal Verdania's canopy pulsing with aurora light. The Wyvernwake descends toward floating orchards that trade pollen like currency.",
        "Captain Seris logs you as an official envoy, granting access to arboreal courts once you disembark." 
      ],
      "choices": [
        { "text": "Start a new journey from Harmontide", "target": "harbor", "restart": true }
      ]
    },
    "stormwall": {
      "title": "Edge of the Stormwall",
      "body": [
        "Lightning braids itself into sigils ahead, forming a barrier of roiling glyphs. Within the storm you glimpse silhouettes of lost caravans suspended in time.",
        "Decoding even a fragment could reroute every merchant windline on the map." 
      ],
      "choices": [
        { "text": "Commit the glyph pattern to memory", "target": "glyphs" },
        { "text": "Retreat and reconsider", "target": "harbor" }
      ]
    },
    "lorekeeper": {
      "title": "Lorekeeper's Pavilion",
      "body": [
        "The Lorekeeper's map rearranges itself as you speak. He highlights a forgotten elevator shaft leading beneath the Council District.",
        "'Stories make shortcuts,' he says, gifting you a map key forged from starlight." 
      ],
      "choices": [
        { "text": "Descend the hidden elevator", "target": "catacombs" },
        { "text": "Pocket the key for later travels", "target": "harbor" }
      ]
    },
    "undercanal": {
      "title": "Undercanal Transit",
      "body": [
        "The skiff glides through crystal tunnels where murals show Harmontide exchanging knowledge with every realm.",
        "At a junction of seven locks, runes flare awaiting your decision of which continent to surface within." 
      ],
      "choices": [
        { "text": "Surface in Saharene's desert archives", "target": "saharene-archives" },
        { "text": "Surface back at Harmontide to report", "target": "harbor" }
      ]
    },
    "contract": {
      "title": "Guild Contract",
      "body": [
        "You sign your name alongside sparks of living ink. Daily requests will now appear in the guild hall, offering rare reagents for daring explorers." 
      ],
      "choices": [
        { "text": "Return to Harmontide to begin preparations", "target": "harbor", "restart": true }
      ]
    },
    "glyphs": {
      "title": "Glyph Imprint",
      "body": [
        "The sigils sear themselves into your memory, unlocking a new navigation layer for the Wyvernwake. Merchants will pay handsomely for safer routes." 
      ],
      "choices": [
        { "text": "Replot your journey from Harmontide", "target": "harbor", "restart": true }
      ]
    },
    "catacombs": {
      "title": "Council Catacombs",
      "body": [
        "Hidden archives beneath Harmontide chronicle treaties with realms long vanished. A spectral archivist invites you to add your own chapter." 
      ],
      "choices": [
        { "text": "Inscribe your tale and begin anew", "target": "harbor", "restart": true }
      ]
    },
    "saharene-archives": {
      "title": "Saharene Archives",
      "body": [
        "Sand-polished libraries shimmer with heat mirages. You are welcomed as a courier of Harmontide, entrusted with scrolls that bend light itself." 
      ],
      "choices": [
        { "text": "Carry the scrolls back to Harmontide", "target": "harbor", "restart": true }
      ]
    }
  }
}
</script>
<script type="module" src="assets/js/adventure.js"></script>