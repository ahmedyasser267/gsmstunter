-- ═══════════════════════════════════════════════════════════════════════════
--  GSMStunter – Complete Database Setup
--  Run this single file to create / rebuild the entire database from scratch.
--  Safe to run multiple times (CREATE IF NOT EXISTS + INSERT IGNORE patterns).
--
--  Tables (in dependency order):
--    1.  admins
--    2.  devices / device_storage_prices
--    3.  conditions / defects / cosmetics / risk_rules / bonuses
--    4.  calculation_settings / trade_settings
--    5.  categories / category_translations
--    6.  products / product_translations
--    7.  product_view_settings
--    8.  section_visibility
--    9.  quotes
--    10. customers
--    11. orders / order_items / order_status_history
--    12. cart_snapshots
--    13. wishlist_items   (new – for customer wishlist)
--    14. app_translations
-- ═══════════════════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS gsmstunter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gsmstunter;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ────────────────────────────────────────────────────────────────────────────
-- 1. ADMINS
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admins (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(100)  NOT NULL UNIQUE,
  password_hash VARCHAR(255)  NOT NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: username=admin  password=admin123
INSERT IGNORE INTO admins (id, username, password_hash) VALUES
(1, 'admin', '$2y$10$8Gf8o8h6TRprvaw2uE9u2ecfB30ZfL/Y6hX7kJXwD7V3oRKYt8q1K');

-- ────────────────────────────────────────────────────────────────────────────
-- 2. DEVICES  (sell / buyback flow)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS devices (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  device_key VARCHAR(100) NOT NULL UNIQUE,
  name       VARCHAR(150) NOT NULL,
  active     TINYINT(1)   NOT NULL DEFAULT 1,
  created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS device_storage_prices (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  device_id     INT             NOT NULL,
  storage_label VARCHAR(20)     NOT NULL,
  base_price    DECIMAL(10,2)   NOT NULL,
  UNIQUE KEY uq_device_storage (device_id, storage_label),
  CONSTRAINT fk_dsp_device FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO devices (device_key, name) VALUES
('iphone_17',    'iPhone 17'),
('iphone_16',    'iPhone 16'),
('iphone_15',    'iPhone 15'),
('iphone_15_pro','iPhone 15 Pro'),
('iphone_14',    'iPhone 14'),
('iphone_14_pro','iPhone 14 Pro'),
('iphone_13',    'iPhone 13'),
('iphone_13_pro','iPhone 13 Pro'),
('iphone_12',    'iPhone 12'),
('iphone_11',    'iPhone 11');

INSERT IGNORE INTO device_storage_prices (device_id, storage_label, base_price)
SELECT d.id, s.storage_label, s.base_price
FROM devices d
JOIN (
  SELECT 'iphone_17'    dk,'128'  storage_label, 950  base_price UNION ALL
  SELECT 'iphone_17',       '256', 1020 UNION ALL SELECT 'iphone_17',       '512', 1100 UNION ALL
  SELECT 'iphone_16',       '128',  880 UNION ALL SELECT 'iphone_16',       '256',  940 UNION ALL
  SELECT 'iphone_16',       '512', 1000 UNION ALL SELECT 'iphone_15',       '128',  700 UNION ALL
  SELECT 'iphone_15',       '256',  750 UNION ALL SELECT 'iphone_15',       '512',  820 UNION ALL
  SELECT 'iphone_15_pro',   '128',  780 UNION ALL SELECT 'iphone_15_pro',   '256',  830 UNION ALL
  SELECT 'iphone_15_pro',   '512',  900 UNION ALL SELECT 'iphone_14',       '128',  500 UNION ALL
  SELECT 'iphone_14',       '256',  550 UNION ALL SELECT 'iphone_14',       '512',  600 UNION ALL
  SELECT 'iphone_14_pro',   '128',  650 UNION ALL SELECT 'iphone_14_pro',   '256',  700 UNION ALL
  SELECT 'iphone_14_pro',   '512',  760 UNION ALL SELECT 'iphone_13',       '128',  380 UNION ALL
  SELECT 'iphone_13',       '256',  420 UNION ALL SELECT 'iphone_13',       '512',  460 UNION ALL
  SELECT 'iphone_13_pro',   '128',  420 UNION ALL SELECT 'iphone_13_pro',   '256',  460 UNION ALL
  SELECT 'iphone_13_pro',   '512',  500 UNION ALL SELECT 'iphone_12',       '64',   240 UNION ALL
  SELECT 'iphone_12',       '128',  270 UNION ALL SELECT 'iphone_12',       '256',  300 UNION ALL
  SELECT 'iphone_11',       '64',   180 UNION ALL SELECT 'iphone_11',       '128',  210 UNION ALL
  SELECT 'iphone_11',       '256',  240
) s ON s.dk = d.device_key;

-- ────────────────────────────────────────────────────────────────────────────
-- 3. LOOKUP TABLES  (conditions / defects / cosmetics / risks / bonuses)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS conditions (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  condition_key VARCHAR(100)  NOT NULL UNIQUE,
  label         VARCHAR(150)  NOT NULL,
  factor        DECIMAL(5,2)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO conditions (condition_key, label, factor) VALUES
('als_nieuw',   'Als nieuw',   1.00),
('zeer_goed',   'Zeer goed',   0.92),
('goed',        'Goed',        0.85),
('beschadigd',  'Beschadigd',  0.70);

CREATE TABLE IF NOT EXISTS defects (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  defect_key  VARCHAR(100)  NOT NULL UNIQUE,
  label       VARCHAR(150)  NOT NULL,
  deduction   DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO defects (defect_key, label, deduction) VALUES
('battery_weak',        'Batterij onder 80%',   20),
('screen_broken',       'Scherm gebroken',       80),
('back_glass_broken',   'Achterkant kapot',      50),
('faceid_not_working',  'Face ID werkt niet',    60),
('camera_not_working',  'Camera werkt niet',     40),
('charging_issue',      'Laadprobleem',          30);

CREATE TABLE IF NOT EXISTS cosmetics (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  cosmetic_key VARCHAR(100)  NOT NULL UNIQUE,
  label        VARCHAR(150)  NOT NULL,
  deduction    DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO cosmetics (cosmetic_key, label, deduction) VALUES
('heavy_scratches', 'Diepe krassen',  20),
('frame_damage',    'Frame schade',   30);

CREATE TABLE IF NOT EXISTS risk_rules (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  risk_key    VARCHAR(100) NOT NULL UNIQUE,
  label       VARCHAR(150) NOT NULL,
  action_type VARCHAR(50)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO risk_rules (risk_key, label, action_type) VALUES
('icloud_locked',        'iCloud lock actief',          'manual_review'),
('no_power',             'Toestel start niet op',       'manual_review'),
('water_damage_major',   'Ernstige waterschade',        'manual_review'),
('imei_blacklist',       'IMEI geblokkeerd',            'manual_review');

CREATE TABLE IF NOT EXISTS bonuses (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  bonus_key VARCHAR(100)  NOT NULL UNIQUE,
  label     VARCHAR(200)  NOT NULL,
  value     DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO bonuses (bonus_key, label, value) VALUES
('store_credit', 'Ontvang €10 extra als tegoed in de winkel', 10.00);

-- ────────────────────────────────────────────────────────────────────────────
-- 4. SETTINGS  (calculation + trade)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS calculation_settings (
  id                       INT           AUTO_INCREMENT PRIMARY KEY,
  min_price                DECIMAL(10,2) NOT NULL DEFAULT 30.00,
  global_reduction_percent DECIMAL(6,2)  NOT NULL DEFAULT 0.00,
  rounding_rule            VARCHAR(30)   NOT NULL DEFAULT 'nearest_5',
  currency                 VARCHAR(10)   NOT NULL DEFAULT 'EUR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO calculation_settings (id, min_price, global_reduction_percent, rounding_rule, currency)
VALUES (1, 30.00, 0.00, 'nearest_5', 'EUR');

CREATE TABLE IF NOT EXISTS trade_settings (
  id                   INT           AUTO_INCREMENT PRIMARY KEY,
  trade_bonus_percent  DECIMAL(6,2)  NOT NULL DEFAULT 0.00,
  exchange_bonus_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  min_trade_price      DECIMAL(10,2) NOT NULL DEFAULT 20.00,
  created_at           TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at           TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO trade_settings (id, trade_bonus_percent, exchange_bonus_value, min_trade_price)
VALUES (1, 5.00, 10.00, 20.00);

-- ────────────────────────────────────────────────────────────────────────────
-- 5. CATEGORIES
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
  id           INT          AUTO_INCREMENT PRIMARY KEY,
  category_key VARCHAR(120) NOT NULL UNIQUE,
  parent_id    INT          NULL,
  icon         VARCHAR(80)  NULL,
  image_url    VARCHAR(400) NULL,
  sort_order   INT          NOT NULL DEFAULT 0,
  is_visible   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS category_translations (
  id          BIGINT       AUTO_INCREMENT PRIMARY KEY,
  category_id INT          NOT NULL,
  lang_code   VARCHAR(5)   NOT NULL,
  name        VARCHAR(160) NOT NULL,
  description TEXT         NULL,
  UNIQUE KEY uq_cat_lang (category_id, lang_code),
  CONSTRAINT fk_ct_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categories (id, category_key, parent_id, icon, sort_order, is_visible) VALUES
(1, 'smartphones',  NULL, 'fa-mobile-screen-button', 10, 1),
(2, 'laptops',      NULL, 'fa-laptop',                20, 1),
(3, 'tablets',      NULL, 'fa-tablet-screen-button',  30, 1),
(4, 'smartwatches', NULL, 'fa-clock',                 40, 1),
(5, 'headphones',   NULL, 'fa-headphones',            50, 1),
(6, 'accessories',  NULL, 'fa-plug',                  60, 1);

INSERT IGNORE INTO category_translations (category_id, lang_code, name, description) VALUES
(1,'nl','Smartphones',          'Refurbished smartphones'),
(1,'de','Smartphones',          'Refurbished Smartphones'),
(1,'fr','Smartphones',          'Smartphones reconditionnés'),
(2,'nl','Laptops',              'Refurbished laptops'),
(2,'de','Laptops',              'Refurbished Laptops'),
(2,'fr','Ordinateurs portables','Ordinateurs reconditionnés'),
(3,'nl','Tablets',              'Refurbished tablets'),
(3,'de','Tablets',              'Refurbished Tablets'),
(3,'fr','Tablettes',            'Tablettes reconditionnées'),
(4,'nl','Smartwatches',         'Refurbished wearables'),
(4,'de','Smartwatches',         'Refurbished Wearables'),
(4,'fr','Montres connectées',   'Montres reconditionnées'),
(5,'nl','Koptelefoons',         'Refurbished audio'),
(5,'de','Kopfhörer',            'Refurbished Audio'),
(5,'fr','Casques',              'Audio reconditionné'),
(6,'nl','Accessoires',          'Accessoires'),
(6,'de','Zubehör',              'Zubehör'),
(6,'fr','Accessoires',          'Accessoires');

-- ────────────────────────────────────────────────────────────────────────────
-- 6. PRODUCTS
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS products (
  id                    BIGINT        AUTO_INCREMENT PRIMARY KEY,
  sku                   VARCHAR(120)  NOT NULL UNIQUE,
  product_type          VARCHAR(60)   NOT NULL DEFAULT 'smartphone',
  category_id           INT           NULL,
  brand                 VARCHAR(80)   NOT NULL,
  model                 VARCHAR(150)  NOT NULL,
  storage_label         VARCHAR(30)   NULL,
  ram_gb                INT           NULL,
  camera_mp             INT           NULL,
  battery_mah           INT           NULL,
  screen_size_in        DECIMAL(4,2)  NULL,
  chipset               VARCHAR(120)  NULL,
  color                 VARCHAR(60)   NULL,
  condition_key         VARCHAR(100)  NULL,
  price                 DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  dynamic_adjust_percent DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  old_price             DECIMAL(10,2) NULL,
  stock_qty             INT           NOT NULL DEFAULT 0,
  image_url             VARCHAR(400)  NULL,
  is_visible            TINYINT(1)    NOT NULL DEFAULT 1,
  sort_order            INT           NOT NULL DEFAULT 0,
  created_at            TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at            TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_translations (
  id                BIGINT       AUTO_INCREMENT PRIMARY KEY,
  product_id        BIGINT       NOT NULL,
  lang_code         VARCHAR(5)   NOT NULL,
  name              VARCHAR(180) NOT NULL,
  short_description TEXT         NULL,
  long_description  TEXT         NULL,
  UNIQUE KEY uq_product_lang (product_id, lang_code),
  CONSTRAINT fk_pt_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed products (idempotent by SKU – one row per INSERT to keep columns clear)
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'IPH15PRO-256-BLK','smartphone',c.id,'Apple','iPhone 15 Pro','256GB','Black','excellent',999.00,0.00,1149.00,18,'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&h=800&fit=crop',1,10 FROM categories c WHERE c.category_key='smartphones';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'IPH14-128-PUR','smartphone',c.id,'Apple','iPhone 14','128GB','Purple','as_new',729.00,0.00,849.00,12,'https://images.unsplash.com/photo-1663499482523-1c0c1bae4ce1?w=800&h=800&fit=crop',1,20 FROM categories c WHERE c.category_key='smartphones';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'IPH13-256-MNT','smartphone',c.id,'Apple','iPhone 13','256GB','Midnight','good',499.00,-5.00,649.00,22,'https://images.unsplash.com/photo-1632661674596-df8be070a5c5?w=800&h=800&fit=crop',1,30 FROM categories c WHERE c.category_key='smartphones';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'S24U-256-TI','smartphone',c.id,'Samsung','Galaxy S24 Ultra','256GB','Titanium Gray','excellent',879.00,0.00,1029.00,14,'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&h=800&fit=crop',1,40 FROM categories c WHERE c.category_key='smartphones';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'S23-128-GRN','smartphone',c.id,'Samsung','Galaxy S23','128GB','Green','good',579.00,-3.00,699.00,19,'https://images.unsplash.com/photo-1678911820864-e2c567c655d7?w=800&h=800&fit=crop',1,50 FROM categories c WHERE c.category_key='smartphones';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'PIX8P-128-BLK','smartphone',c.id,'Google','Pixel 8 Pro','128GB','Obsidian','excellent',799.00,0.00,999.00,8,'https://images.unsplash.com/photo-1598327105854-5c0f4b3ec9a8?w=800&h=800&fit=crop',1,60 FROM categories c WHERE c.category_key='smartphones';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'OP12-256-BLK','smartphone',c.id,'OnePlus','12','256GB','Silky Black','excellent',699.00,0.00,849.00,11,'https://images.unsplash.com/photo-1598327105854-5c0f4b3ec9a8?w=800&h=800&fit=crop',1,70 FROM categories c WHERE c.category_key='smartphones';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'XIR13-256-BLU','smartphone',c.id,'Xiaomi','Redmi Note 13 Pro','256GB','Ocean Teal','excellent',299.00,0.00,399.00,30,'https://images.unsplash.com/photo-1598965675045-45c5e72c7d05?w=800&h=800&fit=crop',1,80 FROM categories c WHERE c.category_key='smartphones';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'MBA13-M3-512-SL','laptop',c.id,'Apple','MacBook Air 13 M3','512GB','Silver','excellent',1249.00,0.00,1449.00,9,'https://images.unsplash.com/photo-1517336714739-489689fd1ca8?w=800&h=800&fit=crop',1,90 FROM categories c WHERE c.category_key='laptops';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'MBP14-M3P-1TB-SG','laptop',c.id,'Apple','MacBook Pro 14 M3 Pro','1TB','Space Gray','excellent',1899.00,0.00,2199.00,5,'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=800&h=800&fit=crop',1,100 FROM categories c WHERE c.category_key='laptops';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'XPS13-1TB-BK','laptop',c.id,'Dell','XPS 13','1TB','Black','good',1099.00,-2.50,1299.00,11,'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&h=800&fit=crop',1,110 FROM categories c WHERE c.category_key='laptops';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'T14S-512-BK','laptop',c.id,'Lenovo','ThinkPad T14s','512GB','Black','excellent',899.00,0.00,1099.00,7,'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=800&h=800&fit=crop',1,120 FROM categories c WHERE c.category_key='laptops';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'IPAIR6-256-BLU','tablet',c.id,'Apple','iPad Air 6','256GB','Blue','excellent',669.00,0.00,789.00,16,'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&h=800&fit=crop',1,130 FROM categories c WHERE c.category_key='tablets';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'IPRO13-512-SL','tablet',c.id,'Apple','iPad Pro 13 M4','512GB','Silver','excellent',1199.00,0.00,1399.00,6,'https://images.unsplash.com/photo-1585790050230-5dd28404ccb9?w=800&h=800&fit=crop',1,140 FROM categories c WHERE c.category_key='tablets';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'STAB8U-256-GR','tablet',c.id,'Samsung','Galaxy Tab S8 Ultra','256GB','Graphite','excellent',649.00,0.00,849.00,10,'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=800&h=800&fit=crop',1,150 FROM categories c WHERE c.category_key='tablets';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'GW7-44-BLK','smartwatch',c.id,'Samsung','Galaxy Watch 7','44mm','Black','excellent',259.00,0.00,339.00,25,'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&h=800&fit=crop',1,160 FROM categories c WHERE c.category_key='smartwatches';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'AW9-45-BLK','smartwatch',c.id,'Apple','Apple Watch Series 9','45mm','Midnight','excellent',449.00,0.00,549.00,18,'https://images.unsplash.com/photo-1434493907317-a46b5bbe7834?w=800&h=800&fit=crop',1,170 FROM categories c WHERE c.category_key='smartwatches';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'WH1000XM5-BLK','headphone',c.id,'Sony','WH-1000XM5',NULL,'Black','excellent',279.00,0.00,369.00,20,'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&h=800&fit=crop',1,180 FROM categories c WHERE c.category_key='headphones';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'BOSE-QC45-WHT','headphone',c.id,'Bose','QuietComfort 45',NULL,'White','excellent',249.00,0.00,329.00,15,'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&h=800&fit=crop',1,190 FROM categories c WHERE c.category_key='headphones';
INSERT IGNORE INTO products (sku,product_type,category_id,brand,model,storage_label,color,condition_key,price,dynamic_adjust_percent,old_price,stock_qty,image_url,is_visible,sort_order)
SELECT 'USBC-30W-WHT','accessory',c.id,'Anker','USB-C Charger 30W',NULL,'White','new',29.00,0.00,39.00,60,'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&h=800&fit=crop',1,200 FROM categories c WHERE c.category_key='accessories';

-- NL Product translations
INSERT INTO product_translations (product_id, lang_code, name, short_description, long_description)
SELECT p.id, 'nl',
  CASE p.sku
    WHEN 'IPH15PRO-256-BLK'  THEN 'iPhone 15 Pro 256GB Zwart'
    WHEN 'IPH14-128-PUR'     THEN 'iPhone 14 128GB Paars'
    WHEN 'IPH13-256-MNT'     THEN 'iPhone 13 256GB Middernacht'
    WHEN 'S24U-256-TI'       THEN 'Samsung Galaxy S24 Ultra 256GB Titanium'
    WHEN 'S23-128-GRN'       THEN 'Samsung Galaxy S23 128GB Groen'
    WHEN 'PIX8P-128-BLK'     THEN 'Google Pixel 8 Pro 128GB Obsidian'
    WHEN 'OP12-256-BLK'      THEN 'OnePlus 12 256GB Zwart'
    WHEN 'XIR13-256-BLU'     THEN 'Xiaomi Redmi Note 13 Pro 256GB Blauw'
    WHEN 'MBA13-M3-512-SL'   THEN 'MacBook Air 13 M3 512GB Zilver'
    WHEN 'MBP14-M3P-1TB-SG'  THEN 'MacBook Pro 14 M3 Pro 1TB Space Gray'
    WHEN 'XPS13-1TB-BK'      THEN 'Dell XPS 13 1TB Zwart'
    WHEN 'T14S-512-BK'       THEN 'Lenovo ThinkPad T14s 512GB Zwart'
    WHEN 'IPAIR6-256-BLU'    THEN 'iPad Air 6 256GB Blauw'
    WHEN 'IPRO13-512-SL'     THEN 'iPad Pro 13 M4 512GB Zilver'
    WHEN 'STAB8U-256-GR'     THEN 'Samsung Galaxy Tab S8 Ultra 256GB Graphite'
    WHEN 'GW7-44-BLK'        THEN 'Samsung Galaxy Watch 7 44mm Zwart'
    WHEN 'AW9-45-BLK'        THEN 'Apple Watch Series 9 45mm Midnight'
    WHEN 'WH1000XM5-BLK'     THEN 'Sony WH-1000XM5 Zwart'
    WHEN 'BOSE-QC45-WHT'     THEN 'Bose QuietComfort 45 Wit'
    WHEN 'USBC-30W-WHT'      THEN 'USB-C Snellader 30W Wit'
    ELSE p.model
  END,
  'Premium refurbished – gecontroleerd en betrouwbaar',
  'Professioneel gerefurbisht apparaat met volledige functionaliteitscontrole. Inclusief 3 jaar garantie en 30 dagen retourrecht. Direct leverbaar.'
FROM products p
WHERE p.sku IN (
  'IPH15PRO-256-BLK','IPH14-128-PUR','IPH13-256-MNT','S24U-256-TI','S23-128-GRN',
  'PIX8P-128-BLK','OP12-256-BLK','XIR13-256-BLU','MBA13-M3-512-SL','MBP14-M3P-1TB-SG',
  'XPS13-1TB-BK','T14S-512-BK','IPAIR6-256-BLU','IPRO13-512-SL','STAB8U-256-GR',
  'GW7-44-BLK','AW9-45-BLK','WH1000XM5-BLK','BOSE-QC45-WHT','USBC-30W-WHT'
)
ON DUPLICATE KEY UPDATE name=VALUES(name), short_description=VALUES(short_description), long_description=VALUES(long_description);

-- DE Product translations
INSERT INTO product_translations (product_id, lang_code, name, short_description, long_description)
SELECT p.id, 'de',
  CASE p.sku
    WHEN 'IPH15PRO-256-BLK'  THEN 'iPhone 15 Pro 256GB Schwarz'
    WHEN 'IPH14-128-PUR'     THEN 'iPhone 14 128GB Violett'
    WHEN 'IPH13-256-MNT'     THEN 'iPhone 13 256GB Mitternacht'
    WHEN 'S24U-256-TI'       THEN 'Samsung Galaxy S24 Ultra 256GB Titan'
    WHEN 'S23-128-GRN'       THEN 'Samsung Galaxy S23 128GB Grün'
    WHEN 'PIX8P-128-BLK'     THEN 'Google Pixel 8 Pro 128GB Obsidian'
    WHEN 'OP12-256-BLK'      THEN 'OnePlus 12 256GB Schwarz'
    WHEN 'XIR13-256-BLU'     THEN 'Xiaomi Redmi Note 13 Pro 256GB Blau'
    WHEN 'MBA13-M3-512-SL'   THEN 'MacBook Air 13 M3 512GB Silber'
    WHEN 'MBP14-M3P-1TB-SG'  THEN 'MacBook Pro 14 M3 Pro 1TB Space Grau'
    WHEN 'XPS13-1TB-BK'      THEN 'Dell XPS 13 1TB Schwarz'
    WHEN 'T14S-512-BK'       THEN 'Lenovo ThinkPad T14s 512GB Schwarz'
    WHEN 'IPAIR6-256-BLU'    THEN 'iPad Air 6 256GB Blau'
    WHEN 'IPRO13-512-SL'     THEN 'iPad Pro 13 M4 512GB Silber'
    WHEN 'STAB8U-256-GR'     THEN 'Samsung Galaxy Tab S8 Ultra 256GB Graphit'
    WHEN 'GW7-44-BLK'        THEN 'Samsung Galaxy Watch 7 44mm Schwarz'
    WHEN 'AW9-45-BLK'        THEN 'Apple Watch Series 9 45mm Mitternacht'
    WHEN 'WH1000XM5-BLK'     THEN 'Sony WH-1000XM5 Schwarz'
    WHEN 'BOSE-QC45-WHT'     THEN 'Bose QuietComfort 45 Weiss'
    WHEN 'USBC-30W-WHT'      THEN 'USB-C Schnellladegerät 30W Weiss'
    ELSE p.model
  END,
  'Premium refurbished – geprüft und zuverlässig',
  'Professionell aufgearbeitetes Gerät mit vollständiger Funktionskontrolle. Inklusive 3 Jahre Garantie und 30 Tage Rückgaberecht.'
FROM products p
WHERE p.sku IN (
  'IPH15PRO-256-BLK','IPH14-128-PUR','IPH13-256-MNT','S24U-256-TI','S23-128-GRN',
  'PIX8P-128-BLK','OP12-256-BLK','XIR13-256-BLU','MBA13-M3-512-SL','MBP14-M3P-1TB-SG',
  'XPS13-1TB-BK','T14S-512-BK','IPAIR6-256-BLU','IPRO13-512-SL','STAB8U-256-GR',
  'GW7-44-BLK','AW9-45-BLK','WH1000XM5-BLK','BOSE-QC45-WHT','USBC-30W-WHT'
)
ON DUPLICATE KEY UPDATE name=VALUES(name), short_description=VALUES(short_description), long_description=VALUES(long_description);

-- FR Product translations
INSERT INTO product_translations (product_id, lang_code, name, short_description, long_description)
SELECT p.id, 'fr',
  CASE p.sku
    WHEN 'IPH15PRO-256-BLK'  THEN 'iPhone 15 Pro 256Go Noir'
    WHEN 'IPH14-128-PUR'     THEN 'iPhone 14 128Go Violet'
    WHEN 'IPH13-256-MNT'     THEN 'iPhone 13 256Go Minuit'
    WHEN 'S24U-256-TI'       THEN 'Samsung Galaxy S24 Ultra 256Go Titanium'
    WHEN 'S23-128-GRN'       THEN 'Samsung Galaxy S23 128Go Vert'
    WHEN 'PIX8P-128-BLK'     THEN 'Google Pixel 8 Pro 128Go Obsidian'
    WHEN 'OP12-256-BLK'      THEN 'OnePlus 12 256Go Noir'
    WHEN 'XIR13-256-BLU'     THEN 'Xiaomi Redmi Note 13 Pro 256Go Bleu'
    WHEN 'MBA13-M3-512-SL'   THEN 'MacBook Air 13 M3 512Go Argent'
    WHEN 'MBP14-M3P-1TB-SG'  THEN 'MacBook Pro 14 M3 Pro 1To Gris Sidéral'
    WHEN 'XPS13-1TB-BK'      THEN 'Dell XPS 13 1To Noir'
    WHEN 'T14S-512-BK'       THEN 'Lenovo ThinkPad T14s 512Go Noir'
    WHEN 'IPAIR6-256-BLU'    THEN 'iPad Air 6 256Go Bleu'
    WHEN 'IPRO13-512-SL'     THEN 'iPad Pro 13 M4 512Go Argent'
    WHEN 'STAB8U-256-GR'     THEN 'Samsung Galaxy Tab S8 Ultra 256Go Graphite'
    WHEN 'GW7-44-BLK'        THEN 'Samsung Galaxy Watch 7 44mm Noir'
    WHEN 'AW9-45-BLK'        THEN 'Apple Watch Série 9 45mm Minuit'
    WHEN 'WH1000XM5-BLK'     THEN 'Sony WH-1000XM5 Noir'
    WHEN 'BOSE-QC45-WHT'     THEN 'Bose QuietComfort 45 Blanc'
    WHEN 'USBC-30W-WHT'      THEN 'Chargeur USB-C 30W Blanc'
    ELSE p.model
  END,
  'Premium reconditionné – contrôlé et fiable',
  'Appareil reconditionnée professionnellement avec contrôle complet des fonctionnalités. Inclus 3 ans de garantie et 30 jours de retour.'
FROM products p
WHERE p.sku IN (
  'IPH15PRO-256-BLK','IPH14-128-PUR','IPH13-256-MNT','S24U-256-TI','S23-128-GRN',
  'PIX8P-128-BLK','OP12-256-BLK','XIR13-256-BLU','MBA13-M3-512-SL','MBP14-M3P-1TB-SG',
  'XPS13-1TB-BK','T14S-512-BK','IPAIR6-256-BLU','IPRO13-512-SL','STAB8U-256-GR',
  'GW7-44-BLK','AW9-45-BLK','WH1000XM5-BLK','BOSE-QC45-WHT','USBC-30W-WHT'
)
ON DUPLICATE KEY UPDATE name=VALUES(name), short_description=VALUES(short_description), long_description=VALUES(long_description);

-- ────────────────────────────────────────────────────────────────────────────
-- 7. PRODUCT VIEW SETTINGS
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS product_view_settings (
  id               INT          AUTO_INCREMENT PRIMARY KEY,
  default_view_mode VARCHAR(20) NOT NULL DEFAULT 'grid',
  items_per_page   INT          NOT NULL DEFAULT 12,
  show_filters     TINYINT(1)   NOT NULL DEFAULT 1,
  show_sort        TINYINT(1)   NOT NULL DEFAULT 1,
  created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO product_view_settings (id, default_view_mode, items_per_page, show_filters, show_sort)
VALUES (1, 'grid', 12, 1, 1);

-- ────────────────────────────────────────────────────────────────────────────
-- 8. SECTION VISIBILITY
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS section_visibility (
  id          INT          AUTO_INCREMENT PRIMARY KEY,
  section_key VARCHAR(120) NOT NULL UNIQUE,
  label       VARCHAR(160) NOT NULL,
  is_visible  TINYINT(1)   NOT NULL DEFAULT 1,
  updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO section_visibility (section_key, label, is_visible) VALUES
('home.hero',             'Home – Hero Banner',          1),
('home.categories',       'Home – Categorieën',          1),
('home.featured_products','Home – Uitgelichte producten', 1),
('home.testimonials',     'Home – Beoordelingen',        1),
('home.newsletter',       'Home – Nieuwsbrief',          1),
('sell.flow',             'Verkopen – Flow',             1),
('trade.flow',            'Inruilen – Flow',             1),
('products.grid',         'Producten – Productgrid',     1),
('products.header',       'Producten – Paginakop',       1);

-- ────────────────────────────────────────────────────────────────────────────
-- 9. QUOTES  (sell / buyback flow)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS quotes (
  id                      BIGINT        AUTO_INCREMENT PRIMARY KEY,
  quote_reference         VARCHAR(40)   NOT NULL UNIQUE,
  customer_name           VARCHAR(150)  NULL,
  customer_email          VARCHAR(180)  NULL,
  customer_phone          VARCHAR(50)   NULL,
  device_key              VARCHAR(100)  NOT NULL,
  storage_label           VARCHAR(20)   NOT NULL,
  condition_key           VARCHAR(100)  NOT NULL,
  selected_defects_json   JSON          NOT NULL,
  selected_cosmetics_json JSON          NOT NULL,
  selected_risks_json     JSON          NOT NULL,
  selected_bonuses_json   JSON          NOT NULL,
  base_price              DECIMAL(10,2) NOT NULL,
  condition_factor        DECIMAL(5,2)  NOT NULL,
  defects_total           DECIMAL(10,2) NOT NULL,
  cosmetics_total         DECIMAL(10,2) NOT NULL,
  bonuses_total           DECIMAL(10,2) NOT NULL,
  final_offer             DECIMAL(10,2) NOT NULL,
  manual_review_required  TINYINT(1)    NOT NULL DEFAULT 0,
  status                  VARCHAR(30)   NOT NULL DEFAULT 'new',
  notes                   TEXT          NULL,
  created_at              TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 10. CUSTOMERS
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS customers (
  id            BIGINT       AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(180) NULL,
  email         VARCHAR(180) NOT NULL UNIQUE,
  phone         VARCHAR(60)  NULL,
  password_hash VARCHAR(255) NULL,
  created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 11. ORDERS
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS orders (
  id               BIGINT        AUTO_INCREMENT PRIMARY KEY,
  order_reference  VARCHAR(40)   NOT NULL UNIQUE,
  customer_id      BIGINT        NOT NULL,
  status           VARCHAR(40)   NOT NULL DEFAULT 'new',
  payment_method   VARCHAR(60)   NULL,
  shipping_method  VARCHAR(60)   NULL,
  shipping_address TEXT          NULL,
  subtotal         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  shipping_cost    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  tax_amount       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_amount     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  notes            TEXT          NULL,
  created_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id           BIGINT        AUTO_INCREMENT PRIMARY KEY,
  order_id     BIGINT        NOT NULL,
  product_id   BIGINT        NULL,
  product_name VARCHAR(220)  NOT NULL,
  sku          VARCHAR(120)  NULL,
  quantity     INT           NOT NULL DEFAULT 1,
  unit_price   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  line_total   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  meta_json    JSON          NULL,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_status_history (
  id          BIGINT       AUTO_INCREMENT PRIMARY KEY,
  order_id    BIGINT       NOT NULL,
  status      VARCHAR(40)  NOT NULL,
  changed_by  VARCHAR(120) NULL,
  created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_osh_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 12. CART SNAPSHOTS
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cart_snapshots (
  id                  BIGINT        AUTO_INCREMENT PRIMARY KEY,
  snapshot_reference  VARCHAR(50)   NOT NULL UNIQUE,
  customer_email      VARCHAR(180)  NULL,
  items_json          JSON          NOT NULL,
  subtotal            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  currency            VARCHAR(10)   NOT NULL DEFAULT 'EUR',
  created_at          TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 13. WISHLIST ITEMS  (stored server-side per customer)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS wishlist_items (
  id          BIGINT    AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT    NOT NULL,
  product_id  BIGINT    NOT NULL,
  added_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_wishlist (customer_id, product_id),
  CONSTRAINT fk_wish_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  CONSTRAINT fk_wish_product  FOREIGN KEY (product_id)  REFERENCES products(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 14. APP TRANSLATIONS  (UI strings, managed via admin or import)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS app_translations (
  id                BIGINT       AUTO_INCREMENT PRIMARY KEY,
  translation_key   VARCHAR(190) NOT NULL,
  lang_code         VARCHAR(5)   NOT NULL,
  translation_value TEXT         NOT NULL,
  UNIQUE KEY uq_translation_lang (translation_key, lang_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- SAFE COLUMN MIGRATIONS  (idempotent – add only if column is missing)
-- These handle databases that were created from older partial migrations.
-- ────────────────────────────────────────────────────────────────────────────
SET @db = DATABASE();

-- calculation_settings.global_reduction_percent
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='calculation_settings' AND COLUMN_NAME='global_reduction_percent');
SET @sql = IF(@col_exists=0,
  'ALTER TABLE calculation_settings ADD COLUMN global_reduction_percent DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER min_price',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- products.dynamic_adjust_percent
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND COLUMN_NAME='dynamic_adjust_percent');
SET @sql = IF(@col_exists=0,
  'ALTER TABLE products ADD COLUMN dynamic_adjust_percent DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER price',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- products.ram_gb / camera_mp / battery_mah / screen_size_in / chipset
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND COLUMN_NAME='ram_gb');
SET @sql = IF(@col_exists=0,
  'ALTER TABLE products ADD COLUMN ram_gb INT NULL AFTER storage_label, ADD COLUMN camera_mp INT NULL AFTER ram_gb, ADD COLUMN battery_mah INT NULL AFTER camera_mp, ADD COLUMN screen_size_in DECIMAL(4,2) NULL AFTER battery_mah, ADD COLUMN chipset VARCHAR(120) NULL AFTER screen_size_in',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- customers.password_hash
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='customers' AND COLUMN_NAME='password_hash');
SET @sql = IF(@col_exists=0,
  'ALTER TABLE customers ADD COLUMN password_hash VARCHAR(255) NULL AFTER phone',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- products.category_id FK (in case products was created before categories)
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND CONSTRAINT_NAME='fk_products_category');
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND COLUMN_NAME='category_id');
SET @sql = IF(@fk_exists=0 AND @col_exists>0,
  'ALTER TABLE products ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET FOREIGN_KEY_CHECKS = 1;

-- ────────────────────────────────────────────────────────────────────────────
-- Link any products with NULL category_id to their category by product_type
-- ────────────────────────────────────────────────────────────────────────────
UPDATE products p
JOIN categories c ON c.category_key = CASE p.product_type
    WHEN 'smartphone'  THEN 'smartphones'
    WHEN 'laptop'      THEN 'laptops'
    WHEN 'tablet'      THEN 'tablets'
    WHEN 'smartwatch'  THEN 'smartwatches'
    WHEN 'headphone'   THEN 'headphones'
    WHEN 'accessory'   THEN 'accessories'
    ELSE ''
  END
SET p.category_id = c.id
WHERE p.category_id IS NULL;

-- ═══════════════════════════════════════════════════════════════════════════
--  DONE – Database is now fully set up.
--  Admin login: username=admin  password=admin123
-- ═══════════════════════════════════════════════════════════════════════════
