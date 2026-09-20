-- ---------------------------------------------------------------------------
-- Battle ailment cure items.
--
-- Convention (mirrors the sickness cures in crescent_caliphate_apothecary.sql):
--   item_effects: effect_type = 'cure', target_stat = 'status_<ailment>'
--   where <ailment> is one of: poison, venom, burn, paralysis, freeze,
--   sleep, rage - or 'status_all' for a cure-everything item.
--   amount is unused for these rows (kept at 1).
-- The unique key uq_item_effects_item_type_stat allows one row per
-- target_stat, so multi-cure items simply carry several status_* rows.
--
-- Battle ailments only exist inside a battle (client-side state), so these
-- items act through the battle item menu; the petting/inventory flow will
-- correctly report "cannot benefit right now" outside of battle.
--
-- Stocked at the Moon-and-Mortar Apothecary (shop_id 31).
-- Run after sql/crescent_caliphate_apothecary.sql. Safe to rerun.
-- ---------------------------------------------------------------------------

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
  SELECT 'Antidote Sap' AS `item_name`,
         'Bitter tree sap that neutralizes poison coursing through a battling creature.' AS `item_description`,
         45.00 AS `base_price`,
         'Worth dirt' AS `rarity_name`,
         'Potion' AS `category_name`,
         20 AS `max_stack`,
         1 AS `tradable`
  UNION ALL
  SELECT 'Venom Extractor Kit',
         'A field kit of cupping bulbs and charcoal wads that draws out venom and lesser poisons alike.',
         120.00, 'Uncommon', 'Potion', 10, 1
  UNION ALL
  SELECT 'Burn Salve',
         'A cool aloe salve that soothes burns and lets a creature swing at full strength again.',
         60.00, 'Worth dirt', 'Potion', 20, 1
  UNION ALL
  SELECT 'Nerve Balm',
         'A tingling eucalyptus balm that loosens paralyzed muscles mid-battle.',
         60.00, 'Worth dirt', 'Potion', 20, 1
  UNION ALL
  SELECT 'Thawing Ember Jar',
         'A sealed jar holding a single warm ember. Cracked open, it melts battle-frost in moments.',
         70.00, 'Uncommon', 'Potion', 10, 1
  UNION ALL
  SELECT 'Wake-Up Chime',
         'A tiny brass chime whose clear note snaps a sleeping creature back to its senses.',
         55.00, 'Worth dirt', 'Potion', 20, 1
  UNION ALL
  SELECT 'Calmleaf Tea',
         'A mellow brewed tea that settles a raging creature and clears its clouded aim.',
         55.00, 'Worth dirt', 'Potion', 20, 1
  UNION ALL
  SELECT 'Panacea Leaf',
         'A rare silver-veined leaf said to cure every battle ailment known to keepers.',
         240.00, 'Rare', 'Potion', 5, 1
) seed
JOIN `item_rarities` rar ON rar.`rarity_name` = seed.`rarity_name`
JOIN `item_categories` cat ON cat.`category_name` = seed.`category_name`
WHERE NOT EXISTS (
  SELECT 1
  FROM `items` existing
  WHERE existing.`item_name` = seed.`item_name`
);

INSERT INTO `item_effects` (`item_id`, `effect_type`, `target_stat`, `amount`)
SELECT i.`item_id`, 'cure', seed.`target_stat`, 1
FROM (
  SELECT 'Antidote Sap' AS `item_name`, 'status_poison' AS `target_stat`
  UNION ALL SELECT 'Venom Extractor Kit', 'status_venom'
  UNION ALL SELECT 'Venom Extractor Kit', 'status_poison'
  UNION ALL SELECT 'Burn Salve',          'status_burn'
  UNION ALL SELECT 'Nerve Balm',          'status_paralysis'
  UNION ALL SELECT 'Thawing Ember Jar',   'status_freeze'
  UNION ALL SELECT 'Wake-Up Chime',       'status_sleep'
  UNION ALL SELECT 'Calmleaf Tea',        'status_rage'
  UNION ALL SELECT 'Panacea Leaf',        'status_all'
) seed
JOIN `items` i ON i.`item_name` = seed.`item_name`
ON DUPLICATE KEY UPDATE
  `amount` = VALUES(`amount`);

INSERT INTO `shop_inventory` (`shop_id`, `item_id`, `price`, `stock`)
SELECT 31,
       i.`item_id`,
       seed.`price`,
       seed.`stock`
FROM (
  SELECT 'Antidote Sap' AS `item_name`, NULL AS `price`, 40 AS `stock`
  UNION ALL SELECT 'Venom Extractor Kit', NULL, 16
  UNION ALL SELECT 'Burn Salve', NULL, 32
  UNION ALL SELECT 'Nerve Balm', NULL, 32
  UNION ALL SELECT 'Thawing Ember Jar', NULL, 20
  UNION ALL SELECT 'Wake-Up Chime', NULL, 32
  UNION ALL SELECT 'Calmleaf Tea', NULL, 32
  UNION ALL SELECT 'Panacea Leaf', NULL, 6
) seed
JOIN `items` i ON i.`item_name` = seed.`item_name`
ON DUPLICATE KEY UPDATE
  `price` = VALUES(`price`),
  `stock` = VALUES(`stock`);
