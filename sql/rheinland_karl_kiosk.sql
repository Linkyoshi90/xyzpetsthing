-- Karl's grosser Kiosk
-- Based on ids present in database-backup.sql:
--   Rheinland region_id = 3
--   Existing shops in dump run through shop_id = 6
-- Assumption: shop_id 9 is free in the local project because shop_id 7 is already referenced by custom pages.
-- Note: the dump contains no Scratch ticket or candy item rows, so the kiosk flavor text mentions them on-site
-- while this inventory uses the closest existing snack/provision items from the dump.

INSERT INTO `shops` (`shop_id`, `shop_name`, `region_id`, `is_npc`, `restock_every_minutes`, `last_restok_at`) VALUES
(9, 'Karl''s grosser Kiosk', 3, 1, 45, NULL);

INSERT INTO `shop_inventory` (`shop_id`, `item_id`, `price`, `stock`) VALUES
(9, 1, NULL, NULL),   -- Berry
(9, 2, 40.00, 24),    -- Healing Potion
(9, 5, 69.00, 19);   -- Pizza Prosciutto
