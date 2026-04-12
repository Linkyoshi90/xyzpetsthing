-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 12, 2026 at 04:57 PM
-- Server version: 8.0.42-0ubuntu0.20.04.1
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ch53461_xyzpetsthing`
--

-- --------------------------------------------------------

--
-- Table structure for table `abandoned_pets`
--

CREATE TABLE `abandoned_pets` (
  `ap_id` bigint UNSIGNED NOT NULL,
  `creature_id` bigint UNSIGNED NOT NULL,
  `old_player_id` bigint UNSIGNED NOT NULL,
  `creature_name` varchar(100) NOT NULL,
  `abandoned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `abandoned_pets`
--

INSERT INTO `abandoned_pets` (`ap_id`, `creature_id`, `old_player_id`, `creature_name`, `abandoned_at`) VALUES
(11, 19, 5, 'Homo Delfin', '2026-01-14 17:55:47');

-- --------------------------------------------------------

--
-- Table structure for table `creature_name_votes`
--

CREATE TABLE `creature_name_votes` (
  `user_id` bigint UNSIGNED NOT NULL,
  `selection_json` json NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `creature_name_votes`
--

INSERT INTO `creature_name_votes` (`user_id`, `selection_json`, `submitted_at`) VALUES
(9, '[{\"base\": \"Lamia\", \"choice\": \"Viperelle\", \"region\": \"Auronia – Aegia Aeterna (AA)\", \"base_slug\": \"lamia\", \"region_slug\": \"auronia_aegia_aeterna_aa\"}, {\"base\": \"Centaur\", \"choice\": \"Hoofkin\", \"region\": \"Auronia – Aegia Aeterna (AA)\", \"base_slug\": \"centaur\", \"region_slug\": \"auronia_aegia_aeterna_aa\"}, {\"base\": \"Amaru\", \"choice\": \"Serpentupa\", \"region\": \"Verdania – Sapa Inti Empire (SIE)\", \"base_slug\": \"amaru\", \"region_slug\": \"verdania_sapa_inti_empire_sie\"}, {\"base\": \"Argentinosaurus\", \"choice\": \"Colossaur\", \"region\": \"Verdania – Sapa Inti Empire (SIE)\", \"base_slug\": \"argentinosaurus\", \"region_slug\": \"verdania_sapa_inti_empire_sie\"}, {\"base\": \"Fishman\", \"choice\": \"Neridon\", \"region\": \"Verdania – Sapa Inti Empire (SIE)\", \"base_slug\": \"fishman\", \"region_slug\": \"verdania_sapa_inti_empire_sie\"}, {\"base\": \"Capybara\", \"choice\": \"Capycurrent\", \"region\": \"Verdania – Gran Columbia (GC)\", \"base_slug\": \"capybara\", \"region_slug\": \"verdania_gran_columbia_gc\"}, {\"base\": \"Curupira\", \"choice\": \"Foresttrix\", \"region\": \"Verdania – Gran Columbia (GC)\", \"base_slug\": \"curupira\", \"region_slug\": \"verdania_gran_columbia_gc\"}, {\"base\": \"Rainbow Serpent\", \"choice\": \"Arcudra\", \"region\": \"Uluru – Yara Nations (YN)\", \"base_slug\": \"rainbow_serpent\", \"region_slug\": \"uluru_yara_nations_yn\"}, {\"base\": \"Bunyip\", \"choice\": \"Marshmurm\", \"region\": \"Uluru – Yara Nations (YN)\", \"base_slug\": \"bunyip\", \"region_slug\": \"uluru_yara_nations_yn\"}, {\"base\": \"Min-Min Lights\", \"choice\": \"Wispstray\", \"region\": \"Uluru – Red Sun Commonwealth (RSC)\", \"base_slug\": \"min_min_lights\", \"region_slug\": \"uluru_red_sun_commonwealth_rsc\"}, {\"base\": \"Drop Bear\", \"choice\": \"Plummko\", \"region\": \"Uluru – Red Sun Commonwealth (RSC)\", \"base_slug\": \"drop_bear\", \"region_slug\": \"uluru_red_sun_commonwealth_rsc\"}, {\"base\": \"Polar Bear\", \"choice\": \"Icebrawler\", \"region\": \"Tundria – Sila Council (SC)\", \"base_slug\": \"polar_bear\", \"region_slug\": \"tundria_sila_council_sc\"}, {\"base\": \"Amarok\", \"choice\": \"Snowmara\", \"region\": \"Tundria – Sila Council (SC)\", \"base_slug\": \"amarok\", \"region_slug\": \"tundria_sila_council_sc\"}, {\"base\": \"Wadjet\", \"choice\": \"Wadja\", \"region\": \"Saharene – Kemet (KM)\", \"base_slug\": \"wadjet\", \"region_slug\": \"saharene_kemet_km\"}, {\"base\": \"Anubis\", \"choice\": \"Anupet\", \"region\": \"Saharene – Kemet (KM)\", \"base_slug\": \"anubis\", \"region_slug\": \"saharene_kemet_km\"}, {\"base\": \"Dolphin\", \"choice\": \"Wavewhistle\", \"region\": \"Orienthem – Eretz-Shalem League (ESL)\", \"base_slug\": \"dolphin\", \"region_slug\": \"orienthem_eretz_shalem_league_esl\"}, {\"base\": \"Golem\", \"choice\": \"Emetron\", \"region\": \"Orienthem – Eretz-Shalem League (ESL)\", \"base_slug\": \"golem\", \"region_slug\": \"orienthem_eretz_shalem_league_esl\"}, {\"base\": \"Lamassu\", \"choice\": \"Gatebull\", \"region\": \"Orienthem – Hammurabia (HR)\", \"base_slug\": \"lamassu\", \"region_slug\": \"orienthem_hammurabia_hr\"}, {\"base\": \"Girtablilu\", \"choice\": \"Stingward\", \"region\": \"Orienthem – Hammurabia (HR)\", \"base_slug\": \"girtablilu\", \"region_slug\": \"orienthem_hammurabia_hr\"}, {\"base\": \"Bahamut\", \"choice\": \"Deepmajest\", \"region\": \"Orienthem – Crescent Caliphate (CC)\", \"base_slug\": \"bahamut\", \"region_slug\": \"orienthem_crescent_caliphate_cc\"}, {\"base\": \"Genie\", \"choice\": \"Djinna\", \"region\": \"Orienthem – Crescent Caliphate (CC)\", \"base_slug\": \"genie\", \"region_slug\": \"orienthem_crescent_caliphate_cc\"}, {\"base\": \"Taniwha\", \"choice\": \"Wakecoil\", \"region\": \"Moana Crown – Spice Route League (SRL)\", \"base_slug\": \"taniwha\", \"region_slug\": \"moana_crown_spice_route_league_srl\"}, {\"base\": \"Crab man\", \"choice\": \"Shellina\", \"region\": \"Moana Crown – Spice Route League (SRL)\", \"base_slug\": \"crab_man\", \"region_slug\": \"moana_crown_spice_route_league_srl\"}, {\"base\": \"Tapir\", \"choice\": \"Dorsnork\", \"region\": \"Gulfbelt – Itzam Empire (IE)\", \"base_slug\": \"tapir\", \"region_slug\": \"gulfbelt_itzam_empire_ie\"}, {\"base\": \"Azureus\", \"choice\": \"Cobaltoad\", \"region\": \"Gulfbelt – Itzam Empire (IE)\", \"base_slug\": \"azureus\", \"region_slug\": \"gulfbelt_itzam_empire_ie\"}, {\"base\": \"Ocelot\", \"choice\": \"Spotblade\", \"region\": \"Gulfbelt – Eagle Serpent Dominion (ESD)\", \"base_slug\": \"ocelot\", \"region_slug\": \"gulfbelt_eagle_serpent_dominion_esd\"}, {\"base\": \"Cipactli\", \"choice\": \"Mawdelta\", \"region\": \"Gulfbelt – Eagle Serpent Dominion (ESD)\", \"base_slug\": \"cipactli\", \"region_slug\": \"gulfbelt_eagle_serpent_dominion_esd\"}, {\"base\": \"Ahuizotl\", \"choice\": \"Lakegnash\", \"region\": \"Gulfbelt – Eagle Serpent Dominion (ESD)\", \"base_slug\": \"ahuizotl\", \"region_slug\": \"gulfbelt_eagle_serpent_dominion_esd\"}, {\"base\": \"Quetzalcoatl\", \"choice\": \"Featherserp\", \"region\": \"Gulfbelt – Eagle Serpent Dominion (ESD)\", \"base_slug\": \"quetzalcoatl\", \"region_slug\": \"gulfbelt_eagle_serpent_dominion_esd\"}, {\"base\": \"Charro Negro\", \"choice\": \"Charro Noct\", \"region\": \"Gulfbelt – Xochimex (XM)\", \"base_slug\": \"charro_negro\", \"region_slug\": \"gulfbelt_xochimex_xm\"}, {\"base\": \"Chupacabra\", \"choice\": \"Cabrasaur\", \"region\": \"Gulfbelt – Xochimex (XM)\", \"base_slug\": \"chupacabra\", \"region_slug\": \"gulfbelt_xochimex_xm\"}, {\"base\": \"La Llorona\", \"choice\": \"Canalcry\", \"region\": \"Gulfbelt – Xochimex (XM)\", \"base_slug\": \"la_llorona\", \"region_slug\": \"gulfbelt_xochimex_xm\"}, {\"base\": \"Yuki-Onna\", \"choice\": \"Frostrae\", \"region\": \"Dawnmarch – Yamanokubo (YK)\", \"base_slug\": \"yuki_onna\", \"region_slug\": \"dawnmarch_yamanokubo_yk\"}, {\"base\": \"Kitsune\", \"choice\": \"Kitsel\", \"region\": \"Dawnmarch – Yamanokubo (YK)\", \"base_slug\": \"kitsune\", \"region_slug\": \"dawnmarch_yamanokubo_yk\"}, {\"base\": \"Spider-Crab\", \"choice\": \"Crabgantic\", \"region\": \"Dawnmarch – Yamanokubo (YK)\", \"base_slug\": \"spider_crab\", \"region_slug\": \"dawnmarch_yamanokubo_yk\"}, {\"base\": \"Naga\", \"choice\": \"Serpanta\", \"region\": \"Dawnmarch – Baharamandal (BM)\", \"base_slug\": \"naga\", \"region_slug\": \"dawnmarch_baharamandal_bm\"}, {\"base\": \"Gandharva\", \"choice\": \"Aerialute\", \"region\": \"Dawnmarch – Baharamandal (BM)\", \"base_slug\": \"gandharva\", \"region_slug\": \"dawnmarch_baharamandal_bm\"}, {\"base\": \"Vermillion Bird\", \"choice\": \"Verbirdion\", \"region\": \"Dawnmarch – Lotus-Dragon Kingdom (LDK)\", \"base_slug\": \"vermillion_bird\", \"region_slug\": \"dawnmarch_lotus_dragon_kingdom_ldk\"}, {\"base\": \"Jiang-Shi\", \"choice\": \"Stiffwalker\", \"region\": \"Dawnmarch – Lotus-Dragon Kingdom (LDK)\", \"base_slug\": \"jiang_shi\", \"region_slug\": \"dawnmarch_lotus_dragon_kingdom_ldk\"}, {\"base\": \"Horned Serpent Uktena\", \"choice\": \"Horncoil\", \"region\": \"Borealia – Sovereign Tribes of the Ancestral Plains (STAP)\", \"base_slug\": \"horned_serpent_uktena\", \"region_slug\": \"borealia_sovereign_tribes_of_the_ancestral_plains_stap\"}, {\"base\": \"Thunderbird\", \"choice\": \"Thundara\", \"region\": \"Borealia – Sovereign Tribes of the Ancestral Plains (STAP)\", \"base_slug\": \"thunderbird\", \"region_slug\": \"borealia_sovereign_tribes_of_the_ancestral_plains_stap\"}, {\"base\": \"Jack-o-lantern\", \"choice\": \"Gourdlume\", \"region\": \"Borealia – United free Republic of Borealia (URB)\", \"base_slug\": \"jack_o_lantern\", \"region_slug\": \"borealia_united_free_republic_of_borealia_urb\"}, {\"base\": \"Lich\", \"choice\": \"Soulbind\", \"region\": \"Borealia – United free Republic of Borealia (URB)\", \"base_slug\": \"lich\", \"region_slug\": \"borealia_united_free_republic_of_borealia_urb\"}, {\"base\": \"Vodyanoy\", \"choice\": \"Pondgrim\", \"region\": \"Auronia – Rodinian Tsardom (RT)\", \"base_slug\": \"vodyanoy\", \"region_slug\": \"auronia_rodinian_tsardom_rt\"}, {\"base\": \"Leshy\", \"choice\": \"Sylvadin\", \"region\": \"Auronia – Rodinian Tsardom (RT)\", \"base_slug\": \"leshy\", \"region_slug\": \"auronia_rodinian_tsardom_rt\"}, {\"base\": \"Succubus\", \"choice\": \"Subliss\", \"region\": \"Auronia – Rheinland (RL)\", \"base_slug\": \"succubus\", \"region_slug\": \"auronia_rheinland_rl\"}, {\"base\": \"Demon\", \"choice\": \"Abyssum\", \"region\": \"Auronia – Rheinland (RL)\", \"base_slug\": \"demon\", \"region_slug\": \"auronia_rheinland_rl\"}, {\"base\": \"Angel\", \"choice\": \"Cherubis\", \"region\": \"Auronia – Rheinland (RL)\", \"base_slug\": \"angel\", \"region_slug\": \"auronia_rheinland_rl\"}, {\"base\": \"Kelpie\", \"choice\": \"Bridlemare\", \"region\": \"Auronia – Bretonreach (BR)\", \"base_slug\": \"kelpie\", \"region_slug\": \"auronia_bretonreach_br\"}, {\"base\": \"Dullahan\", \"choice\": \"Neckless\", \"region\": \"Auronia – Bretonreach (BR)\", \"base_slug\": \"dullahan\", \"region_slug\": \"auronia_bretonreach_br\"}, {\"base\": \"Banshee\", \"choice\": \"Wailis\", \"region\": \"Auronia – Bretonreach (BR)\", \"base_slug\": \"banshee\", \"region_slug\": \"auronia_bretonreach_br\"}, {\"base\": \"Ratatoskr\", \"choice\": \"Skytattle\", \"region\": \"Auronia – Nornheim (NH)\", \"base_slug\": \"ratatoskr\", \"region_slug\": \"auronia_nornheim_nh\"}, {\"base\": \"Kraken\", \"choice\": \"Abyssant\", \"region\": \"Auronia – Nornheim (NH)\", \"base_slug\": \"kraken\", \"region_slug\": \"auronia_nornheim_nh\"}, {\"base\": \"Will-o-Wisp\", \"choice\": \"Glimfloat\", \"region\": \"Auronia – Bretonreach (BR)\", \"base_slug\": \"will_o_wisp\", \"region_slug\": \"auronia_bretonreach_br\"}]', '2025-09-09 12:57:46'),
(11, '[{\"base\": \"Lamia\", \"choice\": \"Viperelle\", \"region\": \"Auronia - Aegia Aeterna (AA)\", \"base_slug\": \"lamia\", \"region_slug\": \"auronia_aegia_aeterna_aa\"}, {\"base\": \"Centaur\", \"choice\": \"Hoofkin\", \"region\": \"Auronia - Aegia Aeterna (AA)\", \"base_slug\": \"centaur\", \"region_slug\": \"auronia_aegia_aeterna_aa\"}, {\"base\": \"Amaru\", \"choice\": \"Serpentupa\", \"region\": \"Verdania - Sapa Inti Empire (SIE)\", \"base_slug\": \"amaru\", \"region_slug\": \"verdania_sapa_inti_empire_sie\"}, {\"base\": \"Argentinosaurus\", \"choice\": \"Colossaur\", \"region\": \"Verdania - Sapa Inti Empire (SIE)\", \"base_slug\": \"argentinosaurus\", \"region_slug\": \"verdania_sapa_inti_empire_sie\"}, {\"base\": \"Fishman\", \"choice\": \"Neridon\", \"region\": \"Verdania - Sapa Inti Empire (SIE)\", \"base_slug\": \"fishman\", \"region_slug\": \"verdania_sapa_inti_empire_sie\"}, {\"base\": \"Capybara\", \"choice\": \"Capycurrent\", \"region\": \"Verdania - Gran Columbia (GC)\", \"base_slug\": \"capybara\", \"region_slug\": \"verdania_gran_columbia_gc\"}, {\"base\": \"Curupira\", \"choice\": \"Foresttrix\", \"region\": \"Verdania - Gran Columbia (GC)\", \"base_slug\": \"curupira\", \"region_slug\": \"verdania_gran_columbia_gc\"}, {\"base\": \"Rainbow Serpent\", \"choice\": \"Arcudra\", \"region\": \"Uluru - Yara Nations (YN)\", \"base_slug\": \"rainbow_serpent\", \"region_slug\": \"uluru_yara_nations_yn\"}, {\"base\": \"Bunyip\", \"choice\": \"Marshmurm\", \"region\": \"Uluru - Yara Nations (YN)\", \"base_slug\": \"bunyip\", \"region_slug\": \"uluru_yara_nations_yn\"}, {\"base\": \"Min-Min Lights\", \"choice\": \"Wispstray\", \"region\": \"Uluru - Red Sun Commonwealth (RSC)\", \"base_slug\": \"min_min_lights\", \"region_slug\": \"uluru_red_sun_commonwealth_rsc\"}, {\"base\": \"Drop Bear\", \"choice\": \"Plummko\", \"region\": \"Uluru - Red Sun Commonwealth (RSC)\", \"base_slug\": \"drop_bear\", \"region_slug\": \"uluru_red_sun_commonwealth_rsc\"}, {\"base\": \"Polar Bear\", \"choice\": \"Icebrawler\", \"region\": \"Tundria - Sila Council (SC)\", \"base_slug\": \"polar_bear\", \"region_slug\": \"tundria_sila_council_sc\"}, {\"base\": \"Amarok\", \"choice\": \"Snowmara\", \"region\": \"Tundria - Sila Council (SC)\", \"base_slug\": \"amarok\", \"region_slug\": \"tundria_sila_council_sc\"}, {\"base\": \"Wadjet\", \"choice\": \"Wadja\", \"region\": \"Saharene - Kemet (KM)\", \"base_slug\": \"wadjet\", \"region_slug\": \"saharene_kemet_km\"}, {\"base\": \"Anubis\", \"choice\": \"Anupet\", \"region\": \"Saharene - Kemet (KM)\", \"base_slug\": \"anubis\", \"region_slug\": \"saharene_kemet_km\"}, {\"base\": \"Dolphin\", \"choice\": \"Wavewhistle\", \"region\": \"Orienthem - Eretz-Shalem League (ESL)\", \"base_slug\": \"dolphin\", \"region_slug\": \"orienthem_eretz_shalem_league_esl\"}, {\"base\": \"Golem\", \"choice\": \"Emetron\", \"region\": \"Orienthem - Eretz-Shalem League (ESL)\", \"base_slug\": \"golem\", \"region_slug\": \"orienthem_eretz_shalem_league_esl\"}, {\"base\": \"Lamassu\", \"choice\": \"Gatebull\", \"region\": \"Orienthem - Hammurabia (HR)\", \"base_slug\": \"lamassu\", \"region_slug\": \"orienthem_hammurabia_hr\"}, {\"base\": \"Girtablilu\", \"choice\": \"Stingward\", \"region\": \"Orienthem - Hammurabia (HR)\", \"base_slug\": \"girtablilu\", \"region_slug\": \"orienthem_hammurabia_hr\"}, {\"base\": \"Bahamut\", \"choice\": \"Deepmajest\", \"region\": \"Orienthem - Crescent Caliphate (CC)\", \"base_slug\": \"bahamut\", \"region_slug\": \"orienthem_crescent_caliphate_cc\"}, {\"base\": \"Genie\", \"choice\": \"Djinna\", \"region\": \"Orienthem - Crescent Caliphate (CC)\", \"base_slug\": \"genie\", \"region_slug\": \"orienthem_crescent_caliphate_cc\"}, {\"base\": \"Taniwha\", \"choice\": \"Wakecoil\", \"region\": \"Moana Crown - Spice Route League (SRL)\", \"base_slug\": \"taniwha\", \"region_slug\": \"moana_crown_spice_route_league_srl\"}, {\"base\": \"Crab man\", \"choice\": \"Shellina\", \"region\": \"Moana Crown - Spice Route League (SRL)\", \"base_slug\": \"crab_man\", \"region_slug\": \"moana_crown_spice_route_league_srl\"}, {\"base\": \"Tapir\", \"choice\": \"Dorsnork\", \"region\": \"Gulfbelt - Itzam Empire (IE)\", \"base_slug\": \"tapir\", \"region_slug\": \"gulfbelt_itzam_empire_ie\"}, {\"base\": \"Azureus\", \"choice\": \"Cobaltoad\", \"region\": \"Gulfbelt - Itzam Empire (IE)\", \"base_slug\": \"azureus\", \"region_slug\": \"gulfbelt_itzam_empire_ie\"}, {\"base\": \"Ocelot\", \"choice\": \"Spotblade\", \"region\": \"Gulfbelt - Eagle Serpent Dominion (ESD)\", \"base_slug\": \"ocelot\", \"region_slug\": \"gulfbelt_eagle_serpent_dominion_esd\"}, {\"base\": \"Cipactli\", \"choice\": \"Mawdelta\", \"region\": \"Gulfbelt - Eagle Serpent Dominion (ESD)\", \"base_slug\": \"cipactli\", \"region_slug\": \"gulfbelt_eagle_serpent_dominion_esd\"}, {\"base\": \"Ahuizotl\", \"choice\": \"Lakegnash\", \"region\": \"Gulfbelt - Eagle Serpent Dominion (ESD)\", \"base_slug\": \"ahuizotl\", \"region_slug\": \"gulfbelt_eagle_serpent_dominion_esd\"}, {\"base\": \"Quetzalcoatl\", \"choice\": \"Featherserp\", \"region\": \"Gulfbelt - Eagle Serpent Dominion (ESD)\", \"base_slug\": \"quetzalcoatl\", \"region_slug\": \"gulfbelt_eagle_serpent_dominion_esd\"}, {\"base\": \"Charro Negro\", \"choice\": \"Charro Noct\", \"region\": \"Gulfbelt - Xochimex (XM)\", \"base_slug\": \"charro_negro\", \"region_slug\": \"gulfbelt_xochimex_xm\"}, {\"base\": \"Chupacabra\", \"choice\": \"Cabrasaur\", \"region\": \"Gulfbelt - Xochimex (XM)\", \"base_slug\": \"chupacabra\", \"region_slug\": \"gulfbelt_xochimex_xm\"}, {\"base\": \"La Llorona\", \"choice\": \"Canalcry\", \"region\": \"Gulfbelt - Xochimex (XM)\", \"base_slug\": \"la_llorona\", \"region_slug\": \"gulfbelt_xochimex_xm\"}, {\"base\": \"Yuki-Onna\", \"choice\": \"Frostrae\", \"region\": \"Dawnmarch - Yamanokubo (YK)\", \"base_slug\": \"yuki_onna\", \"region_slug\": \"dawnmarch_yamanokubo_yk\"}, {\"base\": \"Kitsune\", \"choice\": \"Kitsel\", \"region\": \"Dawnmarch - Yamanokubo (YK)\", \"base_slug\": \"kitsune\", \"region_slug\": \"dawnmarch_yamanokubo_yk\"}, {\"base\": \"Spider-Crab\", \"choice\": \"Crabgantic\", \"region\": \"Dawnmarch - Yamanokubo (YK)\", \"base_slug\": \"spider_crab\", \"region_slug\": \"dawnmarch_yamanokubo_yk\"}, {\"base\": \"Naga\", \"choice\": \"Serpanta\", \"region\": \"Dawnmarch - Baharamandal (BM)\", \"base_slug\": \"naga\", \"region_slug\": \"dawnmarch_baharamandal_bm\"}, {\"base\": \"Gandharva\", \"choice\": \"Aerialute\", \"region\": \"Dawnmarch - Baharamandal (BM)\", \"base_slug\": \"gandharva\", \"region_slug\": \"dawnmarch_baharamandal_bm\"}, {\"base\": \"Vermillion Bird\", \"choice\": \"Verbirdion\", \"region\": \"Dawnmarch - Lotus-Dragon Kingdom (LDK)\", \"base_slug\": \"vermillion_bird\", \"region_slug\": \"dawnmarch_lotus_dragon_kingdom_ldk\"}, {\"base\": \"Jiang-Shi\", \"choice\": \"Stiffwalker\", \"region\": \"Dawnmarch - Lotus-Dragon Kingdom (LDK)\", \"base_slug\": \"jiang_shi\", \"region_slug\": \"dawnmarch_lotus_dragon_kingdom_ldk\"}, {\"base\": \"Horned Serpent Uktena\", \"choice\": \"Horncoil\", \"region\": \"Borealia - Sovereign Tribes of the Ancestral Plains (STAP)\", \"base_slug\": \"horned_serpent_uktena\", \"region_slug\": \"borealia_sovereign_tribes_of_the_ancestral_plains_stap\"}, {\"base\": \"Thunderbird\", \"choice\": \"Thundara\", \"region\": \"Borealia - Sovereign Tribes of the Ancestral Plains (STAP)\", \"base_slug\": \"thunderbird\", \"region_slug\": \"borealia_sovereign_tribes_of_the_ancestral_plains_stap\"}, {\"base\": \"Jack-o-lantern\", \"choice\": \"Gourdlume\", \"region\": \"Borealia - United free Republic of Borealia (URB)\", \"base_slug\": \"jack_o_lantern\", \"region_slug\": \"borealia_united_free_republic_of_borealia_urb\"}, {\"base\": \"Lich\", \"choice\": \"Soulbind\", \"region\": \"Borealia - United free Republic of Borealia (URB)\", \"base_slug\": \"lich\", \"region_slug\": \"borealia_united_free_republic_of_borealia_urb\"}, {\"base\": \"Vodyanoy\", \"choice\": \"Pondgrim\", \"region\": \"Auronia - Rodinian Tsardom (RT)\", \"base_slug\": \"vodyanoy\", \"region_slug\": \"auronia_rodinian_tsardom_rt\"}, {\"base\": \"Leshy\", \"choice\": \"Sylvadin\", \"region\": \"Auronia - Rodinian Tsardom (RT)\", \"base_slug\": \"leshy\", \"region_slug\": \"auronia_rodinian_tsardom_rt\"}, {\"base\": \"Succubus\", \"choice\": \"Subliss\", \"region\": \"Auronia - Rheinland (RL)\", \"base_slug\": \"succubus\", \"region_slug\": \"auronia_rheinland_rl\"}, {\"base\": \"Demon\", \"choice\": \"Abyssum\", \"region\": \"Auronia - Rheinland (RL)\", \"base_slug\": \"demon\", \"region_slug\": \"auronia_rheinland_rl\"}, {\"base\": \"Angel\", \"choice\": \"Cherubis\", \"region\": \"Auronia - Rheinland (RL)\", \"base_slug\": \"angel\", \"region_slug\": \"auronia_rheinland_rl\"}, {\"base\": \"Kelpie\", \"choice\": \"Bridlemare\", \"region\": \"Auronia - Bretonreach (BR)\", \"base_slug\": \"kelpie\", \"region_slug\": \"auronia_bretonreach_br\"}, {\"base\": \"Dullahan\", \"choice\": \"Neckless\", \"region\": \"Auronia - Bretonreach (BR)\", \"base_slug\": \"dullahan\", \"region_slug\": \"auronia_bretonreach_br\"}, {\"base\": \"Banshee\", \"choice\": \"Wailis\", \"region\": \"Auronia - Bretonreach (BR)\", \"base_slug\": \"banshee\", \"region_slug\": \"auronia_bretonreach_br\"}, {\"base\": \"Kraken\", \"choice\": \"Abyssant\", \"region\": \"Auronia - Nornheim (NH)\", \"base_slug\": \"kraken\", \"region_slug\": \"auronia_nornheim_nh\"}]', '2025-11-15 17:13:14');

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `currency_id` tinyint UNSIGNED NOT NULL,
  `currency_code` varchar(16) NOT NULL,
  `display_name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`currency_id`, `currency_code`, `display_name`) VALUES
(1, 'DOSH', 'Cash-Dosh'),
(2, 'GEM', 'Premium Gems');

-- --------------------------------------------------------

--
-- Table structure for table `currency_ledger`
--

CREATE TABLE `currency_ledger` (
  `ledger_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `currency_id` tinyint UNSIGNED NOT NULL,
  `amount_delta` decimal(14,2) NOT NULL,
  `reason` varchar(64) NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `currency_ledger`
--

INSERT INTO `currency_ledger` (`ledger_id`, `user_id`, `currency_id`, `amount_delta`, `reason`, `metadata`, `created_at`) VALUES
(1, 1, 1, -220.00, 'shop_purchase', '{\"qty\": 1, \"item_id\": 3, \"shop_id\": 1}', '2025-09-02 20:37:52'),
(2, 11, 1, 1560.00, 'score_exchange_galaxian', '{\"score\": 3120}', '2025-09-10 13:54:34'),
(3, 11, 1, 72.00, 'score_exchange_tetris', '{\"score\": 60}', '2025-09-10 17:01:16'),
(4, 11, 1, 2555.00, 'score_exchange_galaxian', '{\"score\": 5110}', '2025-09-10 17:37:59'),
(5, 11, 1, 54.68, 'bank_interest', '{\"rate\": 0.025}', '2025-09-11 06:08:15'),
(6, 11, 1, 1675.00, 'score_exchange_galaxian', '{\"score\": 3350}', '2025-09-11 16:47:28'),
(7, 11, 1, 192.00, 'score_exchange_tetris', '{\"score\": 160}', '2025-09-11 17:54:14'),
(8, 11, 1, 288.00, 'score_exchange_fruitstack', '{\"score\": 240}', '2025-09-11 20:02:06'),
(9, 11, 1, 206.04, 'bank_interest', '{\"rate\": 0.025}', '2025-09-12 06:22:18'),
(10, 11, 1, 972.00, 'score_exchange_fruitstack', '{\"score\": 810}', '2025-09-12 07:44:38'),
(11, 11, 1, 3825.00, 'score_exchange_gardeninvaderz', '{\"score\": 7650}', '2025-09-12 07:45:24'),
(12, 11, 1, 2205.00, 'score_exchange_gardeninvaderz', '{\"score\": 4410}', '2025-09-12 07:46:22'),
(13, 11, 1, 672.00, 'score_exchange_fruitstack', '{\"score\": 560}', '2025-09-12 23:31:41'),
(14, 11, 1, 25150.02, 'bank_interest', '{\"rate\": 0.025}', '2025-09-13 10:54:40'),
(15, 11, 1, 11450.00, 'score_exchange_gardeninvaderz', '{\"score\": 22900}', '2025-09-13 11:04:36'),
(16, 11, 1, 26078.77, 'bank_interest', '{\"rate\": 0.025}', '2025-09-14 16:38:45'),
(17, 11, 1, 26730.74, 'bank_interest', '{\"rate\": 0.025}', '2025-09-15 11:04:47'),
(18, 11, 1, 27399.01, 'bank_interest', '{\"rate\": 0.025}', '2025-09-16 05:44:45'),
(19, 11, 1, -10.00, 'blackjack_bet', '{\"bet\": 10}', '2025-09-16 13:48:43'),
(20, 11, 1, 20.00, 'blackjack_win', '{\"bet\": 10, \"payout\": 20, \"result\": \"blackjack\"}', '2025-09-16 13:48:43'),
(21, 11, 1, -10.00, 'blackjack_bet', '{\"bet\": 10}', '2025-09-16 13:48:50'),
(22, 11, 1, -700.00, 'blackjack_bet', '{\"bet\": 700}', '2025-09-16 13:49:06'),
(23, 11, 1, 700.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 700}', '2025-09-16 15:14:56'),
(24, 11, 1, 200.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 200}', '2025-09-16 15:15:04'),
(25, 11, 1, 400.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 400}', '2025-09-16 15:15:08'),
(26, 11, 1, 400.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 400}', '2025-09-16 15:35:51'),
(33, 11, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-09-16 20:25:06'),
(34, 11, 1, 700.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 700}', '2025-09-16 20:25:06'),
(35, 11, 1, -1920.00, 'blackjack_bet', '{\"bet\": 1920}', '2025-09-16 20:39:39'),
(36, 11, 1, 3840.00, 'blackjack_win', '{\"bet\": 1920, \"payout\": 3840, \"result\": \"win\", \"dealer_total\": 26, \"player_total\": 18}', '2025-09-16 20:39:46'),
(37, 11, 1, -3840.00, 'blackjack_bet', '{\"bet\": 3840}', '2025-09-16 20:40:02'),
(38, 11, 1, 3840.00, 'blackjack_push', '{\"bet\": 3840, \"result\": \"push\", \"dealer_total\": 17, \"player_total\": 17}', '2025-09-16 20:40:06'),
(39, 11, 1, -3840.00, 'blackjack_bet', '{\"bet\": 3840}', '2025-09-16 20:40:12'),
(40, 11, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-09-16 21:50:25'),
(41, 11, 1, 189.75, 'score_exchange_wantedalive', '{\"score\": 253}', '2025-09-16 22:20:22'),
(42, 11, 1, -3050.00, 'blackjack_bet', '{\"bet\": 3050}', '2025-09-16 22:21:06'),
(43, 11, 1, 6100.00, 'blackjack_win', '{\"bet\": 3050, \"payout\": 6100, \"result\": \"blackjack\"}', '2025-09-16 22:21:06'),
(44, 11, 1, -3050.00, 'blackjack_bet', '{\"bet\": 3050}', '2025-09-16 22:21:15'),
(45, 11, 1, 6100.00, 'blackjack_win', '{\"bet\": 3050, \"payout\": 6100, \"result\": \"win\", \"dealer_total\": 18, \"player_total\": 19}', '2025-09-16 22:21:23'),
(46, 11, 1, -9150.00, 'blackjack_bet', '{\"bet\": 9150}', '2025-09-16 22:21:29'),
(47, 11, 1, 28000.00, 'bank_interest', '{\"rate\": 0.025}', '2025-09-17 10:31:07'),
(48, 11, 1, 28700.00, 'bank_interest', '{\"rate\": 0.025}', '2025-09-18 13:46:37'),
(49, 11, 1, 29417.50, 'bank_interest', '{\"rate\": 0.025}', '2025-09-19 05:58:30'),
(50, 11, 1, 30152.94, 'bank_interest', '{\"rate\": 0.025}', '2025-09-22 15:31:45'),
(51, 11, 1, 93700.80, 'score_exchange_paddlepanic', '{\"score\": 104112}', '2025-09-22 20:06:39'),
(52, 11, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-09-22 20:08:18'),
(53, 11, 1, 500.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 500}', '2025-09-22 20:08:18'),
(54, 11, 1, -700.00, 'blackjack_bet', '{\"bet\": 700}', '2025-09-22 21:45:50'),
(55, 11, 1, 1400.00, 'blackjack_win', '{\"bet\": 700, \"payout\": 1400, \"result\": \"win\", \"dealer_total\": 23, \"player_total\": 16}', '2025-09-22 21:45:52'),
(56, 11, 1, -1400.00, 'blackjack_bet', '{\"bet\": 1400}', '2025-09-22 21:46:04'),
(57, 11, 1, 2800.00, 'blackjack_win', '{\"bet\": 1400, \"payout\": 2800, \"result\": \"win\", \"dealer_total\": 22, \"player_total\": 20}', '2025-09-22 21:46:06'),
(58, 11, 1, 66652.20, 'score_exchange_paddlepanic', '{\"score\": 74058}', '2025-09-22 21:51:10'),
(59, 11, 1, 34966.89, 'bank_interest', '{\"rate\": 0.025}', '2025-09-23 06:55:44'),
(60, 11, 1, -53.00, 'blackjack_bet', '{\"bet\": 53}', '2025-09-23 11:27:50'),
(61, 11, 1, 106.00, 'blackjack_win', '{\"bet\": 53, \"payout\": 106, \"result\": \"win\", \"dealer_total\": 26, \"player_total\": 17}', '2025-09-23 11:27:51'),
(62, 11, 1, -106.00, 'blackjack_bet', '{\"bet\": 106}', '2025-09-23 11:28:00'),
(63, 11, 1, 35841.06, 'bank_interest', '{\"rate\": 0.025}', '2025-09-24 14:34:03'),
(64, 11, 1, 36737.08, 'bank_interest', '{\"rate\": 0.025}', '2025-09-26 10:00:29'),
(65, 11, 1, 37655.51, 'bank_interest', '{\"rate\": 0.025}', '2025-09-28 15:52:01'),
(66, 11, 1, 38596.90, 'bank_interest', '{\"rate\": 0.025}', '2025-09-29 20:56:55'),
(67, 11, 1, 39561.82, 'bank_interest', '{\"rate\": 0.025}', '2025-09-30 05:48:29'),
(68, 11, 1, 40550.87, 'bank_interest', '{\"rate\": 0.025}', '2025-10-01 10:25:06'),
(69, 11, 1, 41564.64, 'bank_interest', '{\"rate\": 0.025}', '2025-10-02 13:46:48'),
(70, 11, 1, 42603.76, 'bank_interest', '{\"rate\": 0.025}', '2025-10-09 21:04:43'),
(71, 11, 1, -75.00, 'blackjack_bet', '{\"bet\": 75}', '2025-10-09 21:05:42'),
(72, 11, 1, 150.00, 'blackjack_win', '{\"bet\": 75, \"payout\": 150, \"result\": \"win\", \"dealer_total\": 24, \"player_total\": 18}', '2025-10-09 21:05:44'),
(73, 11, 1, -150.00, 'blackjack_bet', '{\"bet\": 150}', '2025-10-09 21:05:56'),
(74, 11, 1, 300.00, 'blackjack_win', '{\"bet\": 150, \"payout\": 300, \"result\": \"blackjack\"}', '2025-10-09 21:05:56'),
(75, 11, 1, -300.00, 'blackjack_bet', '{\"bet\": 300}', '2025-10-09 21:06:02'),
(76, 11, 1, 600.00, 'blackjack_win', '{\"bet\": 300, \"payout\": 600, \"result\": \"win\", \"dealer_total\": 25, \"player_total\": 20}', '2025-10-09 21:06:05'),
(77, 11, 1, -600.00, 'blackjack_bet', '{\"bet\": 600}', '2025-10-09 21:06:10'),
(78, 11, 1, 43668.85, 'bank_interest', '{\"rate\": 0.025}', '2025-10-13 20:36:55'),
(79, 11, 1, 256.00, 'score_exchange_sudoku', '{\"score\": 320}', '2025-10-13 21:06:17'),
(80, 11, 1, -256.00, 'blackjack_bet', '{\"bet\": 256}', '2025-10-13 21:06:44'),
(81, 11, 1, 44760.57, 'bank_interest', '{\"rate\": 0.025}', '2025-10-19 11:19:48'),
(82, 11, 1, -75.00, 'blackjack_bet', '{\"bet\": 75}', '2025-10-19 11:20:32'),
(83, 11, 1, -75.00, 'blackjack_bet', '{\"bet\": 75}', '2025-10-19 11:20:51'),
(84, 11, 1, 150.00, 'blackjack_win', '{\"bet\": 75, \"payout\": 150, \"result\": \"win\", \"dealer_total\": 25, \"player_total\": 19}', '2025-10-19 11:20:56'),
(85, 11, 1, -300.00, 'blackjack_bet', '{\"bet\": 300}', '2025-10-19 11:21:08'),
(86, 11, 1, 45879.58, 'bank_interest', '{\"rate\": 0.025}', '2025-11-04 12:36:33'),
(87, 11, 1, 47026.57, 'bank_interest', '{\"rate\": 0.025}', '2025-11-12 22:07:32'),
(88, 11, 1, 48202.24, 'bank_interest', '{\"rate\": 0.025}', '2025-11-15 15:00:46'),
(89, 13, 1, 16.20, 'score_exchange_paddlepanic', '{\"score\": 18}', '2025-11-15 22:37:44'),
(90, 13, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-11-15 22:37:50'),
(91, 13, 1, 500.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 500}', '2025-11-15 22:37:50'),
(92, 12, 1, -10.00, 'blackjack_bet', '{\"bet\": 10}', '2025-11-15 22:55:23'),
(93, 12, 1, 20.00, 'blackjack_win', '{\"bet\": 10, \"payout\": 20, \"result\": \"win\", \"dealer_total\": 25, \"player_total\": 20}', '2025-11-15 22:55:30'),
(94, 12, 1, -3085.00, 'blackjack_bet', '{\"bet\": 3085}', '2025-11-15 22:55:41'),
(95, 12, 1, 935.00, 'score_exchange_gardeninvaderz', '{\"score\": 1870}', '2025-11-15 22:56:54'),
(96, 12, 1, 1345.00, 'score_exchange_wantedalive', '{\"score\": 269}', '2025-11-15 22:59:20'),
(97, 13, 1, 235.00, 'score_exchange_gardeninvaderz', '{\"score\": 470}', '2025-11-15 23:03:31'),
(98, 13, 1, 145.00, 'score_exchange_gardeninvaderz', '{\"score\": 290}', '2025-11-15 23:03:45'),
(99, 12, 1, 480.00, 'score_exchange_fruitstack', '{\"score\": 400}', '2025-11-15 23:05:31'),
(100, 12, 1, 48.00, 'score_exchange_runngunner', '{\"score\": 60}', '2025-11-15 23:06:48'),
(101, 12, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-11-15 23:08:09'),
(102, 14, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-11-19 10:40:49'),
(103, 14, 1, 500.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 500}', '2025-11-19 10:40:49'),
(104, 14, 1, 62.50, 'bank_interest', '{\"rate\": 0.025}', '2025-11-20 09:56:41'),
(105, 13, 1, 146.20, 'score_exchange_fishing', '{\"score\": 43}', '2025-11-20 22:37:14'),
(106, 13, 1, 142.80, 'score_exchange_fishing', '{\"score\": 42}', '2025-11-20 22:38:24'),
(107, 5, 1, 2142.00, 'score_exchange_fishing', '{\"score\": 63}', '2025-12-01 08:25:01'),
(108, 5, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-12-01 08:25:09'),
(109, 5, 1, 1000.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 1000}', '2025-12-01 08:25:09'),
(110, 5, 1, 996.00, 'score_exchange_fruitstack', '{\"score\": 830}', '2025-12-01 08:35:10'),
(111, 5, 1, 880.00, 'score_exchange_gardeninvaderz', '{\"score\": 1760}', '2025-12-01 08:36:21'),
(112, 5, 1, -4500.00, 'blackjack_bet', '{\"bet\": 4500}', '2025-12-01 08:36:34'),
(113, 5, 1, 9000.00, 'blackjack_win', '{\"bet\": 4500, \"payout\": 9000, \"result\": \"win\", \"dealer_total\": 17, \"player_total\": 19}', '2025-12-01 08:36:42'),
(114, 5, 1, -9000.00, 'blackjack_bet', '{\"bet\": 9000}', '2025-12-01 08:36:54'),
(115, 5, 1, 1122.00, 'score_exchange_fishing', '{\"score\": 33}', '2025-12-08 10:45:54'),
(116, 5, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-12-08 11:14:30'),
(117, 5, 1, 1000.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 1000}', '2025-12-08 11:14:30'),
(118, 5, 1, -1640.00, 'blackjack_bet', '{\"bet\": 1640}', '2025-12-08 11:15:08'),
(119, 5, 1, 1640.00, 'blackjack_push', '{\"bet\": 1640, \"result\": \"push\", \"dealer_total\": 18, \"player_total\": 18}', '2025-12-08 11:15:16'),
(120, 5, 1, -1640.00, 'blackjack_bet', '{\"bet\": 1640}', '2025-12-08 11:15:19'),
(121, 5, 1, 740.00, 'score_exchange_gardeninvaderz', '{\"score\": 1480}', '2025-12-08 11:16:32'),
(122, 5, 1, -740.00, 'blackjack_bet', '{\"bet\": 740}', '2025-12-08 11:16:43'),
(123, 13, 1, 1946.68, 'bank_interest', '{\"rate\": 0.025}', '2025-12-12 18:19:47'),
(124, 5, 1, 138875000.00, 'bank_interest', '{\"rate\": 0.025}', '2025-12-13 11:30:46'),
(125, 5, 1, 142346875.00, 'bank_interest', '{\"rate\": 0.025}', '2025-12-15 07:35:14'),
(126, 5, 1, -1000.00, 'rsc_wheel_spin', '{\"cost\": 1000}', '2025-12-15 13:48:12'),
(127, 5, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-12-15 13:51:59'),
(128, 5, 1, -100.00, 'aa_wof_spin', '{\"cost\": 100}', '2025-12-15 14:01:19'),
(129, 5, 1, -100.00, 'aa_wof_spin', '{\"cost\": 100}', '2025-12-15 14:32:11'),
(130, 5, 1, -100.00, 'aa_wof_spin', '{\"cost\": 100}', '2025-12-15 14:32:12'),
(131, 5, 1, -100.00, 'aa_wof_spin', '{\"cost\": 100}', '2025-12-15 14:32:13'),
(132, 5, 1, -100.00, 'aa_wof_spin', '{\"cost\": 100}', '2025-12-15 14:32:29'),
(133, 5, 1, 145905546.88, 'bank_interest', '{\"rate\": 0.025}', '2025-12-16 07:09:30'),
(134, 5, 1, -100.00, 'aa_wof_spin', '{\"cost\": 100}', '2025-12-16 07:09:46'),
(135, 5, 1, -50.00, 'aa_wof_spin', '{\"cost\": 50}', '2025-12-16 07:28:51'),
(136, 5, 1, -1000.00, 'rsc_wheel_spin', '{\"cost\": 1000}', '2025-12-16 07:29:04'),
(137, 5, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-12-16 07:29:17'),
(138, 5, 1, 500.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 500}', '2025-12-16 07:29:17'),
(139, 5, 1, -50.00, 'aa_wof_spin', '{\"cost\": 50}', '2025-12-16 07:29:58'),
(140, 16, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-12-16 08:16:25'),
(141, 5, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2025-12-16 09:48:43'),
(142, 5, 1, 149553185.55, 'bank_interest', '{\"rate\": 0.025}', '2025-12-17 22:18:18'),
(143, 5, 1, 153292015.19, 'bank_interest', '{\"rate\": 0.025}', '2025-12-18 13:00:39'),
(144, 5, 1, -1000.00, 'wheel_of_fate_spin', '{\"cost\": 1000}', '2025-12-18 16:48:57'),
(145, 5, 1, -1000.00, 'rsc_wheel_spin', '{\"cost\": 1000}', '2025-12-18 16:50:03'),
(146, 5, 1, -1000.00, 'wheel_of_fate_spin', '{\"cost\": 1000}', '2025-12-18 19:00:14'),
(147, 5, 1, 157124315.57, 'bank_interest', '{\"rate\": 0.025}', '2025-12-19 22:44:09'),
(148, 5, 1, 161052423.45, 'bank_interest', '{\"rate\": 0.025}', '2025-12-20 00:15:08'),
(149, 5, 1, -1000.00, 'wheel_of_fate_spin', '{\"cost\": 1000}', '2025-12-20 00:15:14'),
(150, 5, 1, -1000.00, 'wheel_of_fate_spin', '{\"cost\": 1000}', '2025-12-20 00:16:28'),
(151, 5, 1, 165078734.04, 'bank_interest', '{\"rate\": 0.025}', '2025-12-21 22:23:20'),
(152, 5, 1, 169205702.39, 'bank_interest', '{\"rate\": 0.025}', '2025-12-22 16:50:12'),
(153, 5, 1, 173435844.95, 'bank_interest', '{\"rate\": 0.025}', '2025-12-23 08:48:43'),
(154, 5, 1, -1000.00, 'wheel_of_fate_spin', '{\"cost\": 1000}', '2025-12-23 08:49:45'),
(155, 17, 1, -1000.00, 'wheel_of_fate_spin', '{\"cost\": 1000}', '2025-12-23 14:13:26'),
(156, 17, 1, 1385.00, 'score_exchange_wantedalive', '{\"score\": 277}', '2025-12-23 14:18:27'),
(157, 5, 1, 177771741.08, 'bank_interest', '{\"rate\": 0.025}', '2025-12-27 23:47:30'),
(158, 5, 1, -850.00, 'fairy_fountain_deposit', '{\"deposit\": 850}', '2025-12-27 23:48:00'),
(159, 5, 1, 182216034.60, 'bank_interest', '{\"rate\": 0.025}', '2025-12-29 07:27:26'),
(160, 5, 1, -22.00, 'fairy_fountain_deposit', '{\"deposit\": 22}', '2025-12-29 09:25:12'),
(161, 5, 1, -1000.00, 'wheel_of_fate_spin', '{\"cost\": 1000}', '2025-12-29 09:27:21'),
(162, 5, 1, 186771435.47, 'bank_interest', '{\"rate\": 0.025}', '2025-12-30 07:52:08'),
(163, 5, 1, 191440721.35, 'bank_interest', '{\"rate\": 0.025}', '2026-01-05 07:50:42'),
(164, 13, 1, 1995.34, 'bank_interest', '{\"rate\": 0.025}', '2026-01-05 16:40:01'),
(165, 13, 1, 2045.23, 'bank_interest', '{\"rate\": 0.025}', '2026-01-06 13:47:48'),
(166, 5, 1, 196226739.39, 'bank_interest', '{\"rate\": 0.025}', '2026-01-06 15:23:08'),
(167, 5, 1, 201132407.87, 'bank_interest', '{\"rate\": 0.025}', '2026-01-07 09:11:45'),
(168, 13, 1, 2096.36, 'bank_interest', '{\"rate\": 0.025}', '2026-01-07 11:16:37'),
(169, 5, 1, 206160718.07, 'bank_interest', '{\"rate\": 0.025}', '2026-01-09 11:58:12'),
(170, 5, 1, 211314736.02, 'bank_interest', '{\"rate\": 0.025}', '2026-01-10 11:56:58'),
(171, 5, 1, 216597604.42, 'bank_interest', '{\"rate\": 0.025}', '2026-01-12 07:38:45'),
(172, 5, 1, 222012544.53, 'bank_interest', '{\"rate\": 0.025}', '2026-01-12 23:01:17'),
(173, 13, 1, 2148.77, 'bank_interest', '{\"rate\": 0.025}', '2026-01-13 12:41:48'),
(174, 5, 1, 227562858.15, 'bank_interest', '{\"rate\": 0.025}', '2026-01-14 07:57:39'),
(175, 13, 1, 2202.48, 'bank_interest', '{\"rate\": 0.025}', '2026-01-14 11:01:12'),
(176, 5, 1, 233251929.60, 'bank_interest', '{\"rate\": 0.025}', '2026-01-15 11:47:28'),
(177, 5, 1, 239083227.84, 'bank_interest', '{\"rate\": 0.025}', '2026-01-16 10:43:06'),
(178, 5, 1, 245060308.54, 'bank_interest', '{\"rate\": 0.025}', '2026-01-16 23:04:13'),
(179, 5, 1, 1326.00, 'score_exchange_fishing', '{\"score\": 39}', '2026-01-16 23:32:09'),
(180, 18, 1, -1000.00, 'blackjack_bet', '{\"bet\": 1000}', '2026-01-16 23:44:06'),
(181, 18, 1, 1000.00, 'blackjack_push', '{\"bet\": 1000, \"result\": \"push\", \"dealer_total\": 18, \"player_total\": 18}', '2026-01-16 23:44:31'),
(182, 18, 1, -1000.00, 'blackjack_bet', '{\"bet\": 1000}', '2026-01-16 23:44:35'),
(183, 18, 1, -1000.00, 'blackjack_bet', '{\"bet\": 1000}', '2026-01-16 23:44:51'),
(184, 18, 1, 2000.00, 'blackjack_win', '{\"bet\": 1000, \"payout\": 2000, \"result\": \"win\", \"dealer_total\": 23, \"player_total\": 19}', '2026-01-16 23:44:54'),
(185, 18, 1, -1000.00, 'blackjack_bet', '{\"bet\": 1000}', '2026-01-16 23:44:56'),
(186, 18, 1, 1000.00, 'blackjack_push', '{\"bet\": 1000, \"result\": \"push\", \"dealer_total\": 20, \"player_total\": 20}', '2026-01-16 23:45:03'),
(187, 18, 1, -1000.00, 'blackjack_bet', '{\"bet\": 1000}', '2026-01-16 23:45:07'),
(188, 18, 1, 2000.00, 'blackjack_win', '{\"bet\": 1000, \"payout\": 2000, \"result\": \"win\", \"dealer_total\": 18, \"player_total\": 19}', '2026-01-16 23:45:10'),
(189, 18, 1, -1000.00, 'blackjack_bet', '{\"bet\": 1000}', '2026-01-16 23:45:13'),
(190, 18, 1, 1000.00, 'blackjack_push', '{\"bet\": 1000, \"result\": \"push\", \"dealer_total\": 17, \"player_total\": 17}', '2026-01-16 23:45:32'),
(191, 18, 1, -1000.00, 'blackjack_bet', '{\"bet\": 1000}', '2026-01-16 23:45:35'),
(192, 18, 1, 2000.00, 'blackjack_win', '{\"bet\": 1000, \"payout\": 2000, \"result\": \"win\", \"dealer_total\": 18, \"player_total\": 19}', '2026-01-16 23:45:39'),
(193, 18, 1, 33.30, 'score_exchange_paddlepanic', '{\"score\": 37}', '2026-01-16 23:47:17'),
(194, 18, 1, 1598.00, 'score_exchange_fishing', '{\"score\": 47}', '2026-01-16 23:48:01'),
(195, 18, 1, 890.00, 'score_exchange_gardeninvaderz', '{\"score\": 1780}', '2026-01-16 23:48:22'),
(196, 18, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2026-01-16 23:51:09'),
(197, 18, 1, 1000.00, 'wheel_of_fate', '{\"type\": \"currency\", \"amount\": 1000}', '2026-01-16 23:51:09'),
(198, 5, 1, 251186816.25, 'bank_interest', '{\"rate\": 0.025}', '2026-01-18 13:45:45'),
(199, 5, 1, 257466486.66, 'bank_interest', '{\"rate\": 0.025}', '2026-01-19 06:58:37'),
(200, 13, 1, 2257.55, 'bank_interest', '{\"rate\": 0.025}', '2026-01-19 14:41:29'),
(201, 5, 1, 263903148.82, 'bank_interest', '{\"rate\": 0.025}', '2026-01-20 12:03:49'),
(202, 5, 1, 270500727.54, 'bank_interest', '{\"rate\": 0.025}', '2026-01-21 07:50:19'),
(203, 5, 1, 277263245.73, 'bank_interest', '{\"rate\": 0.025}', '2026-01-23 09:22:35'),
(204, 5, 1, 284194826.87, 'bank_interest', '{\"rate\": 0.025}', '2026-01-25 19:42:59'),
(205, 5, 1, 291299697.55, 'bank_interest', '{\"rate\": 0.025}', '2026-01-26 07:35:27'),
(206, 5, 1, 298582189.98, 'bank_interest', '{\"rate\": 0.025}', '2026-01-28 07:22:12'),
(207, 5, 1, 306046744.73, 'bank_interest', '{\"rate\": 0.025}', '2026-02-03 18:12:28'),
(208, 5, 1, 313697913.35, 'bank_interest', '{\"rate\": 0.025}', '2026-02-04 09:00:25'),
(209, 5, 1, 321540361.19, 'bank_interest', '{\"rate\": 0.025}', '2026-02-06 20:55:28'),
(210, 5, 1, 329578870.22, 'bank_interest', '{\"rate\": 0.025}', '2026-02-07 10:00:03'),
(211, 5, 1, 337818341.97, 'bank_interest', '{\"rate\": 0.025}', '2026-02-08 11:36:24'),
(212, 5, 1, 346263800.52, 'bank_interest', '{\"rate\": 0.025}', '2026-02-09 09:38:42'),
(213, 5, 1, 354920395.53, 'bank_interest', '{\"rate\": 0.025}', '2026-02-10 10:01:24'),
(214, 5, 1, 363793405.42, 'bank_interest', '{\"rate\": 0.025}', '2026-02-11 08:51:03'),
(215, 5, 1, 372888240.56, 'bank_interest', '{\"rate\": 0.025}', '2026-02-12 12:56:54'),
(216, 5, 1, 382210446.57, 'bank_interest', '{\"rate\": 0.025}', '2026-02-13 07:42:20'),
(217, 5, 1, -1000.00, 'wheel_of_fate_spin', '{\"cost\": 1000}', '2026-02-13 08:03:28'),
(218, 5, 1, 391765707.74, 'bank_interest', '{\"rate\": 0.025}', '2026-02-14 00:07:59'),
(219, 13, 1, 2313.99, 'bank_interest', '{\"rate\": 0.025}', '2026-02-14 00:23:23'),
(220, 5, 1, 401559850.43, 'bank_interest', '{\"rate\": 0.025}', '2026-02-14 23:11:15'),
(221, 5, 1, 411598846.69, 'bank_interest', '{\"rate\": 0.025}', '2026-02-16 07:57:53'),
(222, 5, 1, 421888817.86, 'bank_interest', '{\"rate\": 0.025}', '2026-02-16 23:11:45'),
(223, 5, 1, 432436038.30, 'bank_interest', '{\"rate\": 0.025}', '2026-02-18 09:55:42'),
(224, 5, 1, 443246939.26, 'bank_interest', '{\"rate\": 0.025}', '2026-02-19 22:49:39'),
(225, 5, 1, 454328112.74, 'bank_interest', '{\"rate\": 0.025}', '2026-02-20 09:09:10'),
(226, 5, 1, 465686315.56, 'bank_interest', '{\"rate\": 0.025}', '2026-02-20 23:49:13'),
(227, 5, 1, 477328473.45, 'bank_interest', '{\"rate\": 0.025}', '2026-02-22 20:47:17'),
(228, 5, 1, 489261685.29, 'bank_interest', '{\"rate\": 0.025}', '2026-02-25 11:53:54'),
(229, 5, 1, 501493227.42, 'bank_interest', '{\"rate\": 0.025}', '2026-02-27 07:36:13'),
(230, 13, 1, 2371.84, 'bank_interest', '{\"rate\": 0.025}', '2026-02-27 13:07:13'),
(231, 13, 1, -500.00, 'wheel_of_fate_spin', '{\"cost\": 500}', '2026-02-27 13:11:08'),
(232, 13, 1, 2431.13, 'bank_interest', '{\"rate\": 0.025}', '2026-03-02 13:27:08'),
(233, 13, 1, 1170.00, 'score_exchange_wantedalive', '{\"score\": 234}', '2026-03-02 13:31:04'),
(234, 5, 1, 514030558.10, 'bank_interest', '{\"rate\": 0.025}', '2026-03-04 07:52:35'),
(235, 5, 1, 526881322.06, 'bank_interest', '{\"rate\": 0.025}', '2026-03-06 07:52:05'),
(236, 13, 1, 2491.91, 'bank_interest', '{\"rate\": 0.025}', '2026-03-06 10:59:51'),
(237, 5, 1, 540053355.11, 'bank_interest', '{\"rate\": 0.025}', '2026-03-07 19:38:43'),
(238, 5, 1, 553554688.99, 'bank_interest', '{\"rate\": 0.025}', '2026-03-09 09:36:57'),
(239, 5, 1, 567393556.21, 'bank_interest', '{\"rate\": 0.025}', '2026-03-14 23:47:49'),
(240, 5, 1, 581578395.12, 'bank_interest', '{\"rate\": 0.025}', '2026-03-16 22:02:18'),
(241, 5, 1, 596117854.99, 'bank_interest', '{\"rate\": 0.025}', '2026-03-17 12:05:20'),
(242, 5, 1, 611020801.37, 'bank_interest', '{\"rate\": 0.025}', '2026-03-18 10:37:55'),
(243, 5, 1, 626296321.40, 'bank_interest', '{\"rate\": 0.025}', '2026-03-19 11:51:05'),
(244, 5, 1, -1000.00, 'wheel_of_fate_spin', '{\"cost\": 1000}', '2026-03-19 19:00:31'),
(245, 5, 1, 641953729.44, 'bank_interest', '{\"rate\": 0.025}', '2026-03-19 23:31:29'),
(246, 13, 1, 2554.21, 'bank_interest', '{\"rate\": 0.025}', '2026-03-20 14:07:00'),
(247, 13, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 116}', '2026-03-20 14:10:10'),
(248, 13, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 33}', '2026-03-20 14:10:14'),
(249, 13, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 91}', '2026-03-20 14:10:17'),
(250, 5, 1, 658002572.67, 'bank_interest', '{\"rate\": 0.025}', '2026-03-20 23:08:25'),
(251, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 68}', '2026-03-20 23:08:33'),
(252, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 124}', '2026-03-20 23:21:36'),
(253, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 41}', '2026-03-20 23:21:42'),
(254, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 5}', '2026-03-20 23:21:57'),
(255, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 122}', '2026-03-20 23:30:45'),
(256, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 147}', '2026-03-20 23:30:48'),
(257, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 157}', '2026-03-20 23:33:42'),
(258, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 152}', '2026-03-20 23:33:45'),
(259, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 19}', '2026-03-20 23:36:53'),
(260, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 68}', '2026-03-20 23:38:19'),
(261, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 52}', '2026-03-20 23:38:31'),
(262, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 166}', '2026-03-20 23:41:30'),
(263, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 52}', '2026-03-20 23:41:33'),
(264, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 72}', '2026-03-20 23:46:59'),
(265, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 115}', '2026-03-20 23:47:29'),
(266, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 151}', '2026-03-20 23:47:32'),
(267, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 16}', '2026-03-20 23:47:35'),
(268, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 78}', '2026-03-21 12:44:35'),
(269, 5, 1, -25.00, 'yamanokubo_gacha_spin', '{\"cost\": 25, \"item_id\": 160}', '2026-03-21 12:44:43'),
(270, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 3}', '2026-03-21 13:08:18'),
(271, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 52}', '2026-03-21 13:08:23'),
(272, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 90}', '2026-03-21 13:08:50'),
(273, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 53}', '2026-03-21 13:08:53'),
(274, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 90}', '2026-03-21 13:17:24'),
(275, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 86}', '2026-03-21 13:17:28'),
(276, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 60}', '2026-03-21 13:17:31'),
(277, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 15}', '2026-03-21 13:17:33'),
(278, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 74}', '2026-03-21 13:17:36'),
(279, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 77}', '2026-03-21 13:24:16'),
(280, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 92}', '2026-03-21 13:24:21'),
(281, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 81}', '2026-03-21 13:24:24'),
(282, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 1}', '2026-03-21 13:24:26'),
(283, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 93}', '2026-03-21 13:24:29'),
(284, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 49}', '2026-03-21 21:13:53'),
(285, 5, 1, 674452636.99, 'bank_interest', '{\"rate\": 0.025}', '2026-03-23 07:37:17'),
(286, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 84}', '2026-03-23 07:37:39'),
(287, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 49}', '2026-03-23 07:37:43'),
(288, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 110}', '2026-03-23 07:37:48'),
(289, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 119}', '2026-03-23 07:38:05'),
(290, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 52}', '2026-03-23 07:38:08'),
(291, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 113}', '2026-03-23 07:38:12'),
(292, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 71}', '2026-03-23 07:39:18'),
(293, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 58}', '2026-03-23 07:39:24'),
(294, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 67}', '2026-03-23 07:39:34'),
(295, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 19}', '2026-03-23 07:41:12'),
(296, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 31}', '2026-03-23 07:41:16'),
(297, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 53}', '2026-03-23 07:41:20'),
(298, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 61}', '2026-03-23 07:41:27'),
(299, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 20}', '2026-03-23 08:09:05'),
(300, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 105}', '2026-03-23 08:12:08'),
(301, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 68}', '2026-03-23 08:12:15'),
(302, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 57}', '2026-03-23 08:39:49'),
(303, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 75}', '2026-03-23 08:45:59'),
(304, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 61}', '2026-03-23 08:46:07'),
(305, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 113}', '2026-03-23 08:46:11'),
(306, 5, 1, 691313952.92, 'bank_interest', '{\"rate\": 0.025}', '2026-03-24 07:20:54'),
(307, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 95}', '2026-03-24 13:51:16'),
(308, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 220}', '2026-03-24 13:51:19'),
(309, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 219}', '2026-03-24 13:51:21'),
(310, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 48}', '2026-03-24 13:51:23'),
(311, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 116}', '2026-03-24 13:51:26'),
(312, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 30}', '2026-03-24 13:51:29'),
(313, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 14}', '2026-03-24 13:51:32'),
(314, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 25}', '2026-03-24 13:51:34'),
(315, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 62}', '2026-03-24 13:51:36'),
(316, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 41}', '2026-03-24 13:51:39'),
(317, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 95}', '2026-03-24 13:51:41'),
(318, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 86}', '2026-03-24 13:51:43'),
(319, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 90}', '2026-03-24 13:51:46'),
(320, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 42}', '2026-03-24 13:51:48'),
(321, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 46}', '2026-03-24 13:51:50'),
(322, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 30}', '2026-03-24 13:51:52'),
(323, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 102}', '2026-03-24 13:51:54'),
(324, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 65}', '2026-03-24 13:51:57'),
(325, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 214}', '2026-03-24 13:51:59'),
(326, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 55}', '2026-03-24 13:52:01'),
(327, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 104}', '2026-03-24 13:52:04'),
(328, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 14}', '2026-03-24 13:52:07'),
(329, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 27}', '2026-03-24 13:52:12'),
(330, 5, 1, -100.00, 'yamanokubo_gacha_spin', '{\"cost\": 100, \"item_id\": 2}', '2026-03-24 13:52:14'),
(331, 13, 1, 2618.06, 'bank_interest', '{\"rate\": 0.025}', '2026-03-25 08:34:33'),
(332, 5, 1, 708596801.74, 'bank_interest', '{\"rate\": 0.025}', '2026-03-25 15:31:34'),
(333, 5, 1, 726311721.78, 'bank_interest', '{\"rate\": 0.025}', '2026-03-27 07:25:13'),
(334, 5, 1, 744469514.83, 'bank_interest', '{\"rate\": 0.025}', '2026-03-30 07:38:13'),
(335, 5, 1, 763081252.70, 'bank_interest', '{\"rate\": 0.025}', '2026-04-01 06:38:19'),
(336, 5, 1, 782158284.01, 'bank_interest', '{\"rate\": 0.025}', '2026-04-03 15:01:58'),
(337, 5, 1, 801712241.11, 'bank_interest', '{\"rate\": 0.025}', '2026-04-04 11:40:12'),
(338, 5, 1, 821755047.14, 'bank_interest', '{\"rate\": 0.025}', '2026-04-07 05:27:59'),
(339, 5, 1, 842298923.32, 'bank_interest', '{\"rate\": 0.025}', '2026-04-11 20:40:58'),
(340, 5, 1, 863356396.40, 'bank_interest', '{\"rate\": 0.025}', '2026-04-12 07:20:31');

-- --------------------------------------------------------

--
-- Table structure for table `daily_fom_fishing_runs`
--

CREATE TABLE `daily_fom_fishing_runs` (
  `user_id` bigint UNSIGNED NOT NULL,
  `run_date` date NOT NULL,
  `caught_item_id` bigint UNSIGNED DEFAULT NULL,
  `completed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `daily_fom_fishing_runs`
--

INSERT INTO `daily_fom_fishing_runs` (`user_id`, `run_date`, `caught_item_id`, `completed_at`) VALUES
(5, '2026-01-14', NULL, '2026-01-14 14:38:22'),
(5, '2026-01-15', NULL, '2026-01-15 12:48:48'),
(5, '2026-02-11', NULL, '2026-02-11 10:12:05'),
(5, '2026-03-25', 114, '2026-03-25 16:32:09');

-- --------------------------------------------------------

--
-- Table structure for table `daily_sudoku_runs`
--

CREATE TABLE `daily_sudoku_runs` (
  `user_id` bigint UNSIGNED NOT NULL,
  `run_date` date NOT NULL,
  `difficulty_percent` tinyint UNSIGNED NOT NULL,
  `base_score` int UNSIGNED NOT NULL DEFAULT '0',
  `final_score` int UNSIGNED NOT NULL DEFAULT '0',
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `daily_sudoku_runs`
--

INSERT INTO `daily_sudoku_runs` (`user_id`, `run_date`, `difficulty_percent`, `base_score`, `final_score`, `completed_at`) VALUES
(5, '2026-01-17', 63, 0, 0, NULL),
(11, '2025-10-13', 65, 0, 0, NULL),
(11, '2025-10-19', 84, 0, 0, NULL),
(11, '2025-11-12', 54, 0, 0, NULL),
(18, '2026-01-17', 16, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `elements`
--

CREATE TABLE `elements` (
  `element_id` smallint UNSIGNED NOT NULL,
  `element_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `elements`
--

INSERT INTO `elements` (`element_id`, `element_name`) VALUES
(6, 'Aer'),
(10, 'Arthropoda'),
(13, 'Cold'),
(4, 'Electra'),
(14, 'Ethereal'),
(18, 'Fae'),
(16, 'Ferrum'),
(7, 'Floor'),
(5, 'Flora'),
(2, 'Heat'),
(12, 'Kampf'),
(15, 'Kuro'),
(11, 'Malus'),
(9, 'Mental'),
(8, 'Stone'),
(3, 'Vai'),
(1, 'Vulgaris'),
(17, 'Wyrm');

-- --------------------------------------------------------

--
-- Table structure for table `element_calc`
--

CREATE TABLE `element_calc` (
  `element_id` smallint UNSIGNED NOT NULL,
  `target_element_id` smallint UNSIGNED NOT NULL,
  `effectiveness` decimal(6,2) NOT NULL DEFAULT '1.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `element_calc`
--

INSERT INTO `element_calc` (`element_id`, `target_element_id`, `effectiveness`) VALUES
(1, 1, 1.00),
(1, 2, 1.00),
(1, 3, 1.00),
(1, 4, 1.00),
(1, 5, 1.00),
(1, 6, 1.00),
(1, 7, 1.00),
(1, 8, 0.50),
(1, 9, 1.00),
(1, 10, 1.00),
(1, 11, 1.00),
(1, 12, 1.00),
(1, 13, 1.00),
(1, 14, 0.00),
(1, 15, 1.00),
(1, 16, 0.50),
(1, 17, 1.00),
(1, 18, 1.00),
(2, 1, 1.00),
(2, 2, 0.50),
(2, 3, 0.50),
(2, 4, 1.00),
(2, 5, 2.00),
(2, 6, 1.00),
(2, 7, 1.00),
(2, 8, 0.50),
(2, 9, 1.00),
(2, 10, 2.00),
(2, 11, 1.00),
(2, 12, 1.00),
(2, 13, 2.00),
(2, 14, 1.00),
(2, 15, 1.00),
(2, 16, 2.00),
(2, 17, 0.50),
(2, 18, 1.00),
(3, 1, 1.00),
(3, 2, 2.00),
(3, 3, 0.50),
(3, 4, 1.00),
(3, 5, 0.50),
(3, 6, 1.00),
(3, 7, 2.00),
(3, 8, 2.00),
(3, 9, 1.00),
(3, 10, 1.00),
(3, 11, 1.00),
(3, 12, 1.00),
(3, 13, 1.00),
(3, 14, 1.00),
(3, 15, 1.00),
(3, 16, 1.00),
(3, 17, 0.50),
(3, 18, 1.00),
(4, 1, 1.00),
(4, 2, 1.00),
(4, 3, 2.00),
(4, 4, 0.50),
(4, 5, 0.50),
(4, 6, 2.00),
(4, 7, 0.00),
(4, 8, 1.00),
(4, 9, 1.00),
(4, 10, 1.00),
(4, 11, 1.00),
(4, 12, 1.00),
(4, 13, 1.00),
(4, 14, 1.00),
(4, 15, 1.00),
(4, 16, 1.00),
(4, 17, 0.50),
(4, 18, 1.00),
(5, 1, 1.00),
(5, 2, 0.50),
(5, 3, 2.00),
(5, 4, 1.00),
(5, 5, 0.50),
(5, 6, 0.50),
(5, 7, 2.00),
(5, 8, 2.00),
(5, 9, 1.00),
(5, 10, 0.50),
(5, 11, 0.50),
(5, 12, 1.00),
(5, 13, 1.00),
(5, 14, 1.00),
(5, 15, 1.00),
(5, 16, 0.50),
(5, 17, 0.50),
(5, 18, 1.00),
(6, 1, 1.00),
(6, 2, 1.00),
(6, 3, 1.00),
(6, 4, 0.50),
(6, 5, 2.00),
(6, 6, 1.00),
(6, 7, 1.00),
(6, 8, 0.50),
(6, 9, 1.00),
(6, 10, 2.00),
(6, 11, 1.00),
(6, 12, 2.00),
(6, 13, 1.00),
(6, 14, 1.00),
(6, 15, 1.00),
(6, 16, 0.50),
(6, 17, 1.00),
(6, 18, 1.00),
(7, 1, 1.00),
(7, 2, 2.00),
(7, 3, 1.00),
(7, 4, 2.00),
(7, 5, 0.50),
(7, 6, 0.00),
(7, 7, 1.00),
(7, 8, 2.00),
(7, 9, 1.00),
(7, 10, 0.50),
(7, 11, 2.00),
(7, 12, 1.00),
(7, 13, 1.00),
(7, 14, 1.00),
(7, 15, 1.00),
(7, 16, 2.00),
(7, 17, 1.00),
(7, 18, 1.00),
(8, 1, 1.00),
(8, 2, 2.00),
(8, 3, 1.00),
(8, 4, 1.00),
(8, 5, 1.00),
(8, 6, 2.00),
(8, 7, 0.50),
(8, 8, 1.00),
(8, 9, 1.00),
(8, 10, 2.00),
(8, 11, 1.00),
(8, 12, 0.50),
(8, 13, 2.00),
(8, 14, 1.00),
(8, 15, 1.00),
(8, 16, 0.50),
(8, 17, 1.00),
(8, 18, 1.00),
(9, 1, 1.00),
(9, 2, 1.00),
(9, 3, 1.00),
(9, 4, 1.00),
(9, 5, 1.00),
(9, 6, 1.00),
(9, 7, 1.00),
(9, 8, 1.00),
(9, 9, 0.50),
(9, 10, 1.00),
(9, 11, 2.00),
(9, 12, 2.00),
(9, 13, 1.00),
(9, 14, 1.00),
(9, 15, 0.00),
(9, 16, 0.50),
(9, 17, 1.00),
(9, 18, 1.00),
(10, 1, 1.00),
(10, 2, 0.50),
(10, 3, 1.00),
(10, 4, 1.00),
(10, 5, 2.00),
(10, 6, 0.50),
(10, 7, 1.00),
(10, 8, 1.00),
(10, 9, 2.00),
(10, 10, 1.00),
(10, 11, 0.50),
(10, 12, 0.50),
(10, 13, 1.00),
(10, 14, 0.50),
(10, 15, 2.00),
(10, 16, 0.50),
(10, 17, 1.00),
(10, 18, 0.50),
(11, 1, 1.00),
(11, 2, 1.00),
(11, 3, 1.00),
(11, 4, 1.00),
(11, 5, 2.00),
(11, 6, 1.00),
(11, 7, 0.50),
(11, 8, 0.50),
(11, 9, 1.00),
(11, 10, 1.00),
(11, 11, 0.50),
(11, 12, 1.00),
(11, 13, 1.00),
(11, 14, 0.50),
(11, 15, 1.00),
(11, 16, 0.00),
(11, 17, 1.00),
(11, 18, 2.00),
(12, 1, 2.00),
(12, 2, 1.00),
(12, 3, 1.00),
(12, 4, 1.00),
(12, 5, 1.00),
(12, 6, 0.50),
(12, 7, 1.00),
(12, 8, 2.00),
(12, 9, 0.50),
(12, 10, 0.50),
(12, 11, 0.50),
(12, 12, 1.00),
(12, 13, 2.00),
(12, 14, 0.00),
(12, 15, 2.00),
(12, 16, 2.00),
(12, 17, 1.00),
(12, 18, 0.50),
(13, 1, 1.00),
(13, 2, 0.50),
(13, 3, 0.50),
(13, 4, 1.00),
(13, 5, 2.00),
(13, 6, 2.00),
(13, 7, 2.00),
(13, 8, 1.00),
(13, 9, 1.00),
(13, 10, 1.00),
(13, 11, 1.00),
(13, 12, 1.00),
(13, 13, 0.50),
(13, 14, 1.00),
(13, 15, 1.00),
(13, 16, 0.50),
(13, 17, 2.00),
(13, 18, 1.00),
(14, 1, 0.00),
(14, 2, 1.00),
(14, 3, 1.00),
(14, 4, 1.00),
(14, 5, 1.00),
(14, 6, 1.00),
(14, 7, 1.00),
(14, 8, 1.00),
(14, 9, 2.00),
(14, 10, 1.00),
(14, 11, 1.00),
(14, 12, 1.00),
(14, 13, 1.00),
(14, 14, 2.00),
(14, 15, 0.50),
(14, 16, 1.00),
(14, 17, 1.00),
(14, 18, 1.00),
(15, 1, 1.00),
(15, 2, 1.00),
(15, 3, 1.00),
(15, 4, 1.00),
(15, 5, 1.00),
(15, 6, 1.00),
(15, 7, 1.00),
(15, 8, 1.00),
(15, 9, 2.00),
(15, 10, 1.00),
(15, 11, 1.00),
(15, 12, 0.50),
(15, 13, 1.00),
(15, 14, 2.00),
(15, 15, 0.50),
(15, 16, 1.00),
(15, 17, 1.00),
(15, 18, 0.50),
(16, 1, 1.00),
(16, 2, 0.50),
(16, 3, 0.50),
(16, 4, 0.50),
(16, 5, 1.00),
(16, 6, 1.00),
(16, 7, 1.00),
(16, 8, 2.00),
(16, 9, 1.00),
(16, 10, 1.00),
(16, 11, 1.00),
(16, 12, 1.00),
(16, 13, 2.00),
(16, 14, 1.00),
(16, 15, 1.00),
(16, 16, 0.50),
(16, 17, 1.00),
(16, 18, 2.00),
(17, 1, 1.00),
(17, 2, 1.00),
(17, 3, 1.00),
(17, 4, 1.00),
(17, 5, 1.00),
(17, 6, 1.00),
(17, 7, 1.00),
(17, 8, 1.00),
(17, 9, 1.00),
(17, 10, 1.00),
(17, 11, 1.00),
(17, 12, 1.00),
(17, 13, 1.00),
(17, 14, 1.00),
(17, 15, 1.00),
(17, 16, 0.50),
(17, 17, 2.00),
(17, 18, 0.00),
(18, 1, 1.00),
(18, 2, 0.50),
(18, 3, 1.00),
(18, 4, 1.00),
(18, 5, 1.00),
(18, 6, 1.00),
(18, 7, 1.00),
(18, 8, 1.00),
(18, 9, 1.00),
(18, 10, 1.00),
(18, 11, 0.50),
(18, 12, 2.00),
(18, 13, 1.00),
(18, 14, 1.00),
(18, 15, 2.00),
(18, 16, 0.50),
(18, 17, 2.00),
(18, 18, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `fairy_fountain_visits`
--

CREATE TABLE `fairy_fountain_visits` (
  `user_id` bigint UNSIGNED NOT NULL,
  `visit_date` date NOT NULL,
  `deposited_amount` decimal(14,2) NOT NULL,
  `reward_key` varchar(64) DEFAULT NULL,
  `reward_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `fairy_fountain_visits`
--

INSERT INTO `fairy_fountain_visits` (`user_id`, `visit_date`, `deposited_amount`, `reward_key`, `reward_note`, `created_at`) VALUES
(5, '2025-12-28', 850.00, 'heal_plus5_party', '+5 HP to every creature.', '2025-12-27 23:48:00'),
(5, '2025-12-29', 22.00, 'heal_plus5_single', '+5 HP to one creature.', '2025-12-29 09:25:12');

-- --------------------------------------------------------

--
-- Table structure for table `food_preferences`
--

CREATE TABLE `food_preferences` (
  `food_pref_id` bigint UNSIGNED NOT NULL,
  `species_id` smallint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `like_scale` tinyint UNSIGNED NOT NULL DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `food_preferences`
--

INSERT INTO `food_preferences` (`food_pref_id`, `species_id`, `item_id`, `like_scale`) VALUES
(1, 183, 25, 2),
(2, 183, 26, 2),
(3, 228, 1, 1),
(4, 242, 1, 2),
(5, 196, 1, 2),
(6, 173, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` bigint UNSIGNED NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_description` text,
  `base_price` decimal(12,2) DEFAULT NULL,
  `rarity_id` smallint UNSIGNED DEFAULT NULL,
  `category_id` smallint UNSIGNED DEFAULT NULL,
  `max_stack` int UNSIGNED NOT NULL DEFAULT '99',
  `tradable` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `replenish` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `item_description`, `base_price`, `rarity_id`, `category_id`, `max_stack`, `tradable`, `created_at`, `replenish`) VALUES
(1, 'Berry', 'A juicy forest berry. Restores a little HP.', 5.00, 1, 1, 99, 1, '2025-09-02 18:32:42', 5),
(2, 'Healing Potion', 'Restores 50 HP.', 50.00, 2, 3, 20, 1, '2025-09-02 18:32:42', 50),
(3, 'Iron Sword', 'A sturdy beginner blade.', 200.00, 2, 2, 1, 1, '2025-09-02 18:32:42', 1),
(4, 'Wizard Hat', 'Stylish and pointy. Boosts magic.', 500.00, 3, 4, 1, 1, '2025-09-02 18:32:42', 1),
(5, 'Mana Elixir', 'Restores 40 MP.', 75.00, 3, 3, 20, 1, '2025-09-02 18:32:42', 40),
(6, 'Red Paint', 'Paints your creature red.', 50000.00, 3, 5, 1, 1, '2025-09-02 18:32:42', 1),
(7, 'Blue Paint', 'Paints your creature blue.', 50000.00, 3, 5, 1, 1, '2025-09-02 18:32:42', 1),
(8, 'Yellow Paint', 'Paints your creature yellow.', 50000.00, 3, 5, 1, 1, '2025-09-02 18:32:42', 1),
(9, 'Green Paint', 'Paints your creature green.', 50000.00, 3, 5, 1, 1, '2025-09-02 18:32:42', 1),
(10, 'Purple Paint', 'Paints your creature purple.', 50000.00, 3, 5, 1, 1, '2025-09-02 18:32:42', 1),
(11, 'Black Paint', 'Paints your creature black.', 1000000.00, 4, 5, 1, 1, '2025-09-02 18:32:42', 1),
(12, 'Realistic Paint', 'Paints your creature realistic.', 2005000.00, 5, 5, 1, 1, '2025-09-02 18:32:42', 1),
(13, 'Crystal Shard', 'A rare sparkling material.', 1200.00, 4, 6, 99, 1, '2025-09-02 18:32:42', 1),
(14, 'Cheese Pizza with Salami', 'A pizza drenched in cheese sauce and speckled with salami', 20.00, 2, 1, 99, 1, '2025-11-04 12:57:35', 60),
(15, 'Pizza Prosciutto', 'A nice old classic. Cheese strips melted evenly across the dough that has been prepared with tomato sauce, topped with the best prosciutto this world has to offer for less than the minimum price of this pizza. The pizza is cooked in an oven made of marble, which gives it an surprising quality: it\'s the same as normal store-bought pizza, which is pretty surprising.', 25.00, 2, 1, 99, 1, '2025-11-04 14:15:46', 70),
(16, 'Pizza al Funghi', 'Another classic for people, who like the taste of Pizza, but would love to strangle people at night. Depending on the mushrooms being used, they might even have a point...', 27.00, 2, 1, 99, 1, '2025-11-16 14:26:39', 70),
(17, 'Dessert Pizza Strawberry Banana', 'A luxurious experience that just screams lavish indulgence in wealth. The Pizza is baked with a sweet dough and the signature strawberry cream first, then put into the fridge, before being decorated with banana pieces. \r\nPeople might grow jealous of the luxury you\'re flaunting, but its decadent taste is surely worth it... Until you feel the cold and hard embrace of a pilum in your stomach...', 35.00, 3, 1, 99, 1, '2025-11-16 14:34:46', 70),
(18, 'Blackberry Kiwi Pizza', 'One of the ultimate sins against Pizza, but if it is a sin... Why does it taste so good?', 40.00, 4, 1, 99, 1, '2025-11-16 21:00:45', 70),
(19, 'Lemon Pizza', 'Sour things have a certain crowd as well, right?', 35.00, 3, 1, 99, 1, '2025-11-16 21:00:45', 50),
(20, 'Lime Pizza', 'That just screams of a crime...', 28.00, 3, 1, 99, 1, '2025-11-16 21:04:38', 50),
(21, 'Raclette Cranberry Pizza', 'A store clerk made this crazy experiment', 30.00, 3, 1, 99, 1, '2025-11-16 21:04:38', 90),
(22, 'Marble Pizza', 'A pizza that just seems like a joke.', 20.00, 2, 1, 99, 1, '2025-11-16 21:06:55', 20),
(23, 'Polygon Paint', 'A throwback to a less difficult time. Where tits were pointy, 60fps were a dream in a programmer\'s eye and gamers were still allowed to be pervy. ', 51658184.00, 6, 5, 99, 1, '2025-11-21 23:51:55', 1),
(24, 'Inverted Paint', 'Do you wanna reflect your negative emotions with a similar paintjob?\r\nHere ya go!', 1200000.00, 6, 5, 99, 1, '2025-11-24 20:45:59', 1),
(25, '8-bit Pizza', 'The pizza that bytes back lmfao', 35.00, 2, 1, 99, 1, '2025-11-27 20:03:07', 89),
(26, 'Anchovy Pizza', 'An acquired taste', 22.00, 1, 1, 99, 1, '2025-11-27 20:03:49', 80),
(27, 'Artichoke Pizza', 'If you\'re a mom in your 50s, this pizza\'s for you', 25.00, 1, 1, 99, 1, '2025-11-27 20:05:23', 82),
(28, 'Asparagus and Yogurt Pizza', 'The Asparagus is cooked. The yogurt isn\'t.', 26.00, 1, 1, 99, 1, '2025-11-27 20:05:23', 83),
(29, 'Banana and Kiwi Pizza', 'For dessert and nothing else. An abomination of nature', NULL, 2, 1, 99, 1, '2025-11-27 20:08:08', 87),
(30, 'Blackberry Pizza', 'Another dessert pizza, this time it\'s sour.', 29.00, 2, 1, 99, 1, '2025-11-27 20:47:16', 85),
(31, 'Blue Pepperoni Pizza', 'Wait, what? What\'s the blue? Why blue? That can\'t be good, can it?', 35.00, 2, 1, 99, 1, '2025-11-27 20:49:27', 86),
(32, 'Blue Pizza', 'What\'s the blue stuff? Who knows?', 31.00, 2, 1, 99, 1, '2025-11-27 20:49:27', 89),
(33, 'Broccoli and Cheese Pizza', 'One doesn\'t fit the other...', 32.00, 2, 1, 99, 1, '2025-11-27 20:54:00', 91),
(34, 'Bubbling Blueberry Pizza', 'Why does it still bubble?', 32.00, 2, 1, 99, 1, '2025-11-27 20:54:00', 90),
(35, 'Bullseye Pizza', 'Pizza that makes you wanna shoot it with an arrow.', 35.00, 2, 1, 99, 1, '2025-11-27 20:57:04', 80),
(36, 'Caramel Pizza', 'A dessert pizza, that seems really sticky.', 36.00, 2, 1, 99, 1, '2025-11-27 20:57:04', 75),
(37, 'Cauliflower and Lentils Pizza', 'Strange but healthy option.', 32.00, 2, 1, 99, 1, '2025-11-27 21:00:24', 85),
(38, 'Cheese Bacon Milkshake', 'How do you get the cheese into that perfect milkshake consistency? Who knows. But there\'s bacon.', 29.00, 2, 1, 99, 1, '2025-11-27 21:00:24', 79),
(39, 'Dung Pizza', 'Something one can\'t think of as good.', 19.00, 2, 1, 99, 1, '2025-11-27 21:04:45', 10),
(40, 'Italian Lemon Tea', 'A relaxing, albeit sour, tea. It aims to soothe your soul and any ailments you have.', 10.00, 1, 1, 99, 1, '2025-11-28 07:08:42', 40),
(41, 'Pakistani Sewage Pipe Tea', 'How is this a thing, you ask? Don\'t! There\'s weird people everywhere.', 7.00, 1, 1, 99, 1, '2025-11-28 07:18:59', 20),
(42, 'Tibetan Yak Butter Dreams Tea', 'What does this combination of words even mean?', 9.00, 1, 1, 99, 1, '2025-11-28 07:18:59', 41),
(43, 'Transparent Pizza', 'Pizza with an interesting quirk: It\'s totally see-through. Nobody knows how, but it just works. And the Community didn\'t even have to mod it.', 35.00, 3, 1, 99, 1, '2025-11-28 07:57:01', 60),
(44, 'Trostlose Zukunft Pizza', 'The pizza is called \"bleak future\". You don\'t need to ask why. The sauce is made up of Wasabi, sauce hollandaise and ranch. Then a whole ear of corn is topped by a single piece of prosciutto that looks like a cape on it and added to the pizza. Nobody knows how to eat it, but you don\'t need to, either.', 34.00, 1, 1, 99, 1, '2025-11-28 08:00:22', 60),
(45, 'Vietnamese Tigerblossom Tea', 'An odd name for such a relaxing tea.', 8.00, 1, 1, 99, 1, '2025-11-28 08:01:08', 40),
(46, 'Energy Drink', 'A sugar laden drink aimed at children.', 7.00, 1, 1, 99, 1, '2025-11-30 22:50:00', 20),
(47, 'Energry Drink Blackberry', 'The popular Energy Drink has a knock off. But hey, it has blackberry flavor', 6.00, 1, 1, 99, 1, '2025-11-30 22:50:00', 15),
(48, 'Asparagus Drink', 'A drink that smells like piss, tastes like piss and is nutritious... Like piss...', 4.00, 1, 1, 99, 1, '2025-12-01 22:38:39', 1),
(49, 'Energy Drink Raspberry', 'An Energy Drink that tastes like the popular berry, refreshing and pristine', 7.00, 1, 1, 99, 1, '2025-12-01 22:44:08', 35),
(50, 'Energy Drink Kiwi', 'An Energy Drink that tastes like the tropical fruit, growing like a weed in some gardens', 8.00, 1, 1, 99, 1, '2025-12-01 22:44:08', 38),
(51, 'Energy Drink Pineapple', 'An Energy Drink that tastes like the popular fruit, which some people like on pizza', 8.00, 1, 1, 99, 1, '2025-12-01 22:44:08', 38),
(52, 'Energy Drink Blueberry', 'An Energy Drink that tastes like the blue berry, a full taste washing down your throat', 7.00, 1, 1, 99, 1, '2025-12-01 22:44:08', 35),
(53, 'Energy Drink Stir Fry', 'An Energy Drink that tastes like the a nice dinner, but... why???', 5.00, 1, 1, 99, 1, '2025-12-01 22:44:08', 21),
(54, 'Cumcumnut', 'A nut, that has a bitter aftertaste. But somehow, all female creatures under the sun love it.', 5.00, 1, 1, 99, 1, '2025-12-02 21:48:33', 15),
(55, 'White Strawberry', 'A strange strawberry... It\'s white...', 3.00, 1, 1, 99, 1, '2025-12-02 21:48:33', 11),
(56, 'Chocopear', 'A pear, that naturally grows on trees, where its chocolate comes in the longer it stays on the branch.', 20.00, 3, 1, 99, 1, '2025-12-02 21:48:33', 35),
(57, 'Milky Love Apple', 'A sweet fruit, that is said to be the bringer of childbearing. Or better said: The consumer becomes horny af.', 55.00, 4, 1, 99, 1, '2025-12-02 21:48:33', 25),
(58, 'Milkmelon', 'A sweet fruit, that is nourished by wetnurse\'s milk.', 45.00, 3, 1, 99, 1, '2025-12-02 21:48:33', 45),
(59, 'Milkmelon Slice', 'A slice of the delicious and rare fruit.', 15.00, 1, 1, 99, 1, '2025-12-02 21:48:33', 25),
(60, 'Milk Noodles', 'Noodles made out of the creamy milk of centaurs in Aegia Aeterna.', 15.00, 3, 1, 99, 1, '2025-12-02 21:48:33', 35),
(61, 'Milk Burger', 'A normal burger, but made out of Jack-o-lantern milk.', 25.00, 1, 1, 99, 1, '2025-12-02 21:48:33', 50),
(62, 'Fruit Pizza', 'Lars: \"Nei\".', 25.00, 1, 1, 99, 1, '2025-12-02 21:48:33', 50),
(63, 'Fruit Burger', 'A burger made entirely out of fruit, nobody\'s really sure why it exists.', 15.00, 3, 1, 99, 1, '2025-12-02 21:48:33', 40),
(64, 'Takoyaki', 'The classic from Yamanokubo\'s westernmost port-towns, made out of batter balls, which get sprinkled with octopus parts during the frying process. Careful: Hot!!!', 8.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 30),
(65, 'Energry Drink Primiticy Papaya', 'The knockoff has a nice flavor, even if you\'re unsure, what \'Primiticy\' means.', 10.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 40),
(66, 'Energy Drink Chill Mango', 'An Energy Drink with the taste of mango. It\'s chilling, apparently...', 10.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 40),
(67, 'Energy Drink Punishment Heaven', 'Yes, yes, yes, yes, one, two, one, two! The energy drink for people, who are punished for their lavish lifestyle by being forced to run on treadmills in front of spikes and similar torturous fitness... In Rheinland they say \'Sport ist Mord!\'', 15.00, 3, 1, 99, 1, '2025-12-02 21:48:33', 30),
(68, 'Energy Drink Napalm', 'A fiery hot drink for fiery hot people with no sensitive parts. It is the ultimate EXXXTREEEME DRINK!!!!.', 18.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 20),
(69, 'Energy Drink Pink Banana', 'Why is the banana pink? Nobody knows! Maybe there\'s mutated bananas wherever they make these drinks...', 10.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 40),
(70, 'Energy Drink Eggplant', 'Can\'t forget the flying eggplant! An odd taste experience, that seems to delight some rare folk... Who need to get institutionalized...', 10.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 40),
(71, 'Purple Toilet Meat', 'Aromatic surprise: The smell fluctuates between lavender, disinfectant, and a public restroom after a long weekend.', 10.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 40),
(72, 'Gold-tooth Soup', 'The soup of the rich and toothless. Tastes like wealth, but sounds suspiciously metallic when chewed.', 10.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 40),
(73, 'Scissor Salad', 'Crisp, fresh, and full of surprises! If you feel a crunch, it might not be the dressing.', 20.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 30),
(74, 'Mummy Stew', 'Slowly cooked over millennia. Tender, aromatic, and with a hint of sand – truly a taste of history!', 20.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 50),
(75, 'Waffle', 'A tender snack, perfect with honey or melted butter.', 10.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 40),
(76, 'Baumkuchen', 'A Kuchen that is a Baum.', 20.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 40),
(77, 'Cream Puffs', 'This dessert is delicious, but gone as fast as you can blink.', 30.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 50),
(78, 'Spaghetti', 'The famous Aegian dinner option, even better with the current sauce \'Bolognese\'. Where\'s that? I don\'t know...', 35.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 80),
(79, 'Milkshake', 'Shaken Milk. Creamy...', 15.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 30),
(81, 'Cheeseburger', 'An all Borelian classic with cheese.', 15.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 50),
(82, 'Hamburger', 'An all Borelian classic without cheese.', 12.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 50),
(83, 'Salad', 'You don\'t win friends with Salads.', 15.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 30),
(84, 'Ramen', 'Many people travel to Yamanokubo just to try it.', 11.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 70),
(85, 'Gyoza', 'Dumplings imported from the Lotus-Dragon Kingdom, but made in Yamanokubo.', 9.00, 2, 1, 99, 1, '2025-12-02 21:48:33', 50),
(86, 'Milk Pizza', 'A Pizza made of milk, concocted in a dark basement underneath a Borealian Pizzeria, because real Aegians won\'t touch that thing.', 35.00, 3, 1, 99, 1, '2025-12-02 21:48:33', 70),
(87, 'Shoyu Ramen', 'Light soy broth with bamboo, nori, and egg.', 65.00, 2, 1, 10, 1, '2025-12-16 07:54:27', 20),
(88, 'Miso Tonkotsu Ramen', 'Earthy miso and pork marrow topped with charred scallions.', 82.00, 2, 1, 10, 1, '2025-12-16 07:54:27', 35),
(89, 'Yuzu Iced Tea', 'Citrus-sweet tea brewed to cut through rich broth.', 28.00, 1, 1, 20, 1, '2025-12-16 07:54:27', 10),
(90, 'Cynthia Milk', 'It\'s milk from Cynthia the Centaur. Rich and creamy in texture, letting you forget that you\'re actually drinking breast milk of a centaur, that might not even be hers... \r\nOr do you think that all that stock in the shops is really hers?', 35.00, 1, 1, 99, 1, '2025-12-20 13:37:00', 75),
(91, 'Quint Paint', 'Lets certain creatures take on the appearance of the core 5 heroines from a rom-com anime.', 5555555.00, 6, 5, 15, 1, '2026-01-14 09:11:08', 1),
(92, 'Baconfish', 'This delicious fish is drenched in oil.', 30.00, 2, 1, 99, 1, '2026-01-14 09:11:08', 75),
(93, 'Bassfish', 'A fish without a bass', 12.00, 2, 1, 99, 1, '2026-01-14 09:16:40', 70),
(94, 'Bassguitarfish', 'A fish with a bass... But it\'s not a bass...', 25.00, 2, 1, 99, 1, '2026-01-14 09:16:40', 80),
(95, 'Burgerfish', 'A soggy burger... Fish...', 35.00, 2, 1, 99, 1, '2026-01-14 09:26:25', 88),
(96, 'Catfish', 'Doesn\'t that just make you seem silly for falling for it on dating platforms? I understand... I can relate...', 36.00, 2, 1, 99, 1, '2026-01-14 09:26:25', 70),
(97, 'Cloverfish', 'Something that happened, when the fish jumped out of its home onto a field of clovers.', 25.00, 2, 1, 99, 1, '2026-01-14 10:24:12', 50),
(98, 'Cokefish', 'Somebody just tossed a whole bottle of Coke into this fish\'s home. This is what happened to it.', 20.00, 2, 1, 99, 1, '2026-01-14 10:24:12', 70),
(99, 'Dogfish', 'A good boy jumped into this fish\'s home.', 40.00, 3, 1, 99, 1, '2026-01-14 10:28:29', 70),
(100, 'Elffish', 'A beautiful fish.', 75.00, 3, 1, 99, 1, '2026-01-14 10:30:59', 90),
(101, 'El-Fish', 'A Xochimexian fish, who has had a little too much tequila.', 50.00, 3, 1, 99, 1, '2026-01-14 10:30:59', 50),
(102, 'Energy Fish', 'Not a new energy drink, but a fish that has had too much of it...', 20.00, 2, 1, 99, 1, '2026-01-14 10:35:40', 50),
(103, 'Fishpot', 'A tea pot fell into the water, this emerged.', 50.00, 2, 1, 99, 1, '2026-01-14 10:35:40', 40),
(104, 'Fishroom', 'Who dumps mushrooms into grachten?', 50.00, 3, 1, 99, 1, '2026-01-14 10:39:29', 20),
(105, 'Fishwitch', 'That little guy\'s gonna curse ya!', 60.00, 3, 1, 99, 1, '2026-01-14 10:39:29', 40),
(106, 'Fishwich', 'Not related to the Fishwitch, instead it\'s a tasty alternative to being cursed... Even if it\'s soggy...', 25.00, 2, 1, 99, 1, '2026-01-14 10:44:46', 90),
(107, 'Foxfish', 'This mischievous fish likes to eat hens and eggs.', 40.00, 2, 1, 99, 1, '2026-01-14 10:44:46', 15),
(108, 'Glassfish', 'A transparent beauty.', 80.00, 2, 1, 99, 1, '2026-01-14 10:47:56', 10),
(109, 'Goldfish', 'A normal goldfish that somebody dumped in the grachten.', 20.00, 2, 1, 99, 1, '2026-01-14 10:47:56', 20),
(110, 'Human Fish', 'This abomination happened, when a girl fell into the grachten.', 100.00, 4, 1, 99, 1, '2026-01-14 10:49:49', 10),
(111, 'Jellyfish', 'A fish made out of jelly.', 20.00, 2, 1, 99, 1, '2026-01-14 10:49:49', 70),
(112, 'Loaffish', 'A loaf of fish', 15.00, 1, 1, 99, 1, '2026-01-14 13:32:07', 70),
(113, 'Nino Fish', 'Tsundere turned runaway train Deredere. Now as a fish.', 70.00, 4, 1, 99, 1, '2026-01-14 13:32:07', 20),
(114, 'Parrotfish', 'It imitates weird stuff sometimes...', 20.00, 2, 1, 99, 1, '2026-01-14 13:33:58', 50),
(115, 'Silverfish', 'The antithesis to the goldfish', 25.00, 1, 1, 99, 1, '2026-01-14 13:33:58', 50),
(116, 'Starfish', 'Not the starfish you\'re thinking about.', 40.00, 2, 1, 99, 1, '2026-01-14 13:35:46', 50),
(117, 'Tadpolefish', 'Cohabitation gone wrong.', 12.00, 1, 1, 99, 1, '2026-01-14 13:35:46', 60),
(118, 'Toastfish', 'Toasty', 35.00, 2, 1, 99, 1, '2026-01-14 13:37:05', 85),
(119, 'Tofufish', 'Tasteless, but very good for you', 12.00, 2, 1, 99, 1, '2026-01-14 13:37:05', 15),
(120, 'Weedfish', 'When you need a hit of that dank and damp.', 80.00, 5, 1, 99, 1, '2026-01-14 13:37:55', 20),
(121, 'Plush Paint', 'A kind of paint, perfect for long and lonely nights.', 8125666.00, 6, 5, 15, 1, '2026-01-14 22:13:12', 1),
(122, 'Sunglasses', 'Glasses against... hold on... THE SUN!', 75.00, 2, 4, 99, 1, '2026-01-15 21:40:58', 1),
(123, '16-Bit Paint', 'Paints your creature 16-bit.', 250580.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(124, '8-Bit Paint', 'Paints your creature 8-bit.', 95230.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(125, 'Agent Paint', 'Paints your creature agent.', 151420.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(126, 'Baby Paint', 'Paints your creature baby.', 177740.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(127, 'Bikini Paint', 'Paints your creature bikini.', 232780.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(128, 'Blackwhite Paint', 'Paints your creature blackwhite.', 331210.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(129, 'Bordeaux Paint', 'Paints your creature bordeaux.', 83950.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(130, 'Brown Paint', 'Paints your creature brown.', 358950.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(131, 'Burlap Paint', 'Paints your creature burlap.', 478940.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(132, 'Candy Paint', 'Paints your creature candy.', 137360.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(133, 'Checkered Paint', 'Paints your creature checkered.', 182800.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(134, 'Cheese Paint', 'Paints your creature cheese.', 242160.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(135, 'Chocolate Paint', 'Paints your creature chocolate.', 441110.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(136, 'Christmas Paint', 'Paints your creature christmas.', 67720.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(137, 'Clay Paint', 'Paints your creature clay.', 254580.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(138, 'Cloud Paint', 'Paints your creature cloud.', 345630.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(139, 'Cookie Paint', 'Paints your creature cookie.', 263860.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(140, 'Coral Paint', 'Paints your creature coral.', 111120.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(141, 'Cowboy Paint', 'Paints your creature cowboy.', 454990.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(142, 'Creepy Paint', 'Paints your creature creepy.', 303950.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(143, 'Cyan Paint', 'Paints your creature cyan.', 231920.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(144, 'Desert Paint', 'Paints your creature desert.', 484080.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(145, 'Eww Paint', 'Paints your creature eww.', 85020.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(146, 'Fabric Paint', 'Paints your creature fabric.', 348890.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(147, 'Fairy Paint', 'Paints your creature fairy.', 276490.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(148, 'Fantasy Paint', 'Paints your creature fantasy.', 118530.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(149, 'Feral Paint', 'Paints your creature feral.', 390390.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(150, 'Festival Paint', 'Paints your creature festival.', 135330.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(151, 'Fire Paint', 'Paints your creature fire.', 390190.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(152, 'Forest Camouflage Paint', 'Paints your creature forest camouflage.', 143310.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(153, 'Funny Paint', 'Paints your creature funny.', 357510.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(154, 'Giraffe Paint', 'Paints your creature giraffe.', 410180.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(155, 'Gold Paint', 'Paints your creature gold.', 203560.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(156, 'Granite Paint', 'Paints your creature granite.', 85230.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(157, 'Grey Paint', 'Paints your creature grey.', 50000.00, 4, 5, 15, 1, '2026-03-08 23:59:44', 1),
(158, 'Gummy Paint', 'Paints your creature gummy.', 340800.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(159, 'Gyaru Paint', 'Paints your creature gyaru.', 492950.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(160, 'Hero Paint', 'Paints your creature hero.', 51540.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(161, 'Holiday Paint', 'Paints your creature holiday.', 489320.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(162, 'Honey Paint', 'Paints your creature honey.', 325780.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(163, 'Ice Cream Paint', 'Paints your creature ice cream.', 283090.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(164, 'Ice Paint', 'Paints your creature ice.', 243840.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(165, 'Island Paint', 'Paints your creature island.', 363720.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(166, 'Jello Paint', 'Paints your creature jello.', 375470.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(167, 'Leather Paint', 'Paints your creature leather.', 372690.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(168, 'Maid Paint', 'Paints your creature maid.', 323960.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(169, 'Marble Paint', 'Paints your creature marble.', 165710.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(170, 'MGE Paint', 'Paints your creature mge.', 185230.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(171, 'Milk Paint', 'Paints your creature milk.', 159990.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(172, 'Moss Paint', 'Paints your creature moss.', 478290.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(173, 'Muffin Paint', 'Paints your creature muffin.', 119730.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(174, 'Neon Paint', 'Paints your creature neon.', 307790.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(175, 'Old Paint', 'Paints your creature old.', 71990.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(176, 'Orange Paint', 'Paints your creature orange.', 50000.00, 4, 5, 15, 1, '2026-03-08 23:59:44', 1),
(177, 'Origami Paint', 'Paints your creature origami.', 308110.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(178, 'Pencil Paint', 'Paints your creature pencil.', 211870.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(179, 'Pink Paint', 'Paints your creature pink.', 50000.00, 4, 5, 15, 1, '2026-03-08 23:59:44', 1),
(180, 'Pirate Paint', 'Paints your creature pirate.', 245690.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(181, 'Python Paint', 'Paints your creature python.', 446200.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(182, 'Quintessential Quality Paint', 'Paints your creature quintessential quality.', 208660.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(183, 'Rainbow Paint', 'Paints your creature rainbow.', 375440.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(184, 'Regal Paint', 'Paints your creature regal.', 333800.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(185, 'Relic Paint', 'Paints your creature relic.', 182610.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(186, 'Scary Paint', 'Paints your creature scary.', 222220.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(187, 'Shadow Paint', 'Paints your creature shadow.', 82640.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(188, 'Silver Paint', 'Paints your creature silver.', 433020.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(189, 'Skyblue Paint', 'Paints your creature skyblue.', 271670.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(190, 'Snow Paint', 'Paints your creature snow.', 142990.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(191, 'Spaghetti Paint', 'Paints your creature spaghetti.', 257800.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(192, 'Split Paint', 'Paints your creature split.', 107670.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(193, 'Sponge Paint', 'Paints your creature sponge.', 332070.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(194, 'Strawberry Paint', 'Paints your creature strawberry.', 195290.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(195, 'Sunburnt Paint', 'Paints your creature sunburnt.', 300640.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(196, 'Sunset Paint', 'Paints your creature sunset.', 376940.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(197, 'Synthwave Paint', 'Paints your creature synthwave.', 154110.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(198, 'Thunder Paint', 'Paints your creature thunder.', 422830.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(199, 'Toon Paint', 'Paints your creature toon.', 236170.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(200, 'Topless Paint', 'Paints your creature topless.', 360490.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(201, 'Toy Paint', 'Paints your creature toy.', 488030.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(202, 'Training Paint', 'Paints your creature training.', 382290.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(203, 'Transparent Paint', 'Paints your creature transparent.', 388690.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(204, 'Valentine Paint', 'Paints your creature valentine.', 129110.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(205, 'Voxel Paint', 'Paints your creature voxel.', 161750.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(206, 'Water Paint', 'Paints your creature water.', 151380.00, 6, 5, 15, 1, '2026-03-08 23:59:44', 1),
(207, 'White Paint', 'Paints your creature white.', 50000.00, 4, 5, 15, 1, '2026-03-08 23:59:44', 1),
(208, 'Wizard Paint', 'Paints your creature wizard.', 269930.00, 5, 5, 15, 1, '2026-03-08 23:59:44', 1),
(209, 'Zebra Paint', 'Paints your creature zebra.', 106360.00, 7, 5, 15, 1, '2026-03-08 23:59:44', 1),
(210, 'Deluxe Mushroom', 'Deluxe Mushroom', 100.10, 5, 1, 99, 1, '2026-03-23 22:31:59', 10),
(211, 'Deluxe Sexdoll', 'Deluxe Sexdoll', 1000.00, 5, 1, 99, 1, '2026-03-23 22:31:59', 100),
(212, 'Deluxe Meat', 'Deluxe Meat', 140.00, 3, 1, 99, 1, '2026-03-23 22:31:59', 50),
(213, 'Deluxe Watermelon', 'Deluxe Watermelon', 120.00, 5, 1, 99, 1, '2026-03-23 22:31:59', 90),
(214, 'Deluxe Strawberry', 'Deluxe Strawberry', 110.00, 5, 1, 99, 1, '2026-03-23 22:31:59', 80),
(215, 'Deluxe Peach', 'Deluxe Peach', 115.00, 5, 1, 99, 1, '2026-03-23 22:31:59', 50),
(216, 'Schwanz', 'A salami with a funny name if you\'re German.', 200.00, 5, 1, 99, 1, '2026-03-23 22:31:59', 70),
(217, 'Premium Meat', 'Premium Meat', 300.00, 4, 1, 99, 1, '2026-03-23 22:31:59', 15),
(218, 'Premium Matcha', 'Premium Matcha', 250.00, 5, 1, 99, 1, '2026-03-23 22:31:59', 50),
(219, 'Pharaoh Choco', 'Pharaoh Choco', 110.00, 5, 1, 99, 1, '2026-03-23 22:31:59', 18),
(220, 'Pharaoh Chocodrink', 'Pharaoh Chocodrink', 180.00, 5, 1, 99, 1, '2026-03-23 22:31:59', 20),
(221, 'Macarons', 'Macarons', 150.00, 5, 1, 99, 1, '2026-03-23 22:31:59', 11),
(222, 'Luxurious Water', 'Luxurious Water', 95.00, 5, 1, 99, 1, '2026-03-23 22:31:59', 10);

-- --------------------------------------------------------

--
-- Table structure for table `item_categories`
--

CREATE TABLE `item_categories` (
  `category_id` smallint UNSIGNED NOT NULL,
  `category_name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `item_categories`
--

INSERT INTO `item_categories` (`category_id`, `category_name`) VALUES
(7, 'Book'),
(1, 'Food'),
(6, 'Misc'),
(5, 'Paint'),
(3, 'Potion'),
(2, 'Weapon'),
(4, 'Wearable');

-- --------------------------------------------------------

--
-- Table structure for table `item_instances`
--

CREATE TABLE `item_instances` (
  `instance_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `owner_user_id` bigint UNSIGNED NOT NULL,
  `durability` int DEFAULT NULL,
  `bound_to_user` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `item_instances`
--

INSERT INTO `item_instances` (`instance_id`, `item_id`, `owner_user_id`, `durability`, `bound_to_user`, `created_at`) VALUES
(1, 3, 1, 100, 0, '2025-09-02 20:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `item_rarities`
--

CREATE TABLE `item_rarities` (
  `rarity_id` smallint UNSIGNED NOT NULL,
  `rarity_name` varchar(40) NOT NULL,
  `rarity_rank` smallint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `item_rarities`
--

INSERT INTO `item_rarities` (`rarity_id`, `rarity_name`, `rarity_rank`) VALUES
(1, 'Worth dirt', 1),
(2, 'Uncommon', 2),
(3, 'Rare', 3),
(4, 'SR', 4),
(5, 'SSR', 5),
(6, 'UR', 6),
(7, 'One of a kind', 7);

-- --------------------------------------------------------

--
-- Table structure for table `moves`
--

CREATE TABLE `moves` (
  `move_id` bigint UNSIGNED NOT NULL,
  `move_key` varchar(64) NOT NULL,
  `move_name` varchar(100) NOT NULL,
  `element_id` smallint UNSIGNED NOT NULL,
  `category` enum('physical','special','status') NOT NULL,
  `power` smallint UNSIGNED DEFAULT NULL,
  `accuracy_percent` decimal(5,2) DEFAULT NULL,
  `pp` smallint UNSIGNED NOT NULL DEFAULT '10',
  `priority` tinyint NOT NULL DEFAULT '0',
  `target_mode` enum('adjacent_enemy','all_enemies','self','ally','all') NOT NULL DEFAULT 'adjacent_enemy',
  `contact` tinyint(1) NOT NULL DEFAULT '0',
  `crit_stage_bonus` tinyint NOT NULL DEFAULT '0',
  `effect_key` varchar(64) DEFAULT NULL,
  `effect_chance_percent` decimal(5,2) DEFAULT NULL,
  `min_hits` tinyint UNSIGNED DEFAULT '1',
  `max_hits` tinyint UNSIGNED DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `moves`
--

INSERT INTO `moves` (`move_id`, `move_key`, `move_name`, `element_id`, `category`, `power`, `accuracy_percent`, `pp`, `priority`, `target_mode`, `contact`, `crit_stage_bonus`, `effect_key`, `effect_chance_percent`, `min_hits`, `max_hits`, `created_at`) VALUES
(1, 'tackle', 'Tackle', 1, 'physical', 40, 100.00, 35, 0, 'adjacent_enemy', 1, 0, NULL, NULL, 1, 1, '2026-04-11 20:56:41'),
(2, 'quick_attack', 'Quick Attack', 1, 'physical', 40, 100.00, 30, 1, 'adjacent_enemy', 1, 0, NULL, NULL, 1, 1, '2026-04-11 20:56:41'),
(3, 'scratch', 'Scratch', 1, 'physical', 40, 100.00, 35, 0, 'adjacent_enemy', 1, 0, NULL, NULL, 1, 1, '2026-04-11 20:56:41'),
(4, 'ember', 'Ember', 2, 'special', 40, 100.00, 25, 0, 'adjacent_enemy', 0, 0, 'burn_10', 10.00, 1, 1, '2026-04-11 20:56:41'),
(5, 'flamethrower', 'Flamethrower', 2, 'special', 90, 100.00, 15, 0, 'adjacent_enemy', 0, 0, 'burn_10', 10.00, 1, 1, '2026-04-11 20:56:41'),
(6, 'water_gun', 'Water Gun', 3, 'special', 40, 100.00, 25, 0, 'adjacent_enemy', 0, 0, NULL, NULL, 1, 1, '2026-04-11 20:56:41'),
(7, 'bubble_beam', 'Bubble Beam', 3, 'special', 65, 100.00, 20, 0, 'adjacent_enemy', 0, 0, 'speed_down_10', 10.00, 1, 1, '2026-04-11 20:56:41'),
(8, 'thunder_shock', 'Thunder Shock', 4, 'special', 40, 100.00, 30, 0, 'adjacent_enemy', 0, 0, 'paralyze_10', 10.00, 1, 1, '2026-04-11 20:56:41'),
(9, 'thunderbolt', 'Thunderbolt', 4, 'special', 90, 100.00, 15, 0, 'adjacent_enemy', 0, 0, 'paralyze_10', 10.00, 1, 1, '2026-04-11 20:56:41'),
(10, 'vine_whip', 'Vine Whip', 5, 'physical', 45, 100.00, 25, 0, 'adjacent_enemy', 1, 0, NULL, NULL, 1, 1, '2026-04-11 20:56:41'),
(11, 'razor_leaf', 'Razor Leaf', 5, 'physical', 55, 95.00, 25, 0, 'all_enemies', 0, 1, NULL, NULL, 1, 1, '2026-04-11 20:56:41'),
(12, 'gust', 'Gust', 6, 'special', 40, 100.00, 35, 0, 'adjacent_enemy', 0, 0, NULL, NULL, 1, 1, '2026-04-11 20:56:41'),
(13, 'wing_attack', 'Wing Attack', 6, 'physical', 60, 100.00, 35, 0, 'adjacent_enemy', 1, 0, NULL, NULL, 1, 1, '2026-04-11 20:56:41'),
(14, 'confusion', 'Confusion', 9, 'special', 50, 100.00, 25, 0, 'adjacent_enemy', 0, 0, 'confuse_10', 10.00, 1, 1, '2026-04-11 20:56:41'),
(15, 'bite', 'Bite', 15, 'physical', 60, 100.00, 25, 0, 'adjacent_enemy', 1, 0, 'flinch_30', 30.00, 1, 1, '2026-04-11 20:56:41'),
(16, 'rock_throw', 'Rock Throw', 8, 'physical', 50, 90.00, 15, 0, 'adjacent_enemy', 0, 0, NULL, NULL, 1, 1, '2026-04-11 20:56:41'),
(17, 'ice_beam', 'Ice Beam', 13, 'special', 90, 100.00, 10, 0, 'adjacent_enemy', 0, 0, 'freeze_10', 10.00, 1, 1, '2026-04-11 20:56:41'),
(18, 'poison_sting', 'Poison Sting', 11, 'physical', 15, 100.00, 35, 0, 'adjacent_enemy', 1, 0, 'poison_30', 30.00, 1, 1, '2026-04-11 20:56:41'),
(19, 'karate_chop', 'Karate Chop', 12, 'physical', 50, 100.00, 25, 0, 'adjacent_enemy', 1, 1, NULL, NULL, 1, 1, '2026-04-11 20:56:41'),
(20, 'growl', 'Growl', 1, 'status', NULL, 100.00, 40, 0, 'all_enemies', 0, 0, 'atk_down_1', 100.00, 1, 1, '2026-04-11 20:56:41');

-- --------------------------------------------------------

--
-- Table structure for table `npc_trainers`
--

CREATE TABLE `npc_trainers` (
  `trainer_id` bigint UNSIGNED NOT NULL,
  `trainer_job` varchar(100) NOT NULL,
  `trainer_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `pet_colors`
--

CREATE TABLE `pet_colors` (
  `color_id` smallint UNSIGNED NOT NULL,
  `color_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pet_colors`
--

INSERT INTO `pet_colors` (`color_id`, `color_name`) VALUES
(13, '16-Bit'),
(12, '8-Bit'),
(58, 'Agent'),
(59, 'Baby'),
(14, 'Bikini'),
(6, 'Black'),
(60, 'Blackwhite'),
(2, 'Blue'),
(61, 'Bordeaux'),
(62, 'Brown'),
(63, 'Burlap'),
(64, 'Candy'),
(15, 'Checkered'),
(65, 'Cheese'),
(16, 'Chocolate'),
(17, 'Christmas'),
(66, 'Clay'),
(18, 'Cloud'),
(19, 'Cookie'),
(67, 'Coral'),
(68, 'Cowboy'),
(69, 'Creepy'),
(70, 'Cyan'),
(20, 'Desert'),
(21, 'Eww'),
(22, 'Fabric'),
(71, 'Fairy'),
(72, 'Fantasy'),
(23, 'Feral'),
(24, 'Festival'),
(25, 'Fire'),
(26, 'Forest Camouflage'),
(73, 'Funny'),
(74, 'Giraffe'),
(27, 'Gold'),
(75, 'Granite'),
(3, 'Green'),
(28, 'Grey'),
(29, 'Gummy'),
(30, 'Gyaru'),
(76, 'Hero'),
(77, 'Holiday'),
(31, 'Honey'),
(33, 'Ice'),
(32, 'Ice Cream'),
(9, 'Inverted'),
(34, 'Island'),
(78, 'Jello'),
(79, 'Leather'),
(35, 'Maid'),
(80, 'Marble'),
(81, 'MGE'),
(36, 'Milk'),
(82, 'Moss'),
(37, 'Muffin'),
(38, 'Neon'),
(83, 'Old'),
(39, 'Orange'),
(40, 'Origami'),
(41, 'Pencil'),
(42, 'Pink'),
(43, 'Pirate'),
(10, 'Plush'),
(8, 'Polygon'),
(5, 'Purple'),
(84, 'Python'),
(11, 'Quint'),
(85, 'Quintessential Quality'),
(44, 'Rainbow'),
(7, 'Realistic'),
(1, 'Red'),
(86, 'Regal'),
(45, 'Relic'),
(87, 'Scary'),
(46, 'Shadow'),
(47, 'Silver'),
(88, 'Skyblue'),
(48, 'Snow'),
(89, 'Spaghetti'),
(49, 'Split'),
(50, 'Sponge'),
(51, 'Strawberry'),
(52, 'Sunburnt'),
(53, 'Sunset'),
(54, 'Synthwave'),
(55, 'Thunder'),
(90, 'Toon'),
(91, 'Topless'),
(92, 'Toy'),
(93, 'Training'),
(94, 'Transparent'),
(95, 'Valentine'),
(96, 'Voxel'),
(97, 'Water'),
(56, 'White'),
(98, 'Wizard'),
(4, 'Yellow'),
(99, 'Zebra');

-- --------------------------------------------------------

--
-- Table structure for table `pet_cosmetics`
--

CREATE TABLE `pet_cosmetics` (
  `Id` int NOT NULL,
  `pet_instance_id` int NOT NULL,
  `item_id` int NOT NULL,
  `xcoord` float DEFAULT NULL,
  `ycoord` float DEFAULT NULL,
  `size` float DEFAULT NULL,
  `rotation` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `pet_cosmetics`
--

INSERT INTO `pet_cosmetics` (`Id`, `pet_instance_id`, `item_id`, `xcoord`, `ycoord`, `size`, `rotation`) VALUES
(9, 23, 122, 292, 237, 6, 0),
(10, 9, 4, 322, 0, 28, 0),
(11, 35, 4, 119, 122, 26, -25);

-- --------------------------------------------------------

--
-- Table structure for table `pet_equipment`
--

CREATE TABLE `pet_equipment` (
  `pet_instance_id` bigint UNSIGNED NOT NULL,
  `slot` varchar(32) NOT NULL,
  `item_instance_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pet_equipment`
--

INSERT INTO `pet_equipment` (`pet_instance_id`, `slot`, `item_instance_id`) VALUES
(1, 'weapon', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pet_instances`
--

CREATE TABLE `pet_instances` (
  `pet_instance_id` bigint UNSIGNED NOT NULL,
  `owner_user_id` bigint UNSIGNED NOT NULL,
  `species_id` smallint UNSIGNED NOT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `color_id` smallint UNSIGNED DEFAULT NULL,
  `level` int UNSIGNED NOT NULL DEFAULT '1',
  `experience` int UNSIGNED NOT NULL DEFAULT '0',
  `hp_current` int DEFAULT NULL,
  `hp_max` int DEFAULT NULL,
  `atk` int DEFAULT NULL,
  `def` int DEFAULT NULL,
  `initiative` int DEFAULT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  `gender` char(1) NOT NULL DEFAULT 'U',
  `hunger` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `happiness` tinyint UNSIGNED NOT NULL DEFAULT '50',
  `intelligence` int UNSIGNED NOT NULL DEFAULT '0',
  `sickness` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pet_instances`
--

INSERT INTO `pet_instances` (`pet_instance_id`, `owner_user_id`, `species_id`, `nickname`, `color_id`, `level`, `experience`, `hp_current`, `hp_max`, `atk`, `def`, `initiative`, `inactive`, `gender`, `hunger`, `happiness`, `intelligence`, `sickness`, `created_at`) VALUES
(1, 1, 112, 'Ember', 1, 5, 120, 40, NULL, 12, 7, 3, 0, 'U', 0, 50, 0, 0, '2025-09-02 20:37:52'),
(2, 2, 196, 'Splash', 2, 3, 40, 35, NULL, 8, 12, 8, 0, 'U', 0, 50, 0, 0, '2025-09-02 20:37:52'),
(3, 3, 195, 'Gale', 3, 7, 300, 42, NULL, 9, 15, 2, 0, 'U', 0, 50, 0, 0, '2025-09-02 20:37:52'),
(5, 11, 116, 'Wilhelmina', 3, 1, 0, 0, 50, 6, 7, 5, 0, 'F', 0, 46, 1, 1, '2025-09-10 10:01:47'),
(6, 11, 58, 'Klemmstein', 3, 1, 0, 0, 50, 8, 5, 7, 0, 'F', 0, 46, 4, 1, '2025-09-10 11:46:24'),
(7, 11, 59, 'PurpiNurpi', 5, 1, 0, 0, 50, 6, 7, 5, 0, 'f', 0, 46, 0, 1, '2025-09-10 12:57:36'),
(9, 5, 186, 'Gween Fwog uwu', 7, 12, 0, 154, 1200, 150, 680, 500, 0, 'f', 100, 19, 18, 0, '2025-11-15 22:34:57'),
(10, 12, 58, 'Lana', 5, 1, 0, 8, 8, 8, 5, 7, 0, 'f', 0, 50, 0, 0, '2025-11-15 23:08:59'),
(14, 12, 231, 'Egg', 4, 1, 0, 12, 12, 6, 7, 5, 0, 'f', 0, 50, 0, 0, '2025-11-16 11:44:47'),
(17, 14, 58, 'Redlet', 1, 1, 0, 0, 8, 8, 5, 7, 0, 'f', 0, 50, 1, 1, '2025-11-17 14:18:48'),
(18, 13, 240, 'Xuanwu', 2, 1, 0, 0, 20, 2, 7, 3, 0, 'f', 0, 60, 1, 1, '2025-11-20 12:54:17'),
(19, 5, 195, 'Homo Delfin', 2, 1, 0, NULL, NULL, NULL, NULL, NULL, 1, 'F', 0, 50, 0, 1, '2025-11-20 22:30:46'),
(20, 5, 116, 'WillWee', 7, 1, 0, 12, 12, 6, 7, 5, 0, 'f', 100, 39, 5, 1, '2025-11-28 09:12:58'),
(21, 5, 59, 'Centaurea', 8, 1, 0, 12, 12, 6, 7, 5, 0, 'f', 100, 19, 3, 1, '2025-11-28 09:33:31'),
(22, 5, 181, 'Charra', 9, 1, 0, 12, 12, 6, 7, 5, 0, 'f', 100, 29, 5, 1, '2025-11-28 10:35:26'),
(23, 5, 178, 'Yellow-Onna', 8, 1, 0, 12, 12, 6, 7, 5, 0, 'f', 100, 34, 4, 1, '2025-11-28 10:36:05'),
(24, 16, 248, 'RedDeath', 1, 1, 0, 10, 10, 18, 11, 12, 0, 'f', 0, 50, 0, 0, '2025-12-16 08:16:01'),
(25, 5, 248, 'Widdly-Widdly-Wee', 7, 1, 0, 10, 10, 18, 11, 12, 0, 'f', 0, 4, 6, 1, '2025-12-18 13:05:25'),
(26, 5, 241, 'Lülüth', 6, 1, 0, 12, 18, 14, 10, 18, 0, 'f', 0, 3, 8, 1, '2025-12-18 16:45:04'),
(27, 5, 251, 'Furple', 8, 1, 0, 8, 8, 19, 4, 12, 0, 'f', 0, 3, 8, 0, '2025-12-23 08:49:12'),
(28, 17, 183, 'Nummer 1', 1, 1, 0, 12, 12, 12, 11, 7, 0, 'f', 5, 70, 0, 0, '2025-12-23 14:20:22'),
(29, 17, 183, 'Nummer 2', 2, 1, 0, 12, 12, 12, 11, 7, 0, 'f', 0, 50, 0, 0, '2025-12-23 14:20:36'),
(30, 17, 183, 'Nummer 3', 3, 1, 0, 12, 12, 12, 11, 7, 0, 'f', 0, 50, 0, 0, '2025-12-23 14:20:46'),
(31, 17, 183, 'Nummer 4', 4, 1, 0, 12, 12, 12, 11, 7, 0, 'f', 0, 50, 0, 0, '2025-12-23 14:20:54'),
(32, 17, 183, 'Nummer 5', 5, 1, 0, 12, 12, 12, 11, 7, 0, 'f', 0, 50, 0, 0, '2025-12-23 14:21:03'),
(33, 17, 244, 'Adolf', 2, 1, 0, 10, 10, 12, 8, 15, 0, 'f', 0, 50, 0, 0, '2025-12-23 14:21:24'),
(34, 17, 183, 'Hitler', 5, 1, 0, 12, 12, 12, 11, 7, 0, 'f', 0, 50, 0, 0, '2025-12-23 14:21:41'),
(35, 13, 257, 'SchniSchnaSchnäpor', 5, 1, 0, 0, 6, 18, 18, 2, 0, 'f', 0, 60, 1, 0, '2026-01-13 12:44:54'),
(36, 13, 256, 'Slidi', 3, 1, 0, 0, 6, 5, 12, 15, 0, 'f', 0, 50, 1, 0, '2026-01-14 11:02:02'),
(37, 18, 196, 'Forever NF', 2, 1, 0, 13, 13, 14, 11, 9, 0, 'f', 0, 50, 0, 0, '2026-01-16 23:43:52');

-- --------------------------------------------------------

--
-- Table structure for table `pet_like_city`
--

CREATE TABLE `pet_like_city` (
  `PLCid` bigint UNSIGNED NOT NULL,
  `pet_id` smallint UNSIGNED NOT NULL,
  `country_id` smallint UNSIGNED NOT NULL,
  `like` tinyint NOT NULL DEFAULT '3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `pet_like_city`
--

INSERT INTO `pet_like_city` (`PLCid`, `pet_id`, `country_id`, `like`) VALUES
(1, 183, 18, 3),
(2, 183, 7, 3),
(3, 183, 8, 3),
(4, 183, 22, 3),
(5, 183, 11, 3),
(6, 183, 25, 3),
(7, 183, 16, 3),
(8, 183, 20, 3),
(9, 183, 4, 3),
(10, 183, 3, 3),
(11, 183, 17, 3),
(12, 183, 2, 3),
(13, 183, 5, 3),
(14, 183, 15, 3),
(15, 183, 10, 3),
(16, 183, 13, 3),
(17, 183, 19, 3),
(18, 183, 14, 3),
(19, 183, 9, 3),
(20, 183, 12, 3),
(21, 183, 26, 3),
(22, 183, 6, 3),
(23, 183, 1, 3),
(24, 183, 21, 3),
(25, 228, 18, 3),
(26, 228, 7, 3),
(27, 228, 8, 3),
(28, 228, 22, 3),
(29, 228, 11, 3),
(30, 228, 25, 3),
(31, 228, 16, 3),
(32, 228, 20, 3),
(33, 228, 4, 3),
(34, 228, 3, 3),
(35, 228, 17, 3),
(36, 228, 2, 3),
(37, 228, 5, 3),
(38, 228, 15, 3),
(39, 228, 10, 3),
(40, 228, 13, 3),
(41, 228, 19, 3),
(42, 228, 14, 3),
(43, 228, 9, 3),
(44, 228, 12, 3),
(45, 228, 26, 3),
(46, 228, 6, 3),
(47, 228, 1, 3),
(48, 228, 21, 3),
(49, 238, 18, 3),
(50, 238, 7, 3),
(51, 238, 8, 3),
(52, 238, 22, 3),
(53, 238, 11, 3),
(54, 238, 25, 3),
(55, 238, 16, 3),
(56, 238, 20, 3),
(57, 238, 4, 3),
(58, 238, 3, 3),
(59, 238, 17, 3),
(60, 238, 2, 3),
(61, 238, 5, 3),
(62, 238, 15, 3),
(63, 238, 10, 3),
(64, 238, 13, 3),
(65, 238, 19, 3),
(66, 238, 14, 3),
(67, 238, 9, 3),
(68, 238, 12, 3),
(69, 238, 26, 3),
(70, 238, 6, 3),
(71, 238, 1, 3),
(72, 238, 21, 3),
(73, 118, 18, 3),
(74, 118, 7, 3),
(75, 118, 8, 3),
(76, 118, 22, 3),
(77, 118, 11, 3),
(78, 118, 25, 3),
(79, 118, 16, 3),
(80, 118, 20, 3),
(81, 118, 4, 3),
(82, 118, 3, 3),
(83, 118, 17, 3),
(84, 118, 2, 3),
(85, 118, 5, 3),
(86, 118, 15, 3),
(87, 118, 10, 3),
(88, 118, 13, 3),
(89, 118, 19, 3),
(90, 118, 14, 3),
(91, 118, 9, 3),
(92, 118, 12, 3),
(93, 118, 26, 3),
(94, 118, 6, 3),
(95, 118, 1, 3),
(96, 118, 21, 3),
(97, 196, 18, 3),
(98, 196, 7, 3),
(99, 196, 8, 3),
(100, 196, 22, 3),
(101, 196, 11, 3),
(102, 196, 25, 3),
(103, 196, 16, 3),
(104, 196, 20, 3),
(105, 196, 4, 3),
(106, 196, 3, 3),
(107, 196, 17, 3),
(108, 196, 2, 3),
(109, 196, 5, 3),
(110, 196, 15, 3),
(111, 196, 10, 3),
(112, 196, 13, 3),
(113, 196, 19, 3),
(114, 196, 14, 3),
(115, 196, 9, 3),
(116, 196, 12, 3),
(117, 196, 26, 3),
(118, 196, 6, 3),
(119, 196, 1, 3),
(120, 196, 21, 3),
(121, 237, 18, 3),
(122, 237, 7, 3),
(123, 237, 8, 3),
(124, 237, 22, 3),
(125, 237, 11, 3),
(126, 237, 25, 3),
(127, 237, 16, 3),
(128, 237, 20, 3),
(129, 237, 4, 3),
(130, 237, 3, 3),
(131, 237, 17, 3),
(132, 237, 2, 3),
(133, 237, 5, 3),
(134, 237, 15, 3),
(135, 237, 10, 3),
(136, 237, 13, 3),
(137, 237, 19, 3),
(138, 237, 14, 3),
(139, 237, 9, 3),
(140, 237, 12, 3),
(141, 237, 26, 3),
(142, 237, 6, 3),
(143, 237, 1, 3),
(144, 237, 21, 3),
(145, 186, 18, 3),
(146, 186, 7, 3),
(147, 186, 8, 3),
(148, 186, 22, 3),
(149, 186, 11, 3),
(150, 186, 25, 3),
(151, 186, 16, 3),
(152, 186, 20, 3),
(153, 186, 4, 3),
(154, 186, 3, 3),
(155, 186, 17, 3),
(156, 186, 2, 3),
(157, 186, 5, 3),
(158, 186, 15, 3),
(159, 186, 10, 3),
(160, 186, 13, 3),
(161, 186, 19, 3),
(162, 186, 14, 3),
(163, 186, 9, 3),
(164, 186, 12, 3),
(165, 186, 26, 3),
(166, 186, 6, 3),
(167, 186, 1, 3),
(168, 186, 21, 3),
(169, 191, 18, 3),
(170, 191, 7, 3),
(171, 191, 8, 3),
(172, 191, 22, 3),
(173, 191, 11, 3),
(174, 191, 25, 3),
(175, 191, 16, 3),
(176, 191, 20, 3),
(177, 191, 4, 3),
(178, 191, 3, 3),
(179, 191, 17, 3),
(180, 191, 2, 3),
(181, 191, 5, 3),
(182, 191, 15, 3),
(183, 191, 10, 3),
(184, 191, 13, 3),
(185, 191, 19, 3),
(186, 191, 14, 3),
(187, 191, 9, 3),
(188, 191, 12, 3),
(189, 191, 26, 3),
(190, 191, 6, 3),
(191, 191, 1, 3),
(192, 191, 21, 3),
(193, 114, 18, 3),
(194, 114, 7, 3),
(195, 114, 8, 3),
(196, 114, 22, 3),
(197, 114, 11, 3),
(198, 114, 25, 3),
(199, 114, 16, 3),
(200, 114, 20, 3),
(201, 114, 4, 3),
(202, 114, 3, 3),
(203, 114, 17, 3),
(204, 114, 2, 3),
(205, 114, 5, 3),
(206, 114, 15, 3),
(207, 114, 10, 3),
(208, 114, 13, 3),
(209, 114, 19, 3),
(210, 114, 14, 3),
(211, 114, 9, 3),
(212, 114, 12, 3),
(213, 114, 26, 3),
(214, 114, 6, 3),
(215, 114, 1, 3),
(216, 114, 21, 3),
(217, 240, 18, 3),
(218, 240, 7, 3),
(219, 240, 8, 3),
(220, 240, 22, 3),
(221, 240, 11, 3),
(222, 240, 25, 3),
(223, 240, 16, 3),
(224, 240, 20, 3),
(225, 240, 4, 3),
(226, 240, 3, 3),
(227, 240, 17, 3),
(228, 240, 2, 3),
(229, 240, 5, 3),
(230, 240, 15, 3),
(231, 240, 10, 3),
(232, 240, 13, 3),
(233, 240, 19, 3),
(234, 240, 14, 3),
(235, 240, 9, 3),
(236, 240, 12, 3),
(237, 240, 26, 3),
(238, 240, 6, 3),
(239, 240, 1, 3),
(240, 240, 21, 3),
(241, 232, 18, 3),
(242, 232, 7, 3),
(243, 232, 8, 3),
(244, 232, 22, 3),
(245, 232, 11, 3),
(246, 232, 25, 3),
(247, 232, 16, 3),
(248, 232, 20, 3),
(249, 232, 4, 3),
(250, 232, 3, 3),
(251, 232, 17, 3),
(252, 232, 2, 3),
(253, 232, 5, 3),
(254, 232, 15, 3),
(255, 232, 10, 3),
(256, 232, 13, 3),
(257, 232, 19, 3),
(258, 232, 14, 3),
(259, 232, 9, 3),
(260, 232, 12, 3),
(261, 232, 26, 3),
(262, 232, 6, 3),
(263, 232, 1, 3),
(264, 232, 21, 3),
(265, 235, 18, 3),
(266, 235, 7, 3),
(267, 235, 8, 3),
(268, 235, 22, 3),
(269, 235, 11, 3),
(270, 235, 25, 3),
(271, 235, 16, 3),
(272, 235, 20, 3),
(273, 235, 4, 3),
(274, 235, 3, 3),
(275, 235, 17, 3),
(276, 235, 2, 3),
(277, 235, 5, 3),
(278, 235, 15, 3),
(279, 235, 10, 3),
(280, 235, 13, 3),
(281, 235, 19, 3),
(282, 235, 14, 3),
(283, 235, 9, 3),
(284, 235, 12, 3),
(285, 235, 26, 3),
(286, 235, 6, 3),
(287, 235, 1, 3),
(288, 235, 21, 3),
(289, 59, 18, 3),
(290, 59, 7, 3),
(291, 59, 8, 3),
(292, 59, 22, 3),
(293, 59, 11, 3),
(294, 59, 25, 3),
(295, 59, 16, 3),
(296, 59, 20, 3),
(297, 59, 4, 3),
(298, 59, 3, 3),
(299, 59, 17, 3),
(300, 59, 2, 3),
(301, 59, 5, 3),
(302, 59, 15, 3),
(303, 59, 10, 3),
(304, 59, 13, 3),
(305, 59, 19, 3),
(306, 59, 14, 3),
(307, 59, 9, 3),
(308, 59, 12, 3),
(309, 59, 26, 3),
(310, 59, 6, 3),
(311, 59, 1, 3),
(312, 59, 21, 3),
(313, 181, 18, 3),
(314, 181, 7, 3),
(315, 181, 8, 3),
(316, 181, 22, 3),
(317, 181, 11, 3),
(318, 181, 25, 3),
(319, 181, 16, 3),
(320, 181, 20, 3),
(321, 181, 4, 3),
(322, 181, 3, 3),
(323, 181, 17, 3),
(324, 181, 2, 3),
(325, 181, 5, 3),
(326, 181, 15, 3),
(327, 181, 10, 3),
(328, 181, 13, 3),
(329, 181, 19, 3),
(330, 181, 14, 3),
(331, 181, 9, 3),
(332, 181, 12, 3),
(333, 181, 26, 3),
(334, 181, 6, 3),
(335, 181, 1, 3),
(336, 181, 21, 3),
(337, 180, 18, 3),
(338, 180, 7, 3),
(339, 180, 8, 3),
(340, 180, 22, 3),
(341, 180, 11, 3),
(342, 180, 25, 3),
(343, 180, 16, 3),
(344, 180, 20, 3),
(345, 180, 4, 3),
(346, 180, 3, 3),
(347, 180, 17, 3),
(348, 180, 2, 3),
(349, 180, 5, 3),
(350, 180, 15, 3),
(351, 180, 10, 3),
(352, 180, 13, 3),
(353, 180, 19, 3),
(354, 180, 14, 3),
(355, 180, 9, 3),
(356, 180, 12, 3),
(357, 180, 26, 3),
(358, 180, 6, 3),
(359, 180, 1, 3),
(360, 180, 21, 3),
(361, 184, 18, 3),
(362, 184, 7, 3),
(363, 184, 8, 3),
(364, 184, 22, 3),
(365, 184, 11, 3),
(366, 184, 25, 3),
(367, 184, 16, 3),
(368, 184, 20, 3),
(369, 184, 4, 3),
(370, 184, 3, 3),
(371, 184, 17, 3),
(372, 184, 2, 3),
(373, 184, 5, 3),
(374, 184, 15, 3),
(375, 184, 10, 3),
(376, 184, 13, 3),
(377, 184, 19, 3),
(378, 184, 14, 3),
(379, 184, 9, 3),
(380, 184, 12, 3),
(381, 184, 26, 3),
(382, 184, 6, 3),
(383, 184, 1, 3),
(384, 184, 21, 3),
(385, 188, 18, 3),
(386, 188, 7, 3),
(387, 188, 8, 3),
(388, 188, 22, 3),
(389, 188, 11, 3),
(390, 188, 25, 3),
(391, 188, 16, 3),
(392, 188, 20, 3),
(393, 188, 4, 3),
(394, 188, 3, 3),
(395, 188, 17, 3),
(396, 188, 2, 3),
(397, 188, 5, 3),
(398, 188, 15, 3),
(399, 188, 10, 3),
(400, 188, 13, 3),
(401, 188, 19, 3),
(402, 188, 14, 3),
(403, 188, 9, 3),
(404, 188, 12, 3),
(405, 188, 26, 3),
(406, 188, 6, 3),
(407, 188, 1, 3),
(408, 188, 21, 3),
(409, 234, 18, 3),
(410, 234, 7, 3),
(411, 234, 8, 3),
(412, 234, 22, 3),
(413, 234, 11, 3),
(414, 234, 25, 3),
(415, 234, 16, 3),
(416, 234, 20, 3),
(417, 234, 4, 3),
(418, 234, 3, 3),
(419, 234, 17, 3),
(420, 234, 2, 3),
(421, 234, 5, 3),
(422, 234, 15, 3),
(423, 234, 10, 3),
(424, 234, 13, 3),
(425, 234, 19, 3),
(426, 234, 14, 3),
(427, 234, 9, 3),
(428, 234, 12, 3),
(429, 234, 26, 3),
(430, 234, 6, 3),
(431, 234, 1, 3),
(432, 234, 21, 3),
(433, 164, 18, 3),
(434, 164, 7, 3),
(435, 164, 8, 3),
(436, 164, 22, 3),
(437, 164, 11, 3),
(438, 164, 25, 3),
(439, 164, 16, 3),
(440, 164, 20, 3),
(441, 164, 4, 3),
(442, 164, 3, 3),
(443, 164, 17, 3),
(444, 164, 2, 3),
(445, 164, 5, 3),
(446, 164, 15, 3),
(447, 164, 10, 3),
(448, 164, 13, 3),
(449, 164, 19, 3),
(450, 164, 14, 3),
(451, 164, 9, 3),
(452, 164, 12, 3),
(453, 164, 26, 3),
(454, 164, 6, 3),
(455, 164, 1, 3),
(456, 164, 21, 3),
(457, 195, 18, 3),
(458, 195, 7, 3),
(459, 195, 8, 3),
(460, 195, 22, 3),
(461, 195, 11, 3),
(462, 195, 25, 3),
(463, 195, 16, 3),
(464, 195, 20, 3),
(465, 195, 4, 3),
(466, 195, 3, 3),
(467, 195, 17, 3),
(468, 195, 2, 3),
(469, 195, 5, 3),
(470, 195, 15, 3),
(471, 195, 10, 3),
(472, 195, 13, 3),
(473, 195, 19, 3),
(474, 195, 14, 3),
(475, 195, 9, 3),
(476, 195, 12, 3),
(477, 195, 26, 3),
(478, 195, 6, 3),
(479, 195, 1, 3),
(480, 195, 21, 3),
(481, 230, 18, 3),
(482, 230, 7, 3),
(483, 230, 8, 3),
(484, 230, 22, 3),
(485, 230, 11, 3),
(486, 230, 25, 3),
(487, 230, 16, 3),
(488, 230, 20, 3),
(489, 230, 4, 3),
(490, 230, 3, 3),
(491, 230, 17, 3),
(492, 230, 2, 3),
(493, 230, 5, 3),
(494, 230, 15, 3),
(495, 230, 10, 3),
(496, 230, 13, 3),
(497, 230, 19, 3),
(498, 230, 14, 3),
(499, 230, 9, 3),
(500, 230, 12, 3),
(501, 230, 26, 3),
(502, 230, 6, 3),
(503, 230, 1, 3),
(504, 230, 21, 3),
(505, 115, 18, 3),
(506, 115, 7, 3),
(507, 115, 8, 3),
(508, 115, 22, 3),
(509, 115, 11, 3),
(510, 115, 25, 3),
(511, 115, 16, 3),
(512, 115, 20, 3),
(513, 115, 4, 3),
(514, 115, 3, 3),
(515, 115, 17, 3),
(516, 115, 2, 3),
(517, 115, 5, 3),
(518, 115, 15, 3),
(519, 115, 10, 3),
(520, 115, 13, 3),
(521, 115, 19, 3),
(522, 115, 14, 3),
(523, 115, 9, 3),
(524, 115, 12, 3),
(525, 115, 26, 3),
(526, 115, 6, 3),
(527, 115, 1, 3),
(528, 115, 21, 3),
(529, 236, 18, 3),
(530, 236, 7, 3),
(531, 236, 8, 3),
(532, 236, 22, 3),
(533, 236, 11, 3),
(534, 236, 25, 3),
(535, 236, 16, 3),
(536, 236, 20, 3),
(537, 236, 4, 3),
(538, 236, 3, 3),
(539, 236, 17, 3),
(540, 236, 2, 3),
(541, 236, 5, 3),
(542, 236, 15, 3),
(543, 236, 10, 3),
(544, 236, 13, 3),
(545, 236, 19, 3),
(546, 236, 14, 3),
(547, 236, 9, 3),
(548, 236, 12, 3),
(549, 236, 26, 3),
(550, 236, 6, 3),
(551, 236, 1, 3),
(552, 236, 21, 3),
(553, 174, 18, 3),
(554, 174, 7, 3),
(555, 174, 8, 3),
(556, 174, 22, 3),
(557, 174, 11, 3),
(558, 174, 25, 3),
(559, 174, 16, 3),
(560, 174, 20, 3),
(561, 174, 4, 3),
(562, 174, 3, 3),
(563, 174, 17, 3),
(564, 174, 2, 3),
(565, 174, 5, 3),
(566, 174, 15, 3),
(567, 174, 10, 3),
(568, 174, 13, 3),
(569, 174, 19, 3),
(570, 174, 14, 3),
(571, 174, 9, 3),
(572, 174, 12, 3),
(573, 174, 26, 3),
(574, 174, 6, 3),
(575, 174, 1, 3),
(576, 174, 21, 3),
(577, 190, 18, 3),
(578, 190, 7, 3),
(579, 190, 8, 3),
(580, 190, 22, 3),
(581, 190, 11, 3),
(582, 190, 25, 3),
(583, 190, 16, 3),
(584, 190, 20, 3),
(585, 190, 4, 3),
(586, 190, 3, 3),
(587, 190, 17, 3),
(588, 190, 2, 3),
(589, 190, 5, 3),
(590, 190, 15, 3),
(591, 190, 10, 3),
(592, 190, 13, 3),
(593, 190, 19, 3),
(594, 190, 14, 3),
(595, 190, 9, 3),
(596, 190, 12, 3),
(597, 190, 26, 3),
(598, 190, 6, 3),
(599, 190, 1, 3),
(600, 190, 21, 3),
(601, 192, 18, 3),
(602, 192, 7, 3),
(603, 192, 8, 3),
(604, 192, 22, 3),
(605, 192, 11, 3),
(606, 192, 25, 3),
(607, 192, 16, 3),
(608, 192, 20, 3),
(609, 192, 4, 3),
(610, 192, 3, 3),
(611, 192, 17, 3),
(612, 192, 2, 3),
(613, 192, 5, 3),
(614, 192, 15, 3),
(615, 192, 10, 3),
(616, 192, 13, 3),
(617, 192, 19, 3),
(618, 192, 14, 3),
(619, 192, 9, 3),
(620, 192, 12, 3),
(621, 192, 26, 3),
(622, 192, 6, 3),
(623, 192, 1, 3),
(624, 192, 21, 3),
(625, 194, 18, 3),
(626, 194, 7, 3),
(627, 194, 8, 3),
(628, 194, 22, 3),
(629, 194, 11, 3),
(630, 194, 25, 3),
(631, 194, 16, 3),
(632, 194, 20, 3),
(633, 194, 4, 3),
(634, 194, 3, 3),
(635, 194, 17, 3),
(636, 194, 2, 3),
(637, 194, 5, 3),
(638, 194, 15, 3),
(639, 194, 10, 3),
(640, 194, 13, 3),
(641, 194, 19, 3),
(642, 194, 14, 3),
(643, 194, 9, 3),
(644, 194, 12, 3),
(645, 194, 26, 3),
(646, 194, 6, 3),
(647, 194, 1, 3),
(648, 194, 21, 3),
(649, 171, 18, 3),
(650, 171, 7, 3),
(651, 171, 8, 3),
(652, 171, 22, 3),
(653, 171, 11, 3),
(654, 171, 25, 3),
(655, 171, 16, 3),
(656, 171, 20, 3),
(657, 171, 4, 3),
(658, 171, 3, 3),
(659, 171, 17, 3),
(660, 171, 2, 3),
(661, 171, 5, 3),
(662, 171, 15, 3),
(663, 171, 10, 3),
(664, 171, 13, 3),
(665, 171, 19, 3),
(666, 171, 14, 3),
(667, 171, 9, 3),
(668, 171, 12, 3),
(669, 171, 26, 3),
(670, 171, 6, 3),
(671, 171, 1, 3),
(672, 171, 21, 3),
(673, 169, 18, 3),
(674, 169, 7, 3),
(675, 169, 8, 3),
(676, 169, 22, 3),
(677, 169, 11, 3),
(678, 169, 25, 3),
(679, 169, 16, 3),
(680, 169, 20, 3),
(681, 169, 4, 3),
(682, 169, 3, 3),
(683, 169, 17, 3),
(684, 169, 2, 3),
(685, 169, 5, 3),
(686, 169, 15, 3),
(687, 169, 10, 3),
(688, 169, 13, 3),
(689, 169, 19, 3),
(690, 169, 14, 3),
(691, 169, 9, 3),
(692, 169, 12, 3),
(693, 169, 26, 3),
(694, 169, 6, 3),
(695, 169, 1, 3),
(696, 169, 21, 3),
(697, 172, 18, 3),
(698, 172, 7, 3),
(699, 172, 8, 3),
(700, 172, 22, 3),
(701, 172, 11, 3),
(702, 172, 25, 3),
(703, 172, 16, 3),
(704, 172, 20, 3),
(705, 172, 4, 3),
(706, 172, 3, 3),
(707, 172, 17, 3),
(708, 172, 2, 3),
(709, 172, 5, 3),
(710, 172, 15, 3),
(711, 172, 10, 3),
(712, 172, 13, 3),
(713, 172, 19, 3),
(714, 172, 14, 3),
(715, 172, 9, 3),
(716, 172, 12, 3),
(717, 172, 26, 3),
(718, 172, 6, 3),
(719, 172, 1, 3),
(720, 172, 21, 3),
(721, 243, 18, 3),
(722, 243, 7, 3),
(723, 243, 8, 3),
(724, 243, 22, 3),
(725, 243, 11, 3),
(726, 243, 25, 3),
(727, 243, 16, 3),
(728, 243, 20, 3),
(729, 243, 4, 3),
(730, 243, 3, 3),
(731, 243, 17, 3),
(732, 243, 2, 3),
(733, 243, 5, 3),
(734, 243, 15, 3),
(735, 243, 10, 3),
(736, 243, 13, 3),
(737, 243, 19, 3),
(738, 243, 14, 3),
(739, 243, 9, 3),
(740, 243, 12, 3),
(741, 243, 26, 3),
(742, 243, 6, 3),
(743, 243, 1, 3),
(744, 243, 21, 3),
(745, 242, 18, 3),
(746, 242, 7, 3),
(747, 242, 8, 3),
(748, 242, 22, 3),
(749, 242, 11, 3),
(750, 242, 25, 3),
(751, 242, 16, 3),
(752, 242, 20, 3),
(753, 242, 4, 3),
(754, 242, 3, 3),
(755, 242, 17, 3),
(756, 242, 2, 3),
(757, 242, 5, 3),
(758, 242, 15, 3),
(759, 242, 10, 3),
(760, 242, 13, 3),
(761, 242, 19, 3),
(762, 242, 14, 3),
(763, 242, 9, 3),
(764, 242, 12, 3),
(765, 242, 26, 3),
(766, 242, 6, 3),
(767, 242, 1, 3),
(768, 242, 21, 3),
(769, 244, 18, 3),
(770, 244, 7, 3),
(771, 244, 8, 3),
(772, 244, 22, 3),
(773, 244, 11, 3),
(774, 244, 25, 3),
(775, 244, 16, 3),
(776, 244, 20, 3),
(777, 244, 4, 3),
(778, 244, 3, 3),
(779, 244, 17, 3),
(780, 244, 2, 3),
(781, 244, 5, 3),
(782, 244, 15, 3),
(783, 244, 10, 3),
(784, 244, 13, 3),
(785, 244, 19, 3),
(786, 244, 14, 3),
(787, 244, 9, 3),
(788, 244, 12, 3),
(789, 244, 26, 3),
(790, 244, 6, 3),
(791, 244, 1, 3),
(792, 244, 21, 3),
(793, 117, 18, 3),
(794, 117, 7, 3),
(795, 117, 8, 3),
(796, 117, 22, 3),
(797, 117, 11, 3),
(798, 117, 25, 3),
(799, 117, 16, 3),
(800, 117, 20, 3),
(801, 117, 4, 3),
(802, 117, 3, 3),
(803, 117, 17, 3),
(804, 117, 2, 3),
(805, 117, 5, 3),
(806, 117, 15, 3),
(807, 117, 10, 3),
(808, 117, 13, 3),
(809, 117, 19, 3),
(810, 117, 14, 3),
(811, 117, 9, 3),
(812, 117, 12, 3),
(813, 117, 26, 3),
(814, 117, 6, 3),
(815, 117, 1, 3),
(816, 117, 21, 3),
(817, 177, 18, 3),
(818, 177, 7, 3),
(819, 177, 8, 3),
(820, 177, 22, 3),
(821, 177, 11, 3),
(822, 177, 25, 3),
(823, 177, 16, 3),
(824, 177, 20, 3),
(825, 177, 4, 3),
(826, 177, 3, 3),
(827, 177, 17, 3),
(828, 177, 2, 3),
(829, 177, 5, 3),
(830, 177, 15, 3),
(831, 177, 10, 3),
(832, 177, 13, 3),
(833, 177, 19, 3),
(834, 177, 14, 3),
(835, 177, 9, 3),
(836, 177, 12, 3),
(837, 177, 26, 3),
(838, 177, 6, 3),
(839, 177, 1, 3),
(840, 177, 21, 3),
(841, 112, 18, 3),
(842, 112, 7, 3),
(843, 112, 8, 3),
(844, 112, 22, 3),
(845, 112, 11, 3),
(846, 112, 25, 3),
(847, 112, 16, 3),
(848, 112, 20, 3),
(849, 112, 4, 3),
(850, 112, 3, 3),
(851, 112, 17, 3),
(852, 112, 2, 3),
(853, 112, 5, 3),
(854, 112, 15, 3),
(855, 112, 10, 3),
(856, 112, 13, 3),
(857, 112, 19, 3),
(858, 112, 14, 3),
(859, 112, 9, 3),
(860, 112, 12, 3),
(861, 112, 26, 3),
(862, 112, 6, 3),
(863, 112, 1, 3),
(864, 112, 21, 3),
(865, 179, 18, 3),
(866, 179, 7, 3),
(867, 179, 8, 3),
(868, 179, 22, 3),
(869, 179, 11, 3),
(870, 179, 25, 3),
(871, 179, 16, 3),
(872, 179, 20, 3),
(873, 179, 4, 3),
(874, 179, 3, 3),
(875, 179, 17, 3),
(876, 179, 2, 3),
(877, 179, 5, 3),
(878, 179, 15, 3),
(879, 179, 10, 3),
(880, 179, 13, 3),
(881, 179, 19, 3),
(882, 179, 14, 3),
(883, 179, 9, 3),
(884, 179, 12, 3),
(885, 179, 26, 3),
(886, 179, 6, 3),
(887, 179, 1, 3),
(888, 179, 21, 3),
(889, 193, 18, 3),
(890, 193, 7, 3),
(891, 193, 8, 3),
(892, 193, 22, 3),
(893, 193, 11, 3),
(894, 193, 25, 3),
(895, 193, 16, 3),
(896, 193, 20, 3),
(897, 193, 4, 3),
(898, 193, 3, 3),
(899, 193, 17, 3),
(900, 193, 2, 3),
(901, 193, 5, 3),
(902, 193, 15, 3),
(903, 193, 10, 3),
(904, 193, 13, 3),
(905, 193, 19, 3),
(906, 193, 14, 3),
(907, 193, 9, 3),
(908, 193, 12, 3),
(909, 193, 26, 3),
(910, 193, 6, 3),
(911, 193, 1, 3),
(912, 193, 21, 3),
(913, 58, 18, 3),
(914, 58, 7, 3),
(915, 58, 8, 3),
(916, 58, 22, 3),
(917, 58, 11, 3),
(918, 58, 25, 3),
(919, 58, 16, 3),
(920, 58, 20, 3),
(921, 58, 4, 3),
(922, 58, 3, 3),
(923, 58, 17, 3),
(924, 58, 2, 3),
(925, 58, 5, 3),
(926, 58, 15, 3),
(927, 58, 10, 3),
(928, 58, 13, 3),
(929, 58, 19, 3),
(930, 58, 14, 3),
(931, 58, 9, 3),
(932, 58, 12, 3),
(933, 58, 26, 3),
(934, 58, 6, 3),
(935, 58, 1, 3),
(936, 58, 21, 3),
(937, 166, 18, 3),
(938, 166, 7, 3),
(939, 166, 8, 3),
(940, 166, 22, 3),
(941, 166, 11, 3),
(942, 166, 25, 3),
(943, 166, 16, 3),
(944, 166, 20, 3),
(945, 166, 4, 3),
(946, 166, 3, 3),
(947, 166, 17, 3),
(948, 166, 2, 3),
(949, 166, 5, 3),
(950, 166, 15, 3),
(951, 166, 10, 3),
(952, 166, 13, 3),
(953, 166, 19, 3),
(954, 166, 14, 3),
(955, 166, 9, 3),
(956, 166, 12, 3),
(957, 166, 26, 3),
(958, 166, 6, 3),
(959, 166, 1, 3),
(960, 166, 21, 3),
(961, 168, 18, 3),
(962, 168, 7, 3),
(963, 168, 8, 3),
(964, 168, 22, 3),
(965, 168, 11, 3),
(966, 168, 25, 3),
(967, 168, 16, 3),
(968, 168, 20, 3),
(969, 168, 4, 3),
(970, 168, 3, 3),
(971, 168, 17, 3),
(972, 168, 2, 3),
(973, 168, 5, 3),
(974, 168, 15, 3),
(975, 168, 10, 3),
(976, 168, 13, 3),
(977, 168, 19, 3),
(978, 168, 14, 3),
(979, 168, 9, 3),
(980, 168, 12, 3),
(981, 168, 26, 3),
(982, 168, 6, 3),
(983, 168, 1, 3),
(984, 168, 21, 3),
(985, 241, 18, 3),
(986, 241, 7, 3),
(987, 241, 8, 3),
(988, 241, 22, 3),
(989, 241, 11, 3),
(990, 241, 25, 3),
(991, 241, 16, 3),
(992, 241, 20, 3),
(993, 241, 4, 3),
(994, 241, 3, 3),
(995, 241, 17, 3),
(996, 241, 2, 3),
(997, 241, 5, 3),
(998, 241, 15, 3),
(999, 241, 10, 3),
(1000, 241, 13, 3),
(1001, 241, 19, 3),
(1002, 241, 14, 3),
(1003, 241, 9, 3),
(1004, 241, 12, 3),
(1005, 241, 26, 3),
(1006, 241, 6, 3),
(1007, 241, 1, 3),
(1008, 241, 21, 3),
(1009, 245, 18, 3),
(1010, 245, 7, 3),
(1011, 245, 8, 3),
(1012, 245, 22, 3),
(1013, 245, 11, 3),
(1014, 245, 25, 3),
(1015, 245, 16, 3),
(1016, 245, 20, 3),
(1017, 245, 4, 3),
(1018, 245, 3, 3),
(1019, 245, 17, 3),
(1020, 245, 2, 3),
(1021, 245, 5, 3),
(1022, 245, 15, 3),
(1023, 245, 10, 3),
(1024, 245, 13, 3),
(1025, 245, 19, 3),
(1026, 245, 14, 3),
(1027, 245, 9, 3),
(1028, 245, 12, 3),
(1029, 245, 26, 3),
(1030, 245, 6, 3),
(1031, 245, 1, 3),
(1032, 245, 21, 3),
(1033, 231, 18, 3),
(1034, 231, 7, 3),
(1035, 231, 8, 3),
(1036, 231, 22, 3),
(1037, 231, 11, 3),
(1038, 231, 25, 3),
(1039, 231, 16, 3),
(1040, 231, 20, 3),
(1041, 231, 4, 3),
(1042, 231, 3, 3),
(1043, 231, 17, 3),
(1044, 231, 2, 3),
(1045, 231, 5, 3),
(1046, 231, 15, 3),
(1047, 231, 10, 3),
(1048, 231, 13, 3),
(1049, 231, 19, 3),
(1050, 231, 14, 3),
(1051, 231, 9, 3),
(1052, 231, 12, 3),
(1053, 231, 26, 3),
(1054, 231, 6, 3),
(1055, 231, 1, 3),
(1056, 231, 21, 3),
(1057, 175, 18, 3),
(1058, 175, 7, 3),
(1059, 175, 8, 3),
(1060, 175, 22, 3),
(1061, 175, 11, 3),
(1062, 175, 25, 3),
(1063, 175, 16, 3),
(1064, 175, 20, 3),
(1065, 175, 4, 3),
(1066, 175, 3, 3),
(1067, 175, 17, 3),
(1068, 175, 2, 3),
(1069, 175, 5, 3),
(1070, 175, 15, 3),
(1071, 175, 10, 3),
(1072, 175, 13, 3),
(1073, 175, 19, 3),
(1074, 175, 14, 3),
(1075, 175, 9, 3),
(1076, 175, 12, 3),
(1077, 175, 26, 3),
(1078, 175, 6, 3),
(1079, 175, 1, 3),
(1080, 175, 21, 3),
(1081, 185, 18, 3),
(1082, 185, 7, 3),
(1083, 185, 8, 3),
(1084, 185, 22, 3),
(1085, 185, 11, 3),
(1086, 185, 25, 3),
(1087, 185, 16, 3),
(1088, 185, 20, 3),
(1089, 185, 4, 3),
(1090, 185, 3, 3),
(1091, 185, 17, 3),
(1092, 185, 2, 3),
(1093, 185, 5, 3),
(1094, 185, 15, 3),
(1095, 185, 10, 3),
(1096, 185, 13, 3),
(1097, 185, 19, 3),
(1098, 185, 14, 3),
(1099, 185, 9, 3),
(1100, 185, 12, 3),
(1101, 185, 26, 3),
(1102, 185, 6, 3),
(1103, 185, 1, 3),
(1104, 185, 21, 3),
(1105, 229, 18, 3),
(1106, 229, 7, 3),
(1107, 229, 8, 3),
(1108, 229, 22, 3),
(1109, 229, 11, 3),
(1110, 229, 25, 3),
(1111, 229, 16, 3),
(1112, 229, 20, 3),
(1113, 229, 4, 3),
(1114, 229, 3, 3),
(1115, 229, 17, 3),
(1116, 229, 2, 3),
(1117, 229, 5, 3),
(1118, 229, 15, 3),
(1119, 229, 10, 3),
(1120, 229, 13, 3),
(1121, 229, 19, 3),
(1122, 229, 14, 3),
(1123, 229, 9, 3),
(1124, 229, 12, 3),
(1125, 229, 26, 3),
(1126, 229, 6, 3),
(1127, 229, 1, 3),
(1128, 229, 21, 3),
(1129, 182, 18, 3),
(1130, 182, 7, 3),
(1131, 182, 8, 3),
(1132, 182, 22, 3),
(1133, 182, 11, 3),
(1134, 182, 25, 3),
(1135, 182, 16, 3),
(1136, 182, 20, 3),
(1137, 182, 4, 3),
(1138, 182, 3, 3),
(1139, 182, 17, 3),
(1140, 182, 2, 3),
(1141, 182, 5, 3),
(1142, 182, 15, 3),
(1143, 182, 10, 3),
(1144, 182, 13, 3),
(1145, 182, 19, 3),
(1146, 182, 14, 3),
(1147, 182, 9, 3),
(1148, 182, 12, 3),
(1149, 182, 26, 3),
(1150, 182, 6, 3),
(1151, 182, 1, 3),
(1152, 182, 21, 3),
(1153, 233, 18, 3),
(1154, 233, 7, 3),
(1155, 233, 8, 3),
(1156, 233, 22, 3),
(1157, 233, 11, 3),
(1158, 233, 25, 3),
(1159, 233, 16, 3),
(1160, 233, 20, 3),
(1161, 233, 4, 3),
(1162, 233, 3, 3),
(1163, 233, 17, 3),
(1164, 233, 2, 3),
(1165, 233, 5, 3),
(1166, 233, 15, 3),
(1167, 233, 10, 3),
(1168, 233, 13, 3),
(1169, 233, 19, 3),
(1170, 233, 14, 3),
(1171, 233, 9, 3),
(1172, 233, 12, 3),
(1173, 233, 26, 3),
(1174, 233, 6, 3),
(1175, 233, 1, 3),
(1176, 233, 21, 3),
(1177, 176, 18, 3),
(1178, 176, 7, 3),
(1179, 176, 8, 3),
(1180, 176, 22, 3),
(1181, 176, 11, 3),
(1182, 176, 25, 3),
(1183, 176, 16, 3),
(1184, 176, 20, 3),
(1185, 176, 4, 3),
(1186, 176, 3, 3),
(1187, 176, 17, 3),
(1188, 176, 2, 3),
(1189, 176, 5, 3),
(1190, 176, 15, 3),
(1191, 176, 10, 3),
(1192, 176, 13, 3),
(1193, 176, 19, 3),
(1194, 176, 14, 3),
(1195, 176, 9, 3),
(1196, 176, 12, 3),
(1197, 176, 26, 3),
(1198, 176, 6, 3),
(1199, 176, 1, 3),
(1200, 176, 21, 3),
(1201, 165, 18, 3),
(1202, 165, 7, 3),
(1203, 165, 8, 3),
(1204, 165, 22, 3),
(1205, 165, 11, 3),
(1206, 165, 25, 3),
(1207, 165, 16, 3),
(1208, 165, 20, 3),
(1209, 165, 4, 3),
(1210, 165, 3, 3),
(1211, 165, 17, 3),
(1212, 165, 2, 3),
(1213, 165, 5, 3),
(1214, 165, 15, 3),
(1215, 165, 10, 3),
(1216, 165, 13, 3),
(1217, 165, 19, 3),
(1218, 165, 14, 3),
(1219, 165, 9, 3),
(1220, 165, 12, 3),
(1221, 165, 26, 3),
(1222, 165, 6, 3),
(1223, 165, 1, 3),
(1224, 165, 21, 3),
(1225, 189, 18, 3),
(1226, 189, 7, 3),
(1227, 189, 8, 3),
(1228, 189, 22, 3),
(1229, 189, 11, 3),
(1230, 189, 25, 3),
(1231, 189, 16, 3),
(1232, 189, 20, 3),
(1233, 189, 4, 3),
(1234, 189, 3, 3),
(1235, 189, 17, 3),
(1236, 189, 2, 3),
(1237, 189, 5, 3),
(1238, 189, 15, 3),
(1239, 189, 10, 3),
(1240, 189, 13, 3),
(1241, 189, 19, 3),
(1242, 189, 14, 3),
(1243, 189, 9, 3),
(1244, 189, 12, 3),
(1245, 189, 26, 3),
(1246, 189, 6, 3),
(1247, 189, 1, 3),
(1248, 189, 21, 3),
(1249, 187, 18, 3),
(1250, 187, 7, 3),
(1251, 187, 8, 3),
(1252, 187, 22, 3),
(1253, 187, 11, 3),
(1254, 187, 25, 3),
(1255, 187, 16, 3),
(1256, 187, 20, 3),
(1257, 187, 4, 3),
(1258, 187, 3, 3),
(1259, 187, 17, 3),
(1260, 187, 2, 3),
(1261, 187, 5, 3),
(1262, 187, 15, 3),
(1263, 187, 10, 3),
(1264, 187, 13, 3),
(1265, 187, 19, 3),
(1266, 187, 14, 3),
(1267, 187, 9, 3),
(1268, 187, 12, 3),
(1269, 187, 26, 3),
(1270, 187, 6, 3),
(1271, 187, 1, 3),
(1272, 187, 21, 3),
(1273, 170, 18, 3),
(1274, 170, 7, 3),
(1275, 170, 8, 3),
(1276, 170, 22, 3),
(1277, 170, 11, 3),
(1278, 170, 25, 3),
(1279, 170, 16, 3),
(1280, 170, 20, 3),
(1281, 170, 4, 3),
(1282, 170, 3, 3),
(1283, 170, 17, 3),
(1284, 170, 2, 3),
(1285, 170, 5, 3),
(1286, 170, 15, 3),
(1287, 170, 10, 3),
(1288, 170, 13, 3),
(1289, 170, 19, 3),
(1290, 170, 14, 3),
(1291, 170, 9, 3),
(1292, 170, 12, 3),
(1293, 170, 26, 3),
(1294, 170, 6, 3),
(1295, 170, 1, 3),
(1296, 170, 21, 3),
(1297, 173, 18, 3),
(1298, 173, 7, 3),
(1299, 173, 8, 3),
(1300, 173, 22, 3),
(1301, 173, 11, 3),
(1302, 173, 25, 3),
(1303, 173, 16, 3),
(1304, 173, 20, 3),
(1305, 173, 4, 3),
(1306, 173, 3, 3),
(1307, 173, 17, 3),
(1308, 173, 2, 3),
(1309, 173, 5, 3),
(1310, 173, 15, 3),
(1311, 173, 10, 3),
(1312, 173, 13, 3),
(1313, 173, 19, 3),
(1314, 173, 14, 3),
(1315, 173, 9, 3),
(1316, 173, 12, 3),
(1317, 173, 26, 3),
(1318, 173, 6, 3),
(1319, 173, 1, 3),
(1320, 173, 21, 3),
(1321, 167, 18, 3),
(1322, 167, 7, 3),
(1323, 167, 8, 3),
(1324, 167, 22, 3),
(1325, 167, 11, 3),
(1326, 167, 25, 3),
(1327, 167, 16, 3),
(1328, 167, 20, 3),
(1329, 167, 4, 3),
(1330, 167, 3, 3),
(1331, 167, 17, 3),
(1332, 167, 2, 3),
(1333, 167, 5, 3),
(1334, 167, 15, 3),
(1335, 167, 10, 3),
(1336, 167, 13, 3),
(1337, 167, 19, 3),
(1338, 167, 14, 3),
(1339, 167, 9, 3),
(1340, 167, 12, 3),
(1341, 167, 26, 3),
(1342, 167, 6, 3),
(1343, 167, 1, 3),
(1344, 167, 21, 3),
(1345, 197, 18, 3),
(1346, 197, 7, 3),
(1347, 197, 8, 3),
(1348, 197, 22, 3),
(1349, 197, 11, 3),
(1350, 197, 25, 3),
(1351, 197, 16, 3),
(1352, 197, 20, 3),
(1353, 197, 4, 3),
(1354, 197, 3, 3),
(1355, 197, 17, 3),
(1356, 197, 2, 3),
(1357, 197, 5, 3),
(1358, 197, 15, 3),
(1359, 197, 10, 3),
(1360, 197, 13, 3),
(1361, 197, 19, 3),
(1362, 197, 14, 3),
(1363, 197, 9, 3),
(1364, 197, 12, 3),
(1365, 197, 26, 3),
(1366, 197, 6, 3),
(1367, 197, 1, 3),
(1368, 197, 21, 3),
(1369, 239, 18, 3),
(1370, 239, 7, 3),
(1371, 239, 8, 3),
(1372, 239, 22, 3),
(1373, 239, 11, 3),
(1374, 239, 25, 3),
(1375, 239, 16, 3),
(1376, 239, 20, 3),
(1377, 239, 4, 3),
(1378, 239, 3, 3),
(1379, 239, 17, 3),
(1380, 239, 2, 3),
(1381, 239, 5, 3),
(1382, 239, 15, 3),
(1383, 239, 10, 3),
(1384, 239, 13, 3),
(1385, 239, 19, 3),
(1386, 239, 14, 3),
(1387, 239, 9, 3),
(1388, 239, 12, 3),
(1389, 239, 26, 3),
(1390, 239, 6, 3),
(1391, 239, 1, 3),
(1392, 239, 21, 3),
(1393, 116, 18, 3),
(1394, 116, 7, 3),
(1395, 116, 8, 3),
(1396, 116, 22, 3),
(1397, 116, 11, 3),
(1398, 116, 25, 3),
(1399, 116, 16, 3),
(1400, 116, 20, 3),
(1401, 116, 4, 3),
(1402, 116, 3, 3),
(1403, 116, 17, 3),
(1404, 116, 2, 3),
(1405, 116, 5, 3),
(1406, 116, 15, 3),
(1407, 116, 10, 3),
(1408, 116, 13, 3),
(1409, 116, 19, 3),
(1410, 116, 14, 3),
(1411, 116, 9, 3),
(1412, 116, 12, 3),
(1413, 116, 26, 3),
(1414, 116, 6, 3),
(1415, 116, 1, 3),
(1416, 116, 21, 3),
(1417, 178, 18, 3),
(1418, 178, 7, 3),
(1419, 178, 8, 3),
(1420, 178, 22, 3),
(1421, 178, 11, 3),
(1422, 178, 25, 3),
(1423, 178, 16, 3),
(1424, 178, 20, 3),
(1425, 178, 4, 3),
(1426, 178, 3, 3),
(1427, 178, 17, 3),
(1428, 178, 2, 3),
(1429, 178, 5, 3),
(1430, 178, 15, 3),
(1431, 178, 10, 3),
(1432, 178, 13, 3),
(1433, 178, 19, 3),
(1434, 178, 14, 3),
(1435, 178, 9, 3),
(1436, 178, 12, 3),
(1437, 178, 26, 3),
(1438, 178, 6, 3),
(1439, 178, 1, 3),
(1440, 178, 21, 3);

-- --------------------------------------------------------

--
-- Table structure for table `pet_species`
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
-- Dumping data for table `pet_species`
--

INSERT INTO `pet_species` (`species_id`, `species_name`, `region_id`, `base_hp`, `base_atk`, `base_def`, `base_init`) VALUES
(58, 'Lamia', 1, 11, 9, 7, 8),
(59, 'Centaur', 1, 12, 10, 8, 9),
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
(240, 'Black Turtle', 5, 19, 7, 18, 4),
(241, 'Lilith', 14, 18, 14, 10, 18),
(242, 'Kappa', 7, 12, 15, 19, 8),
(243, 'Kangaroo', 17, 14, 18, 10, 18),
(244, 'Keelut', 16, 10, 12, 8, 15),
(245, 'Mimic', 22, 8, 15, 12, 2),
(246, 'Jackalope', 22, 10, 12, 8, 18),
(247, 'Mad Hatter', 22, 10, 12, 10, 12),
(248, 'Death', 22, 10, 18, 11, 12),
(249, 'War', 22, 8, 15, 12, 13),
(250, 'Pestilence', 22, 8, 15, 10, 14),
(251, 'Fury', 22, 8, 19, 4, 12),
(252, 'Famine', 25, 10, 12, 13, 17),
(253, 'Elf', 3, 6, 12, 11, 8),
(254, 'Blaze Cat', 7, 7, 11, 7, 12),
(255, 'Waterlil', 5, 15, 11, 8, 1),
(256, 'Slidred', 22, 6, 5, 12, 15),
(257, 'Alisnapor', 25, 6, 18, 18, 2),
(258, 'Daeodon', 4, 12, 18, 10, 11),
(259, 'Anomalocaris', 10, 15, 8, 10, 12),
(260, 'Adaro', 11, 15, 13, 10, 18),
(261, 'Akaname', 7, 8, 15, 10, 17),
(262, 'Bee', 4, 8, 15, 8, 12),
(263, 'Bullywug', 3, 8, 5, 5, 15),
(264, 'Landharpy', 15, 12, 17, 10, 19),
(265, 'Yatagarasu', 7, 8, 12, 12, 13),
(266, 'Tengu', 7, 12, 15, 11, 18),
(267, 'Klabuster', 6, 10, 5, 5, 1),
(268, 'Frost Giant', 2, 15, 18, 13, 5),
(269, 'Fire Giant', 2, 15, 18, 13, 5),
(270, 'Spoodr', 18, 5, 18, 2, 19),
(271, 'Ziz', 14, 15, 17, 10, 18),
(272, 'Toucan', 19, 10, 12, 8, 15),
(273, 'Tiddalik', 18, 16, 12, 10, 18),
(274, 'Spinosaur', 15, 12, 19, 12, 10),
(275, 'Sea Turtle', 17, 12, 5, 18, 14),
(276, 'Pishtaco', 20, 10, 13, 8, 18),
(277, 'Penguin', 16, 10, 9, 8, 15),
(278, 'Pegasus', 1, 12, 13, 10, 14),
(279, 'Oryx', 13, 12, 10, 15, 17),
(280, 'Nightmarcher', 11, 12, 18, 10, 15),
(281, 'Moo', 11, 12, 13, 18, 5),
(282, 'Manticore', 12, 10, 18, 16, 10),
(283, 'La Mano Peluda', 8, 5, 16, 3, 12),
(284, 'Harpy', 1, 12, 17, 10, 18),
(285, 'Dugong', 13, 15, 10, 10, 12),
(286, 'Camel', 12, 10, 15, 12, 13),
(287, 'Apsara', 6, 12, 18, 13, 10),
(288, 'Armadillo', 19, 10, 8, 19, 12),
(289, 'Kachina', 25, 12, 15, 10, 15),
(290, 'Papillon', 3, 10, 5, 5, 13),
(291, 'Brownie', 26, 10, 12, 5, 18),
(292, 'Sunwukong', 5, 14, 13, 10, 13),
(293, 'Tuskin', 27, 18, 12, 10, 4),
(294, 'Rhiwool', 27, 18, 12, 14, 4),
(295, 'Rheintalensis', 27, 14, 11, 12, 7),
(296, 'Argentowlis', 27, 16, 12, 10, 8),
(297, 'Hairypterix', 27, 13, 11, 9, 12),
(298, 'Turtworld', 27, 22, 8, 18, 3),
(299, 'Gigantopithecus', 27, 19, 13, 14, 5),
(301, 'Moopie', 28, 12, 8, 8, 13),
(302, 'Archelon', 28, 18, 9, 17, 4),
(303, 'Doggo', 28, 11, 10, 9, 12),
(304, 'Fernstalker', 27, 14, 11, 13, 8),
(305, 'Sabre', 27, 13, 15, 9, 14),
(306, 'Smilodon', 27, 16, 14, 10, 11),
(307, 'Turtfolk', 27, 17, 10, 16, 6),
(308, 'Tusktaur', 27, 20, 14, 12, 5),
(309, 'Urchskin', 28, 14, 8, 18, 9),
(310, 'Gaitress', 22, 9, 14, 6, 17),
(311, 'Kasurenijimi', 7, 11, 8, 7, 13),
(312, 'Meloncollie', 22, 15, 10, 12, 9),
(313, 'Marmotaur', 1, 12, 14, 12, 9),
(314, 'Strigowl', 1, 9, 11, 8, 15),
(315, 'Skjoldram', 2, 10, 13, 10, 11),
(316, 'Pineskrell', 2, 9, 11, 9, 14),
(317, 'Boghare', 26, 9, 11, 9, 14),
(318, 'Rookmourn', 26, 9, 12, 8, 14),
(319, 'Clockhare', 3, 9, 11, 9, 13),
(320, 'Stagel', 3, 12, 11, 14, 8),
(321, 'Sobolnik', 4, 10, 12, 9, 12),
(322, 'Gribboar', 4, 12, 11, 13, 9),
(323, 'Pumpkingull', 22, 9, 11, 8, 15),
(324, 'Snowloper', 22, 9, 10, 10, 13),
(325, 'Prairhorn', 25, 11, 12, 11, 12),
(326, 'Dustbison', 25, 12, 11, 13, 9),
(327, 'Silkcoon', 5, 9, 10, 10, 13),
(328, 'Bambadger', 5, 12, 11, 13, 9),
(329, 'Peaflare', 6, 8, 12, 9, 13),
(330, 'Palmyr', 6, 9, 11, 9, 13),
(331, 'Shibari', 7, 9, 10, 10, 13),
(332, 'Momoshika', 7, 10, 11, 10, 12),
(333, 'Coatimano', 8, 10, 12, 9, 12),
(334, 'Axolume', 8, 10, 10, 11, 12),
(335, 'Jaglare', 9, 10, 15, 9, 11),
(336, 'Macawtl', 9, 8, 11, 8, 15),
(337, 'Ceibler', 10, 10, 12, 10, 11),
(338, 'Motmora', 10, 8, 10, 9, 15),
(339, 'Dunefennec', 12, 11, 12, 11, 11),
(340, 'Caraviper', 12, 11, 12, 12, 10),
(341, 'Reedbull', 13, 13, 11, 13, 9),
(342, 'Ibisking', 13, 8, 11, 8, 15),
(343, 'Rockhyrax', 14, 10, 11, 13, 10),
(344, 'Datebat', 14, 9, 12, 8, 14),
(345, 'Ibiset', 15, 8, 11, 8, 15),
(346, 'Crocora', 15, 13, 11, 13, 9),
(347, 'Moonseal', 16, 11, 10, 11, 11),
(348, 'Terniq', 16, 9, 10, 9, 14),
(349, 'Emberu', 17, 11, 13, 11, 10),
(350, 'Wattlefox', 17, 12, 13, 11, 10),
(351, 'Cassowrath', 18, 10, 13, 11, 10),
(352, 'Gliderune', 18, 8, 10, 9, 15),
(353, 'Slothren', 19, 10, 10, 11, 11),
(354, 'Riverdillo', 19, 11, 12, 10, 11),
(355, 'Vicuñero', 20, 9, 10, 10, 13),
(356, 'Condorune', 20, 8, 11, 8, 15),
(357, 'Dodobold', 27, 11, 11, 13, 9),
(358, 'Squirricerat', 27, 12, 11, 12, 10),
(359, 'Reeffin', 28, 10, 10, 11, 12),
(360, 'Mantarrow', 28, 10, 10, 10, 13),
(361, 'Nenekea', 11, 10, 11, 10, 12),
(362, 'Cocoskink', 11, 10, 10, 11, 11),
(363, 'Frondbat', 11, 9, 10, 10, 13),
(364, 'Talosling', 1, 11, 13, 12, 9),
(365, 'Aegis Boar', 1, 14, 12, 15, 7),
(366, 'Tarnhelm', 2, 12, 12, 12, 10),
(367, 'Bellmare', 26, 12, 12, 12, 10),
(368, 'Reliquary Dove', 3, 11, 11, 12, 11),
(369, 'Iron Kobold', 3, 12, 13, 12, 9),
(370, 'Koschei Chain', 4, 12, 12, 12, 10),
(371, 'Tin Jack', 22, 12, 12, 12, 10),
(372, 'Peacepipe Bison', 25, 12, 11, 14, 8),
(373, 'Bronzejaw Ox', 13, 13, 11, 14, 8),
(374, 'Ark Seraph', 14, 11, 11, 13, 10),
(375, 'Scarabronze', 15, 11, 11, 13, 10),
(376, 'Aurorantler', 16, 12, 11, 13, 9),
(377, 'Mirror Crane', 5, 11, 11, 12, 11),
(378, 'Sword Koi', 5, 13, 11, 14, 8),
(379, 'Temple Mantis', 6, 11, 11, 13, 10),
(380, 'Obsidian Macuahuitl Hound', 9, 12, 13, 12, 9),
(381, 'Turquoise Scale Serpent', 10, 13, 13, 14, 8),
(382, 'Censer Lion', 12, 11, 13, 12, 9),
(383, 'Date Palm Beetle', 19, 11, 11, 13, 10),
(384, 'Conch Mail Turtle', 28, 13, 11, 14, 8),
(385, 'Ironbeak', 17, 12, 14, 13, 8),
(386, 'Tin Squirrel', 2, 11, 12, 12, 11),
(387, 'Anvil Tortoise', 27, 13, 12, 16, 6),
(388, 'Needlehog', 26, 11, 11, 13, 10),
(389, 'Mailtoad', 3, 13, 11, 14, 8),
(390, 'Sawfin', 28, 13, 11, 14, 8),
(391, 'Brasshorn Ram', 26, 14, 12, 15, 7),
(392, 'Coin Magpie', 3, 11, 11, 12, 11),
(393, 'Rust Otter', 4, 13, 11, 14, 8),
(394, 'Halberelk', 16, 12, 11, 13, 9);

-- --------------------------------------------------------

--
-- Table structure for table `picnic_tree_items`
--

CREATE TABLE `picnic_tree_items` (
  `picnic_item_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `available_quantity` int UNSIGNED NOT NULL DEFAULT '0',
  `chance_percent` decimal(5,2) NOT NULL DEFAULT '100.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `player_unlocked_species`
--

CREATE TABLE `player_unlocked_species` (
  `entryId` bigint UNSIGNED NOT NULL,
  `player_id` bigint UNSIGNED NOT NULL,
  `unlocked_species_id` smallint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `player_unlocked_species`
--

INSERT INTO `player_unlocked_species` (`entryId`, `player_id`, `unlocked_species_id`, `created_at`) VALUES
(1, 5, 268, '2026-02-13 07:42:37'),
(2, 5, 278, '2026-02-18 10:04:06'),
(3, 5, 59, '2026-02-18 10:04:13'),
(4, 5, 284, '2026-02-18 10:04:39'),
(5, 5, 269, '2026-02-18 10:10:09'),
(6, 5, 113, '2026-02-18 10:10:10'),
(7, 5, 117, '2026-02-18 10:10:49'),
(8, 5, 291, '2026-02-18 10:10:49'),
(9, 5, 115, '2026-02-18 10:10:55'),
(10, 5, 116, '2026-02-18 10:10:55'),
(11, 5, 290, '2026-02-18 10:13:07'),
(12, 5, 164, '2026-02-18 10:13:07'),
(13, 5, 263, '2026-02-18 10:13:07'),
(14, 5, 165, '2026-02-18 10:20:06'),
(15, 5, 253, '2026-02-18 10:20:09'),
(16, 5, 183, '2026-02-18 11:59:04'),
(17, 5, 184, '2026-02-18 11:59:04'),
(18, 5, 185, '2026-02-18 11:59:05'),
(19, 5, 283, '2026-02-18 12:00:05'),
(20, 5, 181, '2026-02-18 12:00:11'),
(21, 5, 180, '2026-02-18 12:00:17'),
(22, 5, 239, '2026-02-18 12:04:52'),
(23, 5, 259, '2026-02-18 12:04:55'),
(24, 5, 187, '2026-02-18 12:05:00'),
(25, 5, 241, '2026-02-18 12:09:47'),
(26, 5, 271, '2026-02-18 12:09:53'),
(27, 5, 195, '2026-02-18 12:09:55'),
(28, 5, 279, '2026-02-18 12:12:20'),
(29, 5, 193, '2026-02-18 12:12:25'),
(30, 5, 285, '2026-02-18 12:12:27'),
(31, 5, 191, '2026-02-18 12:14:57'),
(32, 5, 286, '2026-02-18 12:14:59'),
(33, 5, 282, '2026-02-18 12:15:07'),
(34, 5, 229, '2026-02-18 12:58:39'),
(35, 5, 277, '2026-02-18 12:59:00'),
(36, 5, 244, '2026-02-18 12:59:06'),
(37, 5, 274, '2026-02-18 13:00:11'),
(38, 5, 197, '2026-02-18 14:01:53'),
(39, 5, 264, '2026-02-18 14:02:21'),
(40, 5, 243, '2026-02-18 15:52:28'),
(41, 5, 275, '2026-02-18 15:52:32'),
(42, 5, 231, '2026-02-18 15:52:34'),
(43, 5, 281, '2026-02-18 15:54:06'),
(44, 5, 260, '2026-02-18 15:54:11'),
(45, 5, 189, '2026-02-18 15:54:17'),
(46, 5, 280, '2026-02-18 15:54:31'),
(47, 5, 270, '2026-02-18 15:54:58'),
(48, 5, 273, '2026-02-18 15:54:58'),
(49, 5, 233, '2026-02-18 15:55:04'),
(50, 5, 276, '2026-02-20 09:09:20'),
(51, 5, 237, '2026-02-20 09:09:22'),
(52, 5, 238, '2026-02-20 09:09:24'),
(53, 5, 294, '2026-02-27 07:37:15'),
(54, 5, 298, '2026-02-27 07:54:43'),
(55, 13, 276, '2026-02-27 13:09:05'),
(56, 13, 189, '2026-02-27 13:10:20'),
(57, 5, 311, '2026-03-19 23:31:56'),
(58, 5, 242, '2026-03-19 23:32:19'),
(59, 5, 305, '2026-03-19 23:32:48'),
(60, 5, 245, '2026-03-19 23:32:58'),
(61, 5, 251, '2026-03-19 23:33:13'),
(62, 13, 177, '2026-03-20 14:10:24'),
(63, 5, 265, '2026-03-23 08:46:57');

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `region_id` smallint UNSIGNED NOT NULL,
  `region_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`region_id`, `region_name`) VALUES
(21, 'AAAAAAAAAAAAAAAAAAAA'),
(1, 'Aegia Aeterna'),
(27, 'Aeonstep Plateau'),
(6, 'Baharamandal'),
(26, 'Bretonreach'),
(12, 'Crescent Caliphate'),
(9, 'Eagle Serpent Dominion'),
(14, 'Eretz-Shalem League'),
(19, 'Gran Columbia'),
(13, 'Hammurabia'),
(10, 'Itzam Empire'),
(15, 'Kemet'),
(5, 'Lotus-Dragon Kingdom'),
(2, 'Nornheim'),
(28, 'Pelagora'),
(17, 'Red Sun Commonwealth'),
(3, 'Rheinland'),
(4, 'Rodinian Tsardom'),
(20, 'Sapa Inti Empire'),
(16, 'Sila Council'),
(25, 'Sovereign Tribes of the Ancestral Plains'),
(11, 'Spice Route League'),
(22, 'United free Republic of Borealia'),
(8, 'Xochimex'),
(7, 'Yamanokubo'),
(18, 'Yara Nations');

-- --------------------------------------------------------

--
-- Table structure for table `rsc_wheel_spins`
--

CREATE TABLE `rsc_wheel_spins` (
  `user_id` bigint UNSIGNED NOT NULL,
  `last_spin_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rsc_wheel_spins`
--

INSERT INTO `rsc_wheel_spins` (`user_id`, `last_spin_at`) VALUES
(5, '2025-12-18 17:50:03');

-- --------------------------------------------------------

--
-- Table structure for table `shops`
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
-- Dumping data for table `shops`
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
(9, 'Karl\'s grosser Kiosk', 3, 1, 45, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shop_inventory`
--

CREATE TABLE `shop_inventory` (
  `shop_id` int UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `stock` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shop_inventory`
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

-- --------------------------------------------------------

--
-- Table structure for table `shop_transactions`
--

CREATE TABLE `shop_transactions` (
  `transaction_id` bigint UNSIGNED NOT NULL,
  `shop_id` int UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `currency_id` tinyint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shop_transactions`
--

INSERT INTO `shop_transactions` (`transaction_id`, `shop_id`, `user_id`, `item_id`, `quantity`, `unit_price`, `currency_id`, `created_at`) VALUES
(1, 1, 1, 3, 1, 220.00, 1, '2025-09-02 20:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `species_elements`
--

CREATE TABLE `species_elements` (
  `species_id` smallint UNSIGNED NOT NULL,
  `element_id` smallint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `species_elements`
--

INSERT INTO `species_elements` (`species_id`, `element_id`) VALUES
(59, 1),
(113, 1),
(180, 1),
(185, 1),
(187, 1),
(229, 1),
(230, 1),
(235, 1),
(243, 1),
(246, 1),
(258, 1),
(272, 1),
(273, 1),
(279, 1),
(281, 1),
(285, 1),
(286, 1),
(294, 1),
(303, 1),
(116, 2),
(164, 2),
(173, 2),
(177, 2),
(251, 2),
(254, 2),
(269, 2),
(112, 3),
(117, 3),
(167, 3),
(176, 3),
(179, 3),
(183, 3),
(186, 3),
(188, 3),
(189, 3),
(191, 3),
(195, 3),
(232, 3),
(235, 3),
(236, 3),
(240, 3),
(242, 3),
(255, 3),
(259, 3),
(260, 3),
(263, 3),
(273, 3),
(274, 3),
(275, 3),
(277, 3),
(285, 3),
(287, 3),
(295, 3),
(301, 3),
(302, 3),
(309, 3),
(113, 4),
(170, 4),
(231, 4),
(166, 5),
(169, 5),
(187, 5),
(234, 5),
(237, 5),
(242, 5),
(253, 5),
(255, 5),
(298, 5),
(299, 5),
(304, 5),
(307, 5),
(312, 5),
(118, 6),
(170, 6),
(173, 6),
(174, 6),
(182, 6),
(190, 6),
(193, 6),
(197, 6),
(238, 6),
(264, 6),
(265, 6),
(266, 6),
(271, 6),
(272, 6),
(278, 6),
(284, 6),
(296, 6),
(297, 6),
(184, 7),
(196, 7),
(252, 7),
(258, 7),
(264, 7),
(279, 7),
(286, 7),
(288, 7),
(293, 7),
(308, 7),
(194, 8),
(237, 8),
(267, 8),
(275, 8),
(295, 8),
(298, 8),
(302, 8),
(306, 8),
(168, 9),
(190, 9),
(193, 9),
(194, 9),
(195, 9),
(241, 9),
(247, 9),
(257, 9),
(289, 9),
(292, 9),
(296, 9),
(176, 10),
(192, 10),
(259, 10),
(262, 10),
(270, 10),
(290, 10),
(58, 11),
(171, 11),
(175, 11),
(197, 11),
(250, 11),
(261, 11),
(262, 11),
(282, 11),
(309, 11),
(59, 12),
(172, 12),
(188, 12),
(236, 12),
(243, 12),
(249, 12),
(251, 12),
(257, 12),
(263, 12),
(266, 12),
(268, 12),
(269, 12),
(280, 12),
(292, 12),
(299, 12),
(307, 12),
(308, 12),
(310, 12),
(178, 13),
(228, 13),
(229, 13),
(244, 13),
(256, 13),
(268, 13),
(277, 13),
(293, 13),
(294, 13),
(305, 13),
(114, 14),
(115, 14),
(116, 14),
(117, 14),
(166, 14),
(168, 14),
(169, 14),
(172, 14),
(178, 14),
(179, 14),
(181, 14),
(231, 14),
(239, 14),
(244, 14),
(245, 14),
(248, 14),
(260, 14),
(261, 14),
(276, 14),
(280, 14),
(283, 14),
(311, 14),
(58, 15),
(112, 15),
(115, 15),
(164, 15),
(165, 15),
(167, 15),
(180, 15),
(181, 15),
(183, 15),
(185, 15),
(192, 15),
(196, 15),
(228, 15),
(230, 15),
(232, 15),
(239, 15),
(241, 15),
(248, 15),
(249, 15),
(250, 15),
(252, 15),
(254, 15),
(265, 15),
(270, 15),
(276, 15),
(282, 15),
(283, 15),
(284, 15),
(297, 15),
(304, 15),
(305, 15),
(306, 15),
(310, 15),
(240, 16),
(245, 16),
(288, 16),
(171, 17),
(175, 17),
(182, 17),
(184, 17),
(186, 17),
(189, 17),
(191, 17),
(233, 17),
(238, 17),
(256, 17),
(271, 17),
(274, 17),
(114, 18),
(118, 18),
(165, 18),
(174, 18),
(177, 18),
(233, 18),
(234, 18),
(246, 18),
(247, 18),
(253, 18),
(267, 18),
(278, 18),
(287, 18),
(289, 18),
(290, 18),
(291, 18),
(301, 18),
(311, 18),
(312, 18);

-- --------------------------------------------------------

--
-- Table structure for table `trainer_roster`
--

CREATE TABLE `trainer_roster` (
  `trainer_id` bigint UNSIGNED NOT NULL,
  `pet_instance_id` bigint UNSIGNED NOT NULL,
  `roster_position` tinyint UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint UNSIGNED NOT NULL,
  `username` varchar(32) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password_hash` varbinary(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'alice', 'alice@example.com', 0xc6ba365067dce4da848e328e8954a2250f8328cd221f027c924103f2eee97bd8, '2025-09-02 20:32:41', '2025-09-02 20:32:41'),
(2, 'bob', 'bob@example.com', 0xe2c1ad16389eb7cbd1a06485b6c8ace59a93d9f8a21eea31d1510e5a07e9d997, '2025-09-02 20:32:41', '2025-09-02 20:32:41'),
(3, 'cara', 'cara@example.com', 0xbc97782935cdbc5c9f1561977923052c191ea3416d97efd7dc4721171a19702c, '2025-09-02 20:32:41', '2025-09-02 20:32:41'),
(4, 'ahae43', 'ashah@email.com', 0x243279243130244a4347396a4237415361796b587467454c76787265654a4f46457859353733502f666f712e7136466276302f7a73414e586a437571, '2025-09-09 10:23:01', '2025-09-09 10:23:01'),
(5, 'BoobsMcKenzie', 'linkjosha@yahoo.de', 0x243279243130246f6373644439346f79336f417437776c7457576b6765704b3566305937545a7a53557446464f3357556e6c4d383576357254634279, '2025-09-09 10:24:07', '2025-09-09 10:24:07'),
(7, 'Kuroneko', 'Linkjosha@bleb.com', 0x2432792431302463375a5233596f2e5a616132797045464c3170677675336f72314a6248304e2f4a30765775624c6d5951763954465644454b383965, '2025-09-09 12:12:48', '2025-09-09 12:12:48'),
(8, 'baiguabdrgigurkfsdkishrgusildrgh', 'agulra@adhuasoirg.com', 0x243279243130244d2f35687a2e583032686e7052793036386c476c752e755578636d783553676267364d785465456c6a645843655a304753756c4a69, '2025-09-09 12:39:18', '2025-09-09 12:39:18'),
(9, 'e6uzrdu', 'jet6i@r.com', 0x243279243130242e4c47336673776f4a56754b7267494474455a32372e3538524c65586f342e6839473954582e5775546d7953746679775437516236, '2025-09-09 12:57:33', '2025-09-09 12:57:33'),
(10, 'z454zb4', 'zb43@4554z.c', 0x24327924313024577164344943636a5138443449514a782e4163647265736e7957566c787145414c37354f707969474550727144524735596d487657, '2025-09-09 13:41:02', '2025-09-09 13:41:02'),
(11, 'Bummsibuddy', 'Get@get.com', 0x243279243130246136304235526c79497a6f7a6d6148794d557a45797531466a50544a71786861656862626d697270527737537447355074394b386d, '2025-09-10 09:56:58', '2025-09-10 09:56:58'),
(12, 'Kanna-chan', 'Yeay@yahoo.de', 0x2432792431302442744551344c6137782f347746356d3352456a5a554f70526b3964626b5246334e4e3149527a507565726d67315570747546424471, '2025-11-15 21:58:30', '2025-11-15 21:58:30'),
(13, 'Bedavedave', 'josha.kraettli@gmail.com', 0x243279243130246c42716b2e744e75642e4e4d7955574b617051656c75665a6f44584b317033485a69384e6b544833756e3339465853784f70586171, '2025-11-15 22:33:59', '2025-11-20 22:34:28'),
(14, 'ARSGRVrdasgsdfd', 'dinimam@gmail.com', 0x24327924313024504d4779443258583139384d796d4a6265644f736f7567326c6155426e6976344f6f75666e51415a4d356a63733344414549684a4b, '2025-11-17 13:30:52', '2025-11-17 13:30:52'),
(15, 'Blob', 'izvzvzzuzu@hgvhht.com', 0x243279243130244a5158433641525648317645726130344f746b2f732e7552736e377154637043347430626965355731507a4b79564466557444642e, '2025-11-18 12:13:25', '2025-11-18 12:13:25'),
(16, 'Burstibops', 'Shwifty@shwifty.com', 0x243279243130246836354d6a74445a2e632f516a7a395a7558546f512e5772595241306d6a616f47464d4463472e385166414d6e2f484d514f447779, '2025-12-16 08:15:35', '2025-12-16 08:15:35'),
(17, 'Barry Burrito', 'dick@gmail.com', 0x243279243130246e4c3046584e5463347848614c68446c6456466c306530584b6648776a46436e516a76475a7a4f566934384451735736333175642e, '2025-12-23 14:08:25', '2025-12-23 14:08:25'),
(18, 'Forever NF', 'nicolas.leffler@icloud.com', 0x243279243130246d626c73373837756c565575546a7739482e452f7565587a5036306d6464384478634b4a583954445956744e427734582e592f5357, '2026-01-16 23:40:17', '2026-01-16 23:40:17');

-- --------------------------------------------------------

--
-- Table structure for table `user_balances`
--

CREATE TABLE `user_balances` (
  `user_id` bigint UNSIGNED NOT NULL,
  `currency_id` tinyint UNSIGNED NOT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_balances`
--

INSERT INTO `user_balances` (`user_id`, `currency_id`, `balance`) VALUES
(1, 1, 780.00),
(1, 2, 0.00),
(2, 1, 750.00),
(2, 2, 0.00),
(3, 1, 1200.00),
(3, 2, 50.00),
(5, 1, 552259.00),
(7, 1, 3000.00),
(8, 1, 3000.00),
(9, 1, 3000.00),
(10, 1, 3000.00),
(11, 1, 300.01),
(12, 1, 320.00),
(13, 1, 2146.20),
(14, 1, 575.00),
(15, 1, 3000.00),
(16, 1, 2500.00),
(17, 1, 3385.00),
(18, 1, 8098.80);

-- --------------------------------------------------------

--
-- Table structure for table `user_bank`
--

CREATE TABLE `user_bank` (
  `user_id` bigint UNSIGNED NOT NULL,
  `currency_id` tinyint UNSIGNED NOT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `interest` decimal(14,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_bank`
--

INSERT INTO `user_bank` (`user_id`, `currency_id`, `balance`, `interest`) VALUES
(11, 1, 1976291.78, 20251115.00),
(0, 1, 0.00, 20251116.00),
(13, 1, 107340.55, 20260325.00),
(12, 1, 2138.00, 20251116.00),
(14, 1, 2562.50, 20251120.00),
(5, 1, 35397612252.55, 20260412.00),
(18, 1, 0.50, 20260117.00);

-- --------------------------------------------------------

--
-- Table structure for table `user_direct_messages`
--

CREATE TABLE `user_direct_messages` (
  `message_id` bigint UNSIGNED NOT NULL,
  `sender_id` bigint UNSIGNED NOT NULL,
  `recipient_id` bigint UNSIGNED NOT NULL,
  `message_ciphertext` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_friends`
--

CREATE TABLE `user_friends` (
  `connection_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `friend_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_friends`
--

INSERT INTO `user_friends` (`connection_id`, `user_id`, `friend_id`) VALUES
(1, 11, 5);

-- --------------------------------------------------------

--
-- Table structure for table `user_inventory`
--

CREATE TABLE `user_inventory` (
  `user_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL,
  `acquired_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_inventory`
--

INSERT INTO `user_inventory` (`user_id`, `item_id`, `quantity`, `acquired_at`) VALUES
(1, 1, 15, '2025-09-02 20:37:52'),
(1, 2, 2, '2025-09-02 20:37:52'),
(2, 1, 8, '2025-09-02 20:37:52'),
(2, 7, 1, '2025-09-02 20:37:52'),
(3, 4, 1, '2025-09-02 20:37:52'),
(3, 6, 3, '2025-09-02 20:37:52'),
(5, 1, 5, '2025-11-28 09:12:58'),
(5, 2, 52, '2025-11-28 09:12:58'),
(5, 3, 1, '2026-03-21 13:08:18'),
(5, 4, 15, '2026-01-15 21:41:42'),
(5, 5, 2, '2025-12-15 13:51:59'),
(5, 6, 15, '2025-12-02 21:20:15'),
(5, 7, 15, '2025-12-02 21:19:58'),
(5, 8, 15, '2025-12-02 21:19:45'),
(5, 9, 15, '2025-12-03 07:32:31'),
(5, 10, 15, '2025-12-02 21:20:52'),
(5, 11, 14, '2025-11-28 09:36:56'),
(5, 12, 12, '2025-11-28 09:36:38'),
(5, 14, 14, '2025-12-12 18:07:40'),
(5, 15, 13, '2025-12-12 18:07:40'),
(5, 16, 2, '2025-12-12 18:07:40'),
(5, 18, 11, '2025-12-15 14:32:13'),
(5, 19, 7, '2025-12-16 07:29:58'),
(5, 20, 1, '2026-03-23 08:09:05'),
(5, 22, 3, '2025-12-12 18:07:40'),
(5, 23, 12, '2025-11-28 09:35:00'),
(5, 24, 14, '2025-11-28 09:36:20'),
(5, 25, 10, '2025-12-16 07:29:04'),
(5, 27, 1, '2026-03-24 13:52:12'),
(5, 29, 22, '2025-12-20 00:15:14'),
(5, 30, 6, '2025-12-15 14:32:11'),
(5, 31, 1, '2026-03-23 07:41:16'),
(5, 34, 6, '2025-12-15 14:01:19'),
(5, 37, 7, '2025-12-18 16:48:57'),
(5, 38, 1, '2025-12-18 16:50:03'),
(5, 41, 2, '2026-03-20 23:21:42'),
(5, 42, 1, '2026-03-24 13:51:48'),
(5, 44, 7, '2026-02-13 08:03:28'),
(5, 46, 1, '2026-03-24 13:51:50'),
(5, 48, 1, '2026-03-24 13:51:23'),
(5, 49, 2, '2026-03-21 21:13:53'),
(5, 52, 4, '2026-03-20 23:38:31'),
(5, 53, 2, '2026-03-21 13:08:53'),
(5, 55, 1, '2026-03-24 13:52:01'),
(5, 57, 1, '2026-03-23 08:39:49'),
(5, 58, 1, '2026-03-23 07:39:24'),
(5, 60, 1, '2026-03-21 13:17:31'),
(5, 61, 2, '2026-03-23 07:41:27'),
(5, 62, 1, '2026-03-24 13:51:36'),
(5, 65, 1, '2026-03-24 13:51:57'),
(5, 67, 1, '2026-03-23 07:39:34'),
(5, 68, 3, '2026-03-20 23:08:33'),
(5, 71, 1, '2026-03-23 07:39:18'),
(5, 72, 1, '2026-03-20 23:46:59'),
(5, 74, 1, '2026-03-21 13:17:36'),
(5, 75, 1, '2026-03-23 08:45:59'),
(5, 77, 1, '2026-03-21 13:24:16'),
(5, 78, 1, '2026-03-21 12:44:35'),
(5, 81, 1, '2026-03-21 13:24:24'),
(5, 83, 1, '2025-12-15 13:48:12'),
(5, 84, 9, '2025-12-16 08:09:42'),
(5, 86, 2, '2026-03-21 13:17:28'),
(5, 87, 3, '2025-12-16 08:09:42'),
(5, 88, 3, '2025-12-16 08:09:42'),
(5, 89, 5, '2025-12-16 08:09:42'),
(5, 90, 3, '2026-03-21 13:08:50'),
(5, 91, 15, '2026-01-14 13:50:27'),
(5, 92, 1, '2026-03-21 13:24:21'),
(5, 93, 1, '2026-03-21 13:24:29'),
(5, 95, 2, '2026-03-24 13:51:16'),
(5, 102, 1, '2026-03-24 13:51:54'),
(5, 104, 1, '2026-03-24 13:52:04'),
(5, 105, 1, '2026-03-23 08:12:08'),
(5, 110, 1, '2026-03-23 07:37:48'),
(5, 113, 2, '2026-03-23 07:38:12'),
(5, 114, 1, '2026-03-25 15:32:09'),
(5, 115, 1, '2026-03-20 23:47:29'),
(5, 116, 1, '2026-03-24 13:51:26'),
(5, 119, 1, '2026-03-23 07:38:05'),
(5, 121, 15, '2026-01-14 22:14:07'),
(5, 122, 16, '2026-01-15 21:41:42'),
(5, 124, 1, '2026-03-20 23:21:36'),
(5, 147, 1, '2026-03-20 23:30:48'),
(5, 151, 1, '2026-03-20 23:47:32'),
(5, 152, 1, '2026-03-20 23:33:45'),
(5, 157, 1, '2026-03-20 23:33:42'),
(5, 160, 1, '2026-03-21 12:44:43'),
(5, 166, 1, '2026-03-20 23:41:30'),
(5, 214, 1, '2026-03-24 13:51:59'),
(5, 219, 1, '2026-03-24 13:51:21'),
(5, 220, 1, '2026-03-24 13:51:19'),
(10, 1, 1, '2025-09-10 09:17:48'),
(10, 2, 2, '2025-09-10 09:17:48'),
(11, 2, 9, '2025-09-10 10:01:47'),
(11, 3, 2, '2025-09-16 15:14:58'),
(11, 4, 3, '2025-09-16 15:15:00'),
(11, 6, 1, '2025-09-16 15:15:03'),
(11, 14, 1, '2025-11-15 18:37:57'),
(11, 15, 13, '2025-11-15 18:37:57'),
(12, 1, 2, '2025-11-15 23:08:59'),
(12, 2, 6, '2025-11-15 23:07:37'),
(12, 4, 1, '2025-11-15 23:08:09'),
(13, 1, 4, '2025-11-15 22:34:57'),
(13, 2, 10, '2025-11-15 22:34:57'),
(13, 4, 1, '2026-02-27 13:11:08'),
(13, 33, 1, '2026-03-20 14:10:14'),
(13, 91, 1, '2026-03-20 14:10:17'),
(13, 116, 1, '2026-03-20 14:10:10'),
(14, 1, 1, '2025-11-17 14:18:48'),
(14, 2, 2, '2025-11-17 14:18:48'),
(16, 1, 1, '2025-12-16 08:16:01'),
(16, 2, 3, '2025-12-16 08:16:01'),
(17, 1, 6, '2025-12-23 14:20:22'),
(17, 2, 14, '2025-12-23 14:20:22'),
(17, 18, 4, '2025-12-23 14:13:26'),
(18, 1, 1, '2026-01-16 23:43:52'),
(18, 2, 2, '2026-01-16 23:43:52'),
(18, 4, 5, '2026-01-16 23:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_map_unlocks`
--

CREATE TABLE `user_map_unlocks` (
  `user_id` bigint UNSIGNED NOT NULL,
  `map_key` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unlocked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_map_unlocks`
--

INSERT INTO `user_map_unlocks` (`user_id`, `map_key`, `unlocked_at`) VALUES
(5, 'aeonstep_plateau', '2026-02-20 23:49:43'),
(5, 'pelagora_ringtown', '2026-02-27 14:55:36'),
(13, 'aeonstep_plateau', '2026-02-27 13:09:25'),
(13, 'pelagora_ringtown', '2026-02-27 13:10:21');

-- --------------------------------------------------------

--
-- Table structure for table `wheel_of_fate_spins`
--

CREATE TABLE `wheel_of_fate_spins` (
  `user_id` bigint UNSIGNED NOT NULL,
  `last_spin_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `wheel_of_fate_spins`
--

INSERT INTO `wheel_of_fate_spins` (`user_id`, `last_spin_at`) VALUES
(5, '2026-03-19 20:00:31'),
(11, '2025-09-22 22:08:18'),
(12, '2025-11-16 00:08:09'),
(13, '2026-02-27 14:11:08'),
(14, '2025-11-19 11:40:49'),
(16, '2025-12-16 09:16:25'),
(17, '2025-12-23 15:13:26'),
(18, '2026-01-17 00:51:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abandoned_pets`
--
ALTER TABLE `abandoned_pets`
  ADD PRIMARY KEY (`ap_id`),
  ADD UNIQUE KEY `uq_abandoned_creature` (`creature_id`),
  ADD KEY `ix_abandoned_old_player` (`old_player_id`);

--
-- Indexes for table `creature_name_votes`
--
ALTER TABLE `creature_name_votes`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`currency_id`),
  ADD UNIQUE KEY `uq_currency_code` (`currency_code`);

--
-- Indexes for table `currency_ledger`
--
ALTER TABLE `currency_ledger`
  ADD PRIMARY KEY (`ledger_id`),
  ADD KEY `ix_ledger_user_time` (`user_id`,`created_at`),
  ADD KEY `fk_ledger_currency` (`currency_id`);

--
-- Indexes for table `daily_fom_fishing_runs`
--
ALTER TABLE `daily_fom_fishing_runs`
  ADD PRIMARY KEY (`user_id`,`run_date`),
  ADD KEY `ix_daily_fom_fishing_item` (`caught_item_id`);

--
-- Indexes for table `daily_sudoku_runs`
--
ALTER TABLE `daily_sudoku_runs`
  ADD PRIMARY KEY (`user_id`,`run_date`);

--
-- Indexes for table `elements`
--
ALTER TABLE `elements`
  ADD PRIMARY KEY (`element_id`),
  ADD UNIQUE KEY `uq_elements_name` (`element_name`);

--
-- Indexes for table `element_calc`
--
ALTER TABLE `element_calc`
  ADD PRIMARY KEY (`element_id`,`target_element_id`),
  ADD KEY `ix_element_calc_target` (`target_element_id`);

--
-- Indexes for table `fairy_fountain_visits`
--
ALTER TABLE `fairy_fountain_visits`
  ADD PRIMARY KEY (`user_id`,`visit_date`);

--
-- Indexes for table `food_preferences`
--
ALTER TABLE `food_preferences`
  ADD PRIMARY KEY (`food_pref_id`),
  ADD UNIQUE KEY `uq_food_pref_species_item` (`species_id`,`item_id`),
  ADD KEY `fk_food_pref_item` (`item_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `ix_items_name` (`item_name`),
  ADD KEY `fk_items_rarity` (`rarity_id`),
  ADD KEY `fk_items_category` (`category_id`);
ALTER TABLE `items` ADD FULLTEXT KEY `ft_items_name_desc` (`item_name`,`item_description`);

--
-- Indexes for table `item_categories`
--
ALTER TABLE `item_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `uq_category_name` (`category_name`);

--
-- Indexes for table `item_instances`
--
ALTER TABLE `item_instances`
  ADD PRIMARY KEY (`instance_id`),
  ADD KEY `ix_iteminst_owner` (`owner_user_id`),
  ADD KEY `fk_iteminst_item` (`item_id`);

--
-- Indexes for table `item_rarities`
--
ALTER TABLE `item_rarities`
  ADD PRIMARY KEY (`rarity_id`),
  ADD UNIQUE KEY `uq_rarity_name` (`rarity_name`);

--
-- Indexes for table `moves`
--
ALTER TABLE `moves`
  ADD PRIMARY KEY (`move_id`),
  ADD UNIQUE KEY `uq_moves_key` (`move_key`),
  ADD UNIQUE KEY `uq_moves_name` (`move_name`),
  ADD KEY `ix_moves_element` (`element_id`),
  ADD KEY `ix_moves_category` (`category`),
  ADD KEY `ix_moves_power` (`power`);

--
-- Indexes for table `npc_trainers`
--
ALTER TABLE `npc_trainers`
  ADD PRIMARY KEY (`trainer_id`);

--
-- Indexes for table `pet_colors`
--
ALTER TABLE `pet_colors`
  ADD PRIMARY KEY (`color_id`),
  ADD UNIQUE KEY `uq_color_name` (`color_name`);

--
-- Indexes for table `pet_cosmetics`
--
ALTER TABLE `pet_cosmetics`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `pet_equipment`
--
ALTER TABLE `pet_equipment`
  ADD PRIMARY KEY (`pet_instance_id`,`slot`),
  ADD KEY `fk_pe_inst` (`item_instance_id`);

--
-- Indexes for table `pet_instances`
--
ALTER TABLE `pet_instances`
  ADD PRIMARY KEY (`pet_instance_id`),
  ADD KEY `ix_pets_owner` (`owner_user_id`),
  ADD KEY `fk_petinst_species` (`species_id`),
  ADD KEY `fk_petinst_color` (`color_id`);

--
-- Indexes for table `pet_like_city`
--
ALTER TABLE `pet_like_city`
  ADD PRIMARY KEY (`PLCid`),
  ADD UNIQUE KEY `uq_pet_country` (`pet_id`,`country_id`),
  ADD KEY `fk_plc_country` (`country_id`);

--
-- Indexes for table `pet_species`
--
ALTER TABLE `pet_species`
  ADD PRIMARY KEY (`species_id`),
  ADD UNIQUE KEY `uq_species_name` (`species_name`),
  ADD KEY `fk_species_region` (`region_id`);

--
-- Indexes for table `picnic_tree_items`
--
ALTER TABLE `picnic_tree_items`
  ADD PRIMARY KEY (`picnic_item_id`),
  ADD UNIQUE KEY `uq_picnic_item` (`item_id`);

--
-- Indexes for table `player_unlocked_species`
--
ALTER TABLE `player_unlocked_species`
  ADD PRIMARY KEY (`entryId`),
  ADD UNIQUE KEY `uq_player_species_unlock` (`player_id`,`unlocked_species_id`),
  ADD KEY `ix_player_unlocked_player` (`player_id`),
  ADD KEY `ix_player_unlocked_species` (`unlocked_species_id`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`region_id`),
  ADD UNIQUE KEY `uq_region_name` (`region_name`);

--
-- Indexes for table `rsc_wheel_spins`
--
ALTER TABLE `rsc_wheel_spins`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`shop_id`),
  ADD KEY `ix_shops_region` (`region_id`);

--
-- Indexes for table `shop_inventory`
--
ALTER TABLE `shop_inventory`
  ADD PRIMARY KEY (`shop_id`,`item_id`),
  ADD KEY `fk_shopinv_item` (`item_id`);

--
-- Indexes for table `shop_transactions`
--
ALTER TABLE `shop_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `ix_shop_tx_user_time` (`user_id`,`created_at`),
  ADD KEY `fk_shoptx_shop` (`shop_id`),
  ADD KEY `fk_shoptx_item` (`item_id`),
  ADD KEY `fk_shoptx_currency` (`currency_id`);

--
-- Indexes for table `species_elements`
--
ALTER TABLE `species_elements`
  ADD PRIMARY KEY (`species_id`,`element_id`),
  ADD KEY `ix_species_elements_element` (`element_id`);

--
-- Indexes for table `trainer_roster`
--
ALTER TABLE `trainer_roster`
  ADD PRIMARY KEY (`trainer_id`,`pet_instance_id`),
  ADD UNIQUE KEY `uq_trainer_roster_position` (`trainer_id`,`roster_position`),
  ADD KEY `ix_trainer_roster_pet` (`pet_instance_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indexes for table `user_balances`
--
ALTER TABLE `user_balances`
  ADD PRIMARY KEY (`user_id`,`currency_id`),
  ADD KEY `fk_bal_currency` (`currency_id`);

--
-- Indexes for table `user_bank`
--
ALTER TABLE `user_bank`
  ADD PRIMARY KEY (`user_id`,`currency_id`);

--
-- Indexes for table `user_direct_messages`
--
ALTER TABLE `user_direct_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `ix_dm_participants` (`sender_id`,`recipient_id`,`created_at`),
  ADD KEY `ix_dm_recipient_sender` (`recipient_id`,`sender_id`,`created_at`);

--
-- Indexes for table `user_friends`
--
ALTER TABLE `user_friends`
  ADD PRIMARY KEY (`connection_id`),
  ADD UNIQUE KEY `uq_user_friend` (`user_id`,`friend_id`),
  ADD KEY `fk_friend_friend` (`friend_id`);

--
-- Indexes for table `user_inventory`
--
ALTER TABLE `user_inventory`
  ADD PRIMARY KEY (`user_id`,`item_id`),
  ADD KEY `ix_inv_user` (`user_id`),
  ADD KEY `fk_inv_item` (`item_id`);

--
-- Indexes for table `user_map_unlocks`
--
ALTER TABLE `user_map_unlocks`
  ADD PRIMARY KEY (`user_id`,`map_key`),
  ADD KEY `ix_map_unlock_map_key` (`map_key`);

--
-- Indexes for table `wheel_of_fate_spins`
--
ALTER TABLE `wheel_of_fate_spins`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abandoned_pets`
--
ALTER TABLE `abandoned_pets`
  MODIFY `ap_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `currency_id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `currency_ledger`
--
ALTER TABLE `currency_ledger`
  MODIFY `ledger_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=341;

--
-- AUTO_INCREMENT for table `elements`
--
ALTER TABLE `elements`
  MODIFY `element_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `food_preferences`
--
ALTER TABLE `food_preferences`
  MODIFY `food_pref_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT for table `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `category_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `item_instances`
--
ALTER TABLE `item_instances`
  MODIFY `instance_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `item_rarities`
--
ALTER TABLE `item_rarities`
  MODIFY `rarity_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `moves`
--
ALTER TABLE `moves`
  MODIFY `move_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `npc_trainers`
--
ALTER TABLE `npc_trainers`
  MODIFY `trainer_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pet_colors`
--
ALTER TABLE `pet_colors`
  MODIFY `color_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `pet_cosmetics`
--
ALTER TABLE `pet_cosmetics`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pet_instances`
--
ALTER TABLE `pet_instances`
  MODIFY `pet_instance_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `pet_like_city`
--
ALTER TABLE `pet_like_city`
  MODIFY `PLCid` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1441;

--
-- AUTO_INCREMENT for table `pet_species`
--
ALTER TABLE `pet_species`
  MODIFY `species_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=395;

--
-- AUTO_INCREMENT for table `picnic_tree_items`
--
ALTER TABLE `picnic_tree_items`
  MODIFY `picnic_item_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_unlocked_species`
--
ALTER TABLE `player_unlocked_species`
  MODIFY `entryId` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `region_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `shops`
--
ALTER TABLE `shops`
  MODIFY `shop_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `shop_transactions`
--
ALTER TABLE `shop_transactions`
  MODIFY `transaction_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_direct_messages`
--
ALTER TABLE `user_direct_messages`
  MODIFY `message_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_friends`
--
ALTER TABLE `user_friends`
  MODIFY `connection_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `abandoned_pets`
--
ALTER TABLE `abandoned_pets`
  ADD CONSTRAINT `fk_abandoned_old_player` FOREIGN KEY (`old_player_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_abandoned_pet` FOREIGN KEY (`creature_id`) REFERENCES `pet_instances` (`pet_instance_id`) ON DELETE CASCADE;

--
-- Constraints for table `creature_name_votes`
--
ALTER TABLE `creature_name_votes`
  ADD CONSTRAINT `fk_namevote_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `currency_ledger`
--
ALTER TABLE `currency_ledger`
  ADD CONSTRAINT `fk_ledger_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`currency_id`),
  ADD CONSTRAINT `fk_ledger_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `daily_fom_fishing_runs`
--
ALTER TABLE `daily_fom_fishing_runs`
  ADD CONSTRAINT `fk_daily_fom_fishing_item` FOREIGN KEY (`caught_item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `fk_daily_fom_fishing_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `daily_sudoku_runs`
--
ALTER TABLE `daily_sudoku_runs`
  ADD CONSTRAINT `fk_daily_sudoku_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `food_preferences`
--
ALTER TABLE `food_preferences`
  ADD CONSTRAINT `fk_food_pref_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_food_pref_species` FOREIGN KEY (`species_id`) REFERENCES `pet_species` (`species_id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_category` FOREIGN KEY (`category_id`) REFERENCES `item_categories` (`category_id`),
  ADD CONSTRAINT `fk_items_rarity` FOREIGN KEY (`rarity_id`) REFERENCES `item_rarities` (`rarity_id`);

--
-- Constraints for table `item_instances`
--
ALTER TABLE `item_instances`
  ADD CONSTRAINT `fk_iteminst_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `fk_iteminst_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `moves`
--
ALTER TABLE `moves`
  ADD CONSTRAINT `fk_moves_element` FOREIGN KEY (`element_id`) REFERENCES `elements` (`element_id`) ON UPDATE CASCADE;

--
-- Constraints for table `pet_equipment`
--
ALTER TABLE `pet_equipment`
  ADD CONSTRAINT `fk_pe_inst` FOREIGN KEY (`item_instance_id`) REFERENCES `item_instances` (`instance_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pe_pet` FOREIGN KEY (`pet_instance_id`) REFERENCES `pet_instances` (`pet_instance_id`) ON DELETE CASCADE;

--
-- Constraints for table `pet_instances`
--
ALTER TABLE `pet_instances`
  ADD CONSTRAINT `fk_petinst_color` FOREIGN KEY (`color_id`) REFERENCES `pet_colors` (`color_id`),
  ADD CONSTRAINT `fk_petinst_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_petinst_species` FOREIGN KEY (`species_id`) REFERENCES `pet_species` (`species_id`);

--
-- Constraints for table `pet_like_city`
--
ALTER TABLE `pet_like_city`
  ADD CONSTRAINT `fk_plc_country` FOREIGN KEY (`country_id`) REFERENCES `regions` (`region_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_plc_pet` FOREIGN KEY (`pet_id`) REFERENCES `pet_species` (`species_id`) ON DELETE CASCADE;

--
-- Constraints for table `pet_species`
--
ALTER TABLE `pet_species`
  ADD CONSTRAINT `fk_species_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`region_id`);

--
-- Constraints for table `picnic_tree_items`
--
ALTER TABLE `picnic_tree_items`
  ADD CONSTRAINT `fk_picnic_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `player_unlocked_species`
--
ALTER TABLE `player_unlocked_species`
  ADD CONSTRAINT `fk_player_unlocked_player` FOREIGN KEY (`player_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_player_unlocked_species` FOREIGN KEY (`unlocked_species_id`) REFERENCES `pet_species` (`species_id`) ON DELETE CASCADE;

--
-- Constraints for table `shops`
--
ALTER TABLE `shops`
  ADD CONSTRAINT `fk_shop_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`region_id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_inventory`
--
ALTER TABLE `shop_inventory`
  ADD CONSTRAINT `fk_shopinv_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `fk_shopinv_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_transactions`
--
ALTER TABLE `shop_transactions`
  ADD CONSTRAINT `fk_shoptx_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`currency_id`),
  ADD CONSTRAINT `fk_shoptx_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `fk_shoptx_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`),
  ADD CONSTRAINT `fk_shoptx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_balances`
--
ALTER TABLE `user_balances`
  ADD CONSTRAINT `fk_bal_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`currency_id`),
  ADD CONSTRAINT `fk_bal_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_direct_messages`
--
ALTER TABLE `user_direct_messages`
  ADD CONSTRAINT `fk_dm_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dm_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_friends`
--
ALTER TABLE `user_friends`
  ADD CONSTRAINT `fk_friend_friend` FOREIGN KEY (`friend_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_friend_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_inventory`
--
ALTER TABLE `user_inventory`
  ADD CONSTRAINT `fk_inv_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `fk_inv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_map_unlocks`
--
ALTER TABLE `user_map_unlocks`
  ADD CONSTRAINT `fk_map_unlock_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `wheel_of_fate_spins`
--
ALTER TABLE `wheel_of_fate_spins`
  ADD CONSTRAINT `fk_wheel_spin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
