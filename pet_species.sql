-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 28. Nov 2025 um 16:53
-- Server-Version: 8.0.42-0ubuntu0.20.04.1
-- PHP-Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `ch53461_xyzpetsthing`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pet_species`
--

CREATE TABLE `pet_species` (
  `species_id` smallint UNSIGNED NOT NULL,
  `species_name` varchar(100) NOT NULL,
  `region_id` smallint UNSIGNED DEFAULT NULL,
  `base_hp` int NOT NULL,
  `base_atk` int NOT NULL,
  `base_def` int NOT NULL,
  `base_init` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Daten für Tabelle `pet_species`
--

INSERT INTO `pet_species` (`species_id`, `species_name`, `region_id`, `base_hp`, `base_atk`, `base_def`, `base_init`) VALUES
(58 , 'Lamia', 1, 11, 9, 7, 8),
(59 , 'Centaur', 1, 12, 10, 8, 9),
(112, 'Kraken', 2, 18, 12, 11, 4),
(113, 'Ratatoskr', 2, 9, 8, 7, 14),
(114, 'Banshee', 26, 10, 11, 6, 13),
(115, 'Dullahan', 26, 15, 13, 12, 5),
(116, 'Will-o-Wisp', 26, 7, 9, 5, 15),
(117, 'Kelpie', 26, 13, 9, 10, 7),
(118, 'Angel', 3, 14, 12, 10, 10),
(164, 'Demon', 3, 16, 14, 8, 8),
(165, 'Succubus', 3, 12, 11, 7, 12),
(166, 'Leshy', 4, 13, 8, 12, 6),
(167, 'Vodyanoy', 4, 12, 9, 13, 5),
(168, 'Lich', 22, 11, 14, 10, 11),
(169, 'Jack-o-Lantern', 22, 10, 10, 8, 11),
(170, 'Thunderbird', 25, 12, 13, 7, 14),
(171, 'Horned Serpent Uktena', 25, 15, 12, 11, 9),
(172, 'Jiang-Shi', 5, 17, 10, 15, 4),
(173, 'Vermillion Bird', 5, 11, 12, 7, 16),
(174, 'Gandharva', 6, 10, 9, 9, 13),
(175, 'Naga', 6, 14, 11, 12, 7),
(176, 'Spider-Crab', 7, 16, 10, 14, 5),
(177, 'Kitsune', 7, 9, 13, 8, 12),
(178, 'Yuki-Onna', 7, 12, 8, 10, 13),
(179, 'La Llorona', 8, 13, 10, 9, 12),
(180, 'Chupacabra', 8, 11, 13, 9, 10),
(181, 'Charro Negro', 8, 15, 9, 10, 8),
(182, 'Quetzalcoatl', 9, 17, 14, 10, 6),
(183, 'Ahuizotl', 9, 12, 12, 11, 7),
(184, 'Cipactli', 9, 18, 11, 13, 5),
(185, 'Ocelot', 9, 10, 12, 8, 14),
(186, 'Azureus', 10, 9, 10, 9, 11),
(187, 'Tapir', 10, 13, 7, 11, 9),
(188, 'Crab man', 11, 16, 9, 12, 6),
(189, 'Taniwha', 11, 17, 12, 9, 7),
(190, 'Genie', 12, 12, 14, 8, 10),
(191, 'Bahamut', 12, 20, 15, 14, 6),
(192, 'Girtablilu', 13, 15, 11, 13, 7),
(193, 'Lamassu', 13, 14, 10, 14, 8),
(194, 'Golem', 14, 19, 11, 15, 3),
(195, 'Dolphin', 14, 11, 9, 10, 13),
(196, 'Anubis', 15, 13, 14, 11, 9),
(197, 'Wadjet', 15, 12, 12, 10, 9),
(228, 'Amarok', 16, 17, 13, 11, 8),
(229, 'Polar Bear', 16, 18, 13, 12, 5),
(230, 'Drop Bear', 17, 14, 12, 9, 11),
(231, 'Min-Min Lights', 17, 8, 9, 6, 16),
(232, 'Bunyip', 18, 15, 10, 13, 6),
(233, 'Rainbow Serpent', 18, 16, 12, 12, 8),
(234, 'Curupira', 19, 12, 10, 9, 13),
(235, 'Capybara', 19, 14, 8, 9, 7),
(236, 'Fishman', 20, 11, 11, 10, 9),
(237, 'Argentinosaurus', 20, 22, 13, 16, 2),
(238, 'Amaru', 20, 15, 9, 11, 10),
(239, 'Wayob', 10, 10, 11, 7, 15),
(240, 'Black Turtle', 5, 19, 7, 18, 4);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `pet_species`
--
ALTER TABLE `pet_species`
  ADD PRIMARY KEY (`species_id`),
  ADD UNIQUE KEY `uq_species_name` (`species_name`),
  ADD KEY `fk_species_region` (`region_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `pet_species`
--
ALTER TABLE `pet_species`
  MODIFY `species_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `pet_species`
--
ALTER TABLE `pet_species`
  ADD CONSTRAINT `fk_species_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`region_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
