-- Repairs phpMyAdmin dumps that left temporary view stand-ins as BASE TABLES.
-- Run only when information_schema.TABLES reports these *_view objects as BASE TABLE.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP TABLE IF EXISTS marketplace_equipment_view;
DROP TABLE IF EXISTS maintenance_summary_view;
DROP TABLE IF EXISTS equipment_kpi_view;
DROP TABLE IF EXISTS equipment_list_view;
DROP TABLE IF EXISTS equipment_status_view;
DROP VIEW IF EXISTS alert_priority_view;

CREATE OR REPLACE VIEW equipment_status_view AS
 SELECT e.*,s.id AS site_id,s.name AS site_name,u.name AS unit_name,op.full_name AS operator_name
 FROM equipment e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id
 JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id AND sp.owner_id=e.owner_id
 LEFT JOIN users op ON op.id=e.operator_id AND op.owner_id=e.owner_id AND op.is_active=1 AND op.role='OPERATOR'
 AND EXISTS(SELECT 1 FROM user_scopes os WHERE os.user_id=op.id AND os.site_id=s.id AND os.unit_id=u.id)
 WHERE e.archived_at IS NULL;

CREATE OR REPLACE VIEW equipment_list_view AS
 SELECT e.*,p.id AS plant_id,p.name AS plant_name,sp.id AS block_id,sp.name AS block_name,
 c.name AS category_name,sc.name AS subcategory_name,t.name AS type_name,b.name AS brand_name,mo.name AS model_name
 FROM equipment_status_view e JOIN units u ON u.id=e.unit_id JOIN plants p ON p.id=u.plant_id
 JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id
 LEFT JOIN equipment_categories c ON c.id=e.category_id LEFT JOIN equipment_subcategories sc ON sc.id=e.subcategory_id
 LEFT JOIN equipment_types t ON t.id=e.type_id LEFT JOIN equipment_brands b ON b.id=e.brand_id LEFT JOIN equipment_models mo ON mo.id=e.model_id;

CREATE OR REPLACE VIEW equipment_kpi_view AS
 SELECT owner_id,COUNT(*) AS total_assets,SUM(status='OPERATIONAL') AS active_assets,
 SUM(status='MAINTENANCE') AS maintenance_assets,COUNT(DISTINCT operator_id) AS operator_count
 FROM equipment WHERE archived_at IS NULL GROUP BY owner_id;

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

CREATE OR REPLACE VIEW marketplace_equipment_view AS
 SELECT l.id AS listing_id,e.id AS equipment_id,e.owner_id,e.name,e.asset_code,e.category_id,e.subcategory_id,e.type_id,
 e.marketplace_oem_id,e.marketplace_oem_model_id,e.manufacture_year,
 COALESCE(NULLIF(e.long_description,''),e.short_description) AS description,
 e.marketplace_specifications AS specifications,e.status AS equipment_status,e.unit_id,l.purpose,l.listing_status,
 l.marketplace_status,l.visibility,l.available_from,l.price_visibility,l.compliance_status,l.published_at,l.created_at,l.updated_at
 FROM equipment e JOIN marketplace_listings l ON l.asset_id=e.id;
