USE gsmstunter;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_key VARCHAR(120) NOT NULL UNIQUE,
  parent_id INT NULL,
  icon VARCHAR(80) NULL,
  image_url VARCHAR(400) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS category_translations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  lang_code VARCHAR(5) NOT NULL,
  name VARCHAR(160) NOT NULL,
  description TEXT NULL,
  UNIQUE KEY uq_cat_lang (category_id, lang_code),
  CONSTRAINT fk_ct_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

ALTER TABLE products
  ADD COLUMN IF NOT EXISTS category_id INT NULL AFTER product_type;

SET @fk_exists = (
  SELECT COUNT(*)
  FROM information_schema.table_constraints
  WHERE constraint_schema = DATABASE()
    AND table_name = 'products'
    AND constraint_name = 'fk_products_category'
    AND constraint_type = 'FOREIGN KEY'
);
SET @fk_sql = IF(
  @fk_exists = 0,
  'ALTER TABLE products ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE fk_stmt FROM @fk_sql;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

CREATE TABLE IF NOT EXISTS product_view_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  default_view_mode VARCHAR(20) NOT NULL DEFAULT 'grid',
  items_per_page INT NOT NULL DEFAULT 12,
  show_filters TINYINT(1) NOT NULL DEFAULT 1,
  show_sort TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO product_view_settings (id, default_view_mode, items_per_page, show_filters, show_sort)
VALUES (1, 'grid', 12, 1, 1);

INSERT IGNORE INTO categories (id, category_key, parent_id, icon, sort_order, is_visible) VALUES
(1, 'smartphones', NULL, 'fa-mobile-screen-button', 10, 1),
(2, 'laptops', NULL, 'fa-laptop', 20, 1),
(3, 'tablets', NULL, 'fa-tablet-screen-button', 30, 1),
(4, 'smartwatches', NULL, 'fa-clock', 40, 1),
(5, 'headphones', NULL, 'fa-headphones', 50, 1),
(6, 'accessories', NULL, 'fa-plug', 60, 1);

INSERT IGNORE INTO category_translations (category_id, lang_code, name, description) VALUES
(1, 'nl', 'Smartphones', 'Refurbished smartphones'),
(1, 'de', 'Smartphones', 'Refurbished Smartphones'),
(1, 'fr', 'Smartphones', 'Smartphones reconditionnes'),
(2, 'nl', 'Laptops', 'Refurbished laptops'),
(2, 'de', 'Laptops', 'Refurbished Laptops'),
(2, 'fr', 'Ordinateurs portables', 'Ordinateurs reconditionnes'),
(3, 'nl', 'Tablets', 'Refurbished tablets'),
(3, 'de', 'Tablets', 'Refurbished Tablets'),
(3, 'fr', 'Tablettes', 'Tablettes reconditionnees'),
(4, 'nl', 'Smartwatches', 'Refurbished wearables'),
(4, 'de', 'Smartwatches', 'Refurbished Wearables'),
(4, 'fr', 'Montres connectees', 'Montres reconditionnees'),
(5, 'nl', 'Koptelefoons', 'Refurbished audio'),
(5, 'de', 'Kopfhorer', 'Refurbished Audio'),
(5, 'fr', 'Casques', 'Audio reconditionne'),
(6, 'nl', 'Accessoires', 'Accessoires'),
(6, 'de', 'Zubehor', 'Zubehor'),
(6, 'fr', 'Accessoires', 'Accessoires');

