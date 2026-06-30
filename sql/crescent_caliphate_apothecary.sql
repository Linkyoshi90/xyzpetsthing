-- Moon-and-Mortar Apothecary shop and pet sickness cures.
-- Page: /pages/cc-apothecary.php
-- Run after the base schema and sql/sickness.sql.
-- Safe to rerun: shop, items, inventory, effects, and sickness links are upserted.

INSERT INTO `shops`
  (`shop_id`, `shop_name`, `region_id`, `is_npc`, `restock_every_minutes`, `last_restok_at`)
SELECT 31,
       'Moon-and-Mortar Apothecary',
       r.`region_id`,
       1,
       90,
       NULL
FROM `regions` r
WHERE r.`region_name` = 'Crescent Caliphate'
LIMIT 1
ON DUPLICATE KEY UPDATE
  `shop_name` = VALUES(`shop_name`),
  `region_id` = VALUES(`region_id`),
  `is_npc` = VALUES(`is_npc`),
  `restock_every_minutes` = VALUES(`restock_every_minutes`);

INSERT INTO `items`
  (`item_name`, `item_description`, `base_price`, `rarity_id`, `category_id`, `max_stack`, `tradable`, `replenish`)
SELECT seed.`item_name`,
       seed.`item_description`,
       seed.`base_price`,
       rar.`rarity_id`,
       cat.`category_id`,
       seed.`max_stack`,
       seed.`tradable`,
       0
FROM (
  SELECT 'Warming Cardamom Draught' AS `item_name`,
         'A honeyed cardamom draught served warm to clear sniffles and restore a pet suffering from the Common Cold.' AS `item_description`,
         28.00 AS `base_price`,
         'Worth dirt' AS `rarity_name`,
         'Potion' AS `category_name`,
         20 AS `max_stack`,
         1 AS `tradable`
  UNION ALL
  SELECT 'Mint and Date Tonic',
         'A cooling mint tonic sweetened with dates and measured to settle the cramps of a Stomach Bug.',
         34.00,
         'Uncommon',
         'Potion',
         20,
         1
  UNION ALL
  SELECT 'Cleanwater Salt Remedy',
         'Mineral salts dissolved in clean fountain water to draw out the heat and aches of Mud Fever.',
         46.00,
         'Uncommon',
         'Potion',
         20,
         1
  UNION ALL
  SELECT 'Star-Saffron Elixir',
         'A carefully balanced saffron elixir that steadies magical currents disrupted by Mana Malaise.',
         78.00,
         'Rare',
         'Potion',
         10,
         1
  UNION ALL
  SELECT 'Wakeleaf Incense Tonic',
         'A smoky wakeleaf infusion bottled beneath a crescent moon to lift the lingering haze of Dream Fog.',
         62.00,
         'Rare',
         'Potion',
         10,
         1
) seed
JOIN `item_rarities` rar ON rar.`rarity_name` = seed.`rarity_name`
JOIN `item_categories` cat ON cat.`category_name` = seed.`category_name`
WHERE NOT EXISTS (
  SELECT 1
  FROM `items` existing
  WHERE existing.`item_name` = seed.`item_name`
);

INSERT INTO `shop_inventory` (`shop_id`, `item_id`, `price`, `stock`)
SELECT 31,
       i.`item_id`,
       seed.`price`,
       seed.`stock`
FROM (
  SELECT 'Warming Cardamom Draught' AS `item_name`, NULL AS `price`, 48 AS `stock`
  UNION ALL SELECT 'Mint and Date Tonic', NULL, 40
  UNION ALL SELECT 'Cleanwater Salt Remedy', NULL, 32
  UNION ALL SELECT 'Star-Saffron Elixir', NULL, 16
  UNION ALL SELECT 'Wakeleaf Incense Tonic', NULL, 20
) seed
JOIN `items` i ON i.`item_name` = seed.`item_name`
ON DUPLICATE KEY UPDATE
  `price` = VALUES(`price`),
  `stock` = VALUES(`stock`);

UPDATE `sickness` s
JOIN (
  SELECT 'Common Cold' AS `sick_name`, 'Warming Cardamom Draught' AS `item_name`
  UNION ALL SELECT 'Stomach Bug', 'Mint and Date Tonic'
  UNION ALL SELECT 'Mud Fever', 'Cleanwater Salt Remedy'
  UNION ALL SELECT 'Mana Malaise', 'Star-Saffron Elixir'
  UNION ALL SELECT 'Dream Fog', 'Wakeleaf Incense Tonic'
) seed ON seed.`sick_name` = s.`sick_name`
JOIN `items` i ON i.`item_name` = seed.`item_name`
SET s.`sick_cure_itemid` = i.`item_id`;

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

INSERT INTO `item_effects` (`item_id`, `effect_type`, `target_stat`, `amount`)
SELECT i.`item_id`,
       'cure',
       'sickness',
       s.`sick_id`
FROM `sickness` s
JOIN `items` i ON i.`item_id` = s.`sick_cure_itemid`
WHERE s.`sick_cure_itemid` IS NOT NULL
ON DUPLICATE KEY UPDATE
  `amount` = VALUES(`amount`);

ALTER TABLE `shops` AUTO_INCREMENT = 32;
