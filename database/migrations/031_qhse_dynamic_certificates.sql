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
) ENGINE=InnoDB;
CREATE TABLE qhse_certificate_signatories (
 certificate_id CHAR(36) NOT NULL, signatory_role ENUM('TECHNICIAN','QA_REVIEWER','APPROVER','CLIENT_REPRESENTATIVE') NOT NULL,
 personnel_user_id CHAR(36) NULL, signature_asset_id CHAR(36) NULL, signatory_name VARCHAR(255) NULL, role_title VARCHAR(255) NULL,
 asset_snapshot JSON NULL, signed_at DATE NULL, PRIMARY KEY(certificate_id,signatory_role),
 FOREIGN KEY(certificate_id) REFERENCES qhse_certificates(id) ON DELETE CASCADE,
 FOREIGN KEY(personnel_user_id) REFERENCES users(id) ON DELETE SET NULL, FOREIGN KEY(signature_asset_id) REFERENCES qhse_signature_assets(id) ON DELETE SET NULL
) ENGINE=InnoDB;
