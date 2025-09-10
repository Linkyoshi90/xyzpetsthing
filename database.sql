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

-- Currency catalog (so you can add COIN/CASH/etc.)
CREATE TABLE IF NOT EXISTS currencies (
  currency_id   TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  currency_code VARCHAR(16) NOT NULL,
  display_name  VARCHAR(32) NOT NULL,
  PRIMARY KEY (currency_id),
  UNIQUE KEY uq_currency_code (currency_code)
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

CREATE TABLE IF NOT EXISTS pet_colors (
  color_id   SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  color_name VARCHAR(50) NOT NULL,
  PRIMARY KEY (color_id),
  UNIQUE KEY uq_color_name (color_name)
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
  atk             INT NULL,
  def             INT NULL,
  initiative      INT NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (pet_instance_id),
  KEY ix_pets_owner (owner_user_id),
  CONSTRAINT fk_petinst_owner  FOREIGN KEY (owner_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_petinst_species FOREIGN KEY (species_id) REFERENCES pet_species(species_id),
  CONSTRAINT fk_petinst_color   FOREIGN KEY (color_id)   REFERENCES pet_colors(color_id)
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
('alice','alice@example.com', UNHEX(SHA2('alicepw@#@#',256))),
('bob','bob@example.com',   UNHEX(SHA2('bobpw@#@#',256))),
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

-- 6) Regions & species/colors
INSERT INTO regions (region_name) VALUES
('Aegia Aeterna'), ('Nornheim'), ('Bretonreach'), ('Rheinland'), ('Rodinian Tsardom'), 
('United free Republic of Borealia'), ('Sovereign Tribes of the Ancestral Plains'), 
('Lotus-Dragon Kingdom'), ('Baharamandal'), ('Yamanokubo'), ('Xochimex'), 
('Eagle Serpent Dominion'), ('Itzam Empire'), ('Spice Route League'), 
('Crescent Caliphate'), ('Hammurabia'), ('Eretz-Shalem League'), ('Kemet'), 
('Sila Council'), ('Red Sun Commonwealth'), ('Yara Nations'), ('Gran Columbia'), 
('Sapa Inti Empire');

INSERT INTO pet_species (species_name, region_id, base_hp, base_atk, base_def, base_init) VALUES
('Lamia',                   1,  8, 8, 5, 7),
('Centaur',                 1, 12, 6, 7, 5),
('Kraken',                  2, 12, 6, 7, 5),
('Ratatoskr',               2, 12, 6, 7, 5),
('Banshee',                 3, 12, 6, 7, 5),
('Dullahan',                3, 12, 6, 7, 5),
('Will-o-Wisp',             3, 12, 6, 7, 5),
('Kelpie',                  3, 12, 6, 7, 5),
('Angel',                   4, 12, 6, 7, 5),
('Demon',                   4, 12, 6, 7, 5),
('Succubus',                4, 12, 6, 7, 5),
('Leshy',                   5, 12, 6, 7, 5),
('Vodyanoy',                5, 12, 6, 7, 5),
('Lich',                    6, 12, 6, 7, 5),
('Jack-o-Lantern',          6, 12, 6, 7, 5),
('Thunderbird',             7, 12, 6, 7, 5),
('Horned Serpent Uktena',   7, 12, 6, 7, 5),
('Jiang-Shi',               8, 12, 6, 7, 5),
('Vermillion Bird',         8, 12, 6, 7, 5),
('Gandharva',               9, 12, 6, 7, 5),
('Naga',                    9, 12, 6, 7, 5),
('Spider-Crab',            10, 12, 6, 7, 5),
('Kitsune',                10, 12, 6, 7, 5),
('Yuki-Onna',              10, 12, 6, 7, 5),
('La Llorona',             11, 12, 6, 7, 5),
('Chupacabra',             11, 12, 6, 7, 5),
('Charro Negro',           11, 12, 6, 7, 5),
('Quetzalcoatl',           12, 12, 6, 7, 5),
('Ahuizotl',               13, 12, 6, 7, 5),
('Cipactli',               13, 12, 6, 7, 5),
('Ocelot',                 13, 12, 6, 7, 5),
('Azureus',                14, 12, 6, 7, 5),
('Tapir',                  14, 12, 6, 7, 5),
('Crab man',               15, 12, 6, 7, 5),
('Taniwha',                15, 12, 6, 7, 5),
('Genie',                  16, 12, 6, 7, 5),
('Bahamut',                16, 12, 6, 7, 5),
('Girtablilu',             17, 12, 6, 7, 5),
('Lamassu',                17, 12, 6, 7, 5),
('Golem',                  18, 12, 6, 7, 5),
('Dolphin',                18, 12, 6, 7, 5),
('Anubis',                 19, 12, 6, 7, 5),
('Wadjet',                 19, 12, 6, 7, 5),
('Amarok',                 20, 12, 6, 7, 5),
('Polar Bear',             20, 12, 6, 7, 5),
('Drop Bear',              21, 12, 6, 7, 5),
('Min-Min Lights',         21, 12, 6, 7, 5),
('Bunyip',                 22, 12, 6, 7, 5),
('Rainbow Serpent',        22, 12, 6, 7, 5),
('Curupira',               23, 12, 6, 7, 5),
('Capybara',               23, 12, 6, 7, 5),
('Fishman',                24, 12, 6, 7, 5),
('Argentinosaurus',        24, 12, 6, 7, 5),
('Amaru',                  24, 12, 6, 7, 5);

INSERT INTO pet_colors (color_name) VALUES
('Red'),('Blue'),('Green'),('Yellow'),('Purple'),('Black'),('Real');

-- 7) Shops per region
INSERT INTO shops (shop_name, region_id, is_npc, restock_every_minutes, last_restok_at) VALUES
('Eternal General Store', 1, 1, 60,  NULL),
('Crescent Bazaar',       2, 1, 90,  NULL),
('Rodian Emporium',       3, 1, 120, NULL);

INSERT INTO shop_inventory (shop_id, item_id, price, stock) VALUES
(1, 1,   NULL,     NULL),   -- Berry (infinite, base price)
(1, 2,   45.00,    50),     -- Healing Potion
(1, 3,  220.00,     2),     -- Iron Sword
(2, 5,   80.00,    40),     -- Mana Elixir   (was 6)
(2, 13, 1300.00,    5),     -- Crystal Shard (was 7)
(2, 4,  600.00,     3),     -- Wizard Hat
(3, 12, 5200000.00, 1);     -- Real Paintbrush (you don’t have “Ghost Paintbrush”)

-- 8) Player-owned pets
INSERT INTO pet_instances (owner_user_id, species_id, nickname, color_id, level, experience, hp_current, atk, def, initiative) VALUES
(1,1,'Ember',  1, 5, 120, 40, 12, 7, 3),
(2,2,'Splash', 2, 3,  40, 35, 8, 12, 8),
(3,3,'Gale',   3, 7, 300, 42, 9, 15, 2);

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
  ADD COLUMN intelligence INT UNSIGNED NOT NULL DEFAULT 0 AFTER happiness;
