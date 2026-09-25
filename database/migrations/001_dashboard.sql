ALTER TABLE users MODIFY role ENUM('OWNER_ADMIN','OPERATOR','SUPERVISOR','ADMIN','OPS_MANAGER') NOT NULL DEFAULT 'OWNER_ADMIN';
ALTER TABLE invitations MODIFY role ENUM('OPERATOR','SUPERVISOR','ADMIN','OPS_MANAGER') NOT NULL;
CREATE TABLE IF NOT EXISTS equipment (
 id CHAR(36) PRIMARY KEY, owner_id CHAR(36) NOT NULL, unit_id CHAR(36) NOT NULL,
 asset_code VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL,
 status ENUM('OPERATIONAL','MAINTENANCE','DOWN') NOT NULL DEFAULT 'OPERATIONAL',
 operator_id CHAR(36) NULL, utilization_percent DECIMAL(5,2) NULL,
 last_activity_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY equipment_code(owner_id,asset_code), INDEX equipment_scope(owner_id,unit_id,status),
 FOREIGN KEY(owner_id) REFERENCES owners(id), FOREIGN KEY(unit_id) REFERENCES units(id), FOREIGN KEY(operator_id) REFERENCES users(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS maintenance_records (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, title VARCHAR(255) NOT NULL,
 status ENUM('SCHEDULED','IN_PROGRESS','COMPLETED','CANCELLED') NOT NULL DEFAULT 'SCHEDULED',
 due_at DATETIME NOT NULL, completed_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX maintenance_due(equipment_id,status,due_at), FOREIGN KEY(equipment_id) REFERENCES equipment(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS alerts (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, maintenance_id CHAR(36) NULL,
 kind ENUM('BREAKDOWN','OVERDUE','SAFETY','COMPLIANCE') NOT NULL,
 severity ENUM('CRITICAL','HIGH','MEDIUM') NOT NULL, title VARCHAR(255) NOT NULL,
 status ENUM('OPEN','ACKNOWLEDGED','ESCALATED','RESOLVED') NOT NULL DEFAULT 'OPEN',
 assigned_to CHAR(36) NULL, acknowledged_by CHAR(36) NULL, acknowledged_at DATETIME NULL,
 escalation_reason VARCHAR(500) NULL, triggered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY maintenance_alert(maintenance_id), INDEX alert_equipment(equipment_id,status,severity),
 FOREIGN KEY(equipment_id) REFERENCES equipment(id), FOREIGN KEY(maintenance_id) REFERENCES maintenance_records(id),
 FOREIGN KEY(assigned_to) REFERENCES users(id), FOREIGN KEY(acknowledged_by) REFERENCES users(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, owner_id CHAR(36) NOT NULL, actor_id CHAR(36) NOT NULL,
 action VARCHAR(60) NOT NULL, entity_type VARCHAR(30) NOT NULL, entity_id CHAR(36) NOT NULL,
 before_json JSON NULL, after_json JSON NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX audit_entity(owner_id,entity_type,entity_id,id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS dashboard_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, owner_id CHAR(36) NOT NULL, unit_id CHAR(36) NOT NULL,
 entity_type VARCHAR(30) NOT NULL, entity_id CHAR(36) NOT NULL, occurred_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX event_scope(owner_id,unit_id,id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS dashboard_cache (
 scope_key CHAR(64) PRIMARY KEY, revision BIGINT UNSIGNED NOT NULL, payload JSON NOT NULL,
 captured_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS dashboard_history (
 scope_key CHAR(64) NOT NULL, snapshot_date DATE NOT NULL, metrics JSON NOT NULL,
 PRIMARY KEY(scope_key,snapshot_date)
) ENGINE=InnoDB;
CREATE OR REPLACE VIEW equipment_status_view AS
 SELECT e.*, s.id AS site_id, s.name AS site_name, u.name AS unit_name,
 op.full_name AS operator_name
 FROM equipment e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id
 JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id AND sp.owner_id=e.owner_id
 LEFT JOIN users op ON op.id=e.operator_id AND op.owner_id=e.owner_id AND op.is_active=1 AND op.role='OPERATOR'
 AND EXISTS(SELECT 1 FROM user_scopes os WHERE os.user_id=op.id AND os.site_id=s.id AND os.unit_id=u.id);
CREATE OR REPLACE VIEW maintenance_summary_view AS
 SELECT e.owner_id,e.site_id,e.unit_id,COUNT(*) AS total,
 SUM(m.status='SCHEDULED') AS scheduled,SUM(m.status='IN_PROGRESS') AS active,
 SUM(m.status IN ('SCHEDULED','IN_PROGRESS') AND m.due_at<UTC_TIMESTAMP()) AS overdue
 FROM maintenance_records m JOIN equipment_status_view e ON e.id=m.equipment_id
 GROUP BY e.owner_id,e.site_id,e.unit_id;
CREATE OR REPLACE VIEW alert_priority_view AS
 SELECT a.*,e.owner_id,e.unit_id,e.site_id,e.site_name,e.unit_name,e.asset_code,e.name AS equipment_name,
 CASE a.severity WHEN 'CRITICAL' THEN 0 WHEN 'HIGH' THEN 1 ELSE 2 END AS priority
 FROM alerts a JOIN equipment_status_view e ON e.id=a.equipment_id;
