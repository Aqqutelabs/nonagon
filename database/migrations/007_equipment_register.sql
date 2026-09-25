ALTER TABLE equipment_statuses ADD severity INT NOT NULL DEFAULT 0;
UPDATE equipment_statuses SET severity=CASE operational_state WHEN 'DOWN' THEN 2 WHEN 'MAINTENANCE' THEN 1 ELSE 0 END;
CREATE TABLE equipment_operator_assignments (
 id CHAR(36) PRIMARY KEY, equipment_id CHAR(36) NOT NULL, operator_user_id CHAR(36) NOT NULL,
 assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, unassigned_at DATETIME NULL,
 active_equipment CHAR(36) GENERATED ALWAYS AS (IF(unassigned_at IS NULL,equipment_id,NULL)) STORED,
 UNIQUE KEY one_active_assignment(active_equipment), INDEX operator_active(operator_user_id,unassigned_at,equipment_id),
 FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
 FOREIGN KEY(operator_user_id) REFERENCES users(id)
) ENGINE=InnoDB;
INSERT INTO equipment_operator_assignments(id,equipment_id,operator_user_id,assigned_at) SELECT UUID(),id,operator_id,UTC_TIMESTAMP() FROM equipment WHERE operator_id IS NOT NULL;
CREATE TABLE equipment_status_history (
 id BIGINT AUTO_INCREMENT PRIMARY KEY, equipment_id CHAR(36) NOT NULL, status VARCHAR(30) NOT NULL,
 changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX equipment_time(equipment_id,changed_at), FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB;
INSERT INTO equipment_status_history(equipment_id,status) SELECT id,status FROM equipment;
CREATE INDEX equipment_owner_category ON equipment(owner_id,category_id,unit_id);
CREATE OR REPLACE VIEW equipment_list_view AS SELECT e.*,p.id AS plant_id,p.name AS plant_name,sp.id AS block_id,sp.name AS block_name,c.name AS category_name,sc.name AS subcategory_name,t.name AS type_name,b.name AS brand_name,mo.name AS model_name FROM equipment_status_view e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id LEFT JOIN equipment_categories c ON c.id=e.category_id LEFT JOIN equipment_subcategories sc ON sc.id=e.subcategory_id LEFT JOIN equipment_types t ON t.id=e.type_id LEFT JOIN equipment_brands b ON b.id=e.brand_id LEFT JOIN equipment_models mo ON mo.id=e.model_id;
CREATE OR REPLACE VIEW equipment_kpi_view AS SELECT owner_id,COUNT(*) AS total_assets,SUM(status='OPERATIONAL') AS active_assets,SUM(status='MAINTENANCE') AS maintenance_assets,COUNT(DISTINCT operator_id) AS operator_count FROM equipment GROUP BY owner_id;
