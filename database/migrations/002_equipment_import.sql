ALTER TABLE equipment
  ADD COLUMN serial_no VARCHAR(100) NULL AFTER asset_code,
  ADD COLUMN owner_name VARCHAR(255) NULL AFTER name,
  ADD COLUMN condition_remarks TEXT NULL AFTER owner_name;