-- Stillwater Creek random encounter migration and seed.
-- Safe to rerun. Existing rows are left alone unless a time column needs to be normalized.

SET @schema_name := DATABASE();

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `random_encounters` ADD COLUMN `time_from` DATETIME NOT NULL DEFAULT ''2026-01-01 00:00:00'' AFTER `species_id`',
    'ALTER TABLE `random_encounters` MODIFY COLUMN `time_from` DATETIME NOT NULL'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'random_encounters'
    AND COLUMN_NAME = 'time_from'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `random_encounters` ADD COLUMN `time_until` DATETIME NOT NULL DEFAULT ''2036-12-31 23:59:59'' AFTER `time_from`',
    'ALTER TABLE `random_encounters` MODIFY COLUMN `time_until` DATETIME NOT NULL'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'random_encounters'
    AND COLUMN_NAME = 'time_until'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `random_encounters` ADD INDEX `ix_re_window` (`time_from`, `time_until`)',
    'SELECT 1'
  )
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'random_encounters'
    AND INDEX_NAME = 'ix_re_window'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `random_encounters` ADD COLUMN `default_color_slug` varchar(50) NULL AFTER `species_id`',
    'SELECT 1'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'random_encounters'
    AND COLUMN_NAME = 'default_color_slug'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT INTO `regions` (`region_name`)
SELECT 'Stillwater Creek'
WHERE NOT EXISTS (
  SELECT 1
  FROM `regions`
  WHERE `region_name` = 'Stillwater Creek'
);

INSERT INTO `pet_species`
  (`species_name`, `region_id`, `base_hp`, `base_atk`, `base_def`, `base_init`)
SELECT seed.`species_name`, r.`region_id`, seed.`base_hp`, seed.`base_atk`, seed.`base_def`, seed.`base_init`
FROM (
  SELECT 'Bloodhound' AS `species_name`, 135 AS `base_hp`, 15 AS `base_atk`, 5 AS `base_def`, 18 AS `base_init`
  UNION ALL SELECT 'Mary', 100, 8, 8, 1
) seed
JOIN (
  SELECT `region_id`
  FROM `regions`
  WHERE `region_name` = 'Stillwater Creek'
  ORDER BY `region_id`
  LIMIT 1
) r
WHERE NOT EXISTS (
  SELECT 1
  FROM `pet_species` ps
  WHERE ps.`species_name` = seed.`species_name`
    AND ps.`region_id` = r.`region_id`
);

INSERT INTO `species_elements` (`species_id`, `element_id`)
SELECT ps.`species_id`, e.`element_id`
FROM (
  SELECT 'Bloodhound' AS `species_name`, 'Malus' AS `element_name`
  UNION ALL SELECT 'Bloodhound', 'Vulgaris'
  UNION ALL SELECT 'Mary', 'Malus'
  UNION ALL SELECT 'Mary', 'Ethereal'
) seed
JOIN (
  SELECT `region_id`
  FROM `regions`
  WHERE `region_name` = 'Stillwater Creek'
  ORDER BY `region_id`
  LIMIT 1
) r
JOIN `pet_species` ps
  ON ps.`species_name` = seed.`species_name`
 AND ps.`region_id` = r.`region_id`
JOIN `elements` e
  ON e.`element_name` = seed.`element_name`
WHERE NOT EXISTS (
  SELECT 1
  FROM `species_elements` se
  WHERE se.`species_id` = ps.`species_id`
    AND se.`element_id` = e.`element_id`
);

INSERT INTO `random_encounters`
  (`region_id`, `species_id`, `default_color_slug`, `time_from`, `time_until`, `encounter_chance`)
SELECT
  r.`region_id`,
  ps.`species_id`,
  planned.`default_color_slug`,
  planned.`time_from`,
  planned.`time_until`,
  planned.`encounter_chance`
FROM (
  SELECT 'Bloodhound' AS `species_name`, 'brown' AS `default_color_slug`, TIMESTAMP('2026-06-15 00:00:00') AS `time_from`, TIMESTAMP('2036-12-31 23:59:59') AS `time_until`, 45.00 AS `encounter_chance`
  UNION ALL SELECT 'Mary', 'blue', TIMESTAMP('2026-06-15 00:00:00'), TIMESTAMP('2036-12-31 23:59:59'), 6.00
  -- Optional planned town creatures. Add matching pet_species rows later, then rerun this file to seed them.
  UNION ALL SELECT 'Wellwretch', NULL, TIMESTAMP('2026-06-15 18:00:00'), TIMESTAMP('2036-12-31 23:59:59'), 18.00
  UNION ALL SELECT 'Bell-Tongue', NULL, TIMESTAMP('2026-06-15 20:00:00'), TIMESTAMP('2036-12-31 23:59:59'), 14.00
  UNION ALL SELECT 'Sluice Saint', NULL, TIMESTAMP('2026-06-15 22:00:00'), TIMESTAMP('2036-12-31 23:59:59'), 10.00
) planned
JOIN (
  SELECT `region_id`
  FROM `regions`
  WHERE `region_name` = 'Stillwater Creek'
  ORDER BY `region_id`
  LIMIT 1
) r
JOIN `pet_species` ps
  ON ps.`species_name` = planned.`species_name`
 AND ps.`region_id` = r.`region_id`
WHERE NOT EXISTS (
  SELECT 1
  FROM `random_encounters` existing
  WHERE existing.`region_id` = r.`region_id`
    AND existing.`species_id` = ps.`species_id`
    AND existing.`time_from` = planned.`time_from`
    AND existing.`time_until` = planned.`time_until`
);

UPDATE `random_encounters` re
JOIN (
  SELECT `region_id`
  FROM `regions`
  WHERE `region_name` = 'Stillwater Creek'
  ORDER BY `region_id`
  LIMIT 1
) r
  ON r.`region_id` = re.`region_id`
JOIN `pet_species` ps
  ON ps.`species_id` = re.`species_id`
 AND ps.`region_id` = re.`region_id`
SET re.`default_color_slug` = CASE ps.`species_name`
  WHEN 'Mary' THEN 'blue'
  WHEN 'Bloodhound' THEN 'brown'
  ELSE re.`default_color_slug`
END
WHERE ps.`species_name` IN ('Mary', 'Bloodhound')
  AND (re.`default_color_slug` IS NULL OR re.`default_color_slug` = '');
