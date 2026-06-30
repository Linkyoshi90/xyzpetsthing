-- Assign a random species-valid ability to every existing pet instance.
--
-- Each species defines two abilities in `pet_has_ability` (ability ids
-- `species_id * 10 + 1` and `species_id * 10 + 2`). This picks one of the two
-- at random, evaluated independently per pet instance, replacing the earlier
-- deterministic "slot 1" backfill so instances are varied.
--
-- Pets whose species has no catalog ability (none defined yet, e.g. species
-- 396-401) are left untouched and keep their existing ability_id (NULL).
--
-- DML only: requires UPDATE on pet_instances and SELECT on pet_has_ability.
-- Run this after `20260629_pet_abilities_up.sql` (the catalog + column must
-- already exist).

UPDATE pet_instances pi
   SET pi.ability_id = (
       SELECT pha.ability_id
         FROM pet_has_ability pha
        WHERE pha.species_id = pi.species_id
        ORDER BY RAND()
        LIMIT 1
   )
 WHERE EXISTS (
       SELECT 1
         FROM pet_has_ability pha2
        WHERE pha2.species_id = pi.species_id
   );

-- Verification (optional):
--   SELECT COUNT(*) AS total,
--          SUM(ability_id IS NULL) AS without_ability
--     FROM pet_instances;
-- The only remaining NULLs should be pets whose species has no abilities yet.
