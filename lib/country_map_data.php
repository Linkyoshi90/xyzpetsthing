<?php

require_once __DIR__.'/country_interactive_map.php';
require_once __DIR__.'/map_unlocks.php';

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
            'lore_sections' => [
                [
                    'title' => 'Itzankaan - Serpent of the Sky',
                    'html' => '<p><strong>Itzankaan</strong> crowns a limestone ridge between jungle and sinkhole lakes. White plaster gleams on stepped temples, serpent balustrades curl down sun-stairs, and straight stone causeways cut the green like pale lightning. In the Itzam Empire the city is both calendar and crown: days are read in shadows along stair edges, laws are carved in glyphs on stelae, and water is counted as carefully as tribute.</p><p>This is a capital of astronomers, builders, and gardeners. Stargazers watch from roof platforms, masons smooth lime by hand, and scribes paint codices in red and black. Canal canoes slide from cenote to market; runners carry shell trumpets and reed messages along the sacbe roads. Visitors learn fast: follow the white stones, keep your voice low at temple feet, and step wide of the iguanas sunning on the plaza edge.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Serpent Stair Precinct</strong> - Twin pyramids frame the main plaza. On equinox, serpent shadows ripple down the north stair to greet the crowd. Decrees are read at noon from the south terrace.</li><li><strong>House of Glyphs</strong> - Scribes, painters, and binders. Bark-paper codices dry on racks; apprentices grind pigment while masters mend old histories.</li><li><strong>Ballcourt of Banners</strong> - A long sunken court where teams play under scarlet pennants. The crowd speaks with rattles and hand signs more than shouts.</li><li><strong>Cenote Quarter</strong> - Plazas around open blue water. Rope ladders, shaded steps, and shrines for thanks. Water officials log levels on a stone board at dawn.</li><li><strong>Sacbe Gate</strong> - Start of the straight white road to the outlands. Runners rest in shade niches, traders stack baskets by glyph-marked weights.</li><li><strong>Garden Terraces</strong> - Maize, beans, squash, and flowers step the hill in green combs. Beehives sit in clay pots; honey is treaty and dessert both.</li><li><strong>Sounding Court</strong> - A plaza where a clap comes back as a flock of echoes. Musicians meet here at dusk with drums and shell horns.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat the terrace and the lake.</strong> Fresh tortillas, tamales steamed in leaves, chiltomate salsas, squash blossom stews, lake fish with herbs, and turkey with achiote. Street cups brim with atole hot and thick or cacao spiced and frothy. Try the <strong>Serpent Green</strong> sauce at the market grills or a pile of lime-cured fish with maize crisps at the cenote steps.</p><p>Tourist note: stalls facing the Serpent Stair Precinct cost 5 to 10 times more than lane stands. Ask for the "garden measure" and name a terrace you visited; vendors usually nod and pour you the price a canal boatman pays.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>White Road Ward</strong> on the sacbe and the <strong>Cenote Guard</strong> at the water. Fines favor labor on terraces, steps, and drains. Even so, the city keeps shade. People whisper about the <strong>Night Bats</strong>, a courier brotherhood that moves sealed bundles and delicate favors after curfew for a fee of shells and silence. They call it flow, not a racket. Each moon the Ward opens their charity baskets in public at the House of Glyphs; in Itzankaan even a shadow shows a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Temples:</strong> hats off, voices low. Do not climb beyond the rope rail. Offer a flower, a little maize, or clean water.</li><li><strong>Cenotes:</strong> rinse feet, not clothes. No soap in open water. Let elders descend first and keep the steps clear.</li><li><strong>Markets:</strong> greet with an open palm, praise the craft, then bargain. If a seller ties a thread to your wrist, you owe a short tale with your coin.</li><li><strong>Causeways:</strong> keep right for runners. Step aside at shell horn, then wave them through.</li><li><strong>Heat:</strong> rest in shade at high sun. The city wakes again when the plaster cools and drums begin.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Itzankaan keeps the <strong>Binding of Years</strong> when cycle stones are turned and oaths renewed with lamps across the sacbe. Equinox brings the <strong>Serpent Descent</strong> at the north stair; the first rains host the <strong>Flowering Waters</strong> as boats spill petals into the cenotes. On the <strong>Night of Echoes</strong> musicians test the Sounding Court and children chase the clap that turns into birds.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Walk the white roads, count the stair shadows, and drink water with thanks before wit. Tip the runner who answers your lost look, buy honey where the bees fly, and keep a flower for the temple steps. If you pay too much by the serpent stair, call it tribute to the calendar that keeps the city and the sky in step. Itzankaan will remember you kindly in glyph and in gossip.</p>',
                ],
            ],
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
            'lore_sections' => [
                [
                    'title' => 'Ankhmeru - City of the Living Pillar',
                    'html' => '<p><strong>Ankhmeru</strong> stands on a bend of the Great River, its twin banks joined by barges and a long stone bridge of lion pylons. East-bank temples lift painted pylons against the sun; west-bank terraces climb toward the desert where tomb roads vanish into golden cliffs. In Kemet, measure and memory keep the world upright; in Ankhmeru both are written on stone and water.</p><p>The city breathes in three beats: temple, granary, river. Priests time the day with shadow and incense; scribes tally grain with reed pens in cool storehouses; boatmen muscle cedar oars under nets of swallows. At noon the hypostyle halls hold shade like a lake, and the blue of the ceiling is speckled with stars to remind the heat it is only borrowing the sky.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Pylon of the Dawn</strong> - Eastern temple gate painted with banners of sun and lotus. Courts inside stage processions; the sacred lake lies beyond a colonnade where palm shadows stripe the water.</li><li><strong>Hall of Pillars</strong> - Forest of painted columns. Petitioners tie linen wishes to railings; the air smells of resin, cool water, and old stories.</li><li><strong>House of Life</strong> - Scriptorium and clinic. Scribes copy healing texts; herb jars line mud-brick shelves; novices learn letters by tracing birds, hands, and waves.</li><li><strong>Nilometer Court</strong> - Stair and shaft cut to the river for reading the flood. Officials carve the height on stone; tax and feast both follow the mark.</li><li><strong>Granary Row</strong> - Long vaulted barns with seal-rooms and tally niches. Wheat dunes under reed mats; cats patrol like little gods with whiskers.</li><li><strong>Market of Palms</strong> - Palm-roofed stalls, copper and alabaster, baskets, faience, linen, dates, onions, fish strung like silver. Drummers call the cool hour when prices soften.</li><li><strong>Westbank Necropolis Way</strong> - Processional road between guardian statues. Carvers tap reliefs by lamplight; tomb stewards water acacia trees and chase away hyenas with jokes.</li><li><strong>Harbor of Papyrus</strong> - Boat sheds and net yards. Reed skiffs, broad-bellied grain barges, cedar ships with painted eyes that watch the current.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Bread and beer first, then stories.</strong> Flat loaves warm from dome ovens, barley beer poured cool and cloudy, river fish with coriander, fava stews, cucumbers with salt, figs and dates, honey cakes shaped like lotus buds. Try <strong>Flood Platter</strong> near the Nilometer: bread, soft cheese, pickled onions, olives, and a sweet spoon of date paste. In the evening, sip <strong>Lotus Water</strong> (herb and blossom infusion) or a cup of desert wine from a clay jug that sweats in the shade.</p><p>Tourist note: stalls under the Pylon of the Dawn and at the westbank ferry charge 5 to 10 times the lane rate. Ask for the "scribe measure" and compliment the tally marks; most sellers will smile and lower the number to something a boatman would pay.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Medjay Ward</strong>, sand-cloaked watch with ledgers and batons. Fines favor restitution and temple work: sweeping courts at dawn, hauling water to gardens, repairing quay stones. Yet Ankhmeru is old and shade grows in old places. People whisper of the <strong>Copper Jackals</strong>, night runners who move sealed bundles between tomb ramps and river sheds for a fee of silence and bronze. They call it keeping flow, not a racket. Each season the Medjay open their charity jars in public at the House of Life; in this city even a shadow must show a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Temples:</strong> hats off, shoulders covered. Walk sunwise unless told otherwise. Do not touch reliefs; stone remembers fingers.</li><li><strong>Offerings:</strong> small and simple: a flower, a pinch of incense, a cup of clean water. Announce your name softly to the gate.</li><li><strong>Necropolis:</strong> voices low, feet careful. Do not sit on sarcophagi or lean on painted walls. Guides earn their copper by knowing where not to point a torch.</li><li><strong>Markets:</strong> greet with a palm over heart, praise the craft, then bargain. Weights are stones; ask to see the balance and you will be trusted more, not less.</li><li><strong>River:</strong> make way for water carriers and funerary barges. If a boy asks you to hold a rope, you are helping dock a grain boat. Smile and pull.</li><li><strong>Heat:</strong> rest at high sun in colonnades and gardens. The city wakes again when the brick cools and lamps bloom in niches.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Ankhmeru keeps the <strong>Opening of the Year</strong> when the first flood marks are cut and garlands float down the river. The <strong>Procession of the Living God</strong> carries a veiled image by boat between east and west with music and cheers on both banks. In the <strong>Festival of the Valley</strong> families picnic at tomb doors, sharing bread with the quiet. At the <strong>Weighing of Words</strong> (a civic rite, not a trial) scribes read old promises in the Hall of Pillars and citizens add one new line to the ledger of Ma\'at.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Ankhmeru prefers measure to hurry. Walk in straight lines and cool shadows, count your change with a smile, and leave a cup of water where a stranger will find it. If you pay too much at the pylon, call it an offering to the pillars that keep the sky up. The river will remember your courtesy when the flood comes thin.</p>',
                ],
            ],
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
            'lore_sections' => [
                [
                    'title' => 'Shenhedu - Capital of the Divine River',
                    'html' => '<p><strong>Shenhedu</strong> spreads along a slow blue river, its skyline a forest of upturned eaves and watchtowers. Red pillars, green tiles, white plaster, and shadowed courtyards stitch together canals, scholar gardens, and teahouse lanes. Think late imperial splendor: drum and bell towers mark the hours, guild halls face stone bridges, and pagodas step skyward like prayer in wood and tile. In the Lotus-Dragon Kingdom, harmony is policy; in Shenhedu it is carpentered into every beam.</p><p>The city breathes in rhythms of study, craft, and rite. Magistrates read petitions under veranda shade; calligraphers grind ink until it shines; boatmen punt beneath willow arches while tea steam climbs like a quiet dragon. The Lotus-Dragon Way is lived as courtesy: balance your steps, your price, and your voice. The river does not hurry and neither does good work.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Bell and Drum Terrace</strong> - Twin towers on a stone rise. The bell calls dawn, the drum settles dusk. Climb at the hour change and feel the timbers sing.</li><li><strong>Celadon Gate</strong> - Main southern gate with guardian beasts and a plaque in gilt characters. Market inspectors and storytellers share the shade here.</li><li><strong>Lotus Court</strong> - Temple and cloister around a mirror pond. Monks tend lanterns; Daoist adepts read wind over water. Offer a single flower, not a bouquet.</li><li><strong>Scholar Gardens</strong> - Rock, pine, water, and pavilion in perfect argument. Windows frame borrowed views; paths invite slow feet and fast thoughts.</li><li><strong>Hutong North</strong> - Alley neighborhoods of gray brick, red doors, and courtyard homes. Cricket sellers, kite makers, paper shops, and the best sesame noodles after rain.</li><li><strong>Jade Canal</strong> - Working waterway lined with warehouses and tea barges. Stone humpback bridges give boats a polite headache and painters a living.</li><li><strong>Guild Street</strong> - Carpenters, lacquerers, silk weavers, and bronze casters behind carved plaques. Apprentices bow with sawdust in their hair.</li><li><strong>Azure Pagoda</strong> - Seven stories of timber and tile. Pilgrims circle sunwise; the wind at the fifth landing smells like pine and old paper.</li></ul>',
                ],
                [
                    'title' => 'Food and Tea',
                    'html' => '<p><strong>Eat with balance, drink with patience.</strong> Steamed buns with pork or mushrooms, river fish with ginger and scallion, tofu braised in clay pots, pea shoots with garlic, duck lacquered until the skin crackles, and noodles pulled like silk. Teahouses pour green, oolong, and dark leaf with a bow and a timer. Try the <strong>Lotus Banquet</strong> in Lotus Court for festival days or a <strong>Boatman Bowl</strong> of rice, pickles, and hot broth along Jade Canal.</p><p>Tourist note: stalls under the Celadon Gate and by the pagoda steps charge 5 to 10 times the lane price. Ask for the "neighbor measure" and compliment the brushwork on the signboard; most sellers will smile and shave the number.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>River Magistracy</strong> and the <strong>City Watch</strong>, more ledgers than spears on most days. Fines favor restoration: sweeping temple courts, mending bridge rails, or copying rules in neat hand. Even so, Shenhedu has shade beneath its eaves. People mention the <strong>Night Couriers</strong>, discreet go-betweens who move sealed letters and delicate shipments after curfew for a fee of tea bricks and silence. They call it courtesy, not a racket. Each season their donation chests are opened in public at the Bell Terrace; in this city even a shadow shows a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Greetings:</strong> a small bow with hands folded. Loud voices are for rivers in flood, not markets.</li><li><strong>Shrines and halls:</strong> hats off, shoes clean. Incense one stick at a time. Do not tap carved dragons for luck; they bite in stories and splinter in truth.</li><li><strong>Tea:</strong> tap the table with two fingers to thank a pour. Do not drown leaves. Your cup will be filled if your manners are.</li><li><strong>Markets:</strong> praise the craft, then bargain. If a vendor ties a red cord around your purchase, it is blessed against clumsiness; do not cut it until you get home.</li><li><strong>Curfew:</strong> drum at second watch. Lanterns dim in hutongs; keep to main lanes or hire a licensed lantern guide.</li><li><strong>Boats and bridges:</strong> give way to water bearers and funeral barges. On humpbacks, do not stop at the crest to pose; step aside to look.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Shenhedu keeps the <strong>Lantern River</strong> at first full moon: canals glow with floating lights and bridges wear silk. <strong>Clear Bright</strong> sends families to sweep graves and picnic under willow. In midsummer the <strong>Dragon Boat Days</strong> thunder down the Jade Canal, oars beating like drums. At <strong>Autumn Moon</strong> neighbors trade cakes and poems on rooftops. Temples, academies, and guild halls share courtyards; the Lotus-Dragon Way likes truth to sit where tea can reach it.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Walk softly, watch closely, and let the city measure your steps to its drum and bell. If you overpay under the Celadon Gate, call it a donation to the eaves that keep rain off strangers. The river will forgive math done with a bow.</p>',
                ],
            ],
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
            'lore_sections' => [
                [
                    'title' => 'Rheingard - Keep of the River Crown',
                    'html' => '<p><strong>Rheingard</strong> rises where the broad river bends like a mailed arm around a hill. Half the city is stone and bell, the other half oak and guildmark: pointed roofs, steep lanes, bridge towers with iron grilles, and a market square that can hold a coronation at noon and a cattle fair by dusk. In Rheinland, law wears a tabard and carries a ledger; faith keeps company with craft; and the river decides who prospers by who minds the tides.</p><p>The city keeps a memory of the old empires without pretending they were simple. You will find an <strong>Imperial Hall</strong> where electors once argued charters, a dozen guildhouses with saints painted on their beams, and cloisters where scribes debate angels and tariffs with equal heat. Ask about the marshlands downriver and people lower their voices: somewhere beyond the weirs lies <strong>Frankenondermeer</strong>, the low country of dikes and long memories. The river knows how to keep a secret and so do Rheingarders.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Imperial Hall</strong> - Timbered and stone, bannered with old colors. The Diet met here in sterner days; now the city council reads accounts and swears new watch captains on a brass-bound Gospel of Law.</li><li><strong>Cathedral March</strong> - The cathedral close and its processional way. Stone saints look down on merchants and poor alike; on feast days the nave fills with candle smoke and song.</li><li><strong>Schanz Gate</strong> - Bastioned gate with a killing lane that now serves as a toll court. The tollbook is public at noon; cheating earns a bellring and a day hauling water.</li><li><strong>Stonebridge Quarter</strong> - Houses built like laced boots along the great bridge, with mills turning under the arches. Bridge chapels bless travelers and keep ledgers of alms.</li><li><strong>Guildring</strong> - Smiths, tanners, masons, vintners. Guild signs creak above doors; apprentices chalk pride on doorsteps after a good day at the forge.</li><li><strong>Watchers Walk</strong> - The wall path. Belltowers speak across the roofs; a triple ring means fire, a steady roll means flood. Visitors are welcome to climb the south tower for the view if they mind their hat on the wind.</li><li><strong>Scholars Cloister</strong> - A sober court of lime trees and disputation. Manuscript rooms, a disputation hall, and a modest press that smells of ink and ambition.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat like a guild guest.</strong> Pretzels the size of shields, onion tarts, roasts with dark gravy, river-fish with butter and dill, and red cabbage sweet as a hymn. Street braziers hiss with sausages; bakers dust rye loaves with malt. Order a <strong>Rheingard Freibrau</strong>, a copper-dark beer, or a pale wine from terraces upriver. If you are offered a monk cheese with caraway, take it and the advice that comes with it: never speak business until the second slice.</p><p>Tourist truth: anything sold under cathedral eaves or on the bridge will cost 5 to 10 times more. Ask for the "guild rate" and be ready to name a craft you admire; the seller will likely lower the number and tell you where to find the good stuff off the square.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Belltower Ward</strong>, a watch that writes as much as it arrests. Their captains are called <em>Ledger Knights</em>; their swords are shorter than their ink lists. Fines prefer restitution and public work. Yet Rheingard is old, and old cities have shade. People murmur about the <strong>Tollring</strong>, a brotherhood that oils hinges and opens stubborn gates for a fee after curfew. They insist they are a courtesy, not a racket. The Ward disagrees, politely, and audits their alms to flood widows each spring.</p><p>Downriver talk of <strong>Frankenondermeer</strong> comes wrapped in weather. Some say its dike-keepers can slow a flood with a prayer and a spade. Others say the river writes a different law there. Rheingard listens and keeps a second weir just in case.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Bells:</strong> remove your hat at the first toll of the hour if you are on the square. Bless yourself or bow, as you prefer, when the Angelus rings; no one will force you, everyone will notice.</li><li><strong>Titles:</strong> "Master" for a guild head, "Brother" or "Sister" for monastics, "Captain" for a Ward officer, "Canon" for a cathedral scholar. If you are unsure, say "Goodman" or "Goodwoman" and smile.</li><li><strong>Market sense:</strong> weigh your coin in your palm and your words in your mouth. Haggling is expected on the outer stalls, frowned on inside the Guildring.</li><li><strong>Curfew:</strong> the second bell after sunset. Lanterns out on the wall; fines for singing on rooftops unless you are very good or very far from the captain.</li><li><strong>River:</strong> never mock a flood year in earshot of a waterman. River luck turns quick.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Rheingard keeps the <strong>Saints March</strong> in late spring when guilds process with their tools and relics to bless bridges and mills. In harvest the <strong>Angel Fair</strong> fills the square with cloth, bells, books, and show-wrestling between tanners and coopers. On <strong>Night of the Watchfires</strong> the wall is lit end to end and the Ward reads the floodbook aloud. Pilgrims come year-round to the cathedral to ask angels for small mercies and big ones. The city answers with bread and a bench if the angels are busy.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Rheingard likes clear accounts: of coin, of promises, of stories. Bring a tale for the tavern and an apology for the bellringer if you climb the tower at the wrong hour. If a bargeman offers you a ride down toward the low country, pay double and ask what the river is thinking today. He will look at the sky, spit once, and tell you in a few careful words more than the council will write in a day.</p>',
                ],
            ],
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
            'lore_sections' => [
                [
                    'title' => 'Redwind - Harbor of the Red Sun',
                    'html' => '<p><strong>Redwind</strong> wraps a deep blue harbor where ferries draw white stitches across the water and glass towers catch the morning blaze. Surf breaks on pale beaches a short tram ride from the business core; cockatoos heckle joggers in bayside parks; and a salt breeze threads coffee lanes at dawn. In the Red Sun Commonwealth the city creed is simple: work bright, play safer, and leave no trace but footprints.</p><p>Think modern harbor energy with coastal ease. Longboarders share paths with suits, galleries sit above boat sheds, and weekend markets spill under fig trees. The skyline has its icon too: a sail of white shells where music and theater pour into the night. Redwind talks fast, swims often, and tips its hat to the sun with factor 50 and a wide brim.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Glass Harbor Quay</strong> - Ferries, buskers, and waterside cafes. Sunset turns tower windows copper; gulls audit chips with alarming rigor.</li><li><strong>Sun Shell Hall</strong> - The sail-roofed arts house. Daytime tours, night shows, and a forecourt where dancers rehearse under sea breeze.</li><li><strong>Redwind Arch</strong> - Steel span over the harbor. Walk the top with a clipped line or watch climbers from the shade with an ice pop.</li><li><strong>Breaker Bay</strong> - Flagged surf beach with red-and-yellow posts, lifeguard towers, and a salt-stained kiosk that swears the best chips in town.</li><li><strong>Bushline Reserve</strong> - Harbor headlands of eucalyptus and sandstone. Coastal tracks, dragonflies, and lookouts where the city feels very small.</li><li><strong>Laneway Quarter</strong> - Espresso bars, small plates, murals, and late-night ramen. Order at the counter; learn to love the flat white.</li><li><strong>Outback Exchange</strong> - Market halls for regional produce and crafts. Oysters on ice, mangoes by the crate, leather hats you will not wear later.</li><li><strong>Ferry Triangle</strong> - Three wharves, six routes, zero patience for dawdlers. Tap on, tap off, and mind the gap at swell.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat the coast, sip the sun.</strong> Fish and chips with lemon, prawns on the barbie, grilled snapper with lime, meat pies with sauce, bush tomato salads, and fruit bowls that taste like holidays. Coffee culture is a civic sport: flat whites, long blacks, and iced lattes on hot mornings. Try a <strong>Harbor Plate</strong> at Glass Harbor (prawns, oysters, pickles) or a <strong>Breakers Roll</strong> at the beach kiosk (battered fish, slaw, chili). Craft beers live in converted sheds; mocktails sparkle with finger lime and mint.</p><p>Tourist note: waterfront menus run 5 to 10 times dearer than laneways one block inland. Ask for the "local pour" and a half carafe of tap water; most servers will steer you to the better-value plate.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Harbor Watch</strong> on the quays and the <strong>Surf Wardens</strong> on the beaches. Fines for litter and glass on sand land fast; community service often means dawn beach sweeps. Redwind has a gentle shadow too: the <strong>Harbor Runners</strong>, a courier circle that moves last-minute gear between wharves and stages for a fee of cash and silence. They claim it is logistics, not a racket. Once a quarter, the Watch audits their charity jars on the ferry steps; in this town even a shadow shows a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Sun sense:</strong> hat, shirt, sunscreen. Reapply. The UV does not care how tough you feel.</li><li><strong>Swim flags:</strong> swim between red-and-yellow flags only. Rips are real. If in doubt, ask a Warden.</li><li><strong>Ferries:</strong> queue single file, let passengers off, tap your card, keep bags clear of doors.</li><li><strong>Bins:</strong> pack it in, bin it right. Recycling is color coded; fines for beach litter sting.</li><li><strong>Wildlife:</strong> do not feed gulls, ibises, or the big lizard sunning by the path. They take fingers as tips.</li><li><strong>Night:</strong> stay on lit paths, share rides from marked zones, hydrate between rounds.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Redwind keeps a calendar of light and water. <strong>Harbor Lights</strong> launches a flotilla of lantern boats each spring. <strong>Surf Carnival</strong> pits clubs in rescue drills and sprints along Breaker Bay. <strong>Bush to Bay</strong> closes the headland road for runners at dawn. On <strong>Firefly Night</strong> the foreshore dims for an hour so kids can spot sparks in the trees and ships at anchor. City days publish budgets on quay boards and hand out saplings for balcony pots.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Redwind wants you salty, safe, and smiling. Ride a ferry at golden hour, walk a bush track when the cicadas start, tip the busker who makes a seagull dance, and take your rubbish with you. If you overpay on the quay, call it a view tax and try the laneways tomorrow. The harbor will pay you back with a breeze and a sky that looks hand polished.</p>',
                ],
            ],
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
            'lore_sections' => [
                [
                    'title' => 'Overview',
                    'html' => '<p><strong>Velesgrad</strong> sits where a wide river bends under birch and bell. Timber walls and red-brick towers ring a hill-citadel; onion-cap roofs shine like old copper when the sun grieves its way through winter cloud. In the Rodinian Tsardom this is a city of crossroads: forest paths meet caravan roads, river barges kiss frost-bitten piers, and oaths are sworn with one eye on the sky for thunder and one hand on the earth for luck.</p><p>The name honors the old patron of cattle, craft, and hidden gold. Here people say, "Perun keeps the storm, Veles keeps the store." Priests bless the gates; woodcarvers hang house-totems; and every hearth keeps a crust for the domovoi, the stove spirit who minds the brooms and mutters about muddy boots.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Kremlin Hill</strong> - Citadel of brick and oak. The Tsar\'s envoys read decrees in the yard; a bell-tower counts floods and fires. Touch the gate nail with two fingers and a quiet hope.</li><li><strong>Veles Market</strong> - Arcades of felt, linen, icons, iron. Horse bells, barrel hoops, smoked fish, honey cakes. Swindlers fear the market scales; fines are paid in coin and public sweeping.</li><li><strong>River Wharf</strong> - Low piers, high gossip. Bargemen trade news before bread. A small chapel to Saint of Fords shares a wall with the Guild of Ropes and Knots.</li><li><strong>Birch Quarter</strong> - Lanes of carved izbas with warm stoves and cold porches. Window boards show bears, geese, and saints; cats own the thresholds.</li><li><strong>Forge Street</strong> - Black hammers, red mouths of ovens. Smiths temper tools, not just swords; watch for sparks and the tea boy with the tin kettle.</li><li><strong>Banya Gardens</strong> - Bathhouse courtyards steaming even in deep frost. Birch switches whisper advice; quarrels left at the door are said to melt into the snow.</li><li><strong>Oak and Thunder</strong> - A twin shrine: oak for the sky, stone well for the under-earth. Couples circle the oak once for courage, twice for children, thrice for patience.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat for warmth, then for luck.</strong> Dumplings swim in broth; cabbage and mushrooms share a clay pot; river fish meet rye crust and butter. Street grills hiss with skewers; bakers plait poppy buns; pickles grin from jars like green moons. Wash it down with <strong>kvas</strong> from a barrel cart or honey <strong>medovukha</strong> when the wind sharpens its teeth. In deep winter the stall called <em>Little Stove</em> ladles buckwheat porridge and a blessing to anyone with cold hands.</p><p>Tourist truth: anything beside the Kremlin gate or on the wharf runs 5 to 10 times dearer. Ask for the "craft rate" and praise a maker\'s handiwork; a good word for a good joint will usually shave the number and win you a story about an anvil.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>The city watch is the <strong>River-Spear</strong>: cloaks the color of wet bark, spears for floods as well as thieves, and ledgers for every fine. They like restitution better than spectacle; a brawler may find himself hauling water to the banya and scrubbing the steam benches by lantern light.</p><p>Shadows have jerseys too. People hint about the <strong>Market Wolves</strong>, a brotherhood with patient teeth and tidy books, who "guard" night wagons and collect "wolf silver" so that lamps stay lit. They call it courtesy, not a racket. The River-Spear disagrees, politely, and audits their winter alms to widows each thaw. Farther downriver, beyond the weirs, the low country of <strong>Frankenondermeer</strong> watches floods with its own math and its own laws; Velesgrad nods uphill and keeps a second bell in case the river chooses a lesson day.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Thresholds:</strong> knock, step in with the right foot, greet the stove before the host. The domovoi hears manners first.</li><li><strong>Bread and salt:</strong> accept with both hands and a nod. Refuse and you have started a price duel you will lose.</li><li><strong>Names:</strong> titles sit heavy here. "Master," "Mistress," "Father," "Sister," or name and patronymic if you know it.</li><li><strong>Banya rules:</strong> leave quarrels with your boots. Do not boast; steam remembers and repeats.</li><li><strong>Winter sense:</strong> hats off for bells, not for frost. Mind the ice ribs on the wharf and the sled boys who drift like heroes.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Velesgrad keeps the <strong>Week of Butter Fires</strong> before spring: pancakes in the square, sled races, and a straw effigy pulled laughing to the river. Summer brings <strong>Night of Fern Flame</strong>: wreaths on the water, leaps over small fires, and brave fools hunting a flower that never grows. In autumn, <strong>Ancestor Evenings</strong> invite names to the table with extra plates and quiet songs. The year turns with bells and doors, and every turn asks for a thank-you to something larger than a ledger.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Come with a tale, leave with a proverb. Tip the tea boy, praise a carving, and keep a crust for the house spirit. If a storm speaks your name from the oak, speak it back softly and offer a coin to the well. The city will keep you between root and storm if you keep your promises between tongue and teeth.</p>',
                ],
            ],
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
            'lore_sections' => [
                [
                    'title' => 'Overview',
                    'html' => '<p><strong>Qilaktuk</strong> sits on a gravel shore where sea ice grinds like old teeth and the wind writes fast in snow. Color-bright houses climb a low ridge around a sheltered cove; boardwalks stitch neighborhoods over permafrost; sled trails cut straight lines to the horizon. In the Sila Council, weather is teacher and law. Qilaktuk listens hard, plans long, and keeps the lamps filled.</p><p>This is an Arctic town of craft, radio, and careful logistics. Snowmachines and qamutik sleds wait nose-out. VHF chatter fills the mornings. The weekly sealift ties the year together with crates of fuel, flour, guitar strings, and hockey tape. When the wind drops, you hear boots on boardwalk and the small bell of a door that does not want to let heat escape.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Aurora Ridge</strong> - A wind-hardened rise with a heated shelter and an aurora camera. Best view in town when the sky starts to dance.</li><li><strong>Harbor Pack</strong> - Ice-protected cove with a short pier. Skiffs in summer, komatik loads in winter. The tide board here is everybody\'s favorite rumor mill.</li><li><strong>Council House</strong> - Community hall and radio room. Meetings, youth nights, craft markets, weather briefings, and the big coffee pot.</li><li><strong>Qulliq Square</strong> - Plaza around the memorial oil lamp. Elders light it for first ice, first thaw, and remembrance days. Benches face out to the sea.</li><li><strong>Outfitter Row</strong> - Sled runners, rope, fuel, camp stoves, parkas, and borrowed wisdom. The wall map is patched with tape and stories.</li><li><strong>Ice Road Gate</strong> - Start of the flagged winter road. Check the board for conditions and do not move a flag unless you want a very public lecture.</li><li><strong>Inuksuk Point</strong> - Stone markers stand like careful people on the headland. Good place to watch whitecaps decide if they mean it.</li><li><strong>Clinic and Warm Line</strong> - Small but stubborn. Nurses run checks, counselors run a kettle, and the sat link keeps the wider world close enough to help.</li></ul>',
                ],
                [
                    'title' => 'Food and Warmth',
                    'html' => '<p><strong>Eat for heat, then for stories.</strong> Arctic char grilled or dried, muskox stew, caribou with juniper, seal soup, and bannock from cast iron. In berry time there are cloudberries and blueberries by the mug. Winter drinks run hot and sweet: tea with condensed milk, cocoa thick as a mitten, and coffee that could kick a snowmachine awake.</p><p>Tourist note: anything beside the pier or at Aurora Ridge runs 5 to 10 times town price. Ask for the "neighbor measure" and point at the notice board of community rides; most vendors will smile and drop the number to what a deckhand would pay.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Ice Ward</strong>: a small watch with radios, reflective stripes, and a habit of carrying extra mitts for kids. Fines lean toward work everyone benefits from: shoveling boardwalks, hauling water, checking flags. Qilaktuk also has a quiet network of fixers called the <strong>Night Trail</strong> who arrange after-hours fuel drops and airport runs when weather squeezes schedules. They call it kindness with a clipboard, not a racket. Each month the Council posts Night Trail donations on the hall wall; in this town even a shadow shows a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Doors:</strong> knock, then wait. Shake off snow in the porch. Close what you open, every time.</li><li><strong>Elders:</strong> offer the first seat, the first cup, and the last word. If an Elder says "not today," it means not today.</li><li><strong>Photos:</strong> ask people first. Never shoot a hunt without permission. Do not chase wildlife for a picture.</li><li><strong>Trails:</strong> do not move flags, do not block sleds, keep your dog on a line near houses. On ice, follow tracks or stay home.</li><li><strong>Cold sense:</strong> metal is hungry and wind has opinions. Dress for ten degrees worse than you think. Batteries lie; carry a spare.</li><li><strong>Greetings:</strong> a nod and a "hi" or "qujanaq" goes a long way. The weather is always an acceptable first topic.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Qilaktuk keeps a calendar of sky and ice. <strong>First Ice</strong> lights the qulliq and blesses the sleds. <strong>Sun Return</strong> brings a noon feast outside no matter the chill. <strong>Longest Day</strong> runs games on the gravel until midnight sun calls it. In late summer <strong>Berry Time</strong> sends families out with buckets and stories. In winter, the town holds <strong>Night Lights</strong> when the aurora is strong: generators quiet down, kids lie on caribou skins, and everyone tries to hear the sky crackle.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Qilaktuk is small on purpose and large where it counts. Walk with the wind, not against advice. Pay for the ride you take and the heat you borrow. If you overpay at the pier on your first day, call it tuition and ask the nearest auntie where she buys tea. The town will send you home warmer, wiser, and better at reading weather from a doorway.</p>',
                ],
            ],
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
            'lore_sections' => [
                [
                    'title' => 'Overview',
                    'html' => '<p><strong>Intirumi</strong> sits high in the thin blue air where terraced mountains step like stairs to the sky. Cyclopean walls fit stone to stone so tight a knife finds no path; trapezoid doors lean inward like shoulders taking weight; water sings in carved channels day and night. In the Sapa Inti Empire, reciprocity is law and the sun is calendar; in Intirumi both are written in granite and light.</p><p>This is a capital of terraces, roads, and quiet precision. Runners trade shell horns on way stations; stone-cutters polish joints by hand; farmers climb with baskets of quinoa and potatoes; priests read noon-shadow on a golden pin to set work and feast. Visitors learn fast: climb slow, drink water, step wide of the alpacas, and follow the keepers when they sweep the steps at dusk.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Sun Temple Quarter</strong> - Courtyards of cut stone and gold plates. At noon a thin line of light crosses a niche to mark the day. Offer a yellow flower; keep your voice small.</li><li><strong>Four Roads Gate</strong> - Where the royal road divides toward the four quarters of the realm. Runners rest in shade niches; tribute tallies are knotted in colored cords.</li><li><strong>Water Stair</strong> - A cascade of stone basins stepping down a slope. Women wash maize, children race leaf boats, and a water steward logs flow on a carved board.</li><li><strong>Terrace Crown</strong> - Green combs of earth held by stone. Maize near the sun, potatoes below, flowers everywhere for the temples. Llamas graze the edges like tidy clouds.</li><li><strong>Stone School</strong> - Scribes with khipu cords teach counts and records; builders teach plumb and angle with string and sun. A hall of maps shows roads like veins.</li><li><strong>Market of the Quarters</strong> - Arcades where each province sets out cloth, gourds, salt, fish, coca, copper tools. Inspectors check measures with calm eyes and good scales.</li><li><strong>Sky Path</strong> - A high ledge walk with low walls and a long view. Bells ring if fog comes in fast; guides tie ropes and wait without hurry.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat the terraces and the herds.</strong> Quinoa with herbs, potato stews thick as blankets, river trout with kola mint, roasted cuy, alpaca skewers with aji, tamales steamed in leaves. Street cups pour mate de coca for the climb and chicha morada sweet and cold for the afternoon. Try the <strong>Sun Plate</strong> near the temple steps (quinoa, cheese, peppers, and a gold-sided potato you will talk about later) or a <strong>Runner Bowl</strong> at the Four Roads Gate (hot broth, maize, greens, and a second wind).</p><p>Tourist note: stalls beside the Sun Temple and the Sky Path price 5 to 10 times the lane rate. Ask for the "ayni measure" and mention the terrace you helped weed; most sellers will smile and lower the number to what a porter pays.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Sun Ward</strong> on the plazas and the <strong>Road Keepers</strong> on the ledges. Fines lean toward labor that serves all: mending stairs, hauling water, setting stones true. Intirumi also keeps a shaded courtesy. People whisper about the <strong>Nightrunners</strong>, a courier circle that moves sealed bundles and late travelers between gates after curfew for a fee of coca and silence. They call it flow, not a racket. Each moon the Ward opens their charity baskets in public at the Stone School; in this city even a shadow shows a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Altitude:</strong> climb slowly, drink water, take coca tea, and let pride walk behind you. Headaches bow to patience and shade.</li><li><strong>Temples:</strong> hats off, shoulders covered. Do not touch gold plates or carved niches. Step aside for processions without being asked.</li><li><strong>Terraces:</strong> do not cut switchbacks or step on stone lips. If a farmer offers a hoe, take two rows and leave them straighter than you found them.</li><li><strong>Markets:</strong> greet, praise the cloth or the cut, then bargain. If a seller knots a bright cord to your purchase, it is a blessing; keep it on until you get home.</li><li><strong>Roads and ledges:</strong> keep right, give way to llamas and porters. When a shell horn sounds, get your elbows in and your feet sure.</li><li><strong>Night:</strong> curfew at second bell. Lanterns dim; stars do not. Hire a licensed guide if you must cross districts.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Intirumi keeps the <strong>Sun-Standing</strong> at solstice when the noon pin writes the tightest line and dancers turn like compass points. At <strong>Planting Promise</strong> families trade seed and labor for the year and priests bless the first terrace rows. <strong>Return of Rains</strong> brings flute parades and water poured on stone steps. At <strong>Festival of Roads</strong> runners carry torches along the ledges so the city looks like a necklace from the valley. The year ends with the <strong>Weighing of Work</strong>, when each district reads its gifts and asks neighbors for what balance still lacks.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Intirumi keeps its voice low and its stones true. Walk kindly, pay your share, help where a hand will do, and keep a flower for the temple stairs. If you overpay with the sun in your eyes, call it gratitude for walls that do not fall and roads that go everywhere. The mountain will remember your steps.</p>',
                ],
            ],
            'back_label' => 'Back to Gulfbelt',
            'back_href' => '?pg=gulfbelt',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Sapa Inti pet paints.', 'action' => 'Explore', 'href' => '?pg=sie_paint_shack', 'color' => '#f59e0b', 'points' => country_map_rect_points(250, 500, 245, 165, $w, $h)],
            ],
        ],
        'srl' => [
            'title' => 'Spice Route League - Navakai',
            'subtitle' => 'Harbor of Sails and Spice',
            'image' => 'images/harmontide-srl.webp',
            'lore' => 'Navakai thrives on sea trade, spice guilds, and vibrant dockside markets.',
            'lore_sections' => [
                [
                    'title' => 'Overview',
                    'html' => '<p><strong>Navakai</strong> curls around a clear lagoon where reef and deep water shake hands. Outrigger canoes rest on timber cradles, breadfruit trees shade sandy lanes, and meeting houses lift open rafters to trade wind and song. In the Spice Route League, the sea is road and teacher; in Navakai the stars are street signs and the tide is the town clock.</p><p>The city moves by rhythm: dawn launches, noon nets, dusk drums. Navigators practice on a sand star compass, elders plait pandanus and sennit, and children race along the seawall with kites cut like frigate birds. Visitors learn fast: greet first, step off woven mats with clean feet, and ask before you touch anything carved or corded.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Star Compass Green</strong> - A packed-sand circle marked with swell lines and star houses. Lessons at dawn; at night the whole green tilts its face to the sky.</li><li><strong>Marae Terrace</strong> - Sacred courtyard and community green. Ceremonies, welcomes, and quiet counsel under the eyes of carved ancestors. Shoes and loud voices stay outside.</li><li><strong>Lagoon Quay</strong> - Canoe slips, fish racks, shell traders, net menders. The tide board here is law; the bell warns when the reef path turns tricky.</li><li><strong>Fale Longhouse</strong> - Open-sided hall of posts and lashings. Food, talk, craft markets, and the conch that calls meetings and dance practice.</li><li><strong>Tapa Walk</strong> - Cloth makers, wood carvers, shell inlay, and nose flute shops. Dye bowls sit like small moons, and every doorway smells of oil and smoke.</li><li><strong>Reef Gate</strong> - A gap in the coral marked by pale stakes. The pilot boat sits here; cross without a pilot and the whole quay will fold arms and watch you learn.</li><li><strong>Palm Ridge</strong> - Low rise with lookouts over lagoon and ocean. Best place for first-light and last-light. Bring water, leave thanks.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat what the reef and grove give.</strong> Reef fish steamed in leaves, octopus with coconut cream, taro and breadfruit from the earth oven, mango and lime salads, grilled bananas with palm syrup. Street cups hold coconut water and hibiscus tea; evenings pour kava in half shells with slow talk and long listening. Try the <strong>Navigator Bowl</strong> at Lagoon Quay (fish, taro, greens, coconut) or the <strong>Reef Fire Plate</strong> from the earth oven by the longhouse.</p><p>Tourist note: stalls on the quay and beside the Marae Terrace run 5 to 10 times lane price. Ask for the "neighbor measure" and point to the water fund barrel; most sellers will smile and shave the number.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Lagoon Ward</strong>, a small watch with conch horns, ledgers, and more patience than rope. Fines become reef work, quay sweeps, or night watch shifts. Navakai also keeps a quiet network called the <strong>Moon Runners</strong> who ferry late cargo and guests after curfew for a fee of shells and silence. They call it courtesy, not a racket. Each moon the Ward opens their donation baskets in public at the Fale; in this town even a shadow shows a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Greetings:</strong> smile, a light touch at the shoulder, and a "talofa" or "aloha" style hello. Follow the host\'s lead.</li><li><strong>Marae and mats:</strong> ask before entering, remove shoes, do not step over people or food, and never plant feet on woven mats.</li><li><strong>Canoes:</strong> do not touch hulls, lashings, or paddles without permission. A canoe is family.</li><li><strong>Reef sense:</strong> mind the tide and your toes. Shuffle to warn rays, do not break coral, and leave shells for the ocean to keep.</li><li><strong>Curfew:</strong> drum at second star rise. Lantern guides are licensed; hire one or stay in the lit lanes.</li><li><strong>Gifts:</strong> bring fruit, a song, or a strong back. Work shared is work halved.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Navakai keeps the <strong>Wayfinder Nights</strong> when the Pleiades rise and the star compass fills with footsteps and stories. <strong>First Fruits</strong> thanks grove and reef with shared plates in the longhouse. <strong>Outrigger Days</strong> bring canoe races along the reef and drumlines on the quay. In storm season the town holds <strong>Quiet Sea</strong>: boats stay in, nets get mended, and elders tell routes you cannot see on any chart.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Walk soft on sand and loud in praise. Ask, offer, and listen more than you speak. If you overpay at the quay, call it a tide tax and learn a knot from the seller. The lagoon will remember your courtesy when the pilot boat meets you at the Reef Gate.</p>',
                ],
            ],
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
            'lore_sections' => [
                [
                    'title' => 'Overview',
                    'html' => '<p><strong>Turtlestar</strong> stands where tall grass meets two patient rivers, a confederation capital set in rings instead of blocks. Wind moves the prairie like water; bison trails cross old earth mounds; and at night the sky falls close enough to count. In the Sovereign Tribes of the Ancestral Plains, roads follow stars before they follow stakes. Turtlestar keeps that memory in how it listens, feeds guests, and votes by circle rather than by shout.</p><p>The city gathers many nations and lifeways. Timber council houses and canvas lodges share the same wind; beadwork and quillwork hang beside bronze tools and solar panels; horse lines stand near bike racks. The central fire is tended by rota, and visiting delegations plant their banners in the ground instead of on walls. You learn quickly: speak after you have listened; step lightly on grass that feeds more than you; ask before you point a lens at living ceremony.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Circle of Fires</strong> - The council green. Rings for elders, speakers, and guests; a shared fire at the center. Decisions are carried by consensus, not by hurry.</li><li><strong>Star Walk</strong> - A path laid out to mirror a northern constellation. Story poles mark seasons and routes; guides walk the tale at dusk.</li><li><strong>River Bend Market</strong> - Stalls of wild rice, corn, beans, squash, bison jerky, berries, crafts, drums, flutes, and books. Prices are posted plain and change with the wind, not with the face.</li><li><strong>Winter Count Lodge</strong> - A hall of painted hides and canvases recording years by shared memory. Youths learn to keep a count without losing a laugh.</li><li><strong>Bison Gate</strong> - Timber gate and overlook toward the managed herd. Rangers speak about burns, grass, and how not to be very stupid with a very large animal.</li><li><strong>Sky House</strong> - An open-roof observatory with windbreak walls. Star lessons, eclipse watches, and the quiet joy of a clear night.</li><li><strong>River Ford</strong> - Old crossing kept shallow with stone ribs. A place to sit, cool your feet, and remember that water is elder to the road.</li><li><strong>Craft Rings</strong> - Bead, quill, leather, clay, wood, and metal. Makers sit facing the door and the sun, and there is always a spare stool for a lesson if you mind your hands.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat what the prairie and river give.</strong> Bison stew, corn and bean succotash, wild rice with cranberries, frybread and honey, squash roasted in wedges, trout with sage, chokecherry pudding. Coffee is strong and shared; cedar and mint teas cool a hot day. Try the <strong>Trail Bowl</strong> at River Bend (bison, beans, corn, greens) or the <strong>Sky Cakes</strong> at dawn (blue corn griddle cakes with berry sauce).</p><p>Tourist note: anything right on the Circle of Fires or the Star Walk runs 5 to 10 times the lane rate. Ask for the "neighbor measure" and point at the youth fund box; most vendors will smile and set the number to what a drummer pays.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Trail Wardens</strong>, a cross-tribal watch in red sashes. Their first tools are a whistle and a water jug; their second are radios. Fines tend to be service: picking trash from the river, cutting invasive brush, tending fires at the council green. Turtlestar also keeps a quiet network called the <strong>Night Riders</strong> who move elders, medicine, and late guests after curfew for a fee of gas and silence. They call it kin duty, not a racket. Each month the council posts Night Rider donations and routes on a board by the clinic; in this town even a shadow shows a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Greetings:</strong> hello first, business second. A handshake may be light; follow the lead you are given.</li><li><strong>Circles:</strong> do not cut across a circle while people speak; walk the outside. Wait to be invited to the inner ring.</li><li><strong>Ceremony:</strong> ask before photos or recording. If told "not today," believe it, thank them, and put the lens away.</li><li><strong>Food:</strong> elders and kids first in line. If you take the last piece, you also take the job of slicing more.</li><li><strong>Land:</strong> stay on marked paths near nests and burn plots. Do not pick plants without a ranger or a teacher beside you.</li><li><strong>Horses and bison:</strong> admire with eyes, not hands. A ranger will tell you the safe fence; believe the fence.</li><li><strong>Night:</strong> curfew is gentle but real. Lanterns dim; stars take over. Use lit lanes or hire a Night Rider.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Turtlestar keeps a calendar by sky and grass. <strong>First Thunder</strong> opens planting with songs and coffee at dawn. <strong>Berries In</strong> is a sweet month of baskets and stained smiles. <strong>Long Sun</strong> brings dances, horse races, and river games. <strong>First Frost</strong> moves the city inward to story nights and winter counts. The year closes with <strong>Stars Return</strong> when families bring warm food to the Sky House and point out where their names sit among the lights.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Turtlestar will treat you like a neighbor if you act like one. Listen twice, step softly, pack out trash that is not yours, and keep a cup ready for shared coffee. If you overpay on the Star Walk, call it a sky tax and drop a few bills in the youth fund. The prairie remembers good footprints.</p>',
                ],
            ],
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
            'lore_sections' => [
                [
                    'title' => 'Overview',
                    'html' => '<p><strong>Meridian Arc</strong> sits on a broad river where rail, freeway, and flight paths cross like compass marks. Glass and brick share the skyline; street trees run in straight rows; a ring of neighborhoods feeds a downtown that glows after dark. In the United Free Republic of Borealia, the civic pitch is simple: show up, vote often, coach youth sports, and keep the crosswalks painted.</p><p>The vibe is classic American city with a fresh coat. Unions and start-ups share blocks, diners and ramen bars share corners, and murals argue with billboards in good humor. On game nights the riverfront thrums; on Saturday mornings the farmers market is a traffic pattern of strollers, dogs, and iced coffee. Bring good shoes and a patient horn hand.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Capitol Row</strong> - Statehouse dome, courts, and museums. Lawns for rallies, steps for selfies, food trucks at lunch. Security is visible and polite.</li><li><strong>Arcworks</strong> - Brick warehouses turned studios, tech lofts, and brew halls. String lights, bikes, dogs, and a hundred laptops glowing behind plants.</li><li><strong>Rivermile</strong> - Riverwalk, ballpark, concert shell, and the big fountain. Joggers at dawn, fireworks on holidays, jazz on Sundays.</li><li><strong>Old Grid</strong> - Historic blocks of brownstone and stoops. Porch flags, tiny libraries, and the candy store that somehow made it to this century.</li><li><strong>Northside Markets</strong> - Food halls, produce sheds, and flea stalls. Best tacos, best pierogi, best argument about both.</li><li><strong>Meridian Exchange</strong> - Grand station under a steel vault. Trains, light rail, bus concourse, and a coffee stand that knows your order by your shoes.</li><li><strong>Commons Park</strong> - Big green with a carousel, dog run, and summer movies. Fireflies in June if the kids hold still long enough.</li><li><strong>Skyline Bridge</strong> - Signature span with a pedestrian deck and lookouts. Sunset here is a local religion; bring a jacket for the wind.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat neighborhood by neighborhood.</strong> Diners pour bottomless coffee with stacks of pancakes. Food trucks sling birria and bulgogi. Delis stack impossible sandwiches. Riverfront grills do salmon and sweet corn in season. Breweries pour flights; cafes pull serious espresso; soda fountains still stir a cherry phosphate if you ask.</p><p>Order a <strong>Meridian Melt</strong> at a lunch counter (patty, onions, rye), a <strong>River Bowl</strong> at Rivermile (greens, grains, smoked fish), and a <strong>Commons Cone</strong> after the carousel. Tourist note: anything right on the riverwalk or inside the ballpark runs 5 to 10 times the neighborhood rate. Ask for the "local pour" and the server will usually point you one block back for the same plate cheaper.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Civic Department</strong> with patrol bikes, cruisers, and lots of body cams. Fines lean toward service: park cleanups, mural touch-ups, and crosswalk duty. Health teams work festivals with water, info cards, and ride vouchers.</p><p>There is a quiet courier loop called the <strong>Arc Line</strong> that moves last-minute gear and documents between venues after hours for a fee of cash and silence. They call it logistics, not a racket. Once a quarter, the city posts their donation receipts to youth leagues and library programs; in Meridian Arc even a shadow shows a spreadsheet.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Transit:</strong> tap cards on trains and buses, stand right on escalators, let riders off first. Bikes yield to feet on the riverwalk.</li><li><strong>Street sense:</strong> eyes up at crosswalks, bags zipped, headphones at one ear after dark. Use lit routes and posted rideshares.</li><li><strong>Sports nights:</strong> book parking or take rail. Learn the chant or at least clap on the beat.</li><li><strong>Markets:</strong> greet first, sample second, pay the posted price. Cash helps small vendors; card taps fine everywhere else.</li><li><strong>Parks:</strong> leashes, litter bags, no glass on the grass. Grills are first come; share the heat if asked nicely.</li><li><strong>Weather:</strong> summer storms hit fast, winter bites. Layer up; the bridge always runs colder than the street.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Meridian Arc loves a schedule. <strong>Opening Day</strong> for baseball turns Rivermile into a parade. <strong>Arc Fest</strong> fills Arcworks with art, food, and night markets. <strong>River Lights</strong> brings boats and fireworks on the fourth. <strong>Harvest Weekend</strong> stacks pumpkins on the Commons, and <strong>First Snow</strong> flips the switch on a mile of holiday lights down Old Grid. City days post budgets on the station board and hand out tree saplings for stoops and balconies.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Meridian Arc wants you caffeinated, oriented, and five minutes early. Walk the river at golden hour, take the train once for fun and once because it is faster, tip the busker who sounds like a record, and carry a water bottle. If you overpay on the riverwalk, call it a view tax and loop back through the lanes tomorrow. The city will repay you with a shortcut, a sandwich, and a seat for the sunset.</p>',
                ],
            ],
            'back_label' => 'Back to Borealia',
            'back_href' => '?pg=borealia',
            'areas' => [
                ['name' => 'Meridian Arc Paint Shack', 'description' => 'Here you can paint your URB creatures.', 'action' => 'Explore', 'href' => '?pg=urb_paint_shack', 'color' => '#3b82f6', 'points' => country_map_rect_points(240, 500, 250, 170, $w, $h)],
                ['name' => 'Meridian Arc Adventure', 'description' => 'Explore the capital.', 'action' => 'Explore', 'href' => '?pg=urb-adventure2', 'color' => '#a855f7', 'points' => country_map_rect_points(690, 340, 250, 170, $w, $h)],
                ['name' => 'Abandoned Farm Adventure', 'description' => 'Explore an abandoned farm.', 'action' => 'Explore', 'href' => '?pg=urb-adventure', 'color' => '#fb7185', 'points' => country_map_rect_points(980, 260, 220, 150, $w, $h)],
            ],
        ],
        'xochimex' => [
            'title' => 'Xochimex - Xochival',
            'subtitle' => 'Festival Canals and Flowers',
            'image' => 'images/harmontide-xochimex.webp',
            'lore' => 'Xochival is a festival-rich city of flower markets, music, and floating neighborhoods.',
            'lore_sections' => [
                [
                    'title' => 'Overview',
                    'html' => '<p><strong>Xochival</strong> sprawls across a high plateau ringed by distant volcano silhouettes, a basin city of plazas, tree-lined boulevards, modern towers, and old stone. Murals bloom across concrete, museums stack wonders behind cool courtyards, and canals in the lakeward districts still carry flat boats bright with paint and marigolds. In Xochimex, the capital is a living market and a memory palace at once.</p><p>The pace is megacity fast with neighborhood hearts. Metro lines thread the ground, bike lanes share shade with jacaranda, and street vendors turn every corner into breakfast. Brass bands practice in pocket parks while office towers mirror a sky that changes its mind twice a day. Bring good shoes, small bills, and a plan that can flex.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Great Plaza</strong> - Monumental square ringed by civic halls and colonnades. Rallies, festivals, and a flag that writes the wind. By night, music; by morning, pigeons with opinions.</li><li><strong>Canal Gardens</strong> - Lake-edge borough of chinampas and canals. Flower markets, floating kitchens, and painted boats called trajineras. Hire a pilot; the canals have their own rules.</li><li><strong>Museum Mile</strong> - A chain of art houses and history courts. Cool courtyards, stone serpents, and galleries that take longer than you planned. Benches are strategic assets here.</li><li><strong>Old Stones Quarter</strong> - Ruins and foundations threaded through a colonial grid. Market arcades hug old walls; guides tell three versions of every layer.</li><li><strong>Reforma Walk</strong> - Grand boulevard with roundabouts and monuments. Runners at dawn, marches at noon, couples and dogs at dusk.</li><li><strong>Mercado Mayor</strong> - Covered market city within the city. Produce pyramids, butcher aisles, spice canyons, and lunch counters that adopt you for a plate or two.</li><li><strong>Arena Lucha</strong> - Wrestling palace of masks, capes, and popcorn. Families cheer, vendors heckle, heroes fly. It is theater and you are part of the cast.</li><li><strong>Skyline Lookout</strong> - Hill park with a citywide view. Vendors sell fruit cups and opinions about the weather rolling over the rim.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat the street, then sit for soup.</strong> Dawn tamales and atole, mid-morning tlacoyos and quesadillas, noon tacos al pastor, evening pozole or birria, and late-night churros. Fruit cups with lime and chile, fresh juices, and coffee strong enough to argue. Try a <strong>Canal Picnic</strong> in the boats (fresh tortillas, grilled fish, salsas), or a <strong>Market Menu del Dia</strong> at a counter inside Mercado Mayor.</p><p>Tourist note: kiosks on the Great Plaza and Reforma Walk run 5 to 10 times neighborhood prices. Ask for the "vecino measure" and step one block off the main drag; most vendors will meet you halfway and point you to their cousin\'s stand.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>City Guardians</strong> and the <strong>Transit Watch</strong>, visible in plazas and on platforms. Fines tilt toward community cleaning and crosswalk duty. Health booths pop up at big events with water, maps, and quiet help. Still, a city this size has shade. Couriers whisper about the <strong>Flower Line</strong>, a night network that moves documents and last-minute cargo between markets and museums for a fee of cash and silence. They call it logistics, not a racket. Once a month the Guardians audit their donation receipts to school kitchens and canal cleanup crews; in Xochival even a shadow shows a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Transit:</strong> tap card, let riders off first, backpack to the front at rush hour. Keep a small bill stash for buses and tips.</li><li><strong>Street sense:</strong> stay on lit routes at night, phone away near curbs, zipped bag in crowds. Use registered cabs or app zones.</li><li><strong>Markets:</strong> greet the stall, try the salsa carefully, and pay the posted price unless invited to bargain. Do not photograph people without asking.</li><li><strong>Canals:</strong> hire licensed pilots, agree the price before boarding, and keep hands inside on tight turns. Bring cash for boat vendors and bands.</li><li><strong>Altitude and sun:</strong> drink water, take it slow the first day, and respect a sun that bites even when it is cool.</li><li><strong>Plazas:</strong> Sundays are for families and wheels. Share lanes, mind skaters, and clap for the band even if they miss a note.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Xochival keeps a crowded calendar. <strong>Flower Boats</strong> fill the canals in spring with arches of marigold and music. <strong>Night of Paper Lights</strong> sees plazas glow with cut-paper banners and candles. <strong>Masks and Heroes</strong> brings wrestling parades down Reforma. Autumn hosts <strong>Days of Remembrance</strong> when altars bloom in courtyards and good bread sells out twice. City days plant trees along boulevards and post budgets on kiosk screens in three languages.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Plan big, walk small. Let the metro carry you, the markets feed you, and the murals slow you down. Tip in coins, keep a napkin stash, and say gracias more than once. If you overpay on the plaza, call it a view tax and find the lane where the lunch line is longest. Xochival will repay you with a seat on a trajinera, a bowl that steams in your hands, and a sky that turns city lights into another constellation.</p>',
                ],
            ],
            'back_label' => 'Back to Gulfbelt',
            'back_href' => '?pg=gulfbelt',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Here you can paint your Xochimex creatures.', 'action' => 'Explore', 'href' => '?pg=xm_paint_shack', 'color' => '#10b981', 'points' => country_map_rect_points(240, 500, 250, 165, $w, $h)],
            ],
        ],
        'yamanokubo' => [
            'title' => 'Yamanokubo - Amatera',
            'subtitle' => 'Neon Lanes and Shrine Hills',
            'image' => 'images/harmontide-yamanokubo.webp',
            'lore' => 'Amatera blends old shrine hills with high-energy nightlife and story-rich backstreets.',
            'lore_sections' => [
                [
                    'title' => 'Overview',
                    'html' => '<p><strong>Amatera</strong> unfurls along a river below green ridges, a capital of tile roofs, white plaster walls, and gates painted the color of sunrise. Castle keeps step above moat and stone, lantern-lit bridges stitch wards together, and narrow lanes carry sandal steps past noren curtains and paper screens. Think Edo-era grace: drum and bell mark the hours, guild flags hang from eaves, and pagodas rise like calm prayers over cedar.</p><p>The city breathes in three beats: court, craft, and courtyard. Magistrates read petitions from veranda shade; print shops thrum with woodblocks; tea steam curls from garden pavilions while monks ring a bronze bell that makes conversation lower itself out of respect. In the Mountain Shogunate, order is a gardened thing; in Amatera it is pruned daily with broom, brush, and bow.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Sun Keep</strong> - Castle hill with layered walls and a watchtower that sees storms a day early. The moat carp are older than some clans; feeding them is luck and a light fine if you toss anything but grain.</li><li><strong>Willow Bridge Row</strong> - Long bridge lined with lanterns and drum posts. Storytellers and vendors set up at dusk; keep to the left and do not stop on the crest to pose.</li><li><strong>North Magistracy</strong> - Courtyards, sliding doors, evidence gardens, and a public notice board where edicts share space with lost sandals and found umbrellas.</li><li><strong>Paper Lane</strong> - Woodblock carvers, ink grinders, bookbinders. The air smells of pine soot and rice paste; printers hang fresh sheets like sails.</li><li><strong>Smith Street</strong> - Bell makers, blade polishers, kettle founders. Apprentices bow with soot on their faces and pride they do not quite hide.</li><li><strong>Lotus Temple Close</strong> - Shrine and zendo around a pond. One stick of incense is enough; step lightly, bow once, and mind the koi.</li><li><strong>River Fish Market</strong> - Morning clatter of baskets, tubs, and quick knives. Tea sellers walk the aisles with kettles and good advice about bones.</li><li><strong>Moon Theater Quarter</strong> - Kabuki stages, bunraku houses, music halls, and lantern alleys. Families come early; students stay late arguing actors by nickname.</li><li><strong>Pine Garden Walk</strong> - A strolling garden of stone, water, and borrowed view. Paths curve on purpose so thoughts must follow.</li></ul>',
                ],
                [
                    'title' => 'Food and Tea',
                    'html' => '<p><strong>Eat season, drink patience.</strong> Morning onigiri with pickled plum, midday soba that snaps like a good line, tempura so light it forgets it was in oil, river eel lacquered to a shine, skewers of chicken and leek over charcoal, tofu hot in clay with ginger and green. Teahouses pour sencha and matcha with a bow and a quiet lesson in cups; street stalls steam sweet buns and ladle warm amazake on cold nights.</p><p>Tourist note: stalls on Willow Bridge and outside Moon Theater price 5 to 10 times lane rate. Ask for the "neighbor measure" and compliment the brushwork on the signboard; most vendors will smile and shave the number to what a stagehand pays.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>City Watch</strong> under the magistrates: patrols with jingasa hats, ledgers, and the habit of sweeping as they walk. Fines lean toward repair and service: mending bridge rails, copying notices, raking gravel straight after a hurried foot. Amatera also keeps shade where lantern light thins. People mention the <strong>Lantern Guild</strong>, discreet go-betweens who move sealed letters and delicate parcels after curfew for a fee of tea bricks and silence. They call it courtesy, not a racket. Each season their donation chests are opened in public at the North Magistracy; in this city even a shadow shows a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Greetings:</strong> small bow, hands at sides. Loud voices belong to bridges in flood, not to markets.</li><li><strong>Shrines and halls:</strong> hats off, shoes clean. One clap, one bow, one wish. Do not touch carved dragons for luck; they bite in stories and splinter in truth.</li><li><strong>Tea:</strong> tap two fingers to thank a pour. Do not drown leaves; let the pot breathe between cups.</li><li><strong>Streets:</strong> keep to the left on bridges and busy lanes. Umbrellas tilt low at eye level, not high like banners.</li><li><strong>Markets:</strong> praise the craft, then bargain a little. If a vendor ties a red cord to your parcel, it is a blessing against clumsiness; do not cut it until home.</li><li><strong>Curfew:</strong> the drum at second watch. Lantern guides are licensed; hire one or stay on the main streets.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Amatera keeps <strong>Lantern River</strong> at first full moon when candles float from Willow Bridge and children chase their reflections. <strong>Spring Viewing</strong> brings petals that fall like slow snow and merchants who measure sales by how many blossoms land on their ledgers. In high summer <strong>Boat Processions</strong> carry shrine banners along the river with drums and shouted blessings. Autumn hosts <strong>Moon Cakes and Masks</strong> in the theater quarter; winter brings <strong>Quiet Bell</strong> when the year changes with a single long note from Lotus Temple.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Walk as if your sleeves were wider than they are. Read the notice boards, mind the drum, and let tea slow you down. If you overpay under the theater lanterns, call it a stage tax and try Paper Lane tomorrow. The city will repay you with a steadier step, a cleaner page, and a garden path that ends exactly where your breath does.</p>',
                ],
            ],
            'back_label' => 'Back to Dawnmarch',
            'back_href' => '?pg=dawnmarch',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Here you can paint your Yamanokubo creatures.', 'action' => 'Explore', 'href' => '?pg=ynk_paint_shack', 'color' => '#8b5cf6', 'points' => country_map_rect_points(250, 500, 250, 170, $w, $h)],
                ['name' => 'Lotus Terrace Shrine Visit', 'description' => 'A daily shrine stop in Amatera that stacks luck when you keep your visits going.', 'action' => 'Begin', 'href' => '?pg=ynk-adventure', 'color' => '#22c55e', 'points' => country_map_rect_points(700, 350, 230, 170, $w, $h)],
                ['name' => 'Yumenoki Ramen', 'description' => 'Noodle counter with simmered broths, late-night tea, and quick stools under warm cedar.', 'action' => 'Order', 'href' => '?pg=ynk-ramen', 'color' => '#f97316', 'points' => country_map_rect_points(980, 260, 220, 150, $w, $h)],
                ['name' => 'Gacha Machines', 'description' => 'A bunch of machines, giving you something strange for your Dosh.', 'action' => 'Gamble', 'href' => '?pg=gacha', 'color' => '#ec4899', 'points' => country_map_rect_points(1180, 430, 220, 150, $w, $h)],
            ],
        ],
        'yn' => [
            'title' => 'Yara Nations - Warraluma',
            'subtitle' => 'Where River Meets Song',
            'image' => 'images/harmontide-yn.webp',
            'lore' => 'Warraluma stands between river and escarpment, with Country-led stewardship and gathering grounds.',
            'lore_sections' => [
                [
                    'title' => 'Overview',
                    'html' => '<p><strong>Warraluma</strong> rests between a red-rock escarpment and a tidal river that braids into mangroves and billabongs. Heat shimmers at noon, sea breeze comes like a promise by late afternoon, and the evening sky writes old maps in bright chalk. In the Yara Nations the first law is Country. Warraluma keeps that law in how it walks, eats, paints, and greets.</p><p>This is a city built around keeping places and meeting grounds rather than walls. Boardwalks skim wetlands, shade sails stitch plazas, and painted posts mark paths that follow songlines. Elders sit where wind and story move best; rangers read the seasons from flowers and tides. Visitors learn fast: ask before you enter, listen before you speak, and leave a place better than you found it.</p>',
                ],
                [
                    'title' => 'Districts and Landmarks',
                    'html' => '<ul><li><strong>Meeting Ground</strong> - Open circle of red earth and shade trees. Welcomes to Country, smoking ceremonies, community decisions. Shoes off if the custodian asks; photos only with permission.</li><li><strong>River Steps</strong> - Timber terraces down to the tidal water. Kids fish for mullet, aunties teach knots, rangers post the tide and croc board. When the curlew calls, go home by the lit path.</li><li><strong>Keeping Place</strong> - Cultural center and archive. Carvings, bark paintings, fiber works, story rooms, and a small reading table that is always busy. Some rooms are restricted; respect the signs.</li><li><strong>Stone Gallery</strong> - Rock shelters under the escarpment with ancient art. Visits are guided and on the cool side of the day. Stay on the path; stone remembers footprints.</li><li><strong>Songline Walk</strong> - Wayfinding path marked by painted poles and ground symbols. Each bend teaches a plant, an animal, or a season. Walk slow; it is a class, not a shortcut.</li><li><strong>Star Field</strong> - Low hill with a horizon line. Night talks about the Emu in the Sky and river tides. The wind is a better storyteller if you bring a blanket.</li><li><strong>Market Shade</strong> - Long roof of beams and palm thatch. Bush foods, clapsticks, shell work, woven baskets, prints, and cool water. Prices support families and Country projects.</li><li><strong>Mangrove Board</strong> - Raised walk through the mangroves to the river mouth. Mud fiddlers drum, egrets hunt, and the tide writes in mirror script.</li></ul>',
                ],
                [
                    'title' => 'Food and Drink',
                    'html' => '<p><strong>Eat what the seasons bring.</strong> Barramundi grilled in paperbark, kangaroo skewers with pepperberry, damper with wattleseed and honey, yams and bush tomatoes, lemon myrtle chicken, crocodile bites with finger lime, and quandong tarts. Cool drinks run to lemon myrtle iced tea, river mint water, and coffee that forgives long nights.</p><p>Tourist note: stalls right on River Steps and at Stone Gallery trailheads can be 5 to 10 times the lane rate. Ask for the "neighbor measure" and point to the Country fund box; most vendors will smile and set the price to what a ranger pays.</p>',
                ],
                [
                    'title' => 'Order and Underlane',
                    'html' => '<p>Peace is kept by the <strong>Country Rangers</strong> with radios, first aid, and more knowledge than a map. Fines tilt toward work that helps all: path repair, litter sweeps, water runs for elders, and firebreak checks. Warraluma also runs a quiet lift network, the <strong>Night Trackers</strong>, who ferry elders, medicine, and late workers after curfew for fuel money and thanks. They call it kin duty, not a racket. Each month the council posts Night Tracker donations and trips on the notice board by the Meeting Ground; in this town even a shadow shows a receipt.</p>',
                ],
                [
                    'title' => 'Etiquette and Practicalities',
                    'html' => '<ul><li><strong>Welcome:</strong> listen to custodians and elders first. Acknowledge Country when you speak. "Thank you for welcome" goes a long way.</li><li><strong>Access:</strong> some places are men\'s or women\'s business, some are closed. If a sign says no, it means care, not secrecy.</li><li><strong>Photos and recordings:</strong> ask people before you point a lens, and do not record ceremony or rock art without explicit permission.</li><li><strong>Tracks and tides:</strong> stay on marked paths, watch the croc and jellyfish boards, and give mangroves your respect. The tide keeps its own time.</li><li><strong>Fires:</strong> only in set pits, only with ranger go-ahead. Seasonal cool burns are skilled work; admire from the line.</li><li><strong>Gifts and trade:</strong> buy direct from makers, not from trunks. If you pick a fruit, leave two for birds and kids.</li><li><strong>Heat:</strong> carry water, hat, and sense. Rest in shade at noon; the story can wait an hour.</li></ul>',
                ],
                [
                    'title' => 'Calendar and Belief',
                    'html' => '<p>Warraluma follows a many-season wheel. <strong>First Rains</strong> wakes frogs and paints the sky green; <strong>Mango Time</strong> brings sticky hands and laughter; <strong>Burning Cool</strong> moves careful fire through grass to keep Country healthy; <strong>Turtle Moon</strong> watches nests and lights kept low; <strong>Dry High</strong> opens the long road to Stone Gallery at dawn and closes it at noon. Night gatherings mark <strong>Star Stories</strong> with songs and quiet feet. Welcome to Country and smoking ceremonies open big events so guests step in properly.</p>',
                ],
                [
                    'title' => "Traveler's Note",
                    'html' => '<p>Walk soft, ask first, carry water, and leave thanks. If you overpay by the river, call it a tide tax and put a note in the Country fund. Learn the name of the hill that watched you today and say it back to the wind. Warraluma will remember your manners longer than your footprints.</p>',
                ],
            ],
            'back_label' => 'Back to Uluru',
            'back_href' => '?pg=uluru',
            'areas' => [
                ['name' => 'Paint Shack', 'description' => 'Here you can paint your Yara Nations creatures.', 'action' => 'Explore', 'href' => '?pg=yn_paint_shack', 'color' => '#06b6d4', 'points' => country_map_rect_points(250, 500, 250, 170, $w, $h)],
            ],
        ],
    ];

    if (!isset($configs[$slug])) {
        return null;
    }

    $config = $configs[$slug];

    if ($slug === 'sie' && function_exists('current_user')) {
        $user = current_user();
        if ($user && has_map_unlock((int)$user['id'], 'aeonstep_plateau')) {
            $config['areas'][] = [
                'name' => 'Aeonstep Plateau',
                'description' => 'A hidden highland route revealed after a cave-side rock slide.',
                'action' => 'Travel',
                'href' => '?pg=aeonstep',
                'color' => '#a78bfa',
                'points' => country_map_rect_points(1080, 255, 300, 185, $w, $h),
            ];
        }
    }

    return $config + ['width' => $w, 'height' => $h];
}
