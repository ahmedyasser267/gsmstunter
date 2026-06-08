USE gsmstunter;

/* Link legacy products to categories when category_id is missing */
UPDATE products p
JOIN categories c
  ON c.category_key = CASE p.product_type
    WHEN 'smartphone' THEN 'smartphones'
    WHEN 'laptop' THEN 'laptops'
    WHEN 'tablet' THEN 'tablets'
    WHEN 'smartwatch' THEN 'smartwatches'
    WHEN 'headphone' THEN 'headphones'
    WHEN 'accessory' THEN 'accessories'
    ELSE ''
  END
SET p.category_id = c.id
WHERE p.category_id IS NULL;

/* Seed base products (idempotent by SKU) */
INSERT INTO products (sku, product_type, category_id, brand, model, storage_label, color, condition_key, price, dynamic_adjust_percent, old_price, stock_qty, image_url, is_visible, sort_order)
SELECT 'IPH15PRO-256-BLK','smartphone',c.id,'Apple','iPhone 15 Pro','256GB','Black','excellent',999.00,0.00,1149.00,18,'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&h=800&fit=crop&q=80',1,10
FROM categories c WHERE c.category_key='smartphones' AND NOT EXISTS (SELECT 1 FROM products p WHERE p.sku='IPH15PRO-256-BLK');

INSERT INTO products (sku, product_type, category_id, brand, model, storage_label, color, condition_key, price, dynamic_adjust_percent, old_price, stock_qty, image_url, is_visible, sort_order)
SELECT 'S24U-256-TI','smartphone',c.id,'Samsung','Galaxy S24 Ultra','256GB','Titanium Gray','excellent',879.00,0.00,1029.00,14,'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&h=800&fit=crop&q=80',1,20
FROM categories c WHERE c.category_key='smartphones' AND NOT EXISTS (SELECT 1 FROM products p WHERE p.sku='S24U-256-TI');

INSERT INTO products (sku, product_type, category_id, brand, model, storage_label, color, condition_key, price, dynamic_adjust_percent, old_price, stock_qty, image_url, is_visible, sort_order)
SELECT 'MBA13-M3-512-SL','laptop',c.id,'Apple','MacBook Air 13 M3','512GB','Silver','excellent',1249.00,0.00,1449.00,9,'https://images.unsplash.com/photo-1517336714739-489689fd1ca8?w=800&h=800&fit=crop&q=80',1,30
FROM categories c WHERE c.category_key='laptops' AND NOT EXISTS (SELECT 1 FROM products p WHERE p.sku='MBA13-M3-512-SL');

INSERT INTO products (sku, product_type, category_id, brand, model, storage_label, color, condition_key, price, dynamic_adjust_percent, old_price, stock_qty, image_url, is_visible, sort_order)
SELECT 'XPS13-1TB-BK','laptop',c.id,'Dell','XPS 13','1TB','Black','good',1099.00,-2.50,1299.00,11,'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&h=800&fit=crop&q=80',1,40
FROM categories c WHERE c.category_key='laptops' AND NOT EXISTS (SELECT 1 FROM products p WHERE p.sku='XPS13-1TB-BK');

INSERT INTO products (sku, product_type, category_id, brand, model, storage_label, color, condition_key, price, dynamic_adjust_percent, old_price, stock_qty, image_url, is_visible, sort_order)
SELECT 'IPAIR6-256-BLU','tablet',c.id,'Apple','iPad Air 6','256GB','Blue','excellent',669.00,0.00,789.00,16,'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&h=800&fit=crop&q=80',1,50
FROM categories c WHERE c.category_key='tablets' AND NOT EXISTS (SELECT 1 FROM products p WHERE p.sku='IPAIR6-256-BLU');

INSERT INTO products (sku, product_type, category_id, brand, model, storage_label, color, condition_key, price, dynamic_adjust_percent, old_price, stock_qty, image_url, is_visible, sort_order)
SELECT 'GW7-44-BLK','smartwatch',c.id,'Samsung','Galaxy Watch 7','44mm','Black','excellent',259.00,0.00,339.00,25,'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&h=800&fit=crop&q=80',1,60
FROM categories c WHERE c.category_key='smartwatches' AND NOT EXISTS (SELECT 1 FROM products p WHERE p.sku='GW7-44-BLK');

INSERT INTO products (sku, product_type, category_id, brand, model, storage_label, color, condition_key, price, dynamic_adjust_percent, old_price, stock_qty, image_url, is_visible, sort_order)
SELECT 'WH1000XM5-BLK','headphone',c.id,'Sony','WH-1000XM5',NULL,'Black','excellent',279.00,0.00,369.00,20,'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&h=800&fit=crop&q=80',1,70
FROM categories c WHERE c.category_key='headphones' AND NOT EXISTS (SELECT 1 FROM products p WHERE p.sku='WH1000XM5-BLK');

INSERT INTO products (sku, product_type, category_id, brand, model, storage_label, color, condition_key, price, dynamic_adjust_percent, old_price, stock_qty, image_url, is_visible, sort_order)
SELECT 'USBC-30W-WHT','accessory',c.id,'Anker','USB-C Charger 30W',NULL,'White','new',29.00,0.00,39.00,60,'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&h=800&fit=crop&q=80',1,80
FROM categories c WHERE c.category_key='accessories' AND NOT EXISTS (SELECT 1 FROM products p WHERE p.sku='USBC-30W-WHT');

/* NL translations */
INSERT INTO product_translations (product_id, lang_code, name, short_description, long_description)
SELECT p.id,'nl',
  CASE p.sku
    WHEN 'IPH15PRO-256-BLK' THEN 'iPhone 15 Pro 256GB'
    WHEN 'S24U-256-TI' THEN 'Samsung Galaxy S24 Ultra 256GB'
    WHEN 'MBA13-M3-512-SL' THEN 'MacBook Air 13 M3 512GB'
    WHEN 'XPS13-1TB-BK' THEN 'Dell XPS 13 1TB'
    WHEN 'IPAIR6-256-BLU' THEN 'iPad Air 6 256GB'
    WHEN 'GW7-44-BLK' THEN 'Galaxy Watch 7 44mm'
    WHEN 'WH1000XM5-BLK' THEN 'Sony WH-1000XM5'
    WHEN 'USBC-30W-WHT' THEN 'USB-C Snellader 30W'
  END,
  'Premium refurbished',
  'Gecontroleerd, professioneel refurbished en direct leverbaar.'
FROM products p
WHERE p.sku IN ('IPH15PRO-256-BLK','S24U-256-TI','MBA13-M3-512-SL','XPS13-1TB-BK','IPAIR6-256-BLU','GW7-44-BLK','WH1000XM5-BLK','USBC-30W-WHT')
ON DUPLICATE KEY UPDATE
  name=VALUES(name), short_description=VALUES(short_description), long_description=VALUES(long_description);

/* DE translations */
INSERT INTO product_translations (product_id, lang_code, name, short_description, long_description)
SELECT p.id,'de',
  CASE p.sku
    WHEN 'IPH15PRO-256-BLK' THEN 'iPhone 15 Pro 256GB'
    WHEN 'S24U-256-TI' THEN 'Samsung Galaxy S24 Ultra 256GB'
    WHEN 'MBA13-M3-512-SL' THEN 'MacBook Air 13 M3 512GB'
    WHEN 'XPS13-1TB-BK' THEN 'Dell XPS 13 1TB'
    WHEN 'IPAIR6-256-BLU' THEN 'iPad Air 6 256GB'
    WHEN 'GW7-44-BLK' THEN 'Galaxy Watch 7 44mm'
    WHEN 'WH1000XM5-BLK' THEN 'Sony WH-1000XM5'
    WHEN 'USBC-30W-WHT' THEN 'USB-C Ladegeraet 30W'
  END,
  'Premium refurbished',
  'Geprueft, professionell refurbished und sofort lieferbar.'
FROM products p
WHERE p.sku IN ('IPH15PRO-256-BLK','S24U-256-TI','MBA13-M3-512-SL','XPS13-1TB-BK','IPAIR6-256-BLU','GW7-44-BLK','WH1000XM5-BLK','USBC-30W-WHT')
ON DUPLICATE KEY UPDATE
  name=VALUES(name), short_description=VALUES(short_description), long_description=VALUES(long_description);

/* FR translations */
INSERT INTO product_translations (product_id, lang_code, name, short_description, long_description)
SELECT p.id,'fr',
  CASE p.sku
    WHEN 'IPH15PRO-256-BLK' THEN 'iPhone 15 Pro 256Go'
    WHEN 'S24U-256-TI' THEN 'Samsung Galaxy S24 Ultra 256Go'
    WHEN 'MBA13-M3-512-SL' THEN 'MacBook Air 13 M3 512Go'
    WHEN 'XPS13-1TB-BK' THEN 'Dell XPS 13 1To'
    WHEN 'IPAIR6-256-BLU' THEN 'iPad Air 6 256Go'
    WHEN 'GW7-44-BLK' THEN 'Galaxy Watch 7 44mm'
    WHEN 'WH1000XM5-BLK' THEN 'Sony WH-1000XM5'
    WHEN 'USBC-30W-WHT' THEN 'Chargeur USB-C 30W'
  END,
  'Premium reconditionne',
  'Controle, reconditionne professionnel et disponible immediatement.'
FROM products p
WHERE p.sku IN ('IPH15PRO-256-BLK','S24U-256-TI','MBA13-M3-512-SL','XPS13-1TB-BK','IPAIR6-256-BLU','GW7-44-BLK','WH1000XM5-BLK','USBC-30W-WHT')
ON DUPLICATE KEY UPDATE
  name=VALUES(name), short_description=VALUES(short_description), long_description=VALUES(long_description);
