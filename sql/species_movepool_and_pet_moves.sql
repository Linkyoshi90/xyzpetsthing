-- ---------------------------------------------------------------------------
-- Learnable movepools per species + persistent per-creature movesets.
--
-- species_movepool  - which moves a species CAN learn, gated by level.
--                     The Yara Dojo lists rows where learnt_at_level <= the
--                     creature's current level (minus moves already known).
-- pet_instance_moves - the moves a specific creature actually knows, one row
--                     per moveslot (1-4). Battles should prefer these rows
--                     and fall back to the generated element-affinity set
--                     for creatures that never visited the dojo.
--
-- NOTE (live deploy): the DB user on the live host is DML-only. Both CREATE
-- TABLE statements must be applied by hand with an admin account; the seed
-- INSERTs below can then run as the normal user. Safe to re-run (IGNORE +
-- IF NOT EXISTS throughout).
-- ---------------------------------------------------------------------------

-- 1) Schema ------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `species_movepool` (
  `species_id` smallint UNSIGNED NOT NULL,
  `move_id` bigint UNSIGNED NOT NULL,
  `learnt_at_level` int UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`species_id`, `move_id`),
  KEY `ix_species_movepool_move` (`move_id`),
  KEY `ix_species_movepool_level` (`species_id`, `learnt_at_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `pet_instance_moves` (
  `pet_instance_id` bigint UNSIGNED NOT NULL,
  `slot` tinyint UNSIGNED NOT NULL,
  `move_id` bigint UNSIGNED NOT NULL,
  `learned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pet_instance_id`, `slot`),
  UNIQUE KEY `uq_pet_instance_move` (`pet_instance_id`, `move_id`),
  KEY `ix_pet_instance_moves_move` (`move_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 2) Seed species_movepool ---------------------------------------------------
-- Derived, not hand-written, so every species gets a sensible pool:
--   a) moves whose element matches one of the species' elements
--   b) Vulgaris (neutral, element 1) moves for everybody
--   c) the species' signature moves from species_moves
-- Signature moves stay exclusive: (a) and (b) skip anything reserved in
-- species_moves so e.g. Bonechill never leaks to every Cold species.
--
-- learnt_at_level scales with move power: ~40 power unlocks at lv 2, ~60 at
-- lv 8, ~90 at lv 18. Status moves (power NULL) count as 50 -> lv 5.

INSERT IGNORE INTO `species_movepool` (`species_id`, `move_id`, `learnt_at_level`)
SELECT se.`species_id`,
       m.`move_id`,
       GREATEST(1, ROUND((COALESCE(m.`power`, 50) - 35) / 3))
  FROM `species_elements` se
  JOIN `moves` m ON m.`element_id` = se.`element_id`
 WHERE m.`move_id` NOT IN (SELECT `move_id` FROM `species_moves`);

INSERT IGNORE INTO `species_movepool` (`species_id`, `move_id`, `learnt_at_level`)
SELECT ps.`species_id`,
       m.`move_id`,
       GREATEST(1, ROUND((COALESCE(m.`power`, 50) - 35) / 3))
  FROM `pet_species` ps
  JOIN `moves` m ON m.`element_id` = 1
 WHERE m.`move_id` NOT IN (SELECT `move_id` FROM `species_moves`);

INSERT IGNORE INTO `species_movepool` (`species_id`, `move_id`, `learnt_at_level`)
SELECT sm.`species_id`,
       sm.`move_id`,
       GREATEST(1, ROUND((COALESCE(m.`power`, 50) - 35) / 3))
  FROM `species_moves` sm
  JOIN `moves` m ON m.`move_id` = sm.`move_id`;
