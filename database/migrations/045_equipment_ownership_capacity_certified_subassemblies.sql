SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE equipment_assemblies
 ADD COLUMN serial_number VARCHAR(100) NULL AFTER description,
 ADD COLUMN range_text VARCHAR(150) NULL AFTER serial_number;

ALTER TABLE qhse_pressure_test_items
 ADD COLUMN assembly_id CHAR(36) NULL AFTER equipment_id,
 ADD COLUMN test_pressure VARCHAR(100) NULL AFTER range_text,
 ADD CONSTRAINT qhse_pressure_item_assembly_fk FOREIGN KEY(assembly_id) REFERENCES equipment_assemblies(id) ON DELETE SET NULL;
