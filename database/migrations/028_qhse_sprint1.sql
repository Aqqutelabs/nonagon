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
