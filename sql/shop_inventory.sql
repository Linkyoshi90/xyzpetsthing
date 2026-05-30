-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 29. Mai 2026 um 23:30
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
-- Tabellenstruktur für Tabelle `shop_inventory`
--

CREATE TABLE `shop_inventory` (
  `shop_id` int UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `stock` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Daten für Tabelle `shop_inventory`
--

INSERT INTO `shop_inventory` (`shop_id`, `item_id`, `price`, `stock`) VALUES
(1, 1, NULL, NULL),
(1, 2, 45.00, 50),
(1, 3, 220.00, 1),
(2, 4, 600.00, 3),
(2, 5, 80.00, 40),
(2, 13, 1300.00, 5),
(3, 12, 5200000.00, 1),
(4, 14, 25.00, 3),
(4, 15, 30.00, 1),
(4, 16, 30.00, 2),
(4, 22, 21.00, 2),
(7, 84, 40.00, 47),
(7, 87, 30.00, 0),
(7, 88, 65.00, 17),
(7, 89, 25.00, 44),
(9, 1, NULL, NULL),
(9, 2, 40.00, 24),
(9, 5, 69.00, 19);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `shop_inventory`
--
ALTER TABLE `shop_inventory`
  ADD PRIMARY KEY (`shop_id`,`item_id`),
  ADD KEY `fk_shopinv_item` (`item_id`);

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `shop_inventory`
--
ALTER TABLE `shop_inventory`
  ADD CONSTRAINT `fk_shopinv_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `fk_shopinv_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
