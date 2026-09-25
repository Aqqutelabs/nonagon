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
) ENGINE=InnoDB;
CREATE TABLE marketplace_oem_models (
 id CHAR(36) PRIMARY KEY, oem_id CHAR(36) NOT NULL, equipment_type_id CHAR(36) NULL,
 model_name VARCHAR(255) NOT NULL, standard_specifications JSON NULL,
 lifecycle_status ENUM('ACTIVE','LEGACY','OBSOLETE','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY oem_model(oem_id,model_name), INDEX model_type(equipment_type_id,model_name),
 FOREIGN KEY(oem_id) REFERENCES marketplace_oems(id), FOREIGN KEY(equipment_type_id) REFERENCES equipment_types(id)
) ENGINE=InnoDB;
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
) ENGINE=InnoDB;
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
) ENGINE=InnoDB;
CREATE TABLE marketplace_sale_terms (
 listing_id CHAR(36) PRIMARY KEY, asking_price DECIMAL(15,2) NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN',
 negotiable BOOLEAN NOT NULL DEFAULT FALSE, inspection_allowed BOOLEAN NOT NULL DEFAULT TRUE,
 `condition` ENUM('NEW','EXCELLENT','GOOD','FAIR','AS_IS') NOT NULL DEFAULT 'GOOD',
 delivery_terms TEXT NULL, included_items JSON NULL, sale_notes TEXT NULL,
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE marketplace_listing_media (
 id CHAR(36) PRIMARY KEY, listing_id CHAR(36) NOT NULL, equipment_photo_id CHAR(36) NULL,
 media_type ENUM('IMAGE','VIDEO','SPECIFICATION_SHEET','BROCHURE','CERTIFICATE','INSPECTION','OTHER') NOT NULL,
 title VARCHAR(255) NULL, external_url VARCHAR(500) NULL, mime_type VARCHAR(100) NULL, file_data MEDIUMBLOB NULL,
 visibility ENUM('PUBLIC','ON_REQUEST','PRIVATE') NOT NULL DEFAULT 'PUBLIC', is_primary BOOLEAN NOT NULL DEFAULT FALSE,
 sequence INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX listing_media(listing_id,visibility,sequence), UNIQUE KEY listing_equipment_photo(listing_id,equipment_photo_id), FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE,
 FOREIGN KEY(equipment_photo_id) REFERENCES equipment_photos(id) ON DELETE SET NULL
) ENGINE=InnoDB;
