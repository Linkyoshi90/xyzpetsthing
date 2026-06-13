# Async Board Game MVP Implementation Plan

## Purpose

This document records the accepted implementation direction for a first playable async board-game MVP and the path toward a fuller Harmontide feature inspired by Monopoly, Mario Party, and seasonal community events like the Altador Cup.

The ADRs remain in the file as a decision record. Each ADR keeps the options that were considered, marks the accepted choice, and gives implementation consequences for that choice.

## Product Spine

Working pitch:

> A 2-4 player async Harmontide board match where players invite friends or join a random challenge queue, take sequential turns, roll a die, choose board branches, claim and upgrade spaces, trigger board events, and finish after a short fixed number of rounds. Final net worth determines seasonal progress first and a small capped Cash-Dosh bonus second.

The implementation path has two MVP slices:

- Friend/private MVP: prove the async board loop with friend invites, all-invite acceptance, notifications, rewards, and admin recovery tools.
- Public MVP: add random challenges after the friend MVP has turn expiration, reward caps, minimum participation checks, and safe admin controls.

The full game can then add more regional boards, seasonal events, trophies, cosmetics, faction/team progress, richer board effects, and content tooling.

## Current Harmontide Integration Points

- Friends: `user_friends`, `lib/chat.php`, `pages/friends.php`, `pages/user-chat.php`.
- Currency: `user_balances`, `currency_ledger`, `APP_CURRENCY_*` constants.
- Score conversion precedent: `score_exchange.php`.
- Games surface: existing minigames live under `pages/`.
- Notifications: no durable notification table found yet; this plan adds reusable in-app notifications for invites, turns, skips, completion, and rewards.
- Random challenges: no queue/matchmaking system found yet; this plan adds a lightweight board-game queue after the friend MVP guardrails are live.
- Seasonal progress: no board-game season tables exist yet; this plan adds seasonal points as the primary reward layer.

## Accepted Decision Summary

- Scope: friend invites and random challenges are both in the MVP path.
- Sequencing: private friend MVP first, public random challenges after expiration and reward caps are live.
- Working name: Harmontide Circuit.
- First board: Redwind Harbor Circuit in Redwind, Red Sun Commonwealth.
- Friend match start: every invited player must accept.
- Match format: 6 rounds per player on a 12-16 tile regional board.
- Turn flow: roll, choose branch/path if required, resolve tile, buy/upgrade, end turn.
- Minigames: excluded from board-game turn resolution.
- Authority: server-authoritative PHP engine.
- Storage: normalized match tables plus small JSON config/metadata fields.
- Content: first board is regional and stored as versioned JSON.
- Economy: board cash is separate from Cash-Dosh; losses are capped at zero board cash.
- Rewards: seasonal points first, small capped Cash-Dosh bonus second.
- Guardrails: in-app notifications, gentle turn skips, daily/weekly caps, minimum participation, strict idempotency, and admin recovery actions.

## Implementation Phases

### Phase 0: Product Lock And Content Sketch

Status: Created.

Goal: define the smallest board game worth building.

Steps:

1. Pick the MVP name, theme, and first board setting.
2. Define MVP player count: 2-4 players.
3. Define the first regional board and its tile graph.
4. Define starting cash, salary/pass-start bonus, property prices, rent, and upgrade costs.
5. Define short-match tuning: 6 rounds per player and a 12-16 tile board.
6. Define branch/path rules for tiles with multiple outgoing paths.
7. Define capped rent/event losses so board cash cannot go negative.
8. Define seasonal points, small Cash-Dosh bonus, daily/weekly caps, and minimum participation rules.
9. Write the first board as structured JSON before building the UI.

Deliverables:

- Board rules table: `board_game_phase0.md`.
- Regional tile list with branch graph: `board_game_phase0.md` and `data/board_game_boards/redwind_harbor_circuit.json`.
- Economy tuning JSON: `data/board_game_boards/redwind_harbor_circuit.json`.
- Reward tuning JSON: `data/board_game_rewards/mvp_seasonal_small_cash_v1.json`.
- Approved decision summary for the accepted ADR options: this document and `board_game_phase0.md`.

### Phase 1: Database And Server Game Engine

Goal: create a server-authoritative async game state that can be advanced safely one turn at a time.

Steps:

1. Add tables for matches, players, turns, action idempotency, event log, invites, notifications, seasons, season points, and random matchmaking queue.
2. Add `lib/board_game.php` with pure-ish game operations:
   - create match
   - invite players
   - accept/decline invite
   - start match
   - join/leave random queue
   - create match from random queue
   - get match state
   - roll and resolve movement
   - choose path/branch where required
   - buy property
   - upgrade property
   - end turn
   - skip expired turn
   - settle completed match
3. Store match state as normalized rows plus an event log.
4. Wrap every state-changing action in a DB transaction with row locks.
5. Add explicit idempotency keys for roll, path choice, buy, upgrade, end-turn, skip, and settlement.
6. Add deterministic tests or scripted fixtures for movement, branching, rent capping, settlement, and duplicate-submit protection.

Deliverables:

- SQL migration file.
- Server engine library.
- Notification helper library.
- Reward helper library.
- First deterministic match fixture.
- Manual seed game for local testing.

### Phase 2: MVP Pages And Friend Invites

Goal: make the private/friend MVP playable before public random challenges.

Steps:

1. Add a board game landing/list page under `pages/`.
2. Add create-match form:
   - select friends
   - choose player count
   - choose the first regional board/ruleset
3. Add invite inbox states:
   - invited
   - accepted
   - declined
   - expired
4. Start invited matches only when every invited player accepts.
5. Add match detail page:
   - board view
   - player standings
   - current turn prompt
   - turn log
   - branch/path choices
   - available actions
6. Add POST handlers for match actions.
7. Add navigation entry from friends/games areas.
8. Add unread/attention indicators from reusable notifications.

Deliverables:

- `?pg=board-game` match list.
- `?pg=board-game-match&id=...` match view.
- Friend invite flow.
- Notification count/panel.
- Playable 2-player and 4-player local matches.

### Phase 3: MVP Reward Settlement

Goal: settle completed matches into seasonal points and a small capped Cash-Dosh bonus.

Steps:

1. Add match result calculation:
   - cash on hand
   - property value
   - upgrade value
   - event bonuses/penalties
2. Rank players by final net worth.
3. Award seasonal points as the primary reward.
4. Apply daily and weekly Cash-Dosh caps.
5. Apply minimum meaningful participation checks.
6. Insert ledger rows into `currency_ledger` with reason such as `board_game_settlement`.
7. Update `user_balances` in the same transaction.
8. Mark match as settled with a settlement timestamp and metadata.
9. Block duplicate settlement with idempotency keys and settled-state checks.
10. Notify players when rewards are settled.

Deliverables:

- Settlement function.
- Ledger metadata schema.
- Seasonal points schema and writer.
- Reward cap rules.
- Settlement test cases.

### Phase 4: Usability, Abuse Guardrails, And Launch Readiness

Goal: make async play survivable in the real world.

Steps:

1. Add turn timers with configurable grace period.
2. Add gentle skip handling for expired turns.
3. Add surrender handling without breaking remaining players.
4. Add match cancellation before start.
5. Add player-facing turn history.
6. Add admin/debug view for inspecting stuck matches.
7. Add anti-farming checks:
   - minimum meaningful turns
   - Cash-Dosh reward caps per day/week
   - no rewards for abandoned matches
8. Add admin-safe actions:
   - cancel match
   - force skip
   - mark expired
9. Add clear UI states for waiting, your turn, completed, cancelled, and expired.

Deliverables:

- MVP beta-ready flow.
- Admin/debug tooling.
- Abuse checklist.

### Phase 5: Public MVP Random Challenges

Goal: add random challenges once the private MVP is safe enough for players who do not know each other.

Steps:

1. Add random challenge entry from the board-game page.
2. Add queue join/leave flow.
3. Match queued players into 2-4 player games.
4. Apply the same short ruleset, notifications, turn expiration, reward caps, and admin controls.
5. Add queue-specific abuse checks and logging.
6. Add clear UI for queued, matched, waiting, and expired states.

Deliverables:

- Random challenge queue.
- Random match creation.
- Public MVP launch checklist.

### Phase 6: Full Game Expansion

Goal: grow the MVP into a real Harmontide social game.

Steps:

1. Add multiple themed boards tied to countries/regions.
2. Add seasonal events with team or faction scoring.
3. Add trophies, cosmetics, and profile badges for board-game achievements.
4. Add richer tile types:
   - event tiles
   - shop tiles
   - toll roads
   - tax tiles
   - chance/fate tiles
   - regional blessing/curse tiles
5. Add leaderboards for seasonal points, not raw Cash-Dosh extraction.
6. Add content admin tooling for boards, tiles, and seasonal modifiers after MVP rules stabilize.
7. Add analytics for completion rate, stalled matches, average duration, random queue health, and reward outflow.

Deliverables:

- Seasonal board-game module.
- Board content management.
- Long-term progression and cosmetics.

## Proposed Data Model

Table names are tentative.

### `board_game_matches`

- `match_id`
- `ruleset_key`
- `board_key`
- `created_by`
- `match_type`: `friend`, `random`
- `status`: `inviting`, `queued`, `active`, `completed`, `cancelled`, `expired`
- `current_player_id`
- `current_turn_started_at`
- `round_number`
- `max_rounds`
- `state_json`
- `created_at`
- `started_at`
- `completed_at`
- `settled_at`

### `board_game_players`

- `match_player_id`
- `match_id`
- `user_id`
- `seat_order`
- `status`: `invited`, `accepted`, `declined`, `active`, `skipped`, `surrendered`, `forfeited`
- `position`
- `cash`
- `net_worth`
- `turns_taken`
- `missed_turns`
- `last_seen_at`
- `joined_at`

### `board_game_tiles`

Optional if board content becomes database-backed. For MVP, board definitions should live in versioned JSON files under `data/board_game_boards/`.

- `tile_id`
- `board_key`
- `tile_index`
- `tile_type`
- `name`
- `config_json`

### Board Definition JSON

MVP board content should be config-backed rather than database-backed.

- `board_key`
- `region_key`
- `name`
- `ruleset_key`
- `tiles`
- `edges`
- `starting_cash`
- `pass_start_bonus`
- `max_rounds`
- `reward_profile`

### `board_game_properties`

- `match_property_id`
- `match_id`
- `tile_index`
- `owner_user_id`
- `upgrade_level`
- `price`
- `rent`

### `board_game_turns`

- `turn_id`
- `match_id`
- `user_id`
- `round_number`
- `turn_number`
- `status`: `open`, `completed`, `skipped`, `forfeited`
- `started_at`
- `completed_at`
- `idempotency_key`

### `board_game_action_keys`

- `action_key_id`
- `match_id`
- `turn_id`
- `user_id`
- `action_type`
- `idempotency_key`
- `result_event_id`
- `created_at`

### `board_game_events`

- `event_id`
- `match_id`
- `turn_id`
- `actor_user_id`
- `event_type`
- `event_json`
- `created_at`

### `board_game_invites`

- `invite_id`
- `match_id`
- `inviter_user_id`
- `invitee_user_id`
- `status`: `pending`, `accepted`, `declined`, `expired`
- `created_at`
- `responded_at`

### `board_game_random_queue`

- `queue_id`
- `user_id`
- `ruleset_key`
- `board_key`
- `status`: `queued`, `matched`, `cancelled`, `expired`
- `created_at`
- `matched_at`
- `match_id`

### `board_game_seasons`

- `season_id`
- `season_key`
- `name`
- `status`: `draft`, `active`, `completed`
- `starts_at`
- `ends_at`
- `config_json`

### `board_game_season_points`

- `season_point_id`
- `season_id`
- `match_id`
- `user_id`
- `points_delta`
- `reason`
- `metadata`
- `created_at`

### `user_notifications`

Required for the accepted notification strategy.

- `notification_id`
- `user_id`
- `type`
- `subject_type`
- `subject_id`
- `message`
- `is_read`
- `created_at`
- `read_at`

## MVP Rules Draft

These are starting numbers for testing, not final balance.

- Players: 2-4.
- Match types: friend invites and random challenges.
- Friend match start: every invited player must accept before the match starts.
- Random challenge start: queued players are matched after the private MVP guardrails are live.
- Match length: 6 rounds per player.
- Board size: 12-16 tiles.
- Board theme: first board is tied to a Harmontide region/country/city.
- Starting cash: 1500 board cash.
- Passing start: 200 board cash.
- Dice: one six-sided die for MVP.
- Movement: players choose a legal path or destination branch when the board graph offers a branch.
- Buy action: optional when landing on unowned property.
- Upgrade action: optional after landing on own property.
- Rent: paid automatically when landing on another player's property.
- Rent/event losses: capped so board cash cannot go negative.
- Event tiles: simple gain/loss/move effects.
- No player elimination.
- No minigames in board-game turn resolution.
- No debt, forced liquidation, auctions, trades, mortgages, or bankruptcy in MVP.
- Winner: highest net worth after final round.
- Primary rewards: seasonal points based on placement and participation.
- Secondary rewards: small capped Cash-Dosh bonus inserted through `currency_ledger`.
- Reward controls: daily and weekly Cash-Dosh caps plus minimum meaningful participation.
- Notifications: in-app notifications for invites, turns, skips, completion, and rewards.

## ADRs

### ADR-001: MVP Opponent Scope

Status: Accepted

Decision needed: 

Who can be invited to the first playable version?

Options:

- A. Friend-only invites.
- B. Friends plus direct username invites.
- C. Random matchmaking plus friend invites.

Original recommendation:

Choose A for MVP.

Why this is a real decision:

This decides how much social, abuse, and inactivity handling must exist before launch. Random matchmaking sounds like a small UI addition, but it implies queueing, abandoned-game policy, reward farming checks, blocking/reporting, and user trust work.

Implementation impact:

- A uses `user_friends` and existing friend list surfaces.
- B requires user search, invite privacy rules, and awkward "who is allowed to challenge me?" questions.
- C requires a matchmaking queue, stricter turn timers, reward controls, and moderation affordances.

Selected option: C. Random matchmaking plus friend invites.

### ADR-002: Match Start Policy

Status: Accepted

Decision needed: 

When does a created match become active?

Options:

- A. Match starts only when every invited player accepts.
- B. Creator sets a minimum player count, and the match can start once that count accepts.
- C. Creator manually starts after at least one invitee accepts.

Original recommendation:

Choose B for MVP if 3-4 player invites are supported. Choose A if MVP is 2-player only.

Why this is a real decision:

This controls whether invite friction blocks matches. If all invitees must accept, one absent friend can prevent the game from existing. If the creator starts manually, social pressure and accidental exclusions become possible.

Implementation impact:

- A is simplest and cleanest for 2-player games.
- B needs `min_players`, accepted-player locking, and expiration rules for pending invites.
- C needs an explicit start action and clear UI for who is in or out.

Selected option: A. Friend matches start only when every invited player accepts.

### ADR-003: Match End Condition

Status: Accepted

Decision needed: 

How should a match end?

Options:

- A. Fixed number of rounds, then rank by final net worth.
- B. Bankruptcy/elimination, last player standing wins.
- C. Target net worth, first player to reach the threshold wins.
- D. Hybrid: fixed rounds plus early win if a player reaches a large lead.

Original recommendation:

Choose A for MVP.

Why this is a real decision:

This determines match duration, comeback possibility, and whether players can be eliminated from an async game they are still waiting on.

Implementation impact:

- A needs `round_number`, `max_rounds`, and final settlement.
- B needs debt, asset liquidation, elimination handling, and fewer active players over time.
- C can end suddenly and rewards aggressive snowballing.
- D is tunable but harder to explain and test.

Selected option: A. Fixed number of rounds, then rank by final net worth.

### ADR-004: MVP Match Length

Status: Accepted

Decision needed: 

How long should the first match format be?

Options:

- A. Short: 6 rounds per player, 12-16 tile board.
- B. Medium: 12 rounds per player, 20 tile board.
- C. Long: 20+ rounds per player, 28-40 tile board.

Original recommendation:

Choose B for MVP testing, then tune downward if matches stall.

Why this is a real decision:

Short matches may end before property ownership matters. Long matches may feel like a chore in async play. The board size and round count shape every balance number.

Implementation impact:

- A is fastest to test but may feel shallow.
- B gives properties, rent, upgrades, and events time to matter.
- C increases content needs and abandoned-match risk.

Selected option: A. Short match: 6 rounds per player on a 12-16 tile board.

### ADR-005: Core Turn Actions

Status: Accepted

Decision needed: 

What can a player do during an MVP turn?

Options:

- A. Roll, auto-resolve tile, optionally buy/upgrade, end turn.
- B. Roll, choose a path or destination branch, then buy/upgrade.
- C. Roll, buy/upgrade, use one item/card, then end turn.
- D. Full board-game economy with trading, auctions, mortgages, and debt management.

Original recommendation:

Choose A for MVP.

Why this is a real decision:

Turn complexity determines how many async prompts a player must answer. More decisions can be fun, but they also create more places for a player to stop mid-turn.

Implementation impact:

- A supports one clean turn screen and few legal states.
- B needs board path graph logic and path UI.
- C needs item inventory, card effects, and more action validation.
- D needs many more pending-decision states and is likely too much for v1.

Selected option: B. Roll, choose a path or destination branch, then buy/upgrade.

### ADR-006: Minigame Relationship

Status: Accepted

Decision needed: 

How tightly should minigames connect to board turns?

Options:

- A. No minigames in MVP turn resolution.
- B. Async mini-events resolved automatically as dice/card effects.
- C. Optional minigame challenge grants bonus board cash or seasonal points.
- D. Required minigame encounter blocks the turn until completed.

Original recommendation:

Choose A for MVP. Consider C after the board loop is stable.

Why this is a real decision:

Required minigames make the game feel more Mario Party, but they also turn one async board turn into multiple task types and complicate fairness.

Implementation impact:

- A keeps the board engine self-contained.
- B needs a library of simple server-resolved events.
- C needs score validation and reward rules.
- D needs per-minigame integration, stale-turn handling, and anti-cheat work.

Selected option: A. No minigames in board-game turn resolution because required minigames would undermine async play.

### ADR-007: Game Authority

Status: Accepted

Decision needed: 

Where does authoritative match resolution live?

Options:

- A. Server-authoritative PHP engine.
- B. Client calculates outcomes and server stores submitted result.
- C. Mixed model: server validates only rewards and final settlement.

Original recommendation:

Choose A.

Why this is a real decision:

The board game touches competitive state and eventually Cash-Dosh. The authority model decides how much trust is placed in browser JavaScript.

Implementation impact:

- A requires `lib/board_game.php`, DB transactions, and server validation for each action.
- B is fastest but easy to tamper with.
- C still leaves many match-state exploits available.

Selected option: A. Server-authoritative PHP engine.

### ADR-008: Match State Storage

Status: Accepted

Decision needed: 

How should match state be stored?

Options:

- A. Mostly normalized tables with small JSON fields for config/metadata.
- B. One large `state_json` blob per match plus event log.
- C. Fully normalized board, tile, effect, property, and player state from day one.

Original recommendation:

Choose A.

Why this is a real decision:

This controls how easy it is to query stuck games, settle rewards, tune content, and migrate rules later.

Implementation impact:

- A gives reliable queries for players, turns, properties, and settlement while keeping board config flexible.
- B is quick but makes admin/debug views and reporting harder.
- C is clean long-term but heavy before tile rules are proven.

Selected option: A. Mostly normalized tables with small JSON fields for config/metadata.

### ADR-009: Event History Depth

Status: Accepted

Decision needed: 

How much history should the game store?

Options:

- A. Store only current state.
- B. Store player-facing event log for meaningful actions.
- C. Store full audit log including request metadata, random seeds/results, and settlement details.

Original recommendation:

Choose B for MVP, with C for settlement-specific events.

Why this is a real decision:

Async players need to understand what happened while they were away. Admins need enough history to debug reward disputes without building a full compliance log prematurely.

Implementation impact:

- A is minimal but makes the game feel opaque.
- B needs `board_game_events` and a turn log UI.
- C needs more schema, metadata discipline, and retention decisions.

Selected option: B. Store a player-facing event log for meaningful actions.

### ADR-010: Board Content Storage

Status: Accepted

Decision needed: 

Where should board definitions live at MVP?

Options:

- A. Versioned JSON files under `data/board_game_boards/`.
- B. PHP arrays in `lib/board_game_boards.php`.
- C. Database tables edited manually.
- D. Database tables with admin UI.

Original recommendation:

Choose A.

Why this is a real decision:

Board definitions will change often while rules are discovered. The storage choice affects iteration speed, deploy workflow, and whether non-developers can edit content.

Implementation impact:

- A is readable, diffable, and keeps content separate from code.
- B is fastest in PHP but mixes content and engine logic.
- C is queryable but annoying to edit safely.
- D is best for mature content operations but premature for MVP.

Selected option: A. Versioned JSON files under `data/board_game_boards/`.

### ADR-011: Dice And Movement Model

Status: Accepted

Decision needed: 

What movement model should the first ruleset use?

Options:

- A. One six-sided die.
- B. Two six-sided dice.
- C. One custom die with values tuned for board size.
- D. Card/item-based movement instead of dice.

Original recommendation:

Choose A for MVP.

Why this is a real decision:

Movement randomness controls pacing, board coverage, and how often players hit high-impact tiles.

Implementation impact:

- A is easy to explain and test.
- B is more Monopoly-like but swingier and better suited to larger boards.
- C gives tuning control but feels less familiar.
- D adds strategy but needs more content and UI.

Selected option: A. One six-sided die.

### ADR-012: Property Economy Complexity

Status: Accepted

Decision needed: 

Which economic systems ship in MVP?

Options:

- A. Buy unowned property, pay automatic rent, upgrade owned property.
- B. A plus auctions for unbought property.
- C. B plus trades between players.
- D. C plus mortgages, bankruptcy, and asset liquidation.

Original recommendation:

Choose A.

Why this is a real decision:

Every extra economic mechanic creates new async waiting states, balance surfaces, and opportunities for collusion.

Implementation impact:

- A needs simple property rows and clear action validation.
- B needs auction timers and bid notifications.
- C needs offers, acceptance, cancellation, and anti-collusion concerns.
- D needs a much deeper economic engine and more UI.

Selected option: A. Buy unowned property, pay automatic rent, upgrade owned property.

### ADR-013: Player Elimination And Debt

Status: Accepted

Decision needed: 

What happens when a player cannot afford a payment?

Options:

- A. Allow temporary board-cash debt to a fixed floor and settle by net worth.
- B. Force sale/downgrade of properties until payment can be covered.
- C. Eliminate the player when they cannot pay.
- D. Prevent payments that would make cash negative by capping rent/event losses.

Original recommendation:

Choose D for the first MVP if simplicity matters most, or A if debt drama is desired.

Why this is a real decision:

This controls whether a player can be trapped in an unfun state and how much bookkeeping the engine needs.

Implementation impact:

- A needs debt floor rules and settlement math.
- B needs liquidation UI and can stall turns.
- C creates dead-player waiting and bad async experiences.
- D is easiest to explain but reduces economic bite.

Selected option: D. Prevent payments that would make cash negative by capping rent/event losses.

### ADR-014: Notification Strategy

Status: Accepted

Decision needed: 

How should players know that they have invites or turns?

Options:

- A. Board-game page only; players check manually.
- B. Reusable in-app `user_notifications` table and layout count.
- C. Direct chat messages for invites and turns.
- D. Email/browser push notifications.

Original recommendation:

Choose B if async play is meant to be more than a prototype. Choose A for a very small internal test.

Why this is a real decision:

Async games live or die on attention cues. This also decides whether Harmontide gets a reusable notification system now.

Implementation impact:

- A is fastest but weak for real async play.
- B requires schema, helpers, read/unread UI, and layout work.
- C reuses chat but pollutes conversations and is not a true notification model.
- D is powerful but much larger and has deliverability/permission issues.

Selected option: B. Reusable in-app `user_notifications` table and layout count.

### ADR-015: Cash-Dosh Reward Model

Status: Accepted

Decision needed: 

What real account reward should a completed match pay?

Options:

- A. No Cash-Dosh in MVP; board game pays only bragging rights/test stats.
- B. Small capped Cash-Dosh payout on completed matches.
- C. Seasonal points first, small Cash-Dosh bonus second.
- D. Large placement-based Cash-Dosh rewards.

Original recommendation:

Choose C for public release. Choose A or B for early testing depending on how cautious the economy should be.

Why this is a real decision:

Cash-Dosh makes the feature meaningful, but it also turns the game into a farmable currency faucet.

Implementation impact:

- A avoids economy risk but may reduce motivation.
- B needs ledger settlement, daily/weekly caps, and anti-farming checks.
- C needs seasonal point tables but shifts excitement away from raw currency.
- D is high-risk and should wait until abuse controls and tuning are proven.

Selected option: C. Seasonal points first, small Cash-Dosh bonus second.

### ADR-016: Board Cash Versus Live Cash-Dosh

Status: Accepted

Decision needed: 

Should rent, purchases, and upgrades use live account currency?

Options:

- A. Use separate in-match board cash and settle rewards at the end.
- B. Use live Cash-Dosh for every in-game payment.
- C. Charge a live Cash-Dosh entry fee, then play with board cash.

Original recommendation:

Choose A.

Why this is a real decision:

Live-currency rent can create griefing, collusion, and real account losses from asynchronous play.

Implementation impact:

- A stores cash in `board_game_players` and touches `user_balances` only during settlement.
- B needs many live ledger writes and creates player-risk issues.
- C may be useful for tournaments later but needs refund/cancellation rules.

Selected option: A. Use separate in-match board cash and settle rewards at the end.

### ADR-017: Reward Caps And Farming Controls

Status: Accepted

Decision needed: 

How strict should reward controls be in MVP?

Options:

- A. No caps until abuse appears.
- B. Daily per-user Cash-Dosh cap for board-game settlements.
- C. Daily and weekly caps plus minimum meaningful participation.
- D. C plus repeated-opponent dampening and suspicious-match logging.

Original recommendation:

Choose C for MVP with Cash-Dosh rewards. Move to D before random matchmaking.

Why this is a real decision:

Reward controls affect player generosity, economy safety, and how much trust is placed in friend matches.

Implementation impact:

- A is simplest but unsafe.
- B is a basic guardrail.
- C needs participation checks such as rounds completed and non-forfeit status.
- D needs more analytics and match-pattern queries.

Selected option: C. Daily and weekly caps plus minimum meaningful participation.

### ADR-018: Idempotency And Concurrency Strictness

Status: Accepted

Decision needed: 

How much duplicate-submit and race-condition protection should be built into the first version?

Options:

- A. Basic current-player checks only.
- B. Transaction per state-changing action, row locks, and duplicate action rejection.
- C. B plus explicit idempotency keys for roll, buy, upgrade, end-turn, and settlement.

Original recommendation:

Choose C for any version that pays rewards. Choose B only for a no-reward prototype.

Why this is a real decision:

Duplicate rolls, duplicate purchases, or duplicate settlement payouts are severe bugs. The stricter option costs more up front but protects the economy.

Implementation impact:

- A is risky even for local testing.
- B handles most race conditions.
- C handles browser retries and double-clicks more gracefully.

Selected option: C. Transactions, row locks, duplicate action rejection, and explicit idempotency keys.

### ADR-019: Inactivity And Turn Expiration

Status: Accepted

Decision needed:

What happens when a player does not take their turn?

Options:

- A. No turn expiration in friend MVP.
- B. Gentle expiration: after a configurable time, others can skip the turn.
- C. Automatic skip after a deadline.
- D. Automatic forfeit after repeated missed turns.

Original recommendation:

Choose B for friend MVP. Add C or D before random matchmaking.

Why this is a real decision:

Async games can stall indefinitely. Too much automation can feel harsh among friends; too little makes random play unusable.

Implementation impact:

- A is easy but creates stuck games.
- B needs timestamps, skip action, and notifications.
- C needs scheduled checks or page-triggered expiration.
- D needs missed-turn counters and settlement rules for forfeited players.

Selected option: B. Gentle expiration: after a configurable time, others can skip the turn.

### ADR-020: Random Challenge Timing

Status: Accepted

Decision needed:

When should random challenges be added?

Options:

- A. MVP includes random challenges.
- B. Add after friend MVP, once turn expiration and reward caps are live.
- C. Add only as seasonal/tournament queue.
- D. Never add random challenges; keep the feature friend/social only.

Original recommendation:

Choose B.

Why this is a real decision:

Random challenges can make the game feel alive, but they also turn every weak policy into a public problem.

Implementation impact:

- A requires matchmaking, abuse controls, and inactivity handling immediately.
- B keeps scope sane while preserving the roadmap.
- C makes random play more special and easier to message.
- D focuses the feature on known social ties.

Selected option: B. Add random challenges after friend MVP, once turn expiration and reward caps are live.

### ADR-021: Seasonal Progression Layer

Status: Accepted

Decision needed:

What is the long-term progression fantasy after the MVP?

Options:

- A. Cash-Dosh winnings only.
- B. Seasonal board points and leaderboards.
- C. Seasonal points, trophies, cosmetics, and faction/team progress.
- D. Persistent player rank/ELO focused on competitive board-game skill.

Original recommendation:

Choose C for the full game path.

Why this is a real decision:

This decides whether the feature becomes an economy faucet, a social seasonal event, or a competitive ladder.

Implementation impact:

- A is simple but shallow and farm-sensitive.
- B adds event energy but may still feel abstract.
- C fits the Altador Cup inspiration and keeps raw currency modest.
- D requires matchmaking quality, ranking rules, and competitive integrity.

Selected option: C. Seasonal points, trophies, cosmetics, and faction/team progress.

### ADR-022: Regional Board Integration

Status: Accepted

Decision needed:

How deeply should board content connect to Harmontide regions?

Options:

- A. Generic board game detached from world regions.
- B. One generic MVP board, then regional boards later.
- C. First board is already tied to a Harmontide country/city.
- D. Every country gets a board route as part of launch.

Original recommendation:

Choose C if a fitting first region is obvious. Choose B if theme choice would slow implementation.

Why this is a real decision:

Regional integration can make the feature feel native to Harmontide, but it increases content work and may affect where the game lives in navigation.

Implementation impact:

- A is fastest but less distinctive.
- B keeps MVP flexible.
- C gives the first release identity and can connect to map exploration.
- D is far too much content for MVP.

Selected option: C. First board is already tied to a Harmontide country/city.

### ADR-023: Admin And Debug Tooling

Status: Accepted

Decision needed:

How much admin tooling is required before beta?

Options:

- A. No admin tooling; inspect DB manually.
- B. Read-only admin/debug match inspector.
- C. Inspector plus safe actions: cancel match, force skip, mark expired.
- D. Full moderation console with reward reversal tools.

Original recommendation:

Choose C for beta if Cash-Dosh rewards are enabled. Choose B for no-reward prototype.

Why this is a real decision:

Async state machines get stuck in ways that are hard to diagnose from player reports. Cash-Dosh settlement raises the cost of mistakes.

Implementation impact:

- A is fastest but painful once real users play.
- B needs admin route and permission checks.
- C needs carefully scoped write actions and event logging.
- D requires broader policy and should wait.

Selected option: C. Inspector plus safe actions: cancel match, force skip, mark expired.

### ADR-024: Content Admin Timing

Status: Accepted

Decision needed:

When should a board-content editor be built?

Options:

- A. Before MVP.
- B. After MVP rules stabilize.
- C. Only after multiple boards exist.
- D. Never; keep board content code/config managed.

Original recommendation:

Choose B or C.

Why this is a real decision:

A content editor can save time later but can also consume the time needed to discover what the board schema should actually be.

Implementation impact:

- A slows MVP but helps non-developer content work.
- B lets the editor reflect proven rules.
- C avoids premature tooling and works if boards remain developer-authored for a while.
- D is fine for a small project but may limit scale.

Selected option: B. Build content admin after MVP rules stabilize.

## Suggested MVP Acceptance Criteria

- A logged-in user can create a friend board-game match and invite 1-3 friends.
- Invited friends can accept or decline.
- A friend match starts only when every invited player accepts.
- A logged-in user can join and leave the random challenge queue after the private MVP guardrails are live.
- Queued users can be matched into a random board-game match.
- Only the current player can act.
- A current player can roll, choose a legal branch/path when needed, move, resolve tile effects, optionally buy/upgrade, and end turn.
- Rent and event losses never reduce board cash below zero.
- The next player receives an in-app notification when it is their turn.
- Expired turns can be gently skipped by other players after the configured grace period.
- All players can read recent turn history.
- The match ends after 6 rounds per player.
- Final standings are calculated from net worth.
- Seasonal points are awarded as the primary reward.
- Small capped Cash-Dosh rewards are inserted exactly once through `currency_ledger`.
- Abandoned or incomplete matches do not pay rewards.
- Daily and weekly Cash-Dosh caps plus minimum participation checks are enforced.
- Duplicate browser submissions do not duplicate rolls, path choices, purchases, upgrades, skips, turn endings, or payouts.
- Admins can inspect matches and safely cancel, force skip, or mark expired.

## Suggested File/Module Plan

- `sql/board_game.sql`: schema migration.
- `lib/board_game.php`: core match and turn engine.
- `lib/board_game_rewards.php`: reward settlement and caps.
- `lib/board_game_matchmaking.php`: random challenge queue and match creation.
- `lib/board_game_seasons.php`: seasonal points, trophies, cosmetics hooks, and faction/team progress hooks.
- `lib/notifications.php`: reusable notification helpers.
- `data/board_game_boards/redwind_harbor_circuit.json`: first regional board definition.
- `data/board_game_rewards/mvp_seasonal_small_cash_v1.json`: first reward profile.
- `pages/board-game.php`: match list, create form, invite inbox.
- `pages/board-game-match.php`: match board and turn actions.
- `pages/board-game-queue.php`: random challenge join/leave flow, or a section inside `pages/board-game.php`.
- `pages/board-game-action.php`: POST handler or route target if current routing prefers page actions.
- `pages/board-game-admin.php`: debug view with safe cancel, force skip, and expire actions.
- layout notification entry point: unread count/panel for `user_notifications`.

## Open Questions For Approval

1. What should the turn-expiration grace period be before other players can skip?
2. Should random challenges wait for exactly 4 queued players, or start with 2 and fill to 4 only when available?
3. What admin role/permission should be required for match cancel, force skip, and expire actions?
4. Should trophies/cosmetics be purely seasonal, or should some be permanent milestone rewards?

## Recommended First Implementation Slice

1. Load `data/board_game_boards/redwind_harbor_circuit.json` and validate the tile graph.
2. Add schema for matches, players, turns, action keys, properties, invites, events, notifications, seasons, season points, and random queue.
3. Build `lib/board_game.php` with deterministic movement, branching, rent capping, and settlement fixtures.
4. Add reusable notification helpers and layout unread count.
5. Add friend invite page and match view with all-invite acceptance.
6. Add seasonal point settlement and small capped Cash-Dosh ledger settlement.
7. Add gentle skip handling and admin debug/safe-action view.
8. Play several friend/private matches and tune the economy.
9. Add random challenge queue once reward caps and turn expiration are verified.
