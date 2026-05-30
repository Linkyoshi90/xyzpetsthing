-- Wild creature battle encounter seed plan.
-- These rows feed creature-only random battles through random_encounters.
-- No trainers, trainer_roster rows, or trainer-owned pet_instances are created here.

INSERT INTO `random_encounters` (`region_id`, `species_id`, `time_from`, `time_until`, `encounter_chance`)
SELECT
  r.`region_id`,
  ps.`species_id`,
  TIMESTAMP('2026-05-26 00:00:00') AS `time_from`,
  TIMESTAMP('2036-12-31 23:59:59') AS `time_until`,
  planned.`encounter_chance`
FROM (
  SELECT 'Aegia Aeterna' AS `region_name`, 'Lamia' AS `species_name`, 16.00 AS `encounter_chance`
  UNION ALL SELECT 'Aegia Aeterna', 'Centaur', 14.00
  UNION ALL SELECT 'Aegia Aeterna', 'Talosling', 10.00
  UNION ALL SELECT 'Aegia Aeterna', 'Aegis Boar', 8.00
  UNION ALL SELECT 'Nornheim', 'Ratatoskr', 16.00
  UNION ALL SELECT 'Nornheim', 'Kraken', 7.00
  UNION ALL SELECT 'Nornheim', 'Frost Giant', 5.00
  UNION ALL SELECT 'Nornheim', 'Pineskrell', 12.00
  UNION ALL SELECT 'Bretonreach', 'Kelpie', 13.00
  UNION ALL SELECT 'Bretonreach', 'Banshee', 11.00
  UNION ALL SELECT 'Bretonreach', 'Will-o-Wisp', 9.00
  UNION ALL SELECT 'Rheinland', 'Angel', 10.00
  UNION ALL SELECT 'Rheinland', 'Demon', 10.00
  UNION ALL SELECT 'Rheinland', 'Succubus', 10.00
  UNION ALL SELECT 'Rodinian Tsardom', 'Leshy', 14.00
  UNION ALL SELECT 'Rodinian Tsardom', 'Vodyanoy', 12.00
  UNION ALL SELECT 'Lotus-Dragon Kingdom', 'Jiang-Shi', 10.00
  UNION ALL SELECT 'Lotus-Dragon Kingdom', 'Vermillion Bird', 8.00
  UNION ALL SELECT 'Lotus-Dragon Kingdom', 'Black Turtle', 6.00
  UNION ALL SELECT 'Lotus-Dragon Kingdom', 'Sword Koi', 9.00
  UNION ALL SELECT 'Baharamandal', 'Naga', 12.00
  UNION ALL SELECT 'Baharamandal', 'Gandharva', 12.00
  UNION ALL SELECT 'Baharamandal', 'Apsara', 8.00
  UNION ALL SELECT 'Yamanokubo', 'Kitsune', 12.00
  UNION ALL SELECT 'Yamanokubo', 'Yuki-Onna', 10.00
  UNION ALL SELECT 'Yamanokubo', 'Spider-Crab', 8.00
  UNION ALL SELECT 'Yamanokubo', 'Kappa', 9.00
  UNION ALL SELECT 'Xochimex', 'La Llorona', 12.00
  UNION ALL SELECT 'Xochimex', 'Chupacabra', 12.00
  UNION ALL SELECT 'Eagle Serpent Dominion', 'Ahuizotl', 13.00
  UNION ALL SELECT 'Eagle Serpent Dominion', 'Ocelot', 12.00
  UNION ALL SELECT 'Eagle Serpent Dominion', 'Cipactli', 7.00
  UNION ALL SELECT 'Itzam Empire', 'Tapir', 13.00
  UNION ALL SELECT 'Itzam Empire', 'Azureus', 12.00
  UNION ALL SELECT 'Itzam Empire', 'Wayob', 9.00
  UNION ALL SELECT 'Spice Route League', 'Taniwha', 10.00
  UNION ALL SELECT 'Spice Route League', 'Crab man', 12.00
  UNION ALL SELECT 'Spice Route League', 'Adaro', 8.00
  UNION ALL SELECT 'Crescent Caliphate', 'Genie', 11.00
  UNION ALL SELECT 'Crescent Caliphate', 'Bahamut', 5.00
  UNION ALL SELECT 'Crescent Caliphate', 'Manticore', 8.00
  UNION ALL SELECT 'Hammurabia', 'Girtablilu', 11.00
  UNION ALL SELECT 'Hammurabia', 'Lamassu', 11.00
  UNION ALL SELECT 'Eretz-Shalem League', 'Dolphin', 13.00
  UNION ALL SELECT 'Eretz-Shalem League', 'Golem', 8.00
  UNION ALL SELECT 'Eretz-Shalem League', 'Ziz', 6.00
  UNION ALL SELECT 'Kemet', 'Anubis', 12.00
  UNION ALL SELECT 'Kemet', 'Wadjet', 12.00
  UNION ALL SELECT 'Sila Council', 'Polar Bear', 11.00
  UNION ALL SELECT 'Sila Council', 'Amarok', 10.00
  UNION ALL SELECT 'Sila Council', 'Penguin', 14.00
  UNION ALL SELECT 'Sila Council', 'Keelut', 9.00
  UNION ALL SELECT 'Red Sun Commonwealth', 'Drop Bear', 11.00
  UNION ALL SELECT 'Red Sun Commonwealth', 'Min-Min Lights', 12.00
  UNION ALL SELECT 'Red Sun Commonwealth', 'Sea Turtle', 8.00
  UNION ALL SELECT 'Yara Nations', 'Bunyip', 12.00
  UNION ALL SELECT 'Yara Nations', 'Rainbow Serpent', 8.00
  UNION ALL SELECT 'Yara Nations', 'Tiddalik', 9.00
  UNION ALL SELECT 'Gran Columbia', 'Capybara', 14.00
  UNION ALL SELECT 'Gran Columbia', 'Curupira', 12.00
  UNION ALL SELECT 'Gran Columbia', 'Toucan', 10.00
  UNION ALL SELECT 'Sapa Inti Empire', 'Amaru', 12.00
  UNION ALL SELECT 'Sapa Inti Empire', 'Argentinosaurus', 6.00
  UNION ALL SELECT 'Sapa Inti Empire', 'Fishman', 10.00
  UNION ALL SELECT 'United free Republic of Borealia', 'Lich', 9.00
  UNION ALL SELECT 'United free Republic of Borealia', 'Jack-o-Lantern', 12.00
  UNION ALL SELECT 'Sovereign Tribes of the Ancestral Plains', 'Thunderbird', 8.00
  UNION ALL SELECT 'Sovereign Tribes of the Ancestral Plains', 'Horned Serpent Uktena', 8.00
  UNION ALL SELECT 'Aeonstep Plateau', 'Tuskin', 12.00
  UNION ALL SELECT 'Aeonstep Plateau', 'Argentowlis', 10.00
  UNION ALL SELECT 'Aeonstep Plateau', 'Squirricerat', 11.00
  UNION ALL SELECT 'Aeonstep Plateau', 'Sabre', 8.00
  UNION ALL SELECT 'Pelagora', 'Reeffin', 14.00
  UNION ALL SELECT 'Pelagora', 'Mantarrow', 12.00
  UNION ALL SELECT 'Pelagora', 'Archelon', 8.00
  UNION ALL SELECT 'Pelagora', 'Urchskin', 9.00
) planned
JOIN `regions` r
  ON r.`region_name` = planned.`region_name`
JOIN `pet_species` ps
  ON ps.`species_name` = planned.`species_name`
 AND ps.`region_id` = r.`region_id`
WHERE NOT EXISTS (
  SELECT 1
    FROM `random_encounters` existing
   WHERE existing.`region_id` = r.`region_id`
     AND existing.`species_id` = ps.`species_id`
     AND existing.`time_from` = TIMESTAMP('2026-05-26 00:00:00')
     AND existing.`time_until` = TIMESTAMP('2036-12-31 23:59:59')
);
