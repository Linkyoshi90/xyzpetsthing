-- Stillwater Hollow shop, items, and inventory seed.
-- Shop page: /pages/stcr-shop.php
-- Region 22 = United free Republic of Borealia.

INSERT INTO `shops`
  (`shop_id`, `shop_name`, `region_id`, `is_npc`, `restock_every_minutes`, `last_restok_at`)
VALUES
  (30, 'Stillwater Crossroads Supply', 22, 1, 120, NULL)
ON DUPLICATE KEY UPDATE
  `shop_name` = VALUES(`shop_name`),
  `region_id` = VALUES(`region_id`),
  `is_npc` = VALUES(`is_npc`),
  `restock_every_minutes` = VALUES(`restock_every_minutes`);

INSERT INTO `items`
  (`item_name`, `item_description`, `base_price`, `rarity_id`, `category_id`, `max_stack`, `tradable`, `replenish`)
SELECT seed.`item_name`, seed.`item_description`, seed.`base_price`, rar.`rarity_id`, cat.`category_id`, seed.`max_stack`, seed.`tradable`, seed.`replenish`
FROM (
  SELECT 'Stillwater Well Filter' AS `item_name`, 'A screw-on ceramic filter sold with a stern warning not to trust private wells.' AS `item_description`, 42.00 AS `base_price`, 'Uncommon' AS `rarity_name`, 'Misc' AS `category_name`, 20 AS `max_stack`, 1 AS `tradable`, 1 AS `replenish`
  UNION ALL SELECT 'Sealed Creekwater Tin', 'A factory-sealed tin of clean water from outside the hollow. The label is newer than anything else on the shelf.', 18.00, 'Worth dirt', 'Food', 99, 1, 30
  UNION ALL SELECT 'Porch Salt Packet', 'Coarse salt wrapped in wax paper and tucked above doorframes by locals who will not explain why.', 12.00, 'Worth dirt', 'Misc', 99, 1, 1
  UNION ALL SELECT 'Barn Latch Set', 'A heavy latch and two mismatched screws, meant for doors that need to stay shut after sundown.', 36.00, 'Uncommon', 'Misc', 50, 1, 1
  UNION ALL SELECT 'Tarnished Bell Charm', 'A small charm shaped like the cracked church bell, cold to the touch even in a warm pocket.', 72.00, 'Rare', 'Misc', 20, 1, 1
  UNION ALL SELECT 'Lead-Lined Rain Cape', 'A stiff rain cape with a thin protective lining and a smell of old rubber.', 118.00, 'SR', 'Wearable', 10, 1, 1
  UNION ALL SELECT 'Moth-Eaten Nursery Rhyme', 'A brittle booklet of local children songs with several names scratched out in pencil.', 55.00, 'Rare', 'Book', 10, 1, 1
  UNION ALL SELECT 'Bitter Well Tonic', 'A medicinal tonic brewed to settle the stomach after bad water, bad roads, or bad decisions.', 28.00, 'Uncommon', 'Potion', 20, 1, 45
  UNION ALL SELECT 'Roadside Fuel Can', 'A red fuel can with a dented cap and enough fumes to get you back to the repaired roads.', 64.00, 'Rare', 'Misc', 10, 1, 1
) seed
JOIN `item_rarities` rar ON rar.`rarity_name` = seed.`rarity_name`
JOIN `item_categories` cat ON cat.`category_name` = seed.`category_name`
WHERE NOT EXISTS (
  SELECT 1
  FROM `items` i
  WHERE i.`item_name` = seed.`item_name`
);

INSERT INTO `shop_inventory` (`shop_id`, `item_id`, `price`, `stock`)
SELECT 30, i.`item_id`, seed.`price`, seed.`stock`
FROM (
  SELECT 'Stillwater Well Filter' AS `item_name`, NULL AS `price`, 26 AS `stock`
  UNION ALL SELECT 'Sealed Creekwater Tin', NULL, 70
  UNION ALL SELECT 'Porch Salt Packet', NULL, 80
  UNION ALL SELECT 'Barn Latch Set', NULL, 34
  UNION ALL SELECT 'Tarnished Bell Charm', NULL, 12
  UNION ALL SELECT 'Lead-Lined Rain Cape', NULL, 6
  UNION ALL SELECT 'Moth-Eaten Nursery Rhyme', NULL, 8
  UNION ALL SELECT 'Bitter Well Tonic', NULL, 36
  UNION ALL SELECT 'Roadside Fuel Can', NULL, 14
) seed
JOIN `items` i ON i.`item_name` = seed.`item_name`
ON DUPLICATE KEY UPDATE
  `price` = VALUES(`price`),
  `stock` = VALUES(`stock`);

ALTER TABLE `shops` AUTO_INCREMENT = 31;
