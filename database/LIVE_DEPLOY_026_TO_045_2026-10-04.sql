-- NONAGON CONSOLIDATED LIVE DEPLOYMENT
-- Baseline required: schema_migrations contains 001_dashboard.sql through 025_commercial_branding.sql.
-- Target: the currently selected Nonagon database. This file never creates, drops, or selects a database.
-- Generated: 2026-10-04

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(100) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;

-- Compatibility repair for older/imported databases whose canonical ID columns lost their indexes.
-- A unique-index failure here means the affected table contains duplicate IDs and must be repaired before deployment.
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_repair_reference_indexes$$
CREATE PROCEDURE nonagon_repair_reference_indexes()
BEGIN
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='commercial_brands')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='commercial_brands' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `commercial_brands` ADD UNIQUE KEY `deploy_commercial_brands_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='commercial_customers')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='commercial_customers' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `commercial_customers` ADD UNIQUE KEY `deploy_commercial_customers_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='equipment')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='equipment' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `equipment` ADD UNIQUE KEY `deploy_equipment_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='equipment_assemblies')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='equipment_assemblies' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `equipment_assemblies` ADD UNIQUE KEY `deploy_equipment_assemblies_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='equipment_categories')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='equipment_categories' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `equipment_categories` ADD UNIQUE KEY `deploy_equipment_categories_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='equipment_types')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='equipment_types' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `equipment_types` ADD UNIQUE KEY `deploy_equipment_types_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='investment_agreements')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='investment_agreements' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `investment_agreements` ADD UNIQUE KEY `deploy_investment_agreements_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='investment_capital_contributions')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='investment_capital_contributions' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `investment_capital_contributions` ADD UNIQUE KEY `deploy_investment_capital_contributions_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='investment_opportunities')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='investment_opportunities' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `investment_opportunities` ADD UNIQUE KEY `deploy_investment_opportunities_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='investment_promoters')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='investment_promoters' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `investment_promoters` ADD UNIQUE KEY `deploy_investment_promoters_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='owners')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='owners' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `owners` ADD UNIQUE KEY `deploy_owners_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='qhse_certificate_templates')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='qhse_certificate_templates' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `qhse_certificate_templates` ADD UNIQUE KEY `deploy_qhse_certificate_templates_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='qhse_certificates')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='qhse_certificates' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `qhse_certificates` ADD UNIQUE KEY `deploy_qhse_certificates_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='qhse_controls')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='qhse_controls' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `qhse_controls` ADD UNIQUE KEY `deploy_qhse_controls_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='qhse_signature_assets')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='qhse_signature_assets' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `qhse_signature_assets` ADD UNIQUE KEY `deploy_qhse_signature_assets_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='qhse_standards')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='qhse_standards' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `qhse_standards` ADD UNIQUE KEY `deploy_qhse_standards_id_uq` (`id`);
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users')
 AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='id' AND SEQ_IN_INDEX=1) THEN
  ALTER TABLE `users` ADD UNIQUE KEY `deploy_users_id_uq` (`id`);
 END IF;
END$$
CALL nonagon_repair_reference_indexes()$$
DROP PROCEDURE nonagon_repair_reference_indexes$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 026_public_commercial_submissions.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_026$$
CREATE PROCEDURE nonagon_apply_026()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='026_public_commercial_submissions.sql') THEN
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
) ENGINE=InnoDB;
  INSERT INTO schema_migrations(version) VALUES ('026_public_commercial_submissions.sql');
 END IF;
END$$
CALL nonagon_apply_026()$$
DROP PROCEDURE nonagon_apply_026$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 027_commercial_signatories.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_027$$
CREATE PROCEDURE nonagon_apply_027()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='027_commercial_signatories.sql') THEN
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
  INSERT INTO schema_migrations(version) VALUES ('027_commercial_signatories.sql');
 END IF;
END$$
CALL nonagon_apply_027()$$
DROP PROCEDURE nonagon_apply_027$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 028_qhse_sprint1.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_028$$
CREATE PROCEDURE nonagon_apply_028()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='028_qhse_sprint1.sql') THEN
  CREATE TABLE qhse_standards (
 id CHAR(36) PRIMARY KEY, code VARCHAR(30) NOT NULL UNIQUE, title VARCHAR(255) NOT NULL,
 focus_area VARCHAR(80) NOT NULL, description VARCHAR(500) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
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
) ENGINE=InnoDB;
  CREATE TABLE qhse_control_standards (
 control_id CHAR(36) NOT NULL, standard_id CHAR(36) NOT NULL, clause_reference VARCHAR(80) NOT NULL, mapping_note VARCHAR(500) NULL,
 PRIMARY KEY(control_id,standard_id,clause_reference), FOREIGN KEY(control_id) REFERENCES qhse_controls(id) ON DELETE CASCADE,
 FOREIGN KEY(standard_id) REFERENCES qhse_standards(id) ON DELETE CASCADE
) ENGINE=InnoDB;
  CREATE TABLE qhse_evidence_links (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, control_id CHAR(36) NOT NULL,
 source_module VARCHAR(60) NOT NULL, source_type VARCHAR(60) NOT NULL, source_id VARCHAR(100) NOT NULL,
 source_url VARCHAR(500) NULL, evidence_label VARCHAR(255) NOT NULL, linked_by CHAR(36) NOT NULL,
 linked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY qhse_evidence_source(control_id,source_module,source_type,source_id), INDEX qhse_evidence_owner(owner_id,linked_at),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(control_id) REFERENCES qhse_controls(id) ON DELETE CASCADE,
 FOREIGN KEY(linked_by) REFERENCES users(id)
) ENGINE=InnoDB;
  CREATE TABLE qhse_activity_events (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, actor_id CHAR(36) NULL, event_type VARCHAR(60) NOT NULL,
 domain ENUM('QUALITY','HSE','ENVIRONMENT','AUDIT','RISK','APPROVAL','ASSET','GOVERNANCE') NOT NULL,
 title VARCHAR(255) NOT NULL, summary VARCHAR(1000) NULL,
 severity ENUM('INFO','LOW','MEDIUM','HIGH','CRITICAL') NOT NULL DEFAULT 'INFO',
 source_module VARCHAR(60) NOT NULL, source_type VARCHAR(60) NOT NULL, source_id VARCHAR(100) NOT NULL, source_url VARCHAR(500) NULL,
 occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX qhse_activity_feed(owner_id,occurred_at), INDEX qhse_activity_source(owner_id,source_module,source_type,source_id),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
  CREATE TABLE qhse_user_roles (
 user_id CHAR(36) NOT NULL, owner_id CHAR(36) NOT NULL,
 qhse_role ENUM('QHSE_VIEWER','TECHNICIAN','INSPECTOR','QHSE_OFFICER','QUALITY_MANAGER','HSE_OFFICER','ENVIRONMENTAL_OFFICER','AUDITOR','APPROVER','QHSE_ADMINISTRATOR') NOT NULL,
 assigned_by CHAR(36) NOT NULL, assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,qhse_role), INDEX qhse_role_scope(owner_id,qhse_role),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(assigned_by) REFERENCES users(id)
) ENGINE=InnoDB;
  INSERT INTO schema_migrations(version) VALUES ('028_qhse_sprint1.sql');
 END IF;
END$$
CALL nonagon_apply_028()$$
DROP PROCEDURE nonagon_apply_028$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 029_qhse_certificate_generation.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_029$$
CREATE PROCEDURE nonagon_apply_029()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='029_qhse_certificate_generation.sql') THEN
  CREATE TABLE qhse_certificate_templates (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NULL, certificate_type ENUM('HYDROSTATIC_PRESSURE_TEST','PRESSURE_PUMP_TEST','PRESSURE_GAUGE_CALIBRATION','PRESSURE_RELIEF_VALVE_CALIBRATION') NOT NULL,
 name VARCHAR(255) NOT NULL, version INT UNSIGNED NOT NULL DEFAULT 1, field_schema JSON NOT NULL, presentation_schema JSON NOT NULL,
 legal_statement TEXT NULL, is_active BOOLEAN NOT NULL DEFAULT TRUE, created_by CHAR(36) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 scope_key VARCHAR(36) AS (IFNULL(owner_id,'GLOBAL')) STORED, UNIQUE KEY qhse_template_version(scope_key,certificate_type,version),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
  INSERT INTO qhse_certificate_templates(id,certificate_type,name,field_schema,presentation_schema,legal_statement) VALUES
('10000000-0000-4000-8000-000000000001','HYDROSTATIC_PRESSURE_TEST','Hydrostatic Pressure Test','["test_medium","test_pressure","proof_pressure","hold_time","ambient_temperature"]','{"sections":["identity","customer","equipment","test","result","signatures"],"qr":true}','This certificate records the stated inspection and test results at the time of examination.'),
('10000000-0000-4000-8000-000000000002','PRESSURE_PUMP_TEST','Pressure Pump Test','["test_medium","rated_pressure","test_pressure","hold_time","leakage_result"]','{"sections":["identity","customer","equipment","test","result","signatures"],"qr":true}','This certificate records the stated inspection and test results at the time of examination.'),
('10000000-0000-4000-8000-000000000003','PRESSURE_GAUGE_CALIBRATION','Pressure Gauge Calibration','["range","unit","accuracy_class","as_found","as_left","reference_instrument"]','{"sections":["identity","customer","equipment","calibration","result","signatures"],"qr":true}','Calibration results are traceable to the reference equipment identified in this certificate.'),
('10000000-0000-4000-8000-000000000004','PRESSURE_RELIEF_VALVE_CALIBRATION','Pressure Relief Valve Calibration','["set_pressure","unit","test_medium","reseat_pressure","leak_test","reference_instrument"]','{"sections":["identity","customer","equipment","calibration","result","signatures"],"qr":true}','Calibration results are traceable to the reference equipment identified in this certificate.');
  CREATE TABLE qhse_certificate_sequences (
 owner_id CHAR(36) PRIMARY KEY, prefix VARCHAR(20) NOT NULL DEFAULT 'CERT', next_number INT UNSIGNED NOT NULL DEFAULT 1,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE
) ENGINE=InnoDB;
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
) ENGINE=InnoDB;
  CREATE TABLE qhse_certificate_equipment (
 certificate_id CHAR(36) NOT NULL, equipment_id CHAR(36) NOT NULL, customer_equipment_id VARCHAR(100) NULL,
 equipment_snapshot JSON NULL, sort_order INT UNSIGNED NOT NULL DEFAULT 0, PRIMARY KEY(certificate_id,equipment_id),
 FOREIGN KEY(certificate_id) REFERENCES qhse_certificates(id) ON DELETE CASCADE, FOREIGN KEY(equipment_id) REFERENCES equipment(id)
) ENGINE=InnoDB;
  CREATE TABLE qhse_certificate_reference_equipment (
 certificate_id CHAR(36) NOT NULL, equipment_id CHAR(36) NOT NULL, traceability_note VARCHAR(500) NULL,
 equipment_snapshot JSON NULL, PRIMARY KEY(certificate_id,equipment_id),
 FOREIGN KEY(certificate_id) REFERENCES qhse_certificates(id) ON DELETE CASCADE, FOREIGN KEY(equipment_id) REFERENCES equipment(id)
) ENGINE=InnoDB;
  CREATE TABLE qhse_certificate_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, certificate_id CHAR(36) NOT NULL, actor_id CHAR(36) NOT NULL,
 event_type VARCHAR(60) NOT NULL, from_status VARCHAR(30) NULL, to_status VARCHAR(30) NULL, notes VARCHAR(1000) NULL,
 occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX qhse_certificate_timeline(certificate_id,id),
 FOREIGN KEY(certificate_id) REFERENCES qhse_certificates(id) ON DELETE CASCADE, FOREIGN KEY(actor_id) REFERENCES users(id)
) ENGINE=InnoDB;
  INSERT INTO schema_migrations(version) VALUES ('029_qhse_certificate_generation.sql');
 END IF;
END$$
CALL nonagon_apply_029()$$
DROP PROCEDURE nonagon_apply_029$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 030_qhse_quick_create_validity.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_030$$
CREATE PROCEDURE nonagon_apply_030()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='030_qhse_quick_create_validity.sql') THEN
  ALTER TABLE qhse_certificates
 ADD COLUMN validity_duration INT UNSIGNED NULL AFTER valid_from,
 ADD COLUMN validity_unit ENUM('DAY','WEEK','MONTH','YEAR') NULL AFTER validity_duration;
  CREATE TABLE qhse_completion_alerts (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, source_type ENUM('EQUIPMENT','OPERATOR','CUSTOMER','BRAND','CERTIFICATE_TEMPLATE') NOT NULL,
 source_id CHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, missing_fields JSON NOT NULL, status ENUM('OPEN','RESOLVED') NOT NULL DEFAULT 'OPEN',
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, resolved_at DATETIME NULL,
 UNIQUE KEY qhse_completion_source(owner_id,source_type,source_id), INDEX qhse_completion_open(owner_id,status,created_at),
 FOREIGN KEY(owner_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;
  INSERT INTO schema_migrations(version) VALUES ('030_qhse_quick_create_validity.sql');
 END IF;
END$$
CALL nonagon_apply_030()$$
DROP PROCEDURE nonagon_apply_030$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 031_qhse_dynamic_certificates.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_031$$
CREATE PROCEDURE nonagon_apply_031()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='031_qhse_dynamic_certificates.sql') THEN
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
  INSERT INTO schema_migrations(version) VALUES ('031_qhse_dynamic_certificates.sql');
 END IF;
END$$
CALL nonagon_apply_031()$$
DROP PROCEDURE nonagon_apply_031$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 032_qhse_nuprc_number.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_032$$
CREATE PROCEDURE nonagon_apply_032()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='032_qhse_nuprc_number.sql') THEN
  ALTER TABLE qhse_certificates
 ADD COLUMN nuprc_number VARCHAR(150) NULL AFTER customer_reference;
  INSERT INTO schema_migrations(version) VALUES ('032_qhse_nuprc_number.sql');
 END IF;
END$$
CALL nonagon_apply_032()$$
DROP PROCEDURE nonagon_apply_032$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 033_equipment_traceability_fields.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_033$$
CREATE PROCEDURE nonagon_apply_033()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='033_equipment_traceability_fields.sql') THEN
  ALTER TABLE equipment
 ADD COLUMN measurement_lower DECIMAL(18,6) NULL AFTER long_description,
 ADD COLUMN measurement_upper DECIMAL(18,6) NULL AFTER measurement_lower,
 ADD COLUMN measurement_unit VARCHAR(30) NULL AFTER measurement_upper,
 ADD COLUMN measurement_accuracy VARCHAR(100) NULL AFTER measurement_unit,
 ADD COLUMN calibration_date DATE NULL AFTER measurement_accuracy,
 ADD COLUMN calibration_due_date DATE NULL AFTER calibration_date,
 ADD COLUMN calibration_certificate_number VARCHAR(150) NULL AFTER calibration_due_date,
 ADD COLUMN calibration_status VARCHAR(60) NULL AFTER calibration_certificate_number;
  INSERT INTO schema_migrations(version) VALUES ('033_equipment_traceability_fields.sql');
 END IF;
END$$
CALL nonagon_apply_033()$$
DROP PROCEDURE nonagon_apply_033$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 034_equipment_prv_fields.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_034$$
CREATE PROCEDURE nonagon_apply_034()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='034_equipment_prv_fields.sql') THEN
  ALTER TABLE equipment
 ADD COLUMN nominal_size VARCHAR(100) NULL AFTER calibration_status,
 ADD COLUMN connection_specification VARCHAR(150) NULL AFTER nominal_size,
 ADD COLUMN set_pressure DECIMAL(18,6) NULL AFTER connection_specification,
 ADD COLUMN set_pressure_unit VARCHAR(30) NULL AFTER set_pressure,
 ADD COLUMN back_pressure DECIMAL(18,6) NULL AFTER set_pressure_unit,
 ADD COLUMN back_pressure_unit VARCHAR(30) NULL AFTER back_pressure;
  INSERT INTO schema_migrations(version) VALUES ('034_equipment_prv_fields.sql');
 END IF;
END$$
CALL nonagon_apply_034()$$
DROP PROCEDURE nonagon_apply_034$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 035_equipment_size_owner_units.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_035$$
CREATE PROCEDURE nonagon_apply_035()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='035_equipment_size_owner_units.sql') THEN
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
  INSERT INTO schema_migrations(version) VALUES ('035_equipment_size_owner_units.sql');
 END IF;
END$$
CALL nonagon_apply_035()$$
DROP PROCEDURE nonagon_apply_035$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 036_investment_opportunity_sprint1.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_036$$
CREATE PROCEDURE nonagon_apply_036()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='036_investment_opportunity_sprint1.sql') THEN
  CREATE TABLE investment_promoters (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL UNIQUE,
 promoter_type ENUM('INTERNAL','OFISSA','VERIFIED_COMPANY','OPEN_SUBMITTER') NOT NULL,
 status ENUM('PENDING','VERIFIED','SUSPENDED','REJECTED') NOT NULL DEFAULT 'PENDING',
 approved_stage TINYINT UNSIGNED NOT NULL DEFAULT 1, verification_notes TEXT NULL,
 approved_by CHAR(36) NULL, approved_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE,
 FOREIGN KEY(approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_opportunities (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL, promoter_id CHAR(36) NULL, created_by CHAR(36) NOT NULL,
 title VARCHAR(255) NOT NULL, source_type ENUM('INTERNAL','PROMOTER','DEMAND_SIGNAL','REQUEST','TENDER','OTHER') NOT NULL DEFAULT 'PROMOTER',
 status ENUM('DRAFT','SUBMITTED','DUE_DILIGENCE','REVIEW','APPROVED','CONDITIONAL','REJECTED','PUBLISHED','WITHDRAWN') NOT NULL DEFAULT 'DRAFT',
 publication_mode ENUM('PRIVATE','INTEREST_TESTING','INVESTABLE') NOT NULL DEFAULT 'PRIVATE',
 current_version INT UNSIGNED NOT NULL DEFAULT 1,
 equipment_category_id CHAR(36) NULL, equipment_type_id CHAR(36) NULL, industry VARCHAR(150) NULL,
 quantity INT UNSIGNED NOT NULL DEFAULT 1, target_geography VARCHAR(255) NULL,
 opportunity_rationale TEXT NULL, target_funding_amount DECIMAL(18,2) NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN',
 target_term_months INT UNSIGNED NULL, investment_structure VARCHAR(120) NULL,
 ownership_vehicle VARCHAR(255) NULL, finance_security_partner VARCHAR(255) NULL,
 controlled_account_details TEXT NULL, recovery_rights TEXT NULL, distribution_waterfall TEXT NULL,
 primary_risks TEXT NULL, disclosure_statement TEXT NULL,
 submitted_at DATETIME NULL, approved_at DATETIME NULL, published_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX investment_opportunity_owner(organization_id,status,updated_at),
 INDEX investment_opportunity_public(status,publication_mode,published_at),
 FOREIGN KEY(organization_id) REFERENCES owners(id), FOREIGN KEY(promoter_id) REFERENCES investment_promoters(id) ON DELETE SET NULL,
 FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(equipment_category_id) REFERENCES equipment_categories(id) ON DELETE SET NULL,
 FOREIGN KEY(equipment_type_id) REFERENCES equipment_types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_opportunity_versions (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL,
 snapshot JSON NOT NULL, change_reason VARCHAR(500) NULL, created_by CHAR(36) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY investment_opportunity_version(opportunity_id,version_number),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_technical_profiles (
 opportunity_id CHAR(36) PRIMARY KEY, specification TEXT NULL, oem_name VARCHAR(255) NULL, model_name VARCHAR(255) NULL,
 equipment_condition ENUM('NEW','USED','REFURBISHED','TO_BE_DETERMINED') NOT NULL DEFAULT 'TO_BE_DETERMINED',
 subassemblies TEXT NULL, commissioning_requirements TEXT NULL, certification_requirements TEXT NULL,
 serviceability TEXT NULL, parts_support TEXT NULL,
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_demand_evidence (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL,
 evidence_type ENUM('VIEW','LIKE','SAVE','FOLLOW','SHARE','INVESTOR_INTEREST','INDICATIVE_INVESTMENT','CUSTOMER_INTEREST','ENQUIRY','VERIFIED_REQUEST','LOI','TENDER','CONTRACT','COMPARABLE_LEASE','UTILIZATION','SUPPLY_SHORTAGE','OTHER') NOT NULL,
 evidence_strength ENUM('DISCOVERY_SIGNAL','SOFT_INTEREST','VERIFIED_COMMERCIAL') NOT NULL,
 title VARCHAR(255) NOT NULL, counterparty VARCHAR(255) NULL, amount DECIMAL(18,2) NULL, currency CHAR(3) NULL,
 evidence_date DATE NULL, verified_at DATETIME NULL, verified_by CHAR(36) NULL, source_url VARCHAR(500) NULL,
 notes TEXT NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX investment_demand(opportunity_id,evidence_strength,evidence_type),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(verified_by) REFERENCES users(id) ON DELETE SET NULL, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_acquisition_profiles (
 opportunity_id CHAR(36) PRIMARY KEY, supplier_name VARCHAR(255) NULL, supplier_relationship VARCHAR(255) NULL,
 purchase_cost DECIMAL(18,2) NULL, logistics_cost DECIMAL(18,2) NULL, landed_cost DECIMAL(18,2) NULL,
 lead_time_days INT UNSIGNED NULL, warranty_terms TEXT NULL, payment_terms TEXT NULL,
 inspection_requirements TEXT NULL, commissioning_requirements TEXT NULL,
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_financial_scenarios (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL,
 scenario_type ENUM('DOWNSIDE','BASE','UPSIDE') NOT NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN',
 purchase_cost DECIMAL(18,2) NOT NULL DEFAULT 0, landed_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
 insurance_cost DECIMAL(18,2) NOT NULL DEFAULT 0, initial_spares DECIMAL(18,2) NOT NULL DEFAULT 0,
 contingency DECIMAL(18,2) NOT NULL DEFAULT 0, lease_rate DECIMAL(18,2) NOT NULL DEFAULT 0,
 utilization_percent DECIMAL(6,3) NOT NULL DEFAULT 0, downtime_percent DECIMAL(6,3) NOT NULL DEFAULT 0,
 projected_revenue DECIMAL(18,2) NOT NULL DEFAULT 0, operating_expense DECIMAL(18,2) NOT NULL DEFAULT 0,
 maintenance_cost DECIMAL(18,2) NOT NULL DEFAULT 0, management_fee DECIMAL(18,2) NOT NULL DEFAULT 0,
 reserve_amount DECIMAL(18,2) NOT NULL DEFAULT 0, residual_value DECIMAL(18,2) NOT NULL DEFAULT 0,
 projected_return_percent DECIMAL(8,3) NULL, assumptions TEXT NULL,
 UNIQUE KEY investment_scenario(opportunity_id,scenario_type),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_opportunity_documents (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL,
 document_type ENUM('TECHNICAL','QUOTATION','WARRANTY','DEMAND','LOI','TENDER','CONTRACT','FINANCIAL_MODEL','LEGAL','INSURANCE','INVESTMENT_MEMORANDUM','OTHER') NOT NULL,
 title VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL,
 file_size INT UNSIGNED NOT NULL, file_data MEDIUMBLOB NOT NULL,
 visibility ENUM('PRIVATE_REVIEW','INVESTOR') NOT NULL DEFAULT 'PRIVATE_REVIEW',
 uploaded_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX investment_document(opportunity_id,document_type,visibility),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_due_diligence_reviews (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL,
 review_area ENUM('PROMOTER_KYC','TECHNICAL','SUPPLIER_ACQUISITION','DEMAND_DEPLOYMENT','COMMERCIAL_FINANCIAL','LEGAL_FINANCE','RISK') NOT NULL,
 status ENUM('NOT_STARTED','IN_REVIEW','APPROVED','CONDITIONAL','REJECTED') NOT NULL DEFAULT 'NOT_STARTED',
 reviewer_id CHAR(36) NULL, findings TEXT NULL, conditions TEXT NULL, blockers TEXT NULL,
 reviewed_at DATETIME NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY investment_review_area(opportunity_id,review_area),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(reviewer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_opportunity_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, actor_id CHAR(36) NULL,
 event_type VARCHAR(80) NOT NULL, from_status VARCHAR(30) NULL, to_status VARCHAR(30) NULL,
 event_data JSON NULL, occurred_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX investment_event(opportunity_id,occurred_at,id),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_interest_signals (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, actor_user_id CHAR(36) NULL,
 signal_type ENUM('VIEW','LIKE','SAVE','FOLLOW','SHARE','INVESTOR_INTEREST','CUSTOMER_INTEREST') NOT NULL,
 indicative_amount DECIMAL(18,2) NULL, currency CHAR(3) NULL, contact_email VARCHAR(255) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX investment_interest(opportunity_id,signal_type,created_at),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  INSERT INTO schema_migrations(version) VALUES ('036_investment_opportunity_sprint1.sql');
 END IF;
END$$
CALL nonagon_apply_036()$$
DROP PROCEDURE nonagon_apply_036$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 037_investment_participation_sprint2.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_037$$
CREATE PROCEDURE nonagon_apply_037()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='037_investment_participation_sprint2.sql') THEN
  ALTER TABLE investment_opportunities
 ADD COLUMN minimum_participation DECIMAL(18,2) NULL AFTER target_funding_amount,
 ADD COLUMN funding_status ENUM('NOT_OPEN','OPEN','TARGET_REACHED','CLOSED','PROCUREMENT_AUTHORIZED') NOT NULL DEFAULT 'NOT_OPEN' AFTER publication_mode,
 ADD COLUMN funding_opens_at DATETIME NULL AFTER funding_status,
 ADD COLUMN funding_closes_at DATETIME NULL AFTER funding_opens_at,
 ADD COLUMN deployment_state VARCHAR(60) NULL AFTER target_geography,
 ADD INDEX investment_funding_discovery(status,publication_mode,funding_status,funding_closes_at);
  CREATE TABLE investment_investor_profiles (
 user_id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL,
 investor_type ENUM('INDIVIDUAL','ORGANIZATION') NOT NULL DEFAULT 'INDIVIDUAL',
 legal_name VARCHAR(255) NOT NULL, identity_reference VARCHAR(150) NULL, tax_reference VARCHAR(150) NULL,
 address TEXT NULL, country VARCHAR(100) NULL, bank_details JSON NULL,
 kyc_status ENUM('NOT_STARTED','PENDING','VERIFIED','REJECTED') NOT NULL DEFAULT 'NOT_STARTED',
 risk_acknowledged_at DATETIME NULL, title_acknowledged_at DATETIME NULL,
 verified_by CHAR(36) NULL, verified_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX investment_investor_org(organization_id,kyc_status),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE,
 FOREIGN KEY(verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_saved_opportunities (
 user_id CHAR(36) NOT NULL, opportunity_id CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,opportunity_id), INDEX investment_saved_opportunity(opportunity_id,created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_followed_opportunities (
 user_id CHAR(36) NOT NULL, opportunity_id CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,opportunity_id), INDEX investment_followed_opportunity(opportunity_id,created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_funding_instructions (
 opportunity_id CHAR(36) PRIMARY KEY, finance_partner VARCHAR(255) NOT NULL, account_name VARCHAR(255) NOT NULL,
 bank_name VARCHAR(255) NOT NULL, account_reference VARCHAR(255) NOT NULL, instructions TEXT NOT NULL,
 is_active BOOLEAN NOT NULL DEFAULT TRUE, approved_by CHAR(36) NOT NULL, approved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(approved_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_agreements (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, investor_user_id CHAR(36) NOT NULL,
 agreement_version INT UNSIGNED NOT NULL DEFAULT 1, body_snapshot LONGTEXT NOT NULL, terms_snapshot JSON NOT NULL,
 status ENUM('DRAFT','ACCEPTED','SIGNED','VOID') NOT NULL DEFAULT 'DRAFT',
 accepted_at DATETIME NULL, signed_at DATETIME NULL, signature_name VARCHAR(255) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY investment_agreement_party(opportunity_id,investor_user_id),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(investor_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_capital_contributions (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, investor_user_id CHAR(36) NOT NULL,
 agreement_id CHAR(36) NOT NULL, amount DECIMAL(18,2) NOT NULL, currency CHAR(3) NOT NULL,
 status ENUM('INITIATED','PENDING_CONFIRMATION','CONFIRMED','REJECTED','REFUNDED') NOT NULL DEFAULT 'INITIATED',
 investor_reference VARCHAR(255) NULL, provider_reference VARCHAR(255) NULL,
 submitted_at DATETIME NULL, confirmed_at DATETIME NULL, confirmed_by CHAR(36) NULL, rejection_reason VARCHAR(500) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX investment_contribution_opportunity(opportunity_id,status,created_at),
 INDEX investment_contribution_investor(investor_user_id,status,created_at),
 UNIQUE KEY investment_provider_reference(provider_reference),
 CHECK(amount>0),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id),
 FOREIGN KEY(investor_user_id) REFERENCES users(id), FOREIGN KEY(agreement_id) REFERENCES investment_agreements(id),
 FOREIGN KEY(confirmed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_participations (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, investor_user_id CHAR(36) NOT NULL,
 contribution_id CHAR(36) NOT NULL UNIQUE, participation_amount DECIMAL(18,2) NOT NULL, currency CHAR(3) NOT NULL,
 economic_share_percent DECIMAL(9,6) NOT NULL, legal_title_conferred BOOLEAN NOT NULL DEFAULT FALSE,
 status ENUM('ACTIVE','TRANSFERRED','REDEEMED','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX investment_participation_investor(investor_user_id,status,created_at),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id),
 FOREIGN KEY(investor_user_id) REFERENCES users(id), FOREIGN KEY(contribution_id) REFERENCES investment_capital_contributions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE investment_funding_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, actor_user_id CHAR(36) NULL,
 event_type VARCHAR(80) NOT NULL, contribution_id CHAR(36) NULL, event_data JSON NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX investment_funding_timeline(opportunity_id,created_at,id),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(actor_user_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(contribution_id) REFERENCES investment_capital_contributions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  INSERT INTO schema_migrations(version) VALUES ('037_investment_participation_sprint2.sql');
 END IF;
END$$
CALL nonagon_apply_037()$$
DROP PROCEDURE nonagon_apply_037$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 038_nonagon_admin.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_038$$
CREATE PROCEDURE nonagon_apply_038()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='038_nonagon_admin.sql') THEN
  CREATE TABLE nonagon_admin_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, actor_user_id CHAR(36) NOT NULL,
 event_type VARCHAR(80) NOT NULL, entity_type VARCHAR(50) NOT NULL, entity_id CHAR(36) NOT NULL,
 event_data JSON NULL, created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX nonagon_admin_timeline(created_at,id), INDEX nonagon_admin_entity(entity_type,entity_id,created_at),
 FOREIGN KEY(actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  INSERT INTO schema_migrations(version) VALUES ('038_nonagon_admin.sql');
 END IF;
END$$
CALL nonagon_apply_038()$$
DROP PROCEDURE nonagon_apply_038$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 039_investment_term_controls.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_039$$
CREATE PROCEDURE nonagon_apply_039()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='039_investment_term_controls.sql') THEN
  ALTER TABLE investment_opportunities
 ADD COLUMN asset_useful_life_days INT UNSIGNED NULL AFTER target_term_months,
 ADD COLUMN maximum_term_days INT UNSIGNED GENERATED ALWAYS AS (asset_useful_life_days) STORED AFTER asset_useful_life_days,
 ADD COLUMN minimum_term_days INT UNSIGNED NULL AFTER maximum_term_days,
 ADD COLUMN target_term_days INT UNSIGNED NULL AFTER minimum_term_days,
 ADD CONSTRAINT investment_minimum_term_check CHECK(minimum_term_days IS NULL OR minimum_term_days>=90),
 ADD CONSTRAINT investment_target_term_check CHECK(target_term_days IS NULL OR minimum_term_days IS NULL OR asset_useful_life_days IS NULL OR (target_term_days>=minimum_term_days AND target_term_days<=asset_useful_life_days));
  INSERT INTO schema_migrations(version) VALUES ('039_investment_term_controls.sql');
 END IF;
END$$
CALL nonagon_apply_039()$$
DROP PROCEDURE nonagon_apply_039$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 040_pressure_test_certificate_workflow.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_040$$
CREATE PROCEDURE nonagon_apply_040()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='040_pressure_test_certificate_workflow.sql') THEN
  ALTER TABLE qhse_certificates
 MODIFY COLUMN result ENUM('PASS','FAIL','CONDITIONAL') NULL,
 ADD COLUMN due_date DATE NULL AFTER test_date,
 ADD COLUMN certificate_description VARCHAR(500) NULL AFTER inspection_test_type,
 ADD COLUMN duplicated_from_certificate_id CHAR(36) NULL AFTER supersedes_id,
 ADD KEY qhse_certificates_duplicated_from_idx (duplicated_from_certificate_id),
 ADD CONSTRAINT qhse_certificates_duplicated_from_fk FOREIGN KEY (duplicated_from_certificate_id) REFERENCES qhse_certificates(id) ON DELETE SET NULL;
  CREATE TABLE qhse_pressure_test_items (
 id CHAR(36) PRIMARY KEY,
 certificate_id CHAR(36) NOT NULL,
 equipment_id CHAR(36) NULL,
 item_name VARCHAR(255) NOT NULL,
 asset_code VARCHAR(100) NULL,
 serial_number VARCHAR(150) NULL,
 item_description VARCHAR(500) NULL,
 quantity DECIMAL(12,2) NOT NULL DEFAULT 1,
 equipment_snapshot JSON NULL,
 sort_order INT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY qhse_pressure_items_certificate_idx (certificate_id, sort_order),
 CONSTRAINT qhse_pressure_items_certificate_fk FOREIGN KEY (certificate_id) REFERENCES qhse_certificates(id) ON DELETE CASCADE,
 CONSTRAINT qhse_pressure_items_equipment_fk FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  CREATE TABLE qhse_reusable_values (
 id CHAR(36) PRIMARY KEY,
 owner_id CHAR(36) NOT NULL,
 customer_id CHAR(36) NULL,
 value_type ENUM('LOCATION','PROCEDURE','STANDARD','TEST_MEDIUM','TEST_PUMP') NOT NULL,
 value_text VARCHAR(500) NOT NULL,
 created_by CHAR(36) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY qhse_reusable_value_uq (owner_id, customer_id, value_type, value_text(191)),
 KEY qhse_reusable_lookup_idx (owner_id, value_type, value_text(191)),
 CONSTRAINT qhse_reusable_owner_fk FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE CASCADE,
 CONSTRAINT qhse_reusable_customer_fk FOREIGN KEY (customer_id) REFERENCES commercial_customers(id) ON DELETE CASCADE,
 CONSTRAINT qhse_reusable_creator_fk FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  INSERT INTO schema_migrations(version) VALUES ('040_pressure_test_certificate_workflow.sql');
 END IF;
END$$
CALL nonagon_apply_040()$$
DROP PROCEDURE nonagon_apply_040$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 041_qhse_reusable_value_creator_retention.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_041$$
CREATE PROCEDURE nonagon_apply_041()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='041_qhse_reusable_value_creator_retention.sql') THEN
  ALTER TABLE qhse_reusable_values DROP FOREIGN KEY qhse_reusable_creator_fk;
  ALTER TABLE qhse_reusable_values MODIFY COLUMN created_by CHAR(36) NULL;
  ALTER TABLE qhse_reusable_values ADD CONSTRAINT qhse_reusable_creator_retention_fk FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
  INSERT INTO schema_migrations(version) VALUES ('041_qhse_reusable_value_creator_retention.sql');
 END IF;
END$$
CALL nonagon_apply_041()$$
DROP PROCEDURE nonagon_apply_041$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 042_remove_pressure_certificate_due_date.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_042$$
CREATE PROCEDURE nonagon_apply_042()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='042_remove_pressure_certificate_due_date.sql') THEN
  ALTER TABLE qhse_certificates DROP COLUMN due_date;
  INSERT INTO schema_migrations(version) VALUES ('042_remove_pressure_certificate_due_date.sql');
 END IF;
END$$
CALL nonagon_apply_042()$$
DROP PROCEDURE nonagon_apply_042$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 043_pressure_test_item_range.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_043$$
CREATE PROCEDURE nonagon_apply_043()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='043_pressure_test_item_range.sql') THEN
  ALTER TABLE qhse_pressure_test_items
 ADD COLUMN range_text VARCHAR(150) NULL AFTER asset_code;
  INSERT INTO schema_migrations(version) VALUES ('043_pressure_test_item_range.sql');
 END IF;
END$$
CALL nonagon_apply_043()$$
DROP PROCEDURE nonagon_apply_043$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 044_certificate_signature_usage.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_044$$
CREATE PROCEDURE nonagon_apply_044()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='044_certificate_signature_usage.sql') THEN
  ALTER TABLE qhse_certificate_signatories
 ADD COLUMN asset_usage ENUM('SIGNATURE','STAMP','BOTH') NOT NULL DEFAULT 'BOTH' AFTER signature_asset_id;
  ALTER TABLE qhse_signature_assets
 ADD UNIQUE KEY one_signature_asset_per_personnel(owner_id,personnel_user_id);
  INSERT INTO schema_migrations(version) VALUES ('044_certificate_signature_usage.sql');
 END IF;
END$$
CALL nonagon_apply_044()$$
DROP PROCEDURE nonagon_apply_044$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 045_equipment_ownership_capacity_certified_subassemblies.sql
-- -----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS nonagon_apply_045$$
CREATE PROCEDURE nonagon_apply_045()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='045_equipment_ownership_capacity_certified_subassemblies.sql') THEN
  ALTER TABLE equipment_assemblies
 ADD COLUMN serial_number VARCHAR(100) NULL AFTER description,
 ADD COLUMN range_text VARCHAR(150) NULL AFTER serial_number;
  ALTER TABLE qhse_pressure_test_items
 ADD COLUMN assembly_id CHAR(36) NULL AFTER equipment_id,
 ADD COLUMN test_pressure VARCHAR(100) NULL AFTER range_text,
 ADD CONSTRAINT qhse_pressure_item_assembly_fk FOREIGN KEY(assembly_id) REFERENCES equipment_assemblies(id) ON DELETE SET NULL;
  INSERT INTO schema_migrations(version) VALUES ('045_equipment_ownership_capacity_certified_subassemblies.sql');
 END IF;
END$$
CALL nonagon_apply_045()$$
DROP PROCEDURE nonagon_apply_045$$
DELIMITER ;

-- Deployment verification
SELECT version, applied_at FROM schema_migrations WHERE CAST(LEFT(version, 3) AS UNSIGNED) BETWEEN 26 AND 45 ORDER BY version;
