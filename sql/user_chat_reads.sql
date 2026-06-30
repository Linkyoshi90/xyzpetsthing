-- Per-conversation read state for direct messages.
-- Created automatically by chat_ensure_reads_schema() in lib/chat.php; this file
-- is the canonical record / a manual fallback.
CREATE TABLE IF NOT EXISTS `user_chat_reads` (
  `user_id` bigint UNSIGNED NOT NULL,
  `friend_id` bigint UNSIGNED NOT NULL,
  `last_read_message_id` bigint UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `friend_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
