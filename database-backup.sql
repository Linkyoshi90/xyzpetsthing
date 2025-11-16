-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 15, 2025 at 09:53 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xyzpetsthing`
--

-- --------------------------------------------------------

--
-- Table structure for table `creature_name_votes`
--

DROP TABLE IF EXISTS `creature_name_votes`;
CREATE TABLE IF NOT EXISTS `creature_name_votes` (
  `user_id` bigint UNSIGNED NOT NULL,
  `selection_json` json NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
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

DROP TABLE IF EXISTS `currencies`;
CREATE TABLE IF NOT EXISTS `currencies` (
  `currency_id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency_code` varchar(16) NOT NULL,
  `display_name` varchar(32) NOT NULL,
  PRIMARY KEY (`currency_id`),
  UNIQUE KEY `uq_currency_code` (`currency_code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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

DROP TABLE IF EXISTS `currency_ledger`;
CREATE TABLE IF NOT EXISTS `currency_ledger` (
  `ledger_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `currency_id` tinyint UNSIGNED NOT NULL,
  `amount_delta` decimal(14,2) NOT NULL,
  `reason` varchar(64) NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ledger_id`),
  KEY `ix_ledger_user_time` (`user_id`,`created_at`),
  KEY `fk_ledger_currency` (`currency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(88, 11, 1, 48202.24, 'bank_interest', '{\"rate\": 0.025}', '2025-11-15 15:00:46');

-- --------------------------------------------------------

--
-- Table structure for table `daily_sudoku_runs`
--

DROP TABLE IF EXISTS `daily_sudoku_runs`;
CREATE TABLE IF NOT EXISTS `daily_sudoku_runs` (
  `user_id` bigint UNSIGNED NOT NULL,
  `run_date` date NOT NULL,
  `difficulty_percent` tinyint UNSIGNED NOT NULL,
  `base_score` int UNSIGNED NOT NULL DEFAULT '0',
  `final_score` int UNSIGNED NOT NULL DEFAULT '0',
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`,`run_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `daily_sudoku_runs`
--

INSERT INTO `daily_sudoku_runs` (`user_id`, `run_date`, `difficulty_percent`, `base_score`, `final_score`, `completed_at`) VALUES
(11, '2025-10-13', 65, 0, 0, NULL),
(11, '2025-10-19', 84, 0, 0, NULL),
(11, '2025-11-12', 54, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE IF NOT EXISTS `items` (
  `item_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `item_description` text,
  `base_price` decimal(12,2) DEFAULT NULL,
  `rarity_id` smallint UNSIGNED DEFAULT NULL,
  `category_id` smallint UNSIGNED DEFAULT NULL,
  `max_stack` int UNSIGNED NOT NULL DEFAULT '99',
  `tradable` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `replenish` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`item_id`),
  KEY `ix_items_name` (`item_name`),
  KEY `fk_items_rarity` (`rarity_id`),
  KEY `fk_items_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `item_description`, `base_price`, `rarity_id`, `category_id`, `max_stack`, `tradable`, `created_at`, `replenish`) VALUES
(1, 'Berry', 'A juicy forest berry. Restores a little HP.', 5.00, 1, 1, 99, 1, '2025-09-02 20:32:42', 5),
(2, 'Healing Potion', 'Restores 50 HP.', 50.00, 2, 3, 20, 1, '2025-09-02 20:32:42', 50),
(3, 'Iron Sword', 'A sturdy beginner blade.', 200.00, 2, 2, 1, 1, '2025-09-02 20:32:42', 1),
(4, 'Wizard Hat', 'Stylish and pointy. Boosts magic.', 500.00, 3, 4, 1, 1, '2025-09-02 20:32:42', 1),
(5, 'Mana Elixir', 'Restores 40 MP.', 75.00, 3, 3, 20, 1, '2025-09-02 20:32:42', 40),
(6, 'Red Paint', 'Paints your creature red.', 50000.00, 3, 5, 1, 1, '2025-09-02 20:32:42', 1),
(7, 'Blue Paint', 'Paints your creature blue.', 50000.00, 3, 5, 1, 1, '2025-09-02 20:32:42', 1),
(8, 'Yellow Paint', 'Paints your creature yellow.', 50000.00, 3, 5, 1, 1, '2025-09-02 20:32:42', 1),
(9, 'Green Paint', 'Paints your creature green.', 50000.00, 3, 5, 1, 1, '2025-09-02 20:32:42', 1),
(10, 'Purple Paint', 'Paints your creature purple.', 50000.00, 3, 5, 1, 1, '2025-09-02 20:32:42', 1),
(11, 'Black Paint', 'Paints your creature black.', 1000000.00, 4, 5, 1, 1, '2025-09-02 20:32:42', 1),
(12, 'Real Paintbrush', 'Paints your creature realistic.', 2005000.00, 5, 5, 1, 1, '2025-09-02 20:32:42', 1),
(13, 'Crystal Shard', 'A rare sparkling material.', 1200.00, 4, 6, 99, 1, '2025-09-02 20:32:42', 1),
(14, 'Cheese Pizza with Salami', 'A pizza drenched in cheese sauce and speckled with salami', 20.00, 2, 1, 99, 1, '2025-11-04 13:57:35', 1),
(15, 'Pizza Prosciutto', 'A nice old classic. Cheese strips melted evenly across the dough that has been prepared with tomato sauce, topped with the best prosciutto this world has to offer for less than the minimum price of this pizza. The pizza is cooked in an oven made of marble, which gives it an astounding quality: it\'s the same as normal store-bought pizza, which is pretty astounding.', 25.00, 2, 1, 99, 1, '2025-11-04 15:15:46', 1);

-- --------------------------------------------------------

--
-- Table structure for table `item_categories`
--

DROP TABLE IF EXISTS `item_categories`;
CREATE TABLE IF NOT EXISTS `item_categories` (
  `category_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name` varchar(40) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `uq_category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `item_categories`
--

INSERT INTO `item_categories` (`category_id`, `category_name`) VALUES
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

DROP TABLE IF EXISTS `item_instances`;
CREATE TABLE IF NOT EXISTS `item_instances` (
  `instance_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` bigint UNSIGNED NOT NULL,
  `owner_user_id` bigint UNSIGNED NOT NULL,
  `durability` int DEFAULT NULL,
  `bound_to_user` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`instance_id`),
  KEY `ix_iteminst_owner` (`owner_user_id`),
  KEY `fk_iteminst_item` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `item_instances`
--

INSERT INTO `item_instances` (`instance_id`, `item_id`, `owner_user_id`, `durability`, `bound_to_user`, `created_at`) VALUES
(1, 3, 1, 100, 0, '2025-09-02 20:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `item_rarities`
--

DROP TABLE IF EXISTS `item_rarities`;
CREATE TABLE IF NOT EXISTS `item_rarities` (
  `rarity_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `rarity_name` varchar(40) NOT NULL,
  `rarity_rank` smallint UNSIGNED NOT NULL,
  PRIMARY KEY (`rarity_id`),
  UNIQUE KEY `uq_rarity_name` (`rarity_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Table structure for table `pet_colors`
--

DROP TABLE IF EXISTS `pet_colors`;
CREATE TABLE IF NOT EXISTS `pet_colors` (
  `color_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `color_name` varchar(50) NOT NULL,
  PRIMARY KEY (`color_id`),
  UNIQUE KEY `uq_color_name` (`color_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pet_colors`
--

INSERT INTO `pet_colors` (`color_id`, `color_name`) VALUES
(6, 'Black'),
(2, 'Blue'),
(3, 'Green'),
(5, 'Purple'),
(7, 'Realistic'),
(1, 'Red'),
(4, 'Yellow');

-- --------------------------------------------------------

--
-- Table structure for table `pet_equipment`
--

DROP TABLE IF EXISTS `pet_equipment`;
CREATE TABLE IF NOT EXISTS `pet_equipment` (
  `pet_instance_id` bigint UNSIGNED NOT NULL,
  `slot` varchar(32) NOT NULL,
  `item_instance_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`pet_instance_id`,`slot`),
  KEY `fk_pe_inst` (`item_instance_id`)
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

DROP TABLE IF EXISTS `pet_instances`;
CREATE TABLE IF NOT EXISTS `pet_instances` (
  `pet_instance_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `gender` char(1) NOT NULL DEFAULT 'U',
  `hunger` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `happiness` tinyint UNSIGNED NOT NULL DEFAULT '50',
  `intelligence` int UNSIGNED NOT NULL DEFAULT '0',
  `sickness` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pet_instance_id`),
  KEY `ix_pets_owner` (`owner_user_id`),
  KEY `fk_petinst_species` (`species_id`),
  KEY `fk_petinst_color` (`color_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pet_instances`
--

INSERT INTO `pet_instances` (`pet_instance_id`, `owner_user_id`, `species_id`, `nickname`, `color_id`, `level`, `experience`, `hp_current`, `hp_max`, `atk`, `def`, `initiative`, `gender`, `hunger`, `happiness`, `intelligence`, `sickness`, `created_at`) VALUES
(1, 1, 112, 'Ember', 1, 5, 120, 40, NULL, 12, 7, 3, 'U', 0, 50, 0, 0, '2025-09-02 20:37:52'),
(2, 2, 196, 'Splash', 2, 3, 40, 35, NULL, 8, 12, 8, 'U', 0, 50, 0, 0, '2025-09-02 20:37:52'),
(3, 3, 195, 'Gale', 3, 7, 300, 42, NULL, 9, 15, 2, 'U', 0, 50, 0, 0, '2025-09-02 20:37:52'),
(5, 11, 116, 'Wilhelmina', 3, 1, 0, 0, 50, 6, 7, 5, 'F', 0, 46, 1, 1, '2025-09-10 10:01:47'),
(6, 11, 58, 'Klemmstein', 3, 1, 0, 0, 50, 8, 5, 7, 'F', 0, 46, 4, 1, '2025-09-10 11:46:24'),
(7, 11, 59, 'PurpiNurpi', 5, 1, 0, 0, 50, 6, 7, 5, 'f', 0, 46, 0, 1, '2025-09-10 12:57:36');

-- --------------------------------------------------------

--
-- Table structure for table `pet_species`
--

DROP TABLE IF EXISTS `pet_species`;
CREATE TABLE IF NOT EXISTS `pet_species` (
  `species_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `species_name` varchar(100) NOT NULL,
  `region_id` smallint UNSIGNED DEFAULT NULL,
  `base_hp` int NOT NULL,
  `base_atk` int NOT NULL,
  `base_def` int NOT NULL,
  `base_init` int NOT NULL,
  PRIMARY KEY (`species_id`),
  UNIQUE KEY `uq_species_name` (`species_name`),
  KEY `fk_species_region` (`region_id`)
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pet_species`
--

INSERT INTO `pet_species` (`species_id`, `species_name`, `region_id`, `base_hp`, `base_atk`, `base_def`, `base_init`) VALUES
(58, 'Lamia', 1, 8, 8, 5, 7),
(59, 'Centaur', 1, 12, 6, 7, 5),
(112, 'Kraken', 2, 12, 6, 7, 5),
(113, 'Ratatoskr', 2, 12, 6, 7, 5),
(114, 'Banshee', 3, 12, 6, 7, 5),
(115, 'Dullahan', 3, 12, 6, 7, 5),
(116, 'Will-o-Wisp', 3, 12, 6, 7, 5),
(117, 'Kelpie', 3, 12, 6, 7, 5),
(118, 'Angel', 4, 12, 6, 7, 5),
(164, 'Demon', 4, 12, 6, 7, 5),
(165, 'Succubus', 4, 12, 6, 7, 5),
(166, 'Leshy', 5, 12, 6, 7, 5),
(167, 'Vodyanoy', 5, 12, 6, 7, 5),
(168, 'Lich', 6, 12, 6, 7, 5),
(169, 'Jack-o-Lantern', 6, 12, 6, 7, 5),
(170, 'Thunderbird', 7, 12, 6, 7, 5),
(171, 'Horned Serpent Uktena', 7, 12, 6, 7, 5),
(172, 'Jiang-Shi', 8, 12, 6, 7, 5),
(173, 'Vermillion Bird', 8, 12, 6, 7, 5),
(174, 'Gandharva', 9, 12, 6, 7, 5),
(175, 'Naga', 9, 12, 6, 7, 5),
(176, 'Spider-Crab', 10, 12, 6, 7, 5),
(177, 'Kitsune', 10, 12, 6, 7, 5),
(178, 'Yuki-Onna', 10, 12, 6, 7, 5),
(179, 'La Llorona', 11, 12, 6, 7, 5),
(180, 'Chupacabra', 11, 12, 6, 7, 5),
(181, 'Charro Negro', 11, 12, 6, 7, 5),
(182, 'Quetzalcoatl', 12, 12, 6, 7, 5),
(183, 'Ahuizotl', 13, 12, 6, 7, 5),
(184, 'Cipactli', 13, 12, 6, 7, 5),
(185, 'Ocelot', 13, 12, 6, 7, 5),
(186, 'Azureus', 14, 12, 6, 7, 5),
(187, 'Tapir', 14, 12, 6, 7, 5),
(188, 'Crab man', 15, 12, 6, 7, 5),
(189, 'Taniwha', 15, 12, 6, 7, 5),
(190, 'Genie', 16, 12, 6, 7, 5),
(191, 'Bahamut', 16, 12, 6, 7, 5),
(192, 'Girtablilu', 17, 12, 6, 7, 5),
(193, 'Lamassu', 17, 12, 6, 7, 5),
(194, 'Golem', 18, 12, 6, 7, 5),
(195, 'Dolphin', 18, 12, 6, 7, 5),
(196, 'Anubis', 19, 12, 6, 7, 5),
(197, 'Wadjet', 19, 12, 6, 7, 5),
(228, 'Amarok', 20, 12, 6, 7, 5),
(229, 'Polar Bear', 20, 12, 6, 7, 5),
(230, 'Drop Bear', 21, 12, 6, 7, 5),
(231, 'Min-Min Lights', 21, 12, 6, 7, 5),
(232, 'Bunyip', 22, 12, 6, 7, 5),
(233, 'Rainbow Serpent', 22, 12, 6, 7, 5),
(234, 'Curupira', 25, 12, 6, 7, 5),
(235, 'Capybara', 25, 12, 6, 7, 5),
(236, 'Fishman', 26, 12, 6, 7, 5),
(237, 'Argentinosaurus', 26, 12, 6, 7, 5),
(238, 'Amaru', 26, 12, 6, 7, 5),
(239, 'Wayob', 22, 12, 6, 7, 5);

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
CREATE TABLE IF NOT EXISTS `regions` (
  `region_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `region_name` varchar(100) NOT NULL,
  PRIMARY KEY (`region_id`),
  UNIQUE KEY `uq_region_name` (`region_name`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`region_id`, `region_name`) VALUES
(21, 'AAAAAAAAAAAAAAAAAAAA'),
(1, 'Aegia Aeterna'),
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
-- Table structure for table `shops`
--

DROP TABLE IF EXISTS `shops`;
CREATE TABLE IF NOT EXISTS `shops` (
  `shop_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(100) NOT NULL,
  `region_id` smallint UNSIGNED NOT NULL,
  `is_npc` tinyint(1) NOT NULL DEFAULT '1',
  `restock_every_minutes` int UNSIGNED DEFAULT NULL,
  `last_restok_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`shop_id`),
  KEY `ix_shops_region` (`region_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shops`
--

INSERT INTO `shops` (`shop_id`, `shop_name`, `region_id`, `is_npc`, `restock_every_minutes`, `last_restok_at`) VALUES
(1, 'Eternal General Store', 1, 1, 60, NULL),
(2, 'Crescent Bazaar', 2, 1, 90, NULL),
(3, 'Rodian Emporium', 3, 1, 120, NULL),
(4, 'Pizzeria Sol Invicta', 1, 1, 60, NULL),
(5, 'Crescent Bazaar', 2, 1, 90, NULL),
(6, 'Rodian Emporium', 3, 1, 120, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shop_inventory`
--

DROP TABLE IF EXISTS `shop_inventory`;
CREATE TABLE IF NOT EXISTS `shop_inventory` (
  `shop_id` int UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `stock` int DEFAULT NULL,
  PRIMARY KEY (`shop_id`,`item_id`),
  KEY `fk_shopinv_item` (`item_id`)
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
(4, 14, 25.00, 14),
(4, 15, 30.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `shop_transactions`
--

DROP TABLE IF EXISTS `shop_transactions`;
CREATE TABLE IF NOT EXISTS `shop_transactions` (
  `transaction_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` int UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `currency_id` tinyint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  KEY `ix_shop_tx_user_time` (`user_id`,`created_at`),
  KEY `fk_shoptx_shop` (`shop_id`),
  KEY `fk_shoptx_item` (`item_id`),
  KEY `fk_shoptx_currency` (`currency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shop_transactions`
--

INSERT INTO `shop_transactions` (`transaction_id`, `shop_id`, `user_id`, `item_id`, `quantity`, `unit_price`, `currency_id`, `created_at`) VALUES
(1, 1, 1, 3, 1, 220.00, 1, '2025-09-02 20:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password_hash` varbinary(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(11, 'Bummsibuddy', 'Get@get.com', 0x243279243130246136304235526c79497a6f7a6d6148794d557a45797531466a50544a71786861656862626d697270527737537447355074394b386d, '2025-09-10 09:56:58', '2025-09-10 09:56:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_balances`
--

DROP TABLE IF EXISTS `user_balances`;
CREATE TABLE IF NOT EXISTS `user_balances` (
  `user_id` bigint UNSIGNED NOT NULL,
  `currency_id` tinyint UNSIGNED NOT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`user_id`,`currency_id`),
  KEY `fk_bal_currency` (`currency_id`)
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
(7, 1, 3000.00),
(8, 1, 3000.00),
(9, 1, 3000.00),
(10, 1, 3000.00),
(11, 1, 300.01);

-- --------------------------------------------------------

--
-- Table structure for table `user_bank`
--

DROP TABLE IF EXISTS `user_bank`;
CREATE TABLE IF NOT EXISTS `user_bank` (
  `user_id` bigint UNSIGNED NOT NULL,
  `currency_id` tinyint UNSIGNED NOT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `interest` decimal(14,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`user_id`,`currency_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_bank`
--

INSERT INTO `user_bank` (`user_id`, `currency_id`, `balance`, `interest`) VALUES
(11, 1, 1976291.78, 20251115.00);

-- --------------------------------------------------------

--
-- Table structure for table `user_direct_messages`
--

DROP TABLE IF EXISTS `user_direct_messages`;
CREATE TABLE IF NOT EXISTS `user_direct_messages` (
  `message_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id` bigint UNSIGNED NOT NULL,
  `recipient_id` bigint UNSIGNED NOT NULL,
  `message_ciphertext` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `ix_dm_participants` (`sender_id`,`recipient_id`,`created_at`),
  KEY `ix_dm_recipient_sender` (`recipient_id`,`sender_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_friends`
--

DROP TABLE IF EXISTS `user_friends`;
CREATE TABLE IF NOT EXISTS `user_friends` (
  `connection_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `friend_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`connection_id`),
  UNIQUE KEY `uq_user_friend` (`user_id`,`friend_id`),
  KEY `fk_friend_friend` (`friend_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_friends`
--

INSERT INTO `user_friends` (`connection_id`, `user_id`, `friend_id`) VALUES
(1, 11, 5);

-- --------------------------------------------------------

--
-- Table structure for table `user_inventory`
--

DROP TABLE IF EXISTS `user_inventory`;
CREATE TABLE IF NOT EXISTS `user_inventory` (
  `user_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL,
  `acquired_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`item_id`),
  KEY `ix_inv_user` (`user_id`),
  KEY `fk_inv_item` (`item_id`)
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
(10, 1, 1, '2025-09-10 09:17:48'),
(10, 2, 2, '2025-09-10 09:17:48'),
(11, 2, 9, '2025-09-10 10:01:47'),
(11, 3, 2, '2025-09-16 15:14:58'),
(11, 4, 3, '2025-09-16 15:15:00'),
(11, 6, 1, '2025-09-16 15:15:03'),
(11, 14, 1, '2025-11-15 18:37:57'),
(11, 15, 13, '2025-11-15 18:37:57');

-- --------------------------------------------------------

--
-- Table structure for table `wheel_of_fate_spins`
--

DROP TABLE IF EXISTS `wheel_of_fate_spins`;
CREATE TABLE IF NOT EXISTS `wheel_of_fate_spins` (
  `user_id` bigint UNSIGNED NOT NULL,
  `last_spin_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `wheel_of_fate_spins`
--

INSERT INTO `wheel_of_fate_spins` (`user_id`, `last_spin_at`) VALUES
(11, '2025-09-22 22:08:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items` ADD FULLTEXT KEY `ft_items_name_desc` (`item_name`,`item_description`);

--
-- Constraints for dumped tables
--

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
-- Constraints for table `daily_sudoku_runs`
--
ALTER TABLE `daily_sudoku_runs`
  ADD CONSTRAINT `fk_daily_sudoku_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
-- Constraints for table `pet_species`
--
ALTER TABLE `pet_species`
  ADD CONSTRAINT `fk_species_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`region_id`);

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
-- Constraints for table `wheel_of_fate_spins`
--
ALTER TABLE `wheel_of_fate_spins`
  ADD CONSTRAINT `fk_wheel_spin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
