# Regional Gyms and Stadiums

This document analyzes the Pokemon gym battle formula and translates the useful parts into an implementation plan for Harmontide's regional PvE and PvP battle systems.

The goal is not to copy Pokemon's exact gyms, badges, characters, or aesthetics. The goal is to understand why those arena milestones work: they bind combat, town identity, player preparation, progression, local reputation, and a memorable leader into one compact ritual. Harmontide already has strong regional identity and a playable battle prototype. Regional gyms/stadiums should be the bridge between those two systems.

## Source Notes

External research used:

- [Gym Leader, Bulbapedia](https://bulbapedia.bulbagarden.net/wiki/Gym_Leader): gym leaders test trainers, award badges, oversee themed gyms, often specialize in types, and often have jobs or public roles outside the gym.
- [Gym, Bulbapedia](https://bulbapedia.bulbagarden.net/wiki/Gym): gyms commonly include tests, puzzles, special match rules, gym trainers, and leader battles.
- [Badge, Bulbapedia](https://bulbapedia.bulbagarden.net/wiki/Badge): badges act as proof of progress, league qualification, obedience/progression gates, and collectible identity.
- [Pokemon Sword and Shield official site, Galar Gym Challenge](https://swordshield.pokemon.com/fr-ca/people-galar-region/rivals/): Galar reframes gyms as public stadium entertainment with uniforms, endorsements, sponsors, broadcasts, and a Champion Cup.
- [Pokemon Scarlet and Violet official site, Treasure Hunt](https://scarletviolet.pokemon.com/en-au/news/treasure_hunt/): Paldea uses Gym Tests before leader battles, with each test revealing local town customs and the leader's personality.
- [Trial Captain, Bulbapedia](https://bulbapedia.bulbagarden.net/wiki/Trial_Captain): Alola replaces gyms with island trials, captains, totem battles, and kahunas, proving the formula can be regionalized beyond buildings.

Project systems checked:

- `pages/battle_minigame.php`
- `assets/js/battle-minigame.js`
- `assets/css/battle-minigame.css`
- `lib/random_events.php`
- `data/random_events.json`
- `pages/games.php`
- `lib/country_map_data.php`
- `lib/country_interactive_map.php`
- `lib/city_locations.php`
- `data-readonly/city-names.txt`
- `sql/random_battle_encounters.sql`
- `database.sql`

## Executive Summary

Pokemon gyms are effective because they are not merely bosses. They are local institutions.

At their best, they do five jobs at once:

- They give the player a clear regional milestone.
- They ask the player to prove mastery through a themed pre-challenge.
- They deliver a curated battle against a memorable leader.
- They make the town feel socially aware of the player's goal.
- They award permanent proof that changes the player's status in the world.

Harmontide already has several pieces that can support this:

- Region and city identities are rich and already organized into interactive map pages.
- Player creatures have stats, levels, HP, elements, colors, and images.
- The battle prototype supports player teams, trainer teams, wild encounters, moves, elemental effectiveness, switching, healing items, victory rewards, combat logs, and animated battle presentation.
- Random regional events can already launch wild battles.
- Region-specific species and shops already suggest natural local strategies.

What is missing is the gym layer itself:

- Fixed regional venues.
- Persistent badges or venue progress.
- Fixed leader selection instead of random trainer selection.
- Staged gym tests before the leader.
- Town dialogue that builds the leader's reputation.
- Special rulesets, battle formats, rematches, leader identity, arena theming, and PvP-ready battle authority.

The next phase should treat gyms/stadiums as a new progression layer over the battle engine, not as one-off pages. Build the first venue as an end-to-end vertical slice, then generalize.

## What Pokemon Gyms Have In Common

### 1. A Sanctioned Local Milestone

A gym is usually the official battle institution of a town or city. The player understands it as "the reason I came here" even when the town also has shops, side characters, story events, and routes.

The milestone is legible before the fight:

- The building or stadium is visible.
- NPCs mention the leader.
- A sign, receptionist, guide, rival, or local rumor points the player toward it.
- The leader is framed as a public figure, not just a random enemy.

For Harmontide, the equivalent should be a regional "battle venue" that may be called a gym, stadium, proving hall, dojo, arena, trial ground, lodge, theater, council ring, or circuit depending on the nation. The important part is not the noun. The important part is that each venue is recognized by locals as the place where challengers are tested.

### 2. A Themed Identity

Most gyms use a type theme. Even when the theme is not perfectly pure, the leader's battle strategy, the room, the puzzle, the trainer dialogue, and the reward all point toward a coherent identity.

Common theme layers:

- Combat type or element.
- Local ecology.
- A leader's profession.
- A town's industry or culture.
- A puzzle that teaches how to think about the battle.
- A signature ace creature or transformation.
- A badge shape and name that visually summarizes the theme.

This is why a gym is more memorable than a generic boss fight. The player is not only fighting a strong team. They are entering a small cultural thesis: water as pools and sailors, electric as machinery, grass as gardens, ghost as theater or ruins, ice as climate, fighting as discipline, normal as everyday life, and so on.

Harmontide should combine:

- Region identity from `lib/country_map_data.php`.
- City identity from `data-readonly/city-names.txt`.
- Creature elements from `elements` and `species_elements`.
- Local species from `pet_species.region_id`.
- Local items and shops.
- Leader personality and public role.

### 3. A Pre-Battle Test

The gym leader is rarely available immediately. The player usually has to pass through some combination of:

- A puzzle.
- A small gauntlet of trainers.
- A navigation challenge.
- A quiz.
- A local errand.
- A mini-game.
- A themed trial.
- A requirement gate.

The best tests are not random friction. They prepare the player for the leader.

Examples of what the test teaches:

- Use type matchups.
- Preserve HP across multiple fights.
- Read a clue.
- Notice local customs.
- Move through the town carefully.
- Understand a leader's values.
- Think about speed, status, switching, terrain, or team order.

This is especially important for Harmontide because the current battle system has mechanics that players may not yet understand. A venue test can introduce them safely before the leader fight.

### 4. A Curated Leader Battle

The leader battle has a structure:

- A formal intro.
- A limited roster chosen around a strategy.
- A clear difficulty spike.
- A final ace creature.
- A signature move, tactic, or mechanic.
- A defeat line that reveals character.
- A reward that marks the player's progress.

The player usually has more freedom than the leader. In many Pokemon games, players can use their whole party, switch, prepare items, and counter-team. The leader is stronger through curation, theme, arena presentation, and a signature strategy rather than pure rules parity.

For Harmontide, this means the story-mode gym leader does not have to be "fair" in the same way a PvP match must be fair. PvE leaders can be teachable, dramatic, and readable. PvP versions of those venues can later use stricter mirrored rules.

### 5. A Permanent Reward

The reward is not only currency.

Pokemon gyms commonly use badges as:

- Proof that the player overcame the local institution.
- A key toward league qualification.
- A way to unlock obedience or level thresholds.
- A visual collection.
- A social status marker.
- A prerequisite for later challenges.
- A memory object tied to the team used to win.

Harmontide needs an equivalent badge layer. It can still award Dosh, items, move access, map access, or cosmetics, but the badge itself should be permanent and visible. A player should be able to look at a profile and see a journey.

### 6. The Town Talks About The Leader

This is one of the most important patterns.

Gym leaders feel larger than their limited screen time because townspeople talk about them before and after the battle. The leader may be:

- The local expert.
- A public servant.
- A celebrity.
- A teacher.
- A shop owner.
- A mayor-like figure.
- A prodigy.
- A weird recluse.
- A beloved regular.
- A controversial authority.

The town acts as the leader's amplifier. NPCs set expectations, provide hints, give gossip, complain, admire, sell counters, and react to victory. This makes the gym part of the town rather than a battle menu.

For Harmontide, every gym/stadium should have at least five voices around it:

- A fan who admires the leader.
- A practical local who offers counterplay advice.
- A critic who reveals a flaw or tension.
- A staff member who explains the challenge.
- A post-victory NPC who acknowledges the player's changed status.

## Regional Design Patterns Across Pokemon

### Kanto and Johto: Simple Type Landmarks

Early gyms are direct and readable. A town has an official gym, the gym has a type, and the leader is a local specialist. The design language is simple: rocks, water, electricity, plants, poison traps, psychic rooms, fire riddles, earth authority.

What works:

- New players understand the loop immediately.
- Each gym is a clean lesson in type preparation.
- Towns feel like itinerary stops.
- Badges create a strong collection arc.

Implementation takeaway:

Harmontide's first gym should be simple. A single region, a visible venue on the map, one challenge, one leader, one badge. Do not begin with a full tournament system.

### Hoenn and Sinnoh: Geography, Movement, and Environmental Puzzle Design

These games lean more into terrain and traversal. Gyms often behave like miniature environmental dungeons: currents, ice floors, rotating platforms, elevators, darkness, strength, or navigation logic.

What works:

- The puzzle teaches spatial thinking before combat.
- The gym echoes regional geography.
- A leader's type often feels tied to climate or landscape.

Implementation takeaway:

Harmontide can use region map identity as the gym's first design input. Sila's gym should feel like ice, sky, aurora, and survival. Spice Route League should feel like tide, docks, cargo, route planning, and wind. Kemet should feel like weight, law, ritual, and stone.

### Unova: Leaders As Civic Professionals

Unova strongly ties leaders to jobs and public identity: museum work, restaurants, modeling, art, planes, music, schools, and public facilities. The gym is often not isolated from the city. It is embedded in civic life.

What works:

- Leaders feel like people with lives.
- The town introduces them naturally.
- The gym's puzzle can be based on their profession.
- The leader can matter outside battle.

Implementation takeaway:

Harmontide leaders should have jobs. A leader should run a tea house, guard a river gate, host a theater, captain a ferry, manage a ranger lodge, judge contracts, teach at a school, or maintain a shrine. The gym should feel like a public role that also happens to produce battles.

### Kalos: Style, Spectacle, and Personal Aesthetics

Kalos gyms often feel like curated spaces: galleries, fashion, climbing walls, bright technology, and performance. The leader's personality and city aesthetics are foregrounded.

What works:

- The arena becomes a memory image.
- The leader has a strong visual hook.
- The gym can feel like an exhibit or performance.

Implementation takeaway:

Harmontide should not make every venue an industrial battle room. Some should be theatrical, sacred, market-like, scholarly, or ceremonial. The battle CSS should eventually support venue skins.

### Alola: Regionalized Replacement, Not Absence

Alola does not use the classic gym structure, but it still keeps the core function:

- Local mentors guide the player.
- Trial sites test the player.
- Totem battles act as boss encounters.
- Island kahunas provide major authority battles.
- The system is explicitly rooted in local culture.

This proves a key design lesson: the gym formula can be changed as long as the role remains clear. The player still gets regional milestones, tests, boss fights, and proof of completion.

Implementation takeaway:

Harmontide should not force every region into a standardized gym building. Some nations should use trials, councils, public matches, contracts, tournaments, festivals, rite paths, or arena circuits. The shared backend can be "battle venues"; the frontend name can vary by region.

### Galar: Stadiums, Sports Culture, Sponsors, Broadcasts

Galar reframes gyms as a sports league. The challenger wears a uniform, leaders battle in stadiums, spectators attend, corporations sponsor venues, and the Champion Cup turns gym progress into a public tournament.

What works:

- The player feels watched.
- Battles have public stakes.
- Leaders become celebrities.
- The gym challenge has structure beyond one town.
- Stadiums naturally support rematches and PvP formats.

Implementation takeaway:

Harmontide's stadium-style regions should use crowd presentation, banners, announcers, leader rankings, league divisions, and seasonal cups. This is especially useful for PvP. A "minor league" venue can be a starter PvP bracket, while a "major league" venue can require badges or rating.

### Paldea: Open-World Order and Gym Tests

Paldea's gyms can be challenged more freely, but each uses a Gym Test first. The tests are explicitly positioned as ways to learn about town customs, local features, and the leader.

What works:

- The player can choose route order.
- The test makes the town matter.
- The leader is introduced through behavior before battle.
- Gym identity is less about room puzzles and more about local social tasks.

Implementation takeaway:

Harmontide can let players challenge venues in flexible order, but must handle scaling. The test should be local and story-rich. A market gym can ask the player to find ingredients, a court gym can ask them to read testimony, a harbor gym can ask them to route cargo, and a theater gym can ask them to observe performances.

## Player Engagement Analysis

### Predictable Goal, Local Surprise

Players like knowing the broad loop: enter town, prepare, pass test, fight leader, earn proof. The surprise comes from the local wrapper. This balance is powerful. Too much novelty becomes confusing. Too much repetition becomes dull.

For Harmontide:

- Keep the venue loop consistent.
- Vary the local test, leader, rules, and reward.
- Always show the player what step they are on.

### Preparation and Counterplay

Gyms encourage players to inspect their team and prepare. Type themes make strategy legible without requiring deep math.

For Harmontide:

- Show the venue's likely element(s) before battle.
- Let local shops sell useful counters or healing.
- Let nearby wild encounters include creatures that can help.
- Add scouting text from townspeople.
- Avoid hidden "gotcha" strategies in story-mode gyms.

### Skill Check Without Permanent Punishment

A gym is a checkpoint. If the player loses, they know what to improve:

- Level up.
- Heal.
- Switch team order.
- Bring a counter element.
- Use items.
- Learn the leader's ace.

For Harmontide:

- Losing should not delete progress through the whole venue unless that is a special hard mode.
- A loss should produce advice.
- The leader should hint at why the player lost.
- Rematches should be immediate or clearly available.

### The Leader As A Social Anchor

A memorable leader makes the fight feel personal. Their profession and reputation make them stick in memory even if the mechanical fight is short.

For Harmontide:

- Each leader needs a public title.
- Each leader needs a local role outside battle.
- Each leader needs town gossip before and after the fight.
- Each leader needs a battle philosophy.
- Each leader needs an ace creature that expresses that philosophy.

### The Badge As Memory

The badge is small but psychologically important. It is proof that a specific player team overcame a specific local ritual.

For Harmontide:

- Store the winning team snapshot when possible.
- Display badges on profile or region map.
- Let NPCs check badges.
- Let badges unlock rematches, shops, map areas, move recipes, or PvP brackets.

### Spectacle Escalation

Gyms build up:

1. Town rumors.
2. Venue exterior.
3. Reception or guide.
4. Gym trainers or puzzle.
5. Leader entrance.
6. Battle.
7. Ace moment.
8. Victory line.
9. Badge ceremony.
10. Town reaction.

The player should feel a rise in stakes.

For Harmontide:

- The current battle intro overlay is a good start.
- The battle stage should eventually accept venue skins and leader assets.
- The combat log should support leader voice lines at specific moments.
- The reward screen should linger enough for ceremony.

## Existing Harmontide Gameplay Similarities

### Already Implemented Or Close

#### Regional Identity

The strongest foundation is the world structure:

- `lib/country_map_data.php` defines rich region/city pages, lore sections, landmarks, etiquette, food, order, calendars, and interactive map areas.
- `data-readonly/city-names.txt` maps every nation to a capital or key city concept.
- `lib/country_interactive_map.php` renders clickable regional maps with highlighted areas, tooltips, legend buttons, back links, zooming, and lore panels.
- `lib/city_locations.php` maps pages to nations/cities and parent country maps.

This already supports the "town talks about the gym" requirement. The missing step is adding gym/stadium areas and dialogue hooks.

#### Battle Presentation

`pages/battle_minigame.php`, `assets/js/battle-minigame.js`, and `assets/css/battle-minigame.css` already provide a strong prototype:

- Trainer intro overlay.
- Opposing and player creature display.
- HP bars.
- Element chips.
- Combat feed.
- Command menu.
- Fight, Item, Creatures, and Flee choices.
- Animated attacks, damage flashes, healing numbers, summoning, and fainting.
- Victory and defeat exits.

This is close to the Pokemon-style battle screen feeling. It needs venue-specific control, persistence, rulesets, and server authority before becoming a gym/PvP foundation.

#### Player Teams

`battle_load_team_for_user()` pulls active player creatures from `pet_instances` and joins `pet_species` plus colors. That gives gyms a ready player-side party.

Existing creature data includes:

- Level.
- Experience.
- HP.
- Attack.
- Defense.
- Initiative.
- Element associations.
- Species image and color.
- Active/inactive state.

This is enough for a first PvE gym.

#### Trainer Teams

The current trainer flow uses:

- `trainers`
- `trainer_roster`
- trainer-owned `pet_instances`

`battle_load_random_trainer()` currently picks a trainer by `ORDER BY RAND()`. `battle_load_trainer_team()` then loads that trainer's roster.

This is close, but gyms need fixed trainer lookup by venue and role. The current random trainer logic should become a fallback, not the main flow.

#### Wild Regional Battles

Wild battles already exist:

- `random_encounters` stores region, species, time windows, and encounter chance.
- `sql/random_battle_encounters.sql` seeds many region-specific wild battles.
- `lib/random_events.php` checks whether the current region has active encounters.
- `data/random_events.json` includes wild battle events that create an action link to `battle_minigame`.
- `battle_minigame.php` supports `?battle=wild&region_id=...`.

This can be reused for pre-gym scouting, local route practice, and gym tests that require a wild challenge.

#### Elements and Moves

The schema already includes:

- `elements`
- `species_elements`
- `element_calc`
- `moves`

The current battle page loads move data and elemental effectiveness. `assets/js/battle-minigame.js` calculates damage using move power, target defense, and target element multipliers.

The data already includes fields for category, accuracy, PP, priority, target mode, contact, crit bonus, effect key, effect chance, and multi-hit counts. Many of those are not yet enforced in the client battle flow, but the schema is forward-looking.

#### Items

`battle_load_battle_items()` finds inventory items that look like berries, potions, heals, restores, or HP items and exposes them to the battle menu. `battle_consume_item()` decrements inventory server-side.

This is useful for story gyms, but some leader rulesets should limit items.

#### Rewards

`battle_award_victory()` can award currency after a battle, with session token checks to prevent repeated reward collection.

This can become the seed for gym rewards, but badges, unlocks, and progress records need their own persistence.

### Partially Implemented

#### Local Battle Entrypoints

The `games` page exposes "Trainer Battle" as a random encounter. Random events expose wild battles from exploration.

What is missing:

- A venue page.
- A region map area that links to that venue.
- A fixed leader challenge link.
- A progress-aware flow that knows whether the player is in test, leader, rematch, or completed state.

#### Regional Pages As Setup

Regional pages already contain local lore, etiquette, shops, and adventure pages. This can support leader buildup, but no gym leader is currently integrated into a town's identity.

What is missing:

- Gym/stadium area definitions in map configs.
- NPC comments about the leader.
- Venue-specific shop hints.
- Post-victory town state.

#### Battle Rules

The current battle engine has enough to run a basic story duel. It does not yet have formal rulesets.

Needed rules:

- Team size limit.
- Level cap or scaling.
- Item allowance.
- Switch allowance.
- Flee allowance.
- Required region creatures.
- Required badge count.
- Single battle, double battle, gauntlet, or special hazard.
- PvE story, PvE rematch, PvP casual, PvP ranked.

#### Move Effects

The database has effect fields, but the current JavaScript primarily uses power, element, category/contact for animation, speed order, and defense. Status effects, accuracy misses, PP, priority, crits, multi-hit, targeting, and most effects are not yet functional.

This matters for gym design. Early gyms should avoid relying on mechanics that are only in the schema. Later gyms can be designed as those mechanics land.

### Not Yet Implemented

#### Persistent Badges

There is no clear `player_badges`, `battle_badges`, or `venue_progress` table. Gyms need persistent proof of completion.

A gym completion record should store:

- User id.
- Venue id.
- Badge id.
- Completion timestamp.
- Difficulty tier.
- Winning team snapshot or pet ids.
- Number of attempts.
- Optional battle session id.

#### Fixed Venue and Leader Metadata

Current trainers do not appear tied to a region, venue, challenge tier, role, or ruleset. Gym leaders need metadata beyond `class_name`, `trainer_name`, `encounter_line`, `defeat_line`, and `defeat_currency`.

Needed metadata:

- Region id.
- Venue slug.
- Leader title.
- Public role.
- Specialty elements.
- Badge.
- Stage skin.
- Intro/defeat/rematch lines.
- Town gossip hooks.
- Difficulty tiers.
- Roster variants.
- Unlocks.

#### Gym Tests

There is no generic challenge-step framework. Adventure pages demonstrate branching content, and mini-games exist, but gym tests need a reusable way to track state and gate the leader battle.

#### Server-Authoritative Battle Resolution

The current battle flow sends a complete payload to the client. Damage and turn resolution happen in JavaScript. That is fine for a prototype PvE minigame, but not for serious PvP or valuable PvE rewards.

Before PvP becomes meaningful, battle resolution should move server-side or be validated server-side:

- Create battle session.
- Store teams and rules.
- Submit command.
- Server computes turn order, hit, damage, status, fainting, rewards.
- Client renders returned events.

#### PvP Matchmaking and Rule Enforcement

There is no PvP pairing, challenge invite, ladder, asynchronous defense, replay, or anti-cheat system yet.

Gym/stadium rules can become PvP formats later, but only after server authority and persistent sessions exist.

## Similarity Matrix

| Pokemon Gym Pattern | Exists In Harmontide? | Current Equivalent | Gap |
| --- | --- | --- | --- |
| Regional town identity | Strong | Country map/lore pages | Needs leader/venue hooks |
| Gym/stadium visible on town map | Partial | Interactive map areas | Add venue areas and links |
| Town NPCs talk about leader | Partial | Lore text, speech data, adventures | Add dialogue states and post-victory lines |
| Type/element specialty | Strong foundation | `elements`, `species_elements`, `element_calc` | Need venue specialty metadata |
| Gym trainers before leader | Partial | `trainers`, `trainer_roster` | Need fixed venue trainers and sequence |
| Gym puzzle/test | Partial | Adventures and mini-games | Need reusable step gating |
| Fixed leader battle | Partial | Trainer battle can load trainer teams | Needs `trainer_id`/`venue_id` selection |
| Badge reward | Missing | Currency only | Add badges/progress tables |
| League progression | Missing | None | Add badge gates and stadium cups |
| Rematches | Missing | Random trainer battle only | Add completed-state rematch tiers |
| Battle presentation | Strong prototype | Battle minigame CSS/JS | Needs venue skins and leader moments |
| PvP-ready combat | Missing | Client-side PvE flow | Server-side session engine required |

## Harmontide Gym/Stadium Design Principles

### Principle 1: Use "Battle Venue" As The Backend Concept

The database and code should call the shared system a venue. The player-facing name can vary.

Examples:

- Gym.
- Stadium.
- Dojo.
- Proving Hall.
- Trial Path.
- Council Ring.
- Shieldhall.
- Court Arena.
- Festival Stage.
- Harbor Circuit.
- Shrine Challenge.

This lets Alola-style regions feel culturally specific while sharing implementation.

### Principle 2: Every Venue Teaches One Main Lesson

Each venue should have one primary combat lesson.

Examples:

- Element advantage.
- Switching.
- Speed order.
- Sustain across a gauntlet.
- Defensive walls.
- Status effects.
- Multi-hit pressure.
- Weather/arena hazard.
- Item restriction.
- Team size limitation.
- Reading the opponent's strategy.

Avoid teaching too many mechanics in one gym. If a venue has a puzzle, the puzzle should support the same lesson.

### Principle 3: The Leader Is A Local Person First

The leader should not be defined only by element.

Required leader profile fields:

- Name.
- Title.
- Region and city.
- Public job.
- Reputation.
- Specialty element(s).
- Ace creature.
- Battle philosophy.
- Favorite local landmark.
- What locals admire.
- What locals complain about.
- What the leader says before battle.
- What the leader says after defeat.
- What changes in town after victory.

### Principle 4: The Town Builds The Fight Before The UI Does

Before the player clicks "Challenge Leader," they should have already heard:

- What the leader specializes in.
- What the challenge is known for.
- What local habit or custom matters.
- What kind of creature might help.
- What the leader is like as a person.

This can happen through:

- Region lore panel.
- Map area descriptions.
- Shop text.
- Adventure choices.
- Random events.
- Header pet speech.
- A venue receptionist.
- A "battle guide" NPC.

### Principle 5: Story PvE And Competitive PvP Are Related, But Not The Same

Story gyms can be dramatic and asymmetrical. PvP must be fair and enforceable.

Use the same venue identity for both:

- PvE: player challenges a leader's curated roster.
- PvP casual: friends fight under that venue's rules.
- PvP ranked: players enter a seasonal bracket with enforced team limits.
- PvP asynchronous: a player creates a "gym defense team" for others to challenge.

But do not ship ranked PvP until the battle engine is server-authoritative.

## Recommended User Flow

### 1. Arrival In Town

The venue should be present on the region map. Its map area description should not only say "Battle here." It should explain why locals care.

Example:

> "The Meridian Arc Fieldhouse hosts civic league battles under bright signs and louder opinions. Locals say the leader knows every ordinary trick in the book."

### 2. Local Buildup

Before entering, local content should foreshadow the leader:

- A shop sells a counter item.
- A random event mentions challengers training.
- A local NPC complains about the venue crowd.
- A fan praises the leader's ace.
- A city lore section names the venue as a landmark.

### 3. Registration

The venue page should show:

- Venue name.
- Badge.
- Leader portrait.
- Specialty element(s).
- Recommended level or badge count.
- Current player status.
- Challenge steps.
- Ruleset.
- Reward preview.

This page should not immediately start the battle unless it is a wild encounter. A gym has ceremony.

### 4. Gym Test

The test should take 2 to 6 minutes for early venues and 5 to 12 minutes for later venues.

Possible test forms:

- Quiz with local lore clues.
- Short adventure path.
- Mini trainer gauntlet.
- Find-and-return errand.
- Shop/market clue puzzle.
- Map navigation task.
- Small mini-game.
- Wild creature challenge.
- "Beat three apprentices without using items."
- "Choose the correct counter team order."

The test should be tracked in persistent progress.

### 5. Leader Challenge

The leader battle should start with a venue-specific version of `battle_minigame`.

Needed additions:

- Pass `venue_id` or `trainer_id` into the battle page.
- Load fixed leader team.
- Load venue rules.
- Load venue background/stage class.
- Load badge reward.
- Add leader mid-battle lines.
- Add ace intro line.
- On victory, award badge once.

### 6. Badge Ceremony

On victory, do not immediately redirect. Show:

- Badge name.
- Leader line.
- Reward list.
- Unlocks.
- Winning team mention.
- Return to town button.
- Rematch unavailable/available state.

### 7. Town Aftermath

After completion:

- The map area should show badge earned.
- NPCs should acknowledge victory.
- The leader may offer rematches.
- A local shop may unlock new stock.
- A PvP format may unlock.
- Random events can reference the player's status.

## Town Dialogue Model

Each venue should have dialogue in at least five categories.

### Before Challenge

Purpose: build reputation and provide tactical hints.

Example voices:

- Fan: "Everyone in town knows Leader Mara. She makes ordinary creatures look impossible to predict."
- Shopkeeper: "If you are going to the Fieldhouse, bring something that can survive quick openers."
- Critic: "Mara smiles for the posters, but she hates sloppy switches. She will punish the first lazy move."
- Apprentice: "The test is about reading the scoreboard, not staring at it."
- Elder: "That venue used to be a train shed. Now it is where the city proves it can cheer without breaking chairs."

### During Challenge

Purpose: make the venue feel staffed and reactive.

Example voices:

- Receptionist: "Your party is registered. Three rounds, limited items, leader at the end."
- Announcer: "Challenger on deck. Meridian Arc, keep your hands clear of the rail."
- Apprentice after loss: "You saw the trick. Now show me you understood it."
- Staff hint: "Leader Mara's ace waits for tired teams."

### Before Leader

Purpose: formalize the fight.

Example voices:

- Guide: "This is the last door. If you need to switch your lead creature, do it now."
- Leader: "A city is built from ordinary choices. So is a winning team."

### After Victory

Purpose: make the town react to status change.

Example voices:

- Fan: "You beat Mara? I saw the replay board. That switch was clean."
- Shopkeeper: "Badge discount, then. Do not spend it all on victory snacks."
- Critic: "Fine. Maybe the posters were not exaggerating this time."
- Leader: "Come back for the night league when your team wants a harsher lesson."

### After Loss

Purpose: reduce frustration and guide improvement.

Example voices:

- Leader: "You had the answer two turns before you used it."
- Receptionist: "Want to retry the leader, or run the apprentice drill again?"
- Battle guide: "Your lead was too slow for this ruleset. Try opening with initiative."

## Proposed Data Model

This can start as JSON for a prototype, but SQL will be needed for persistence, rewards, and PvP.

### `battle_venues`

Core venue identity.

Suggested fields:

- `venue_id`
- `venue_slug`
- `region_id`
- `page_slug`
- `venue_name`
- `venue_kind`
- `city_name`
- `leader_trainer_id`
- `badge_id`
- `ruleset_id`
- `specialty_element_id`
- `secondary_element_id`
- `recommended_level`
- `required_badge_count`
- `map_label`
- `short_description`
- `long_description`
- `stage_theme_key`
- `is_active`

### `battle_badges`

Permanent proof and display.

Suggested fields:

- `badge_id`
- `badge_slug`
- `badge_name`
- `region_id`
- `venue_id`
- `element_id`
- `icon_path`
- `description`
- `unlock_summary`

### `user_battle_badges`

Player completion.

Suggested fields:

- `user_id`
- `badge_id`
- `venue_id`
- `earned_at`
- `difficulty`
- `attempt_count`
- `winning_team_json`
- `battle_session_id`

### `battle_venue_progress`

Tracks test progress before leader.

Suggested fields:

- `user_id`
- `venue_id`
- `state`
- `current_step`
- `completed_steps_json`
- `started_at`
- `updated_at`
- `leader_unlocked_at`

### `battle_venue_steps`

Reusable gym tests.

Suggested fields:

- `step_id`
- `venue_id`
- `step_order`
- `step_type`
- `title`
- `description`
- `config_json`
- `reward_json`
- `required_to_unlock_leader`

Step types can include:

- `dialogue`
- `quiz`
- `adventure`
- `trainer_battle`
- `wild_battle`
- `minigame`
- `item_check`
- `team_check`
- `route_choice`

### `battle_rulesets`

Shared by PvE and PvP.

Suggested fields:

- `ruleset_id`
- `ruleset_slug`
- `display_name`
- `team_size`
- `level_cap`
- `scale_to_level`
- `allow_items`
- `allow_switching`
- `allow_flee`
- `battle_format`
- `allowed_region_id`
- `required_element_id`
- `max_duplicate_species`
- `arena_hazard_key`
- `config_json`

### `battle_venue_dialogue`

Town and venue speech.

Suggested fields:

- `dialogue_id`
- `venue_id`
- `speaker_role`
- `speaker_name`
- `state`
- `line`
- `weight`
- `conditions_json`

States:

- `before_seen`
- `before_test`
- `test_active`
- `leader_unlocked`
- `after_loss`
- `after_victory`
- `rematch_available`
- `season_active`

### `battle_sessions`

Required before serious PvP.

Suggested fields:

- `battle_session_id`
- `battle_kind`
- `venue_id`
- `ruleset_id`
- `player_one_user_id`
- `player_two_user_id`
- `npc_trainer_id`
- `state`
- `turn_number`
- `team_one_json`
- `team_two_json`
- `battle_log_json`
- `created_at`
- `updated_at`
- `completed_at`
- `winner_user_id`

This table can also support PvE if the project wants battle replay, stronger anti-cheat, or better reward validation.

## Changes Needed In The Current Battle Page

### Add Fixed Trainer/Venue Selection

Current behavior:

- `?pg=battle_minigame` loads a random trainer.
- `?pg=battle_minigame&battle=wild&region_id=...` loads a wild encounter.

Needed behavior:

- `?pg=battle_minigame&battle=trainer&trainer_id=...`
- `?pg=battle_minigame&battle=venue&venue_id=...`
- `?pg=battle_minigame&battle=venue&venue_slug=...`

If `trainer_id` or `venue_id` is present, the battle should not call `ORDER BY RAND()`. It should validate access, load the correct trainer/team/ruleset, and store those in the session.

### Add Venue Payload Fields

Extend `$battle_payload` with:

- `venue`
- `ruleset`
- `badgeReward`
- `leader`
- `stageTheme`
- `midBattleLines`
- `victoryUnlocks`
- `onVictoryReturnUrl`
- `onLossReturnUrl`

### Move Toward Server-Side Turn Resolution

For the first PvE gym, client resolution is acceptable if rewards remain low and badge awarding validates session state. For PvP, it is not enough.

Migration path:

1. Keep the current JS renderer.
2. Extract damage and turn logic into a PHP battle engine.
3. Add POST command endpoint: fight/item/switch/flee.
4. Server returns a list of battle events.
5. JS renders those events using existing animation functions.
6. Store session state server-side.

### Implement Missing Move Mechanics In Priority Order

The schema already supports more than the JS uses. For gyms, implement mechanics in this order:

1. Accuracy and misses.
2. Priority.
3. PP or per-battle move uses.
4. Critical hits.
5. Basic status: burn, poison, paralyze, freeze/sleep equivalent, confusion.
6. Stat stage changes.
7. Multi-hit moves.
8. Target modes and double battles.
9. Arena hazards.

Do not design a gym around an unimplemented mechanic unless that mechanic is part of the same implementation phase.

## First Vertical Slice Recommendation

Build one gym/stadium fully before designing all regions.

Recommended first venue: Meridian Arc Fieldhouse in the United Free Republic of Borealia.

Why this is a good first slice:

- The project already has `urb-adventure2.php`, which includes arena/backstage flavor.
- The region has modern civic/sports language that fits the current battle minigame presentation.
- A stadium format can naturally become a PvP hub later.
- It can use a simple story-mode ruleset: 3v3, items allowed, level cap optional.
- The leader can specialize in "ordinary" fundamentals, making it a tutorial gym for switching, speed, and neutral damage.

Minimum vertical slice:

- Add venue data.
- Add a region map area linking to the venue.
- Add a venue page.
- Add one test step.
- Add one fixed leader trainer and roster.
- Add fixed trainer loading to `battle_minigame.php`.
- Add a badge reward table.
- Add post-victory map/page state.
- Add five dialogue lines before and after victory.

## Example First Venue: Meridian Arc Fieldhouse

### Identity

- Region: United free Republic of Borealia.
- City: Meridian Arc.
- Venue kind: Stadium/fieldhouse.
- Specialty: Vulgaris plus speed/initiative fundamentals.
- Leader role: Civic league champion, former transit safety officer, public recreation advocate.
- Public reputation: Beloved by kids, mocked by elite trainers as "too ordinary," feared by serious challengers because her fundamentals are clean.
- Badge: Signal Badge.
- Lesson: Turn order, switching, and not underestimating neutral matchups.

### Town Buildup

- The map area description mentions crowds, local broadcasts, and a practical leader.
- `urb-corner-mart` can sell a healing item or speed-support item with flavor about fieldhouse challengers.
- `urb-adventure2.php` already has arena and backstage flavor that can point toward the venue.
- Random event text can mention kids arguing over the leader's ace.

### Gym Test

Name: Scoreboard Drill.

Structure:

1. Reception asks the player to register a lead creature.
2. The player faces two apprentice trainers or simulated matchups.
3. Before each fight, a scoreboard preview hints which opponent is faster or bulkier.
4. The player must win or correctly answer one tactical question after losing.

Mechanic taught:

- Faster creatures act first.
- Switching is a valid answer.
- Neutral attacks can still win if the stat matchup is good.

### Leader Battle

Story rules:

- 3 creature limit for leader.
- Player can use full party for first implementation, but UI should show recommended 3.
- Items allowed for story mode.
- Flee disabled once leader battle starts, or fleeing counts as a loss.

Leader roster shape:

- Opener: fast creature with moderate attack.
- Mid: defensive creature that punishes poor element choice.
- Ace: balanced creature with a priority or high-speed move once priority is implemented. Until then, give it strong initiative.

### Rewards

- Signal Badge.
- Dosh.
- A move recipe or item tied to initiative.
- Unlock Fieldhouse Rematch.
- Unlock casual "Fieldhouse Rules" PvP once PvP exists.

### Aftermath

- Fan NPC praises the player's switch or endurance.
- Shopkeeper offers badge discount or new stock.
- Leader offers rematches after a cooldown.
- Map area shows "Badge earned."

## Regional Venue Concept Bank

These are not final implementation tickets. They are examples of how Harmontide can apply the same formula differently by region.

### Aegia Aeterna: Heliadora Vowstone Gym

- City identity: marble forums, vow-stones, solar civic ritual.
- Venue kind: Forum gym/proving court.
- Specialty: Stone and Heat.
- Leader role: oath judge and public works duelist.
- Test: The player reads three vow-stone plaques and chooses which promise matches each trainer's battle style.
- Lesson: Defensive typing and commitment. Once you choose a path, you fight its specialist.
- Town talk: Locals admire the leader's fairness but warn that "fair" does not mean "easy."
- Reward: Vow Badge, unlocks a contract-style rematch ladder.

### Nornheim: Skeldgard Shieldhall

- City identity: shield-walled thingstead under Worldtree spurs.
- Venue kind: Shieldhall.
- Specialty: Cold and Wyrm.
- Leader role: winter warden and saga keeper.
- Test: Endurance gauntlet where healing is limited between apprentice fights.
- Lesson: Sustain, team order, and preserving HP.
- Town talk: Elders praise patience; young challengers brag until the cold drains them.
- Reward: Hearthshield Badge, unlocks a weekly survival cup.

### Kemet: Ankhmeru Hall of Ma'at

- City identity: living pyramid, law, river quays, ritual order.
- Venue kind: Judgment hall.
- Specialty: Mental and Stone.
- Leader role: scale keeper, archivist, ceremonial judge.
- Test: Weigh testimony from NPCs, then fight the trainer whose "claim" was false.
- Lesson: Reading battle hints and choosing counters.
- Town talk: Scribes call the leader merciful; merchants call them impossible to fool.
- Reward: Scale Badge, unlocks a puzzle-rematch tier.

### Yamanokubo: Amatera Lantern Dojo

- City identity: shrine avenues, storm drums, theater, careful etiquette.
- Venue kind: Dojo and shrine path.
- Specialty: Ethereal, Fae, and Kuro.
- Leader role: shrine performer and etiquette instructor.
- Test: Observe lantern patterns and choose the correct path; wrong choices trigger apprentice battles.
- Lesson: Anticipation, switching, and recognizing deceptive matchups.
- Town talk: Locals emphasize manners. Victory should feel like being accepted, not conquering a place.
- Reward: Lantern Badge, unlocks night rematches.

### Xochimex: Xochival Flower Market Arena

- City identity: marigold boulevards, markets, canals, night celebrations.
- Venue kind: Festival arena.
- Specialty: Flora and Malus.
- Leader role: market organizer and festival battler.
- Test: Gather correct offerings/ingredients from market stalls while avoiding misleading sales pitches.
- Lesson: Status preparation and resource choice.
- Town talk: Vendors disagree about whether the leader is generous or terrifyingly competitive.
- Reward: Marigold Badge, unlocks festival cup events.

### Spice Route League: Navakai Harbor Circuit

- City identity: wayfinders' atoll capital, canoe halls, trade winds.
- Venue kind: Harbor circuit.
- Specialty: Vai and Aer.
- Leader role: route captain and trade mediator.
- Test: Pick a cargo route through changing wind/tide clues. Each route changes the trainer faced before the leader.
- Lesson: Previewing risk and choosing matchups.
- Town talk: Sailors talk in practical hints; tourists misunderstand them as poetry.
- Reward: Compass Badge, unlocks rotating route battles.

### Sila Council: Qilaktuk Aurora Moot

- City identity: aurora moot on sea ice.
- Venue kind: Council ring.
- Specialty: Cold and Aer.
- Leader role: ranger-mediator and weather reader.
- Test: Read aurora colors to choose safe paths; wrong paths trigger hazard battles.
- Lesson: Arena hazards and defensive play. Implement after hazards exist.
- Town talk: Locals respect restraint. Reckless challengers are treated as a rescue problem.
- Reward: Aurora Badge, unlocks hazard-format rematches.

### Gran Columbia: Solvine Congress Green

- City identity: sun-bound congress city draped in vines.
- Venue kind: Public debate arena.
- Specialty: Flora and Electra.
- Leader role: civic speaker and botanical engineer.
- Test: Debate-style choice prompts where each answer selects a trainer with a different team style.
- Lesson: Adapting team order to visible opponent archetypes.
- Town talk: The leader is praised for public works and teased for never giving a short answer.
- Reward: Vinevolt Badge, unlocks debate bracket PvP.

### Rheinland: Rheingard Bellworks Arena

- City identity: bell-and-craft stronghold on the broad river.
- Venue kind: Foundry arena.
- Specialty: Ferrum and Electra.
- Leader role: bell founder and craft guild champion.
- Test: Tune bells in correct sequence; each bell represents speed, attack, defense, or element.
- Lesson: Stat reading and initiative.
- Town talk: Craftspeople describe battling like making a bell: bad timing cracks the work.
- Reward: Bellmark Badge, unlocks gear-themed rewards.

### Yara Nations: Warraluma Meeting Ground

- City identity: song-water, meeting ground, Country-led stewardship.
- Venue kind: Respectful trial ground.
- Specialty: Vai, Flora, and Stone depending on local custodians.
- Leader role: ranger custodian and community teacher.
- Test: Ask permission, follow marked paths, and learn local signs before any fight.
- Lesson: Restraint, reading conditions, and honoring rules.
- Town talk: The leader is not framed as someone to defeat, but someone who decides whether the challenger listened.
- Reward: Songwater Badge or Stamp, unlocks stewardship encounters.

## PvE Design Direction

### Story Gyms

Story gyms should be player-friendly and clear.

Recommended settings:

- Leader team size: 2 to 4 for early gyms, 4 to 6 for late gyms.
- Player team: full active party at first.
- Items: allowed in early gyms, restricted in rematches.
- Level scaling: either fixed recommended level or badge-count scaling.
- Rewards: badge, Dosh, item, unlock.

### Rematch Gyms

Rematches should show the leader learning from the player.

Possible changes:

- Higher level roster.
- Better move coverage.
- Item restrictions.
- Alternate ace.
- Arena hazard.
- Required team size parity.
- Once-per-day or once-per-week reward.

### Stadium Cups

Once multiple venues exist, create cups:

- Region cup: only creatures from that region.
- Badge cup: requires specific badges.
- Element cup: single element or counter-element format.
- Rookie cup: level cap.
- Master cup: no item use, team size parity.

### Trial-Style Venues

Some regions should not feel like sports arenas. For those, use:

- Local ritual.
- Mentor/captain.
- Totem-style boss creature.
- Kahuna-like authority battle.
- Stamp instead of badge if culturally appropriate.

Backend can still award a badge-like record. The UI can call it a stamp, mark, knot, seal, token, or charter.

## PvP Design Direction

PvP should grow out of stadiums, not be bolted onto them.

### PvP Prerequisites

Before real PvP:

- Server-side battle resolution.
- Battle session persistence.
- Ruleset validation.
- Team snapshotting.
- Reward anti-cheat.
- Turn timeout handling.
- Surrender handling.
- Replay or audit log.

### PvP Formats From Venues

Each venue can define one PvP format.

Examples:

- Fieldhouse Rules: 3v3, level cap, no duplicate species.
- Shieldhall Rules: no items, gauntlet HP carries between rounds.
- Harbor Circuit Rules: players pick route modifiers before battle.
- Bellworks Rules: initiative matters, priority moves limited.
- Festival Rules: status moves enabled, item use limited.

### Asynchronous Player Gyms

Later, players could build "defense rosters" under venue rules.

Flow:

1. Player earns a venue badge.
2. Player unlocks that venue's defense format.
3. Player registers a team.
4. Other players challenge the team as PvE-like battles.
5. Defender earns small rewards based on successful defenses.

This gives PvP flavor without requiring both players online, but it still requires server-side battle validation.

### Seasonal Stadium Ladders

Galar-style public spectacle can become:

- Weekly brackets.
- Monthly leaderboards.
- Region-limited seasons.
- Cosmetic titles.
- Stadium banners.
- Profile badges.

Avoid high-value rewards until anti-cheat is mature.

## Implementation Phasing

### Phase 1: PvE Venue Vertical Slice

Goal: one complete gym/stadium with badge.

Tasks:

- Create venue and badge data.
- Add a region map area.
- Add a venue page.
- Add fixed trainer loading to `battle_minigame.php`.
- Add one leader trainer and roster.
- Add one simple pre-battle test.
- Add persistent badge/progress table.
- Award badge after victory.
- Add post-victory town state.

Do not implement:

- Full PvP.
- Double battles.
- Complex status.
- Full league.
- Every region.

### Phase 2: Reusable Venue Framework

Goal: make the second and third venues cheap to build.

Tasks:

- Generalize venue page.
- Generalize challenge steps.
- Add dialogue table or JSON.
- Add ruleset loading.
- Add venue battle payload.
- Add stage skin support.
- Add rematch tier support.

### Phase 3: Battle Engine Hardening

Goal: prepare for advanced PvE and PvP.

Tasks:

- Move turn resolution server-side.
- Add battle session persistence.
- Implement accuracy, priority, PP/use limits, status, and stat stages.
- Validate rewards against session outcome.
- Add replay/audit logs.

### Phase 4: League Layer

Goal: connect venues into a regional or global progression.

Tasks:

- Badge case UI.
- Badge gates.
- League qualification.
- Stadium cup page.
- Region leaderboards.
- Leader rematches.
- Seasonal PvE events.

### Phase 5: PvP

Goal: fair venue-based player battles.

Tasks:

- Friend challenge.
- Casual venue rules.
- Asynchronous defense rosters.
- Seasonal ladders.
- Ranked rewards.

## Minimum Data For One Venue

For the first implementation, the project can start with this minimal set:

- `venue_slug`
- `region_id`
- `venue_name`
- `venue_kind`
- `leader_trainer_id`
- `badge_name`
- `specialty_element_id`
- `recommended_level`
- `ruleset`
- `challenge_steps`
- `before_dialogue`
- `after_victory_dialogue`
- `stage_theme_key`

Only after the first venue works should the schema be expanded to the full model.

## Battle Design Checklist

Every venue concept should answer these before implementation:

- What local institution is this?
- Why do townspeople care about it?
- Who is the leader outside battle?
- What is the leader's public reputation?
- Which element or mechanic does the venue teach?
- What test comes before the leader?
- Which local creature or item helps the player prepare?
- What is the leader's opener, mid-fight plan, and ace?
- What special rule, if any, makes the fight distinct?
- What does the badge unlock?
- What changes in town after victory?
- How can this venue later become a PvP format?

## Recommended Definition Of Done For The First Gym

The first gym/stadium is done when:

- It appears as a clickable area on a regional map.
- Its venue page shows leader, badge, specialty, rules, and player status.
- At least five local lines mention the leader or venue.
- The player must complete one pre-leader test.
- The leader battle uses a fixed trainer, not random selection.
- Victory awards a persistent badge once.
- Loss gives useful retry advice.
- The regional page or venue page reflects completion.
- The leader can be rematched in a basic form.
- The implementation does not disturb existing random trainer or wild battle flows.

## Important Technical Warnings

### Do Not Build PvP On The Current Client-Side Damage Model

The current JavaScript battle flow is excellent for presentation, but it computes combat client-side. That is not acceptable for meaningful PvP or valuable rewards.

Use it as the renderer. Move authority to PHP before competitive systems.

### Do Not Make Every Venue A One-Off Page

One-off pages are fine for prototypes, but gyms need shared systems:

- Shared venue data.
- Shared progress.
- Shared badge award logic.
- Shared battle launch.
- Shared dialogue states.
- Shared rulesets.

Otherwise every region becomes maintenance debt.

### Do Not Overload The First Gym

The first venue should prove the loop:

town buildup -> venue page -> test -> fixed battle -> badge -> aftermath.

It does not need complex status, PvP, hazards, double battles, seasonal cups, or full league structure.

## Final Recommendation

Build the gym/stadium feature as a regional battle venue framework.

Start with one complete stadium in Meridian Arc because it aligns with the existing arena flavor and current battle presentation. Use it to add fixed leader battles, venue progress, badge rewards, and town dialogue. Once that loop feels good, add two culturally different venues: one formal/traditional venue such as Ankhmeru Hall of Ma'at or Amatera Lantern Dojo, and one environmental/trial venue such as Qilaktuk Aurora Moot or Navakai Harbor Circuit.

That trio will prove the system can support:

- Stadium spectacle.
- Civic/traditional ritual.
- Environmental/regional trials.

After that, the project can safely move toward rematches, league progression, and venue-based PvP.
