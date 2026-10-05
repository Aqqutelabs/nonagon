ALTER TABLE investment_opportunities
 ADD COLUMN minimum_participation DECIMAL(18,2) NULL AFTER target_funding_amount,
 ADD COLUMN funding_status ENUM('NOT_OPEN','OPEN','TARGET_REACHED','CLOSED','PROCUREMENT_AUTHORIZED') NOT NULL DEFAULT 'NOT_OPEN' AFTER publication_mode,
 ADD COLUMN funding_opens_at DATETIME NULL AFTER funding_status,
 ADD COLUMN funding_closes_at DATETIME NULL AFTER funding_opens_at,
 ADD COLUMN deployment_state VARCHAR(60) NULL AFTER target_geography,
 ADD INDEX investment_funding_discovery(status,publication_mode,funding_status,funding_closes_at);

CREATE TABLE investment_investor_profiles (
 user_id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL,
 investor_type ENUM('INDIVIDUAL','ORGANIZATION') NOT NULL DEFAULT 'INDIVIDUAL',
 legal_name VARCHAR(255) NOT NULL, identity_reference VARCHAR(150) NULL, tax_reference VARCHAR(150) NULL,
 address TEXT NULL, country VARCHAR(100) NULL, bank_details JSON NULL,
 kyc_status ENUM('NOT_STARTED','PENDING','VERIFIED','REJECTED') NOT NULL DEFAULT 'NOT_STARTED',
 risk_acknowledged_at DATETIME NULL, title_acknowledged_at DATETIME NULL,
 verified_by CHAR(36) NULL, verified_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX investment_investor_org(organization_id,kyc_status),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE,
 FOREIGN KEY(verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_saved_opportunities (
 user_id CHAR(36) NOT NULL, opportunity_id CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,opportunity_id), INDEX investment_saved_opportunity(opportunity_id,created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_followed_opportunities (
 user_id CHAR(36) NOT NULL, opportunity_id CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,opportunity_id), INDEX investment_followed_opportunity(opportunity_id,created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_funding_instructions (
 opportunity_id CHAR(36) PRIMARY KEY, finance_partner VARCHAR(255) NOT NULL, account_name VARCHAR(255) NOT NULL,
 bank_name VARCHAR(255) NOT NULL, account_reference VARCHAR(255) NOT NULL, instructions TEXT NOT NULL,
 is_active BOOLEAN NOT NULL DEFAULT TRUE, approved_by CHAR(36) NOT NULL, approved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(approved_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_agreements (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, investor_user_id CHAR(36) NOT NULL,
 agreement_version INT UNSIGNED NOT NULL DEFAULT 1, body_snapshot LONGTEXT NOT NULL, terms_snapshot JSON NOT NULL,
 status ENUM('DRAFT','ACCEPTED','SIGNED','VOID') NOT NULL DEFAULT 'DRAFT',
 accepted_at DATETIME NULL, signed_at DATETIME NULL, signature_name VARCHAR(255) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY investment_agreement_party(opportunity_id,investor_user_id),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(investor_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_capital_contributions (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, investor_user_id CHAR(36) NOT NULL,
 agreement_id CHAR(36) NOT NULL, amount DECIMAL(18,2) NOT NULL, currency CHAR(3) NOT NULL,
 status ENUM('INITIATED','PENDING_CONFIRMATION','CONFIRMED','REJECTED','REFUNDED') NOT NULL DEFAULT 'INITIATED',
 investor_reference VARCHAR(255) NULL, provider_reference VARCHAR(255) NULL,
 submitted_at DATETIME NULL, confirmed_at DATETIME NULL, confirmed_by CHAR(36) NULL, rejection_reason VARCHAR(500) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX investment_contribution_opportunity(opportunity_id,status,created_at),
 INDEX investment_contribution_investor(investor_user_id,status,created_at),
 UNIQUE KEY investment_provider_reference(provider_reference),
 CHECK(amount>0),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id),
 FOREIGN KEY(investor_user_id) REFERENCES users(id), FOREIGN KEY(agreement_id) REFERENCES investment_agreements(id),
 FOREIGN KEY(confirmed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_participations (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, investor_user_id CHAR(36) NOT NULL,
 contribution_id CHAR(36) NOT NULL UNIQUE, participation_amount DECIMAL(18,2) NOT NULL, currency CHAR(3) NOT NULL,
 economic_share_percent DECIMAL(9,6) NOT NULL, legal_title_conferred BOOLEAN NOT NULL DEFAULT FALSE,
 status ENUM('ACTIVE','TRANSFERRED','REDEEMED','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX investment_participation_investor(investor_user_id,status,created_at),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id),
 FOREIGN KEY(investor_user_id) REFERENCES users(id), FOREIGN KEY(contribution_id) REFERENCES investment_capital_contributions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_funding_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, actor_user_id CHAR(36) NULL,
 event_type VARCHAR(80) NOT NULL, contribution_id CHAR(36) NULL, event_data JSON NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX investment_funding_timeline(opportunity_id,created_at,id),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(actor_user_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(contribution_id) REFERENCES investment_capital_contributions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
