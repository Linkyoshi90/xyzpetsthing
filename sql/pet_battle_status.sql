-- ---------------------------------------------------------------------------
-- Persistent battle ailments.
--
-- One row per afflicted creature; absence of a row means healthy. Persisted
-- keys: poison, venom, burn, paralysis, freeze, sleep. Rage is volatile
-- (clears when the creature leaves the field) and fainting clears any
-- ailment, so neither ever reaches this table.
--
-- The battle page writes rows through the existing HP-sync snapshot and
-- reads them back when building the battle payload. Cure items clear rows
-- both from the battle item menu and from the petting/inventory flow.
--
-- NOTE (live deploy): the DB user on the live host is DML-only. This
-- CREATE TABLE must be applied by hand with an admin account.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `pet_battle_status` (
  `pet_instance_id` bigint UNSIGNED NOT NULL,
  `status_key` varchar(16) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pet_instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
