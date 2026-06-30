-- Turtlestar Starpath Proving Ring seed.
-- Minimal import script: plain inserts only, no schema changes.
-- Assumes the existing project seed where STAP is region_id 25.

SET @stap_region_id := 25;
SET @encounter_start := TIMESTAMP('2026-06-16 00:00:00');
SET @encounter_end := TIMESTAMP('2036-12-31 23:59:59');

INSERT INTO `random_encounters`
  (`region_id`, `species_id`, `time_from`, `time_until`, `encounter_chance`)
SELECT
  @stap_region_id,
  ps.`species_id`,
  @encounter_start,
  @encounter_end,
  planned.`encounter_chance`
FROM (
  SELECT 'Thunderbird' AS `species_name`, 14.00 AS `encounter_chance`
  UNION ALL SELECT 'Horned Serpent Uktena', 10.00
  UNION ALL SELECT 'Kachina', 11.00
  UNION ALL SELECT 'Prairhorn', 13.00
  UNION ALL SELECT 'Dustbison', 13.00
  UNION ALL SELECT 'Famine', 5.00
  UNION ALL SELECT 'Alisnapor', 7.00
  UNION ALL SELECT 'Peacepipe Bison', 4.00
) planned
JOIN `pet_species` ps
  ON ps.`region_id` = @stap_region_id
 AND BINARY ps.`species_name` = BINARY planned.`species_name`
WHERE NOT EXISTS (
  SELECT 1
    FROM `random_encounters` existing
   WHERE existing.`region_id` = @stap_region_id
     AND existing.`species_id` = ps.`species_id`
     AND existing.`time_from` = @encounter_start
     AND existing.`time_until` = @encounter_end
);

INSERT INTO `trainers`
  (`class_name`, `trainer_name`, `encounter_line`, `defeat_line`, `defeat_currency`)
SELECT
  'Starpath Keeper',
  'Sahana Red Sash',
  'You waited outside the circle, read the story poles, and brought your companions without rushing them. Good. Now show whether your battle choices can listen as well as your feet.',
  'The fire has seen you stand, bend, and stand again. Take the Starfire Badge, and carry it like a promise, not a trophy.',
  850
WHERE NOT EXISTS (
  SELECT 1
    FROM `trainers`
   WHERE BINARY `class_name` = BINARY 'Starpath Keeper'
     AND BINARY `trainer_name` = BINARY 'Sahana Red Sash'
);

SELECT MIN(`trainer_id`) INTO @stap_leader_trainer_id
  FROM `trainers`
 WHERE BINARY `class_name` = BINARY 'Starpath Keeper'
   AND BINARY `trainer_name` = BINARY 'Sahana Red Sash';

SELECT MIN(`user_id`) INTO @system_user_id
  FROM `users`;

SELECT MIN(`color_id`) INTO @blue_color_id
  FROM `pet_colors`
 WHERE BINARY `color_name` = BINARY 'Blue';

INSERT INTO `pet_instances`
  (`owner_user_id`, `species_id`, `nickname`, `color_id`, `level`, `experience`, `hp_current`, `hp_max`, `atk`, `def`, `initiative`, `inactive`, `gender`, `hunger`, `happiness`, `intelligence`, `sickness`)
SELECT @system_user_id, ps.`species_id`, 'Sahana - Starpath Prairhorn', @blue_color_id, 7, 0, 74, 74, 20, 17, 18, 1, 'U', 0, 70, 3, 0
FROM `pet_species` ps
WHERE @system_user_id IS NOT NULL
  AND ps.`region_id` = @stap_region_id
  AND BINARY ps.`species_name` = BINARY 'Prairhorn'
  AND NOT EXISTS (
    SELECT 1
      FROM `pet_instances` pi
     WHERE pi.`owner_user_id` = @system_user_id
       AND BINARY pi.`nickname` = BINARY 'Sahana - Starpath Prairhorn'
  );

INSERT INTO `pet_instances`
  (`owner_user_id`, `species_id`, `nickname`, `color_id`, `level`, `experience`, `hp_current`, `hp_max`, `atk`, `def`, `initiative`, `inactive`, `gender`, `hunger`, `happiness`, `intelligence`, `sickness`)
SELECT @system_user_id, ps.`species_id`, 'Sahana - Council Kachina', @blue_color_id, 8, 0, 78, 78, 24, 17, 23, 1, 'U', 0, 72, 4, 0
FROM `pet_species` ps
WHERE @system_user_id IS NOT NULL
  AND ps.`region_id` = @stap_region_id
  AND BINARY ps.`species_name` = BINARY 'Kachina'
  AND NOT EXISTS (
    SELECT 1
      FROM `pet_instances` pi
     WHERE pi.`owner_user_id` = @system_user_id
       AND BINARY pi.`nickname` = BINARY 'Sahana - Council Kachina'
  );

INSERT INTO `pet_instances`
  (`owner_user_id`, `species_id`, `nickname`, `color_id`, `level`, `experience`, `hp_current`, `hp_max`, `atk`, `def`, `initiative`, `inactive`, `gender`, `hunger`, `happiness`, `intelligence`, `sickness`)
SELECT @system_user_id, ps.`species_id`, 'Sahana - Dustbison Wall', @blue_color_id, 8, 0, 92, 92, 20, 25, 13, 1, 'U', 0, 68, 2, 0
FROM `pet_species` ps
WHERE @system_user_id IS NOT NULL
  AND ps.`region_id` = @stap_region_id
  AND BINARY ps.`species_name` = BINARY 'Dustbison'
  AND NOT EXISTS (
    SELECT 1
      FROM `pet_instances` pi
     WHERE pi.`owner_user_id` = @system_user_id
       AND BINARY pi.`nickname` = BINARY 'Sahana - Dustbison Wall'
  );

INSERT INTO `pet_instances`
  (`owner_user_id`, `species_id`, `nickname`, `color_id`, `level`, `experience`, `hp_current`, `hp_max`, `atk`, `def`, `initiative`, `inactive`, `gender`, `hunger`, `happiness`, `intelligence`, `sickness`)
SELECT @system_user_id, ps.`species_id`, 'Sahana - Starfire Thunderbird', @blue_color_id, 10, 0, 94, 94, 28, 18, 30, 1, 'U', 0, 80, 5, 0
FROM `pet_species` ps
WHERE @system_user_id IS NOT NULL
  AND ps.`region_id` = @stap_region_id
  AND BINARY ps.`species_name` = BINARY 'Thunderbird'
  AND NOT EXISTS (
    SELECT 1
      FROM `pet_instances` pi
     WHERE pi.`owner_user_id` = @system_user_id
       AND BINARY pi.`nickname` = BINARY 'Sahana - Starfire Thunderbird'
  );

SELECT MIN(`pet_instance_id`) INTO @leader_pet_1
  FROM `pet_instances`
 WHERE `owner_user_id` = @system_user_id
   AND BINARY `nickname` = BINARY 'Sahana - Starpath Prairhorn';

SELECT MIN(`pet_instance_id`) INTO @leader_pet_2
  FROM `pet_instances`
 WHERE `owner_user_id` = @system_user_id
   AND BINARY `nickname` = BINARY 'Sahana - Council Kachina';

SELECT MIN(`pet_instance_id`) INTO @leader_pet_3
  FROM `pet_instances`
 WHERE `owner_user_id` = @system_user_id
   AND BINARY `nickname` = BINARY 'Sahana - Dustbison Wall';

SELECT MIN(`pet_instance_id`) INTO @leader_pet_4
  FROM `pet_instances`
 WHERE `owner_user_id` = @system_user_id
   AND BINARY `nickname` = BINARY 'Sahana - Starfire Thunderbird';

INSERT INTO `trainer_roster` (`trainer_id`, `pet_instance_id`, `roster_position`)
SELECT @stap_leader_trainer_id, @leader_pet_1, 1
WHERE @stap_leader_trainer_id IS NOT NULL
  AND @leader_pet_1 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1
      FROM `trainer_roster`
     WHERE `trainer_id` = @stap_leader_trainer_id
       AND `roster_position` = 1
  );

INSERT INTO `trainer_roster` (`trainer_id`, `pet_instance_id`, `roster_position`)
SELECT @stap_leader_trainer_id, @leader_pet_2, 2
WHERE @stap_leader_trainer_id IS NOT NULL
  AND @leader_pet_2 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1
      FROM `trainer_roster`
     WHERE `trainer_id` = @stap_leader_trainer_id
       AND `roster_position` = 2
  );

INSERT INTO `trainer_roster` (`trainer_id`, `pet_instance_id`, `roster_position`)
SELECT @stap_leader_trainer_id, @leader_pet_3, 3
WHERE @stap_leader_trainer_id IS NOT NULL
  AND @leader_pet_3 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1
      FROM `trainer_roster`
     WHERE `trainer_id` = @stap_leader_trainer_id
       AND `roster_position` = 3
  );

INSERT INTO `trainer_roster` (`trainer_id`, `pet_instance_id`, `roster_position`)
SELECT @stap_leader_trainer_id, @leader_pet_4, 4
WHERE @stap_leader_trainer_id IS NOT NULL
  AND @leader_pet_4 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1
      FROM `trainer_roster`
     WHERE `trainer_id` = @stap_leader_trainer_id
       AND `roster_position` = 4
  );
