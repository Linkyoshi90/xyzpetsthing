-- Roll back only the pet-ability migration.
ALTER TABLE pet_instances
  DROP FOREIGN KEY fk_pet_instances_ability,
  DROP INDEX ix_pet_instances_ability,
  DROP COLUMN ability_id;

DROP TABLE IF EXISTS pet_has_ability;
