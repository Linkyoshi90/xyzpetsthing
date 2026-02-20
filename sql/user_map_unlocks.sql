CREATE TABLE IF NOT EXISTS user_map_unlocks (
  user_id BIGINT UNSIGNED NOT NULL,
  map_key VARCHAR(80) NOT NULL,
  unlocked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, map_key),
  KEY ix_map_unlock_map_key (map_key),
  CONSTRAINT fk_map_unlock_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
