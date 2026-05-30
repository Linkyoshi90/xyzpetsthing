-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 29. Mai 2026 um 23:37
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
-- Tabellenstruktur für Tabelle `shops`
--

CREATE TABLE `shops` (
  `shop_id` int UNSIGNED NOT NULL,
  `shop_name` varchar(100) NOT NULL,
  `region_id` smallint UNSIGNED NOT NULL,
  `is_npc` tinyint(1) NOT NULL DEFAULT '1',
  `restock_every_minutes` int UNSIGNED DEFAULT NULL,
  `last_restok_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Daten für Tabelle `shops`
--

INSERT INTO `shops` (`shop_id`, `shop_name`, `region_id`, `is_npc`, `restock_every_minutes`, `last_restok_at`) VALUES
(1, 'Eternal General Store', 1, 1, 60, NULL),
(2, 'Crescent Bazaar', 2, 1, 90, NULL),
(3, 'Rodian Emporium', 3, 1, 120, NULL),
(4, 'Pizzeria Sol Invicta', 1, 1, 60, NULL),
(5, 'Library', 1, 1, 90, NULL),
(6, 'Rodian Emporium', 3, 1, 120, NULL),
(7, 'Yumenoki Ramen', 22, 1, 45, NULL),
(8, 'Aeon Products', 27, 1, 150, NULL),
(9, 'Karl\'s grosser Kiosk', 3, 1, 45, NULL),
(10, 'Mandala Market Stall', 6, 1, 75, NULL),
(11, 'Souq al-Hilal Provisions', 12, 1, 90, NULL),
(12, 'Feather and Flint Exchange', 9, 1, 100, NULL),
(13, 'Olive Lamp Goods', 14, 1, 85, NULL),
(14, 'Keerstide Lockside Shop', 3, 1, 60, NULL),
(15, 'Solvine Plaza Kiosk', 19, 1, 60, NULL),
(16, 'Ziggurat Ledger House', 13, 1, 120, NULL),
(17, 'Canopy Relic Post', 10, 1, 100, NULL),
(18, 'Ankhmeru Bazaar Tent', 15, 1, 110, NULL),
(19, 'Shenhedu Tea and Trinkets', 5, 1, 80, NULL),
(20, 'Skeldgard Frostmarket', 2, 1, 90, NULL),
(21, 'Redwind Roadhouse Store', 17, 1, 70, NULL),
(22, 'Velesgrad Winter Pantry', 4, 1, 90, NULL),
(23, 'Intirumi Sun Terrace Goods', 20, 1, 100, NULL),
(24, 'Qilaktuk Ice Cache', 16, 1, 110, NULL),
(25, 'Turtlestar Trading Blanket', 25, 1, 90, NULL),
(26, 'Navakai Spice Dock', 11, 1, 75, NULL),
(27, 'Meridian Corner Mart', 22, 1, 45, NULL),
(28, 'Xochival Flower Market', 8, 1, 70, NULL),
(29, 'Warraluma Keeping Place Shop', 18, 1, 100, NULL);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`shop_id`),
  ADD KEY `ix_shops_region` (`region_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `shops`
--
ALTER TABLE `shops`
  MODIFY `shop_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `shops`
--
ALTER TABLE `shops`
  ADD CONSTRAINT `fk_shop_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`region_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
