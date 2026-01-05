-- Add inactive flag to pet instances so daycare can hold pets
ALTER TABLE pet_instances
  ADD COLUMN inactive TINYINT(1) NOT NULL DEFAULT 0 AFTER initiative;

-- Track daycare breeding pairs and egg progress
CREATE TABLE IF NOT EXISTS breeding (
  breed_instance_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_user_id BIGINT UNSIGNED NOT NULL,
  deposit_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  father BIGINT UNSIGNED NULL,
  mother BIGINT UNSIGNED NOT NULL,
  egg_creature_id SMALLINT UNSIGNED NULL,
  egg_count INT UNSIGNED NOT NULL DEFAULT 0,
  time_to_hatch TINYINT UNSIGNED NOT NULL DEFAULT 0,
  father_best_stat VARCHAR(16) NULL,
  mother_best_stat VARCHAR(16) NULL,
  PRIMARY KEY (breed_instance_id),
  KEY ix_breeding_owner (owner_user_id),
  CONSTRAINT fk_breeding_owner FOREIGN KEY (owner_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_breeding_father FOREIGN KEY (father) REFERENCES pet_instances(pet_instance_id) ON DELETE SET NULL,
  CONSTRAINT fk_breeding_mother FOREIGN KEY (mother) REFERENCES pet_instances(pet_instance_id) ON DELETE CASCADE,
  CONSTRAINT fk_breeding_species FOREIGN KEY (egg_creature_id) REFERENCES pet_species(species_id)
) ENGINE=InnoDB;
