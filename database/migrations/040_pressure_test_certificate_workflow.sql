SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

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
