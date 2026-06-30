-- Item gifting between friends (escrow model).
--
-- The item leaves the sender's bag the moment they send the gift (held in escrow)
-- and is delivered to the receiver only on accept; a decline (or future cancel/
-- expiry) refunds it to the sender. `received` tracks that lifecycle:
--   0 = pending (item held in escrow, removed from sender)
--   1 = accepted (item delivered to receiver)
--   2 = rejected (item refunded to sender)
--
-- Apply by hand on the live DB (the live user is DML-only, no CREATE/ALTER).

CREATE TABLE IF NOT EXISTS `item_send` (
  `gift_id`     BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `sender_id`   BIGINT UNSIGNED  NOT NULL,
  `receiver_id` BIGINT UNSIGNED  NOT NULL,
  `item_id`     BIGINT UNSIGNED  NOT NULL,
  `received`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` TIMESTAMP        NULL     DEFAULT NULL,
  PRIMARY KEY (`gift_id`),
  KEY `ix_gift_receiver_state` (`receiver_id`, `received`),
  KEY `ix_gift_sender` (`sender_id`),
  KEY `fk_gift_item` (`item_id`),
  CONSTRAINT `fk_gift_sender`   FOREIGN KEY (`sender_id`)   REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_gift_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_gift_item`     FOREIGN KEY (`item_id`)     REFERENCES `items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
