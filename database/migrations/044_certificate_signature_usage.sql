SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE qhse_certificate_signatories
 ADD COLUMN asset_usage ENUM('SIGNATURE','STAMP','BOTH') NOT NULL DEFAULT 'BOTH' AFTER signature_asset_id;

ALTER TABLE qhse_signature_assets
 ADD UNIQUE KEY one_signature_asset_per_personnel(owner_id,personnel_user_id);
