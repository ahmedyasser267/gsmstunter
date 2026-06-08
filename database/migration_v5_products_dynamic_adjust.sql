USE gsmstunter;

ALTER TABLE products
  ADD COLUMN IF NOT EXISTS dynamic_adjust_percent DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER price;

