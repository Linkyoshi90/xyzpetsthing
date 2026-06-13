# Harmontide Circuit Phase 0

## Status

Phase 0 is created and ready to feed Phase 1 implementation.

## Product Lock

- Working game name: Harmontide Circuit.
- First board: Redwind Harbor Circuit.
- First region: Red Sun Commonwealth, Redwind.
- First map entry point: `?pg=rsc`.
- Match format: 2-4 players, 6 rounds per player, one six-sided die.
- Turn flow: roll, choose a legal branch if prompted, move, resolve tile, optionally buy or upgrade, end turn.
- Minigames: none in turn resolution.
- Economy model: board cash only during the match; Cash-Dosh only at settlement.
- Reward model: seasonal points first, small capped Cash-Dosh bonus second.
- Public random challenges: after private friend MVP guardrails are live.

## Artifacts

- Board definition: `data/board_game_boards/redwind_harbor_circuit.json`
- Reward tuning: `data/board_game_rewards/mvp_seasonal_small_cash_v1.json`
- Main plan: `board_game_mvp_implementation_plan.md`

## Board Rules Table

| Rule | Phase 0 Value |
| --- | --- |
| Players | 2-4 |
| Rounds | 6 per player |
| Board size | 14 tiles |
| Dice | 1d6 |
| Branches | Player chooses a legal outgoing edge when a tile has multiple exits |
| Starting board cash | 1500 |
| Pass start bonus | 200 |
| Loss floor | 0 board cash |
| Debt | No |
| Elimination | No |
| Auctions | No |
| Trading | No |
| Mortgages | No |
| Upgrades | 3 levels per property |
| Winner | Highest final net worth |

## Regional Board Graph

| Tile | Type | Routes |
| --- | --- | --- |
| 0 Glass Harbor Start | Start | 1 Busker Pitch |
| 1 Busker Pitch | Property | 2 Ferry Triangle |
| 2 Ferry Triangle | Branch | 3 Sun Shell Forecourt or 5 Breaker Bay Kiosk |
| 3 Sun Shell Forecourt | Property | 4 Harbor Lights |
| 4 Harbor Lights | Event | 8 Redwind Arch Climb |
| 5 Breaker Bay Kiosk | Property | 6 Beach Cleanup Fine |
| 6 Beach Cleanup Fine | Tax | 7 Bushline Reserve |
| 7 Bushline Reserve | Event | 8 Redwind Arch Climb |
| 8 Redwind Arch Climb | Property | 9 Laneway Crossing |
| 9 Laneway Crossing | Branch | 10 Outback Exchange Stall or 12 Wheel of Fate |
| 10 Outback Exchange Stall | Property | 11 Roadhouse Store |
| 11 Roadhouse Store | Property | 13 Glass Harbor Quay |
| 12 Wheel of Fate | Event | 13 Glass Harbor Quay |
| 13 Glass Harbor Quay | Property | 0 Glass Harbor Start |

## Economy Tuning

| Property | Price | Base Rent | Upgrade Cost | Level 1 Rent | Level 2 Rent | Level 3 Rent |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| Busker Pitch | 120 | 20 | 70 | 31 | 45 | 62 |
| Sun Shell Forecourt | 180 | 28 | 90 | 43 | 63 | 87 |
| Breaker Bay Kiosk | 160 | 24 | 80 | 37 | 54 | 74 |
| Redwind Arch Climb | 240 | 40 | 120 | 62 | 90 | 124 |
| Outback Exchange Stall | 220 | 36 | 110 | 56 | 81 | 112 |
| Roadhouse Store | 260 | 44 | 130 | 68 | 99 | 136 |
| Glass Harbor Quay | 300 | 52 | 150 | 81 | 117 | 161 |

## Event Tuning

| Event | Outcomes |
| --- | --- |
| Harbor Lights | 45% gain 90, 35% lose 60, 20% move forward 1 |
| Bushline Reserve | 40% gain 70, 35% lose 50, 25% move to Laneway Crossing |
| Wheel of Fate | 35% gain 120, 35% lose 80, 30% move to Glass Harbor Quay |
| Beach Cleanup Fine | Lose 90, capped at current board cash |

## Reward Tuning

| Placement | Seasonal Points | Cash-Dosh |
| --- | ---: | ---: |
| 1st | 100 | 80 |
| 2nd | 75 | 55 |
| 3rd | 55 | 35 |
| 4th | 40 | 25 |

Additional reward rules:

- Completion bonus: 20 seasonal points and 20 Cash-Dosh.
- Turn participation: 3 seasonal points per completed turn, capped at 18.
- Cash-Dosh daily cap: 250 per user from board-game settlements.
- Cash-Dosh weekly cap: 1000 per user from board-game settlements.
- Cap scope: board-game only, separate from score exchange.
- Minimum reward eligibility: persistent account, at least 4 completed turns, and at least 66% round completion.
- Temporary users may play unrewarded.

## Phase 1 Notes

- Add board loading from JSON before writing hard-coded tile logic.
- Treat branch selection as a first-class pending turn state.
- Use server-side weighted random event resolution and store the chosen outcome in `board_game_events`.
- Cap all rent, tax, and event losses at the payer's current board cash.
- Store reward profile keys on matches so future tuning does not rewrite historical settlements.
- Admin permission still needs an implementation hook because the current app does not expose a reusable role helper.
