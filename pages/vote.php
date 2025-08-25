<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Creature Name Picker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet" />
  <style>
    :root{
      --bg1:#b7e3ff; --bg2:#e1f6ff; --bg3:#e3ffe5; --glass:rgba(255,255,255,.55);
      --ink:#0b1733; --muted:#4b5563; --accent:#00c2ff; --accent2:#22d3ee; --ok:#16a34a; --warn:#f59e0b;
      --chip:#f0f9ff; --chipBorder:#cde8ff; --chipInk:#0b1733;
    }
    html,body{height:100%;}
    body{
      margin:0; font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:var(--ink);
      background: radial-gradient(1200px 900px at 10% -10%, #a7f3d0 0%, transparent 60%),
                  radial-gradient(900px 700px at 110% 10%, #bae6fd 0%, transparent 60%),
                  linear-gradient(135deg,var(--bg1),var(--bg2) 40%,var(--bg3));
      background-attachment: fixed;
      overflow-y:auto;
    }

    /* Floating bubbles for a Frutiger‑Aero vibe */
    .bubble{ position:fixed; border-radius:50%; filter: blur(0.3px) saturate(120%);
      background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.9), rgba(255,255,255,.35) 60%, rgba(255,255,255,.05) 70%),
                  radial-gradient(circle at 70% 70%, rgba(255,255,255,.6), rgba(255,255,255,.1) 60%);
      box-shadow: inset 0 1px 2px rgba(255,255,255,.6), inset 0 -6px 12px rgba(0,0,0,.08);
      mix-blend-mode: screen; pointer-events:none; animation: drift var(--d, 46s) linear infinite;
      opacity: .5;
    }
    @keyframes drift { from { transform: translateY(0) translateX(0);} to { transform: translateY(-120vh) translateX(10vw);} }

    .app{ max-width:1100px; margin: clamp(16px,3vw,32px) auto; padding: 18px;
      backdrop-filter: blur(14px) saturate(120%);
      -webkit-backdrop-filter: blur(14px) saturate(120%);
      background: var(--glass);
      border: 1px solid rgba(255,255,255,.6);
      border-radius: 24px; box-shadow: 0 20px 60px rgba(15, 23, 42, .12), inset 0 1px 0 rgba(255,255,255,.5);
    }

    header{
      display:flex; gap:16px; align-items:center; justify-content:space-between; flex-wrap:wrap; padding: 8px 6px 18px;
    }
    .title{
      font-weight:800; letter-spacing:.2px; line-height:1.1; margin:0;
      background: linear-gradient(90deg,#0ea5e9, #10b981 50%, #22d3ee);
      -webkit-background-clip:text; background-clip:text; color:transparent; font-size: clamp(24px, 3vw, 36px);
      text-shadow: 0 1px 0 rgba(255,255,255,.35);
    }
    .toolbar{ display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .select, .btn{ appearance:none; border:1px solid rgba(255,255,255,.7); background: linear-gradient(#ffffffaa,#ffffff77);
      box-shadow: 0 6px 20px rgba(2,132,199,.18), inset 0 1px 0 rgba(255,255,255,.7);
      padding: 10px 14px; border-radius: 14px; font-weight:600; cursor:pointer; color:var(--ink);
      backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    }
    .btn.primary{ background: linear-gradient(180deg, #baf1ff, #8ee7ff); border-color:#b6ecff; }
    .btn.good{ background: linear-gradient(180deg, #baf7c1, #7ae38a); border-color:#bbf0c0; }
    .btn.warn{ background: linear-gradient(180deg, #ffeab5, #ffd08a); border-color:#ffe4b8; }
    .btn:disabled{ opacity:.6; cursor:not-allowed; }

    .progress{ font-size: 14px; color:var(--muted); }

    .region{ margin: 18px 6px; padding: 14px 12px 4px; border-radius: 18px; border:1px solid rgba(255,255,255,.7);
      background: linear-gradient(180deg,#ffffffaa,#ffffff66);
      box-shadow: inset 0 1px 0 rgba(255,255,255,.6), 0 8px 24px rgba(2,132,199,.12);
    }
    .region h2{ margin:4px 2px 10px; font-weight:800; font-size: clamp(18px,2.2vw,22px); }

    .group{ padding:10px 8px 12px; border-radius: 14px; transition: background .25s ease; }
    .group:hover{ background: rgba(255,255,255,.45); }
    /* Artwork image under each base name */
    .art{ display:block; width:min(420px,100%); height: auto; object-fit:cover;
      margin:6px 2px 12px; border-radius:16px; border:1px solid rgba(255,255,255,.75);
      background: linear-gradient(180deg,#ffffff,#f5fbff);
      box-shadow: 0 10px 30px rgba(2,132,199,.18), inset 0 1px 0 rgba(255,255,255,.8);
    }

    .options{ display:flex; flex-wrap:wrap; gap:10px; }
    .option{ display:inline-flex; align-items:center; justify-content:center; padding:9px 12px; border-radius: 999px;
      background: linear-gradient(180deg,#f8fcff,#ecf7ff); border:1px solid var(--chipBorder); color:var(--chipInk);
      box-shadow: 0 6px 18px rgba(14,165,233,.15), inset 0 1px 0 rgba(255,255,255,.7);
      font-weight:700; letter-spacing:.2px; cursor:pointer; user-select:none; min-height: 36px;
      transition: transform .06s ease, box-shadow .15s ease, background .2s ease; outline:none;
    }
    .option:hover{ transform: translateY(-1px); }
    .option[aria-checked="true"]{
      background: linear-gradient(180deg,#b6f1ff,#7ae7ff); border-color:#9ae8ff;
      box-shadow: 0 10px 26px rgba(34,211,238,.28), inset 0 1px 0 rgba(255,255,255,.9);
    }
    .option:focus-visible{ box-shadow: 0 0 0 3px rgba(34,211,238,.5), 0 6px 16px rgba(34,211,238,.25); }

    .summary{ position: sticky; bottom: 10px; display:flex; gap:10px; justify-content:flex-end; align-items:center; padding-top:10px; }

    .mini{ font-size:12px; color:var(--muted); }

    details.howto{ margin: 10px 6px 0; }
    details.howto summary{ cursor:pointer; font-weight:700; }

    @media (prefers-reduced-motion: reduce){
      .bubble{ display:none; }
      .option{ transition:none; }
    }
  </style>
</head>
<body>
  <!-- Ambient bubbles -->
  <div class="bubble" style="--d:52s; width:220px; height:220px; left:5vw; bottom:-20vh;"></div>
  <div class="bubble" style="--d:64s; width:320px; height:320px; left:25vw; bottom:-25vh;"></div>
  <div class="bubble" style="--d:58s; width:180px; height:180px; left:70vw; bottom:-18vh;"></div>
  <div class="bubble" style="--d:70s; width:260px; height:260px; left:85vw; bottom:-22vh;"></div>

  <main class="app" id="app" aria-live="polite">
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

    <div class="progress" id="progressText">Loading…</div>

    <div id="regions"></div>

    <div class="summary">
      <div class="mini" id="summaryText"></div>
    </div>

    <details class="howto">
      <summary>How this works (and how to hook up PHP)</summary>
      <div class="mini">
        <p>• Click exactly one suggested name per line. Your choices autosave in your browser.</p>
        <p>• <strong>Submit</strong> posts to <code>submit.php</code> via a standard <code>POST</code> with two payload styles for convenience:
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
    <form id="submitForm" action="submit.php" method="post" hidden></form>
  </main>

  <!-- Your source data: paste/edit freely. Lines with “–” (en dash) are regions; lines with “name:” are groups. -->
  <script id="source" type="text/plain">
Auronia – Aegia Aeterna (AA)

Charybdis: Riptara, Maelstrix, Vortessa, Gulpmire, Swirlgrim

Lamia: Lamoria, Serafemme, Nagaressa, Viperelle, Lamivra

Centaur: Centara, Sagritar, Hoofkin, Bowmane, Tauriel

Pegasus: Skysteed, Pegara, Nimbuscolt, Aetherfilly, Wingstall

Arachne: Silkatrix, Arachnea, Loomspinner, Weblira, Threadessa

Cyclops: Unoculus, Monocleus, Eyeclast, Cycloidon, Oneyx

Dryad: Verdelle, Grovelet, Barknymph, Sylfae, Leafdry

Alraune: Rootbelle, Mandrella, Belladroot, Alranth, Bloomdra

Minotaur: Labyrhorn, Minotorn, Bullmaze, Asterion, Hornox

Gorgon: Medusia, Gorgyra, Petrisa, Stonegaze, Serpelle

Mermaid: Nerissa, Tideva, Brinelle, Marivyn, Seamelle

Phoenix: Pyrewing, Emberis, Ashra, Reflara, Feniqa

Griffin: Gryphlet, Aerieon, Liongale, Griphus, Eagral

Harpy: Galeshriek, Talonette, Harpixa, Skreeva, Vireen

Automaton: Clockwerk, Gearlin, Automa, Cognibot, Brassoul

Auronia – Nornheim

Draugr: Barrowmar, Grimwake, Frostrevenant, Drowger, Coldwight

Kraken: Riftkrak, Abyssant, Maelmaph, Tentacast, Deepclaw

Ratatoskr: Gossquirrel, Rattasker, Skytattle, Runaskip, Nibblenut

Valkyrie: Valyra, Shieldra, Wingyr, Skymaiden, Einheris

Auronia – Bretonreach

Banshee: Keenera, Mourna, Sidhecall, Cryrae, Wailis

Dullahan: Noctorider, Headlesser, Neckless, Corthorse, Dullahand

Pixi: Spriggle, Glimmerkin, Pipsprite, Pixelle, Sparkli

Kelpie: Reedsteed, Mirehoof, Tidefoal, Kelpry, Bridlemare

Cait Sith: Catshee, Shadowpurr, Pookat, Caithsí, Wispwhisk

Cu Sith: Fayfang, Moorwoof, Cushie, Houndshee, Greenhound

Kobold: Kobble, Coalbold, Boldergob, Minegnome, Kobald

Vampire: Nosfera, Hemora, Nightling, Ebonfang, Sangrin

Hellhound: Brimwoof, Cindermaw, Hadehound, Scorchpup, Pyrocanis

Will-o-Wisp: Marshglow, Willo, Glimfloat, Fadelflame, Winkwisp

Gremlin: Gremlit, Tinkerimp, Sprungle, Clanksqueak, Mischiv

Wyvern: Wyverna, Wingserp, Skyrake, Glairex, Galevyr

Auronia – Rheinland

Angel: Serapha, Haloria, Lumiel, Aurafin, Cherubis

Demon: Infernox, Malifer, Abyssum, Diabrette, Gloomarch

Leviathan: Abyssodon, Tidemor, Seamonger, Leviacra, Oceanox

Cetus: Cetoid, Whalith, Behemocean, Seastros, Leviwhale

Unicorn: Prismane, Moonhoof, Alicor, Hornbelle, Puricorn

Cockatrice: Basilicrow, Crownserp, Roostone, Gallowsbeak, Cockatox

Undine: Undina, Rippleine, Aquefa, Limerill, Seafae

Ogre: Grummox, Ogrum, Maulgob, Boulderbulk, Brutelkin

Basilisk: Baslyx, Petricoil, Stonecoil, Gorguile, Basilith

Succubus: Lilivex, Dreamra, Umbrakiss, Charmina, Subliss

Gargoyle: Stoneward, Gargra, Corniclaw, Groteska, Graule

Salamander: Flamander, Sizzlenewt, Pyromander, Embertail, Ignivern

Phoenix: Igniph, Fenyx, Ashflare, Embercrest, Rebornix

Auronia – Rodinian Tsardom

Kikimora: Kikima, Moranette, Broomwitch, Kikiwife, Cronekin

Leshy: Leshkin, Mossvard, Barkovik, Sylvadin, Woodwight

Domovoi: Hearthkin, Domovi, Ashsprite, Stovekeeper, Bristlehob

Rusalka: Rusalla, Brookbelle, Miremaid, Dewsirene, Weepwave

Vodyanoy: Vodyak, Pondgrim, Marshbeard, Rillgob, Drownard

Alkonost: Alkonya, Joysong, Blissbird, Halcyra, Sunchant

Borealia – URB

Lich: Grimarch, Phylax, Soulbind, Mortivus, Boneward

Jack-o-lantern: Pumpkith, Gourdlume, Wick-O, Lanternjack, Hollowkin

Lizardman: Lacerto, Saurlet, Geckru, Drakshed, Scalekin

Mothman: Umbramoth, Noctemoth, Lampryth, Dreadwing, Eyelume

Borealia – STAP

Thunderbird: Stormwing, Skylash, Thundara, Boomfeather, Astrapin

Sasquatch: Squatcha, Mossbulk, Shaggfoot, Pinegruff, Woodbaron

Horned Serpent Uktena: Uktalon, Horncoil, Antlisk, Serphorn, Uktenox

Pukwudgie: Pukwidge, Thornmite, Pricklet, Needlenik, Puckbud

Trickster Raven: Trickrave, Quipwing, Cawjax, Jestcorvid, Prankrook

Kachina: Kachiri, Cloudancer, Maskway, Hopispirit, Rainson

Dawnmarch – LDK

Jiang-Shi: Stiffwalker, Jiangpo, Coinseal, Hopseng, Qinghop

Sandworm: Dunewyrm, Sirocoil, Shachong, Grainmaw, Sandswallow

Yeti: Frozape, Abomni, Shaggra, Himalok, Snowyasha

Long: Longfei, Rivendra, Jadecoil, Shenlongo, Pearlwyrm

Taotie: Taomask, Mawtile, Voramask, Greedjaw, Guimunch

Huli-Jing: Hulivix, Jingkits, Charmvulp, Silkfox, Hujin

Xiangliu: Xianghydra, Ninecoil, Manymaw, Ninelash, Jiushe, Multiheadthreat

Vermillion Bird: Vermiluxe, Zhuquet, Emberquin, Scarletail, Vermisa, Verbirdion

Azure Dragon: Azulong, Qingwyrm, Bluemander, Skyserp, Cobaltlong

White Tiger: Baihuan, Frostclaw, Whitera, Palepride, Ivoryfang

Black Turtle: Noctorto, UmbraChel, Serpshell, Northguard, Darkcarap

Dawnmarch – Baharamandal

Apsara: Padmara, Apsalia, Celindra, Nritiya, Skyraas

Rakshasa: Rakshar, Manebeast, Raksine, Shagrin, Rakshox

Naga: Nagiva, Hoodara, Coilindra, Vasukin, Serpanta

Garuda: Garudra, Windtalon, Skyadu, Garuwing, Sunraja

Gandharva: Gandharo, Songriva, Ragalor, Cloudharp, Aerialute

Vetala: Vetalisk, Gravebind, Nightpriest, Hollowji, Cadawit

Dawnmarch – Yamanokubo

Kitsune: Kitsumi, Ninevix, Foxyre, Hoshikits, Kitsel

Akaname: Grimelick, Akalick, Scuzzume, Filthling, Sudsnap

Japanese Lantern: Glowchin, Obakite, Chochibi, Lantera, Shadelamp

Kappa: Kappon, Cucumbran, Saucerling, Mizukawa, Shellume

Ittan Momen: Boltgeist, Clothwing, Yardrift, Sashwisp, Momenfly

Jorōgumo: Silkuma, Bridewidow, Joroweb, Venestra, Webbelle

Karakasa-obake: Hopparasol, Umbrette, Karakappy, Rainwight, One-Eyella

Tengu: Stormbeak, Skyfiend, Karastryx, Airtalon, Tengale

Tsuchinoko: Stubsnake, Chubbcoil, Coilobi, Gourdserp, Tsuchipop

Kamaitachi: Weaslash, Galeasel, Sickleto, Kamawea, Cutcurrent

Yuki-Onna: Yukiro, Glacelle, Frostrae, Cryoveil, Snowhime

Gulfbelt – Xochimex

La Llorona: Weepbelle, Marimourn, Canalcry, Noxmadre, Riverwail

Chupacabra: Chupito, Goatsip, Cabrasaur, Suckfang, Chupalisk

Alebrije: Alebrilla, Colorbloom, Dreambeast, Brijito, Paintpelt

La Lechuza: Lechusa, Hootbruja, Midnoct, Owlinyx, Sorcowl

Charro Negro: Nightrider, Sombrablack, Charro Noct, CoalCab, Catrinero

La Mano Peluda: Pilohand, Peluman, Furrygrasp, Hairclutch, Palmpelt, Unmannedhand

Gulfbelt – ESD

Quetzalcoatl: Quetzala, Plumecoil, Featherserp, Quetza, Skyscale

Ahuizotl: Ahuizot, Handtail, Lakegnash, Azotlusk, Drowndog

Cipactli: Cipac, Crocprime, Mawdelta, Primacroc, Jagjaw

Ocelot: Oceluna, Selvacat, Spotblade, Ocelin, Nightrosette

Gulfbelt – IE

Alux: Aluxi, Fieldling, Milpafae, Aluxobit, Plotkin

Camazotz: Camazra, Zotslash, Nightmuzzle, Camabat, Batlord

Wayob: Wayova, Dreamtwin, Shadebound, Soulstep, Nightcopy

Azureus: Azurtoad, Darti, Cobaltoad, Bluetip, Poispip

Sloth: Slumbril, Mossloth, Dozeclaw, Laggrin, Hangslow

Tapir: Tapiri, Snoutback, Dorsnork, Taproot, Palmtree

Macaw: Macara, Scarletwing, Palettail, Araflare, Macawra

Moana Crown – SRL

Crab girl: Kanikina, Pinchette, Shellina, Reefclara, Coconella

Taniwha: Taniwa, Reefward, Wakecoil, Gorgeguard, Riverjarl

Moo: Mo’ona, Reefmoke, Mooscale, O’ogeko, Lagodrake

Menehune: Menehu, Stonebuilder, Menehop, Tinykanaka, Nightmason

Nightmarcher: Nightmarch, Drumstride, Pathwraith, Torchline, Ghostfile

Adaro: Adarow, Brineblade, Cyclonfin, Stormmer, Spearfish

Orienthem – CC

Genie: Djinna, Wishra, Lampheer, Jinnari, Aetherif

Bahamut: Bahamox, Abyhmut, Deepmajest, Behemarid, Grandray

Manticore: Scoroleon, Mantikra, Quillmaw, Tailspike, Manestryx

Ifrit: Ifryx, Emberdjinn, Blazefrit, Ifraze, Cindershah

Camel: Camelor, Dunalop, Humpset, Sandromed, Caravanx

Ghoul: Ghulkin, Sandghast, Carozerk, Duneskulk, Gravegnaw

Roc: Rocara, Skymass, Taloroc, Sunlift, Stratoshrike

Nasnas: Nasni, Halflurch, Splitling, Crookstride, Onehalf

Orienthem – Hammurabia

Girtablilu: Scorpitar, Stingward, Akrabuin, Ziggaur, Biluguard

Lamassu: Lamasson, Gatebull, Wingguard, Lamashield, Ziggurhon

Oryx: Oryxon, Scimahorn, Dunestrike, Saharox, Sandbuck

Anzu: Anzugal, Galegriff, Monsoonroar, Stormzu, Skybellow

Pazuzu: Pazuwind, Zephzuzu, Sandzeph, Eastwhirl, Desertzu

Dugong: Dugona, Seagracer, Herbaflo, Seacowly, Grassgrazer

Fennec: Fennix, Dunesqueak, Earflare, Sandfen, Vulpetta

Caracal: Caraclaw, Desertlyn, Karaquik, Tuftstrike, Earblade

Orienthem – ESL

Golem: Clayward, Golim, Shamirite, Seferim, Emetron

Dolphin: Tursabelle, Wavewhistle, Seasmile, Surfari, Dolfwing

Shedim: Shedael, Shadeim, Duskkin, Murkhost, Netherim

Ziz: Zizra, Skyvast, Cloudsoar, Stratosaur, Firmament

Behemoth: Behemax, Earthbulk, Deepbrute, Landlevi, Terraox

Lilith: Lilanis, Nightael, Moonkiss, Lilistra, Umbrama

Saharene – Kemet

Anubis: Duati, Jackalakh, Anupet, Embalord, Khepwolf

Bastet: Bastelle, Catara, Purrsesh, Sekmi, Bastara

Mummy: Bandrath, Wrapscar, Tombweft, Cartonox, Linenraith

Sphinx: Sandphinx, Oracleon, Pyramayne, Riddleon, Desertis

Wadjet: Wadja, Cobragard, Nilehood, Ureye, Serishield

Bennu: Bennura, Sunplume, Nileflare, Heronix, Dawnflit

Ammit: Ammunch, Scalesnap, Crocleo, Devoura, Heartnosh

Serpopard: Serpop, Pardsnake, Neckcoil, Spotcoil, Serpopard

Uraeus: Uraelia, Browflame, Diademserp, Royalcoil, Cobreris

Spinosaur: 1, 2, 3, 4, 5

Tundria – Sila Council

Amarok: Amarokh, Frostfang, Glaciwulf, Tundrahound, Snowmara

Polar Bear: Polarus, Bergbear, Icebrawler, Glaciursus, Floefur

Akhlut: Akhlune, Brinelup, Orcawolf, Finfang, Seawyr

Keelut: Keelusk, Barehound, Slickpaw, Nightcur, Skinwolf

Ijiraq: Ijirak, Shiftling, Miragekin, Maskstag, Lostwalker

Penguin: 1, 2, 3, 4, 5, Pengwing

Caribou: 1, 2, 3, 4, 5

Walrus: 1, 2, 3, 4, 5

Uluru – RSC

Drop Bear: 1, 2, 3, 4, 5

Sea Turtle: 1, 2, 3, 4, 5

Australovenator: 1, 2, 3, 4, 5

Platypus: 1, 2, 3, 4, 5

Emu: 1, 2, 3, 4, 5

Min-Min Lights: 1, 2, 3, 4, 5

Hoop Snake: 1, 2, 3, 4, 5

Kangaroo: 1, 2, 3, 4, 5

Uluru – Yara Nations

Bunyip: Billabellow, Bunyara, Marshmurm, Bogboon, Creeklurker

Rainbow Serpent: 1, 2, 3, 4, 5

Mimi spirit: 1, 2, 3, 4, 5

Croc-Man: 1, 2, 3, 4, 5

Nargun: 1, 2, 3, 4, 5

Tiddalik: 1, 2, 3, 4, 5

Yara-ma-yha-who: 1, 2, 3, 4, 5

Verdania – Gran Columbia

Curupira: 1, 2, 3, 4, 5

Mapinguari: 1, 2, 3, 4, 5

El Silbon: 1, 2, 3, 4, 5

La Tunda: 1, 2, 3, 4, 5

Pombero: 1, 2, 3, 4, 5

Encantado: 1, 2, 3, 4, 5

Chullachaqui: 1, 2, 3, 4, 5

Verdania – SIE

Amaru: 1, 2, 3, 4, 5

Apu: 1, 2, 3, 4, 5

Supay: 1, 2, 3, 4, 5

Ukuku: 1, 2, 3, 4, 5

Urcuchillay: 1, 2, 3, 4, 5

Pishtaco: 1, 2, 3, 4, 5

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
      if(!line) continue;
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

  // Default artwork image for every base (change this later per-base if desired)
  const IMAGE_BASE = 'images/vote/';
  const IMAGE_EXT = '.webp';
  const FALLBACK_IMAGE = 'images/tengu.webp';
  const imageForBase = (base) => `${IMAGE_BASE}${slug(base)}${IMAGE_EXT}`;

  const raw = $('#source').textContent; // keep diacritics
  const parsed = parseSource(raw);

  // Group by region
  const regions = [];
  let current=null;
  for(const item of parsed){
    if(item.type==='region'){ current = { name:item.region, slug: slug(item.region), groups:[] }; regions.push(current); }
    else if(item.type==='group' && current){ current.groups.push(item); }
  }

  // Build jump menu
  for(const r of regions){
    const opt = document.createElement('option');
    opt.value = r.slug; opt.textContent = r.name;
    jump.appendChild(opt);
  }
  jump.addEventListener('change', () => {
    const id = 'region-' + jump.value;
    const target = document.getElementById(id);
    if(target){ target.scrollIntoView({behavior:'smooth', block:'start'}); }
  });

  // Selection state (persisted)
  const STATE_KEY = 'namepicker_selections_v1';
  let state = JSON.parse(localStorage.getItem(STATE_KEY) || '{}'); // key => choice

  const totalGroups = regions.reduce((n,r)=>n + r.groups.length, 0);
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
  for(const r of regions){
    const sec = document.createElement('section');
    sec.className='region'; sec.id = 'region-' + r.slug;
    const h2 = document.createElement('h2');
    h2.innerHTML = `<strong>${r.name}</strong>`;
    sec.appendChild(h2);

    for(const g of r.groups){
      const key = slug(r.name) + '__' + slug(g.base);
      META[key] = { region: r.name, base: g.base };

      const wrap = document.createElement('div');
      wrap.className='group'; wrap.dataset.key = key;

      const base = document.createElement('div');
      base.className='base';
      base.innerHTML = `<strong>${g.base}</strong>`;
      wrap.appendChild(base);

      // artwork image per original/base name
      const img = new Image();
      img.src = imageForBase(g.base);
      img.alt = `${g.base} artwork`;
      img.className = 'art';
      img.loading = 'lazy';
      img.decoding = 'async';
      img.onerror = () => { img.onerror = null; img.src = FALLBACK_IMAGE; };
      wrap.appendChild(img);

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
  }

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

  submitBtn.addEventListener('click', () => {
    rebuildForm();
    form.submit();
  });

  // Initial UI state
  rebuildForm();
  updateProgress();
  </script>
</body>
</html>
