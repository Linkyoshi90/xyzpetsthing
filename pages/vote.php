<?php
require_login();

// Determine which species are enabled
$allowedSpecies = [];
$file = __DIR__ . '/../available_creatures.txt';
if (is_file($file)) {
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $allowedSpecies[] = $line;
    }
}
if ($allowedSpecies) {
    $placeholders = implode(',', array_fill(0, count($allowedSpecies), '?'));
    $allowedSpecies = q(
        "SELECT species_name FROM pet_species WHERE species_name IN ($placeholders)",
        $allowedSpecies
    )->fetchAll(PDO::FETCH_COLUMN);
}
function slugify($str) { return strtolower(preg_replace('/[^a-z0-9]+/i', '_', $str)); }
$allowedSlugs = array_map('slugify', $allowedSpecies);

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = $_POST['selection_json'] ?? '[]';
    // Ensure valid JSON to satisfy column constraint
    $data = json_decode($json, true);
    if ($data === null) {
        $json = '[]';
    }
    q(
        "INSERT INTO creature_name_votes (user_id, selection_json) VALUES (?, ?) " .
        "ON DUPLICATE KEY UPDATE selection_json=VALUES(selection_json), submitted_at=CURRENT_TIMESTAMP",
        [current_user()['id'], $json]
    );
    $submitted = true;
}

// Build a list of available gender/variant combinations per creature
$image_variants = [];
foreach (glob(__DIR__ . '/../images/*_*_*.webp') as $file) {
    $name = basename($file, '.webp');
    if (preg_match('/^(.*)_([mf])_(.+)$/', $name, $m)) {
        // Normalize the creature slug to lowercase so it matches the
        // front‑end slug() helper which also lowercases names.
        $slug = strtolower($m[1]);
        if ($allowedSlugs && !in_array($slug, $allowedSlugs, true)) {
            continue;
        }
        $combo = $m[2] . '_' . $m[3];
        $image_variants[$slug][] = $combo;
    }
}
?>
<div class="vote-app" aria-live="polite">
    <header>
      <h1 class="title">Creature Name Picker</h1>
      <div class="toolbar">
        <select id="jump" class="select" aria-label="Jump to region"></select>
        <button class="btn warn" id="resetBtn" type="button">Reset</button>
        <button class="btn" id="exportBtn" type="button">Export JSON</button>
        <button class="btn primary" id="copyBtn" type="button">Copy JSON</button>
        <button class="btn good" id="submitBtn" type="button" disabled>Submit</button>
      </div>
    </header>

    <?php if (!empty($submitted)): ?>
    <div class="mini">Your vote has been submitted.</div>
    <?php endif; ?>

    <div class="progress" id="progressText">Loading…</div>

    <div id="regions"></div>

    <div class="summary">
      <div class="mini" id="summaryText"></div>
    </div>

    <details class="howto">
      <summary>How this works (and how to hook up PHP)</summary>
      <div class="mini">
        <p>• Click exactly one suggested name per line. Your choices autosave in your browser.</p>
        <p>• <strong>Submit</strong> posts to this page via a standard <code>POST</code> with two payload styles for convenience:
        a PHP array <code>selection[region_slug][base_slug] = chosen</code> and a JSON string in <code>selection_json</code>.</p>
        <pre><code>// Minimal PHP sketch (server‑side)
// $json = $_POST['selection_json'] ?? '{}';
// $data = json_decode($json, true);
// // $data is [{region, base, choice, base_slug, region_slug}, ...]
// // Write to MySQL here.
</code></pre>
      </div>
    </details>

    <!-- Hidden HTML form that will be populated and submitted -->
    <form id="submitForm" action="?pg=vote" method="post" hidden></form>
  </div>

  <button id="toTopBtn" class="btn return-top" type="button">Return to top</button>

  <!-- Your source data: paste/edit freely. Lines with “–” (en dash) are regions; lines with “name:” are groups. -->
  <script id="source" type="text/plain">
Auronia – Aegia Aeterna (AA)
Lamia: Lamoria, Serafemme, Nagaressa, Viperelle, Lamivra
Centaur: Centara, Sagritar, Hoofkin, Bowmane, Tauriel
Charybdis: Riptara, Maelstrix, Vortessa, Gulpmire, Swirlgrim
#Pegasus: Skysteed, Pegara, Nimbuscolt, Aetherfilly, Wingstall
#Arachne: Silkatrix, Arachnea, Loomspinner, Weblira, Threadessa
#Cyclops: Unoculus, Monocleus, Eyeclast, Cycloidon, Oneyx
#Dryad: Verdelle, Grovelet, Barknymph, Sylfae, Leafdry
#Alraune: Rootbelle, Mandrella, Belladroot, Alranth, Bloomdra
#Minotaur: Labyrhorn, Minotorn, Bullmaze, Asterion, Hornox
#Gorgon: Medusia, Gorgyra, Petrisa, Stonegaze, Serpelle
#Mermaid: Nerissa, Tideva, Brinelle, Marivyn, Seamelle
#Phoenix: Pyrewing, Emberis, Ashra, Reflara, Feniqa
#Griffin: Gryphlet, Aerieon, Liongale, Griphus, Eagral
#Harpy: Galeshriek, Talonette, Harpixa, Skreeva, Vireen
#Automaton: Clockwerk, Gearlin, Automa, Cognibot, Brassoul

Auronia – Nornheim (NH)
#Draugr: Barrowmar, Grimwake, Frostrevenant, Drowger, Coldwight
#Squidkin: Riftkrak, Abyssant, Maelmaph, Tentacast, Deepclaw
Kraken: Riftkrak, Abyssant, Maelmaph, Tentacast, Deepclaw
Ratatoskr: Gossquirrel, Rattasker, Skytattle, Runaskip, Nibblenut
#Valkyrie: Valyra, Shieldra, Wingyr, Skymaiden, Einheris

Auronia – Bretonreach (BR)
Banshee: Keenera, Mourna, Sidhecall, Cryrae, Wailis
Dullahan: Noctorider, Headlesser, Neckless, Corthorse, Dullahand
Will-o-Wisp: Marshglow, Willo, Glimfloat, Fadelflame, Winkwisp
Kelpie: Reedsteed, Mirehoof, Tidefoal, Kelpry, Bridlemare
#Pixi: Spriggle, Glimmerkin, Pipsprite, Pixelle, Sparkli
#Cait Sith: Catshee, Shadowpurr, Pookat, Caithsí, Wispwhisk
#Cu Sith: Fayfang, Moorwoof, Cushie, Houndshee, Greenhound
#Kobold: Kobble, Coalbold, Boldergob, Minegnome, Kobald
#Vampire: Nosfera, Hemora, Nightling, Ebonfang, Sangrin
#Hellhound: Brimwoof, Cindermaw, Hadehound, Scorchpup, Pyrocanis
#Gremlin: Gremlit, Tinkerimp, Sprungle, Clanksqueak, Mischiv
#Wyvern: Wyverna, Wingserp, Skyrake, Glairex, Galevyr

Auronia – Rheinland (RL)
Angel: Serapha, Haloria, Lumiel, Aurafin, Cherubis
Demon: Infernox, Malifer, Abyssum, Diabrette, Gloomarch
Succubus: Lilivex, Dreamra, Umbrakiss, Charmina, Subliss
#Leviathan: Abyssodon, Tidemor, Seamonger, Leviacra, Oceanox
#Cetus: Cetoid, Whalith, Behemocean, Seastros, Leviwhale
#Unicorn: Prismane, Moonhoof, Alicor, Hornbelle, Puricorn
#Cockatrice: Basilicrow, Crownserp, Roostone, Gallowsbeak, Cockatox
#Undine: Undina, Rippleine, Aquefa, Limerill, Seafae
#Ogre: Grummox, Ogrum, Maulgob, Boulderbulk, Brutelkin
#Basilisk: Baslyx, Petricoil, Stonecoil, Gorguile, Basilith
#Gargoyle: Stoneward, Gargra, Corniclaw, Groteska, Graule
#Salamander: Flamander, Sizzlenewt, Pyromander, Embertail, Ignivern
#Phoenix: Igniph, Fenyx, Ashflare, Embercrest, Rebornix

Auronia – Rodinian Tsardom (RT)
Leshy: Leshkin, Mossvard, Barkovik, Sylvadin, Woodwight
Vodyanoy: Vodyak, Pondgrim, Marshbeard, Rillgob, Drownard
#Kikimora: Kikima, Moranette, Broomwitch, Kikiwife, Cronekin
#Domovoi: Hearthkin, Domovi, Ashsprite, Stovekeeper, Bristlehob
#Rusalka: Rusalla, Brookbelle, Miremaid, Dewsirene, Weepwave
#Alkonost: Alkonya, Joysong, Blissbird, Halcyra, Sunchant

Borealia – United free Republic of Borealia (URB)
Lich: Grimarch, Phylax, Soulbind, Mortivus, Boneward
Jack-o-lantern: Pumpkith, Gourdlume, Wick-O, Lanternjack, Hollowkin
#Lizardman: Lacerto, Saurlet, Geckru, Drakshed, Scalekin
#Mothman: Umbramoth, Noctemoth, Lampryth, Dreadwing, Eyelume

Borealia – Sovereign Tribes of the Ancestral Plains (STAP)
Thunderbird: Stormwing, Skylash, Thundara, Boomfeather, Astrapin
Horned Serpent Uktena: Uktalon, Horncoil, Antlisk, Serphorn, Uktenox
#Sasquatch: Squatcha, Mossbulk, Shaggfoot, Pinegruff, Woodbaron
#Pukwudgie: Pukwidge, Thornmite, Pricklet, Needlenik, Puckbud
#Trickster Raven: Trickrave, Quipwing, Cawjax, Jestcorvid, Prankrook
#Kachina: Kachiri, Cloudancer, Maskway, Hopispirit, Rainson

Dawnmarch – Lotus-Dragon Kingdom (LDK)
Jiang-Shi: Stiffwalker, Jiangpo, Coinseal, Hopseng, Qinghop
Vermillion Bird: Vermiluxe, Zhuquet, Emberquin, Scarletail, Vermisa, Verbirdion
#Sandworm: Dunewyrm, Sirocoil, Shachong, Grainmaw, Sandswallow
#Yeti: Frozape, Abomni, Shaggra, Himalok, Snowyasha
#Long: Longfei, Rivendra, Jadecoil, Shenlongo, Pearlwyrm
#Taotie: Taomask, Mawtile, Voramask, Greedjaw, Guimunch
#Huli-Jing: Hulivix, Jingkits, Charmvulp, Silkfox, Hujin
#Xiangliu: Xianghydra, Ninecoil, Manymaw, Ninelash, Jiushe, Multiheadthreat
#Azure Dragon: Azulong, Qingwyrm, Bluemander, Skyserp, Cobaltlong
#White Tiger: Baihuan, Frostclaw, Whitera, Palepride, Ivoryfang
#Black Turtle: Noctorto, UmbraChel, Serpshell, Northguard, Darkcarap

Dawnmarch – Baharamandal (BM)
Gandharva: Gandharo, Songriva, Ragalor, Cloudharp, Aerialute
Naga: Nagiva, Hoodara, Coilindra, Vasukin, Serpanta
#Apsara: Padmara, Apsalia, Celindra, Nritiya, Skyraas
#Rakshasa: Rakshar, Manebeast, Raksine, Shagrin, Rakshox
#Garuda: Garudra, Windtalon, Skyadu, Garuwing, Sunraja
#Vetala: Vetalisk, Gravebind, Nightpriest, Hollowji, Cadawit

Dawnmarch – Yamanokubo (YK)
Spider-Crab: Scrabber, Crabgantic, Scrooder, Kani, Kanigant
Kitsune: Kitsumi, Ninevix, Foxyre, Hoshikits, Kitsel
Yuki-Onna: Yukiro, Glacelle, Frostrae, Cryoveil, Snowhime
#Akaname: Grimelick, Akalick, Scuzzume, Filthling, Sudsnap
#Japanese Lantern: Glowchin, Obakite, Chochibi, Lantera, Shadelamp
#Kappa: Kappon, Cucumbran, Saucerling, Mizukawa, Shellume
#Ittan Momen: Boltgeist, Clothwing, Yardrift, Sashwisp, Momenfly
#Jorōgumo: Silkuma, Bridewidow, Joroweb, Venestra, Webbelle
#Karakasa-obake: Hopparasol, Umbrette, Karakappy, Rainwight, One-Eyella
#Tengu: Stormbeak, Skyfiend, Karastryx, Airtalon, Tengale
#Tsuchinoko: Stubsnake, Chubbcoil, Coilobi, Gourdserp, Tsuchipop
#Kamaitachi: Weaslash, Galeasel, Sickleto, Kamawea, Cutcurrent

Gulfbelt – Xochimex (XM)
La Llorona: Weepbelle, Marimourn, Canalcry, Noxmadre, Riverwail
Chupacabra: Chupito, Goatsip, Cabrasaur, Suckfang, Chupalisk
Charro Negro: Nightrider, Sombrablack, Charro Noct, CoalCab, Catrinero
#Alebrije: Alebrilla, Colorbloom, Dreambeast, Brijito, Paintpelt
#La Lechuza: Lechusa, Hootbruja, Midnoct, Owlinyx, Sorcowl
#La Mano Peluda: Pilohand, Peluman, Furrygrasp, Hairclutch, Palmpelt, Unmannedhand

Gulfbelt – Eagle Serpent Dominion (ESD)
Quetzalcoatl: Quetzala, Plumecoil, Featherserp, Quetza, Skyscale
Ahuizotl: Ahuizot, Handtail, Lakegnash, Azotlusk, Drowndog
Cipactli: Cipac, Crocprime, Mawdelta, Primacroc, Jagjaw
Ocelot: Oceluna, Selvacat, Spotblade, Ocelin, Nightrosette

Gulfbelt – Itzam Empire (IE)
Azureus: Azurtoad, Darti, Cobaltoad, Bluetip, Poispip
Tapir: Tapiri, Snoutback, Dorsnork, Taproot, Palmtree
#Alux: Aluxi, Fieldling, Milpafae, Aluxobit, Plotkin
#Camazotz: Camazra, Zotslash, Nightmuzzle, Camabat, Batlord
#Wayob: Wayova, Dreamtwin, Shadebound, Soulstep, Nightcopy
#Sloth: Slumbril, Mossloth, Dozeclaw, Laggrin, Hangslow
#Macaw: Macara, Scarletwing, Palettail, Araflare, Macawra

Moana Crown – Spice Route League (SRL)
Crab man: Kanikina, Pinchette, Shellina, Reefclara, Coconella
Taniwha: Taniwa, Reefward, Wakecoil, Gorgeguard, Riverjarl
#Moo: Mo’ona, Reefmoke, Mooscale, O’ogeko, Lagodrake
#Menehune: Menehu, Stonebuilder, Menehop, Tinykanaka, Nightmason
#Nightmarcher: Nightmarch, Drumstride, Pathwraith, Torchline, Ghostfile
#Adaro: Adarow, Brineblade, Cyclonfin, Stormmer, Spearfish

Orienthem – Crescent Caliphate (CC)
Genie: Djinna, Wishra, Lampheer, Jinnari, Aetherif
Bahamut: Bahamox, Abyhmut, Deepmajest, Behemarid, Grandray
#Manticore: Scoroleon, Mantikra, Quillmaw, Tailspike, Manestryx
#Ifrit: Ifryx, Emberdjinn, Blazefrit, Ifraze, Cindershah
#Camel: Emberdary, Dromablaze, Pyromedary, Flamelid, Scorchump, Camelor, Dunalop, Humpset, Sandromed, Caravanx, Flamel, Pyrodary, Geisedar, GEISEDAR MANN
#Ghoul: Ghulkin, Sandghast, Carozerk, Duneskulk, Gravegnaw
#Roc: Rocara, Skymass, Taloroc, Sunlift, Stratoshrike
#Nasnas: Nasni, Halflurch, Splitling, Crookstride, Onehalf

Orienthem – Hammurabia (HR)
Girtablilu: Scorpitar, Stingward, Akrabuin, Ziggaur, Biluguard
Lamassu: Lamasson, Gatebull, Wingguard, Lamashield, Ziggurhon
#Oryx: Oryxon, Scimahorn, Dunestrike, Saharox, Sandbuck
#Anzu: Anzugal, Galegriff, Monsoonroar, Stormzu, Skybellow
#Pazuzu: Pazuwind, Zephzuzu, Sandzeph, Eastwhirl, Desertzu
#Dugong: Dugona, Seagracer, Herbaflo, Seacowly, Grassgrazer
#Fennec: Fennix, Dunesqueak, Earflare, Sandfen, Vulpetta
#Caracal: Caraclaw, Desertlyn, Karaquik, Tuftstrike, Earblade

Orienthem – Eretz-Shalem League (ESL)
Golem: Clayward, Golim, Shamirite, Seferim, Emetron
Dolphin: Tursabelle, Wavewhistle, Seasmile, Surfari, Dolfwing
#Shedim: Shedael, Shadeim, Duskkin, Murkhost, Netherim
#Ziz: Zizra, Skyvast, Cloudsoar, Stratosaur, Firmament
#Behemoth: Behemax, Earthbulk, Deepbrute, Landlevi, Terraox
#Lilith: Lilanis, Nightael, Moonkiss, Lilistra, Umbrama

Saharene – Kemet (KM)
Anubis: Duati, Jackalakh, Anupet, Embalord, Khepwolf
Wadjet: Wadja, Cobragard, Nilehood, Ureye, Serishield
#Bastet: Bastelle, Catara, Purrsesh, Sekmi, Bastara
#Mummy: Bandrath, Wrapscar, Tombweft, Cartonox, Linenraith
#Sphinx: Sandphinx, Oracleon, Pyramayne, Riddleon, Desertis
#Bennu: Bennura, Sunplume, Nileflare, Heronix, Dawnflit
#Ammit: Ammunch, Scalesnap, Crocleo, Devoura, Heartnosh
#Serpopard: Serpop, Pardsnake, Neckcoil, Spotcoil, Serpopard
#Uraeus: Uraelia, Browflame, Diademserp, Royalcoil, Cobreris
#Spinosaur: Spinosurge, Sailjaw, Rivensail, Marshsaur, Spindrake

Tundria – Sila Council (SC)
Amarok: Amarokh, Frostfang, Glaciwulf, Tundrahound, Snowmara
Polar Bear: Polarus, Bergbear, Icebrawler, Glaciursus, Floefur
#Akhlut: Akhlune, Brinelup, Orcawolf, Finfang, Seawyr
#Keelut: Keelusk, Barehound, Slickpaw, Nightcur, Skinwolf
#Ijiraq: Ijirak, Shiftling, Miragekin, Maskstag, Lostwalker
#Penguin: Pengwing, Chillquin, Tuxlide, Frostwaddle, Glacupen, Pengwing
#Caribou: Caribolt, Tundrabo, Rangivel, Velvetine, Snowrack
#Walrus: Walrumble, Tuskurrent, Brinewhisk, Icewal, Blubtusk

Uluru – Red Sun Commonwealth (RSC)
Drop Bear: Dropkoal, Eucapounce, Plummko, Branchdrop, Sneakkoal
Min-Min Lights: Minminn, Outglow, Dunespark, Bushglint, Wispstray
#Sea Turtle: Seashield, Chelomar, Tiderunner, Shellvoy, Oceleon
#Australovenator: Australash, Banjorap, Bushclaw, Redtalon, Novaraptor
#Platypus: Platypup, Billabub, Ornipod, Spurling, Puddleplat
#Emu: Emuzoom, Dromach, Plumezu, Strideru, Emuprise
#Hoop Snake: Hoopcoil, Ringadder, Wheelserp, Ouroloop, Rollasp
#Kangaroo: Pouchpunch, Boomaroo, Skipslam, Marsupow, Roojack

Uluru – Yara Nations (YN)
Bunyip: Billabellow, Bunyara, Marshmurm, Bogboon, Creeklurker
Rainbow Serpent: Prismcoil, Spectracoil, Arcudra, Raincoil, Chromanaga
#Mimi spirit: Miminee, EchoMimi, Paintwisp, Rockscribe, Slimi
#Croc-Man: Crocborne, Scalewalker, Caimanox, Brinegait, Reptanth
#Nargun: Rocknarg, Basalurk, Cavergast, Stonewarden, Gloomglen
#Tiddalik: Tiddalick, Floodfrog, Gorgegulp, Swellhop, Drysip
#Yara-ma-yha-who: Yarahoo, Figdropper, Treeperch, Redgrin, Yaramite

Verdania – Gran Columbia (GC)
Curupira: Curupix, Backheel, Foresttrix, Trailwisp, Heelturn
Capybara: Capybuddy, Floatbara, Capycurrent, Marshcavy, Chillbara
#Mapinguari: Mapingraw, Selvalurk, Mawpingu, Junglemaw, Slothgrim
#Toucan: Beakazon, Toucolor, Tropibeak, Aracara, Fruitcrest
#Piranha: Razorfin, Snapshoal, Pirabite, Gnashna, Sawgill
#Armadillo: Rolladillo, Shellbur, Dillotank, Cactillo, Armaterra
#Ant-eater: Myrmunch, Tongolong, Anteasy, Sipant, Tamanduo

Verdania – Sapa Inti Empire (SIE)
Fishman: Gillborn, Brinewalker, Neridon, Finstrider, Surfkin
Argentinosaurus: Argentitan, Pampadon, Colossaur, Platarex, Titanpamp
Amaru: Amarusa, Twincoil, Serpentupa, Stormamar, Andecoil
#Apu: Apuguard, Peakward, Summiton, Mountadel, Andecrown
#Supay: Supaxa, Cavernox, Umbravax, Pitwarden, Abysses
#Ypupiara: Ypupiro, Riverhar, Scalehand, Pupyara, Ypurana
#Urcuchillay: Chillama, Urcuchi, Llamastar, Skyllama, Constellama
#Pishtaco: Pishtaxa, Cordillon, Nightprow, Andesly, Dusktrader

  </script>

  <script>
  // --- Utilities ---
  const $ = (sel, el=document) => el.querySelector(sel);
  const $$ = (sel, el=document) => Array.from(el.querySelectorAll(sel));
  const slug = s => s.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'');

  // Parse the source text into a structured object
  function parseSource(text){
    const lines = text.split(/\r?\n/);
    const data = [];
    let currentRegion = null;
    for (const raw of lines){
      const line = raw.trim();
      if(!line || line.startsWith('#')) continue;
      if(line.includes('–') && !line.includes(':')){
        currentRegion = line; // Region header
        data.push({ type:'region', region: currentRegion });
        continue;
      }
      const idx = line.indexOf(':');
      if(idx !== -1){
        const base = line.slice(0, idx).trim();
        const rest = line.slice(idx+1).trim();
        const suggestions = rest.split(',').map(s=>s.trim()).filter(Boolean);
        if(!currentRegion){
          currentRegion = 'Uncategorized';
          data.push({ type:'region', region: currentRegion });
        }
        data.push({ type:'group', region: currentRegion, base, suggestions });
      }
    }
    return data;
  }

  // Build DOM
  const app = $('#app');
  const regionsEl = $('#regions');
  const progressText = $('#progressText');
  const jump = $('#jump');
  const submitBtn = $('#submitBtn');
  const resetBtn = $('#resetBtn');
  const exportBtn = $('#exportBtn');
  const copyBtn = $('#copyBtn');
  const form = $('#submitForm');
  const summaryText = $('#summaryText');
  const toTopBtn = $('#toTopBtn');

  // Default artwork image for every base (change this later per-base if desired)
  const IMAGE_BASE = 'images/';
  const DEFAULT_GENDER = 'f';
  const DEFAULT_VARIANT = 'blue';
  const IMAGE_EXT = '.webp';
  const FALLBACK_IMAGE = 'images/tengu.webp';
  const IMAGE_VARIANTS = <?= json_encode($image_variants) ?>;
  const AVAILABLE_SPECIES = <?= json_encode($allowedSlugs) ?>;
  const variantsFor = (base) => {
    const sb = slug(base);
    const opts = IMAGE_VARIANTS[sb];
    return {
      sb,
      list: (opts && opts.length) ? opts : [`${DEFAULT_GENDER}_${DEFAULT_VARIANT}`]
    };
  };

  const raw = $('#source').textContent; // keep diacritics
  const parsed = parseSource(raw);
  const allowed = new Set(AVAILABLE_SPECIES);
  const filtered = parsed.filter(item => item.type !== 'group' || allowed.has(slug(item.base)));

  // Group by region
  const regions = [];
  let current=null;
  for(const item of filtered){
    if(item.type==='region'){ current = { name:item.region, slug: slug(item.region), groups:[] }; regions.push(current); }
    else if(item.type==='group' && current){ current.groups.push(item); }
  }
  const regionsFiltered = regions.filter(r => r.groups.length);

  // Build jump menu
  for(const r of regions){
    const opt = document.createElement('option');
    opt.value = r.slug; opt.textContent = r.name;
    jump.appendChild(opt);
  }
  jump.addEventListener('change', () => {
    const id = 'region-' + jump.value;
    const target = document.getElementById(id);
    if(target){
      target.open = true;
      target.scrollIntoView({behavior:'smooth', block:'start'});
    }
  });

  // Selection state (persisted)
  const STATE_KEY = 'namepicker_selections_v1';
  let state = JSON.parse(localStorage.getItem(STATE_KEY) || '{}'); // key => choice

  const totalGroups = regionsFiltered.reduce((n,r)=>n + r.groups.length, 0);
  function selectedCount(){ return Object.keys(state).length; }
  function updateProgress(){
    const left = totalGroups - selectedCount();
    progressText.textContent = `Selected ${selectedCount()} / ${totalGroups} · ${left} left`;
    summaryText.textContent = left ? `Pick one option in each line to enable Submit.` : `All set! You can Submit or Export.`;
    submitBtn.disabled = !!left;
  }

  // Build form hidden inputs based on state
  function rebuildForm(){
    form.innerHTML='';
    // JSON blob for easy server handling
    const arr = Object.entries(state).map(([key,choice])=>{
      const [regionSlug, baseSlug] = key.split('__');
      const meta = META[key];
      return { region: meta.region, base: meta.base, choice, region_slug: regionSlug, base_slug: baseSlug };
    });
    const json = document.createElement('input');
    json.type='hidden'; json.name='selection_json'; json.value = JSON.stringify(arr);
    form.appendChild(json);

    // PHP array style selection[region_slug][base_slug] = choice
    for(const [key,choice] of Object.entries(state)){
      const [regionSlug, baseSlug] = key.split('__');
      const input = document.createElement('input');
      input.type='hidden'; input.name = `selection[${regionSlug}][${baseSlug}]`;
      input.value = choice;
      form.appendChild(input);
    }
  }

  // Create a meta map from group key => {region, base}
  const META = {};

  // Render regions + groups
  regionsFiltered.forEach((r, i) => {
    const sec = document.createElement('details');
    sec.className = 'region';
    sec.id = 'region-' + r.slug;
    if(i === 0) sec.open = true;
    const summary = document.createElement('summary');
    summary.innerHTML = `<strong>${r.name}</strong>`;
    sec.appendChild(summary);

    for(const g of r.groups){
      const key = slug(r.name) + '__' + slug(g.base);
      META[key] = { region: r.name, base: g.base };

      const wrap = document.createElement('div');
      wrap.className='group'; wrap.dataset.key = key;

      const base = document.createElement('div');
      base.className='base';
      base.innerHTML = `<strong>${g.base}</strong>`;
      wrap.appendChild(base);

      // artwork image per original/base name with variant navigation
      const { sb, list: variants } = variantsFor(g.base);
      let vIndex = 0;

      const artWrap = document.createElement('div');
      artWrap.className = 'art-wrap';

      const prev = document.createElement('button');
      prev.type = 'button';
      prev.className = 'variant-nav prev';
      prev.textContent = '\u25C0'; // ◀

      const next = document.createElement('button');
      next.type = 'button';
      next.className = 'variant-nav next';
      next.textContent = '\u25B6'; // ▶

      const img = new Image();
      img.src = `${IMAGE_BASE}${sb}_${variants[vIndex]}${IMAGE_EXT}`;
      img.alt = `${g.base} artwork`;
      img.className = 'art';
      img.loading = 'lazy';
      img.decoding = 'async';
      img.onerror = () => { img.onerror = null; img.src = FALLBACK_IMAGE; };
      prev.addEventListener('click', () => {
        vIndex = (vIndex - 1 + variants.length) % variants.length;
        img.src = `${IMAGE_BASE}${sb}_${variants[vIndex]}${IMAGE_EXT}`;
      });

      next.addEventListener('click', () => {
        vIndex = (vIndex + 1) % variants.length;
        img.src = `${IMAGE_BASE}${sb}_${variants[vIndex]}${IMAGE_EXT}`;
      });

      artWrap.appendChild(prev);
      artWrap.appendChild(img);
      artWrap.appendChild(next);
      wrap.appendChild(artWrap);

      const opts = document.createElement('div');
      opts.className = 'options';
      opts.setAttribute('role','radiogroup');
      opts.setAttribute('aria-label', `${g.base} options`);

      g.suggestions.forEach((label, i) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'option';
        b.textContent = label;
        b.setAttribute('role','radio');
        b.setAttribute('aria-checked','false');
        b.dataset.value = label;

        // Keyboard: left/right to move
        b.addEventListener('keydown', (e) => {
          const list = $$('.option', opts);
          const idx = list.indexOf(b);
          if(['ArrowRight','ArrowDown'].includes(e.key)){ e.preventDefault(); const n = list[Math.min(idx+1, list.length-1)]; n?.focus(); }
          if(['ArrowLeft','ArrowUp'].includes(e.key)){ e.preventDefault(); const p = list[Math.max(idx-1, 0)]; p?.focus(); }
          if(['Enter',' '].includes(e.key)){ e.preventDefault(); b.click(); }
        });

        b.addEventListener('click', () => {
          selectOption(key, label, opts);
        });
        opts.appendChild(b);
      });

      // Preselect from saved state
      if(state[key]){
        setTimeout(()=>{ const btn = $(`.option[data-value="${CSS.escape(state[key])}"]`, opts); if(btn) markSelected(btn, opts); }, 0);
      }

      wrap.appendChild(opts);
      sec.appendChild(wrap);
    }

    regionsEl.appendChild(sec);
  });

  for (const key of Object.keys(state)) {
    if (!META[key]) {
      delete state[key];
    }
  }
  localStorage.setItem(STATE_KEY, JSON.stringify(state));

  function markSelected(btn, container){
    $$('.option', container).forEach(el=>el.setAttribute('aria-checked','false'));
    btn.setAttribute('aria-checked','true');
  }

  function selectOption(key, value, container){
    state[key] = value;
    localStorage.setItem(STATE_KEY, JSON.stringify(state));
    markSelected($(`.option[data-value="${CSS.escape(value)}"]`, container), container);
    updateProgress();
    rebuildForm();
  }

  resetBtn.addEventListener('click', () => {
    if(!confirm('Clear all selections?')) return;
    state = {}; localStorage.removeItem(STATE_KEY);
    $$('.option[aria-checked="true"]').forEach(b=>b.setAttribute('aria-checked','false'));
    updateProgress(); rebuildForm();
  });

  exportBtn.addEventListener('click', () => {
    const payload = form.querySelector('[name="selection_json"]').value;
    const blob = new Blob([payload], {type:'application/json'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'name-selections.json'; a.click();
    URL.revokeObjectURL(url);
  });

  copyBtn.addEventListener('click', async () => {
    const payload = form.querySelector('[name="selection_json"]').value;
    try{ await navigator.clipboard.writeText(payload); copyBtn.textContent='Copied!'; setTimeout(()=>copyBtn.textContent='Copy JSON', 1400);}catch(e){ alert('Copy failed. Export JSON instead.'); }
  });

  toTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  submitBtn.addEventListener('click', () => {
    rebuildForm();
    form.submit();
  });

  // Initial UI state
  rebuildForm();
  updateProgress();
  </script>