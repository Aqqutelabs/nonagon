ALTER TABLE marketplace_listings
 ADD certification_type VARCHAR(150) NULL,
 ADD certification_valid_until DATE NULL,
 ADD last_inspected_on DATE NULL;
CREATE TABLE marketplace_availability_periods (
 id CHAR(36) PRIMARY KEY, listing_id CHAR(36) NOT NULL,
 start_date DATE NOT NULL, end_date DATE NOT NULL,
 availability ENUM('AVAILABLE','UNAVAILABLE') NOT NULL,
 note VARCHAR(255) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CHECK(end_date>=start_date), INDEX listing_dates(listing_id,start_date,end_date,availability),
 FOREIGN KEY(listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE
) ENGINE=InnoDB;
