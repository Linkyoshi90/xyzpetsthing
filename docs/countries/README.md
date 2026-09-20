# Harmontide — Country Design References

One file per country describing **what its culture looks like**: palette, motifs, pattern, materials, architecture, dress, props, and creature-design cues, so art, items, and new locations stay consistent per region.

## How these were built
- **Grounded data** comes from the codebase: continent membership and identity colours from `pages/<continent>.php`, lore and landmarks from `lib/country_map_data.php` (plus `pages/nornheim.php` and `pages/aa.php`, which hold their lore inline), region IDs and species rosters from the `regions` and `pet_species` tables in `sql/20260726-dump.sql`, and paint-shack codes from `pages/paint_shack_region.php`.
- **Palettes, motifs, and pattern vocabularies are proposals** derived from that lore — art direction to build on, not extracted from existing assets. The only colour taken straight from code is each country's map identity colour, which is labelled as such.

## By continent

### Auronia
| Country | Capital | Region | Doc |
| --- | --- | --- | --- |
| Aegia Aeterna | Heliadora | `1` | [aegia-aeterna.md](aegia-aeterna.md) |
| Nornheim | Skeldgard | `2` | [nornheim.md](nornheim.md) |
| Rheinland | Rheingard | `3` | [rheinland.md](rheinland.md) |
| ↳ Frankenondermeer | — | (shares `3`) | [frankenondermeer.md](frankenondermeer.md) |
| Rodinian Tsardom | Velesgrad | `4` | [rodinian-tsardom.md](rodinian-tsardom.md) |
| Bretonreach | Avalore | `26` | [bretonreach.md](bretonreach.md) |

### Verdania
| Country | Capital | Region | Doc |
| --- | --- | --- | --- |
| Gran Columbia | Solvine | `19` | [gran-columbia.md](gran-columbia.md) |
| Sapa Inti Empire | Intirumi | `20` | [sapa-inti-empire.md](sapa-inti-empire.md) |
| ↳ Aeonstep Plateau (wild) | — | `27` | [aeonstep-plateau.md](aeonstep-plateau.md) |

### Gulfbelt
| Country | Capital | Region | Doc |
| --- | --- | --- | --- |
| Xochimex | Xochival | `8` | [xochimex.md](xochimex.md) |
| Eagle Serpent Dominion | Coatlxochi | `9` | [eagle-serpent-dominion.md](eagle-serpent-dominion.md) |
| Itzam Empire | Itzankaan | `10` | [itzam-empire.md](itzam-empire.md) |

### Orienthem
| Country | Capital | Region | Doc |
| --- | --- | --- | --- |
| Crescent Caliphate | Ansurah | `12` | [crescent-caliphate.md](crescent-caliphate.md) |
| Hammurabia | Ziggurab | `13` | [hammurabia.md](hammurabia.md) |
| Eretz-Shalem League | Shalemdor | `14` | [eretz-shalem-league.md](eretz-shalem-league.md) |

### Dawnmarch
| Country | Capital | Region | Doc |
| --- | --- | --- | --- |
| Lotus-Dragon Kingdom | Shenhedu | `5` | [lotus-dragon-kingdom.md](lotus-dragon-kingdom.md) |
| Baharamandal | Padmanagara | `6` | [baharamandal.md](baharamandal.md) |
| Yamanokubo | Amatera | `7` | [yamanokubo.md](yamanokubo.md) |

### Borealia
| Country | Capital | Region | Doc |
| --- | --- | --- | --- |
| United Free Republic of Borealia | Meridian Arc | `22` | [urb.md](urb.md) |
| ↳ Stillwater Hollow | — | `29` | [stillwater-hollow.md](stillwater-hollow.md) |
| Sovereign Tribes of the Ancestral Plains | Turtlestar | `25` | [stap.md](stap.md) |

### Moana Crown
| Country | Capital | Region | Doc |
| --- | --- | --- | --- |
| Spice Route League | Navakai | `11` | [spice-route-league.md](spice-route-league.md) |
| ↳ Pelagora | — | `28` | [pelagora.md](pelagora.md) |

### Saharene
| Country | Capital | Region | Doc |
| --- | --- | --- | --- |
| Kemet | Ankhmeru | `15` | [kemet.md](kemet.md) |

### Uluru
| Country | Capital | Region | Doc |
| --- | --- | --- | --- |
| Red Sun Commonwealth | Redwind | `17` | [red-sun-commonwealth.md](red-sun-commonwealth.md) |
| Yara Nations | Warraluma | `18` | [yara-nations.md](yara-nations.md) |

### Tundria
| Country | Capital | Region | Doc |
| --- | --- | --- | --- |
| Sila Council | Qilaktuk | `16` | [sila-council.md](sila-council.md) |

## Cross-cutting design rules

**Shared world grammar.** Several devices recur across countries and should stay consistent wherever they appear:
- *Vow-stones that hairline-crack on a broken oath* — Aegia Aeterna and Nornheim.
- *The blessing cord tied to a purchase* — Lotus-Dragon Kingdom, Yamanokubo, Eagle Serpent Dominion, Itzam Empire, Sapa Inti Empire.
- *"Even a shadow shows a receipt"* — nearly every country has an audited criminal network. Their visual tell should always be a **ledger, jar, or posted list**, never a skull or a gang colour.
- *The 5–10× tourist markup and the local-rate phrase* — a recurring joke; the "local measure" is a fine hook for shop UI.

**Living-culture guardrails.** The Yara Nations, Sovereign Tribes of the Ancestral Plains, Sila Council, and Spice Route League are modelled on living Indigenous cultures; the Crescent Caliphate, Eretz-Shalem League, Kemet, and Itzam Empire involve real religious or script traditions. In all of these: **invent the motif and script vocabularies**, keep sacred objects and ceremony out of tradeable items and costume slots, and depict the communities as contemporary and self-governing. Each doc carries its specific note.

## Known inconsistencies found while writing these
- **Eagle Serpent Dominion** is listed under Gulfbelt in `pages/gulfbelt.php`, but `lib/country_map_data.php` sets its back-link to Verdania.
- **Rodinian / Rodanian Tsardom** is spelled two ways; `pages/auronia.php` says *Rodanian*, while the `regions` table and `pages/paint_shack_region.php` say *Rodinian* — the paint-shack lookup depends on the DB spelling.
- **Nornheim** and **Aegia Aeterna** are the only countries whose maps and lore live inline in their page files rather than in `lib/country_map_data.php`, so they have no entry in the shared config.
- The `regions` table contains a junk row, `region_id 21` = `AAAAAAAAAAAAAAAAAAAA`.
- **Sila Council** has `Penguin` in an Arctic roster.
