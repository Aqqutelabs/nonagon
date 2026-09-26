CREATE TABLE marketplace_saved_listings (
 user_id CHAR(36) NOT NULL, listing_id CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,listing_id), INDEX saved_listing(listing_id,created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE marketplace_conversations (
 id CHAR(36) PRIMARY KEY, enquiry_id CHAR(36) NOT NULL UNIQUE, listing_id CHAR(36) NOT NULL,
 buyer_organization_id CHAR(36) NOT NULL, owner_organization_id CHAR(36) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(enquiry_id) REFERENCES marketplace_enquiries(id) ON DELETE CASCADE,
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id),
 FOREIGN KEY(buyer_organization_id) REFERENCES owners(id), FOREIGN KEY(owner_organization_id) REFERENCES owners(id)
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

ALTER TABLE marketplace_offers ADD FOREIGN KEY(accepted_version_id) REFERENCES marketplace_offer_versions(id);

CREATE TABLE marketplace_messages (
 id CHAR(36) PRIMARY KEY, conversation_id CHAR(36) NOT NULL, sender_user_id CHAR(36) NOT NULL,
 sender_organization_id CHAR(36) NOT NULL, message_text TEXT NULL, referenced_offer_id CHAR(36) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX conversation_messages(conversation_id,created_at),
 FOREIGN KEY(conversation_id) REFERENCES marketplace_conversations(id) ON DELETE CASCADE,
 FOREIGN KEY(sender_user_id) REFERENCES users(id), FOREIGN KEY(sender_organization_id) REFERENCES owners(id),
 FOREIGN KEY(referenced_offer_id) REFERENCES marketplace_offers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE marketplace_message_attachments (
 id CHAR(36) PRIMARY KEY, message_id CHAR(36) NOT NULL, file_name VARCHAR(255) NOT NULL,
 mime_type VARCHAR(100) NOT NULL, file_size INT UNSIGNED NOT NULL, file_data MEDIUMBLOB NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(message_id) REFERENCES marketplace_messages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE marketplace_reservations (
 id CHAR(36) PRIMARY KEY, asset_id CHAR(36) NOT NULL, offer_id CHAR(36) NOT NULL, offer_version_id CHAR(36) NOT NULL,
 start_date DATE NOT NULL, end_date DATE NOT NULL,
 status ENUM('HOLD','CONFIRMED','RELEASED','EXPIRED') NOT NULL DEFAULT 'CONFIRMED',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX reservation_conflict(asset_id,status,start_date,end_date), UNIQUE KEY reservation_offer(offer_id),
 FOREIGN KEY(asset_id) REFERENCES equipment(id), FOREIGN KEY(offer_id) REFERENCES marketplace_offers(id),
 FOREIGN KEY(offer_version_id) REFERENCES marketplace_offer_versions(id)
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE marketplace_notifications (
 id CHAR(36) PRIMARY KEY, user_id CHAR(36) NOT NULL, event_type VARCHAR(80) NOT NULL,
 entity_type VARCHAR(40) NOT NULL, entity_id CHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, body VARCHAR(500) NULL,
 read_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX notification_inbox(user_id,read_at,created_at), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
