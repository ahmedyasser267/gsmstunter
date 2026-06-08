USE gsmstunter;

CREATE TABLE IF NOT EXISTS products (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(120) NOT NULL UNIQUE,
  product_type VARCHAR(60) NOT NULL DEFAULT 'smartphone',
  brand VARCHAR(80) NOT NULL,
  model VARCHAR(150) NOT NULL,
  storage_label VARCHAR(30) NULL,
  color VARCHAR(60) NULL,
  condition_key VARCHAR(100) NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  old_price DECIMAL(10,2) NULL,
  stock_qty INT NOT NULL DEFAULT 0,
  image_url VARCHAR(400) NULL,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS product_translations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  lang_code VARCHAR(5) NOT NULL,
  name VARCHAR(180) NOT NULL,
  short_description TEXT NULL,
  long_description TEXT NULL,
  UNIQUE KEY uq_product_lang (product_id, lang_code),
  CONSTRAINT fk_pt_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS section_visibility (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section_key VARCHAR(120) NOT NULL UNIQUE,
  label VARCHAR(160) NOT NULL,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trade_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trade_bonus_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
  exchange_bonus_value DECIMAL(10,2) NOT NULL DEFAULT 0,
  min_trade_price DECIMAL(10,2) NOT NULL DEFAULT 20,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS app_translations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  translation_key VARCHAR(190) NOT NULL,
  lang_code VARCHAR(5) NOT NULL,
  translation_value TEXT NOT NULL,
  UNIQUE KEY uq_translation_lang (translation_key, lang_code)
);

INSERT IGNORE INTO section_visibility (section_key, label, is_visible) VALUES
('home.hero', 'Home Hero', 1),
('home.categories', 'Home Categories', 1),
('home.featured_products', 'Home Featured Products', 1),
('home.testimonials', 'Home Testimonials', 1),
('home.newsletter', 'Home Newsletter', 1),
('sell.flow', 'Sell Flow', 1),
('trade.flow', 'Trade Flow', 1),
('products.grid', 'Products Grid', 1);

INSERT IGNORE INTO trade_settings (id, trade_bonus_percent, exchange_bonus_value, min_trade_price)
VALUES (1, 5.00, 10.00, 20.00);

