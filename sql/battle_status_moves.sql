-- ---------------------------------------------------------------------------
-- Battle test content: stat-changing moves + status ailment moves
--
-- Ailments implemented client-side in assets/js/battle-minigame.js:
--   poison    - loses 12.5% max HP per turn, never drops below 1 HP
--   venom     - loses 25% max HP per turn, CAN faint the creature
--   burn      - poison tick (floor 1 HP) + attack halved while burned
--   paralyze  - speed x0.75, 25% chance to skip the turn
--   freeze    - cannot act; 10% thaw chance, +10% per turn,
--               +50% whenever a Heat (element 2) move hits the frozen creature
--   sleep     - cannot act; 10% wake chance, +10% per turn,
--               heals 5% max HP each turn spent asleep
--   rage      - attack x1.5, accuracy x0.5 until switched out
--
-- Stat-stage effect keys (Pokemon-style -6..+6 stages):
--   curse                     = self: Attack +1, Defense +1, Speed -1
--   atk|def|spd|acc_up_N      = self stat rises N stages
--   atk|def|spd|acc_down_N    = enemy stat falls N stages
--
-- NOTE (live deploy): the DB user on the live host is DML-only. The
-- CREATE TABLE below must be applied by hand with an admin account;
-- the INSERTs can then run as the normal user.
-- ---------------------------------------------------------------------------

-- 1) New moves --------------------------------------------------------------

INSERT IGNORE INTO `moves`
  (`move_key`, `move_name`, `element_id`, `category`, `power`, `accuracy_percent`, `pp`, `priority`, `target_mode`, `contact`, `crit_stage_bonus`, `effect_key`, `effect_chance_percent`, `min_hits`, `max_hits`)
VALUES
  -- Stat-changing moves (category 'status', no damage)
  ('curse',           'Curse',           15, 'status',  NULL, NULL,   10, 0, 'self',           0, 0, 'curse',      100.00, 1, 1),
  ('disarming_voice', 'Disarming Voice', 18, 'status',  NULL, 100.00, 25, 0, 'adjacent_enemy', 0, 0, 'atk_down_1', 100.00, 1, 1),
  ('flash',           'Flash',            4, 'status',  NULL, 100.00, 20, 0, 'adjacent_enemy', 0, 0, 'acc_down_1', 100.00, 1, 1),
  ('harden',          'Harden',           8, 'status',  NULL, NULL,   30, 0, 'self',           0, 0, 'def_up_1',   100.00, 1, 1),
  ('agility',         'Agility',          9, 'status',  NULL, NULL,   30, 0, 'self',           0, 0, 'spd_up_2',   100.00, 1, 1),

  -- Ailment-inflicting moves (100% proc for easy testing; tune later)
  ('poison_stab',     'Poison Stab',     11, 'physical', 50, 100.00, 20, 0, 'adjacent_enemy', 1, 0, 'poison',   100.00, 1, 1),
  ('venom_injection', 'Venom Injection', 11, 'physical', 40,  90.00, 15, 0, 'adjacent_enemy', 1, 0, 'venom',    100.00, 1, 1),
  ('frying_pan',      'Frying Pan',       2, 'physical', 55,  95.00, 15, 0, 'adjacent_enemy', 1, 0, 'burn',     100.00, 1, 1),
  ('spine_break',     'Spine Break',     12, 'physical', 60,  90.00, 15, 0, 'adjacent_enemy', 1, 0, 'paralyze', 100.00, 1, 1),
  ('bonechill',       'Bonechill',       13, 'special',  50,  95.00, 15, 0, 'adjacent_enemy', 0, 0, 'freeze',   100.00, 1, 1),
  ('hypnotherapy',    'Hypnotherapy',     9, 'status',  NULL,  80.00, 15, 0, 'adjacent_enemy', 0, 0, 'sleep',    100.00, 1, 1),
  ('taunt',           'Taunt',           15, 'status',  NULL, 100.00, 20, 0, 'adjacent_enemy', 0, 0, 'rage',     100.00, 1, 1);

-- 2) Per-species move pool --------------------------------------------------
-- Moves listed here are reserved for these species: the battle page removes
-- them from the shared element-affinity pool and deals them only to the
-- species mapped below (max 2 signature moves per creature, rest of the
-- moveset is filled from the shared pool as before).

CREATE TABLE IF NOT EXISTS `species_moves` (
  `species_id` bigint UNSIGNED NOT NULL,
  `move_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`species_id`, `move_id`),
  KEY `ix_species_moves_move` (`move_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3) Sparse assignments -----------------------------------------------------
-- Resolved by name/key so the file survives id drift between local and live.

INSERT IGNORE INTO `species_moves` (`species_id`, `move_id`)
SELECT ps.`species_id`, m.`move_id`
  FROM (
        SELECT 'Lamia'          AS species_name, 'poison_stab'     AS move_key
  UNION ALL SELECT 'Naga',           'poison_stab'
  UNION ALL SELECT 'Chupacabra',     'venom_injection'
  UNION ALL SELECT 'Girtablilu',     'venom_injection'
  UNION ALL SELECT 'Kitsune',        'frying_pan'
  UNION ALL SELECT 'Jack-o-Lantern', 'frying_pan'
  UNION ALL SELECT 'Centaur',        'spine_break'
  UNION ALL SELECT 'Golem',          'spine_break'
  UNION ALL SELECT 'Golem',          'harden'
  UNION ALL SELECT 'Yuki-Onna',      'bonechill'
  UNION ALL SELECT 'Banshee',        'disarming_voice'
  UNION ALL SELECT 'La Llorona',     'disarming_voice'
  UNION ALL SELECT 'Banshee',        'curse'
  UNION ALL SELECT 'Lich',           'curse'
  UNION ALL SELECT 'Succubus',       'hypnotherapy'
  UNION ALL SELECT 'Gandharva',      'hypnotherapy'
  UNION ALL SELECT 'Will-o-Wisp',    'flash'
  UNION ALL SELECT 'Demon',          'taunt'
  UNION ALL SELECT 'Ratatoskr',      'taunt'
  UNION ALL SELECT 'Ocelot',         'agility'
       ) pairs
  JOIN `pet_species` ps ON ps.`species_name` = pairs.species_name
  JOIN `moves` m        ON m.`move_key` = pairs.move_key;
