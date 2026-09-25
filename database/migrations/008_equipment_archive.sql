ALTER TABLE equipment ADD archived_at DATETIME NULL, ADD archived_by CHAR(36) NULL;
CREATE INDEX equipment_archive_scope ON equipment(owner_id,archived_at,unit_id);
CREATE OR REPLACE VIEW equipment_status_view AS
 SELECT e.*,s.id AS site_id,s.name AS site_name,u.name AS unit_name,op.full_name AS operator_name
 FROM equipment e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id
 JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id AND sp.owner_id=e.owner_id
 LEFT JOIN users op ON op.id=e.operator_id AND op.owner_id=e.owner_id AND op.is_active=1 AND op.role='OPERATOR'
 AND EXISTS(SELECT 1 FROM user_scopes os WHERE os.user_id=op.id AND os.site_id=s.id AND os.unit_id=u.id)
 WHERE e.archived_at IS NULL;
CREATE OR REPLACE VIEW equipment_kpi_view AS SELECT owner_id,COUNT(*) AS total_assets,SUM(status='OPERATIONAL') AS active_assets,SUM(status='MAINTENANCE') AS maintenance_assets,COUNT(DISTINCT operator_id) AS operator_count FROM equipment WHERE archived_at IS NULL GROUP BY owner_id;
