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
) ENGINE=InnoDB;

CREATE TABLE marketplace_agreement_templates (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NULL, agreement_type ENUM('LEASE','SALE','ADDENDUM') NOT NULL,
 name VARCHAR(255) NOT NULL, version VARCHAR(40) NOT NULL, body_template LONGTEXT NOT NULL, is_active BOOLEAN NOT NULL DEFAULT TRUE,
 created_by CHAR(36) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY agreement_template_version(organization_id,agreement_type,name,version), FOREIGN KEY(organization_id) REFERENCES owners(id), FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_agreements (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, agreement_type ENUM('LEASE','SALE','ADDENDUM') NOT NULL,
 template_id CHAR(36) NULL, template_version VARCHAR(40) NOT NULL, current_version INT UNSIGNED NOT NULL DEFAULT 1,
 status ENUM('DRAFT','REVIEW','SENT','PARTIALLY_SIGNED','SIGNED','VOID') NOT NULL DEFAULT 'DRAFT',
 generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, signed_at DATETIME NULL, created_by CHAR(36) NOT NULL,
 INDEX agreement_transaction(transaction_id,status), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE,
 FOREIGN KEY(template_id) REFERENCES marketplace_agreement_templates(id), FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_agreement_versions (
 id CHAR(36) PRIMARY KEY, agreement_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL,
 body LONGTEXT NOT NULL, terms_snapshot JSON NOT NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY agreement_version(agreement_id,version_number), FOREIGN KEY(agreement_id) REFERENCES marketplace_agreements(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_agreement_signatures (
 id CHAR(36) PRIMARY KEY, agreement_id CHAR(36) NOT NULL, agreement_version_id CHAR(36) NOT NULL,
 organization_id CHAR(36) NOT NULL, signed_by CHAR(36) NOT NULL, signer_name VARCHAR(255) NOT NULL,
 signature_method ENUM('CONFIRMATION','PROVIDER') NOT NULL DEFAULT 'CONFIRMATION', provider_reference VARCHAR(255) NULL,
 signed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY agreement_party_signature(agreement_id,organization_id),
 FOREIGN KEY(agreement_id) REFERENCES marketplace_agreements(id) ON DELETE CASCADE, FOREIGN KEY(agreement_version_id) REFERENCES marketplace_agreement_versions(id),
 FOREIGN KEY(organization_id) REFERENCES owners(id), FOREIGN KEY(signed_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_payments (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, payment_provider VARCHAR(80) NULL,
 provider_reference VARCHAR(255) NULL, idempotency_key VARCHAR(255) NOT NULL, amount DECIMAL(15,2) NOT NULL, currency CHAR(3) NOT NULL,
 payment_type ENUM('DEPOSIT','FULL','MILESTONE','EXTENSION','SETTLEMENT') NOT NULL,
 status ENUM('PENDING','CONFIRMED','FAILED','REFUNDED','PARTIALLY_REFUNDED') NOT NULL DEFAULT 'PENDING',
 due_at DATETIME NULL, confirmed_at DATETIME NULL, provider_payload JSON NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY payment_idempotency(idempotency_key), UNIQUE KEY payment_provider_reference(payment_provider,provider_reference), INDEX payment_transaction(transaction_id,status),
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE marketplace_escrow_records (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, provider VARCHAR(80) NULL, external_reference VARCHAR(255) NULL,
 secured_amount DECIMAL(15,2) NOT NULL, currency CHAR(3) NOT NULL,
 status ENUM('PENDING','FUNDED','PARTIALLY_RELEASED','RELEASED','REFUNDED','DISPUTED') NOT NULL DEFAULT 'PENDING',
 funded_at DATETIME NULL, released_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY escrow_external(provider,external_reference), INDEX escrow_transaction(transaction_id,status), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE marketplace_insurance_records (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, provider VARCHAR(255) NOT NULL, policy_reference VARCHAR(255) NOT NULL,
 coverage_start DATETIME NOT NULL, coverage_end DATETIME NOT NULL,
 status ENUM('QUOTED','SELECTED','ACTIVE','EXPIRED','CANCELLED') NOT NULL DEFAULT 'SELECTED', notes TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY insurance_policy(provider,policy_reference), INDEX insurance_transaction(transaction_id,status),
 FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE marketplace_handover_records (
 id CHAR(36) PRIMARY KEY, lease_id CHAR(36) NOT NULL, event_type ENUM('HANDOVER','RETURN') NOT NULL,
 inspected_by CHAR(36) NOT NULL, location VARCHAR(500) NOT NULL, runtime_reading DECIMAL(15,2) NULL, mileage_reading DECIMAL(15,2) NULL,
 condition_state ENUM('GOOD','ACCEPTABLE','DAMAGED','CRITICAL') NOT NULL, checklist JSON NOT NULL, notes TEXT NULL,
 owner_signature VARCHAR(255) NULL, customer_signature VARCHAR(255) NULL, completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY lease_handover_event(lease_id,event_type), FOREIGN KEY(lease_id) REFERENCES marketplace_leases(id) ON DELETE CASCADE, FOREIGN KEY(inspected_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_handover_media (
 id CHAR(36) PRIMARY KEY, handover_id CHAR(36) NOT NULL, file_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL,
 file_size INT UNSIGNED NOT NULL, file_data MEDIUMBLOB NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(handover_id) REFERENCES marketplace_handover_records(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE marketplace_transaction_exceptions (
 id CHAR(36) PRIMARY KEY, transaction_id CHAR(36) NOT NULL, gate_key VARCHAR(80) NOT NULL, reason TEXT NOT NULL,
 approved_by CHAR(36) NOT NULL, expires_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX transaction_exception(transaction_id,gate_key,expires_at), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE, FOREIGN KEY(approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE marketplace_transaction_activity (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, transaction_id CHAR(36) NOT NULL, actor_user_id CHAR(36) NULL,
 actor_organization_id CHAR(36) NULL, event_type VARCHAR(100) NOT NULL, event_data JSON NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX transaction_activity(transaction_id,created_at,id), FOREIGN KEY(transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE,
 FOREIGN KEY(actor_user_id) REFERENCES users(id), FOREIGN KEY(actor_organization_id) REFERENCES owners(id)
) ENGINE=InnoDB;
