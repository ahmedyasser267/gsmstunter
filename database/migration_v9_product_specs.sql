USE gsmstunter;

ALTER TABLE products
  ADD COLUMN IF NOT EXISTS ram_gb INT NULL AFTER storage_label,
  ADD COLUMN IF NOT EXISTS camera_mp INT NULL AFTER ram_gb,
  ADD COLUMN IF NOT EXISTS battery_mah INT NULL AFTER camera_mp,
  ADD COLUMN IF NOT EXISTS screen_size_in DECIMAL(4,2) NULL AFTER battery_mah,
  ADD COLUMN IF NOT EXISTS chipset VARCHAR(120) NULL AFTER screen_size_in;

