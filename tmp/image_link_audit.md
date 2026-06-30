# Image Link Audit

- Workspace: `D:\wamp64\www`
- Scope: scanned `assets`, `data`, `layout`, `lib`, `mockups`, `pages`, and root runtime files; skipped image binaries, SQL dumps except the item-name check, backups, and git internals.
- Scanned source/data files: 238
- Literal image references found: 182
- Broken unguarded literal image references: 42
- Root-relative image references: 4
- Dynamic image references/patterns: 11

## Broken Summary
- `active-page`: 20
- `inactive-page`: 8
- `runtime-source`: 14

## Dynamic / Logic Findings
- `high` `wrong-base-path` `runtime-source` [lib/pets.php:107] - get_pet_cosmetics() builds cosmetic item URLs as images/creatures/items/<file>, but item art lives under images/items and images/creatures/items does not exist.
- `medium` `missing-fallback` `active-page` [pages/petting2.php:14] - petting2_image_path() falls back to images/creatures/tengu_f_blue.png, which is missing; tengu_f_blue.webp exists.
- `low` `missing-fallback` `runtime-source` [pages/paint_shack_region.php:187] - When no preview pet is available, the initial preview path uses missing images/creatures/tengu_f_blue.png; the onerror handler later points to the existing .webp.
- `medium` `missing-fallback` `inactive-page` [pages/bestiary.php:326] - Bestiary fallback references images/creatures/tengu_f_blue.png, but only tengu_f_blue.webp exists.
- `medium` `missing-fallback` `inactive-page` [pages/bestiary (2).php:287] - Duplicate bestiary fallback references images/creatures/tengu_f_blue.png, but only tengu_f_blue.webp exists.
- `info` `direct-item-name-path` `active-page` [pages/inventory.php:165] - Inventory builds images/items/{item name}.png/.webp directly instead of using shop_find_item_image(). Against database.sql, 0 of 221 item names lack an exact .png/.webp match.

## Broken Literal Image References
- `runtime-source` [assets/js/garden-invaderz.js:43] `images/games/player.png` -> `images/games/player.png`
- `runtime-source` [assets/js/garden-invaderz.js:51] `images/games/bonus.png` -> `images/games/bonus.png`
- `runtime-source` [assets/js/garden-invaderz.js:53] `images/games/powerupmachinegun.png` -> `images/games/powerupmachinegun.png`
- `runtime-source` [assets/js/garden-invaderz.js:54] `images/games/powerup3shot.png` -> `images/games/powerup3shot.png`
- `runtime-source` [assets/js/garden-invaderz.js:55] `images/games/powerupshield.png` -> `images/games/powerupshield.png`
- `runtime-source` [assets/js/garden-invaderz.js:56] `images/games/powerupclone.png` -> `images/games/powerupclone.png`
- `runtime-source` [assets/js/runngunner.js:27] `images/games/runngunner_player.png` -> `images/games/runngunner_player.png`
- `runtime-source` [assets/js/runngunner.js:28] `images/games/runngunner_enemy.png` -> `images/games/runngunner_enemy.png`
- `runtime-source` [assets/js/runngunner.js:30] `images/games/power_spread.png` -> `images/games/power_spread.png`
- `runtime-source` [assets/js/runngunner.js:31] `images/games/power_machine.png` -> `images/games/power_machine.png`
- `runtime-source` [assets/js/runngunner.js:32] `images/games/power_laser.png` -> `images/games/power_laser.png`
- `runtime-source` [assets/js/runngunner.js:33] `images/games/power_flame.png` -> `images/games/power_flame.png`
- `runtime-source` [assets/js/runngunner.js:34] `images/games/power_invincible.png` -> `images/games/power_invincible.png`
- `inactive-page` [pages/bestiary (2).php:287] `images/creatures/tengu_f_blue.png` -> `images/creatures/tengu_f_blue.png`
- `inactive-page` [pages/bestiary (2).php:296] `images/creatures/tengu_f_blue.png` -> `images/creatures/tengu_f_blue.png`
- `inactive-page` [pages/bestiary (2).php:1141] `images/creatures/tengu_f_blue.png` -> `images/creatures/tengu_f_blue.png`
- `inactive-page` [pages/bestiary.php:326] `images/creatures/tengu_f_blue.png` -> `images/creatures/tengu_f_blue.png`
- `inactive-page` [pages/bestiary.php:335] `images/creatures/tengu_f_blue.png` -> `images/creatures/tengu_f_blue.png`
- `inactive-page` [pages/bestiary.php:1340] `images/creatures/tengu_f_blue.png` -> `images/creatures/tengu_f_blue.png`
- `active-page` [pages/k-adventure.php:31] `assets/images/adventure/k-adventure/k_adv1.png` -> `assets/images/adventure/k-adventure/k_adv1.png`
- `active-page` [pages/k-adventure.php:43] `assets/images/adventure/k-adventure/k_adv2.png` -> `assets/images/adventure/k-adventure/k_adv2.png`
- `active-page` [pages/k-adventure.php:55] `assets/images/adventure/k-adventure/k_adv3.png` -> `assets/images/adventure/k-adventure/k_adv3.png`
- `active-page` [pages/k-adventure.php:67] `assets/images/adventure/k-adventure/k_adv4.png` -> `assets/images/adventure/k-adventure/k_adv4.png`
- `active-page` [pages/k-adventure.php:79] `assets/images/adventure/k-adventure/k_adv5.png` -> `assets/images/adventure/k-adventure/k_adv5.png`
- `active-page` [pages/k-adventure.php:91] `assets/images/adventure/k-adventure/k_adv6.png` -> `assets/images/adventure/k-adventure/k_adv6.png`
- `active-page` [pages/k-adventure.php:103] `assets/images/adventure/k-adventure/k_adv7.png` -> `assets/images/adventure/k-adventure/k_adv7.png`
- `active-page` [pages/k-adventure.php:115] `assets/images/adventure/k-adventure/k_adv8.png` -> `assets/images/adventure/k-adventure/k_adv8.png`
- `active-page` [pages/k-adventure.php:127] `assets/images/adventure/k-adventure/k_adv9.png` -> `assets/images/adventure/k-adventure/k_adv9.png`
- `active-page` [pages/nh-adventure.php:76] `images/nh-adv1.webp` -> `images/nh-adv1.webp`
- `active-page` [pages/nh-adventure.php:88] `images/nh-adv2.webp` -> `images/nh-adv2.webp`
- `active-page` [pages/nh-adventure.php:100] `images/nh-adv3.webp` -> `images/nh-adv3.webp`
- `active-page` [pages/nh-adventure.php:113] `images/nh-adv4.webp` -> `images/nh-adv4.webp`
- `active-page` [pages/nh-adventure.php:125] `images/nh-adv5.webp` -> `images/nh-adv5.webp`
- `active-page` [pages/nh-adventure.php:137] `images/nh-adv6.webp` -> `images/nh-adv6.webp`
- `active-page` [pages/nh-adventure.php:148] `images/nh-adv7.webp` -> `images/nh-adv7.webp`
- `active-page` [pages/nh-adventure.php:159] `images/nh-adv8.webp` -> `images/nh-adv8.webp`
- `active-page` [pages/nh-adventure.php:170] `images/nh-adv9.webp` -> `images/nh-adv9.webp`
- `runtime-source` [pages/paint_shack_region.php:187] `images/creatures/tengu_f_blue.png` -> `images/creatures/tengu_f_blue.png`
- `active-page` [pages/petting2.php:14] `images/creatures/tengu_f_blue.png` -> `images/creatures/tengu_f_blue.png`
- `active-page` [pages/petting2.php:459] `images/creatures/tengu_f_blue.png` -> `images/creatures/tengu_f_blue.png`
- `inactive-page` [pages/petting_old.php:293] `images/games/ui/inventory.png` -> `images/games/ui/inventory.png`
- `inactive-page` [pages/petting_old.php:294] `images/games/ui/pet_list.png` -> `images/games/ui/pet_list.png`

## Root-Relative Image References
These exist on disk unless also listed above, but can break when the app is served from a subdirectory. `kid-puzzle.js` has candidate fallback logic; `harmonflap` uses the root path directly.
- `runtime-source` [assets/css/harmonflap.css:2] `/images/bg/flap1.png` (exists)
- `runtime-source` [assets/js/kid-puzzle.js:40] `/images/games/kid.webp` (exists)
- `active-page` [pages/harmonflap.php:22] `/images/bg/flap1.png` (exists)
- `active-page` [pages/kid-puzzle.php:11] `/images/games/kid.webp` (exists)

## Guarded Missing Candidates
These paths are missing, but the same line guards them with `is_file` or `file_exists`, so they are probably fallback candidates rather than active broken links.
- `active-page` [pages/harmonflap.php:9] `images/creatures/tengu_f_blue.png` -> `images/creatures/tengu_f_blue.png`

## Dynamic References Not Directly Verified
- `inactive-page` [pages/bestiary (2).php:284] `images/creatures/{$speciesSlug}_f_blue.webp`
- `inactive-page` [pages/bestiary (2).php:285] `images/creatures/{$speciesSlug}_f_blue.png`
- `inactive-page` [pages/bestiary (2).php:286] `images/creatures/{$speciesSlug}_f_blue.jpg`
- `inactive-page` [pages/bestiary.php:323] `images/creatures/{$speciesSlug}_f_blue.webp`
- `inactive-page` [pages/bestiary.php:324] `images/creatures/{$speciesSlug}_f_blue.png`
- `inactive-page` [pages/bestiary.php:325] `images/creatures/{$speciesSlug}_f_blue.jpg`
- `active-page` [pages/create_pet.php:255] `images/creatures/${slug}_${selectedGender}_${colorName}.webp`
- `active-page` [pages/create_pet.php:190] `images/creatures/<?= $slug ?>_f_blue.webp`
- `runtime-source` [pages/paint_shack_region.php:388] `images/creatures/${speciesSlug}_f_${colorSlug}.webp`
- `runtime-source` [pages/paint_shack_region.php:419] `images/creatures/${slug}_${variants[index]}.webp`
- `active-page` [pages/petting2.php:12] `images/creatures/{$species_slug}_f_{$color_slug}.webp`
