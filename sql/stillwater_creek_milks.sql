-- Stillwater Creek milking minigame reward items.
-- Page: /pages/harmontide-milking-minigame.php
-- Region context: United free Republic of Borealia / Stillwater Creek.
-- Safe to rerun: item rows are inserted only when the item_id is absent.
-- Milk item IDs match sql/ch53461_xyzpetsthing.sql:
--   356 Jackalope Milk
--   357 Death Milk
--   358 Fury Milk
--   359 Lich Milk
--   360 Jack-o-Lantern Milk
--   361 Pestilence Milk
--   90 Cynthia Milk

CREATE TABLE IF NOT EXISTS `daily_stillwater_milking_runs` (
  `user_id` bigint UNSIGNED NOT NULL,
  `run_date` date NOT NULL,
  `creature_id` varchar(40) NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `completed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `run_date`),
  KEY `ix_daily_stillwater_milking_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `items`
  (`item_id`, `item_name`, `item_description`, `base_price`, `rarity_id`, `category_id`, `max_stack`, `tradable`, `replenish`)
SELECT seed.`item_id`, seed.`item_name`, seed.`item_description`, seed.`base_price`, rar.`rarity_id`, cat.`category_id`, seed.`max_stack`, seed.`tradable`, seed.`replenish`
FROM (
  SELECT 90 AS `item_id`, 'Cynthia Milk' AS `item_name`, 'A creamy bottle from Dairy Centaur Cynthia, labeled for the Stillwater counter and sealed with a blue ribbon stamp.' AS `item_description`, 35.00 AS `base_price`, 'Worth dirt' AS `rarity_name`, 'Food' AS `category_name`, 99 AS `max_stack`, 1 AS `tradable`, 75 AS `replenish`
  UNION ALL SELECT 356, 'Jackalope Milk', 'A warm, sweet cream from a Stillwater jackalope. It smells faintly of clover, fence dust, and bad road directions.', 34.00, 'Uncommon', 'Food', 20, 1, 42
  UNION ALL SELECT 357, 'Death Milk', 'A pale, silent milk from a URB Death. The bottle is always cold and always heavier than it looks.', 88.00, 'Rare', 'Food', 10, 1, 66
  UNION ALL SELECT 358, 'Fury Milk', 'A hot orange milk from a URB Fury. Shake carefully; it has opinions about containment.', 76.00, 'Rare', 'Food', 10, 1, 58
  UNION ALL SELECT 359, 'Lich Milk', 'A violet milk from a URB Lich, labeled with three warnings and one small apology from the clerk.', 94.00, 'Rare', 'Food', 10, 1, 62
  UNION ALL SELECT 360, 'Jack-o-Lantern Milk', 'A golden autumn milk from a URB Jack-o-Lantern. Good in pies, soups, and decisions made after sundown.', 48.00, 'Uncommon', 'Food', 20, 1, 50
  UNION ALL SELECT 361, 'Pestilence Milk', 'A greenish Stillwater bottle sealed twice over. Restorative, technically, but nobody agrees on what it restores.', 112.00, 'SR', 'Food', 5, 1, 72
) seed
JOIN `item_rarities` rar ON rar.`rarity_name` = seed.`rarity_name`
JOIN `item_categories` cat ON cat.`category_name` = seed.`category_name`
WHERE NOT EXISTS (
  SELECT 1
  FROM `items` i
  WHERE i.`item_id` = seed.`item_id`
);

ALTER TABLE `items` AUTO_INCREMENT = 362;
