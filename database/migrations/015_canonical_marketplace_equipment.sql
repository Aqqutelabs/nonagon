-- Marketplace listings are commercial configuration for canonical equipment.
-- This migration is intentionally additive/backward-compatible.
UPDATE equipment e
JOIN marketplace_listings l ON l.asset_id=e.id
SET e.long_description=l.description
WHERE (e.long_description IS NULL OR e.long_description='')
  AND (e.short_description IS NULL OR e.short_description='')
  AND l.description<>'';

UPDATE equipment e
JOIN marketplace_listings l ON l.asset_id=e.id
SET e.marketplace_specifications=l.public_specifications
WHERE e.marketplace_specifications IS NULL
  AND l.public_specifications IS NOT NULL;

UPDATE equipment e
JOIN marketplace_listings l ON l.asset_id=e.id
SET e.marketplace_only=0
WHERE e.marketplace_only=1;

CREATE OR REPLACE VIEW marketplace_equipment_view AS
SELECT
 l.id AS listing_id,
 e.id AS equipment_id,
 e.owner_id,
 e.name,
 e.asset_code,
 e.category_id,
 e.subcategory_id,
 e.type_id,
 e.marketplace_oem_id,
 e.marketplace_oem_model_id,
 e.manufacture_year,
 COALESCE(NULLIF(e.long_description,''),e.short_description) AS description,
 e.marketplace_specifications AS specifications,
 e.status AS equipment_status,
 e.unit_id,
 l.purpose,
 l.listing_status,
 l.marketplace_status,
 l.visibility,
 l.available_from,
 l.price_visibility,
 l.compliance_status,
 l.published_at,
 l.created_at,
 l.updated_at
FROM equipment e
JOIN marketplace_listings l ON l.asset_id=e.id;
