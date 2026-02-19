<?php

require_once __DIR__.'/country_interactive_map.php';

function get_country_map_config(string $slug): ?array {
    $w = 1531;
    $h = 811;

    $configs = [
        'baharamandal' => [
            'title' => 'Baharamandal - Padmanagara',
            'subtitle' => 'City of the Lotus',
            'image' => 'images/harmontide-padmanagara.webp',
            'lore' => 'Padmanagara is a river-city of temples, scholarship, and market craft where beauty is treated as civic duty.',
            'lore_sections' => [
                [
                    'title' => 'Padmanagara - City of the Lotus',
                    'html' => '<p><strong>Padmanagara</strong> sits on a braid of holy rivers, its ghats stepping down like open palms to the water. Bells carry on the morning mist; saffron flags tilt above carved gateways; and everywhere the scent of cardamom, sandal, and river silt. The city keeps its stones polished not from nostalgia alone but from duty: in Baharamandal, beauty is a public trust. Scratch a pillar, and a priest-accountant may hand you a broom faster than a fine.</p><p>Its spirit is a weave of <strong>temple scholarship</strong>, <strong>bazaar craft</strong>, and a warm diaspora return. Students argue dharma under neem trees while spice merchants count with quick fingers; pilgrims trade songs for sweet tea; and returning families bring back street snacks and cinema posters that the old quarter promptly blesses with marigolds.</p>',
                ],
                [
                    'title' => 'Districts &amp; Landmarks',
                    'html' => '<ul><li><strong>Lotus Ghats</strong> - Terraced steps where lamps float at dusk. The water courts sit above the 3rd landing; disputes are heard after a shared sip from the same brass cup.</li><li><strong>Mandala Chowk</strong> - The bazaar-heart. Garlands, pigments, brasswork, silk, and street theater. The ground is chalked with designs that ask feet to walk kindly.</li><li><strong>Devalok Gate</strong> - The temple quarter. Shrines stand shoulder to shoulder with schools and kitchens. Oaths spoken here are sealed with a pinch of ash on the wrist.</li><li><strong>River Colleges</strong> - Red-stone cloisters where monks and mathematicians share a board. Sand tables map the floodplain; prayer wheels share space with abacuses.</li><li><strong>Peacock Arcade</strong> - A colonnade of artisans: miniature painters, veena makers, perfumers. The roof feathers are tile; when the monsoon drums, it sounds like a dance.</li><li><strong>Sunshade Garden</strong> - Banyan, neem, lotus ponds. Public shade is a civic right; readers and chess players keep quiet score while parakeets argue.</li></ul>',
                ],
                [
                    'title' => 'Food &amp; the Diaspora Touch',
                    'html' => '<p><strong>Eat with your eyes first.</strong> Padmanagara plates are color and comfort: banana-leaf thalis with lentils, pickles, rice, and flatbreads that puff like proud cheeks. Street sides fry masala fritters and green-chili pakoras; clay cups pour cardamom chai. The diaspora brought back new cravings, and the city obliged. Try <strong>Lotus Lassi</strong> with rose and pistachio, or the <strong>Returnee Roll</strong>: spiced paneer wrapped in a flaky bread and seared on a steel drum.</p><p>For a famous bite, queue at <strong>Mandala Meals</strong> where the line moves like a prayer. For less fanfare and more soul, ask a bookseller for a "student plate" and you will be sent to a lane canteen that serves rice, dal, ghee, and a chutney that tastes like a monsoon afternoon. Prices near the ghats can multiply by 5 to 10. Smile, ask for the "local measure," and name the day of the temple calendar; the vendor will probably knock the number down.</p>',
                ],
                [
                    'title' => 'Order &amp; Underlane',
                    'html' => '<p>Padmanagara prides itself on soft authority. The <strong>Lamp Ward</strong> keeps watch with ledgers and lanterns; patrols end by relighting a shrine, not by slamming doors. Still, there are whispers about the <strong>Silk Houses</strong>: old merchant families who maintain quiet monopolies on certain lanes, charging a "thread fee" to keep the peace. They insist they are guardians of tradition, not rackets. The Lamp Ward shrugs and audits their charity lines every season. In this city, even shadow accounts must show a receipt for donated rice.</p>',
                ],
                [
                    'title' => 'Etiquette &amp; Practicalities',
                    'html' => '<ul><li><strong>At ghats:</strong> shoes off at the last step; no photos during lamp rituals unless invited. Do not touch a floating lamp; bless it with a bow instead.</li><li><strong>At shrines:</strong> right hand for offerings; left hand stays quiet. Ring the bell once, not as a drum.</li><li><strong>Haggling:</strong> begin with a greeting and a compliment; bargains made with honey last longer than those made with thunder.</li><li><strong>Lines:</strong> there are visible queues and invisible ones. Ask "who is last" and inherit the place gracefully.</li><li><strong>Heat:</strong> drink water, not pride. Vendors will offer clay-cup water as courtesy; refuse only if you must, and then with thanks.</li></ul>',
                ],
                [
                    'title' => 'Calendar &amp; Belief',
                    'html' => '<p>The city keeps the <strong>Festival of Lamps</strong> at river-drop: thousands of flames ride the current while verses of the Devalok Path are sung from the steps. The <strong>Kite Monsoon</strong> paints the sky in midyear, strings humming like sitars across roofs. On the night of <strong>Quiet Lamps</strong>, markets dim their lanterns together for one minute so promises can be heard. Temples, schools, and courts share walls; in Padmanagara the triad is simple: <strong>study, serve, celebrate</strong>.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Padmanagara asks for unhurried steps and bright eyes. Touch stone with respect, taste what is offered, and count your change with a grin rather than a glare. If the bazaar tempts your purse thin, call it a donation to the gods of appetite and art. The rivers remember generosity longer than any receipt.</p>',
                ],
            ],
            'back_label' => 'Back to Dawnmarch',
            'back_href' => '?pg=dawnmarch',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Paint your local pets with Baharamandal palettes.', 'action' => 'Explore', 'href' => '?pg=bm_paint_shack', 'color' => '#8b5cf6', 'points' => country_map_rect_points(190, 520, 260, 175, $w, $h)],
                ['name' => 'Picnic Tree', 'description' => 'Pick up random goodies from Padmanagara\'s community tree.', 'action' => 'Visit', 'href' => '?pg=bm_pt', 'color' => '#22c55e', 'points' => country_map_rect_points(520, 420, 240, 160, $w, $h)],
            ],
        ],
        'bretonreach' => [
            'title' => 'Bretonreach - Avalore',
            'subtitle' => 'The Bastion of Stormwalls',
            'image' => 'images/harmontide-br.webp',
            'lore' => 'Avalore is a fortress-port where guild law, sea trade, and watchful walls shape daily life.',
            'lore_sections' => [
                [
                    'title' => 'Avalore - Crown of the Mists',
                    'html' => '<p><strong>Avalore</strong> rises from green hills and river fog, a ring-walled capital where guest-right is law and songs count as currency. Half-timber lanes lean over cobbles, ivy climbs watchtowers, and way-bakers set out warm loaves before dawn so travelers leave the gates with crumbs and gratitude. In Bretonreach, hospitality is not a nicety. It is how the roads stay safe when the mists grow curious.</p><p>The city lives at the seam of <strong>Celtic and old English lore</strong>: courts of oak and rowan, ballads that bargain with the weather, hearths that keep room for strangers and for the not-quite-mortal. The elders say Avalore was founded where a silver branch chimed over a spring. True or not, bells still hang at doorframes and ring when a lie tries to enter.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Green Crown</strong> - The hill-fort heart. Moot oak at the center, council benches circling out. Petitioners tie ribbon-pledges to the rail; the beadle snips them loose at dusk when promises are kept.</li><li><strong>Rowan Gate</strong> - Eastern gate strung with red berries and iron nails. Market stalls spill under faded pennants. If a hawker will not name a price, they want a story, not coin.</li><li><strong>Bridge of Three Larks</strong> - A triple-arched span over the River Kindly. Lark carvings face upstream, downstream, and skyward. Lovers carve initials on the underside where the river keeps secrets.</li><li><strong>Lantern Close</strong> - Lamplit alley of inns and music houses. Fiddles, pipes, and hand drums until curfew. The inn called <em>The Silver Branch</em> is said to pour ale that cannot sour even in bad years.</li><li><strong>Willow Court</strong> - A crescent green with three living willows trained into an open-air hall. Here the <em>Courts of Courtesy</em> hear disputes with tea and shortbread before fines.</li><li><strong>Warden Walk</strong> - Patrol path along the inner wall. Chalk sigils mark true north, and brass mirrors hang to catch glamours trying to cross as shadows.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat like a guest, not a raider.</strong> Avalore feeds the road: root pies with cheddar lids, leek and nettle soup, oat bannocks, and river trout with lemon and thyme. Street trays carry honeyed apples in autumn and cream scones year-round. Try <strong>Mistbrew</strong>, a pale ale cut with heather, or the <strong>Green Crown Stew</strong> that arrives with a warning about wishing out loud while you eat it.</p><p>Tourist tip: Lantern Close can run 5 to 10 times dearer than lanes one street back. Ask for the "traveler plate" and name the day of the last Bellfast (city bell maintenance day) to earn a smile and a fairer price.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>The <strong>Road Wardens</strong> wear blue cloaks and carry ash staves banded with iron. They settle quarrels with courtesy first and kettles second. Beneath the civility, Avalore remembers that mists have teeth. There is whisper of the <strong>Gentle Folk</strong> who prefer gifts to taxes, guiding certain high-value wagons through fog for a fee of bread, salt, and silence. The Wardens pretend not to hear, while the <em>Courts of Courtesy</em> post bounties for those who steal without leaving thanks.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>At doors:</strong> knock twice, then once. Bells at lintels decide if you are welcome. If the bell does not ring, try again with a softer breath.</li><li><strong>Guest-right:</strong> accept bread and salt before bargaining. If you decline, you have declared a duel of prices.</li><li><strong>Stories:</strong> some stalls take tales as tender. Keep one clean and one spooky. Do not trade a true name for pudding.</li><li><strong>Mist manners:</strong> never call a stranger "friend" until sunrise. Offer your name but not your shadow. Iron in your pocket keeps the road honest.</li><li><strong>Curfew:</strong> when the Watch-horn sounds, the lanterns of Lantern Close dim. After-curfew singing must be whispered or be very, very good.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Avalore keeps <strong>Breadfirst</strong> at harvest, a procession where loaves baked with carved faces ride to the Green Crown and are broken among strangers. Spring belongs to <strong>Rowanrise</strong>, when red ribbons move from winter charms to marriage poles. On fog-thick nights the city lights <strong>Kindly Lamps</strong> along the river, and children float paper boats with good-luck lines written inside.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Avalore will make room for you if you make room for others. Share a bench. Leave a coin on a windowsill when the fiddler plays your name out of the air. If a fox salutes you at dawn, salute back and say nothing else. And if the price of a scone seems wicked, remember: some of what you pay goes to keep the mists telling nicer stories about strangers.</p>',
                ],
            ],
            'back_label' => 'Back to Auronia',
            'back_href' => '?pg=auronia',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Apply Bretonreach paints and heraldic finishes.', 'action' => 'Explore', 'href' => '?pg=br_paint_shack', 'color' => '#3b82f6', 'points' => country_map_rect_points(240, 470, 230, 170, $w, $h)],
                ['name' => 'Everything Store', 'description' => 'A famous all-purpose market in Avalore.', 'action' => 'Enter', 'href' => '?pg=br-everything-store', 'color' => '#f59e0b', 'points' => country_map_rect_points(680, 360, 260, 180, $w, $h)],
            ],
        ],
        'cc' => [
            'title' => 'Crescent Caliphate - Ansurah',
            'subtitle' => 'Moonlit Markets and Minarets',
            'image' => 'images/harmontide-cc.webp',
            'lore' => 'Ansurah is known for scholarship, caravans, and cosmopolitan bazaars under crescent towers.',
            'lore_sections' => [
                [
                    'title' => 'Ansurah - The Crescent Gate',
                    'html' => '<p><strong>Ansurah</strong> crowns an old trade bend where caravan roads meet a wide river. Crescent walls catch first light; windtowers breathe cool air into lanes; blue and white tile calms the eye. Fountains stand at every quarter under the care of waqf endowments. In the Crescent Caliphate, law is the lamp and mercy the oil; in Ansurah both are kept trimmed by habit, not spectacle.</p><p>This is a 15th century city of books and bales. Jurists argue in shaded iwans, astronomers chart stars from rooftop terraces, and merchants tally saffron, indigo, leather, and glass in khans that ring at dusk with the sound of travel kettles. Calligraphers write verses that double as shop signs, and the muhtasib, the market steward, weighs honesty with the same care as grain.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Qalb al Qanun</strong> - The Law Quarter. Madrasas, chancery halls, and a public loggia where verdicts are read after both sides share water from a brass cup. Ledgers of fines are posted weekly on tile boards.</li><li><strong>Souq al Hilal</strong> - The Crescent Market under high canvas. Copper, carpets, perfumes, spices, bookstalls, and a line of cup-bearers who keep travelers from bargaining on an empty throat.</li><li><strong>Dar al Qalam</strong> - House of the Pen. Scribes, binders, and illuminators. Commission a name-verse for your doorway; the master here will sand the ink while telling you three short laws of trade.</li><li><strong>Khan al Safar</strong> - Caravanserai of the Travelers. Stone court with stables, storerooms, prayer hall, and a fountain shaded by palms. Gates close at nightfall; late arrivals ring a brass bell and swear they bring no quarrels inside.</li><li><strong>Qanat Gardens</strong> - Gardens fed by hidden channels. Fig, pomegranate, and date; tiled benches and chess tables; a pavilion where children learn letters in the heat of the day.</li><li><strong>Towers of the Wind</strong> - A street of windcatchers that pour cool air into vaulted houses and bathhouses. Kites run from tower to tower when the north wind is kind.</li><li><strong>Bayt al Waqf</strong> - The Endowment House. Public registers of fountains, schools, and bread ovens funded by charity. Inspectors audit the loaves by weight, not by smile.</li></ul>',
                ],
                [
                    'title' => 'Bath, Bread, and Bazaar',
                    'html' => '<p><strong>Begin with bread and dates.</strong> Flatbreads pulled hot from clay ovens, lentil soups, rice bright with saffron and nuts, lamb baked with herbs, and trays of sesame rings and honey sweets. The bathhouse culture is practical and kind: steam, scrub, tea, and news. Try the <strong>Crescent Coffee</strong>, cardamom-dark with a curl of orange peel, or cool off with <strong>sharbat</strong> of rose and mint. Street stalls near the main arches charge festival prices, 5 to 10 times the lane rate. Ask for the "waqf measure" and say you will tip the fountain fund; most sellers will smile and shave the number.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Lamp Ward</strong>, a watch that prefers ledgers to cudgels. The muhtasib walks the souq at noon, testing weights and temper. Restitution is favored over shame, and fines often end as bread for the poor. Even so, Ansurah has its shadows. Travelers whisper of the <strong>Sand Scribes</strong>, caravan fixers who move letters and favors along dune roads for a quiet fee. They claim to be guardians of flow, not a racket. Each season the Lamp Ward audits their charity chest in public at Bayt al Waqf. In this city even a shadow must show a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Water:</strong> accept a small cup when offered, sip, bless, and return with thanks. Do not waste fountain water. Children will correct you if you forget.</li><li><strong>Greetings:</strong> right hand to heart before words. Begin bargaining with a blessing, not a number. Laughter softens prices faster than thunder.</li><li><strong>Dress and shrines:</strong> cover shoulders and knees in the Law Quarter and temple courts. Remove shoes where mats begin. Do not step over a praying person.</li><li><strong>Markets:</strong> use a calm voice. If a stallkeeper will not name a price, they want a story or a sample, not coin.</li><li><strong>Siesta:</strong> honor the heat hour. The city hums again when shadows lengthen and lamps wake.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Ansurah keeps the <strong>Night of Two Lamps</strong> when river-law and desert-verse are read by alternating voices and the courts and fountains are lit together. In high summer the <strong>Market of Kites</strong> runs strings like music between windtowers. On the <strong>Day of Ledgers</strong> the Lamp Ward posts the city accounts in Bayt al Waqf and schoolchildren paint lanterns to thank the endowments that feed and teach them.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Ansurah is a city of measured light. Walk in shade without hurry, learn a blessing or two, and count mercy as part of the price. If you must overpay under the crescent arch, call it a donation to the lamps that make strangers feel like neighbors. The wind will remember you kindly.</p>',
                ],
            ],
            'back_label' => 'Back to Orienthem',
            'back_href' => '?pg=orienthem',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Caliphate-inspired pigments and motifs.', 'action' => 'Explore', 'href' => '?pg=cc_paint_shack', 'color' => '#06b6d4', 'points' => country_map_rect_points(260, 500, 250, 180, $w, $h)],
            ],
        ],
        'esd' => [
            'title' => 'Eagle Serpent Dominion - Coatlxochi',
            'subtitle' => 'Feathers, Stone, and Sun',
            'image' => 'images/harmontide-esd.webp',
            'lore' => 'Coatlxochi blends ceremonial plazas with martial tradition and serpent-eagle iconography.',
            'lore_sections' => [
                [
                    'title' => 'Coatlxochi - Flower of the Eagle and Serpent',
                    'html' => '<p><strong>Coatlxochi</strong> rises from a wide lake on stone islands linked by long causeways. White walls catch the sun; painted serpent rails coil along bridges; eagle standards tilt over plazas bright with banners and flower garlands. Gardens float on reed beds at the edges of the water, and canals stitch the city together like turquoise thread. In the Eagle Serpent Dominion, power is balance: sky and earth, blade and blossom, decree and gift.</p><p>The capital is a planner\'s dream and a pilgrim\'s maze. Council houses face stepped temples; ballcourts echo with rubber thunder; market squares bloom at dawn with maize, cacao, bright feathers, copper bells, obsidian blades, and woven cloth that holds whole stories. Priests pace shaded porticoes burning copal; scribes score tribute tallies on bark-paper; and canal boats carry both news and melons. Visitors learn fast: follow the banners, keep right on the causeways, and do not block the couriers in red paint.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Sun Crown Precinct</strong> - The central plaza with twin stepped temples. Political proclamations are read from the northern stair at midday; flower offerings and incense climb the southern stair at dusk.</li><li><strong>Market of Feathers</strong> - The grand market. Guild lanes for jade, featherwork, textiles, pottery, and tools. Inspectors weigh measures and test obsidian edges with a practiced thumb.</li><li><strong>Causeway Gates</strong> - Three great roads over water with gatehouses that lift wooden spans at night. Toll is light for traders, lighter for couriers, and paid in coin or maize cakes.</li><li><strong>Canal Quarter</strong> - Neighborhoods of bridges and boat docks. Painters stripe door lintels with family colors; herons hunt from roof corners at dawn.</li><li><strong>Ballcourt of Two Skies</strong> - A long sunken court where teams play under eagle and serpent carvings. Festival finals draw banners from every district.</li><li><strong>House of Reed and Stone</strong> - Council compound and scribal schools. Bark-paper books dry on racks; stone slabs hold tribute lists cut so clean they look like water.</li><li><strong>Chinampa Gardens</strong> - Floating fields anchored by willow stakes. Maize, beans, squash, flowers for the precinct altars, and a thousand dragonflies keeping summer honest.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat the lake and the garden.</strong> Steamed tamales, maize griddle cakes, lake fish with green herbs, squash blossom stews, black beans bright with chile and lime. Street sellers pour <strong>atole</strong> hot and sweet, or hand you a gourd of spiced <strong>cacao</strong> that hums like a drumline. Try the <strong>Eagle Skewer</strong> (pepper and herb chicken) or the <strong>Serpent Green</strong> (tomatillo and pumpkin seed sauce) at the Causeway Gates.</p><p>Tourist note: stalls facing the Sun Crown Precinct charge 5 to 10 times the lane rate. Ask for the "garden measure" and mention which chinampa you visited; most vendors will nod and drop the price to something a canal boatman would pay.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Eagle Guard</strong> on the plazas and the <strong>Serpent Ward</strong> on the canals. Fines turn into labor on causeways, gardens, and fountains; pride turns into a lecture from a grandmother who outranks any captain. Yet Coatlxochi keeps shadows under its bridges. People whisper about the <strong>Night Jaguars</strong>, merchant-brotherhood guides who move tribute and rare goods after curfew for a fee of shells and silence. They call it courtesy and flow, not a racket. The Ward audits their "charity baskets" at the Market of Feathers each moon; in this city even a shadow must show a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Causeways:</strong> keep right, leave the center to runners and litters. Do not stop on a bridge crest; step aside to look.</li><li><strong>Precincts:</strong> remove hats, speak low, and follow the hand signs of ushers. Do not point at temple tops; open your palm if you must indicate.</li><li><strong>Markets:</strong> greet the stall, praise the craft, then bargain. If a seller ties a red thread to your wrist, you owe a small tale with the coin.</li><li><strong>Gifts:</strong> flowers are safe, salt is strong, feathers are a compliment. Never gift a knife without a coin to "buy back" the cut.</li><li><strong>Water:</strong> step aside for water carriers and canal priests. If a boy with a shell horn asks you to hold a rope, you are helping dock a boat. Smile and pull.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Coatlxochi keeps the <strong>Rising Eagle</strong> at dry-season peak, when banners climb the stairs and ballgames crown champions. The first rains bring the <strong>Serpent Welcoming</strong>: canals fill with floating flowers and the city thanks the lake with music and maize. Every 52 years the <strong>Reed Year Binding</strong> renews civic oaths with lamps in every district and a night when the causeways glow like constellations across the water.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Let the city teach you its steps: right foot on the causeway, left on the boat, eyes on the banners. Eat what the gardens offer, tip the water carriers, and keep a flower for the precinct stairs. If you pay too much at the Sun Crown, call it a gift to the balance that lets eagles and serpents share a flag. The lake will remember your courtesy.</p>',
                ],
            ],
            'back_label' => 'Back to Gulfbelt',
            'back_href' => '?pg=gulfbelt',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Color your companions with dominion palettes.', 'action' => 'Explore', 'href' => '?pg=esd_paint_shack', 'color' => '#ef4444', 'points' => country_map_rect_points(260, 470, 220, 170, $w, $h)],
            ],
        ],
        'esl' => [
            'title' => 'Eretz-Shalem League - Shalemdor',
            'subtitle' => 'Covenants and Citadels',
            'image' => 'images/harmontide-esl.webp',
            'lore' => 'Shalemdor is a covenant city where law, trade, and pilgrimage routes converge.',
            'lore_sections' => [
                [
                    'title' => 'Shalemdor - Gate of Wholeness',
                    'html' => '<p><strong>Shalemdor</strong> climbs a sunlit ridge of pale stone, its lanes curling between courtyards, vaulted gates, and market arcades. Doorways carry small prayer-scroll cases at the lintel, olive trees lean over cisterns, and rooftops glow with clay lamps at dusk. In the Eretz-Shalem League, law is argued with learning and kindness is counted as much as coin; in Shalemdor those two threads are knotted on every street.</p><p>The city is a braid of study, trade, and song. Scholars debate in cool study halls, scribes mend letters in the shade, and the shuk rings with copper, textiles, spices, and children chasing pomegranate seeds. When the sun lowers, you will hear a ram horn from a gate tower and smell bread from every block oven. Visitors learn quickly: touch the mezuzah lightly, say "shalom," and follow the shade lines laid by generations of builders who knew the noon heat well.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Beit Midrash Quarter</strong> - Linked study houses where pages turn like wings. Disputes are written in wide margins; answers are walked, not shouted.</li><li><strong>Shuk HaShalem</strong> - The great market under woven canopies. Olive oil, figs, dates, copperwork, wool, glass beads, parchment, and good gossip. Scales are inspected at midday.</li><li><strong>Gate of Peace</strong> - Main eastern gate. A small court sits in its shade; travelers share water and get their first lesson in local prices.</li><li><strong>Water Steps</strong> - A tiered spring with stone basins. Families draw water at dawn; in the heat, elders sit and trade short proverbs for long stories.</li><li><strong>Hall of Scales</strong> - Civic hall and beit din. Cases are heard after both sides share bread; judgments prefer restitution and public charity.</li><li><strong>Oil Lamp Street</strong> - Lamp makers, metalworkers, and scribes. At dusk the whole lane lights at once and looks like a river of fireflies.</li><li><strong>Roofline Walk</strong> - Joined roofs where guards and neighbors cross in the cool hours. The view tells a quiet history in domes, chimneys, and fig trees.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Begin with bread and olives.</strong> Braided loaves for the rest day, flatbreads for the road, bowls of chickpea purees, sesame sauces, grilled eggplant, herbs, and river fish. Street sellers carry sesame rings, date cakes, and cups of herb tea. Try the <strong>Lion Gate Stew</strong> with chickpeas and lemon or a plate of <strong>Market Salads</strong> with pickles and fresh cheese. Coffee comes dark with cardamom; sweet mint tea chases the dust down.</p><p>Tourist note: stalls beside the main arches charge 5 to 10 times the lane rate. Ask for the "charity measure" and nod at the fountain fund box; most sellers will soften the price and point you toward a family stall one street over.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Olive Ward</strong>, a watch that counts lamps as carefully as fines. The market steward walks the shuk at noon, checking weights and tempers. Records of public charity are posted weekly at the Hall of Scales. Even so, Shalemdor keeps a few shadows for hot afternoons. People whisper about the <strong>Courtyard Keys</strong>, a circle of fixers who smooth permits and night deliveries for a fee of coin and silence. They claim to serve continuity, not coin alone. Each season their donation jars are opened in public; in this city even a shadow must show a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Greetings:</strong> right hand to heart and "shalom." Bargain with patience; begin with a blessing, not a number.</li><li><strong>Doorways:</strong> touch the mezuzah lightly, do not kiss it unless invited. Step in with respect and a smaller voice.</li><li><strong>Dress and courtyards:</strong> cover shoulders and knees in study houses and synagogues. Remove hats only when hosts do; head coverings are common.</li><li><strong>Rest day:</strong> from sundown to sundown shops close, lanes quiet, and lamps glow. Plan travel and purchases around it; neighbors will help if you misjudge the hour.</li><li><strong>Water:</strong> do not waste fountain water. Offer your place in line to elders and children; you will gain five friends and a better route across town.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Shalemdor keeps a calendar of light and harvest. The <strong>Festival of Lights</strong> brightens winter with small lamps in every window and stories of old courage. The <strong>Festival of Booths</strong> fills rooftops and courtyards with shaded huts where families eat and welcome guests. In early spring the <strong>New Year of Trees</strong> sees children planting saplings along the Water Steps. Between spring and summer, the days of <strong>Counting Weeks</strong> tie learning to harvest with daily verses posted on courtyard walls.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Walk in the shade and keep a proverb ready. Share water, leave a coin in a charity box, and ask a scribe to write your name in a good hand. If you pay too much under the Gate of Peace, call it a gift to the lamps that make strangers look like neighbors. The city will remember you kindly and in detail.</p>',
                ],
            ],
            'back_label' => 'Back to Orienthem',
            'back_href' => '?pg=orienthem',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Paint your pets in Shalemdor style.', 'action' => 'Explore', 'href' => '?pg=esl_paint_shack', 'color' => '#a855f7', 'points' => country_map_rect_points(220, 520, 230, 160, $w, $h)],
            ],
        ],
        'fom' => [
            'title' => 'Frankenondermeer',
            'subtitle' => 'Dikes, windmills, and low-country canals',
            'image' => 'images/harmontide-fom.webp',
            'lore' => 'Frankenondermeer is Rheinland\'s low-country sibling: wind, canals, and flood-smart towns built on stubborn engineering.',
            'lore_sections' => [
                [
                    'title' => 'Frankenondermeer - Where Rivers Weigh the Sky',
                    'html' => '<p><strong>Frankenondermeer</strong> lies where the great Rhinefork loosens into flat green polders, reed-choked creeks, and mirror-still canals. From above it looks like a ledger drawn on water: long dikes, straight roads on raised embankments, and square fields shining with dew. Bell towers and tide-sails rise from the mist, and the air always smells faintly of river salt, peat smoke, and frying batter.</p><p>The lowlands are careful and stubborn. Farmers walk the dike crowns at dawn, fingers on the wind; barge captains read the current like script; children learn to count by lock-gates and flood marks. Here, law is written in water levels and ledgers more than walls. When feelings run too high, the mists thicken and the old stories say the canals remember - spawning willful currents and mood-struck spirits that tug at unwary boats. The people of Frankenondermeer live by three equal measures: keep the water, keep the books, keep your temper.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Onderhaven Chain</strong> - A string of harbor towns on a shared ring dyke, their brick gables and crane-beams leaning over the main trade canal like they are eavesdropping on every cargo.</li><li><strong>Keerstide Locks</strong> - A massive stair of lock-gates and sluices where river and sea bargain daily. Tollkeepers keep both pages and tempers balanced; arguments wait until the next lock pool.</li><li><strong>Mistfen Commons</strong> - Wide peat marshes and grazing meadows edged with willow and rush. Lantern posts mark safe paths; locals say the lights flicker when emotions sour.</li><li><strong>Gable Crown Market</strong> - A barge-borne market that drifts between towns each week. Colorful awnings, cheese wheels, dried fish, canal-flowers, and ledger clerks with ink-stained cuffs.</li><li><strong>Stillekerk Spire</strong> - An old river-chapel on a manmade hill, its tower used as a flood mark. Bells ring three times when waters rise too fast, once when the village needs calm.</li><li><strong>Windward Mills</strong> - Tall tide-sails and windmills that grind grain and pump water, painted in house colors. At dusk their turning arms look like slow, patient guardians sweeping the sky.</li><li><strong>Ledgerhall of Frankenondermeer</strong> - Regional seat in a modest brick town, its great chamber lined with water gauges and open ledgers. Fines feed the dikes and the lamp fund before any purse.</li><li><strong>Reedsong Canals</strong> - Narrow side-canals where reed beds crowd the banks. Boatmen swear the wind in the reeds repeats overheard secrets and worries back to you in softer words.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat with the weather.</strong> On bright days you will find fried river fish, pickled eel, and thick slices of rye bread spread with sharp cheese and sweet syrup. In the damp chill, kitchens steam with cabbage stews, potato and leek soups, and pan-cakes poured thin and rolled around apples or canal-berry jam. Try a plate of <strong>Dikewalker Stew</strong> with smoked sausage and beans, or a cone of <strong>Harbor Crisps</strong>, tiny fish fried whole with lemon and herb salt. Brewers favor light, foamy ales for day work and dark, molasses-rich beers for long stories.</p><p>Tourist note: barge-front stalls at the busiest locks charge 5 to 10 times the village tavern rate. Ask for the "polder share" with a smile and nod toward the nearest dike box; most hosts will tap the price back to fair and suggest a family quay one bridge away.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Water and peace are watched by the <strong>Dikewardens</strong>, a civic ward that walks the embankments in blue-lined cloaks, carrying sounding poles and stamp-sealed notebooks. Their judgments favor restitution: repair the dike you neglected, share half the catch you hid, fund a new lamp along the foggiest towpath. Beneath the surface, boatmen whisper of the <strong>Canal Ledger</strong>, a loose fellowship of bargemasters and warehouse keepers who arrange quiet cargo shifts, night lockings, and discreet passage for those who pay and behave. They claim their work keeps trade flowing, not lawbreaking, and each thaw they nail their redacted accounts to the Ledgerhall door, line by line, for all to read.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Mind the dikes:</strong> ever sit on a dike edge with your feet kicking at the turf, and do not drive stakes without asking a Dikewarden.</li><li><strong>Step and greet:</strong> on narrow towpaths, step to the field side for those leading horses or hauling barges and offer a simple "dag" or "good tide."</li><li><strong>Boots and doorways:</strong> scrape or leave muddy boots at the threshold of farmhouses and lockhouses; wood floors are harder to dry than you think.</li><li><strong>Lantern law:</strong> carry a lantern or wear a reflector charm after dusk near canals. Locals will press a spare into your hand rather than let you walk dark.</li><li><strong>Bargaining:</strong> haggle gently at the moving markets, never at the flood-time relief stalls. Begin with thanks, not accusation, and let the seller write the final number.</li><li><strong>Mist manners:</strong> in heavy fog, keep your voice low and your temper lower. Shouting on the water is said to wake brooding spirits and invite tricky currents.</li><li><strong>Signals:</strong> learn the common bell and horn calls; three short notes at night usually mean "lock closed" or "do not approach."</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>The year in Frankenondermeer turns on water and wind. At the <strong>First Pumping</strong> in early spring, mills and tide-sails are blessed with chalk marks and songs to keep them turning true. The midsummer <strong>Night of Open Locks</strong> sees gates raised in careful sequence, lanterns strung along the canals, and flotillas of decorated boats gliding under quiet fireworks. In autumn, the <strong>Harvest of Reeds</strong> brings reed-cutters, weavers, and instrument makers together for markets and music. When winter fog lays thick, families share stories during the <strong>Mistwatch Evenings</strong>, telling of times when anger made the waters rise and how calm words brought them down again.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Keep your feet dry, your notebook handy, and your feelings folded, not bottled. Walk a dike at sunrise, listen to the reeds rehearse your worries, and let a lockkeeper tell you how many kinds of tide there are. If you pay too much for your first cone of Harbor Crisps at Keerstide, count it as a toll to the river and a lesson: in Frankenondermeer, everything flows back into balance, given time and a good ledger.</p>',
                ],
            ],
            'back_label' => 'Back to Rheingard',
            'back_href' => '?pg=rheinland',
            'areas' => [
                ['name' => 'Grachten Fishing', 'description' => 'Head to the canal event site for your daily cast.', 'action' => 'Go Fishing', 'href' => '?pg=fom-fishing', 'color' => '#22d3ee', 'points' => country_map_rect_points(670, 360, 280, 170, $w, $h)],
            ],
        ],
        'gc' => [
            'title' => 'Gran Columbia - Solvine',
            'subtitle' => 'City of Sun-Winds',
            'image' => 'images/harmontide-gc.webp',
            'lore' => 'Solvine is a bright trading city with strong civic plazas and highland-coast culture.',
            'lore_sections' => [
                [
                    'title' => 'Solvine - City of Sun and Vines',
                    'html' => '<p><strong>Solvine</strong> is a modern metropolis where glass towers climb above old plazas and hillside barrios spill down like terraces of green. The riverfront glitters at night, streetcars hum, and murals bloom across concrete like jacaranda. Gran Columbia built Solvine as a promise: culture, commerce, and sunlight in the same square meter.</p><p>The vibe is present-day and proud. Cafes open before dawn for cyclists and dockworkers, tech incubators share blocks with print shops, and corner tiendas sit beside regulated pharmacies. The city runs a clear legal framework for controlled substances and harm-reduction: licensed dispensaries, ID checks, health desks, and strict zoning. Street festivals feel electric but organized; you will see safety tents and water stations beside food trucks and sound stages.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Golden Mile</strong> - Riverwalk of parks, museums, and towers. Sunset turns the glass gold. Runners, families, and buskers share the path; food carts cluster near the footbridges.</li><li><strong>Cerro Verde</strong> - Hillside neighborhoods with cable-cars, stair murals, pocket gardens, and community ovens. Best city views, best street arepas.</li><li><strong>Plaza Sol</strong> - Historic core. Cathedral shadow on modern bricks, palms around a long fountain. Weekend book markets and artisan fairs set up here.</li><li><strong>Mercado Andino</strong> - Covered market with fruit pyramids, ceviche bars, coffee roasters, florists, and vinyl stalls. Prices honest, lines long for the roast pork stand.</li><li><strong>Juramento Complex</strong> - Civic and legal quarter. City Hall, public ombuds, and the Health & Safety Pavilion where the harm-reduction desks live.</li><li><strong>Parque del Vino</strong> - Riverbank amphitheater and lawns under vineyard trellises. Outdoor films, concerts, and evening dance classes.</li><li><strong>Solar Yard</strong> - Start-up sheds turned studios. Makerspaces, indie galleries, and food halls; most doors are murals waiting to happen.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat the valley.</strong> Arepas and empanadas at dawn, ceviche with sweet corn and plantain chips at noon, roast pork with crackling and aji by evening. Street coconuts, passionfruit juices, and coffee that could move a bus. Try a <strong>Solvine Cooler</strong> (lime, panela, sparkling water) or a glass from the urban wineries along Parque del Vino.</p><p>Tourist note: riverfront kiosks mark up 5 to 10 times compared to Mercado Andino. Ask for the "vecino rate" with a smile and a "por favor"; most vendors will meet you halfway and point you to the stall they eat at.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>The <strong>Civic Guard</strong> runs on cameras, body cams, and a lot of paperwork. Fines tilt toward community service and cleanup. Health teams work the nightlife beat with water, info cards, and free rides to clinics when needed. Still, whispers cling to the viaducts: the <strong>Vine Syndicate</strong> moves favors and fast deliveries through a web of couriers and coded stickers. They call it logistics, not a racket. The Guard audits charity ledgers and zoning permits every quarter; in Solvine even shadows get spreadsheeted.</p><p>Legal note: licensed pharmacies and lounges operate under zoning. Street sellers do not. If a price sounds too good, it is; the Civic Guard will agree. Use common sense, follow posted rules, and keep the emergency number saved.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Transit:</strong> tap card for streetcar and cable-car. Offer seats to elders. Keep doors clear; platform lines are not decorative.</li><li><strong>Nightlife:</strong> carry ID. Stay in lit corridors. Hydrate. Follow staff instructions at security checks and health tents.</li><li><strong>Markets:</strong> greet first, bargain second. Do not squeeze fruit you are not buying. Cash is king for small stalls; cards work most elsewhere.</li><li><strong>Hills:</strong> respect the stairs and the neighbors. Quiet hours start at 22:00 off the main drags.</li><li><strong>Safety:</strong> keep your bag zipped and your phone off the railings. If you feel watched, step into a lit shop or callbox; staff are trained to help.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Solvine keeps a calendar of light. <strong>Sunvine Week</strong> brings parades and vineyard nights. <strong>River Bloom</strong> turns the Golden Mile into a flower corridor. <strong>Night of Lanterns</strong> floats candles from the hill to the river. Civic days post budgets on city walls and hand out saplings for balcony gardens; kids race to claim the tallest ones.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Solvine wants you fed, sunlit, and on time. Walk the river at dusk, ride the cable-car at blue hour, tip street musicians, and drink water between coffees. If the kiosk charges too much, call it a view tax and move one block inland next time. The city will repay your patience with a mural, a melody, and a plate that tastes like warm stone and bright fruit.</p>',
                ],
            ],
            'back_label' => 'Back to Verdania',
            'back_href' => '?pg=verdania',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Gran Columbia regional colors and themes.', 'action' => 'Explore', 'href' => '?pg=gc_paint_shack', 'color' => '#f97316', 'points' => country_map_rect_points(250, 480, 230, 170, $w, $h)],
            ],
        ],
        'hammurabia' => [
            'title' => 'Hammurabia - Ziggurab',
            'subtitle' => 'Steps of Bronze Law',
            'image' => 'images/harmontide-hammurabia.webp',
            'lore' => 'Ziggurab rises in terraced districts where scribes and merchants keep meticulous records.',
            'lore_sections' => [
                [
                    'title' => 'Ziggurab - Stepped City of River-Law',
                    'html' => '<p><strong>Ziggurab</strong> rises from the fork of two brown rivers, a city of sun-baked brick, blue glaze, and shadowed lanes. Canals stitch the quarters like seams; reed boats nose the wharves; the air smells of date syrup, clay, and bitumen after heat. At its heart stands the great stepped temple that gave the city its name. In Hammurabia, the river teaches justice: measure fairly, keep channels clear, and do not move your neighbor\'s marker stone.</p><p>This is a city of tablets and toll-gates. Scribes press wedges into wet clay under porticoes while gaugers read rods and cords in the sun. Laws are posted as steles in market courts; elders recite them by memory with a hand on a basket of grain. When the south wind drops, you can hear the ziggurat stair hum with pilgrims counting each step like a quiet abacus of days.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Stair of the Stars</strong> - The ziggurat precinct. Stepped terraces, a shrine at the top for dawn offerings, and a shadow-court below where verdicts are read with water shared first.</li><li><strong>Tablet House</strong> - Scribal quarter. Schools for numbers, contracts, and maps of canals. Public tablets record floodmarks going back farther than anyone\'s grandmother remembers.</li><li><strong>Gate of Lions</strong> - Glazed-brick processional gate. Market toll is taken here; weights are checked at noon on a public balance beam.</li><li><strong>Canal of Measures</strong> - Main waterway lined with workshops. Potters, metal casters, fabric dyers, and the House of Reed where boaters mend their craft.</li><li><strong>Bitumen Wharf</strong> - Low wharf where barges load jars and bricks. Smells strong but honest. The tide board here is the city\'s metronome.</li><li><strong>Date Grove Quarter</strong> - Lanes of palms and mud-brick courtyards. Press houses boil syrup; children climb stone ricks pretending they are towers.</li><li><strong>House of Weights and Rods</strong> - Civic office that certifies measures. Marks are re-cut at the new year; fines for tampered scales are severe and public.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat from grove and river.</strong> Flatbreads, grilled river fish with herbs, lentil pots, onion stews, goat skewers with coriander, and date cakes sticky as afternoon sun. Street sellers pour barley beer and pressed date water; travelers swear by clay cups of sour yogurt in the heat. Try the <strong>Stele Platter</strong> near the Gate of Lions: bread, soft cheese, olives, pickles, and a scoop of sweet date paste.</p><p>Tourist note: stalls beside the ziggurat steps and the Gate of Lions run 5 to 10 times the lane rate. Ask for the "canal measure" and mention the last floodmark year; most sellers will smile and shave the number.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Canal Ward</strong>, a watch that travels on foot and by skiff with ledgers and rods. Restitution and canal work are the usual sentences; those who foul the water sweep the steps at dawn. Even so, Ziggurab keeps a few shadows under its arches. Travelers whisper about the <strong>Marker Guild</strong>, quiet fixers who arrange night deliveries and "lost-and-found" marker stones for a fee in silver and silence. They call it keeping flow, not a racket. Each season the Canal Ward opens their charity jars in public at the House of Weights; in this city even a shadow must show a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Water:</strong> step aside for carriers. Do not bathe or wash animals in public channels. Offer a dipper to elders before yourself.</li><li><strong>Gates and courts:</strong> hats off under the Gate of Lions. Speak low; verdicts are heard after both sides share water.</li><li><strong>Markets:</strong> greet the stall, praise the craft, then bargain. Weights may be checked on the public beam; smart sellers will offer before you ask.</li><li><strong>Stairs:</strong> on the ziggurat, keep to the right, count your steps if you like, and do not block the landings. Offerings are small: flowers, grain, clean water.</li><li><strong>Heat:</strong> rest in shade at high sun. The city wakes again when the brick cools and lamps glow in niches.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Ziggurab keeps the <strong>Flood Calling</strong> at river-rise: clay tablets with last year\'s height are washed and re-inscribed, and officials cast bread to thank the river. The <strong>Binding of Measures</strong> at new year re-marks rods and recalibrates weights. On <strong>Night of Lamps</strong> families place oil lights on canal edges so the water can find its way. Harvest ends with the <strong>Date Boil</strong>, when syrup kettles run all night and the air tastes like copper and honey.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Ziggurab is patient and exact. Walk its lines, mind its markers, and drink its water with gratitude. If you pay too much by the Gate of Lions, call it a contribution to the balance beam that keeps trade honest. The river will remember your manners when the banks grow tight.</p>',
                ],
            ],
            'back_label' => 'Back to Orienthem',
            'back_href' => '?pg=orienthem',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Regional paints from the workshops of Ziggurab.', 'action' => 'Explore', 'href' => '?pg=h_paint_shack', 'color' => '#84cc16', 'points' => country_map_rect_points(230, 510, 250, 170, $w, $h)],
            ],
        ],
        'ie' => [
            'title' => 'Itzam Empire - Itzankaan',
            'subtitle' => 'Jade Courts and Canals',
            'image' => 'images/harmontide-ie.webp',
            'lore' => 'Itzankaan pairs imperial ceremony with vibrant waterway life and star-calendar observances.',
            'back_label' => 'Back to Gulfbelt',
            'back_href' => '?pg=gulfbelt',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Empire colors for local creatures.', 'action' => 'Explore', 'href' => '?pg=ie_paint_shack', 'color' => '#14b8a6', 'points' => country_map_rect_points(230, 500, 245, 175, $w, $h)],
            ],
        ],
        'kemet' => [
            'title' => 'Kemet - Ankhmeru',
            'subtitle' => 'River Crown of Sand and Stone',
            'image' => 'images/harmontide-k.webp',
            'lore' => 'Ankhmeru is a Nile-like capital of monuments, scholars, and river-borne markets.',
            'back_label' => 'Back to Saharene',
            'back_href' => '?pg=saharene',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Kemet-inspired patterns and paint.', 'action' => 'Explore', 'href' => '?pg=k_paint_shack', 'color' => '#eab308', 'points' => country_map_rect_points(250, 480, 250, 170, $w, $h)],
                ['name' => 'Shelter Adventure', 'description' => 'Investigate strange happenings in the local shelter.', 'action' => 'Begin', 'href' => '?pg=k-adventure', 'color' => '#6366f1', 'points' => country_map_rect_points(720, 320, 250, 170, $w, $h)],
            ],
        ],
        'ldk' => [
            'title' => 'Lotus-Dragon Kingdom - Shenhedu',
            'subtitle' => 'Silk, Steel, and Lanterns',
            'image' => 'images/harmontide-ldk.webp',
            'lore' => 'Shenhedu balances imperial ritual, artisan guilds, and riverfront commerce.',
            'back_label' => 'Back to Dawnmarch',
            'back_href' => '?pg=dawnmarch',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Lotus-Dragon paints and motifs.', 'action' => 'Explore', 'href' => '?pg=ldk_paint_shack', 'color' => '#ec4899', 'points' => country_map_rect_points(250, 500, 230, 165, $w, $h)],
                ['name' => 'Breeding Pavilion', 'description' => 'Visit the breeding pavilion of Shenhedu.', 'action' => 'Visit', 'href' => '?pg=ldk_breeding', 'color' => '#06b6d4', 'points' => country_map_rect_points(700, 350, 250, 170, $w, $h)],
            ],
        ],
        'rheinland' => [
            'title' => 'Rheinland - Rheingard',
            'subtitle' => 'Iron Rivers, Ordered Streets',
            'image' => 'images/harmontide-rheingard.webp',
            'lore' => 'Rheingard is an industrial capital of strict timetables, workshops, and fortress bridges.',
            'back_label' => 'Back to Auronia',
            'back_href' => '?pg=auronia',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Rheinland palette and trims.', 'action' => 'Explore', 'href' => '?pg=rl_paint_shack', 'color' => '#64748b', 'points' => country_map_rect_points(235, 500, 250, 170, $w, $h)],
                ['name' => 'Frankenondermeer', 'description' => 'Visit the low-country canals of Frankenondermeer.', 'action' => 'Explore', 'href' => '?pg=fom', 'color' => '#14b8a6', 'points' => country_map_rect_points(620, 360, 280, 170, $w, $h)],
                ['name' => 'Fairy Fountain', 'description' => 'A moonlit cave where coins wake a shy river fairy.', 'action' => 'Enter the cave', 'href' => '?pg=rl_ff', 'color' => '#38bdf8', 'points' => country_map_rect_points(980, 250, 230, 150, $w, $h)],
            ],
        ],
        'rsc' => [
            'title' => 'Red Sun Commonwealth - Redwind',
            'subtitle' => 'Crimson Towers and Coin-Fairs',
            'image' => 'images/harmontide-rsc.webp',
            'lore' => 'Redwind is a proud commonwealth capital known for market games and showman culture.',
            'back_label' => 'Back to Uluru',
            'back_href' => '?pg=uluru',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Paint local pets with Redwind tones.', 'action' => 'Explore', 'href' => '?pg=rsc_paint_shack', 'color' => '#ef4444', 'points' => country_map_rect_points(250, 500, 240, 170, $w, $h)],
                ['name' => 'Wheel of Fate', 'description' => 'Spin for rewards in the city carnival district.', 'action' => 'Spin', 'href' => '?pg=rsc-wof', 'color' => '#22c55e', 'points' => country_map_rect_points(700, 330, 240, 170, $w, $h)],
            ],
        ],
        'rt' => [
            'title' => 'Rodinian Tsardom - Velesgrad',
            'subtitle' => 'Frostdomes and Gilded Courts',
            'image' => 'images/harmontide-rt.webp',
            'lore' => 'Velesgrad is a tsardom capital of grand avenues, onion domes, and winter festivals.',
            'back_label' => 'Back to Auronia',
            'back_href' => '?pg=auronia',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Tsardom-inspired coat colors.', 'action' => 'Explore', 'href' => '?pg=rt_paint_shack', 'color' => '#60a5fa', 'points' => country_map_rect_points(230, 500, 250, 170, $w, $h)],
            ],
        ],
        'sc' => [
            'title' => 'Sila Council - Qilaktuk',
            'subtitle' => 'Aurora Council at the Ice Edge',
            'image' => 'images/harmontide-sc.webp',
            'lore' => 'Qilaktuk is a northern council-city shaped by sea ice, seasonal travel, and shared stewardship.',
            'back_label' => 'Back to Tundria',
            'back_href' => '?pg=tundria',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Icy colors and arctic motifs.', 'action' => 'Explore', 'href' => '?pg=sc_paint_shack', 'color' => '#22d3ee', 'points' => country_map_rect_points(250, 500, 240, 170, $w, $h)],
            ],
        ],
        'sie' => [
            'title' => 'Sapa Inti Empire - Intirumi',
            'subtitle' => 'The High Sun Seat',
            'image' => 'images/harmontide-sie.webp',
            'lore' => 'Intirumi perches in mountain terraces where ritual roads and market stairs intertwine.',
            'back_label' => 'Back to Verdania',
            'back_href' => '?pg=verdania',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Sapa Inti pet paints.', 'action' => 'Explore', 'href' => '?pg=sie_paint_shack', 'color' => '#f59e0b', 'points' => country_map_rect_points(250, 500, 245, 165, $w, $h)],
            ],
        ],
        'srl' => [
            'title' => 'Spice Route League - Navakai',
            'subtitle' => 'Harbor of Sails and Spice',
            'image' => 'images/harmontide-srl.webp',
            'lore' => 'Navakai thrives on sea trade, spice guilds, and vibrant dockside markets.',
            'back_label' => 'Back to Moana Crown',
            'back_href' => '?pg=moana_crown',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Spice Route League custom colors.', 'action' => 'Explore', 'href' => '?pg=srl_paint_shack', 'color' => '#f43f5e', 'points' => country_map_rect_points(250, 500, 250, 170, $w, $h)],
            ],
        ],
        'stap' => [
            'title' => 'Sovereign Tribes - Turtlestar',
            'subtitle' => 'Circle Fires and Open Plains',
            'image' => 'images/harmontide-stap.webp',
            'lore' => 'Turtlestar is a plains capital organized around council circles, trade paths, and sky festivals.',
            'back_label' => 'Back to Borealia',
            'back_href' => '?pg=borealia',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Ancestral Plains region paints.', 'action' => 'Explore', 'href' => '?pg=stap_paint_shack', 'color' => '#f97316', 'points' => country_map_rect_points(250, 510, 250, 165, $w, $h)],
            ],
        ],
        'urb' => [
            'title' => 'United free Republic of Borealia - Meridian Arc',
            'subtitle' => 'Free Ports and Arc-Lights',
            'image' => 'images/harmontide-urb.webp',
            'lore' => 'Meridian Arc is a republican metropolis balancing trade rights, free districts, and civic debate.',
            'back_label' => 'Back to Borealia',
            'back_href' => '?pg=borealia',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Borealia regional paints.', 'action' => 'Explore', 'href' => '?pg=urb_paint_shack', 'color' => '#3b82f6', 'points' => country_map_rect_points(240, 500, 250, 170, $w, $h)],
                ['name' => 'Adventure District', 'description' => 'Take on adventures around Meridian Arc.', 'action' => 'Begin', 'href' => '?pg=urb-adventure', 'color' => '#a855f7', 'points' => country_map_rect_points(690, 340, 250, 170, $w, $h)],
                ['name' => 'Adventure District II', 'description' => 'Continue with higher stakes adventures.', 'action' => 'Continue', 'href' => '?pg=urb-adventure2', 'color' => '#fb7185', 'points' => country_map_rect_points(980, 260, 220, 150, $w, $h)],
            ],
        ],
        'xochimex' => [
            'title' => 'Xochimex - Xochival',
            'subtitle' => 'Festival Canals and Flowers',
            'image' => 'images/harmontide-xochimex.webp',
            'lore' => 'Xochival is a festival-rich city of flower markets, music, and floating neighborhoods.',
            'back_label' => 'Back to Gulfbelt',
            'back_href' => '?pg=gulfbelt',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Xochimex colorways and patterns.', 'action' => 'Explore', 'href' => '?pg=xm_paint_shack', 'color' => '#10b981', 'points' => country_map_rect_points(240, 500, 250, 165, $w, $h)],
            ],
        ],
        'yamanokubo' => [
            'title' => 'Yamanokubo - Amatera',
            'subtitle' => 'Neon Lanes and Shrine Hills',
            'image' => 'images/harmontide-yamanokubo.webp',
            'lore' => 'Amatera blends old shrine hills with high-energy nightlife and story-rich backstreets.',
            'back_label' => 'Back to Dawnmarch',
            'back_href' => '?pg=dawnmarch',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Yamanokubo painting station.', 'action' => 'Explore', 'href' => '?pg=ynk_paint_shack', 'color' => '#8b5cf6', 'points' => country_map_rect_points(250, 500, 250, 170, $w, $h)],
                ['name' => 'Ramen District', 'description' => 'Warm up at the city ramen hall.', 'action' => 'Eat', 'href' => '?pg=ynk-ramen', 'color' => '#f97316', 'points' => country_map_rect_points(700, 350, 230, 170, $w, $h)],
                ['name' => 'Adventure Gate', 'description' => 'Begin a local adventure route.', 'action' => 'Begin', 'href' => '?pg=ynk-adventure', 'color' => '#22c55e', 'points' => country_map_rect_points(980, 260, 220, 150, $w, $h)],
            ],
        ],
        'yn' => [
            'title' => 'Yara Nations - Warraluma',
            'subtitle' => 'Where River Meets Song',
            'image' => 'images/harmontide-yn.webp',
            'lore' => 'Warraluma stands between river and escarpment, with Country-led stewardship and gathering grounds.',
            'back_label' => 'Back to Uluru',
            'back_href' => '?pg=uluru',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Yara Nations custom paint station.', 'action' => 'Explore', 'href' => '?pg=yn_paint_shack', 'color' => '#06b6d4', 'points' => country_map_rect_points(250, 500, 250, 170, $w, $h)],
            ],
        ],
    ];

    if (!isset($configs[$slug])) {
        return null;
    }

    return $configs[$slug] + ['width' => $w, 'height' => $h];
}
