-- Adds optional default color slugs to wild random encounters.
-- Safe to rerun. NULL or invalid slugs fall back to a random usable species color in battle.

SET @schema_name := DATABASE();

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

UPDATE `random_encounters` re
JOIN `regions` r
  ON r.`region_id` = re.`region_id`
JOIN `pet_species` ps
  ON ps.`species_id` = re.`species_id`
 AND ps.`region_id` = re.`region_id`
SET re.`default_color_slug` = CASE ps.`species_name`
  WHEN 'Mary' THEN 'blue'
  WHEN 'Bloodhound' THEN 'brown'
  ELSE re.`default_color_slug`
END
WHERE r.`region_name` = 'Stillwater Creek'
  AND ps.`species_name` IN ('Mary', 'Bloodhound')
  AND (re.`default_color_slug` IS NULL OR re.`default_color_slug` = '');
