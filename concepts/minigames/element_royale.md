# Element Royale

A grid battle royale for the games page. Every entity is a single number wearing an element.
You roam a generated map, collide with other entities, and the winner absorbs the loser's number.
Last entity standing takes the pot.

This document turns the raw idea into something implementable. It resolves the collision maths
against the existing `element_calc` table, names the design holes, and proposes a build order.

## Project systems this builds on

- `pages/games.php` — the minigame index (add one card)
- `layout/header.php` — per-page script enqueue with `filemtime` cache busting
- `assets/js/element-icons.js` — the 18 element glyphs, keyed by `element_id`
- `assets/js/battle-minigame.js` — reference for the payload-in-`<script type="application/json">` pattern
- `score_exchange.php` — score to Dosh conversion, with a 3-per-day cap already enforced
- `elements` and `element_calc` tables — the entire combat model, already populated

## 1. Core loop

1. Pick an element from the 18 (icons make this a wheel, not a dropdown).
2. Pick a spawn tile by x/y on the map preview.
3. Everyone starts with 5 points.
4. Tick-based real time: hold a direction, step one tile per your movement cooldown.
5. Step into an occupied tile and the collision resolves immediately.
6. Winner takes the loser's points. Loser is gone.
7. Ring closes. Last one standing wins.

## 2. Collision resolution

The raw idea says "fire and water, water would win" and "same element, bigger number wins".
Both of those fall out of one formula, so there is no need for special cases.

`element_calc` is directional: a row is `(element_id, target_element_id, effectiveness)` where
effectiveness is one of `0.00`, `0.50`, `1.00`, `2.00`. A collision is mutual, so look up both
directions and let each side hit with its number scaled by its own matchup:

```
powerA = A.points * eff(A.element -> B.element)
powerB = B.points * eff(B.element -> A.element)
```

Higher power wins. The mover — the entity that stepped into the other — wins ties, which rewards
committing to an attack instead of circling.

Worked examples against real table rows:

| Collision | Lookup | Power | Result |
|---|---|---|---|
| Heat 5 steps into Vai 5 | `eff(2,3)=0.5`, `eff(3,2)=2.0` | 2.5 vs 10 | Vai wins with 10 points |
| Flora 8 steps into Flora 5 | `eff(5,5)=0.5` both ways | 4 vs 2.5 | bigger number wins, as intended |
| Venom 20 steps into Iron 3 | `eff(11,16)=0.0`, `eff(16,11)=1.0` | 0 vs 3 | Iron eats a blob eight times its size |

That last row is the interesting one, and it is the single most important tuning dial in the game.

### The immunity dial

Iron is flatly immune to Venom. Under the pure formula a 3-point Iron deletes a 20-point Venom,
which is a spectacular upset the first time and a rage quit the fifth time.

Three options, in order of preference:

1. **Clamp the multiplier to a floor of 0.25 in this mode.** Venom 20 vs Iron 3 becomes 5 vs 3 —
   the Iron still punches far above its weight but overwhelming size still means something.
   Recommended default.
2. **Keep true immunity but halve the reward.** The upset stays, the winner only absorbs half,
   so farming immunities is not a strategy on its own.
3. **Keep it raw.** Maximum drama, maximum salt. Good for a limited-time weekend ruleset.

Ship option 1 as the default with the floor in a config constant so it can be retuned without
touching game logic.

## 3. Anti-snowball

A pure absorption game has one failure mode: whoever gets the first two kills wins by inertia and
the last two minutes are a formality. Three cheap counterweights, all of which interact with the
number that is already on screen:

- **Mass slows you down.** `moveCooldown = base + floor(points / 5)` ticks. A 5-point entity is
  nimble, a 40-point entity lumbers. Small entities choose their fights; big ones cannot escape one.
- **Big entities are visible everywhere.** Below a threshold you only see nearby entities. Above it
  you are drawn on the whole map as a beacon. The leader becomes the hunted, which is the exact
  emotional arc a royale needs.
- **Element counters ignore size.** Already free from `element_calc`. A small entity that picks a
  favourable element has a real answer to a large one, so choosing your element at spawn is a
  genuine strategic decision rather than flavour.

Full absorption stays — it is the core fantasy of the mode and should not be nerfed. Control the
runaway with speed and visibility, not with reduced rewards.

## 4. Map

Seeded generation, so a map is reproducible from a single integer. Log the seed with the run.

| Tile | Movement | Sight | Notes |
|---|---|---|---|
| Floor | passable | clear | default |
| Roadblock | blocked | blocked | the basic wall |
| Tree | blocked | blocked | visual variety, same rules as roadblock |
| Bush | passable | blocks occupant | ambush tile — you are hidden while standing in it |
| Water | blocked | clear | opens sightlines while splitting the map |

Bushes are the cheapest depth in the whole design: one flag on a tile turns the map from a maze
into a game of ambushes, and the AI can use them too.

Generation must guarantee connectivity — flood fill from a spawn tile and carve until every floor
tile is reachable, otherwise someone spawns in a sealed pocket and the round stalls.

Starting numbers: 40x30 grid, 24 entities, roughly 25% obstacle coverage.

### Ring

Every N ticks the outermost surviving ring of tiles becomes lethal ground. Standing outside costs
**1 point per tick** rather than killing outright, which means a big entity can tank a shortcut
through the dead zone and a small one cannot. It also guarantees the round terminates.

### Orbs

Neutral +1 point pickups scattered on the floor, respawning slowly. They give a small entity a path
back into contention without a fight, and they create predictable hotspots where collisions happen.
Without them, early deaths feel arbitrary and the midgame is empty walking.

## 5. NPC AI

The NPCs use exactly the same collision predictor the game uses to resolve fights, so they play by
rules the player can learn and exploit. Each tick, for every visible entity, predict the outcome,
then act on personality:

| Personality | Behaviour |
|---|---|
| Bully | hunts anything it beats on raw points, ignores matchups. Punishes greed. |
| Zealot | hunts anything its element beats, regardless of size. The giant killer. |
| Coward | flees any predicted loss, forages orbs, hides in bushes. Survives to late game. |
| Wanderer | mostly random, occasional lunge. Early-game chaos and free points. |

Difficulty is three knobs, not four separate AIs: sight radius, whether pathing is BFS or greedy
single-step, and reaction delay in ticks. A dumb Bully walks into walls chasing you; a smart one
cuts you off.

## 6. Determinism and cheating

Run the whole simulation off a seeded PRNG and a tick counter. Given the same seed and the same
input log, the round replays identically. That buys three things: reproducible bug reports,
replays/ghosts later, and the option to re-simulate a submitted run server-side if score fraud ever
becomes a problem. Do not build the server-side validator in phase 1 — just keep the sim
deterministic so the door stays open.

## 7. Data and endpoints

No new tables are needed for phase 1.

- Page reads `elements` and `element_calc` once and embeds them as JSON in the page, mirroring
  `#battle-payload` in `pages/battle_minigame.php`. That is 18 + 324 rows, trivial.
- Scoring goes through the existing `score_exchange.php`: add a rate entry such as
  `'elementroyale' => 0.4`. The daily 3-exchange cap and the temp-user path both come for free.
- Final score should combine placement and points, not points alone, otherwise suiciding into a
  blob at 40 points scores the same as winning.

If a leaderboard is wanted later it needs a new table, which means a migration file under `sql/`
run by hand in phpMyAdmin — the live DB user has no DDL rights.

## 8. Multiplayer

The name says battle royale, but real-time PvP on cPanel shared hosting is a different project:
it needs a persistent socket process, and every existing minigame is self-contained client-side JS.

The recommended path is **asynchronous ghosts**. Store each completed run as its seed plus input
log, then populate later lobbies on the same map with other players' recorded runs as opponents.
Players see real names and real elements, the lobby feels populated, and the infrastructure is a
single table of blobs. It is not true PvP, but it delivers most of the feeling at a fraction of the
cost, and it is the natural payoff of keeping the simulation deterministic.

Phase 1 through 3 should assume NPCs only.

## 9. Build order

**Phase 1 — playable core.** Canvas grid, seeded map generation with connectivity, player movement
on a tick loop, the collision formula with the immunity floor, one NPC personality, win/lose states.
No DB, no scoring. The goal is to find out whether moving around and colliding is fun.

**Phase 2 — the game.** Ring, orbs, mass-based movement cooldown, visibility rules and the leader
beacon, all four personalities, element selection wheel using the existing icons, spawn picker.

**Phase 3 — integration.** Games page card, score exchange rate, run summary screen, sound.

**Phase 4 — ghosts.** Persist runs, replay them as opponents, add a leaderboard table.

## 10. Open questions

- Does the player know an enemy's element before colliding? Recommended yes — draw every entity as
  its element glyph with its number on it, so the whole HUD is the map itself. Hidden elements make
  the game a coin flip.
- Are diagonal moves allowed? Recommended no. Orthogonal only keeps the matchup maths readable and
  makes obstacles matter.
- Can two entities swap places in one tick, and who wins? Needs an explicit rule; simplest is that
  simultaneous mutual entry resolves as a normal collision with no mover bonus for either side.
## 11. Phase 1 as built

Shipped in `pages/element-royale.php`, `assets/js/element-royale.js`, `assets/css/element-royale.css`.

Two constants had to be discovered rather than guessed. The first build used a sight radius of 9
against a minimum spawn distance of 4, which meant every entity opened the round already locked
onto two or three targets: the first kill landed 0.5s in and a player who did not move died after
a median of 2.4s. **Minimum spawn distance must stay above sight radius.** At 38x26 with sight 6
and spawn distance 9 the first kill lands around 1.4s and a randomly-walking player survives a
median of ~7s, with real players expected to do considerably better since they flee and use bushes.

Two stand-ins cover systems that belong to phase 2:

- **Sight radius grows by 1 every 250 ticks.** A cheap substitute for the closing ring — without
  it, the last survivors wander an 850-tile map without ever meeting.
- **Rounds are capped at 2400 ticks and settled on points held.** Necessary because mass-based
  movement means a small entity is strictly faster than a big one, so a chase can genuinely run
  forever. Measured: rounds hit 20000+ ticks before the cap existed. The ring makes this obsolete.

Browser testing then found three things a headless run structurally cannot:

- **The overlay was covering the board from page load.** `.er-overlay` sets `display: flex`, which
  outranks the browser's built-in `[hidden] { display: none }`, so the "Round over" panel was
  always on screen and the game looked like it ended the moment you pressed start. Any element
  hidden via the `hidden` attribute needs an explicit `[hidden] { display: none }` rule if the
  stylesheet also gives it a `display`.
- **A key tap could be swallowed.** Holding a direction worked, but a quick press that began and
  ended between two ticks was lost, because only `heldDir` was consulted. The last direction
  pressed is now remembered until it has actually been spent on a step.
- **There was no spawn protection.** Nothing could be eaten in the first 3 seconds was needed
  simply so the player has time to put their hands on the keys. Hunters also wander rather than
  hunt during grace: while they were still hunting, they piled up against blocked targets and
  three entities died on the single tick that grace lifted.

Verified headlessly over 120 simulated rounds and driven by hand in a browser: every round
terminates, the total point pool is conserved on every tick (no duplication or loss through
collisions), and the last-one-standing branch ends with the entire pool on the winner. With grace
in place the first kill lands ~2.8s in and a randomly-walking player survives a median of ~10s.

## 12. Decided

- **Element choice is unrestricted.** All 18 are available to everyone from the start. Gating the
  choice behind owned creatures would permanently lock some players out of some elements, which is
  worse than the lost collection tie-in.
- **Score is final points alone**, not points weighted by placement. Simpler to explain, and it
  means an aggressive run that dies at 40 points is worth as much as a cautious win — deliberately.
- **Phase 1 ships one NPC personality, the Hunter**, which uses the full collision predictor rather
  than raw points. A pure raw-points Bully as the only opponent would make elements feel irrelevant
  in the first playtest. The four-personality split lands in phase 2.
