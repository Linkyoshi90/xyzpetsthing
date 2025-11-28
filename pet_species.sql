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
(58, 'Lamia', 1, 8, 8, 5, 7),
(59, 'Centaur', 1, 12, 6, 7, 5),
(112, 'Kraken', 2, 12, 6, 7, 5),
(113, 'Ratatoskr', 2, 12, 6, 7, 5),
(114, 'Banshee', 26, 12, 6, 7, 5),
(115, 'Dullahan', 26, 12, 6, 7, 5),
(116, 'Will-o-Wisp', 26, 12, 6, 7, 5),
(117, 'Kelpie', 26, 12, 6, 7, 5),
(118, 'Angel', 3, 12, 6, 7, 5),
(164, 'Demon', 3, 12, 6, 7, 5),
(165, 'Succubus', 3, 12, 6, 7, 5),
(166, 'Leshy', 4, 12, 6, 7, 5),
(167, 'Vodyanoy', 4, 12, 6, 7, 5),
(168, 'Lich', 22, 12, 6, 7, 5),
(169, 'Jack-o-Lantern', 22, 12, 6, 7, 5),
(170, 'Thunderbird', 25, 12, 6, 7, 5),
(171, 'Horned Serpent Uktena', 25, 12, 6, 7, 5),
(172, 'Jiang-Shi', 5, 12, 6, 7, 5),
(173, 'Vermillion Bird', 5, 12, 6, 7, 5),
(174, 'Gandharva', 6, 12, 6, 7, 5),
(175, 'Naga', 6, 12, 6, 7, 5),
(176, 'Spider-Crab', 7, 12, 6, 7, 5),
(177, 'Kitsune', 7, 12, 6, 7, 5),
(178, 'Yuki-Onna', 7, 12, 6, 7, 5),
(179, 'La Llorona', 8, 12, 6, 7, 5),
(180, 'Chupacabra', 8, 12, 6, 7, 5),
(181, 'Charro Negro', 8, 12, 6, 7, 5),
(182, 'Quetzalcoatl', 9, 12, 6, 7, 5),
(183, 'Ahuizotl', 9, 12, 6, 7, 5),
(184, 'Cipactli', 9, 12, 6, 7, 5),
(185, 'Ocelot', 9, 12, 6, 7, 5),
(186, 'Azureus', 10, 12, 6, 7, 5),
(187, 'Tapir', 10, 12, 6, 7, 5),
(188, 'Crab man', 11, 12, 6, 7, 5),
(189, 'Taniwha', 11, 12, 6, 7, 5),
(190, 'Genie', 12, 12, 6, 7, 5),
(191, 'Bahamut', 12, 12, 6, 7, 5),
(192, 'Girtablilu', 13, 12, 6, 7, 5),
(193, 'Lamassu', 13, 12, 6, 7, 5),
(194, 'Golem', 14, 12, 6, 7, 5),
(195, 'Dolphin', 14, 12, 6, 7, 5),
(196, 'Anubis', 15, 12, 6, 7, 5),
(197, 'Wadjet', 15, 12, 6, 7, 5),
(228, 'Amarok', 16, 12, 6, 7, 5),
(229, 'Polar Bear', 16, 12, 6, 7, 5),
(230, 'Drop Bear', 17, 12, 6, 7, 5),
(231, 'Min-Min Lights', 17, 12, 6, 7, 5),
(232, 'Bunyip', 18, 12, 6, 7, 5),
(233, 'Rainbow Serpent', 18, 12, 6, 7, 5),
(234, 'Curupira', 19, 12, 6, 7, 5),
(235, 'Capybara', 19, 12, 6, 7, 5),
(236, 'Fishman', 20, 12, 6, 7, 5),
(237, 'Argentinosaurus', 20, 12, 6, 7, 5),
(238, 'Amaru', 20, 12, 6, 7, 5),
(239, 'Wayob', 10, 12, 6, 7, 5),
(240, 'Black Turtle', 5, 20, 2, 7, 3);

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
