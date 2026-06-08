CREATE DATABASE IF NOT EXISTS gsmstunter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gsmstunter;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  device_key VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS device_storage_prices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  device_id INT NOT NULL,
  storage_label VARCHAR(20) NOT NULL,
  base_price DECIMAL(10,2) NOT NULL,
  UNIQUE KEY uq_device_storage (device_id, storage_label),
  CONSTRAINT fk_dsp_device FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS conditions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  condition_key VARCHAR(100) NOT NULL UNIQUE,
  label VARCHAR(150) NOT NULL,
  factor DECIMAL(5,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS defects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  defect_key VARCHAR(100) NOT NULL UNIQUE,
  label VARCHAR(150) NOT NULL,
  deduction DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS cosmetics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cosmetic_key VARCHAR(100) NOT NULL UNIQUE,
  label VARCHAR(150) NOT NULL,
  deduction DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS risk_rules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  risk_key VARCHAR(100) NOT NULL UNIQUE,
  label VARCHAR(150) NOT NULL,
  action_type VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS bonuses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bonus_key VARCHAR(100) NOT NULL UNIQUE,
  label VARCHAR(200) NOT NULL,
  value DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS calculation_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  min_price DECIMAL(10,2) NOT NULL DEFAULT 30,
  rounding_rule VARCHAR(30) NOT NULL DEFAULT 'nearest_5',
  currency VARCHAR(10) NOT NULL DEFAULT 'EUR'
);

CREATE TABLE IF NOT EXISTS quotes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  quote_reference VARCHAR(40) NOT NULL UNIQUE,
  customer_name VARCHAR(150) NULL,
  customer_email VARCHAR(180) NULL,
  customer_phone VARCHAR(50) NULL,
  device_key VARCHAR(100) NOT NULL,
  storage_label VARCHAR(20) NOT NULL,
  condition_key VARCHAR(100) NOT NULL,
  selected_defects_json JSON NOT NULL,
  selected_cosmetics_json JSON NOT NULL,
  selected_risks_json JSON NOT NULL,
  selected_bonuses_json JSON NOT NULL,
  base_price DECIMAL(10,2) NOT NULL,
  condition_factor DECIMAL(5,2) NOT NULL,
  defects_total DECIMAL(10,2) NOT NULL,
  cosmetics_total DECIMAL(10,2) NOT NULL,
  bonuses_total DECIMAL(10,2) NOT NULL,
  final_offer DECIMAL(10,2) NOT NULL,
  manual_review_required TINYINT(1) NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'new',
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO admins (id, username, password_hash) VALUES
(1, 'admin', '$2y$10$8Gf8o8h6TRprvaw2uE9u2ecfB30ZfL/Y6hX7kJXwD7V3oRKYt8q1K');
-- password: admin123

INSERT IGNORE INTO devices (device_key, name) VALUES
('iphone_17', 'iPhone 17'),
('iphone_16', 'iPhone 16'),
('iphone_15', 'iPhone 15'),
('iphone_15_pro', 'iPhone 15 Pro'),
('iphone_14', 'iPhone 14'),
('iphone_14_pro', 'iPhone 14 Pro'),
('iphone_13', 'iPhone 13'),
('iphone_13_pro', 'iPhone 13 Pro'),
('iphone_12', 'iPhone 12'),
('iphone_11', 'iPhone 11');

INSERT IGNORE INTO device_storage_prices (device_id, storage_label, base_price)
SELECT d.id, s.storage_label, s.base_price
FROM devices d
JOIN (
  SELECT 'iphone_17' AS dk, '128' AS storage_label, 950 AS base_price UNION ALL
  SELECT 'iphone_17', '256', 1020 UNION ALL
  SELECT 'iphone_17', '512', 1100 UNION ALL
  SELECT 'iphone_16', '128', 880 UNION ALL
  SELECT 'iphone_16', '256', 940 UNION ALL
  SELECT 'iphone_16', '512', 1000 UNION ALL
  SELECT 'iphone_15', '128', 700 UNION ALL
  SELECT 'iphone_15', '256', 750 UNION ALL
  SELECT 'iphone_15', '512', 820 UNION ALL
  SELECT 'iphone_15_pro', '128', 780 UNION ALL
  SELECT 'iphone_15_pro', '256', 830 UNION ALL
  SELECT 'iphone_15_pro', '512', 900 UNION ALL
  SELECT 'iphone_14', '128', 500 UNION ALL
  SELECT 'iphone_14', '256', 550 UNION ALL
  SELECT 'iphone_14', '512', 600 UNION ALL
  SELECT 'iphone_14_pro', '128', 650 UNION ALL
  SELECT 'iphone_14_pro', '256', 700 UNION ALL
  SELECT 'iphone_14_pro', '512', 760 UNION ALL
  SELECT 'iphone_13', '128', 380 UNION ALL
  SELECT 'iphone_13', '256', 420 UNION ALL
  SELECT 'iphone_13', '512', 460 UNION ALL
  SELECT 'iphone_13_pro', '128', 420 UNION ALL
  SELECT 'iphone_13_pro', '256', 460 UNION ALL
  SELECT 'iphone_13_pro', '512', 500 UNION ALL
  SELECT 'iphone_12', '64', 240 UNION ALL
  SELECT 'iphone_12', '128', 270 UNION ALL
  SELECT 'iphone_12', '256', 300 UNION ALL
  SELECT 'iphone_11', '64', 180 UNION ALL
  SELECT 'iphone_11', '128', 210 UNION ALL
  SELECT 'iphone_11', '256', 240
) s ON s.dk = d.device_key;

INSERT IGNORE INTO conditions (condition_key, label, factor) VALUES
('als_nieuw', 'Als nieuw', 1.00),
('zeer_goed', 'Zeer goed', 0.92),
('goed', 'Goed', 0.85),
('beschadigd', 'Beschadigd', 0.70);

INSERT IGNORE INTO defects (defect_key, label, deduction) VALUES
('battery_weak', 'Batterij onder 80%', 20),
('screen_broken', 'Scherm gebroken', 80),
('back_glass_broken', 'Achterkant kapot', 50),
('faceid_not_working', 'Face ID werkt niet', 60),
('camera_not_working', 'Camera werkt niet', 40),
('charging_issue', 'Laadprobleem', 30);

INSERT IGNORE INTO cosmetics (cosmetic_key, label, deduction) VALUES
('heavy_scratches', 'Diepe krassen', 20),
('frame_damage', 'Frame schade', 30);

INSERT IGNORE INTO risk_rules (risk_key, label, action_type) VALUES
('icloud_locked', 'iCloud lock actief', 'manual_review'),
('no_power', 'Toestel start niet op', 'manual_review'),
('water_damage_major', 'Ernstige waterschade', 'manual_review'),
('imei_blacklist', 'IMEI geblokkeerd', 'manual_review');

INSERT IGNORE INTO bonuses (bonus_key, label, value) VALUES
('store_credit', 'Ontvang €10 extra als tegoed in de winkel', 10);

INSERT IGNORE INTO calculation_settings (id, min_price, rounding_rule, currency)
VALUES (1, 30, 'nearest_5', 'EUR');

