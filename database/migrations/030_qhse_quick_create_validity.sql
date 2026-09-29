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
