<section class="adventure" aria-labelledby="meridian-arc-heading">


  <header class="adventure-header">


    <h1 id="meridian-arc-heading">Meridian Arc Night Walk</h1>


    <p class="muted">Slip into the United Free Republic's river city for an evening of alleys, arenas, and odd chances. Choices can cost you dosh or lift your creatures' spirits.</p>


  </header>


  <div class="adventure-grid">


    <div class="card glass adventure-stage" id="adventure-stage" aria-live="polite">


      <div class="adventure-scene" id="adventure-scene">


        <h2 id="adventure-scene-title"></h2>


        <div id="adventure-scene-body" class="adventure-scene-body"></div>


      </div>


      <div class="adventure-choices" id="adventure-choices" aria-label="Available choices"></div>


    </div>


    <aside class="card glass adventure-timeline" aria-label="Evening log">


      <h3>Evening Log</h3>


      <p class="muted">Track where you turned. Meridian Arc remembers both the good and the sketchy.</p>


      <ol id="adventure-history" class="adventure-history"></ol>


    </aside>


  </div>


</section>





<script id="adventure-data" type="application/json">


{


  "start": "station-arrival",


  "nodes": {


    "station-arrival": {


      "title": "Meridian Exchange Station",


      "body": [


        "Trains sigh under the steel vault, and the smell of pretzels mixes with jet fuel. Screens flash arrivals for cities that sound suspiciously like real-world hubs.",


        "A busker plays brass near the escalators. A poster lists tonight's ballgame and a mural walk down in Arcworks."


      ],


      "choices": [


        { "text": "Take the light rail toward Rivermile", "target": "river-walk" },


        { "text": "Cut through the freight alley shortcut", "target": "alley-shortcut" },


        { "text": "Follow a media crew toward the arena", "target": "backstage-lobby" }


      ]


    },


    "river-walk": {


      "title": "Rivermile Riverwalk",


      "body": [


        "Sunset pours orange over glass towers. Food stalls hawk skewers, tacos, and corn on sticks. Joggers weave around you with practiced politeness.",


        "A street muralist waves you closer—she's finishing a skyline under the bridge."],


      "choices": [


        { "text": "Tip the artist and ask for a quick sketch", "target": "sketch-gift" },


        { "text": "Buy a cone and stroll toward the fountain", "target": "fountain-ambush" },


        { "text": "Head back toward the station", "target": "station-arrival", "restart": true }


      ]


    },


    "sketch-gift": {


      "title": "Bridge Mural",


      "body": [


        "The artist adds a tiny version of your favorite creature perched on a cornice. She laughs, says the city needs more guardians.",


        "Your team feels seen; happiness rises like the bridge lights."


      ],


      "choices": [


        { "text": "Walk the lit path toward Arcworks", "target": "arcworks-lanterns" },


        { "text": "Head back to the station", "target": "station-arrival", "restart": true }


      ]


    },


    "arcworks-lanterns": {


      "title": "Arcworks Alley Lanterns",


      "body": [


        "String lights zigzag above brick walls painted with community slogans. Startups and art studios share loading docks.",


        "A courier on a bike nods as you pass, balancing a box with \"Arc Line\" stenciled on the tape."],


      "choices": [


        { "text": "Chat up the courier for shortcuts", "target": "courier-tip" },


        { "text": "Take a side alley to reach the arena faster", "target": "alley-shortcut" }


      ]


    },


    "courier-tip": {


      "title": "Arc Line Advice",


      "body": [


        "The courier maps a safer late-night route: lit streets, cameras on corners, and a coffee cart that will refill your bottle for free.",


        "He warns: \"Don't cut through Old Grid alleys after the breweries close unless you like filing police reports.\""


      ],


      "choices": [


        { "text": "Follow the safe route", "target": "commons-park" },


        { "text": "Ignore the warning and take the alley", "target": "alley-robbery" }


      ]


    },


    "alley-shortcut": {


      "title": "Freight Alley",


      "body": [


        "The shortcut smells like hops and rain. A flickering lamp casts long shadows between loading docks.",


        "Footsteps echo from behind. Maybe it's just a delivery runner—or not."],


      "choices": [


        { "text": "Keep walking, stay confident", "target": "alley-robbery" },


        { "text": "Turn around and rejoin the main street", "target": "station-arrival", "restart": true }


      ]


    },


    "alley-robbery": {


      "title": "Pocket Check Gone Wrong",


      "body": [


        "Two figures step from the shadow and crowd your path. One bumps your shoulder while the other murmurs an apology that sounds rehearsed.",


        "By the time you clear the alley, you're lighter by 500 dosh. The city stings a lesson into your wallet."


      ],


      "choices": [


        { "text": "Report it to nearby patrol bikes", "target": "patrol-response" },


        { "text": "Shake it off and stick to lit streets", "target": "commons-park" }


      ]


    },


    "patrol-response": {


      "title": "Civic Department Patrol",


      "body": [


        "Officers take your statement, note the alley camera, and promise to file for restitution. They hand you a voucher for transit and a stern reminder: stay where the crowds are.",


        "It's not instant cash back, but it keeps you moving."],


      "choices": [


        { "text": "Use the voucher toward the arena", "target": "backstage-lobby" },


        { "text": "Call it a night and ride home", "target": "station-arrival", "restart": true }


      ]


    },


    "fountain-ambush": {


      "title": "Fountain Crowd",


      "body": [


        "Fireworks from the ballpark crack in the distance. Kids splash in the fountain, vendors ring bells, and a man with a foam finger waves you toward the arena.",


        "You hear someone famous is in town for the seventh-inning stretch."],


      "choices": [


        { "text": "Follow the excitement toward the arena", "target": "backstage-lobby" },


        { "text": "Find a quiet bench by the river", "target": "quiet-bench" }


      ]


    },


    "quiet-bench": {


      "title": "River Bench",


      "body": [


        "You listen to the splash of the fountain and the far-off roar of the stadium. Streetlights ripple on the water.",


        "A passerby drops a travel tip about a commuter discount that saves you 200 dosh on your next ride."],


      "choices": [


        { "text": "Head home content", "target": "station-arrival", "restart": true },


        { "text": "Take the tram to Arcworks night market", "target": "arcworks-lanterns" }


      ]


    },


    "backstage-lobby": {


      "title": "Arena Backstage Lobby",


      "body": [


        "Security funnels VIPs through glass doors. The media crew you followed flashes badges and a producer glances your way.",


        "A chart-topping singer recognizes your creature pin and waves you over."],


      "choices": [


        { "text": "Chat with the singer about pets", "target": "celebrity-chat" },


        { "text": "Decline politely and head to your seat", "target": "upper-deck" }


      ]


    },


    "celebrity-chat": {


      "title": "Celebrity Shoutout",


      "body": [


        "The singer shares backstage snacks, takes a selfie with your favorite creature plush, and posts a shoutout.",


        "Your creatures buzz with happiness from all the praise—morale climbs for the next few days."],


      "choices": [


        { "text": "Enjoy the show from VIP rail", "target": "upper-deck" },


        { "text": "Thank them and head back toward the station", "target": "station-arrival", "restart": true }


      ]


    },


    "upper-deck": {


      "title": "Upper Deck View",


      "body": [


        "From the cheap seats you see the whole skyline lit. A hawker sells hotdogs at regular price—Meridian Arc tradition against gouging.",


        "Win or lose, the night feels like a story worth keeping."],


      "choices": [


        { "text": "Catch the last train home", "target": "station-arrival", "restart": true },


        { "text": "Stay for fireworks on the bridge", "target": "skyline-bridge" }


      ]


    },


    "commons-park": {


      "title": "Commons Park",


      "body": [


        "Kids chase fireflies near the carousel while food trucks pack up. Volunteers hand out water and maps to late stragglers.",


        "The atmosphere is calm, a reminder that most of the city runs on kindness and caffeine."],


      "choices": [


        { "text": "Grab a map and plan tomorrow's errands", "target": "map-notes" },


        { "text": "Hop the bus toward Arcworks", "target": "arcworks-lanterns" }


      ]


    },


    "map-notes": {


      "title": "Transit Map Notes",


      "body": [


        "You mark safe late-night routes, note police call boxes, and circle the best food stalls. A passerby insists you try the Meridian Melt before leaving town.",


        "Prepared and fed, you feel steadier for the next outing."],


      "choices": [


        { "text": "Wrap the night and return to the station", "target": "station-arrival", "restart": true }


      ]


    },


    "skyline-bridge": {


      "title": "Skyline Bridge Glow",


      "body": [


        "The pedestrian deck fills with neighbors sharing thermoses. Fireworks bloom over the river, reflected twice in glass and water.",


        "You share snacks with a family visiting from out of state. Your creatures absorb the goodwill like a battery."],


      "choices": [


        { "text": "Walk home with the crowd", "target": "station-arrival", "restart": true }


      ]


    }


  }


}


</script>





<script type="module" src="assets/js/adventure.js"></script>