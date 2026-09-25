UPDATE equipment e
JOIN marketplace_listings l ON l.asset_id=e.id
SET e.marketplace_only=0
WHERE e.marketplace_only=1;
