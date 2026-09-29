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
