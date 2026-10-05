ALTER TABLE equipment
 ADD COLUMN public_qr_token CHAR(64) NULL,
 ADD COLUMN xinng_short_link_id BIGINT UNSIGNED NULL,
 ADD COLUMN xinng_short_url TEXT NULL,
 ADD COLUMN xinng_destination_url TEXT NULL,
 ADD COLUMN xinng_back_half VARCHAR(64) NULL,
 ADD UNIQUE KEY equipment_public_qr_token(public_qr_token);

ALTER TABLE marketplace_requests
 ADD COLUMN xinng_short_link_id BIGINT UNSIGNED NULL,
 ADD COLUMN xinng_short_url TEXT NULL,
 ADD COLUMN xinng_destination_url TEXT NULL,
 ADD COLUMN xinng_back_half VARCHAR(64) NULL;

ALTER TABLE qhse_certificates
 ADD COLUMN xinng_short_link_id BIGINT UNSIGNED NULL,
 ADD COLUMN xinng_short_url TEXT NULL,
 ADD COLUMN xinng_destination_url TEXT NULL,
 ADD COLUMN xinng_back_half VARCHAR(64) NULL;