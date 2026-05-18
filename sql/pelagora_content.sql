-- Pelagora map content: shops, item inserts, and daily activity ledgers.
-- Apply after the base database.sql dump.

INSERT INTO `regions` (`region_id`, `region_name`) VALUES
(28, 'Pelagora')
ON DUPLICATE KEY UPDATE `region_name` = VALUES(`region_name`);

INSERT INTO `items` (`item_id`, `item_name`, `item_description`, `base_price`, `rarity_id`, `category_id`, `max_stack`, `tradable`, `created_at`, `replenish`) VALUES
(223, 'Aquatic Breathing Kelp', 'A braided strip of sweet lagoon kelp. Pelagora divers chew it before shallow salvage runs and swear it buys one calmer breath.', 18.00, 2, 1, 99, 1, '2026-05-18 00:00:00', 35),
(224, 'Pelagoric Pearl Snack', 'A chewy pearl-colored sweet rolled in sea salt and citrus ash. It is served from shell trays on the ring-quay.', 44.00, 3, 1, 99, 1, '2026-05-18 00:00:00', 65),
(225, 'Pelagora Tide Compass', 'A brass-and-shell compass that points toward the safest stair whenever the Heart Mirror begins to rise.', 120.00, 4, 6, 10, 1, '2026-05-18 00:00:00', 1),
(226, 'Underwater Lantern Oil', 'Blue-green lamp oil that keeps burning under spray and gives flooded arches a soft ghostly glow.', 28.00, 2, 6, 20, 1, '2026-05-18 00:00:00', 1),
(227, 'Pelagoric Mirrorfish', 'A silver lagoon fish whose scales reflect old rooftops that are no longer above water.', 75.00, 3, 1, 99, 1, '2026-05-18 00:00:00', 90),
(228, 'Underwater Bellfish', 'A round little fish with a bronze belly. It makes a tiny bell note when startled.', 55.00, 3, 1, 99, 1, '2026-05-18 00:00:00', 80),
(229, 'Aquatic Shellcoin', 'A polished shell token accepted by Pelagora net-menders, ferry pilots, and children selling lamp-wicks.', 35.00, 2, 6, 99, 1, '2026-05-18 00:00:00', 1),
(230, 'Pelagora Ring Eel', 'A long blue eel that curls into a perfect circle when pulled from the Heart Mirror.', 64.00, 3, 1, 99, 1, '2026-05-18 00:00:00', 85),
(231, 'Underwater Bells of Pelagora Book', 'A waterproof lore book about the drowned bells that toll when greed or envy trouble the ring-town.', 95.00, 3, 7, 10, 1, '2026-05-18 00:00:00', 1),
(232, 'Pelagoric Ring Ledger Book', 'A shell-bound civic ledger explaining shoreline law, tide fines, and why no door facing the sea may be sealed.', 105.00, 3, 7, 10, 1, '2026-05-18 00:00:00', 1),
(233, 'Aquatic Oaths of the Heart Mirror Book', 'A book of careful promises spoken beside the central lagoon, copied in ink that resists salt.', 115.00, 4, 7, 10, 1, '2026-05-18 00:00:00', 1),
(234, 'Pelagora Before the Sinking Book', 'A rare illustrated reconstruction of the old capital before the rings folded and the sea entered the streets.', 140.00, 4, 7, 10, 1, '2026-05-18 00:00:00', 1)
ON DUPLICATE KEY UPDATE
  `item_name` = VALUES(`item_name`),
  `item_description` = VALUES(`item_description`),
  `base_price` = VALUES(`base_price`),
  `rarity_id` = VALUES(`rarity_id`),
  `category_id` = VALUES(`category_id`),
  `max_stack` = VALUES(`max_stack`),
  `tradable` = VALUES(`tradable`),
  `replenish` = VALUES(`replenish`);

INSERT INTO `shops` (`shop_id`, `shop_name`, `region_id`, `is_npc`, `restock_every_minutes`, `last_restok_at`) VALUES
(10, 'Tideglass Aquatics', 28, 1, 90, NULL),
(11, 'Drowned Stacks Library', 28, 1, 180, NULL)
ON DUPLICATE KEY UPDATE
  `shop_name` = VALUES(`shop_name`),
  `region_id` = VALUES(`region_id`),
  `is_npc` = VALUES(`is_npc`),
  `restock_every_minutes` = VALUES(`restock_every_minutes`);

INSERT INTO `shop_inventory` (`shop_id`, `item_id`, `price`, `stock`) VALUES
(10, 223, NULL, 60),
(10, 224, 48.00, 24),
(10, 225, 135.00, 8),
(10, 226, NULL, 32),
(10, 227, 82.00, 12),
(10, 228, 60.00, 18),
(10, 229, NULL, 40),
(10, 230, 72.00, 14),
(11, 231, NULL, 16),
(11, 232, NULL, 12),
(11, 233, 125.00, 10),
(11, 234, 155.00, 6)
ON DUPLICATE KEY UPDATE
  `price` = VALUES(`price`),
  `stock` = VALUES(`stock`);

CREATE TABLE IF NOT EXISTS `daily_pelagora_fishing_runs` (
  `user_id` bigint UNSIGNED NOT NULL,
  `run_date` date NOT NULL,
  `caught_item_id` bigint UNSIGNED DEFAULT NULL,
  `completed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `run_date`),
  KEY `ix_daily_pelagora_fishing_item` (`caught_item_id`),
  CONSTRAINT `fk_daily_pelagora_fishing_item` FOREIGN KEY (`caught_item_id`) REFERENCES `items` (`item_id`),
  CONSTRAINT `fk_daily_pelagora_fishing_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `daily_pelagora_salvage_runs` (
  `user_id` bigint UNSIGNED NOT NULL,
  `run_date` date NOT NULL,
  `salvaged_item_id` bigint UNSIGNED DEFAULT NULL,
  `completed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `run_date`),
  KEY `ix_daily_pelagora_salvage_item` (`salvaged_item_id`),
  CONSTRAINT `fk_daily_pelagora_salvage_item` FOREIGN KEY (`salvaged_item_id`) REFERENCES `items` (`item_id`),
  CONSTRAINT `fk_daily_pelagora_salvage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `items` AUTO_INCREMENT = 235;
ALTER TABLE `shops` AUTO_INCREMENT = 12;
