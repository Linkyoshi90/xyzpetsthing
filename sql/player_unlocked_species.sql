CREATE TABLE IF NOT EXISTS player_unlocked_species (
  entryId              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  player_id            BIGINT UNSIGNED NOT NULL,
  unlocked_species_id  SMALLINT UNSIGNED NOT NULL,
  created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (entryId),
  UNIQUE KEY uq_player_species_unlock (player_id, unlocked_species_id),
  KEY ix_player_unlocked_player (player_id),
  KEY ix_player_unlocked_species (unlocked_species_id),
  CONSTRAINT fk_player_unlocked_player FOREIGN KEY (player_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_player_unlocked_species FOREIGN KEY (unlocked_species_id) REFERENCES pet_species(species_id) ON DELETE CASCADE
) ENGINE=InnoDB;
