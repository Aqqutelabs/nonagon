-- Nonagon live migration recovery after equipment_photos FK failure
-- Generated for live UUID collation: utf8mb4_0900_ai_ci
-- Starts at the first failed statement. Do not run the 001-035 file again first.

SET NAMES utf8mb4 COLLATE utf8mb4_0900_ai_ci;

CREATE TABLE equipment_photos (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, url VARCHAR(500) NOT NULL, caption VARCHAR(255) NULL,
 is_primary BOOLEAN NOT NULL DEFAULT FALSE, mime_type VARCHAR(30) NOT NULL, image_data MEDIUMBLOB NOT NULL,
 primary_equipment CHAR(36) AS (IF(is_primary=1,equipment_id,NULL)) STORED,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY primary_photo(primary_equipment),
 CONSTRAINT fk_equipment_photos_equipment FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE equipment_depreciation_profile (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL UNIQUE, method ENUM('STRAIGHT_LINE') NOT NULL DEFAULT 'STRAIGHT_LINE',
 currency CHAR(3) NOT NULL DEFAULT 'NGN', acquisition_cost DECIMAL(14,2) NOT NULL, acquisition_date DATE NOT NULL,
 useful_life_years DECIMAL(8,3) NOT NULL, salvage_value DECIMAL(14,2) NOT NULL DEFAULT 0, start_date DATE NOT NULL,
 is_active BOOLEAN NOT NULL DEFAULT TRUE, notes TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CHECK(acquisition_cost>=0 AND useful_life_years>0 AND salvage_value>=0 AND salvage_value<=acquisition_cost),
 FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE equipment_value_snapshot (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, as_of_date DATE NOT NULL,
 value_type ENUM('PURCHASE','BOOK','MARKET','BOOK_ESTIMATE_AI','MARKET_ESTIMATE_AI') NOT NULL,
 amount DECIMAL(14,2) NOT NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN',
 source_type ENUM('SYSTEM','USER','AI_AGENT','EXTERNAL_API') NOT NULL, source_detail TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX value_history(equipment_id,as_of_date,value_type), CHECK(amount>=0),
 FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE equipment_value_current (
 equipment_id CHAR(36) PRIMARY KEY, current_book_value DECIMAL(14,2) NULL, current_book_value_source_type VARCHAR(20) NULL,
 current_book_value_source_detail TEXT NULL, current_market_value DECIMAL(14,2) NULL,
 current_market_value_source_type VARCHAR(20) NULL, current_market_value_source_detail TEXT NULL,
 last_calculated_at DATETIME NOT NULL, FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE part_master (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, name VARCHAR(255) NOT NULL, description TEXT NULL,
 manufacturer_part_number VARCHAR(150) NULL, oem_part_number VARCHAR(150) NULL, brand_id CHAR(36) NULL, model_id CHAR(36) NULL,
 unit_of_measure VARCHAR(20) NOT NULL DEFAULT 'PCS', function_name VARCHAR(100) NULL,
 is_serviceable BOOLEAN NOT NULL DEFAULT FALSE, is_consumable BOOLEAN NOT NULL DEFAULT FALSE,
 is_global BOOLEAN NOT NULL DEFAULT FALSE, is_verified BOOLEAN NOT NULL DEFAULT FALSE, created_by CHAR(36) NOT NULL,
 FOREIGN KEY(owner_id) REFERENCES owners(id), FOREIGN KEY(brand_id) REFERENCES equipment_brands(id), FOREIGN KEY(model_id) REFERENCES equipment_models(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE assembly_templates (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, name VARCHAR(255) NOT NULL, description TEXT NULL,
 equipment_type_id CHAR(36) NOT NULL, brand_id CHAR(36) NULL, model_id CHAR(36) NULL,
 is_global BOOLEAN NOT NULL DEFAULT FALSE, is_verified BOOLEAN NOT NULL DEFAULT FALSE, created_by CHAR(36) NOT NULL,
 FOREIGN KEY(owner_id) REFERENCES owners(id), FOREIGN KEY(equipment_type_id) REFERENCES equipment_types(id),
 FOREIGN KEY(brand_id) REFERENCES equipment_brands(id), FOREIGN KEY(model_id) REFERENCES equipment_models(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE assembly_template_items (
 id CHAR(36) PRIMARY KEY, assembly_template_id CHAR(36) NOT NULL, part_id CHAR(36) NULL, subassembly_id CHAR(36) NULL,
 quantity DECIMAL(12,3) NOT NULL DEFAULT 1, sequence INT NOT NULL DEFAULT 0, is_required BOOLEAN NOT NULL DEFAULT TRUE,
 is_custom_slot BOOLEAN NOT NULL DEFAULT FALSE, notes VARCHAR(500) NULL,
 CHECK(quantity>0), CHECK((part_id IS NOT NULL)+(subassembly_id IS NOT NULL)+is_custom_slot=1),
 FOREIGN KEY(assembly_template_id) REFERENCES assembly_templates(id), FOREIGN KEY(part_id) REFERENCES part_master(id),
 FOREIGN KEY(subassembly_id) REFERENCES assembly_templates(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE equipment_assemblies (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, parent_assembly_id CHAR(36) NULL,
 name VARCHAR(255) NOT NULL, description TEXT NULL, sequence INT NOT NULL DEFAULT 0,
 template_id CHAR(36) NULL, is_custom_addon BOOLEAN NOT NULL DEFAULT FALSE,
 FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE, FOREIGN KEY(parent_assembly_id) REFERENCES equipment_assemblies(id) ON DELETE CASCADE,
 FOREIGN KEY(template_id) REFERENCES assembly_templates(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE equipment_assembly_items (
 id CHAR(36) PRIMARY KEY, equipment_assembly_id CHAR(36) NOT NULL, part_id CHAR(36) NULL, custom_name VARCHAR(255) NULL,
 quantity DECIMAL(12,3) NOT NULL DEFAULT 1, sequence INT NOT NULL DEFAULT 0, template_item_id CHAR(36) NULL,
 is_custom_addon BOOLEAN NOT NULL DEFAULT FALSE, installed_at DATETIME NULL, removed_at DATETIME NULL,
 status VARCHAR(100) NULL, notes VARCHAR(500) NULL, CHECK(quantity>0), CHECK(part_id IS NOT NULL OR custom_name IS NOT NULL),
 FOREIGN KEY(equipment_assembly_id) REFERENCES equipment_assemblies(id) ON DELETE CASCADE,
 FOREIGN KEY(part_id) REFERENCES part_master(id), FOREIGN KEY(template_item_id) REFERENCES assembly_template_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE OR REPLACE VIEW equipment_status_view AS
 SELECT e.*,s.id AS site_id,s.name AS site_name,u.name AS unit_name,op.full_name AS operator_name
 FROM equipment e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id
 JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id AND sp.owner_id=e.owner_id
 LEFT JOIN users op ON op.id=e.operator_id AND op.owner_id=e.owner_id AND op.is_active=1 AND op.role='OPERATOR'
 AND EXISTS(SELECT 1 FROM user_scopes os WHERE os.user_id=op.id AND os.site_id=s.id AND os.unit_id=u.id);
-- END 004_equipment_catalog.sql

-- BEGIN 005_block_compatibility.sql
ALTER TABLE sites ADD COLUMN block_id CHAR(36) AS (space_id) VIRTUAL;
-- END 005_block_compatibility.sql

-- BEGIN 006_default_status_identity.sql
ALTER TABLE equipment_statuses ADD COLUMN default_state VARCHAR(20) AS (IF(is_default=1,operational_state,NULL)) STORED, ADD UNIQUE KEY default_status(owner_id,default_state);
-- END 006_default_status_identity.sql

-- BEGIN 007_equipment_register.sql
ALTER TABLE equipment_statuses ADD severity INT NOT NULL DEFAULT 0;
UPDATE equipment_statuses SET severity=CASE operational_state WHEN 'DOWN' THEN 2 WHEN 'MAINTENANCE' THEN 1 ELSE 0 END;
CREATE TABLE equipment_operator_assignments (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, operator_user_id CHAR(36) NOT NULL,
 assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, unassigned_at DATETIME NULL,
 active_equipment CHAR(36) GENERATED ALWAYS AS (IF(unassigned_at IS NULL,equipment_id,NULL)) STORED,
 UNIQUE KEY one_active_assignment(active_equipment), INDEX operator_active(operator_user_id,unassigned_at,equipment_id),
 FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
 FOREIGN KEY(operator_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
INSERT INTO equipment_operator_assignments(id,equipment_id,operator_user_id,assigned_at) SELECT UUID(),id,operator_id,UTC_TIMESTAMP() FROM equipment WHERE operator_id IS NOT NULL;
CREATE TABLE equipment_status_history (
 id BIGINT AUTO_INCREMENT PRIMARY KEY, equipment_id CHAR(36) NOT NULL, status VARCHAR(30) NOT NULL,
 changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX equipment_time(equipment_id,changed_at), FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
INSERT INTO equipment_status_history(equipment_id,status) SELECT id,status FROM equipment;
CREATE INDEX equipment_owner_category ON equipment(owner_id,category_id,unit_id);
CREATE OR REPLACE VIEW equipment_list_view AS SELECT e.*,p.id AS plant_id,p.name AS plant_name,sp.id AS block_id,sp.name AS block_name,c.name AS category_name,sc.name AS subcategory_name,t.name AS type_name,b.name AS brand_name,mo.name AS model_name FROM equipment_status_view e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id LEFT JOIN equipment_categories c ON c.id=e.category_id LEFT JOIN equipment_subcategories sc ON sc.id=e.subcategory_id LEFT JOIN equipment_types t ON t.id=e.type_id LEFT JOIN equipment_brands b ON b.id=e.brand_id LEFT JOIN equipment_models mo ON mo.id=e.model_id;
CREATE OR REPLACE VIEW equipment_kpi_view AS SELECT owner_id,COUNT(*) AS total_assets,SUM(status='OPERATIONAL') AS active_assets,SUM(status='MAINTENANCE') AS maintenance_assets,COUNT(DISTINCT operator_id) AS operator_count FROM equipment GROUP BY owner_id;
-- END 007_equipment_register.sql

-- BEGIN 008_equipment_archive.sql
ALTER TABLE equipment ADD archived_at DATETIME NULL, ADD archived_by CHAR(36) NULL;
CREATE INDEX equipment_archive_scope ON equipment(owner_id,archived_at,unit_id);
CREATE OR REPLACE VIEW equipment_status_view AS
 SELECT e.*,s.id AS site_id,s.name AS site_name,u.name AS unit_name,op.full_name AS operator_name
 FROM equipment e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id
 JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id AND sp.owner_id=e.owner_id
 LEFT JOIN users op ON op.id=e.operator_id AND op.owner_id=e.owner_id AND op.is_active=1 AND op.role='OPERATOR'
 AND EXISTS(SELECT 1 FROM user_scopes os WHERE os.user_id=op.id AND os.site_id=s.id AND os.unit_id=u.id)
 WHERE e.archived_at IS NULL;
CREATE OR REPLACE VIEW equipment_kpi_view AS SELECT owner_id,COUNT(*) AS total_assets,SUM(status='OPERATIONAL') AS active_assets,SUM(status='MAINTENANCE') AS maintenance_assets,COUNT(DISTINCT operator_id) AS operator_count FROM equipment WHERE archived_at IS NULL GROUP BY owner_id;
-- END 008_equipment_archive.sql

-- BEGIN 009_marketplace_sprint1.sql
ALTER TABLE owners
 ADD public_description TEXT NULL,
 ADD public_country VARCHAR(100) NULL,
 ADD public_state VARCHAR(100) NULL,
 ADD public_city VARCHAR(100) NULL,
 ADD marketplace_verified BOOLEAN NOT NULL DEFAULT FALSE;
CREATE TABLE marketplace_oems (
 id CHAR(36) PRIMARY KEY, suggested_by_owner_id CHAR(36) NULL,
 legal_name VARCHAR(255) NOT NULL, brand_name VARCHAR(255) NOT NULL,
 slug VARCHAR(180) NOT NULL UNIQUE, website VARCHAR(500) NULL,
 verification_status ENUM('PENDING','UNVERIFIED','VERIFIED') NOT NULL DEFAULT 'UNVERIFIED',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX oem_name(brand_name,legal_name), FOREIGN KEY(suggested_by_owner_id) REFERENCES owners(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE marketplace_oem_models (
 id CHAR(36) PRIMARY KEY, oem_id CHAR(36) NOT NULL, equipment_type_id CHAR(36) NULL,
 model_name VARCHAR(255) NOT NULL, standard_specifications JSON NULL,
 lifecycle_status ENUM('ACTIVE','LEGACY','OBSOLETE','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY oem_model(oem_id,model_name), INDEX model_type(equipment_type_id,model_name),
 FOREIGN KEY(oem_id) REFERENCES marketplace_oems(id), FOREIGN KEY(equipment_type_id) REFERENCES equipment_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
ALTER TABLE equipment
 ADD marketplace_only BOOLEAN NOT NULL DEFAULT FALSE,
 ADD marketplace_oem_id CHAR(36) NULL,
 ADD marketplace_oem_model_id CHAR(36) NULL,
 ADD marketplace_specifications JSON NULL,
 ADD FOREIGN KEY(marketplace_oem_id) REFERENCES marketplace_oems(id),
 ADD FOREIGN KEY(marketplace_oem_model_id) REFERENCES marketplace_oem_models(id);
CREATE TABLE marketplace_listings (
 id CHAR(36) PRIMARY KEY, asset_id CHAR(36) NOT NULL, organization_id CHAR(36) NOT NULL, created_by CHAR(36) NOT NULL,
 purpose ENUM('LEASE','SALE','LEASE_OR_SALE') NOT NULL,
 title VARCHAR(255) NOT NULL, description TEXT NOT NULL,
 listing_status ENUM('DRAFT','ACTIVE','PAUSED','RESERVED','CLOSED') NOT NULL DEFAULT 'DRAFT',
 marketplace_status ENUM('AVAILABLE','RESERVED','MOBILIZING','IN_USE','MAINTENANCE','OFFLINE','BLOCKED') NOT NULL DEFAULT 'AVAILABLE',
 visibility ENUM('PUBLIC','NETWORK','PRIVATE') NOT NULL DEFAULT 'PUBLIC',
 public_asset_code BOOLEAN NOT NULL DEFAULT FALSE,
 country VARCHAR(100) NOT NULL, state_region VARCHAR(100) NOT NULL, city VARCHAR(100) NOT NULL,
 available_from DATE NULL, price_visibility ENUM('PUBLIC','REQUEST_QUOTE') NOT NULL DEFAULT 'REQUEST_QUOTE',
 compliance_status ENUM('NOT_PROVIDED','AVAILABLE_ON_REQUEST','VALID','EXPIRED') NOT NULL DEFAULT 'NOT_PROVIDED',
 public_specifications JSON NULL, views INT UNSIGNED NOT NULL DEFAULT 0,
 published_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX marketplace_discovery(listing_status,visibility,marketplace_status,purpose,available_from),
 INDEX marketplace_owner(organization_id,listing_status,updated_at), INDEX marketplace_asset(asset_id,created_at),
 FOREIGN KEY(asset_id) REFERENCES equipment(id), FOREIGN KEY(organization_id) REFERENCES owners(id), FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE marketplace_lease_terms (
 listing_id CHAR(36) PRIMARY KEY, daily_rate DECIMAL(15,2) NULL, weekly_rate DECIMAL(15,2) NULL,
 monthly_rate DECIMAL(15,2) NULL, project_rate DECIMAL(15,2) NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN',
 minimum_duration INT UNSIGNED NULL, maximum_duration INT UNSIGNED NULL,
 duration_unit ENUM('DAY','WEEK','MONTH') NULL, security_deposit DECIMAL(15,2) NULL,
 negotiable BOOLEAN NOT NULL DEFAULT FALSE, operator_included BOOLEAN NOT NULL DEFAULT FALSE,
 fuel_included BOOLEAN NOT NULL DEFAULT FALSE, mobilization_included BOOLEAN NOT NULL DEFAULT FALSE,
 demobilization_included BOOLEAN NOT NULL DEFAULT FALSE, maintenance_responsibility VARCHAR(255) NULL,
 insurance_requirement VARCHAR(255) NULL, geographic_restrictions TEXT NULL,
 mobilization_terms TEXT NULL, demobilization_terms TEXT NULL,
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE marketplace_sale_terms (
 listing_id CHAR(36) PRIMARY KEY, asking_price DECIMAL(15,2) NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN',
 negotiable BOOLEAN NOT NULL DEFAULT FALSE, inspection_allowed BOOLEAN NOT NULL DEFAULT TRUE,
 `condition` ENUM('NEW','EXCELLENT','GOOD','FAIR','AS_IS') NOT NULL DEFAULT 'GOOD',
 delivery_terms TEXT NULL, included_items JSON NULL, sale_notes TEXT NULL,
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE marketplace_listing_media (
 id CHAR(36) PRIMARY KEY, listing_id CHAR(36) NOT NULL, equipment_photo_id CHAR(36) NULL,
 media_type ENUM('IMAGE','VIDEO','SPECIFICATION_SHEET','BROCHURE','CERTIFICATE','INSPECTION','OTHER') NOT NULL,
 title VARCHAR(255) NULL, external_url VARCHAR(500) NULL, mime_type VARCHAR(100) NULL, file_data MEDIUMBLOB NULL,
 visibility ENUM('PUBLIC','ON_REQUEST','PRIVATE') NOT NULL DEFAULT 'PUBLIC', is_primary BOOLEAN NOT NULL DEFAULT FALSE,
 sequence INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX listing_media(listing_id,visibility,sequence), UNIQUE KEY listing_equipment_photo(listing_id,equipment_photo_id), FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE,
 FOREIGN KEY(equipment_photo_id) REFERENCES equipment_photos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 009_marketplace_sprint1.sql

-- BEGIN 010_marketplace_equipment_views.sql
CREATE OR REPLACE VIEW equipment_status_view AS
 SELECT e.*,s.id AS site_id,s.name AS site_name,u.name AS unit_name,op.full_name AS operator_name
 FROM equipment e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id
 JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id AND sp.owner_id=e.owner_id
 LEFT JOIN users op ON op.id=e.operator_id AND op.owner_id=e.owner_id AND op.is_active=1 AND op.role='OPERATOR'
 AND EXISTS(SELECT 1 FROM user_scopes os WHERE os.user_id=op.id AND os.site_id=s.id AND os.unit_id=u.id)
 WHERE e.archived_at IS NULL;
CREATE OR REPLACE VIEW equipment_list_view AS
 SELECT e.*,p.id AS plant_id,p.name AS plant_name,sp.id AS block_id,sp.name AS block_name,
 c.name AS category_name,sc.name AS subcategory_name,t.name AS type_name,b.name AS brand_name,mo.name AS model_name
 FROM equipment_status_view e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id
 JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id
 LEFT JOIN equipment_categories c ON c.id=e.category_id LEFT JOIN equipment_subcategories sc ON sc.id=e.subcategory_id
 LEFT JOIN equipment_types t ON t.id=e.type_id LEFT JOIN equipment_brands b ON b.id=e.brand_id LEFT JOIN equipment_models mo ON mo.id=e.model_id;
-- END 010_marketplace_equipment_views.sql

-- BEGIN 011_marketplace_availability_compliance.sql
ALTER TABLE marketplace_listings
 ADD certification_type VARCHAR(150) NULL,
 ADD certification_valid_until DATE NULL,
 ADD last_inspected_on DATE NULL;
CREATE TABLE marketplace_availability_periods (
 id CHAR(36) PRIMARY KEY, listing_id CHAR(36) NOT NULL,
 start_date DATE NOT NULL, end_date DATE NOT NULL,
 availability ENUM('AVAILABLE','UNAVAILABLE') NOT NULL,
 note VARCHAR(255) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CHECK(end_date>=start_date), INDEX listing_dates(listing_id,start_date,end_date,availability),
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 011_marketplace_availability_compliance.sql

-- BEGIN 012_marketplace_structured_terms.sql
ALTER TABLE marketplace_lease_terms
  MODIFY duration_unit ENUM('HOUR','DAY','WEEK','MONTH') NULL,
  ADD COLUMN rate DECIMAL(15,2) NULL AFTER currency,
  ADD COLUMN operator_rate DECIMAL(15,2) NULL AFTER operator_included,
  ADD COLUMN consumables_included TINYINT(1) NOT NULL DEFAULT 0 AFTER fuel_included,
  ADD COLUMN consumables_details VARCHAR(500) NULL AFTER consumables_included,
  ADD COLUMN mobilization_option VARCHAR(60) NULL AFTER mobilization_terms,
  ADD COLUMN mobilization_custom TEXT NULL AFTER mobilization_option,
  ADD COLUMN demobilization_option VARCHAR(60) NULL AFTER demobilization_terms,
  ADD COLUMN demobilization_custom TEXT NULL AFTER demobilization_option,
  ADD COLUMN maintenance_option VARCHAR(80) NULL AFTER maintenance_responsibility,
  ADD COLUMN maintenance_details TEXT NULL AFTER maintenance_option,
  ADD COLUMN insurance_option VARCHAR(80) NULL AFTER insurance_requirement,
  ADD COLUMN insurance_details TEXT NULL AFTER insurance_option;

UPDATE marketplace_lease_terms
SET rate=CASE duration_unit WHEN 'WEEK' THEN weekly_rate WHEN 'MONTH' THEN monthly_rate ELSE daily_rate END,
    consumables_included=fuel_included
WHERE rate IS NULL;

ALTER TABLE marketplace_sale_terms
  MODIFY `condition` ENUM('NEW','EXCELLENT','GOOD','FAIR','AS_IS','INSPECTED','SERVICED_BEFORE_HANDOVER','TO_BE_AGREED') NOT NULL DEFAULT 'AS_IS',
  ADD COLUMN payment_terms VARCHAR(40) NULL AFTER negotiable,
  ADD COLUMN payment_terms_custom TEXT NULL AFTER payment_terms,
  ADD COLUMN taxes_fees VARCHAR(40) NULL AFTER `condition`;

CREATE TABLE marketplace_locations (
  id CHAR(36) PRIMARY KEY,
  canonical_name VARCHAR(180) NOT NULL,
  location_type ENUM('COUNTRY','REGION','CITY') NOT NULL,
  country_code CHAR(2) NOT NULL,
  country_name VARCHAR(100) NULL,
  parent_id CHAR(36) NULL,
  search_name VARCHAR(350) GENERATED ALWAYS AS (LOWER(CONCAT_WS(' ',canonical_name,country_name,country_code))) STORED,
  UNIQUE KEY marketplace_location_identity(country_code,location_type,canonical_name),
  KEY marketplace_location_search(search_name(150)),
  KEY marketplace_location_parent(parent_id),
  CONSTRAINT marketplace_location_parent_fk FOREIGN KEY(parent_id) REFERENCES marketplace_locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_listing_location_rules (
  id CHAR(36) PRIMARY KEY,
  listing_id CHAR(36) NOT NULL,
  location_id CHAR(36) NOT NULL,
  rule_type ENUM('ALLOW','RESTRICT') NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY marketplace_listing_location_unique(listing_id,location_id),
  KEY marketplace_listing_location_location(location_id,rule_type),
  CONSTRAINT marketplace_listing_location_listing_fk FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE,
  CONSTRAINT marketplace_listing_location_location_fk FOREIGN KEY(location_id) REFERENCES marketplace_locations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO marketplace_locations(id,canonical_name,location_type,country_code,country_name,parent_id) VALUES
(UUID(),'Afghanistan','COUNTRY','AF',NULL,NULL),
(UUID(),'Albania','COUNTRY','AL',NULL,NULL),
(UUID(),'Algeria','COUNTRY','DZ',NULL,NULL),
(UUID(),'Andorra','COUNTRY','AD',NULL,NULL),
(UUID(),'Angola','COUNTRY','AO',NULL,NULL),
(UUID(),'Antigua and Barbuda','COUNTRY','AG',NULL,NULL),
(UUID(),'Argentina','COUNTRY','AR',NULL,NULL),
(UUID(),'Armenia','COUNTRY','AM',NULL,NULL),
(UUID(),'Australia','COUNTRY','AU',NULL,NULL),
(UUID(),'Austria','COUNTRY','AT',NULL,NULL),
(UUID(),'Azerbaijan','COUNTRY','AZ',NULL,NULL),
(UUID(),'Bahamas','COUNTRY','BS',NULL,NULL),
(UUID(),'Bahrain','COUNTRY','BH',NULL,NULL),
(UUID(),'Bangladesh','COUNTRY','BD',NULL,NULL),
(UUID(),'Barbados','COUNTRY','BB',NULL,NULL),
(UUID(),'Belarus','COUNTRY','BY',NULL,NULL),
(UUID(),'Belgium','COUNTRY','BE',NULL,NULL),
(UUID(),'Belize','COUNTRY','BZ',NULL,NULL),
(UUID(),'Benin','COUNTRY','BJ',NULL,NULL),
(UUID(),'Bhutan','COUNTRY','BT',NULL,NULL),
(UUID(),'Bolivia','COUNTRY','BO',NULL,NULL),
(UUID(),'Bosnia and Herzegovina','COUNTRY','BA',NULL,NULL),
(UUID(),'Botswana','COUNTRY','BW',NULL,NULL),
(UUID(),'Brazil','COUNTRY','BR',NULL,NULL),
(UUID(),'Brunei','COUNTRY','BN',NULL,NULL),
(UUID(),'Bulgaria','COUNTRY','BG',NULL,NULL),
(UUID(),'Burkina Faso','COUNTRY','BF',NULL,NULL),
(UUID(),'Burundi','COUNTRY','BI',NULL,NULL),
(UUID(),'Cabo Verde','COUNTRY','CV',NULL,NULL),
(UUID(),'Cambodia','COUNTRY','KH',NULL,NULL),
(UUID(),'Cameroon','COUNTRY','CM',NULL,NULL),
(UUID(),'Canada','COUNTRY','CA',NULL,NULL),
(UUID(),'Central African Republic','COUNTRY','CF',NULL,NULL),
(UUID(),'Chad','COUNTRY','TD',NULL,NULL),
(UUID(),'Chile','COUNTRY','CL',NULL,NULL),
(UUID(),'China','COUNTRY','CN',NULL,NULL),
(UUID(),'Colombia','COUNTRY','CO',NULL,NULL),
(UUID(),'Comoros','COUNTRY','KM',NULL,NULL),
(UUID(),'Congo','COUNTRY','CG',NULL,NULL),
(UUID(),'Congo, Democratic Republic','COUNTRY','CD',NULL,NULL),
(UUID(),'Costa Rica','COUNTRY','CR',NULL,NULL),
(UUID(),'Cote d''Ivoire','COUNTRY','CI',NULL,NULL),
(UUID(),'Croatia','COUNTRY','HR',NULL,NULL),
(UUID(),'Cuba','COUNTRY','CU',NULL,NULL),
(UUID(),'Cyprus','COUNTRY','CY',NULL,NULL),
(UUID(),'Czechia','COUNTRY','CZ',NULL,NULL),
(UUID(),'Denmark','COUNTRY','DK',NULL,NULL),
(UUID(),'Djibouti','COUNTRY','DJ',NULL,NULL),
(UUID(),'Dominica','COUNTRY','DM',NULL,NULL),
(UUID(),'Dominican Republic','COUNTRY','DO',NULL,NULL),
(UUID(),'Ecuador','COUNTRY','EC',NULL,NULL),
(UUID(),'Egypt','COUNTRY','EG',NULL,NULL),
(UUID(),'El Salvador','COUNTRY','SV',NULL,NULL),
(UUID(),'Equatorial Guinea','COUNTRY','GQ',NULL,NULL),
(UUID(),'Eritrea','COUNTRY','ER',NULL,NULL),
(UUID(),'Estonia','COUNTRY','EE',NULL,NULL),
(UUID(),'Eswatini','COUNTRY','SZ',NULL,NULL),
(UUID(),'Ethiopia','COUNTRY','ET',NULL,NULL),
(UUID(),'Fiji','COUNTRY','FJ',NULL,NULL),
(UUID(),'Finland','COUNTRY','FI',NULL,NULL),
(UUID(),'France','COUNTRY','FR',NULL,NULL),
(UUID(),'Gabon','COUNTRY','GA',NULL,NULL),
(UUID(),'Gambia','COUNTRY','GM',NULL,NULL),
(UUID(),'Georgia','COUNTRY','GE',NULL,NULL),
(UUID(),'Germany','COUNTRY','DE',NULL,NULL),
(UUID(),'Ghana','COUNTRY','GH',NULL,NULL),
(UUID(),'Greece','COUNTRY','GR',NULL,NULL),
(UUID(),'Grenada','COUNTRY','GD',NULL,NULL),
(UUID(),'Guatemala','COUNTRY','GT',NULL,NULL),
(UUID(),'Guinea','COUNTRY','GN',NULL,NULL),
(UUID(),'Guinea-Bissau','COUNTRY','GW',NULL,NULL),
(UUID(),'Guyana','COUNTRY','GY',NULL,NULL),
(UUID(),'Haiti','COUNTRY','HT',NULL,NULL),
(UUID(),'Honduras','COUNTRY','HN',NULL,NULL),
(UUID(),'Hungary','COUNTRY','HU',NULL,NULL),
(UUID(),'Iceland','COUNTRY','IS',NULL,NULL),
(UUID(),'India','COUNTRY','IN',NULL,NULL),
(UUID(),'Indonesia','COUNTRY','ID',NULL,NULL),
(UUID(),'Iran','COUNTRY','IR',NULL,NULL),
(UUID(),'Iraq','COUNTRY','IQ',NULL,NULL),
(UUID(),'Ireland','COUNTRY','IE',NULL,NULL),
(UUID(),'Israel','COUNTRY','IL',NULL,NULL),
(UUID(),'Italy','COUNTRY','IT',NULL,NULL),
(UUID(),'Jamaica','COUNTRY','JM',NULL,NULL),
(UUID(),'Japan','COUNTRY','JP',NULL,NULL),
(UUID(),'Jordan','COUNTRY','JO',NULL,NULL),
(UUID(),'Kazakhstan','COUNTRY','KZ',NULL,NULL),
(UUID(),'Kenya','COUNTRY','KE',NULL,NULL),
(UUID(),'Kiribati','COUNTRY','KI',NULL,NULL),
(UUID(),'North Korea','COUNTRY','KP',NULL,NULL),
(UUID(),'South Korea','COUNTRY','KR',NULL,NULL),
(UUID(),'Kuwait','COUNTRY','KW',NULL,NULL),
(UUID(),'Kyrgyzstan','COUNTRY','KG',NULL,NULL),
(UUID(),'Laos','COUNTRY','LA',NULL,NULL),
(UUID(),'Latvia','COUNTRY','LV',NULL,NULL),
(UUID(),'Lebanon','COUNTRY','LB',NULL,NULL),
(UUID(),'Lesotho','COUNTRY','LS',NULL,NULL),
(UUID(),'Liberia','COUNTRY','LR',NULL,NULL),
(UUID(),'Libya','COUNTRY','LY',NULL,NULL),
(UUID(),'Liechtenstein','COUNTRY','LI',NULL,NULL),
(UUID(),'Lithuania','COUNTRY','LT',NULL,NULL),
(UUID(),'Luxembourg','COUNTRY','LU',NULL,NULL),
(UUID(),'Madagascar','COUNTRY','MG',NULL,NULL),
(UUID(),'Malawi','COUNTRY','MW',NULL,NULL),
(UUID(),'Malaysia','COUNTRY','MY',NULL,NULL),
(UUID(),'Maldives','COUNTRY','MV',NULL,NULL),
(UUID(),'Mali','COUNTRY','ML',NULL,NULL),
(UUID(),'Malta','COUNTRY','MT',NULL,NULL),
(UUID(),'Marshall Islands','COUNTRY','MH',NULL,NULL),
(UUID(),'Mauritania','COUNTRY','MR',NULL,NULL),
(UUID(),'Mauritius','COUNTRY','MU',NULL,NULL),
(UUID(),'Mexico','COUNTRY','MX',NULL,NULL),
(UUID(),'Micronesia','COUNTRY','FM',NULL,NULL),
(UUID(),'Moldova','COUNTRY','MD',NULL,NULL),
(UUID(),'Monaco','COUNTRY','MC',NULL,NULL),
(UUID(),'Mongolia','COUNTRY','MN',NULL,NULL),
(UUID(),'Montenegro','COUNTRY','ME',NULL,NULL),
(UUID(),'Morocco','COUNTRY','MA',NULL,NULL),
(UUID(),'Mozambique','COUNTRY','MZ',NULL,NULL),
(UUID(),'Myanmar','COUNTRY','MM',NULL,NULL),
(UUID(),'Namibia','COUNTRY','NA',NULL,NULL),
(UUID(),'Nauru','COUNTRY','NR',NULL,NULL),
(UUID(),'Nepal','COUNTRY','NP',NULL,NULL),
(UUID(),'Netherlands','COUNTRY','NL',NULL,NULL),
(UUID(),'New Zealand','COUNTRY','NZ',NULL,NULL),
(UUID(),'Nicaragua','COUNTRY','NI',NULL,NULL),
(UUID(),'Niger','COUNTRY','NE',NULL,NULL),
(UUID(),'Nigeria','COUNTRY','NG',NULL,NULL),
(UUID(),'North Macedonia','COUNTRY','MK',NULL,NULL),
(UUID(),'Norway','COUNTRY','NO',NULL,NULL),
(UUID(),'Oman','COUNTRY','OM',NULL,NULL),
(UUID(),'Pakistan','COUNTRY','PK',NULL,NULL),
(UUID(),'Palau','COUNTRY','PW',NULL,NULL),
(UUID(),'Palestine','COUNTRY','PS',NULL,NULL),
(UUID(),'Panama','COUNTRY','PA',NULL,NULL),
(UUID(),'Papua New Guinea','COUNTRY','PG',NULL,NULL),
(UUID(),'Paraguay','COUNTRY','PY',NULL,NULL),
(UUID(),'Peru','COUNTRY','PE',NULL,NULL),
(UUID(),'Philippines','COUNTRY','PH',NULL,NULL),
(UUID(),'Poland','COUNTRY','PL',NULL,NULL),
(UUID(),'Portugal','COUNTRY','PT',NULL,NULL),
(UUID(),'Qatar','COUNTRY','QA',NULL,NULL),
(UUID(),'Romania','COUNTRY','RO',NULL,NULL),
(UUID(),'Russia','COUNTRY','RU',NULL,NULL),
(UUID(),'Rwanda','COUNTRY','RW',NULL,NULL),
(UUID(),'Saint Kitts and Nevis','COUNTRY','KN',NULL,NULL),
(UUID(),'Saint Lucia','COUNTRY','LC',NULL,NULL),
(UUID(),'Saint Vincent and the Grenadines','COUNTRY','VC',NULL,NULL),
(UUID(),'Samoa','COUNTRY','WS',NULL,NULL),
(UUID(),'San Marino','COUNTRY','SM',NULL,NULL),
(UUID(),'Sao Tome and Principe','COUNTRY','ST',NULL,NULL),
(UUID(),'Saudi Arabia','COUNTRY','SA',NULL,NULL),
(UUID(),'Senegal','COUNTRY','SN',NULL,NULL),
(UUID(),'Serbia','COUNTRY','RS',NULL,NULL),
(UUID(),'Seychelles','COUNTRY','SC',NULL,NULL),
(UUID(),'Sierra Leone','COUNTRY','SL',NULL,NULL),
(UUID(),'Singapore','COUNTRY','SG',NULL,NULL),
(UUID(),'Slovakia','COUNTRY','SK',NULL,NULL),
(UUID(),'Slovenia','COUNTRY','SI',NULL,NULL),
(UUID(),'Solomon Islands','COUNTRY','SB',NULL,NULL),
(UUID(),'Somalia','COUNTRY','SO',NULL,NULL),
(UUID(),'South Africa','COUNTRY','ZA',NULL,NULL),
(UUID(),'South Sudan','COUNTRY','SS',NULL,NULL),
(UUID(),'Spain','COUNTRY','ES',NULL,NULL),
(UUID(),'Sri Lanka','COUNTRY','LK',NULL,NULL),
(UUID(),'Sudan','COUNTRY','SD',NULL,NULL),
(UUID(),'Suriname','COUNTRY','SR',NULL,NULL),
(UUID(),'Sweden','COUNTRY','SE',NULL,NULL),
(UUID(),'Switzerland','COUNTRY','CH',NULL,NULL),
(UUID(),'Syria','COUNTRY','SY',NULL,NULL),
(UUID(),'Taiwan','COUNTRY','TW',NULL,NULL),
(UUID(),'Tajikistan','COUNTRY','TJ',NULL,NULL),
(UUID(),'Tanzania','COUNTRY','TZ',NULL,NULL),
(UUID(),'Thailand','COUNTRY','TH',NULL,NULL),
(UUID(),'Timor-Leste','COUNTRY','TL',NULL,NULL),
(UUID(),'Togo','COUNTRY','TG',NULL,NULL),
(UUID(),'Tonga','COUNTRY','TO',NULL,NULL),
(UUID(),'Trinidad and Tobago','COUNTRY','TT',NULL,NULL),
(UUID(),'Tunisia','COUNTRY','TN',NULL,NULL),
(UUID(),'Turkiye','COUNTRY','TR',NULL,NULL),
(UUID(),'Turkmenistan','COUNTRY','TM',NULL,NULL),
(UUID(),'Tuvalu','COUNTRY','TV',NULL,NULL),
(UUID(),'Uganda','COUNTRY','UG',NULL,NULL),
(UUID(),'Ukraine','COUNTRY','UA',NULL,NULL),
(UUID(),'United Arab Emirates','COUNTRY','AE',NULL,NULL),
(UUID(),'United Kingdom','COUNTRY','GB',NULL,NULL),
(UUID(),'United States','COUNTRY','US',NULL,NULL),
(UUID(),'Uruguay','COUNTRY','UY',NULL,NULL),
(UUID(),'Uzbekistan','COUNTRY','UZ',NULL,NULL),
(UUID(),'Vanuatu','COUNTRY','VU',NULL,NULL),
(UUID(),'Vatican City','COUNTRY','VA',NULL,NULL),
(UUID(),'Venezuela','COUNTRY','VE',NULL,NULL),
(UUID(),'Vietnam','COUNTRY','VN',NULL,NULL),
(UUID(),'Yemen','COUNTRY','YE',NULL,NULL),
(UUID(),'Zambia','COUNTRY','ZM',NULL,NULL),
(UUID(),'Zimbabwe','COUNTRY','ZW',NULL,NULL);

INSERT INTO marketplace_locations(id,canonical_name,location_type,country_code,country_name,parent_id)
SELECT UUID(),x.name,'REGION','NG','Nigeria',ng.id
FROM marketplace_locations ng
JOIN (
 SELECT 'Abia' name UNION ALL SELECT 'Abuja Federal Capital Territory' UNION ALL SELECT 'Adamawa' UNION ALL SELECT 'Akwa Ibom' UNION ALL SELECT 'Anambra' UNION ALL SELECT 'Bauchi' UNION ALL SELECT 'Bayelsa' UNION ALL SELECT 'Benue' UNION ALL SELECT 'Borno' UNION ALL SELECT 'Cross River' UNION ALL SELECT 'Delta' UNION ALL SELECT 'Ebonyi' UNION ALL SELECT 'Edo' UNION ALL SELECT 'Ekiti' UNION ALL SELECT 'Enugu' UNION ALL SELECT 'Gombe' UNION ALL SELECT 'Imo' UNION ALL SELECT 'Jigawa' UNION ALL SELECT 'Kaduna' UNION ALL SELECT 'Kano' UNION ALL SELECT 'Katsina' UNION ALL SELECT 'Kebbi' UNION ALL SELECT 'Kogi' UNION ALL SELECT 'Kwara' UNION ALL SELECT 'Lagos State' UNION ALL SELECT 'Nasarawa' UNION ALL SELECT 'Niger State' UNION ALL SELECT 'Ogun' UNION ALL SELECT 'Ondo' UNION ALL SELECT 'Osun' UNION ALL SELECT 'Oyo' UNION ALL SELECT 'Plateau' UNION ALL SELECT 'Rivers' UNION ALL SELECT 'Sokoto' UNION ALL SELECT 'Taraba' UNION ALL SELECT 'Yobe' UNION ALL SELECT 'Zamfara'
) x
WHERE ng.country_code='NG' AND ng.location_type='COUNTRY';

INSERT INTO marketplace_locations(id,canonical_name,location_type,country_code,country_name,parent_id)
SELECT UUID(),x.city,'CITY',x.code,x.country,l.id
FROM (
 SELECT 'Lagos' city,'NG' code,'Nigeria' country,'Lagos State' region UNION ALL
 SELECT 'Port Harcourt','NG','Nigeria','Rivers' UNION ALL
 SELECT 'Abuja','NG','Nigeria','Abuja Federal Capital Territory' UNION ALL
 SELECT 'Kano','NG','Nigeria','Kano' UNION ALL
 SELECT 'Accra','GH','Ghana','Ghana' UNION ALL
 SELECT 'Kumasi','GH','Ghana','Ghana' UNION ALL
 SELECT 'London','GB','United Kingdom','United Kingdom' UNION ALL
 SELECT 'Dubai','AE','United Arab Emirates','United Arab Emirates' UNION ALL
 SELECT 'Houston','US','United States','United States'
) x
JOIN marketplace_locations l ON l.country_code=x.code AND ((l.location_type='REGION' AND l.canonical_name=x.region) OR (l.location_type='COUNTRY' AND l.canonical_name=x.region));
-- END 012_marketplace_structured_terms.sql

-- BEGIN 013_marketplace_assets_are_standard.sql
UPDATE equipment e
JOIN marketplace_listings l ON l.asset_id=e.id
SET e.marketplace_only=0
WHERE e.marketplace_only=1;
-- END 013_marketplace_assets_are_standard.sql

-- BEGIN 014_marketplace_negotiation.sql
CREATE TABLE marketplace_saved_listings (
 user_id CHAR(36) NOT NULL, listing_id CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,listing_id), INDEX saved_listing(listing_id,created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_enquiries (
 id CHAR(36) PRIMARY KEY, listing_id CHAR(36) NOT NULL, asset_id CHAR(36) NOT NULL,
 buyer_organization_id CHAR(36) NOT NULL, owner_organization_id CHAR(36) NOT NULL, created_by CHAR(36) NOT NULL,
 category ENUM('AVAILABILITY','SPECIFICATION','CERTIFICATION','INSPECTION','MOBILIZATION','OPERATOR','COMMERCIAL_TERMS','OTHER') NOT NULL,
 subject VARCHAR(255) NOT NULL, status ENUM('OPEN','RESPONDED','CLOSED') NOT NULL DEFAULT 'OPEN',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX enquiry_buyer(buyer_organization_id,status,updated_at), INDEX enquiry_owner(owner_organization_id,status,updated_at),
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id), FOREIGN KEY(asset_id) REFERENCES equipment(id),
 FOREIGN KEY(buyer_organization_id) REFERENCES owners(id), FOREIGN KEY(owner_organization_id) REFERENCES owners(id),
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_conversations (
 id CHAR(36) PRIMARY KEY, enquiry_id CHAR(36) NOT NULL UNIQUE, listing_id CHAR(36) NOT NULL,
 buyer_organization_id CHAR(36) NOT NULL, owner_organization_id CHAR(36) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(enquiry_id) REFERENCES marketplace_enquiries(id) ON DELETE CASCADE,
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id),
 FOREIGN KEY(buyer_organization_id) REFERENCES owners(id), FOREIGN KEY(owner_organization_id) REFERENCES owners(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_offers (
 id CHAR(36) PRIMARY KEY, enquiry_id CHAR(36) NULL, listing_id CHAR(36) NOT NULL, asset_id CHAR(36) NOT NULL,
 transaction_type ENUM('LEASE','SALE') NOT NULL, buyer_organization_id CHAR(36) NOT NULL, seller_organization_id CHAR(36) NOT NULL,
 created_by CHAR(36) NOT NULL,
 status ENUM('DRAFT','SUBMITTED','VIEWED','COUNTERED','REVISED','ACCEPTED','REJECTED','WITHDRAWN','EXPIRED') NOT NULL DEFAULT 'DRAFT',
 current_version INT UNSIGNED NOT NULL DEFAULT 1, accepted_version_id CHAR(36) NULL,
 submitted_at DATETIME NULL, viewed_at DATETIME NULL, accepted_at DATETIME NULL, rejected_at DATETIME NULL, withdrawn_at DATETIME NULL, expires_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX offer_buyer(buyer_organization_id,status,updated_at), INDEX offer_seller(seller_organization_id,status,updated_at),
 INDEX offer_listing(listing_id,status,updated_at),
 FOREIGN KEY(enquiry_id) REFERENCES marketplace_enquiries(id) ON DELETE SET NULL,
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id), FOREIGN KEY(asset_id) REFERENCES equipment(id),
 FOREIGN KEY(buyer_organization_id) REFERENCES owners(id), FOREIGN KEY(seller_organization_id) REFERENCES owners(id),
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_offer_versions (
 id CHAR(36) PRIMARY KEY, offer_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL,
 proposed_by_user_id CHAR(36) NOT NULL, proposed_by_organization_id CHAR(36) NOT NULL,
 amount DECIMAL(15,2) NOT NULL, currency CHAR(3) NOT NULL,
 pricing_basis ENUM('HOURLY','DAILY','WEEKLY','MONTHLY','PROJECT','SALE') NOT NULL,
 lease_start_date DATE NULL, lease_end_date DATE NULL,
 project_country VARCHAR(100) NULL, project_state VARCHAR(100) NULL, project_city VARCHAR(100) NULL,
 intended_use TEXT NULL, operator_requirement ENUM('OWNER_OPERATOR','LESSEE_OPERATOR','TO_BE_DETERMINED','NOT_APPLICABLE') NOT NULL DEFAULT 'NOT_APPLICABLE',
 mobilization_cost DECIMAL(15,2) NULL, security_deposit DECIMAL(15,2) NULL,
 commercial_conditions TEXT NULL, additional_requirements TEXT NULL, valid_until DATETIME NOT NULL,
 response_to_version_id CHAR(36) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY offer_version(offer_id,version_number), INDEX offer_version_proposer(proposed_by_organization_id,created_at),
 FOREIGN KEY(offer_id) REFERENCES marketplace_offers(id) ON DELETE CASCADE,
 FOREIGN KEY(proposed_by_user_id) REFERENCES users(id), FOREIGN KEY(proposed_by_organization_id) REFERENCES owners(id),
 FOREIGN KEY(response_to_version_id) REFERENCES marketplace_offer_versions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE marketplace_offers ADD FOREIGN KEY(accepted_version_id) REFERENCES marketplace_offer_versions(id);

CREATE TABLE marketplace_messages (
 id CHAR(36) PRIMARY KEY, conversation_id CHAR(36) NOT NULL, sender_user_id CHAR(36) NOT NULL,
 sender_organization_id CHAR(36) NOT NULL, message_text TEXT NULL, referenced_offer_id CHAR(36) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX conversation_messages(conversation_id,created_at),
 FOREIGN KEY(conversation_id) REFERENCES marketplace_conversations(id) ON DELETE CASCADE,
 FOREIGN KEY(sender_user_id) REFERENCES users(id), FOREIGN KEY(sender_organization_id) REFERENCES owners(id),
 FOREIGN KEY(referenced_offer_id) REFERENCES marketplace_offers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_message_attachments (
 id CHAR(36) PRIMARY KEY, message_id CHAR(36) NOT NULL, file_name VARCHAR(255) NOT NULL,
 mime_type VARCHAR(100) NOT NULL, file_size INT UNSIGNED NOT NULL, file_data MEDIUMBLOB NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(message_id) REFERENCES marketplace_messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_reservations (
 id CHAR(36) PRIMARY KEY, asset_id CHAR(36) NOT NULL, offer_id CHAR(36) NOT NULL, offer_version_id CHAR(36) NOT NULL,
 start_date DATE NOT NULL, end_date DATE NOT NULL,
 status ENUM('HOLD','CONFIRMED','RELEASED','EXPIRED') NOT NULL DEFAULT 'CONFIRMED',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX reservation_conflict(asset_id,status,start_date,end_date), UNIQUE KEY reservation_offer(offer_id),
 FOREIGN KEY(asset_id) REFERENCES equipment(id), FOREIGN KEY(offer_id) REFERENCES marketplace_offers(id),
 FOREIGN KEY(offer_version_id) REFERENCES marketplace_offer_versions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_transactions (
 id CHAR(36) PRIMARY KEY, transaction_type ENUM('LEASE','SALE') NOT NULL,
 listing_id CHAR(36) NOT NULL, asset_id CHAR(36) NOT NULL, accepted_offer_id CHAR(36) NOT NULL UNIQUE,
 accepted_offer_version_id CHAR(36) NOT NULL, supplier_organization_id CHAR(36) NOT NULL, customer_organization_id CHAR(36) NOT NULL,
 gross_value DECIMAL(15,2) NOT NULL, currency CHAR(3) NOT NULL, frozen_terms JSON NOT NULL,
 status ENUM('OFFER_ACCEPTED','CANCELLED','COMPLETED') NOT NULL DEFAULT 'OFFER_ACCEPTED',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, completed_at DATETIME NULL,
 INDEX transaction_supplier(supplier_organization_id,status,created_at), INDEX transaction_customer(customer_organization_id,status,created_at),
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id), FOREIGN KEY(asset_id) REFERENCES equipment(id),
 FOREIGN KEY(accepted_offer_id) REFERENCES marketplace_offers(id), FOREIGN KEY(accepted_offer_version_id) REFERENCES marketplace_offer_versions(id),
 FOREIGN KEY(supplier_organization_id) REFERENCES owners(id), FOREIGN KEY(customer_organization_id) REFERENCES owners(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_notifications (
 id CHAR(36) PRIMARY KEY, user_id CHAR(36) NOT NULL, event_type VARCHAR(80) NOT NULL,
 entity_type VARCHAR(40) NOT NULL, entity_id CHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, body VARCHAR(500) NULL,
 read_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX notification_inbox(user_id,read_at,created_at), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 014_marketplace_negotiation.sql

-- BEGIN 015_canonical_marketplace_equipment.sql
-- Marketplace listings are commercial configuration for canonical equipment.
-- This migration is intentionally additive/backward-compatible.
UPDATE equipment e
JOIN marketplace_listings l ON l.asset_id=e.id
SET e.long_description=l.description
WHERE (e.long_description IS NULL OR e.long_description='')
  AND (e.short_description IS NULL OR e.short_description='')
  AND l.description<>'';

UPDATE equipment e
JOIN marketplace_listings l ON l.asset_id=e.id
SET e.marketplace_specifications=l.public_specifications
WHERE e.marketplace_specifications IS NULL
  AND l.public_specifications IS NOT NULL;

UPDATE equipment e
JOIN marketplace_listings l ON l.asset_id=e.id
SET e.marketplace_only=0
WHERE e.marketplace_only=1;

CREATE OR REPLACE VIEW marketplace_equipment_view AS
SELECT
 l.id AS listing_id,
 e.id AS equipment_id,
 e.owner_id,
 e.name,
 e.asset_code,
 e.category_id,
 e.subcategory_id,
 e.type_id,
 e.marketplace_oem_id,
 e.marketplace_oem_model_id,
 e.manufacture_year,
 COALESCE(NULLIF(e.long_description,''),e.short_description) AS description,
 e.marketplace_specifications AS specifications,
 e.status AS equipment_status,
 e.unit_id,
 l.purpose,
 l.listing_status,
 l.marketplace_status,
 l.visibility,
 l.available_from,
 l.price_visibility,
 l.compliance_status,
 l.published_at,
 l.created_at,
 l.updated_at
FROM equipment e
JOIN marketplace_listings l ON l.asset_id=e.id;
-- END 015_canonical_marketplace_equipment.sql

-- BEGIN 016_marketplace_transaction_workspace.sql
ALTER TABLE marketplace_transactions
 MODIFY status ENUM('OFFER_ACCEPTED','AGREEMENT','PAYMENT_PENDING','PREMOBILIZATION','READY','MOBILIZING','ACTIVE','RETURN_PENDING','INSPECTION','SETTLEMENT','COMPLETED','CANCELLED','DISPUTED','DEFAULTED') NOT NULL DEFAULT 'OFFER_ACCEPTED';

CREATE TABLE marketplace_leases (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL UNIQUE, asset_id CHAR(36) NOT NULL,
 lessor_organization_id CHAR(36) NOT NULL, lessee_organization_id CHAR(36) NOT NULL,
 contracted_start DATE NOT NULL, contracted_end DATE NOT NULL, actual_start_at DATETIME NULL, actual_return_at DATETIME NULL,
 rate DECIMAL(15,2) NOT NULL, rate_basis ENUM('HOURLY','DAILY','WEEKLY','MONTHLY','PROJECT') NOT NULL,
 status ENUM('PENDING','PREMOBILIZATION','READY','MOBILIZING','ACTIVE','RETURN_PENDING','INSPECTION','SETTLEMENT','COMPLETED','CANCELLED') NOT NULL DEFAULT 'PENDING',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(asset_id) REFERENCES equipment(id),
 FOREIGN KEY(lessor_organization_id) REFERENCES owners(id), FOREIGN KEY(lessee_organization_id) REFERENCES owners(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_agreement_templates (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NULL, agreement_type ENUM('LEASE','SALE','ADDENDUM') NOT NULL,
 name VARCHAR(255) NOT NULL, version VARCHAR(40) NOT NULL, body_template LONGTEXT NOT NULL, is_active BOOLEAN NOT NULL DEFAULT TRUE,
 created_by CHAR(36) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY agreement_template_version(organization_id,agreement_type,name,version), FOREIGN KEY(organization_id) REFERENCES owners(id), FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_agreements (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, agreement_type ENUM('LEASE','SALE','ADDENDUM') NOT NULL,
 template_id CHAR(36) NULL, template_version VARCHAR(40) NOT NULL, current_version INT UNSIGNED NOT NULL DEFAULT 1,
 status ENUM('DRAFT','REVIEW','SENT','PARTIALLY_SIGNED','SIGNED','VOID') NOT NULL DEFAULT 'DRAFT',
 generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, signed_at DATETIME NULL, created_by CHAR(36) NOT NULL,
 INDEX agreement_transaction(transaction_id,status), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE,
 FOREIGN KEY(template_id) REFERENCES marketplace_agreement_templates(id), FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_agreement_versions (
 id CHAR(36) PRIMARY KEY, agreement_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL,
 body LONGTEXT NOT NULL, terms_snapshot JSON NOT NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY agreement_version(agreement_id,version_number), FOREIGN KEY(agreement_id) REFERENCES marketplace_agreements(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_agreement_signatures (
 id CHAR(36) PRIMARY KEY, agreement_id CHAR(36) NOT NULL, agreement_version_id CHAR(36) NOT NULL,
 organization_id CHAR(36) NOT NULL, signed_by CHAR(36) NOT NULL, signer_name VARCHAR(255) NOT NULL,
 signature_method ENUM('CONFIRMATION','PROVIDER') NOT NULL DEFAULT 'CONFIRMATION', provider_reference VARCHAR(255) NULL,
 signed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY agreement_party_signature(agreement_id,organization_id),
 FOREIGN KEY(agreement_id) REFERENCES marketplace_agreements(id) ON DELETE CASCADE, FOREIGN KEY(agreement_version_id) REFERENCES marketplace_agreement_versions(id),
 FOREIGN KEY(organization_id) REFERENCES owners(id), FOREIGN KEY(signed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_payments (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, payment_provider VARCHAR(80) NULL,
 provider_reference VARCHAR(255) NULL, idempotency_key VARCHAR(255) NOT NULL, amount DECIMAL(15,2) NOT NULL, currency CHAR(3) NOT NULL,
 payment_type ENUM('DEPOSIT','FULL','MILESTONE','EXTENSION','SETTLEMENT') NOT NULL,
 status ENUM('PENDING','CONFIRMED','FAILED','REFUNDED','PARTIALLY_REFUNDED') NOT NULL DEFAULT 'PENDING',
 due_at DATETIME NULL, confirmed_at DATETIME NULL, provider_payload JSON NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY payment_idempotency(idempotency_key), UNIQUE KEY payment_provider_reference(payment_provider,provider_reference), INDEX payment_transaction(transaction_id,status),
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_escrow_records (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, provider VARCHAR(80) NULL, external_reference VARCHAR(255) NULL,
 secured_amount DECIMAL(15,2) NOT NULL, currency CHAR(3) NOT NULL,
 status ENUM('PENDING','FUNDED','PARTIALLY_RELEASED','RELEASED','REFUNDED','DISPUTED') NOT NULL DEFAULT 'PENDING',
 funded_at DATETIME NULL, released_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY escrow_external(provider,external_reference), INDEX escrow_transaction(transaction_id,status), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_insurance_records (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, provider VARCHAR(255) NOT NULL, policy_reference VARCHAR(255) NOT NULL,
 coverage_start DATETIME NOT NULL, coverage_end DATETIME NOT NULL,
 status ENUM('QUOTED','SELECTED','ACTIVE','EXPIRED','CANCELLED') NOT NULL DEFAULT 'SELECTED', notes TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY insurance_policy(provider,policy_reference), INDEX insurance_transaction(transaction_id,status),
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_handover_records (
 id CHAR(36) PRIMARY KEY, lease_id CHAR(36) NOT NULL, event_type ENUM('HANDOVER','RETURN') NOT NULL,
 inspected_by CHAR(36) NOT NULL, location VARCHAR(500) NOT NULL, runtime_reading DECIMAL(15,2) NULL, mileage_reading DECIMAL(15,2) NULL,
 condition_state ENUM('GOOD','ACCEPTABLE','DAMAGED','CRITICAL') NOT NULL, checklist JSON NOT NULL, notes TEXT NULL,
 owner_signature VARCHAR(255) NULL, customer_signature VARCHAR(255) NULL, completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY lease_handover_event(lease_id,event_type), FOREIGN KEY(lease_id) REFERENCES marketplace_leases(id) ON DELETE CASCADE, FOREIGN KEY(inspected_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_handover_media (
 id CHAR(36) PRIMARY KEY, handover_id CHAR(36) NOT NULL, file_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL,
 file_size INT UNSIGNED NOT NULL, file_data MEDIUMBLOB NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(handover_id) REFERENCES marketplace_handover_records(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_transaction_exceptions (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, gate_key VARCHAR(80) NOT NULL, reason TEXT NOT NULL,
 approved_by CHAR(36) NOT NULL, expires_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX transaction_exception(transaction_id,gate_key,expires_at), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(approved_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_transaction_activity (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, transaction_id CHAR(36) NOT NULL, actor_user_id CHAR(36) NULL,
 actor_organization_id CHAR(36) NULL, event_type VARCHAR(100) NOT NULL, event_data JSON NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX transaction_activity(transaction_id,created_at,id), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE,
 FOREIGN KEY(actor_user_id) REFERENCES users(id), FOREIGN KEY(actor_organization_id) REFERENCES owners(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 016_marketplace_transaction_workspace.sql

-- BEGIN 017_marketplace_agreement_templates.sql
INSERT INTO marketplace_agreement_templates(id,organization_id,agreement_type,name,version,body_template)
VALUES
(UUID(),NULL,'LEASE','Nonagon standard equipment lease','1.0','NONAGON {{agreement_name}} AGREEMENT\n\nEquipment: {{equipment}}\nSupplier: {{supplier}}\nCustomer: {{customer}}\nValue: {{value}}\n\nThis agreement incorporates the accepted offer terms and the recorded mobilization, insurance, payment, handover and return obligations.'),
(UUID(),NULL,'SALE','Nonagon standard equipment sale','1.0','NONAGON {{agreement_name}} AGREEMENT\n\nEquipment: {{equipment}}\nSupplier: {{supplier}}\nCustomer: {{customer}}\nValue: {{value}}\n\nThis agreement incorporates the accepted offer terms and the recorded payment, insurance and handover obligations.');
-- END 017_marketplace_agreement_templates.sql

-- BEGIN 018_marketplace_lifecycle_completion.sql
CREATE TABLE marketplace_transaction_documents (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, document_type ENUM('AGREEMENT','ADDENDUM','HANDOVER','RETURN','INVOICE','RECEIPT','INSURANCE','OTHER') NOT NULL,
 title VARCHAR(255) NOT NULL, version INT UNSIGNED NOT NULL DEFAULT 1, mime_type VARCHAR(100) NOT NULL DEFAULT 'text/plain', content LONGTEXT NULL,
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY transaction_document_version(transaction_id,document_type,version), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_lease_incidents (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, lease_id CHAR(36) NOT NULL, reported_by CHAR(36) NOT NULL,
 category ENUM('BREAKDOWN','DAMAGE','SAFETY','MISSING_COMPONENT','OTHER') NOT NULL, severity ENUM('LOW','MEDIUM','HIGH','CRITICAL') NOT NULL,
 title VARCHAR(255) NOT NULL, description TEXT NOT NULL, status ENUM('OPEN','ACKNOWLEDGED','RESOLVED','CLOSED') NOT NULL DEFAULT 'OPEN',
 erp_alert_id CHAR(36) NULL, maintenance_id CHAR(36) NULL, insurance_claim_reference VARCHAR(255) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, resolved_at DATETIME NULL,
 INDEX incident_lease(lease_id,status,created_at), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE,
 FOREIGN KEY(lease_id) REFERENCES marketplace_leases(id) ON DELETE CASCADE, FOREIGN KEY(reported_by) REFERENCES users(id), FOREIGN KEY(erp_alert_id) REFERENCES alerts(id), FOREIGN KEY(maintenance_id) REFERENCES maintenance_records(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_lease_extensions (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, lease_id CHAR(36) NOT NULL, requested_by_organization_id CHAR(36) NOT NULL,
 current_version INT UNSIGNED NOT NULL DEFAULT 1, status ENUM('REQUESTED','COUNTERED','ACCEPTED','REJECTED','CANCELLED') NOT NULL DEFAULT 'REQUESTED',
 accepted_version_id CHAR(36) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX extension_transaction(transaction_id,status), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(lease_id) REFERENCES marketplace_leases(id) ON DELETE CASCADE, FOREIGN KEY(requested_by_organization_id) REFERENCES owners(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_lease_extension_versions (
 id CHAR(36) PRIMARY KEY, extension_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL, proposed_by_organization_id CHAR(36) NOT NULL,
 proposed_end DATE NOT NULL, amount DECIMAL(15,2) NOT NULL, currency CHAR(3) NOT NULL, notes TEXT NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY extension_version(extension_id,version_number), FOREIGN KEY(extension_id) REFERENCES marketplace_lease_extensions(id) ON DELETE CASCADE,
 FOREIGN KEY(proposed_by_organization_id) REFERENCES owners(id), FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
ALTER TABLE marketplace_lease_extensions ADD FOREIGN KEY(accepted_version_id) REFERENCES marketplace_lease_extension_versions(id);

CREATE TABLE marketplace_return_exceptions (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, handover_id CHAR(36) NOT NULL,
 exception_type ENUM('DAMAGE','MISSING_COMPONENT','EXCESS_RUNTIME','EXCESS_MILEAGE','CLEANING','LATE_RETURN','OTHER') NOT NULL,
 description TEXT NOT NULL, amount DECIMAL(15,2) NOT NULL DEFAULT 0, currency CHAR(3) NOT NULL, status ENUM('OPEN','ACCEPTED','DISPUTED','WAIVED','SETTLED') NOT NULL DEFAULT 'OPEN',
 insurance_claim_reference VARCHAR(255) NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX return_exception(transaction_id,status), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(handover_id) REFERENCES marketplace_handover_records(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_settlements (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL UNIQUE, currency CHAR(3) NOT NULL,
 gross_amount DECIMAL(15,2) NOT NULL, additional_charges DECIMAL(15,2) NOT NULL DEFAULT 0, deposit_applied DECIMAL(15,2) NOT NULL DEFAULT 0,
 refund_amount DECIMAL(15,2) NOT NULL DEFAULT 0, platform_fee DECIMAL(15,2) NOT NULL DEFAULT 0, supplier_payout DECIMAL(15,2) NOT NULL DEFAULT 0,
 status ENUM('DRAFT','DISPUTED','APPROVED','SETTLED') NOT NULL DEFAULT 'DRAFT', approved_by CHAR(36) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, settled_at DATETIME NULL,
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(approved_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE equipment_ownership_history (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, previous_owner_id CHAR(36) NOT NULL, new_owner_id CHAR(36) NOT NULL,
 transaction_id CHAR(36) NOT NULL UNIQUE, transferred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, transferred_by CHAR(36) NOT NULL,
 FOREIGN KEY(equipment_id) REFERENCES equipment(id), FOREIGN KEY(previous_owner_id) REFERENCES owners(id), FOREIGN KEY(new_owner_id) REFERENCES owners(id),
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id), FOREIGN KEY(transferred_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_reviews (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, reviewer_organization_id CHAR(36) NOT NULL, reviewed_organization_id CHAR(36) NOT NULL,
 rating TINYINT UNSIGNED NOT NULL, review_text TEXT NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY transaction_reviewer(transaction_id,reviewer_organization_id), INDEX reviewed_rating(reviewed_organization_id,rating),
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(reviewer_organization_id) REFERENCES owners(id), FOREIGN KEY(reviewed_organization_id) REFERENCES owners(id), FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_commercial_audit (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, transaction_id CHAR(36) NOT NULL, sequence_number INT UNSIGNED NOT NULL,
 actor_user_id CHAR(36) NULL, event_type VARCHAR(100) NOT NULL, event_data JSON NULL, previous_hash CHAR(64) NULL, event_hash CHAR(64) NOT NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), UNIQUE KEY transaction_audit_sequence(transaction_id,sequence_number), UNIQUE KEY commercial_event_hash(event_hash),
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 018_marketplace_lifecycle_completion.sql

-- BEGIN 019_marketplace_extension_delete_rule.sql
ALTER TABLE marketplace_lease_extensions DROP FOREIGN KEY marketplace_lease_extensions_ibfk_4;
ALTER TABLE marketplace_lease_extensions ADD CONSTRAINT marketplace_extension_accepted_version FOREIGN KEY(accepted_version_id) REFERENCES marketplace_lease_extension_versions(id) ON DELETE SET NULL;
-- END 019_marketplace_extension_delete_rule.sql

-- BEGIN 020_request_supply_sprint1.sql
CREATE TABLE marketplace_requests (
 id CHAR(36) PRIMARY KEY, requester_user_id CHAR(36) NOT NULL, organization_id CHAR(36) NOT NULL,
 equipment_type_id CHAR(36) NOT NULL, category_id CHAR(36) NULL, subcategory_id CHAR(36) NULL,
 title VARCHAR(255) NOT NULL, description TEXT NOT NULL, intent ENUM('LEASE','PURCHASE','EITHER') NOT NULL,
 quantity_required INT UNSIGNED NOT NULL DEFAULT 1, required_location_id CHAR(36) NOT NULL,
 required_from DATE NOT NULL, required_until DATE NULL, response_deadline DATETIME NOT NULL,
 visibility ENUM('PRIVATE','PUBLIC') NOT NULL DEFAULT 'PUBLIC', alternatives_accepted BOOLEAN NOT NULL DEFAULT TRUE,
 core_specifications JSON NULL, required_documents TEXT NULL, oem_preference VARCHAR(255) NULL, model_preference VARCHAR(255) NULL,
 current_version INT UNSIGNED NOT NULL DEFAULT 1,
 status ENUM('DRAFT','OPEN','RESPONSES_RECEIVED','AWARDED','FULFILLED','EXPIRED','CLOSED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
 published_at DATETIME NULL, expired_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX request_discovery(status,visibility,response_deadline,required_from), INDEX request_owner(organization_id,status,updated_at), INDEX request_equipment(equipment_type_id,status),
 FOREIGN KEY(requester_user_id) REFERENCES users(id), FOREIGN KEY(organization_id) REFERENCES owners(id),
 FOREIGN KEY(equipment_type_id) REFERENCES equipment_types(id), FOREIGN KEY(category_id) REFERENCES equipment_categories(id), FOREIGN KEY(subcategory_id) REFERENCES equipment_subcategories(id), FOREIGN KEY(required_location_id) REFERENCES marketplace_locations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE marketplace_request_versions (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL, snapshot JSON NOT NULL,
 change_reason VARCHAR(500) NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY request_version(request_id,version_number), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE request_requirements (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, attribute_key VARCHAR(120) NOT NULL, required_value JSON NOT NULL,
 comparison_operator VARCHAR(20) NULL, requirement_level ENUM('MANDATORY','PREFERRED','INFORMATIONAL') NOT NULL DEFAULT 'MANDATORY', notes TEXT NULL,
 INDEX request_requirement(request_id,requirement_level), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE supply_offers (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, supplier_organization_id CHAR(36) NOT NULL, created_by CHAR(36) NOT NULL,
 equipment_id CHAR(36) NOT NULL, marketplace_listing_id CHAR(36) NULL, quantity_offered INT UNSIGNED NOT NULL,
 current_version INT UNSIGNED NOT NULL DEFAULT 1, accepted_version_id CHAR(36) NULL,
 status ENUM('DRAFT','SUBMITTED','VIEWED','ACCEPTED','NOT_SELECTED','WITHDRAWN','EXPIRED') NOT NULL DEFAULT 'DRAFT',
 submitted_at DATETIME NULL, viewed_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX supply_request(request_id,status,updated_at), INDEX supply_supplier(supplier_organization_id,status,updated_at),
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE, FOREIGN KEY(supplier_organization_id) REFERENCES owners(id), FOREIGN KEY(created_by) REFERENCES users(id),
 FOREIGN KEY(equipment_id) REFERENCES equipment(id), FOREIGN KEY(marketplace_listing_id) REFERENCES marketplace_listings(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE supply_offer_versions (
 id CHAR(36) PRIMARY KEY, supply_offer_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL,
 commercial_terms JSON NOT NULL, technical_response JSON NOT NULL, deviations JSON NULL, validity_until DATETIME NULL,
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY supply_offer_version(supply_offer_id,version_number), FOREIGN KEY(supply_offer_id) REFERENCES supply_offers(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
ALTER TABLE supply_offers ADD CONSTRAINT supply_offer_accepted_version FOREIGN KEY(accepted_version_id) REFERENCES supply_offer_versions(id) ON DELETE SET NULL;

CREATE TABLE supply_offer_documents (
 id CHAR(36) PRIMARY KEY, supply_offer_version_id CHAR(36) NOT NULL, file_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, file_size INT UNSIGNED NOT NULL, file_data MEDIUMBLOB NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(supply_offer_version_id) REFERENCES supply_offer_versions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE request_awards (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL UNIQUE, supply_offer_id CHAR(36) NOT NULL UNIQUE, supply_offer_version_id CHAR(36) NOT NULL,
 awarded_quantity INT UNSIGNED NOT NULL, transaction_type ENUM('LEASE','PURCHASE') NOT NULL, transaction_id CHAR(36) NULL UNIQUE,
 awarded_by CHAR(36) NOT NULL, awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id), FOREIGN KEY(supply_offer_id) REFERENCES supply_offers(id), FOREIGN KEY(supply_offer_version_id) REFERENCES supply_offer_versions(id), FOREIGN KEY(awarded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE request_asset_reservations (
 id CHAR(36) PRIMARY KEY, award_id CHAR(36) NOT NULL UNIQUE, equipment_id CHAR(36) NOT NULL, quantity INT UNSIGNED NOT NULL,
 start_date DATE NOT NULL, end_date DATE NULL, status ENUM('CONFIRMED','RELEASED','CANCELLED') NOT NULL DEFAULT 'CONFIRMED', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX request_asset_reservation(equipment_id,status,start_date,end_date), FOREIGN KEY(award_id) REFERENCES request_awards(id) ON DELETE CASCADE, FOREIGN KEY(equipment_id) REFERENCES equipment(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE request_messages (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, supply_offer_id CHAR(36) NOT NULL, sender_user_id CHAR(36) NOT NULL,
 message_text TEXT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX request_message_thread(supply_offer_id,created_at), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE, FOREIGN KEY(supply_offer_id) REFERENCES supply_offers(id) ON DELETE CASCADE, FOREIGN KEY(sender_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE request_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_id CHAR(36) NOT NULL, actor_user_id CHAR(36) NULL, event_type VARCHAR(100) NOT NULL,
 event_data JSON NULL, created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), INDEX request_event(request_id,created_at,id),
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE, FOREIGN KEY(actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE marketplace_transactions DROP FOREIGN KEY marketplace_transactions_ibfk_1;
ALTER TABLE marketplace_transactions DROP FOREIGN KEY marketplace_transactions_ibfk_3;
ALTER TABLE marketplace_transactions DROP FOREIGN KEY marketplace_transactions_ibfk_4;
ALTER TABLE marketplace_transactions MODIFY listing_id CHAR(36) NULL, MODIFY accepted_offer_id CHAR(36) NULL, MODIFY accepted_offer_version_id CHAR(36) NULL;
ALTER TABLE marketplace_transactions ADD request_award_id CHAR(36) NULL UNIQUE,
 ADD CONSTRAINT transaction_listing_fk FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id),
 ADD CONSTRAINT transaction_offer_fk FOREIGN KEY(accepted_offer_id) REFERENCES marketplace_offers(id),
 ADD CONSTRAINT transaction_offer_version_fk FOREIGN KEY(accepted_offer_version_id) REFERENCES marketplace_offer_versions(id),
 ADD CONSTRAINT transaction_request_award_fk FOREIGN KEY(request_award_id) REFERENCES request_awards(id);
ALTER TABLE request_awards ADD CONSTRAINT request_award_transaction_fk FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE SET NULL;
-- END 020_request_supply_sprint1.sql

-- BEGIN 021_request_photos.sql
CREATE TABLE request_photos (
 id CHAR(36) PRIMARY KEY,
 request_id CHAR(36) NOT NULL,
 file_name VARCHAR(255) NOT NULL,
 mime_type VARCHAR(100) NOT NULL,
 file_size INT UNSIGNED NOT NULL,
 image_data MEDIUMBLOB NOT NULL,
 caption VARCHAR(255) NULL,
 is_primary BOOLEAN NOT NULL DEFAULT FALSE,
 sequence INT UNSIGNED NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX request_photo_order(request_id,is_primary,sequence,created_at),
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 021_request_photos.sql

-- BEGIN 022_request_distribution_sprint2.sql
CREATE TABLE request_share_links (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, token CHAR(64) NOT NULL UNIQUE,
 access_type ENUM('PUBLIC','PRIVATE_LINK','INVITATION') NOT NULL, campaign_id CHAR(36) NULL,
 expires_at DATETIME NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 revoked_at DATETIME NULL, INDEX request_share(request_id,access_type,created_at),
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE distribution_campaigns (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL,
 channel ENUM('SUPPLIER_NETWORK','SELECTED_SUPPLIERS','EMAIL','WHATSAPP','DIRECT_LINK','SOCIAL','PAID') NOT NULL,
 created_by CHAR(36) NOT NULL, audience_definition JSON NULL, approved_content TEXT NULL,
 budget DECIMAL(18,2) NULL, currency CHAR(3) NULL,
 status ENUM('DRAFT','ACTIVE','ENDED','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
 started_at DATETIME NULL, ended_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX request_campaign(request_id,status,created_at), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE,
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE request_share_links ADD CONSTRAINT request_share_campaign_fk FOREIGN KEY(campaign_id) REFERENCES distribution_campaigns(id) ON DELETE SET NULL;

CREATE TABLE supplier_invitations (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, share_link_id CHAR(36) NOT NULL,
 organization_id CHAR(36) NULL, contact_name VARCHAR(255) NULL, contact_email VARCHAR(255) NULL, contact_phone VARCHAR(50) NULL,
 channel ENUM('EMAIL','WHATSAPP','DIRECT_LINK','SUPPLIER_NETWORK') NOT NULL,
 status ENUM('SENT','VIEWED','RESPONDED','CANCELLED','EXPIRED') NOT NULL DEFAULT 'SENT',
 invited_by CHAR(36) NOT NULL, sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 viewed_at DATETIME NULL, responded_at DATETIME NULL, offer_id CHAR(36) NULL,
 INDEX request_invitation(request_id,status,sent_at), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE,
 FOREIGN KEY(share_link_id) REFERENCES request_share_links(id) ON DELETE CASCADE, FOREIGN KEY(organization_id) REFERENCES owners(id),
 FOREIGN KEY(invited_by) REFERENCES users(id), FOREIGN KEY(offer_id) REFERENCES supply_offers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE external_supplier_responses (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, share_link_id CHAR(36) NOT NULL, invitation_id CHAR(36) NULL,
 contact_name VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL, contact_email VARCHAR(255) NOT NULL, contact_phone VARCHAR(50) NULL,
 equipment_description TEXT NOT NULL, oem_name VARCHAR(255) NULL, model_name VARCHAR(255) NULL,
 availability_from DATE NOT NULL, availability_until DATE NULL, quantity_offered INT UNSIGNED NOT NULL DEFAULT 1,
 amount DECIMAL(18,2) NOT NULL, currency CHAR(3) NOT NULL, pricing_basis VARCHAR(20) NOT NULL,
 technical_response TEXT NULL, notes TEXT NULL, document_name VARCHAR(255) NULL, document_mime VARCHAR(100) NULL, document_data MEDIUMBLOB NULL,
 status ENUM('SUBMITTED','CONTACTED','CONVERTED','REJECTED') NOT NULL DEFAULT 'SUBMITTED', submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX external_request(request_id,status,submitted_at), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE,
 FOREIGN KEY(share_link_id) REFERENCES request_share_links(id), FOREIGN KEY(invitation_id) REFERENCES supplier_invitations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE campaign_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_id CHAR(36) NOT NULL, campaign_id CHAR(36) NULL,
 share_link_id CHAR(36) NULL, invitation_id CHAR(36) NULL, event_type VARCHAR(50) NOT NULL,
 attribution JSON NULL, occurred_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX campaign_timeline(request_id,occurred_at,id), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE,
 FOREIGN KEY(campaign_id) REFERENCES distribution_campaigns(id) ON DELETE SET NULL,
 FOREIGN KEY(share_link_id) REFERENCES request_share_links(id) ON DELETE SET NULL,
 FOREIGN KEY(invitation_id) REFERENCES supplier_invitations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 022_request_distribution_sprint2.sql

-- BEGIN 023_saved_marketplace_requests.sql
CREATE TABLE marketplace_saved_requests (
 user_id CHAR(36) NOT NULL,
 request_id CHAR(36) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,request_id),
 INDEX saved_request(request_id,created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 023_saved_marketplace_requests.sql

-- BEGIN 024_commercial_sprint1.sql
CREATE TABLE commercial_profiles (
 organization_id CHAR(36) PRIMARY KEY, legal_name VARCHAR(255) NOT NULL, trading_name VARCHAR(255) NULL, registered_address TEXT NULL,
 email VARCHAR(255) NULL, phone VARCHAR(50) NULL, website VARCHAR(255) NULL, tin VARCHAR(100) NULL, rc_number VARCHAR(100) NULL,
 default_currency CHAR(3) NOT NULL DEFAULT 'NGN', default_vat DECIMAL(6,3) NOT NULL DEFAULT 7.500, default_payment_terms VARCHAR(255) NULL,
 quotation_terms TEXT NULL, invoice_terms TEXT NULL, authorized_signatory VARCHAR(255) NULL, quote_prefix VARCHAR(20) NOT NULL DEFAULT 'QT',
 invoice_prefix VARCHAR(20) NOT NULL DEFAULT 'INV', next_quote_number INT UNSIGNED NOT NULL DEFAULT 1, next_invoice_number INT UNSIGNED NOT NULL DEFAULT 1,
 updated_by CHAR(36) NOT NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE commercial_bank_accounts (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL, account_name VARCHAR(255) NOT NULL, bank_name VARCHAR(255) NOT NULL,
 account_number VARCHAR(100) NOT NULL, currency CHAR(3) NOT NULL, swift_code VARCHAR(50) NULL, instructions TEXT NULL, is_default BOOLEAN NOT NULL DEFAULT FALSE,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX commercial_bank_owner(organization_id,currency),
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE commercial_customers (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, customer_code VARCHAR(100) NULL, rc_number VARCHAR(100) NULL,
 industry VARCHAR(150) NULL, website VARCHAR(255) NULL, primary_contact VARCHAR(255) NULL, finance_contact VARCHAR(255) NULL,
 procurement_contact VARCHAR(255) NULL, email VARCHAR(255) NULL, phone VARCHAR(50) NULL, billing_address TEXT NULL, service_address TEXT NULL,
 state_region VARCHAR(150) NULL, country VARCHAR(100) NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN', payment_terms VARCHAR(255) NULL,
 credit_period INT UNSIGNED NULL, notes TEXT NULL, tin VARCHAR(100) NULL, tax_registered_name VARCHAR(255) NULL,
 tax_registered_address TEXT NULL, tax_jurisdiction VARCHAR(150) NULL, tax_authority VARCHAR(150) NULL,
 tax_verification_status ENUM('UNVERIFIED','PENDING','VERIFIED','REJECTED') NOT NULL DEFAULT 'UNVERIFIED', tax_last_verified_at DATETIME NULL,
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY commercial_customer_code(organization_id,customer_code), INDEX commercial_customer_search(organization_id,name),
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE commercial_documents (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL, customer_id CHAR(36) NOT NULL, document_type ENUM('QUOTATION','INVOICE') NOT NULL,
 document_number VARCHAR(80) NOT NULL, status VARCHAR(30) NOT NULL DEFAULT 'DRAFT', issue_date DATE NOT NULL, valid_until DATE NULL, due_date DATE NULL,
 currency CHAR(3) NOT NULL, customer_po VARCHAR(150) NULL, customer_reference VARCHAR(150) NULL, rfq_number VARCHAR(150) NULL,
 prepared_by CHAR(36) NOT NULL, converted_from_id CHAR(36) NULL, bank_account_id CHAR(36) NULL, payment_terms TEXT NULL, delivery_terms TEXT NULL,
 lead_time VARCHAR(255) NULL, notes TEXT NULL, subtotal DECIMAL(18,2) NOT NULL DEFAULT 0, discount_total DECIMAL(18,2) NOT NULL DEFAULT 0,
 tax_total DECIMAL(18,2) NOT NULL DEFAULT 0, adjustment_total DECIMAL(18,2) NOT NULL DEFAULT 0, grand_total DECIMAL(18,2) NOT NULL DEFAULT 0,
 issued_snapshot JSON NULL, issued_at DATETIME NULL, cancelled_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY commercial_document_number(organization_id,document_type,document_number), INDEX commercial_document_register(organization_id,document_type,status,issue_date),
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(customer_id) REFERENCES commercial_customers(id),
 FOREIGN KEY(prepared_by) REFERENCES users(id), FOREIGN KEY(converted_from_id) REFERENCES commercial_documents(id), FOREIGN KEY(bank_account_id) REFERENCES commercial_bank_accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE commercial_line_items (
 id CHAR(36) PRIMARY KEY, document_id CHAR(36) NOT NULL, description TEXT NOT NULL, quantity DECIMAL(14,3) NOT NULL DEFAULT 1,
 unit VARCHAR(40) NOT NULL DEFAULT 'Each', duration DECIMAL(14,3) NOT NULL DEFAULT 1, rate DECIMAL(18,2) NOT NULL DEFAULT 0,
 discount_rate DECIMAL(6,3) NOT NULL DEFAULT 0, tax_rate DECIMAL(6,3) NOT NULL DEFAULT 0, base_amount DECIMAL(18,2) NOT NULL,
 discount_amount DECIMAL(18,2) NOT NULL, tax_amount DECIMAL(18,2) NOT NULL, total_amount DECIMAL(18,2) NOT NULL,
 source_type VARCHAR(50) NULL, source_id CHAR(36) NULL, source_snapshot JSON NULL, sort_order INT UNSIGNED NOT NULL DEFAULT 0,
 FOREIGN KEY(document_id) REFERENCES commercial_documents(id) ON DELETE CASCADE, INDEX commercial_line_document(document_id,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE commercial_tax_records (
 id CHAR(36) PRIMARY KEY, invoice_id CHAR(36) NOT NULL UNIQUE, vat_rate DECIMAL(6,3) NOT NULL DEFAULT 0, vat_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
 wht_applicable BOOLEAN NOT NULL DEFAULT FALSE, wht_rate DECIMAL(6,3) NOT NULL DEFAULT 0, expected_wht DECIMAL(18,2) NOT NULL DEFAULT 0,
 wht_status ENUM('NOT_APPLICABLE','EXPECTED','DEDUCTED','SUBMITTED','CREDIT_CONFIRMED','RECONCILED') NOT NULL DEFAULT 'NOT_APPLICABLE',
 customer_tin VARCHAR(100) NULL, tax_authority VARCHAR(150) NULL, submission_reference VARCHAR(255) NULL, reconciliation_date DATE NULL,
 FOREIGN KEY(invoice_id) REFERENCES commercial_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE commercial_document_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, document_id CHAR(36) NOT NULL, actor_user_id CHAR(36) NULL, event_type VARCHAR(80) NOT NULL,
 previous_value JSON NULL, new_value JSON NULL, created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX commercial_event_document(document_id,created_at,id), FOREIGN KEY(document_id) REFERENCES commercial_documents(id) ON DELETE CASCADE,
 FOREIGN KEY(actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 024_commercial_sprint1.sql

-- BEGIN 025_commercial_branding.sql
CREATE TABLE commercial_brands (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL, brand_name VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL,
 creation_mode ENUM('ELEMENTS','ARTWORK') NOT NULL, addresses JSON NULL, phones JSON NULL, emails JSON NULL, website VARCHAR(255) NULL,
 logo_mime VARCHAR(50) NULL, logo_data MEDIUMBLOB NULL, header_mime VARCHAR(50) NULL, header_data MEDIUMBLOB NULL,
 footer_mime VARCHAR(50) NULL, footer_data MEDIUMBLOB NULL, is_default BOOLEAN NOT NULL DEFAULT FALSE,
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX commercial_brand_owner(organization_id,is_default,brand_name), FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE,
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE commercial_documents ADD brand_id CHAR(36) NULL AFTER bank_account_id,
 ADD CONSTRAINT commercial_document_brand_fk FOREIGN KEY(brand_id) REFERENCES commercial_brands(id);
-- END 025_commercial_branding.sql

-- BEGIN 026_public_commercial_submissions.sql
CREATE TABLE public_commercial_submissions (
 id CHAR(36) PRIMARY KEY,
 session_reference CHAR(64) NULL,
 document_type ENUM('QUOTATION','INVOICE') NOT NULL,
 document_number VARCHAR(80) NOT NULL,
 issuer_name VARCHAR(255) NULL,
 issuer_email VARCHAR(255) NULL,
 customer_name VARCHAR(255) NULL,
 customer_email VARCHAR(255) NULL,
 payload JSON NOT NULL,
 logo_mime VARCHAR(50) NULL,
 logo_data MEDIUMBLOB NULL,
 terms_version VARCHAR(30) NOT NULL,
 terms_accepted_at DATETIME NOT NULL,
 marketing_consent BOOLEAN NOT NULL DEFAULT TRUE,
 ip_hash CHAR(64) NULL,
 user_agent VARCHAR(500) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX public_commercial_created(created_at),
 INDEX public_commercial_issuer_email(issuer_email),
 INDEX public_commercial_customer_email(customer_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 026_public_commercial_submissions.sql

-- BEGIN 027_commercial_signatories.sql
ALTER TABLE commercial_documents
 ADD signatory_name VARCHAR(255) NULL AFTER notes,
 ADD signatory_title VARCHAR(255) NULL AFTER signatory_name,
 ADD signature_mime VARCHAR(50) NULL AFTER signatory_title,
 ADD signature_data MEDIUMBLOB NULL AFTER signature_mime;

ALTER TABLE commercial_profiles
 ADD authorized_signatory_title VARCHAR(255) NULL AFTER authorized_signatory,
 ADD signature_mime VARCHAR(50) NULL AFTER authorized_signatory_title,
 ADD signature_data MEDIUMBLOB NULL AFTER signature_mime;

ALTER TABLE public_commercial_submissions
 ADD signature_mime VARCHAR(50) NULL AFTER logo_data,
 ADD signature_data MEDIUMBLOB NULL AFTER signature_mime;
-- END 027_commercial_signatories.sql

-- BEGIN 028_qhse_sprint1.sql
CREATE TABLE qhse_standards (
 id CHAR(36) PRIMARY KEY, code VARCHAR(30) NOT NULL UNIQUE, title VARCHAR(255) NOT NULL,
 focus_area VARCHAR(80) NOT NULL, description VARCHAR(500) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
INSERT INTO qhse_standards(id,code,title,focus_area,description) VALUES
('00000000-0000-4000-8000-000000009001','ISO 9001','Quality management systems','QUALITY','Quality processes, customer requirements, nonconformity and continual improvement.'),
('00000000-0000-4000-8000-000000014001','ISO 14001','Environmental management systems','ENVIRONMENT','Environmental aspects, obligations, operational controls and performance.'),
('00000000-0000-4000-8000-000000045001','ISO 45001','Occupational health and safety','HSE','Hazards, worker participation, operational controls and incident improvement.'),
('00000000-0000-4000-8000-000000014224','ISO 14224','Reliability and maintenance data','ASSET','Equipment taxonomy, reliability and maintenance evidence alignment.');
CREATE TABLE qhse_controls (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, control_code VARCHAR(60) NOT NULL,
 title VARCHAR(255) NOT NULL, domain ENUM('QUALITY','HSE','ENVIRONMENT','ASSET','GOVERNANCE') NOT NULL,
 status ENUM('DRAFT','ACTIVE','REVIEW_DUE','RETIRED') NOT NULL DEFAULT 'ACTIVE',
 control_owner_id CHAR(36) NULL, review_due_at DATE NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY qhse_control_code(owner_id,control_code), INDEX qhse_control_status(owner_id,status,review_due_at),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(control_owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE qhse_control_standards (
 control_id CHAR(36) NOT NULL, standard_id CHAR(36) NOT NULL, clause_reference VARCHAR(80) NOT NULL, mapping_note VARCHAR(500) NULL,
 PRIMARY KEY(control_id,standard_id,clause_reference), FOREIGN KEY(control_id) REFERENCES qhse_controls(id) ON DELETE CASCADE,
 FOREIGN KEY(standard_id) REFERENCES qhse_standards(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE qhse_evidence_links (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, control_id CHAR(36) NOT NULL,
 source_module VARCHAR(60) NOT NULL, source_type VARCHAR(60) NOT NULL, source_id VARCHAR(100) NOT NULL,
 source_url VARCHAR(500) NULL, evidence_label VARCHAR(255) NOT NULL, linked_by CHAR(36) NOT NULL,
 linked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY qhse_evidence_source(control_id,source_module,source_type,source_id), INDEX qhse_evidence_owner(owner_id,linked_at),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(control_id) REFERENCES qhse_controls(id) ON DELETE CASCADE,
 FOREIGN KEY(linked_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE qhse_activity_events (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, actor_id CHAR(36) NULL, event_type VARCHAR(60) NOT NULL,
 domain ENUM('QUALITY','HSE','ENVIRONMENT','AUDIT','RISK','APPROVAL','ASSET','GOVERNANCE') NOT NULL,
 title VARCHAR(255) NOT NULL, summary VARCHAR(1000) NULL,
 severity ENUM('INFO','LOW','MEDIUM','HIGH','CRITICAL') NOT NULL DEFAULT 'INFO',
 source_module VARCHAR(60) NOT NULL, source_type VARCHAR(60) NOT NULL, source_id VARCHAR(100) NOT NULL, source_url VARCHAR(500) NULL,
 occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX qhse_activity_feed(owner_id,occurred_at), INDEX qhse_activity_source(owner_id,source_module,source_type,source_id),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE qhse_user_roles (
 user_id CHAR(36) NOT NULL, owner_id CHAR(36) NOT NULL,
 qhse_role ENUM('QHSE_VIEWER','TECHNICIAN','INSPECTOR','QHSE_OFFICER','QUALITY_MANAGER','HSE_OFFICER','ENVIRONMENTAL_OFFICER','AUDITOR','APPROVER','QHSE_ADMINISTRATOR') NOT NULL,
 assigned_by CHAR(36) NOT NULL, assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,qhse_role), INDEX qhse_role_scope(owner_id,qhse_role),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(assigned_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 028_qhse_sprint1.sql

-- BEGIN 029_qhse_certificate_generation.sql
CREATE TABLE qhse_certificate_templates (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, certificate_type ENUM('HYDROSTATIC_PRESSURE_TEST','PRESSURE_PUMP_TEST','PRESSURE_GAUGE_CALIBRATION','PRESSURE_RELIEF_VALVE_CALIBRATION') NOT NULL,
 name VARCHAR(255) NOT NULL, version INT UNSIGNED NOT NULL DEFAULT 1, field_schema JSON NOT NULL, presentation_schema JSON NOT NULL,
 legal_statement TEXT NULL, is_active BOOLEAN NOT NULL DEFAULT TRUE, created_by CHAR(36) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 scope_key VARCHAR(36) AS (IFNULL(owner_id,'GLOBAL')) STORED, UNIQUE KEY qhse_template_version(scope_key,certificate_type,version),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
INSERT INTO qhse_certificate_templates(id,certificate_type,name,field_schema,presentation_schema,legal_statement) VALUES
('10000000-0000-4000-8000-000000000001','HYDROSTATIC_PRESSURE_TEST','Hydrostatic Pressure Test','["test_medium","test_pressure","proof_pressure","hold_time","ambient_temperature"]','{"sections":["identity","customer","equipment","test","result","signatures"],"qr":true}','This certificate records the stated inspection and test results at the time of examination.'),
('10000000-0000-4000-8000-000000000002','PRESSURE_PUMP_TEST','Pressure Pump Test','["test_medium","rated_pressure","test_pressure","hold_time","leakage_result"]','{"sections":["identity","customer","equipment","test","result","signatures"],"qr":true}','This certificate records the stated inspection and test results at the time of examination.'),
('10000000-0000-4000-8000-000000000003','PRESSURE_GAUGE_CALIBRATION','Pressure Gauge Calibration','["range","unit","accuracy_class","as_found","as_left","reference_instrument"]','{"sections":["identity","customer","equipment","calibration","result","signatures"],"qr":true}','Calibration results are traceable to the reference equipment identified in this certificate.'),
('10000000-0000-4000-8000-000000000004','PRESSURE_RELIEF_VALVE_CALIBRATION','Pressure Relief Valve Calibration','["set_pressure","unit","test_medium","reseat_pressure","leak_test","reference_instrument"]','{"sections":["identity","customer","equipment","calibration","result","signatures"],"qr":true}','Calibration results are traceable to the reference equipment identified in this certificate.');
CREATE TABLE qhse_certificate_sequences (
 owner_id CHAR(36) PRIMARY KEY, prefix VARCHAR(20) NOT NULL DEFAULT 'CERT', next_number INT UNSIGNED NOT NULL DEFAULT 1,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE qhse_certificates (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, certificate_number VARCHAR(80) NOT NULL, template_id CHAR(36) NOT NULL, template_version INT UNSIGNED NOT NULL,
 certificate_type ENUM('HYDROSTATIC_PRESSURE_TEST','PRESSURE_PUMP_TEST','PRESSURE_GAUGE_CALIBRATION','PRESSURE_RELIEF_VALVE_CALIBRATION') NOT NULL,
 status ENUM('DRAFT','AWAITING_REVIEW','APPROVED','ISSUED','REJECTED','REVOKED','SUPERSEDED') NOT NULL DEFAULT 'DRAFT',
 brand_id CHAR(36) NULL, customer_id CHAR(36) NOT NULL, customer_site VARCHAR(255) NULL, customer_po VARCHAR(150) NULL,
 project_job VARCHAR(255) NULL, location VARCHAR(500) NULL, issue_date DATE NULL, valid_from DATE NULL, expiry_date DATE NULL,
 inspection_test_type VARCHAR(255) NOT NULL, procedure_reference VARCHAR(255) NULL, standard_reference VARCHAR(255) NULL,
 technician_id CHAR(36) NULL, inspector_id CHAR(36) NULL, reviewer_id CHAR(36) NULL, approver_id CHAR(36) NULL,
 result ENUM('PASS','FAIL','CONDITIONAL') NOT NULL, observations TEXT NULL, remarks TEXT NULL, technical_data JSON NOT NULL,
 verification_token CHAR(32) NOT NULL, issued_snapshot JSON NULL, submitted_at DATETIME NULL, reviewed_at DATETIME NULL, approved_at DATETIME NULL,
 issued_at DATETIME NULL, revoked_at DATETIME NULL, rejection_reason VARCHAR(1000) NULL, created_by CHAR(36) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY qhse_certificate_number(owner_id,certificate_number), UNIQUE KEY qhse_certificate_verify(verification_token),
 INDEX qhse_certificate_register(owner_id,status,certificate_type,expiry_date), INDEX qhse_certificate_customer(owner_id,customer_id),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(template_id) REFERENCES qhse_certificate_templates(id),
 FOREIGN KEY(brand_id) REFERENCES commercial_brands(id) ON DELETE SET NULL, FOREIGN KEY(customer_id) REFERENCES commercial_customers(id),
 FOREIGN KEY(technician_id) REFERENCES users(id) ON DELETE SET NULL, FOREIGN KEY(inspector_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(reviewer_id) REFERENCES users(id) ON DELETE SET NULL, FOREIGN KEY(approver_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE qhse_certificate_equipment (
 certificate_id CHAR(36) NOT NULL, equipment_id CHAR(36) NOT NULL, customer_equipment_id VARCHAR(100) NULL,
 equipment_snapshot JSON NULL, sort_order INT UNSIGNED NOT NULL DEFAULT 0, PRIMARY KEY(certificate_id,equipment_id),
 FOREIGN KEY(certificate_id) REFERENCES qhse_certificates(id) ON DELETE CASCADE, FOREIGN KEY(equipment_id) REFERENCES equipment(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE qhse_certificate_reference_equipment (
 certificate_id CHAR(36) NOT NULL, equipment_id CHAR(36) NOT NULL, traceability_note VARCHAR(500) NULL,
 equipment_snapshot JSON NULL, PRIMARY KEY(certificate_id,equipment_id),
 FOREIGN KEY(certificate_id) REFERENCES qhse_certificates(id) ON DELETE CASCADE, FOREIGN KEY(equipment_id) REFERENCES equipment(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE qhse_certificate_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, certificate_id CHAR(36) NOT NULL, actor_id CHAR(36) NOT NULL,
 event_type VARCHAR(60) NOT NULL, from_status VARCHAR(30) NULL, to_status VARCHAR(30) NULL, notes VARCHAR(1000) NULL,
 occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX qhse_certificate_timeline(certificate_id,id),
 FOREIGN KEY(certificate_id) REFERENCES qhse_certificates(id) ON DELETE CASCADE, FOREIGN KEY(actor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 029_qhse_certificate_generation.sql

-- BEGIN 030_qhse_quick_create_validity.sql
ALTER TABLE qhse_certificates
 ADD COLUMN validity_duration INT UNSIGNED NULL AFTER valid_from,
 ADD COLUMN validity_unit ENUM('DAY','WEEK','MONTH','YEAR') NULL AFTER validity_duration;
CREATE TABLE qhse_completion_alerts (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, source_type ENUM('EQUIPMENT','OPERATOR','CUSTOMER','BRAND','CERTIFICATE_TEMPLATE') NOT NULL,
 source_id CHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, missing_fields JSON NOT NULL, status ENUM('OPEN','RESOLVED') NOT NULL DEFAULT 'OPEN',
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, resolved_at DATETIME NULL,
 UNIQUE KEY qhse_completion_source(owner_id,source_type,source_id), INDEX qhse_completion_open(owner_id,status,created_at),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 030_qhse_quick_create_validity.sql

-- BEGIN 031_qhse_dynamic_certificates.sql
ALTER TABLE qhse_certificates
 ADD COLUMN test_date DATE NULL AFTER location,
 ADD COLUMN customer_reference VARCHAR(150) NULL AFTER customer_po,
 ADD COLUMN client_representative VARCHAR(255) NULL AFTER approver_id,
 ADD COLUMN signoff_date DATE NULL AFTER client_representative,
 ADD COLUMN revision_number INT UNSIGNED NOT NULL DEFAULT 1 AFTER certificate_number,
 ADD COLUMN supersedes_id CHAR(36) NULL AFTER revision_number,
 ADD COLUMN superseded_by_id CHAR(36) NULL AFTER supersedes_id,
 ADD CONSTRAINT qhse_certificate_supersedes_fk FOREIGN KEY(supersedes_id) REFERENCES qhse_certificates(id) ON DELETE SET NULL,
 ADD CONSTRAINT qhse_certificate_superseded_by_fk FOREIGN KEY(superseded_by_id) REFERENCES qhse_certificates(id) ON DELETE SET NULL;
ALTER TABLE qhse_certificate_equipment ADD COLUMN connection_detail VARCHAR(255) NULL AFTER customer_equipment_id;
ALTER TABLE qhse_certificate_reference_equipment
 ADD COLUMN reference_role VARCHAR(40) NOT NULL DEFAULT 'PRIMARY' AFTER equipment_id,
 ADD COLUMN traceability_data JSON NULL AFTER traceability_note;
CREATE TABLE qhse_signature_assets (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, asset_name VARCHAR(255) NOT NULL, personnel_user_id CHAR(36) NULL,
 signatory_name VARCHAR(255) NOT NULL, role_title VARCHAR(255) NULL, signature_mime VARCHAR(50) NULL, signature_data MEDIUMBLOB NULL,
 stamp_mime VARCHAR(50) NULL, stamp_data MEDIUMBLOB NULL, is_active BOOLEAN NOT NULL DEFAULT TRUE,
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX qhse_signature_library(owner_id,is_active,asset_name), FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE,
 FOREIGN KEY(personnel_user_id) REFERENCES users(id) ON DELETE SET NULL, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE qhse_certificate_signatories (
 certificate_id CHAR(36) NOT NULL, signatory_role ENUM('TECHNICIAN','QA_REVIEWER','APPROVER','CLIENT_REPRESENTATIVE') NOT NULL,
 personnel_user_id CHAR(36) NULL, signature_asset_id CHAR(36) NULL, signatory_name VARCHAR(255) NULL, role_title VARCHAR(255) NULL,
 asset_snapshot JSON NULL, signed_at DATE NULL, PRIMARY KEY(certificate_id,signatory_role),
 FOREIGN KEY(certificate_id) REFERENCES qhse_certificates(id) ON DELETE CASCADE,
 FOREIGN KEY(personnel_user_id) REFERENCES users(id) ON DELETE SET NULL, FOREIGN KEY(signature_asset_id) REFERENCES qhse_signature_assets(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- END 031_qhse_dynamic_certificates.sql

-- BEGIN 032_qhse_nuprc_number.sql
ALTER TABLE qhse_certificates
 ADD COLUMN nuprc_number VARCHAR(150) NULL AFTER customer_reference;
-- END 032_qhse_nuprc_number.sql

-- BEGIN 033_equipment_traceability_fields.sql
ALTER TABLE equipment
 ADD COLUMN measurement_lower DECIMAL(18,6) NULL AFTER long_description,
 ADD COLUMN measurement_upper DECIMAL(18,6) NULL AFTER measurement_lower,
 ADD COLUMN measurement_unit VARCHAR(30) NULL AFTER measurement_upper,
 ADD COLUMN measurement_accuracy VARCHAR(100) NULL AFTER measurement_unit,
 ADD COLUMN calibration_date DATE NULL AFTER measurement_accuracy,
 ADD COLUMN calibration_due_date DATE NULL AFTER calibration_date,
 ADD COLUMN calibration_certificate_number VARCHAR(150) NULL AFTER calibration_due_date,
 ADD COLUMN calibration_status VARCHAR(60) NULL AFTER calibration_certificate_number;
-- END 033_equipment_traceability_fields.sql

-- BEGIN 034_equipment_prv_fields.sql
ALTER TABLE equipment
 ADD COLUMN nominal_size VARCHAR(100) NULL AFTER calibration_status,
 ADD COLUMN connection_specification VARCHAR(150) NULL AFTER nominal_size,
 ADD COLUMN set_pressure DECIMAL(18,6) NULL AFTER connection_specification,
 ADD COLUMN set_pressure_unit VARCHAR(30) NULL AFTER set_pressure,
 ADD COLUMN back_pressure DECIMAL(18,6) NULL AFTER set_pressure_unit,
 ADD COLUMN back_pressure_unit VARCHAR(30) NULL AFTER back_pressure;
-- END 034_equipment_prv_fields.sql

-- BEGIN 035_equipment_size_owner_units.sql
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
-- END 035_equipment_size_owner_units.sql

CREATE TABLE IF NOT EXISTS schema_migrations (
 version VARCHAR(100) PRIMARY KEY,
 applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO schema_migrations(version) VALUES
('001_dashboard.sql'),
('002_equipment_import.sql'),
('003_equipment_import_sequence.sql'),
('004_equipment_catalog.sql'),
('005_block_compatibility.sql'),
('006_default_status_identity.sql'),
('007_equipment_register.sql'),
('008_equipment_archive.sql'),
('009_marketplace_sprint1.sql'),
('010_marketplace_equipment_views.sql'),
('011_marketplace_availability_compliance.sql'),
('012_marketplace_structured_terms.sql'),
('013_marketplace_assets_are_standard.sql'),
('014_marketplace_negotiation.sql'),
('015_canonical_marketplace_equipment.sql'),
('016_marketplace_transaction_workspace.sql'),
('017_marketplace_agreement_templates.sql'),
('018_marketplace_lifecycle_completion.sql'),
('019_marketplace_extension_delete_rule.sql'),
('020_request_supply_sprint1.sql'),
('021_request_photos.sql'),
('022_request_distribution_sprint2.sql'),
('023_saved_marketplace_requests.sql'),
('024_commercial_sprint1.sql'),
('025_commercial_branding.sql'),
('026_public_commercial_submissions.sql'),
('027_commercial_signatories.sql'),
('028_qhse_sprint1.sql'),
('029_qhse_certificate_generation.sql'),
('030_qhse_quick_create_validity.sql'),
('031_qhse_dynamic_certificates.sql'),
('032_qhse_nuprc_number.sql'),
('033_equipment_traceability_fields.sql'),
('034_equipment_prv_fields.sql'),
('035_equipment_size_owner_units.sql');

