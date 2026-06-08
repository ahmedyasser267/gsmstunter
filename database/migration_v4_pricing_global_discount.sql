USE gsmstunter;

ALTER TABLE calculation_settings
  ADD COLUMN IF NOT EXISTS global_reduction_percent DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER min_price;

