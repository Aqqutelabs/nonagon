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
) ENGINE=InnoDB;

CREATE TABLE marketplace_request_versions (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL, snapshot JSON NOT NULL,
 change_reason VARCHAR(500) NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY request_version(request_id,version_number), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE request_requirements (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, attribute_key VARCHAR(120) NOT NULL, required_value JSON NOT NULL,
 comparison_operator VARCHAR(20) NULL, requirement_level ENUM('MANDATORY','PREFERRED','INFORMATIONAL') NOT NULL DEFAULT 'MANDATORY', notes TEXT NULL,
 INDEX request_requirement(request_id,requirement_level), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE supply_offers (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, supplier_organization_id CHAR(36) NOT NULL, created_by CHAR(36) NOT NULL,
 equipment_id CHAR(36) NOT NULL, marketplace_listing_id CHAR(36) NULL, quantity_offered INT UNSIGNED NOT NULL,
 current_version INT UNSIGNED NOT NULL DEFAULT 1, accepted_version_id CHAR(36) NULL,
 status ENUM('DRAFT','SUBMITTED','VIEWED','ACCEPTED','NOT_SELECTED','WITHDRAWN','EXPIRED') NOT NULL DEFAULT 'DRAFT',
 submitted_at DATETIME NULL, viewed_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX supply_request(request_id,status,updated_at), INDEX supply_supplier(supplier_organization_id,status,updated_at),
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE, FOREIGN KEY(supplier_organization_id) REFERENCES owners(id), FOREIGN KEY(created_by) REFERENCES users(id),
 FOREIGN KEY(equipment_id) REFERENCES equipment(id), FOREIGN KEY(marketplace_listing_id) REFERENCES marketplace_listings(id)
) ENGINE=InnoDB;

CREATE TABLE supply_offer_versions (
 id CHAR(36) PRIMARY KEY, supply_offer_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL,
 commercial_terms JSON NOT NULL, technical_response JSON NOT NULL, deviations JSON NULL, validity_until DATETIME NULL,
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY supply_offer_version(supply_offer_id,version_number), FOREIGN KEY(supply_offer_id) REFERENCES supply_offers(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;
ALTER TABLE supply_offers ADD CONSTRAINT supply_offer_accepted_version FOREIGN KEY(accepted_version_id) REFERENCES supply_offer_versions(id) ON DELETE SET NULL;

CREATE TABLE supply_offer_documents (
 id CHAR(36) PRIMARY KEY, supply_offer_version_id CHAR(36) NOT NULL, file_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, file_size INT UNSIGNED NOT NULL, file_data MEDIUMBLOB NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(supply_offer_version_id) REFERENCES supply_offer_versions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE request_awards (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL UNIQUE, supply_offer_id CHAR(36) NOT NULL UNIQUE, supply_offer_version_id CHAR(36) NOT NULL,
 awarded_quantity INT UNSIGNED NOT NULL, transaction_type ENUM('LEASE','PURCHASE') NOT NULL, transaction_id CHAR(36) NULL UNIQUE,
 awarded_by CHAR(36) NOT NULL, awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id), FOREIGN KEY(supply_offer_id) REFERENCES supply_offers(id), FOREIGN KEY(supply_offer_version_id) REFERENCES supply_offer_versions(id), FOREIGN KEY(awarded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE request_asset_reservations (
 id CHAR(36) PRIMARY KEY, award_id CHAR(36) NOT NULL UNIQUE, equipment_id CHAR(36) NOT NULL, quantity INT UNSIGNED NOT NULL,
 start_date DATE NOT NULL, end_date DATE NULL, status ENUM('CONFIRMED','RELEASED','CANCELLED') NOT NULL DEFAULT 'CONFIRMED', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX request_asset_reservation(equipment_id,status,start_date,end_date), FOREIGN KEY(award_id) REFERENCES request_awards(id) ON DELETE CASCADE, FOREIGN KEY(equipment_id) REFERENCES equipment(id)
) ENGINE=InnoDB;

CREATE TABLE request_messages (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, supply_offer_id CHAR(36) NOT NULL, sender_user_id CHAR(36) NOT NULL,
 message_text TEXT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX request_message_thread(supply_offer_id,created_at), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE, FOREIGN KEY(supply_offer_id) REFERENCES supply_offers(id) ON DELETE CASCADE, FOREIGN KEY(sender_user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE request_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_id CHAR(36) NOT NULL, actor_user_id CHAR(36) NULL, event_type VARCHAR(100) NOT NULL,
 event_data JSON NULL, created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), INDEX request_event(request_id,created_at,id),
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE, FOREIGN KEY(actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB;

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
