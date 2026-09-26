CREATE TABLE marketplace_transaction_documents (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, document_type ENUM('AGREEMENT','ADDENDUM','HANDOVER','RETURN','INVOICE','RECEIPT','INSURANCE','OTHER') NOT NULL,
 title VARCHAR(255) NOT NULL, version INT UNSIGNED NOT NULL DEFAULT 1, mime_type VARCHAR(100) NOT NULL DEFAULT 'text/plain', content LONGTEXT NULL,
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY transaction_document_version(transaction_id,document_type,version), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_lease_incidents (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, lease_id CHAR(36) NOT NULL, reported_by CHAR(36) NOT NULL,
 category ENUM('BREAKDOWN','DAMAGE','SAFETY','MISSING_COMPONENT','OTHER') NOT NULL, severity ENUM('LOW','MEDIUM','HIGH','CRITICAL') NOT NULL,
 title VARCHAR(255) NOT NULL, description TEXT NOT NULL, status ENUM('OPEN','ACKNOWLEDGED','RESOLVED','CLOSED') NOT NULL DEFAULT 'OPEN',
 erp_alert_id CHAR(36) NULL, maintenance_id CHAR(36) NULL, insurance_claim_reference VARCHAR(255) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, resolved_at DATETIME NULL,
 INDEX incident_lease(lease_id,status,created_at), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE,
 FOREIGN KEY(lease_id) REFERENCES marketplace_leases(id) ON DELETE CASCADE, FOREIGN KEY(reported_by) REFERENCES users(id), FOREIGN KEY(erp_alert_id) REFERENCES alerts(id), FOREIGN KEY(maintenance_id) REFERENCES maintenance_records(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_lease_extensions (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, lease_id CHAR(36) NOT NULL, requested_by_organization_id CHAR(36) NOT NULL,
 current_version INT UNSIGNED NOT NULL DEFAULT 1, status ENUM('REQUESTED','COUNTERED','ACCEPTED','REJECTED','CANCELLED') NOT NULL DEFAULT 'REQUESTED',
 accepted_version_id CHAR(36) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX extension_transaction(transaction_id,status), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(lease_id) REFERENCES marketplace_leases(id) ON DELETE CASCADE, FOREIGN KEY(requested_by_organization_id) REFERENCES owners(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_lease_extension_versions (
 id CHAR(36) PRIMARY KEY, extension_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL, proposed_by_organization_id CHAR(36) NOT NULL,
 proposed_end DATE NOT NULL, amount DECIMAL(15,2) NOT NULL, currency CHAR(3) NOT NULL, notes TEXT NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY extension_version(extension_id,version_number), FOREIGN KEY(extension_id) REFERENCES marketplace_lease_extensions(id) ON DELETE CASCADE,
 FOREIGN KEY(proposed_by_organization_id) REFERENCES owners(id), FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;
ALTER TABLE marketplace_lease_extensions ADD FOREIGN KEY(accepted_version_id) REFERENCES marketplace_lease_extension_versions(id);

CREATE TABLE marketplace_return_exceptions (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, handover_id CHAR(36) NOT NULL,
 exception_type ENUM('DAMAGE','MISSING_COMPONENT','EXCESS_RUNTIME','EXCESS_MILEAGE','CLEANING','LATE_RETURN','OTHER') NOT NULL,
 description TEXT NOT NULL, amount DECIMAL(15,2) NOT NULL DEFAULT 0, currency CHAR(3) NOT NULL, status ENUM('OPEN','ACCEPTED','DISPUTED','WAIVED','SETTLED') NOT NULL DEFAULT 'OPEN',
 insurance_claim_reference VARCHAR(255) NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX return_exception(transaction_id,status), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(handover_id) REFERENCES marketplace_handover_records(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_settlements (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL UNIQUE, currency CHAR(3) NOT NULL,
 gross_amount DECIMAL(15,2) NOT NULL, additional_charges DECIMAL(15,2) NOT NULL DEFAULT 0, deposit_applied DECIMAL(15,2) NOT NULL DEFAULT 0,
 refund_amount DECIMAL(15,2) NOT NULL DEFAULT 0, platform_fee DECIMAL(15,2) NOT NULL DEFAULT 0, supplier_payout DECIMAL(15,2) NOT NULL DEFAULT 0,
 status ENUM('DRAFT','DISPUTED','APPROVED','SETTLED') NOT NULL DEFAULT 'DRAFT', approved_by CHAR(36) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, settled_at DATETIME NULL,
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE equipment_ownership_history (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, previous_owner_id CHAR(36) NOT NULL, new_owner_id CHAR(36) NOT NULL,
 transaction_id CHAR(36) NOT NULL UNIQUE, transferred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, transferred_by CHAR(36) NOT NULL,
 FOREIGN KEY(equipment_id) REFERENCES equipment(id), FOREIGN KEY(previous_owner_id) REFERENCES owners(id), FOREIGN KEY(new_owner_id) REFERENCES owners(id),
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id), FOREIGN KEY(transferred_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_reviews (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, reviewer_organization_id CHAR(36) NOT NULL, reviewed_organization_id CHAR(36) NOT NULL,
 rating TINYINT UNSIGNED NOT NULL, review_text TEXT NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY transaction_reviewer(transaction_id,reviewer_organization_id), INDEX reviewed_rating(reviewed_organization_id,rating),
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(reviewer_organization_id) REFERENCES owners(id), FOREIGN KEY(reviewed_organization_id) REFERENCES owners(id), FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_commercial_audit (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, transaction_id CHAR(36) NOT NULL, sequence_number INT UNSIGNED NOT NULL,
 actor_user_id CHAR(36) NULL, event_type VARCHAR(100) NOT NULL, event_data JSON NULL, previous_hash CHAR(64) NULL, event_hash CHAR(64) NOT NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), UNIQUE KEY transaction_audit_sequence(transaction_id,sequence_number), UNIQUE KEY commercial_event_hash(event_hash),
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB;
