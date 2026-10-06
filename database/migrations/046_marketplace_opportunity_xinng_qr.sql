ALTER TABLE marketplace_listings
 ADD COLUMN xinng_short_link_id BIGINT UNSIGNED NULL,
 ADD COLUMN xinng_short_url TEXT NULL,
 ADD COLUMN xinng_destination_url TEXT NULL,
 ADD COLUMN xinng_back_half VARCHAR(64) NULL;
