-- Adds table-backed consumable item effects for pet stats.
-- Run after the base schema and existing item data are present.

CREATE TABLE IF NOT EXISTS `item_effects` (
  `item_effect_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` bigint UNSIGNED NOT NULL,
  `effect_type` varchar(32) NOT NULL DEFAULT 'increase',
  `target_stat` varchar(32) NOT NULL,
  `amount` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_effect_id`),
  UNIQUE KEY `uq_item_effects_item_type_stat` (`item_id`, `effect_type`, `target_stat`),
  KEY `ix_item_effects_item` (`item_id`),
  CONSTRAINT `fk_item_effects_item`
    FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Existing Potion-category items become health-restoring stat items.
INSERT INTO `item_effects` (`item_id`, `effect_type`, `target_stat`, `amount`)
SELECT i.`item_id`, 'increase', 'hp_current', GREATEST(0, i.`replenish`)
  FROM `items` i
WHERE i.`category_id` = 3
   AND i.`replenish` > 0
   AND i.`item_name` NOT IN (
       'Vitality Tonic',
       'Power Tonic',
       'Guard Tonic',
       'Guard Tonic X',
       'Quickstep Tonic',
       'Brightmind Tonic',
       'Cheer Tonic'
   )
ON DUPLICATE KEY UPDATE
  `amount` = VALUES(`amount`);

-- New stat booster items. Item ids are intentionally not explicit.
INSERT INTO `items`
  (`item_name`, `item_description`, `base_price`, `rarity_id`, `category_id`, `max_stack`, `tradable`, `replenish`)
SELECT seed.`item_name`,
       seed.`item_description`,
       seed.`base_price`,
       rar.`rarity_id`,
       cat.`category_id`,
       seed.`max_stack`,
       seed.`tradable`,
       seed.`replenish`
  FROM (
    SELECT 'Vitality Tonic' AS `item_name`, 'A warm red tonic that permanently raises a creature''s max HP.' AS `item_description`, 140.00 AS `base_price`, 'Uncommon' AS `rarity_name`, 20 AS `max_stack`, 1 AS `tradable`, 5 AS `replenish`
    UNION ALL SELECT 'Power Tonic', 'A peppery tonic that permanently raises a creature''s attack.', 160.00, 'Uncommon', 20, 1, 1
    UNION ALL SELECT 'Guard Tonic', 'A mineral-rich tonic that permanently raises a creature''s defense.', 160.00, 'Uncommon', 20, 1, 1
    UNION ALL SELECT 'Guard Tonic X', 'A dense mineral tonic that permanently raises a creature''s defense even further.', 320.00, 'Rare', 20, 1, 3
    UNION ALL SELECT 'Quickstep Tonic', 'A sparkling tonic that permanently raises a creature''s initiative.', 160.00, 'Uncommon', 20, 1, 1
    UNION ALL SELECT 'Brightmind Tonic', 'A clear tonic that permanently raises a creature''s intelligence.', 150.00, 'Uncommon', 20, 1, 1
    UNION ALL SELECT 'Cheer Tonic', 'A sweet tonic that raises a creature''s happiness.', 90.00, 'Worth dirt', 20, 1, 10
  ) seed
  JOIN `item_categories` cat ON cat.`category_name` = 'Potion'
  JOIN `item_rarities` rar ON rar.`rarity_name` = seed.`rarity_name`
 WHERE NOT EXISTS (
       SELECT 1
         FROM `items` existing
        WHERE existing.`item_name` = seed.`item_name`
 );

INSERT INTO `item_effects` (`item_id`, `effect_type`, `target_stat`, `amount`)
SELECT i.`item_id`, 'increase', seed.`target_stat`, seed.`amount`
  FROM (
    SELECT 'Vitality Tonic' AS `item_name`, 'hp_max' AS `target_stat`, 5 AS `amount`
    UNION ALL SELECT 'Power Tonic', 'atk', 1
    UNION ALL SELECT 'Guard Tonic', 'def', 1
    UNION ALL SELECT 'Guard Tonic X', 'def', 3
    UNION ALL SELECT 'Quickstep Tonic', 'initiative', 1
    UNION ALL SELECT 'Brightmind Tonic', 'intelligence', 1
    UNION ALL SELECT 'Cheer Tonic', 'happiness', 10
  ) seed
  JOIN `items` i ON i.`item_name` = seed.`item_name`
ON DUPLICATE KEY UPDATE
  `amount` = VALUES(`amount`);
