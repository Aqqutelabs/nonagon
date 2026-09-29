CREATE TABLE equipment_measurement_units (
 id CHAR(36) PRIMARY KEY,
 owner_id CHAR(36) NULL,
 code VARCHAR(30) NOT NULL,
 label VARCHAR(80) NOT NULL,
 unit_group VARCHAR(40) NOT NULL DEFAULT 'GENERAL',
 scope_key VARCHAR(36) AS (IFNULL(owner_id,'GLOBAL')) STORED,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY equipment_unit_code(scope_key,code),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE
) ENGINE=InnoDB;
INSERT INTO equipment_measurement_units(id,owner_id,code,label,unit_group) VALUES
('51000000-0000-4000-8000-000000000001',NULL,'IN','Inches','SIZE'),
('51000000-0000-4000-8000-000000000002',NULL,'MM','Millimetres','SIZE'),
('51000000-0000-4000-8000-000000000003',NULL,'CM','Centimetres','SIZE'),
('51000000-0000-4000-8000-000000000004',NULL,'M','Metres','SIZE'),
('51000000-0000-4000-8000-000000000005',NULL,'PSI','Pounds per square inch','PRESSURE'),
('51000000-0000-4000-8000-000000000006',NULL,'BAR','Bar','PRESSURE'),
('51000000-0000-4000-8000-000000000007',NULL,'KPA','Kilopascals','PRESSURE'),
('51000000-0000-4000-8000-000000000008',NULL,'MPA','Megapascals','PRESSURE');
ALTER TABLE equipment
 ADD COLUMN equipment_size DECIMAL(18,6) NULL AFTER nominal_size,
 ADD COLUMN equipment_size_unit VARCHAR(30) NULL AFTER equipment_size,
 ADD COLUMN equipment_owner_user_id CHAR(36) NULL AFTER equipment_size_unit,
 ADD COLUMN equipment_owner_customer_id CHAR(36) NULL AFTER equipment_owner_user_id,
 ADD CONSTRAINT equipment_owner_user_fk FOREIGN KEY(equipment_owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
 ADD CONSTRAINT equipment_owner_customer_fk FOREIGN KEY(equipment_owner_customer_id) REFERENCES commercial_customers(id) ON DELETE SET NULL;
