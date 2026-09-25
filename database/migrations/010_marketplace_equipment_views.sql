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
