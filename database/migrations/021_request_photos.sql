CREATE TABLE request_photos (
 id CHAR(36) PRIMARY KEY,
 request_id CHAR(36) NOT NULL,
 file_name VARCHAR(255) NOT NULL,
 mime_type VARCHAR(100) NOT NULL,
 file_size INT UNSIGNED NOT NULL,
 image_data MEDIUMBLOB NOT NULL,
 caption VARCHAR(255) NULL,
 is_primary BOOLEAN NOT NULL DEFAULT FALSE,
 sequence INT UNSIGNED NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX request_photo_order(request_id,is_primary,sequence,created_at),
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB;
