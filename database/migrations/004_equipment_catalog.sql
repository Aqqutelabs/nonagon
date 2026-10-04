ALTER TABLE owners MODIFY name VARCHAR(255) NULL, MODIFY email VARCHAR(255) NULL, MODIFY phone VARCHAR(30) NULL;
ALTER TABLE spaces ADD COLUMN description TEXT NULL;
ALTER TABLE sites ADD COLUMN state VARCHAR(100) NULL, ADD COLUMN lga VARCHAR(100) NULL, ADD COLUMN gps_lat DECIMAL(10,7) NULL, ADD COLUMN gps_lng DECIMAL(10,7) NULL, ADD COLUMN address VARCHAR(500) NULL;
ALTER TABLE plants ADD COLUMN description TEXT NULL, ADD COLUMN gps_lat DECIMAL(10,7) NULL, ADD COLUMN gps_lng DECIMAL(10,7) NULL;
ALTER TABLE units ADD COLUMN description TEXT NULL, ADD COLUMN department_code VARCHAR(100) NULL;
CREATE OR REPLACE VIEW blocks AS SELECT id,owner_id,name,description,is_default AS is_system_default,created_at FROM spaces;
CREATE TABLE equipment_categories (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, industry VARCHAR(100) NOT NULL, name VARCHAR(150) NOT NULL,
 scope_key VARCHAR(36) AS (IFNULL(owner_id,'GLOBAL')) STORED,
 UNIQUE KEY category_name(scope_key,industry,name), FOREIGN KEY(owner_id) REFERENCES owners(id)
) ENGINE=InnoDB;
CREATE TABLE equipment_subcategories (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, category_id CHAR(36) NOT NULL, name VARCHAR(150) NOT NULL,
 artwork_url VARCHAR(500) NULL, scope_key VARCHAR(36) AS (IFNULL(owner_id,'GLOBAL')) STORED,
 UNIQUE KEY subcategory_name(scope_key,category_id,name), FOREIGN KEY(category_id) REFERENCES equipment_categories(id), FOREIGN KEY(owner_id) REFERENCES owners(id)
) ENGINE=InnoDB;
CREATE TABLE equipment_types (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, subcategory_id CHAR(36) NOT NULL, name VARCHAR(150) NOT NULL,
 scope_key VARCHAR(36) AS (IFNULL(owner_id,'GLOBAL')) STORED,
 UNIQUE KEY type_name(scope_key,subcategory_id,name), FOREIGN KEY(subcategory_id) REFERENCES equipment_subcategories(id), FOREIGN KEY(owner_id) REFERENCES owners(id)
) ENGINE=InnoDB;
CREATE TABLE equipment_brands (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, name VARCHAR(150) NOT NULL,
 scope_key VARCHAR(36) AS (IFNULL(owner_id,'GLOBAL')) STORED,
 UNIQUE KEY brand_name(scope_key,name), FOREIGN KEY(owner_id) REFERENCES owners(id)
) ENGINE=InnoDB;
CREATE TABLE equipment_models (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, brand_id CHAR(36) NOT NULL, name VARCHAR(150) NOT NULL,
 scope_key VARCHAR(36) AS (IFNULL(owner_id,'GLOBAL')) STORED,
 UNIQUE KEY model_name(scope_key,brand_id,name), FOREIGN KEY(brand_id) REFERENCES equipment_brands(id), FOREIGN KEY(owner_id) REFERENCES owners(id)
) ENGINE=InnoDB;
CREATE TABLE equipment_statuses (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, name VARCHAR(100) NOT NULL,
 operational_state ENUM('OPERATIONAL','MAINTENANCE','DOWN') NOT NULL, is_default BOOLEAN NOT NULL DEFAULT FALSE,
 UNIQUE KEY status_name(owner_id,name), FOREIGN KEY(owner_id) REFERENCES owners(id)
) ENGINE=InnoDB;
ALTER TABLE equipment
 ADD COLUMN category_id CHAR(36) NULL, ADD COLUMN subcategory_id CHAR(36) NULL, ADD COLUMN type_id CHAR(36) NULL,
 ADD COLUMN short_description VARCHAR(500) NULL, ADD COLUMN long_description TEXT NULL,
 ADD COLUMN status_id CHAR(36) NULL, ADD COLUMN brand_id CHAR(36) NULL, ADD COLUMN model_id CHAR(36) NULL,
 ADD COLUMN manufacture_year SMALLINT UNSIGNED NULL, ADD COLUMN photo_primary_url VARCHAR(500) NULL,
 ADD COLUMN aid VARCHAR(100) AS (asset_code) VIRTUAL, ADD COLUMN emd VARCHAR(100) AS (serial_no) VIRTUAL,
 ADD FOREIGN KEY(category_id) REFERENCES equipment_categories(id), ADD FOREIGN KEY(subcategory_id) REFERENCES equipment_subcategories(id),
 ADD FOREIGN KEY(type_id) REFERENCES equipment_types(id), ADD FOREIGN KEY(status_id) REFERENCES equipment_statuses(id),
 ADD FOREIGN KEY(brand_id) REFERENCES equipment_brands(id), ADD FOREIGN KEY(model_id) REFERENCES equipment_models(id);
CREATE TABLE equipment_photos (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, url VARCHAR(500) NOT NULL, caption VARCHAR(255) NULL,
 is_primary BOOLEAN NOT NULL DEFAULT FALSE, mime_type VARCHAR(30) NOT NULL, image_data MEDIUMBLOB NOT NULL,
 primary_equipment CHAR(36) AS (IF(is_primary=1,equipment_id,NULL)) STORED,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY primary_photo(primary_equipment),
 CONSTRAINT fk_equipment_photos_equipment FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE equipment_depreciation_profile (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL UNIQUE, method ENUM('STRAIGHT_LINE') NOT NULL DEFAULT 'STRAIGHT_LINE',
 currency CHAR(3) NOT NULL DEFAULT 'NGN', acquisition_cost DECIMAL(14,2) NOT NULL, acquisition_date DATE NOT NULL,
 useful_life_years DECIMAL(8,3) NOT NULL, salvage_value DECIMAL(14,2) NOT NULL DEFAULT 0, start_date DATE NOT NULL,
 is_active BOOLEAN NOT NULL DEFAULT TRUE, notes TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CHECK(acquisition_cost>=0 AND useful_life_years>0 AND salvage_value>=0 AND salvage_value<=acquisition_cost),
 FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE equipment_value_snapshot (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, as_of_date DATE NOT NULL,
 value_type ENUM('PURCHASE','BOOK','MARKET','BOOK_ESTIMATE_AI','MARKET_ESTIMATE_AI') NOT NULL,
 amount DECIMAL(14,2) NOT NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN',
 source_type ENUM('SYSTEM','USER','AI_AGENT','EXTERNAL_API') NOT NULL, source_detail TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX value_history(equipment_id,as_of_date,value_type), CHECK(amount>=0),
 FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE equipment_value_current (
 equipment_id CHAR(36) PRIMARY KEY, current_book_value DECIMAL(14,2) NULL, current_book_value_source_type VARCHAR(20) NULL,
 current_book_value_source_detail TEXT NULL, current_market_value DECIMAL(14,2) NULL,
 current_market_value_source_type VARCHAR(20) NULL, current_market_value_source_detail TEXT NULL,
 last_calculated_at DATETIME NOT NULL, FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE part_master (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, name VARCHAR(255) NOT NULL, description TEXT NULL,
 manufacturer_part_number VARCHAR(150) NULL, oem_part_number VARCHAR(150) NULL, brand_id CHAR(36) NULL, model_id CHAR(36) NULL,
 unit_of_measure VARCHAR(20) NOT NULL DEFAULT 'PCS', function_name VARCHAR(100) NULL,
 is_serviceable BOOLEAN NOT NULL DEFAULT FALSE, is_consumable BOOLEAN NOT NULL DEFAULT FALSE,
 is_global BOOLEAN NOT NULL DEFAULT FALSE, is_verified BOOLEAN NOT NULL DEFAULT FALSE, created_by CHAR(36) NOT NULL,
 FOREIGN KEY(owner_id) REFERENCES owners(id), FOREIGN KEY(brand_id) REFERENCES equipment_brands(id), FOREIGN KEY(model_id) REFERENCES equipment_models(id)
) ENGINE=InnoDB;
CREATE TABLE assembly_templates (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, name VARCHAR(255) NOT NULL, description TEXT NULL,
 equipment_type_id CHAR(36) NOT NULL, brand_id CHAR(36) NULL, model_id CHAR(36) NULL,
 is_global BOOLEAN NOT NULL DEFAULT FALSE, is_verified BOOLEAN NOT NULL DEFAULT FALSE, created_by CHAR(36) NOT NULL,
 FOREIGN KEY(owner_id) REFERENCES owners(id), FOREIGN KEY(equipment_type_id) REFERENCES equipment_types(id),
 FOREIGN KEY(brand_id) REFERENCES equipment_brands(id), FOREIGN KEY(model_id) REFERENCES equipment_models(id)
) ENGINE=InnoDB;
CREATE TABLE assembly_template_items (
 id CHAR(36) PRIMARY KEY, assembly_template_id CHAR(36) NOT NULL, part_id CHAR(36) NULL, subassembly_id CHAR(36) NULL,
 quantity DECIMAL(12,3) NOT NULL DEFAULT 1, sequence INT NOT NULL DEFAULT 0, is_required BOOLEAN NOT NULL DEFAULT TRUE,
 is_custom_slot BOOLEAN NOT NULL DEFAULT FALSE, notes VARCHAR(500) NULL,
 CHECK(quantity>0), CHECK((part_id IS NOT NULL)+(subassembly_id IS NOT NULL)+is_custom_slot=1),
 FOREIGN KEY(assembly_template_id) REFERENCES assembly_templates(id), FOREIGN KEY(part_id) REFERENCES part_master(id),
 FOREIGN KEY(subassembly_id) REFERENCES assembly_templates(id)
) ENGINE=InnoDB;
CREATE TABLE equipment_assemblies (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, parent_assembly_id CHAR(36) NULL,
 name VARCHAR(255) NOT NULL, description TEXT NULL, sequence INT NOT NULL DEFAULT 0,
 template_id CHAR(36) NULL, is_custom_addon BOOLEAN NOT NULL DEFAULT FALSE,
 FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE, FOREIGN KEY(parent_assembly_id) REFERENCES equipment_assemblies(id) ON DELETE CASCADE,
 FOREIGN KEY(template_id) REFERENCES assembly_templates(id)
) ENGINE=InnoDB;
CREATE TABLE equipment_assembly_items (
 id CHAR(36) PRIMARY KEY, equipment_assembly_id CHAR(36) NOT NULL, part_id CHAR(36) NULL, custom_name VARCHAR(255) NULL,
 quantity DECIMAL(12,3) NOT NULL DEFAULT 1, sequence INT NOT NULL DEFAULT 0, template_item_id CHAR(36) NULL,
 is_custom_addon BOOLEAN NOT NULL DEFAULT FALSE, installed_at DATETIME NULL, removed_at DATETIME NULL,
 status VARCHAR(100) NULL, notes VARCHAR(500) NULL, CHECK(quantity>0), CHECK(part_id IS NOT NULL OR custom_name IS NOT NULL),
 FOREIGN KEY(equipment_assembly_id) REFERENCES equipment_assemblies(id) ON DELETE CASCADE,
 FOREIGN KEY(part_id) REFERENCES part_master(id), FOREIGN KEY(template_item_id) REFERENCES assembly_template_items(id)
) ENGINE=InnoDB;
CREATE OR REPLACE VIEW equipment_status_view AS
 SELECT e.*,s.id AS site_id,s.name AS site_name,u.name AS unit_name,op.full_name AS operator_name
 FROM equipment e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id
 JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id AND sp.owner_id=e.owner_id
 LEFT JOIN users op ON op.id=e.operator_id AND op.owner_id=e.owner_id AND op.is_active=1 AND op.role='OPERATOR'
 AND EXISTS(SELECT 1 FROM user_scopes os WHERE os.user_id=op.id AND os.site_id=s.id AND os.unit_id=u.id);
