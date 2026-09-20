# Rodinian Tsardom — Velesgrad
> *Frostdomes and Gilded Courts*

| Field | Value |
| --- | --- |
| Continent | Auronia |
| Region ID | `4` |
| Page | `?pg=rt` |
| Paint shack | `?pg=rt_paint_shack` (`rt`) |
| Map identity colour | `#60a5fa` (paint shack area colour; the country has no polygon on the Auronia map) |
| Touchstone | Slavic tsardom: kremlin, izba, banya, Perun and Veles, house spirits |

> **Naming note:** the codebase spells this two ways — `Rodanian Tsardom` in `pages/auronia.php`, `Rodinian Tsardom` in the `regions` table and `pages/paint_shack_region.php`. The DB spelling (`Rodinian`) is the one the paint shack lookup depends on.

## Design thesis
**Between root and storm.** Perun keeps the storm, Veles keeps the store. Everything is either sky-facing (bells, domes, thunder-marks) or earth-facing (stoves, cellars, hidden gold). Carved wood does the emotional work; gilt and brick do the official work.

## Palette
| Role | Hex | Use |
| --- | --- | --- |
| Frost blue | `#60a5fa` | Winter light, dome shadow, ice |
| Kremlin red | `#9e2b25` | Brick towers, banners, painted trim |
| Old copper | `#6f8f7a` | Onion-dome verdigris |
| Gilt | `#d4a017` | Icon frames, dome caps, honey |
| Birch white | `#e9e6dd` | Bark, snow, plaster, linen |
| Stove black | `#2a2320` | Forge mouths, iron, soot |
| Felt grey-brown | `#6b5c4a` | Felted wool, wet bark cloaks |

## Motifs and symbols
- **Onion dome** with a gilt or copper cap — the instant silhouette read
- Thunder-mark (six-spoke rosette) carved on beams and window boards
- House-totem over a door; a crust left on the stove for the domovoi
- Bread and salt on an embroidered cloth
- Twin shrine: an oak (sky) and a stone well (under-earth)
- Birch switch and steam (banya); the gate nail touched with two fingers
- Market scales — the thing swindlers actually fear

## Pattern and ornament
- **Nalichniki**: fretwork window boards with bears, geese, saints, and sunwheels
- Red-on-white counted embroidery bands at collar, cuff, and hem
- Khokhloma-style gold-and-black florals on wooden bowls and trays
- Repeating arch (kokoshnik) tiers stacked up a façade
- Painted arcade panels in the market; icon-style gold leaf borders

## Materials and finish
Carved and painted timber (izby), red brick, whitewash, felted wool, birch bark, iron, tin, gilded wood, glazed tile stoves. Finish is **warm and slightly worn**: paint chalked by frost, gilt rubbed at the corners, snow packed in the carving.

## Architecture and silhouette
A hill-citadel of brick towers and timber walls, onion caps, a bell tower that counts floods and fires; log houses with cold porches and warm stoves; bathhouse courtyards steaming in deep frost. Silhouette: **stacked bulbs and tent-roofs over squat log masses**.

## Dress and accessories
Long belted coats and sheepskins, felt boots, fur hats, embroidered shirts under everything. Wolf-bark-grey cloaks for the River-Spear watch, with spears used for floods as often as thieves. Accessories: birch-bark tuesok boxes, brass kettles, prayer cords, a coin kept for the well.

## Objects, signage, item flavour
Icons, felt and linen bolts, barrel hoops, horse bells, smoked fish, honey cakes, poppy buns, kvas carts, medovukha jugs, tin kettles, the *Little Stove* porridge stall. Signage is **painted panel with an icon-style figure**, gold ground optional.

## Creature design cues
Native species (`region_id = 4`): **Bee, Daeodon, Gribboar, Koschei Chain, Leshy, Rust Otter, Sobolnik, Vodyanoy**.

Forest-and-water folklore with a rust-and-iron streak. Leshy and Vodyanoy should read as *place made animate* — bark, moss, weed, river silt — rather than costumed humanoids. Koschei Chain and Rust Otter carry the iron/hidden-gold thread: chain links, keyholes, coin glints. Give the small ones fretwork-pattern markings echoing nalichniki.

## Landmarks to draw from
Kremlin Hill · Veles Market · River Wharf · Birch Quarter · Forge Street · Banya Gardens · Oak and Thunder (twin shrine) · Velesgrad Winter Pantry

## Guardrails
- Keep the folk layer bigger than the imperial layer — this is a country of villages with a citadel, not a court drama.
- Superstition should be *practical*: the domovoi crust, the two-finger gate nail, the coin in the well.
- Don't overlap Nornheim: Rodinia is carved-and-painted colour, Nornheim is scoured and monochrome.
