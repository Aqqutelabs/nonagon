CREATE TABLE request_share_links (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, token CHAR(64) NOT NULL UNIQUE,
 access_type ENUM('PUBLIC','PRIVATE_LINK','INVITATION') NOT NULL, campaign_id CHAR(36) NULL,
 expires_at DATETIME NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 revoked_at DATETIME NULL, INDEX request_share(request_id,access_type,created_at),
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE distribution_campaigns (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL,
 channel ENUM('SUPPLIER_NETWORK','SELECTED_SUPPLIERS','EMAIL','WHATSAPP','DIRECT_LINK','SOCIAL','PAID') NOT NULL,
 created_by CHAR(36) NOT NULL, audience_definition JSON NULL, approved_content TEXT NULL,
 budget DECIMAL(18,2) NULL, currency CHAR(3) NULL,
 status ENUM('DRAFT','ACTIVE','ENDED','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
 started_at DATETIME NULL, ended_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX request_campaign(request_id,status,created_at), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE,
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

ALTER TABLE request_share_links ADD CONSTRAINT request_share_campaign_fk FOREIGN KEY(campaign_id) REFERENCES distribution_campaigns(id) ON DELETE SET NULL;

CREATE TABLE supplier_invitations (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, share_link_id CHAR(36) NOT NULL,
 organization_id CHAR(36) NULL, contact_name VARCHAR(255) NULL, contact_email VARCHAR(255) NULL, contact_phone VARCHAR(50) NULL,
 channel ENUM('EMAIL','WHATSAPP','DIRECT_LINK','SUPPLIER_NETWORK') NOT NULL,
 status ENUM('SENT','VIEWED','RESPONDED','CANCELLED','EXPIRED') NOT NULL DEFAULT 'SENT',
 invited_by CHAR(36) NOT NULL, sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 viewed_at DATETIME NULL, responded_at DATETIME NULL, offer_id CHAR(36) NULL,
 INDEX request_invitation(request_id,status,sent_at), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE,
 FOREIGN KEY(share_link_id) REFERENCES request_share_links(id) ON DELETE CASCADE, FOREIGN KEY(organization_id) REFERENCES owners(id),
 FOREIGN KEY(invited_by) REFERENCES users(id), FOREIGN KEY(offer_id) REFERENCES supply_offers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE external_supplier_responses (
 id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL, share_link_id CHAR(36) NOT NULL, invitation_id CHAR(36) NULL,
 contact_name VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL, contact_email VARCHAR(255) NOT NULL, contact_phone VARCHAR(50) NULL,
 equipment_description TEXT NOT NULL, oem_name VARCHAR(255) NULL, model_name VARCHAR(255) NULL,
 availability_from DATE NOT NULL, availability_until DATE NULL, quantity_offered INT UNSIGNED NOT NULL DEFAULT 1,
 amount DECIMAL(18,2) NOT NULL, currency CHAR(3) NOT NULL, pricing_basis VARCHAR(20) NOT NULL,
 technical_response TEXT NULL, notes TEXT NULL, document_name VARCHAR(255) NULL, document_mime VARCHAR(100) NULL, document_data MEDIUMBLOB NULL,
 status ENUM('SUBMITTED','CONTACTED','CONVERTED','REJECTED') NOT NULL DEFAULT 'SUBMITTED', submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX external_request(request_id,status,submitted_at), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE,
 FOREIGN KEY(share_link_id) REFERENCES request_share_links(id), FOREIGN KEY(invitation_id) REFERENCES supplier_invitations(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE campaign_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_id CHAR(36) NOT NULL, campaign_id CHAR(36) NULL,
 share_link_id CHAR(36) NULL, invitation_id CHAR(36) NULL, event_type VARCHAR(50) NOT NULL,
 attribution JSON NULL, occurred_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX campaign_timeline(request_id,occurred_at,id), FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE,
 FOREIGN KEY(campaign_id) REFERENCES distribution_campaigns(id) ON DELETE SET NULL,
 FOREIGN KEY(share_link_id) REFERENCES request_share_links(id) ON DELETE SET NULL,
 FOREIGN KEY(invitation_id) REFERENCES supplier_invitations(id) ON DELETE SET NULL
) ENGINE=InnoDB;
