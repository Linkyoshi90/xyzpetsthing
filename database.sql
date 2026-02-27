-- Recommended session settings
SET NAMES utf8mb4 COLLATE utf8mb4_0900_ai_ci;
SET sql_safe_updates = 0;

-- Use your DB
CREATE DATABASE IF NOT EXISTS xyzpetsthing CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE xyzpetsthing;

-- Users
CREATE TABLE IF NOT EXISTS users (
  user_id       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(32)  NOT NULL,
  email         VARCHAR(254) NOT NULL,
  password_hash VARBINARY(255) NOT NULL, -- bcrypt/argon2 hash bytes
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

-- Remember-me tokens
CREATE TABLE IF NOT EXISTS user_remember_tokens (
  token_id   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    BIGINT UNSIGNED NOT NULL,
  selector   CHAR(18) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (token_id),
  UNIQUE KEY uq_remember_selector (selector),
  KEY ix_remember_user (user_id),
  KEY ix_remember_expires (expires_at),
  CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Currency catalog (so you can add COIN/CASH/etc.)
CREATE TABLE IF NOT EXISTS currencies (
  currency_id   TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  currency_code VARCHAR(16) NOT NULL,
  display_name  VARCHAR(32) NOT NULL,
  PRIMARY KEY (currency_id),
  UNIQUE KEY uq_currency_code (currency_code)
) ENGINE=InnoDB;

-- Wheel of Fate spin tracking
CREATE TABLE IF NOT EXISTS wheel_of_fate_spins (
  user_id      BIGINT UNSIGNED NOT NULL,
  last_spin_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_wheel_spin_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `rsc_wheel_spins`;
CREATE TABLE IF NOT EXISTS `rsc_wheel_spins` (
  `user_id` bigint UNSIGNED NOT NULL,
  `last_spin_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS daily_sudoku_runs (
  user_id            BIGINT UNSIGNED NOT NULL,
  run_date           DATE NOT NULL,
  difficulty_percent TINYINT UNSIGNED NOT NULL,
  base_score         INT UNSIGNED NOT NULL DEFAULT 0,
  final_score        INT UNSIGNED NOT NULL DEFAULT 0,
  completed_at       DATETIME NULL,
  PRIMARY KEY (user_id, run_date),
  CONSTRAINT fk_daily_sudoku_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS daily_fom_fishing_runs (
  user_id         BIGINT UNSIGNED NOT NULL,
  run_date        DATE NOT NULL,
  caught_item_id  BIGINT UNSIGNED NULL,
  completed_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, run_date),
  KEY ix_daily_fom_fishing_item (caught_item_id),
  CONSTRAINT fk_daily_fom_fishing_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_daily_fom_fishing_item FOREIGN KEY (caught_item_id) REFERENCES items(item_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_friends (
  connection_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  friend_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (connection_id),
  UNIQUE KEY uq_user_friend (user_id, friend_id),
  CONSTRAINT fk_friend_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_friend_friend FOREIGN KEY (friend_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_direct_messages (
  message_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sender_id BIGINT UNSIGNED NOT NULL,
  recipient_id BIGINT UNSIGNED NOT NULL,
  message_ciphertext TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id),
  KEY ix_dm_participants (sender_id, recipient_id, created_at),
  KEY ix_dm_recipient_sender (recipient_id, sender_id, created_at),
  CONSTRAINT fk_dm_sender FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_dm_recipient FOREIGN KEY (recipient_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Per-user balances
CREATE TABLE IF NOT EXISTS user_balances (
  user_id     BIGINT UNSIGNED NOT NULL,
  currency_id TINYINT UNSIGNED NOT NULL,
  balance     DECIMAL(14,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id, currency_id),
  CONSTRAINT fk_bal_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_bal_currency FOREIGN KEY (currency_id) REFERENCES currencies(currency_id)
) ENGINE=InnoDB;

-- Per-user balances
CREATE TABLE IF NOT EXISTS user_bank (
  user_id     BIGINT UNSIGNED NOT NULL,
  currency_id TINYINT UNSIGNED NOT NULL,
  balance     DECIMAL(14,2) NOT NULL DEFAULT 0,
  interest    DECIMAL(14,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id, currency_id),
  CONSTRAINT fk_bank_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_bank_currency FOREIGN KEY (currency_id) REFERENCES currencies(currency_id)
) ENGINE=InnoDB;

-- Ledger for auditing economy
CREATE TABLE IF NOT EXISTS currency_ledger (
  ledger_id   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     BIGINT UNSIGNED NOT NULL,
  currency_id TINYINT UNSIGNED NOT NULL,
  amount_delta DECIMAL(14,2) NOT NULL,  -- +/- 
  reason      VARCHAR(64) NOT NULL,     -- 'battle_reward','shop_purchase',...
  metadata    JSON NULL,                -- context
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ledger_id),
  KEY ix_ledger_user_time (user_id, created_at),
  CONSTRAINT fk_ledger_user FOREIGN KEY (user_id) REFERENCES users(user_id),
  CONSTRAINT fk_ledger_currency FOREIGN KEY (currency_id) REFERENCES currencies(currency_id)
) ENGINE=InnoDB;

-- Item taxonomy
CREATE TABLE IF NOT EXISTS item_rarities (
  rarity_id    SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  rarity_name  VARCHAR(40) NOT NULL,
  rarity_rank  SMALLINT UNSIGNED NOT NULL,   -- renamed
  PRIMARY KEY (rarity_id),
  UNIQUE KEY uq_rarity_name (rarity_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS item_categories (
  category_id   SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_name VARCHAR(40) NOT NULL,
  PRIMARY KEY (category_id),
  UNIQUE KEY uq_category_name (category_name)
) ENGINE=InnoDB;

-- Items
CREATE TABLE IF NOT EXISTS items (
  item_id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_name         VARCHAR(100) NOT NULL,
  item_description  TEXT NULL,
  replenish         Int NOT NULL DEFAULT 1,
  base_price        DECIMAL(12,2) NULL,
  rarity_id         SMALLINT UNSIGNED NULL,
  category_id       SMALLINT UNSIGNED NULL,
  max_stack         INT UNSIGNED NOT NULL DEFAULT 99,
  tradable          TINYINT(1) NOT NULL DEFAULT 1,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (item_id),
  KEY ix_items_name (item_name),
  CONSTRAINT fk_items_rarity   FOREIGN KEY (rarity_id)  REFERENCES item_rarities(rarity_id),
  CONSTRAINT fk_items_category FOREIGN KEY (category_id) REFERENCES item_categories(category_id)
) ENGINE=InnoDB;

-- Optional: enable full-text search for items (MySQL/InnoDB supports this)
ALTER TABLE items ADD FULLTEXT KEY ft_items_name_desc (item_name, item_description);

-- Inventory (stackable)
CREATE TABLE IF NOT EXISTS user_inventory (
  user_id     BIGINT UNSIGNED NOT NULL,
  item_id     BIGINT UNSIGNED NOT NULL,
  quantity    INT UNSIGNED NOT NULL,
  acquired_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, item_id),
  KEY ix_inv_user (user_id),
  CONSTRAINT fk_inv_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_inv_item FOREIGN KEY (item_id) REFERENCES items(item_id)
) ENGINE=InnoDB;

-- Optional: unique item instances (durability/soulbound)
CREATE TABLE IF NOT EXISTS item_instances (
  instance_id   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_id       BIGINT UNSIGNED NOT NULL,
  owner_user_id BIGINT UNSIGNED NOT NULL,
  durability    INT NULL,
  bound_to_user TINYINT(1) NOT NULL DEFAULT 0,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (instance_id),
  KEY ix_iteminst_owner (owner_user_id),
  CONSTRAINT fk_iteminst_item  FOREIGN KEY (item_id) REFERENCES items(item_id),
  CONSTRAINT fk_iteminst_owner FOREIGN KEY (owner_user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Picnic Tree item pool
CREATE TABLE IF NOT EXISTS picnic_tree_items (
  picnic_item_id      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_id             BIGINT UNSIGNED NOT NULL,
  available_quantity  INT UNSIGNED NOT NULL DEFAULT 0,
  chance_percent      DECIMAL(5,2) NOT NULL DEFAULT 100.00,
  created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (picnic_item_id),
  UNIQUE KEY uq_picnic_item (item_id),
  CONSTRAINT fk_picnic_item FOREIGN KEY (item_id) REFERENCES items(item_id)
) ENGINE=InnoDB;

-- World/Regions & Pets
CREATE TABLE IF NOT EXISTS regions (
  region_id   SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  region_name VARCHAR(100) NOT NULL,
  PRIMARY KEY (region_id),
  UNIQUE KEY uq_region_name (region_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pet_species (
  species_id   SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  species_name VARCHAR(100) NOT NULL,
  region_id    SMALLINT UNSIGNED NULL,
  base_hp      INT NOT NULL,
  base_atk     INT NOT NULL,
  base_def     INT NOT NULL,
  base_init    INT NOT NULL,
  PRIMARY KEY (species_id),
  UNIQUE KEY uq_species_name (species_name),
  CONSTRAINT fk_species_region FOREIGN KEY (region_id) REFERENCES regions(region_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS player_unlocked_species (
  entryId              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  player_id            BIGINT UNSIGNED NOT NULL,
  unlocked_species_id  SMALLINT UNSIGNED NOT NULL,
  created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (entryId),
  UNIQUE KEY uq_player_species_unlock (player_id, unlocked_species_id),
  KEY ix_player_unlocked_player (player_id),
  KEY ix_player_unlocked_species (unlocked_species_id),
  CONSTRAINT fk_player_unlocked_player FOREIGN KEY (player_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_player_unlocked_species FOREIGN KEY (unlocked_species_id) REFERENCES pet_species(species_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pet_like_city (
  PLCid      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  pet_id     SMALLINT UNSIGNED NOT NULL,
  country_id SMALLINT UNSIGNED NOT NULL,
  `like`     TINYINT NOT NULL DEFAULT 3,
  PRIMARY KEY (PLCid),
  UNIQUE KEY uq_pet_country (pet_id, country_id),
  CONSTRAINT fk_plc_pet FOREIGN KEY (pet_id) REFERENCES pet_species(species_id) ON DELETE CASCADE,
  CONSTRAINT fk_plc_country FOREIGN KEY (country_id) REFERENCES regions(region_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pet_colors (
  color_id   SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  color_name VARCHAR(50) NOT NULL,
  PRIMARY KEY (color_id),
  UNIQUE KEY uq_color_name (color_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS food_preferences (
  food_pref_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  species_id   SMALLINT UNSIGNED NOT NULL,
  item_id      BIGINT UNSIGNED NOT NULL,
  like_scale   TINYINT UNSIGNED NOT NULL DEFAULT 2,
  PRIMARY KEY (food_pref_id),
  UNIQUE KEY uq_food_pref_species_item (species_id, item_id),
  CONSTRAINT fk_food_pref_species FOREIGN KEY (species_id) REFERENCES pet_species(species_id) ON DELETE CASCADE,
  CONSTRAINT fk_food_pref_item    FOREIGN KEY (item_id)    REFERENCES items(item_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Player-owned pets (instances)
CREATE TABLE IF NOT EXISTS pet_instances (
  pet_instance_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_user_id   BIGINT UNSIGNED NOT NULL,
  species_id      SMALLINT UNSIGNED NOT NULL,
  nickname        VARCHAR(100) NULL,
  color_id        SMALLINT UNSIGNED NULL,
  level           INT UNSIGNED NOT NULL DEFAULT 1,
  experience      INT UNSIGNED NOT NULL DEFAULT 0,
  hp_current      INT NULL,  -- nullable => derive from base/level if NULL
  hp_max          INT NULL,
  atk             INT NULL,
  def             INT NULL,
  initiative      INT NULL,
  inactive        TINYINT(1) NOT NULL DEFAULT 0,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (pet_instance_id),
  KEY ix_pets_owner (owner_user_id),
  CONSTRAINT fk_petinst_owner  FOREIGN KEY (owner_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_petinst_species FOREIGN KEY (species_id) REFERENCES pet_species(species_id),
  CONSTRAINT fk_petinst_color   FOREIGN KEY (color_id)   REFERENCES pet_colors(color_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS abandoned_pets (
  ap_id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  creature_id   BIGINT UNSIGNED NOT NULL,
  old_player_id BIGINT UNSIGNED NOT NULL,
  creature_name VARCHAR(100) NOT NULL,
  abandoned_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ap_id),
  UNIQUE KEY uq_abandoned_creature (creature_id),
  KEY ix_abandoned_old_player (old_player_id),
  CONSTRAINT fk_abandoned_pet FOREIGN KEY (creature_id) REFERENCES pet_instances(pet_instance_id) ON DELETE CASCADE,
  CONSTRAINT fk_abandoned_old_player FOREIGN KEY (old_player_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Equipment for pets (uses unique item instances)
CREATE TABLE IF NOT EXISTS pet_equipment (
  pet_instance_id  BIGINT UNSIGNED NOT NULL,
  slot             VARCHAR(32) NOT NULL,  -- 'head','body','weapon','trinket'
  item_instance_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (pet_instance_id, slot),
  CONSTRAINT fk_pe_pet  FOREIGN KEY (pet_instance_id)  REFERENCES pet_instances(pet_instance_id) ON DELETE CASCADE,
  CONSTRAINT fk_pe_inst FOREIGN KEY (item_instance_id) REFERENCES item_instances(instance_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Shops per region (NPC shops)
CREATE TABLE IF NOT EXISTS shops (
  shop_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  shop_name       VARCHAR(100) NOT NULL,
  region_id       SMALLINT UNSIGNED NOT NULL,
  is_npc          TINYINT(1) NOT NULL DEFAULT 1,
  restock_every_minutes INT UNSIGNED NULL,  -- e.g., 60
  last_restok_at  TIMESTAMP NULL,
  PRIMARY KEY (shop_id),
  KEY ix_shops_region (region_id),
  CONSTRAINT fk_shop_region FOREIGN KEY (region_id) REFERENCES regions(region_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shop_inventory (
  shop_id  INT UNSIGNED NOT NULL,
  item_id  BIGINT UNSIGNED NOT NULL,
  price    DECIMAL(12,2) NULL,  -- NULL => use base_price
  stock    INT NULL,            -- NULL => infinite
  PRIMARY KEY (shop_id, item_id),
  CONSTRAINT fk_shopinv_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id) ON DELETE CASCADE,
  CONSTRAINT fk_shopinv_item FOREIGN KEY (item_id) REFERENCES items(item_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shop_transactions (
  transaction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  shop_id        INT UNSIGNED NOT NULL,
  user_id        BIGINT UNSIGNED NOT NULL,
  item_id        BIGINT UNSIGNED NOT NULL,
  quantity       INT UNSIGNED NOT NULL,
  unit_price     DECIMAL(12,2) NOT NULL,
  currency_id    TINYINT UNSIGNED NOT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (transaction_id),
  KEY ix_shop_tx_user_time (user_id, created_at),
  CONSTRAINT fk_shoptx_shop     FOREIGN KEY (shop_id)     REFERENCES shops(shop_id),
  CONSTRAINT fk_shoptx_user     FOREIGN KEY (user_id)     REFERENCES users(user_id),
  CONSTRAINT fk_shoptx_item     FOREIGN KEY (item_id)     REFERENCES items(item_id),
  CONSTRAINT fk_shoptx_currency FOREIGN KEY (currency_id) REFERENCES currencies(currency_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS creature_name_votes (
  user_id        BIGINT UNSIGNED NOT NULL,
  selection_json JSON NOT NULL,
  submitted_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_namevote_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

------------------------------------------------- 

USE xyzpetsthing;

-- 1) Currencies
INSERT INTO currencies (currency_code, display_name) VALUES
('DOSH','Cash-Dosh'),
('GEM','Premium Gems');

-- 2) Users (store hash bytes via UNHEX(SHA2(...)))
INSERT INTO users (username, email, password_hash) VALUES
('cara','cara@example.com', UNHEX(SHA2('carapw@#@#',256)));

-- 3) Starting balances
INSERT INTO user_balances (user_id, currency_id, balance) VALUES
(1,1,1000.00),(1,2,0.00),
(2,1, 750.00),(2,2,0.00),
(3,1,1200.00),(3,2,50.00);

-- 4) Item rarities & categories
INSERT INTO item_rarities (rarity_name, rarity_rank) VALUES
('Worth dirt',1),('Uncommon',2),('Rare',3),('SR',4),('SSR',5),('UR',6),('One of a kind',7);

INSERT INTO item_categories (category_name) VALUES
('Food'),('Weapon'),('Potion'),('Wearable'),('Paint'),('Misc');

-- 5) Items
INSERT INTO items (item_name, item_description, replenish, base_price, rarity_id, category_id, max_stack, tradable) VALUES
('Berry','A juicy forest berry. Restores a little HP.', 5,           5.00, 1, 1, 99, 1),
('Healing Potion','Restores 50 HP.', 50,                            50.00, 2, 3, 20, 1),
('First Aid Spray','Made from herbs growing near a city 
with some sort of procyonid as their town mascot. 
Heals 200 HP.', 50,                                                250.00, 2, 3, 20, 1),
('Flu Shot','Heals flu-ridden mosnters.', 50,                      550.00, 2, 3, 20, 1),
('Iron Sword','A sturdy beginner blade.', 100,                     200.00, 2, 2,  1, 1),
('Wizard Hat','Stylish and pointy. Boosts magic.', 20,             500.00, 3, 4,  1, 1),
('Mana Elixir','Restores 40 MP.', 40,                               75.00, 3, 3, 20, 1),
('Red Paint','Paints your creature red.', 10,                    50000.00, 3, 5,  1, 1),
('Blue Paint','Paints your creature blue.', 10,                  50000.00, 3, 5,  1, 1),
('Yellow Paint','Paints your creature yellow.', 10,              50000.00, 3, 5,  1, 1),
('Green Paint','Paints your creature green.', 10,                50000.00, 3, 5,  1, 1),
('Purple Paint','Paints your creature purple.', 10,              50000.00, 3, 5,  1, 1),
('Black Paint','Paints your creature black.', 10,              1000000.00, 4, 5,  1, 1),
('Real Paintbrush','Paints your creature realistic.', 10,      2005000.00, 5, 5,  1, 1),
('Crystal Shard','A rare sparkling material.', 10,                1200.00, 4, 6, 99, 1);

INSERT INTO regions (region_name) VALUES
('Aegia Aeterna'), ('Baharamandal'), ('Bretonreach'), ('Crescent Caliphate'), 
('Eagle Serpent Dominion'), ('Eretz-Shalem League'), ('Gran Columbia'), ('Hammurabia'), 
('Itzam Empire'), ('Kemet'), ('Lotus-Dragon Kingdom'), ('Nornheim'), ('Red Sun Commonwealth'), 
('Rheinland'), ('Rodinian Tsardom'), ('Sapa Inti Empire'), ('Sila Council'), 
('Sovereign Tribes of the Ancestral Plains'), ('Spice Route League'), 
('United free Republic of Borealia'), ('Xochimex'), ('Yamanokubo'), ('Yara Nations');


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
(246, 'Mimic', 22, 8, 15, 12, 2),
(247, 'Jackalope', 22, 10, 12, 8, 18),
(248, 'Mad Hatter', 22, 10, 12, 10, 12),
(249, 'Death', 22, 10, 18, 11, 12),
(250, 'War', 22, 8, 15, 12, 13),
(251, 'Pestilence', 22, 8, 15, 10, 14),
(252, 'Fury', 22, 8, 19, 4, 12),
(253, 'Famine', 22, 10, 12, 13, 17),
(254, 'Elf', 3, 6, 12, 11, 8),
(255, 'Blaze Cat', 7, 7, 11, 7, 12),
(256, 'Waterlil', 5, 15, 11, 8, 1),
(257, 'Slidred', 22, 6, 5, 12, 15),
(258, 'Alisnapor', 25, 6, 18, 18, 2),
(266, 'Daeodon', 4, 12, 18, 10, 11),
(267, 'Anomalocaris', 10, 15, 8, 10, 12),
(268, 'Adaro', 11, 15, 13, 10, 18),
(269, 'Akaname', 7, 8, 15, 10, 17),
(270, 'Bee', 4, 8, 15, 8, 12),
(271, 'Bullywug', 3, 8, 5, 5, 15),
(272, 'Landharpy', 15, 12, 17, 10, 19),
(273, 'Yatagarasu', 7, 8, 12, 12, 13),
(274, 'Tengu', 7, 12, 15, 11, 18),
(275, 'Klabuster', 6, 10, 5, 5, 1),
(276, 'Frost Giant', 2, 15, 18, 13, 5),
(277, 'Fire Giant', 2, 15, 18, 13, 5),
(278, 'Spoodr', 18, 5, 18, 2, 19),
(279, 'Ziz', 14, 15, 17, 10, 18),
(280, 'Toucan', 19, 10, 12, 8, 15),
(281, 'Tiddalik', 18, 16, 12, 10, 18),
(282, 'Spinosaur', 15, 12, 19, 12, 10),
(283, 'Sea Turtle', 17, 12, 5, 18, 14),
(284, 'Pishtaco', 20, 10, 13, 8, 18),
(285, 'Penguin', 16, 10, 9, 8, 15),
(286, 'Pegasus', 1, 12, 13, 10, 14),
(287, 'Oryx', 13, 12, 10, 15, 17),
(288, 'Nightmarcher', 11, 12, 18, 10, 15),
(289, 'Moo', 11, 12, 13, 18, 5),
(290, 'Manticore', 12, 10, 18, 16, 10),
(291, 'La Mano Peluda', 8, 5, 16, 3, 12),
(292, 'Harpy', 1, 12, 17, 10, 18),
(293, 'Dugong', 13, 15, 10, 10, 12),
(294, 'Camel', 12, 10, 15, 12, 13),
(295, 'Apsara', 6, 12, 18, 13, 10),
(296, 'Armadillo', 19, 10, 8, 19, 12),
(297, 'Kachina', 25, 12, 15, 10, 15),
(298, 'Papillon', 3, 10, 5, 5, 13),
(299, 'Brownie', 26, 10, 12, 5, 18),
(300, 'Sunwukong', 5, 14, 13, 10, 13),
(301, 'Tuskin', 27, 18, 12, 10, 4),
(302, 'Rhiwool', 27, 18, 12, 14, 4),
(303, 'Rheintalensis', 27, 14, 11, 12, 7),
(304, 'Argentowlis', 27, 16, 12, 10, 8),
(305, 'Hairypterix', 27, 13, 11, 9, 12),
(306, 'Turtworld', 27, 22, 8, 18, 3),
(307, 'Gigantopithecus', 27, 19, 13, 14, 5),
(308, 'Moopie', 28, 12, 8, 8, 13);

INSERT INTO pet_colors (color_name) VALUES
('Red'),('Blue'),('Green'),('Yellow'),('Purple'),('Black'),('Real');

-- 7) Shops per region
INSERT INTO shops (shop_name, region_id, is_npc, restock_every_minutes, last_restok_at) VALUES
('Eternal General Store', 1, 1, 60,  NULL),
('Crescent Bazaar',       2, 1, 90,  NULL),
('Rodian Emporium',       3, 1, 120, NULL),
('Pizzeria Sol Invicta',  1, 1, 60, NULL),
('Crescent Bazaar',       2, 1, 90, NULL),
('Rodian Emporium',       3, 1, 120, NULL);

INSERT INTO shop_inventory (shop_id, item_id, price, stock) VALUES
(1, 1,   NULL,     NULL),   -- Berry (infinite, base price)
(1, 2,   45.00,    50),     -- Healing Potion
(1, 3,   45.00,    50),     -- First aid spray
(1, 4,   45.00,    50),     -- Flu Shot
(1, 5,  220.00,     2),     -- Iron Sword
(2, 7,   80.00,    40),     -- Mana Elixir   (was 6)
(2, 15, 1300.00,    5),     -- Crystal Shard (was 7)
(2, 6,  600.00,     3),     -- Wizard Hat
(3, 12, 5200000.00, 1);     -- Real Paintbrush (you don’t have “Ghost Paintbrush”)

-- 8) Player-owned pets
INSERT INTO pet_instances (owner_user_id, species_id, nickname, color_id, level, experience, hp_current, hp_max, atk, def, initiative) VALUES
(1,1,'Ember',  1, 5, 120, 40, 40, 12, 7, 3),
(2,2,'Splash', 2, 3,  40, 35, 35, 8, 12, 8),
(3,3,'Gale',   3, 7, 300, 42, 42, 9, 15, 2);

-- 9) Player inventories (stackables)
INSERT INTO user_inventory (user_id, item_id, quantity) VALUES
(1,1,15),  -- Alice: Berries x15
(1,2, 2),  -- Alice: Healing Potion x2
(2,7, 1),  -- Bob: Crystal Shard x1
(2,1, 8),  -- Bob: Berries x8
(3,6, 3),  -- Cara: Mana Elixir x3
(3,4, 1);  -- Cara: Wizard Hat x1

-- 10) Example: give Alice a unique Iron Sword and equip her pet
INSERT INTO item_instances (item_id, owner_user_id, durability, bound_to_user)
VALUES (3, 1, 100, 0);  -- likely instance_id = 1

INSERT INTO pet_equipment (pet_instance_id, slot, item_instance_id)
VALUES (1, 'weapon', 1);

-- 11) Sample economy movement: Alice buys an Iron Sword (220 COIN) from shop 1
INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata)
VALUES (1, 1, -220.00, 'shop_purchase', JSON_OBJECT('shop_id', 1, 'item_id', 3, 'qty', 1));

INSERT INTO shop_transactions (shop_id, user_id, item_id, quantity, unit_price, currency_id)
VALUES (1, 1, 3, 1, 220.00, 1);

-- reflect the purchase in balances and shop stock (optional but nice)
UPDATE user_balances
SET balance = balance - 220.00
WHERE user_id = 1 AND currency_id = 1;

UPDATE shop_inventory
SET stock = stock - 1
WHERE shop_id = 1 AND item_id = 3;

ALTER TABLE pet_instances
  ADD COLUMN gender CHAR(1) NOT NULL DEFAULT 'f' AFTER initiative,
  ADD COLUMN hunger TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER gender,
  ADD COLUMN happiness TINYINT UNSIGNED NOT NULL DEFAULT 50 AFTER hunger,
  ADD COLUMN intelligence INT UNSIGNED NOT NULL DEFAULT 0 AFTER happiness,
  ADD COLUMN sickness TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER intelligence,
  ADD COLUMN hp_max INT NULL AFTER hp_current,
  ADD COLUMN IF NOT EXISTS inactive TINYINT(1) NOT NULL DEFAULT 0 AFTER initiative;

UPDATE pet_instances
   SET hp_max = COALESCE(hp_max, hp_current),
       hp_current = LEAST(COALESCE(hp_max, hp_current), hp_current)
 WHERE hp_current IS NOT NULL;

-- Backfill inactive flag for existing pet rows (safe to re-run)
UPDATE pet_instances SET inactive = COALESCE(inactive, 0);

-- Breeding daycare
CREATE TABLE IF NOT EXISTS breeding (
  breed_instance_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_user_id BIGINT UNSIGNED NOT NULL,
  deposit_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  father BIGINT UNSIGNED NULL,
  mother BIGINT UNSIGNED NOT NULL,
  egg_creature_id SMALLINT UNSIGNED NULL,
  egg_count INT UNSIGNED NOT NULL DEFAULT 0,
  time_to_hatch TINYINT UNSIGNED NOT NULL DEFAULT 0,
  father_best_stat VARCHAR(16) NULL,
  mother_best_stat VARCHAR(16) NULL,
  PRIMARY KEY (breed_instance_id),
  KEY ix_breeding_owner (owner_user_id),
  CONSTRAINT fk_breeding_owner FOREIGN KEY (owner_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_breeding_father FOREIGN KEY (father) REFERENCES pet_instances(pet_instance_id) ON DELETE SET NULL,
  CONSTRAINT fk_breeding_mother FOREIGN KEY (mother) REFERENCES pet_instances(pet_instance_id) ON DELETE CASCADE,
  CONSTRAINT fk_breeding_species FOREIGN KEY (egg_creature_id) REFERENCES pet_species(species_id)
) ENGINE=InnoDB;
