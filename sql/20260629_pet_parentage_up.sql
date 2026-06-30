-- Apply after the existing breeding migration.
CREATE TABLE IF NOT EXISTS pet_parentage (
  piid BIGINT UNSIGNED NOT NULL,
  motherid BIGINT UNSIGNED NULL,
  fatherid BIGINT UNSIGNED NULL,
  PRIMARY KEY (piid),
  KEY ix_pet_parentage_mother (motherid),
  KEY ix_pet_parentage_father (fatherid),
  CONSTRAINT fk_pet_parentage_pet FOREIGN KEY (piid)
    REFERENCES pet_instances(pet_instance_id) ON DELETE CASCADE,
  CONSTRAINT fk_pet_parentage_mother FOREIGN KEY (motherid)
    REFERENCES pet_instances(pet_instance_id) ON DELETE SET NULL,
  CONSTRAINT fk_pet_parentage_father FOREIGN KEY (fatherid)
    REFERENCES pet_instances(pet_instance_id) ON DELETE SET NULL
) ENGINE=InnoDB;
