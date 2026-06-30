-- Pet sickness lookup data.
--
-- `pet_instances.sickness` currently uses 0 to mean healthy, so sickness IDs
-- begin at 1. No foreign key is added from pet_instances yet because its
-- existing healthy value (0) has no corresponding sickness row.
--
-- Cure item IDs are intentionally NULL until the matching items are created.
-- Run after the base schema so the referenced `items` table already exists.

CREATE TABLE IF NOT EXISTS `sickness` (
  `sick_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sick_name` varchar(100) NOT NULL,
  `sick_desc` text NOT NULL,
  `sick_cure_itemid` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`sick_id`),
  UNIQUE KEY `uq_sickness_name` (`sick_name`),
  KEY `ix_sickness_cure_item` (`sick_cure_itemid`),
  CONSTRAINT `fk_sickness_cure_item`
    FOREIGN KEY (`sick_cure_itemid`) REFERENCES `items` (`item_id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Safe to rerun: only missing sickness IDs/names are inserted.
INSERT INTO `sickness`
  (`sick_id`, `sick_name`, `sick_desc`, `sick_cure_itemid`)
SELECT seed.`sick_id`,
       seed.`sick_name`,
       seed.`sick_desc`,
       NULL
FROM (
  SELECT 1 AS `sick_id`,
         'Common Cold' AS `sick_name`,
         'A mild illness that leaves the pet tired, sniffly, and under the weather.' AS `sick_desc`
  UNION ALL
  SELECT 2,
         'Stomach Bug',
         'An upset stomach that reduces the pet''s appetite and makes activity uncomfortable.'
  UNION ALL
  SELECT 3,
         'Mud Fever',
         'A lingering fever commonly picked up after spending too long in cold, muddy places.'
  UNION ALL
  SELECT 4,
         'Mana Malaise',
         'A magical imbalance that causes fatigue, dizziness, and an unreliable sparkle.'
  UNION ALL
  SELECT 5,
         'Dream Fog',
         'A strange drowsiness that leaves the pet distracted and caught between waking and sleep.'
) seed
WHERE NOT EXISTS (
  SELECT 1
  FROM `sickness` existing
  WHERE existing.`sick_id` = seed.`sick_id`
     OR existing.`sick_name` = seed.`sick_name`
);
