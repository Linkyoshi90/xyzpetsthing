-- Highscore table for the Creature Quiz (index.php?pg=pkmn-quiz).
-- timer = chosen round length in seconds (60-1800).
CREATE TABLE `pkmn-game` (
  `scoreId` INT NOT NULL AUTO_INCREMENT,
  `initials` VARCHAR(10) NOT NULL,
  `score` INT NOT NULL,
  `timer` INT NOT NULL,
  PRIMARY KEY (`scoreId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
