CREATE TABLE commercial_brands (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL, brand_name VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL,
 creation_mode ENUM('ELEMENTS','ARTWORK') NOT NULL, addresses JSON NULL, phones JSON NULL, emails JSON NULL, website VARCHAR(255) NULL,
 logo_mime VARCHAR(50) NULL, logo_data MEDIUMBLOB NULL, header_mime VARCHAR(50) NULL, header_data MEDIUMBLOB NULL,
 footer_mime VARCHAR(50) NULL, footer_data MEDIUMBLOB NULL, is_default BOOLEAN NOT NULL DEFAULT FALSE,
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX commercial_brand_owner(organization_id,is_default,brand_name), FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE,
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

ALTER TABLE commercial_documents ADD brand_id CHAR(36) NULL AFTER bank_account_id,
 ADD CONSTRAINT commercial_document_brand_fk FOREIGN KEY(brand_id) REFERENCES commercial_brands(id);
