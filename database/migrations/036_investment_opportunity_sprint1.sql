CREATE TABLE investment_promoters (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL UNIQUE,
 promoter_type ENUM('INTERNAL','OFISSA','VERIFIED_COMPANY','OPEN_SUBMITTER') NOT NULL,
 status ENUM('PENDING','VERIFIED','SUSPENDED','REJECTED') NOT NULL DEFAULT 'PENDING',
 approved_stage TINYINT UNSIGNED NOT NULL DEFAULT 1, verification_notes TEXT NULL,
 approved_by CHAR(36) NULL, approved_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE,
 FOREIGN KEY(approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_opportunities (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL, promoter_id CHAR(36) NULL, created_by CHAR(36) NOT NULL,
 title VARCHAR(255) NOT NULL, source_type ENUM('INTERNAL','PROMOTER','DEMAND_SIGNAL','REQUEST','TENDER','OTHER') NOT NULL DEFAULT 'PROMOTER',
 status ENUM('DRAFT','SUBMITTED','DUE_DILIGENCE','REVIEW','APPROVED','CONDITIONAL','REJECTED','PUBLISHED','WITHDRAWN') NOT NULL DEFAULT 'DRAFT',
 publication_mode ENUM('PRIVATE','INTEREST_TESTING','INVESTABLE') NOT NULL DEFAULT 'PRIVATE',
 current_version INT UNSIGNED NOT NULL DEFAULT 1,
 equipment_category_id CHAR(36) NULL, equipment_type_id CHAR(36) NULL, industry VARCHAR(150) NULL,
 quantity INT UNSIGNED NOT NULL DEFAULT 1, target_geography VARCHAR(255) NULL,
 opportunity_rationale TEXT NULL, target_funding_amount DECIMAL(18,2) NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN',
 target_term_months INT UNSIGNED NULL, investment_structure VARCHAR(120) NULL,
 ownership_vehicle VARCHAR(255) NULL, finance_security_partner VARCHAR(255) NULL,
 controlled_account_details TEXT NULL, recovery_rights TEXT NULL, distribution_waterfall TEXT NULL,
 primary_risks TEXT NULL, disclosure_statement TEXT NULL,
 submitted_at DATETIME NULL, approved_at DATETIME NULL, published_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX investment_opportunity_owner(organization_id,status,updated_at),
 INDEX investment_opportunity_public(status,publication_mode,published_at),
 FOREIGN KEY(organization_id) REFERENCES owners(id), FOREIGN KEY(promoter_id) REFERENCES investment_promoters(id) ON DELETE SET NULL,
 FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(equipment_category_id) REFERENCES equipment_categories(id) ON DELETE SET NULL,
 FOREIGN KEY(equipment_type_id) REFERENCES equipment_types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_opportunity_versions (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, version_number INT UNSIGNED NOT NULL,
 snapshot JSON NOT NULL, change_reason VARCHAR(500) NULL, created_by CHAR(36) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY investment_opportunity_version(opportunity_id,version_number),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_technical_profiles (
 opportunity_id CHAR(36) PRIMARY KEY, specification TEXT NULL, oem_name VARCHAR(255) NULL, model_name VARCHAR(255) NULL,
 equipment_condition ENUM('NEW','USED','REFURBISHED','TO_BE_DETERMINED') NOT NULL DEFAULT 'TO_BE_DETERMINED',
 subassemblies TEXT NULL, commissioning_requirements TEXT NULL, certification_requirements TEXT NULL,
 serviceability TEXT NULL, parts_support TEXT NULL,
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_demand_evidence (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL,
 evidence_type ENUM('VIEW','LIKE','SAVE','FOLLOW','SHARE','INVESTOR_INTEREST','INDICATIVE_INVESTMENT','CUSTOMER_INTEREST','ENQUIRY','VERIFIED_REQUEST','LOI','TENDER','CONTRACT','COMPARABLE_LEASE','UTILIZATION','SUPPLY_SHORTAGE','OTHER') NOT NULL,
 evidence_strength ENUM('DISCOVERY_SIGNAL','SOFT_INTEREST','VERIFIED_COMMERCIAL') NOT NULL,
 title VARCHAR(255) NOT NULL, counterparty VARCHAR(255) NULL, amount DECIMAL(18,2) NULL, currency CHAR(3) NULL,
 evidence_date DATE NULL, verified_at DATETIME NULL, verified_by CHAR(36) NULL, source_url VARCHAR(500) NULL,
 notes TEXT NULL, created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX investment_demand(opportunity_id,evidence_strength,evidence_type),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(verified_by) REFERENCES users(id) ON DELETE SET NULL, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_acquisition_profiles (
 opportunity_id CHAR(36) PRIMARY KEY, supplier_name VARCHAR(255) NULL, supplier_relationship VARCHAR(255) NULL,
 purchase_cost DECIMAL(18,2) NULL, logistics_cost DECIMAL(18,2) NULL, landed_cost DECIMAL(18,2) NULL,
 lead_time_days INT UNSIGNED NULL, warranty_terms TEXT NULL, payment_terms TEXT NULL,
 inspection_requirements TEXT NULL, commissioning_requirements TEXT NULL,
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_financial_scenarios (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL,
 scenario_type ENUM('DOWNSIDE','BASE','UPSIDE') NOT NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN',
 purchase_cost DECIMAL(18,2) NOT NULL DEFAULT 0, landed_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
 insurance_cost DECIMAL(18,2) NOT NULL DEFAULT 0, initial_spares DECIMAL(18,2) NOT NULL DEFAULT 0,
 contingency DECIMAL(18,2) NOT NULL DEFAULT 0, lease_rate DECIMAL(18,2) NOT NULL DEFAULT 0,
 utilization_percent DECIMAL(6,3) NOT NULL DEFAULT 0, downtime_percent DECIMAL(6,3) NOT NULL DEFAULT 0,
 projected_revenue DECIMAL(18,2) NOT NULL DEFAULT 0, operating_expense DECIMAL(18,2) NOT NULL DEFAULT 0,
 maintenance_cost DECIMAL(18,2) NOT NULL DEFAULT 0, management_fee DECIMAL(18,2) NOT NULL DEFAULT 0,
 reserve_amount DECIMAL(18,2) NOT NULL DEFAULT 0, residual_value DECIMAL(18,2) NOT NULL DEFAULT 0,
 projected_return_percent DECIMAL(8,3) NULL, assumptions TEXT NULL,
 UNIQUE KEY investment_scenario(opportunity_id,scenario_type),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_opportunity_documents (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL,
 document_type ENUM('TECHNICAL','QUOTATION','WARRANTY','DEMAND','LOI','TENDER','CONTRACT','FINANCIAL_MODEL','LEGAL','INSURANCE','INVESTMENT_MEMORANDUM','OTHER') NOT NULL,
 title VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL,
 file_size INT UNSIGNED NOT NULL, file_data MEDIUMBLOB NOT NULL,
 visibility ENUM('PRIVATE_REVIEW','INVESTOR') NOT NULL DEFAULT 'PRIVATE_REVIEW',
 uploaded_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX investment_document(opportunity_id,document_type,visibility),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_due_diligence_reviews (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL,
 review_area ENUM('PROMOTER_KYC','TECHNICAL','SUPPLIER_ACQUISITION','DEMAND_DEPLOYMENT','COMMERCIAL_FINANCIAL','LEGAL_FINANCE','RISK') NOT NULL,
 status ENUM('NOT_STARTED','IN_REVIEW','APPROVED','CONDITIONAL','REJECTED') NOT NULL DEFAULT 'NOT_STARTED',
 reviewer_id CHAR(36) NULL, findings TEXT NULL, conditions TEXT NULL, blockers TEXT NULL,
 reviewed_at DATETIME NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY investment_review_area(opportunity_id,review_area),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(reviewer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_opportunity_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, actor_id CHAR(36) NULL,
 event_type VARCHAR(80) NOT NULL, from_status VARCHAR(30) NULL, to_status VARCHAR(30) NULL,
 event_data JSON NULL, occurred_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX investment_event(opportunity_id,occurred_at,id),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investment_interest_signals (
 id CHAR(36) PRIMARY KEY, opportunity_id CHAR(36) NOT NULL, actor_user_id CHAR(36) NULL,
 signal_type ENUM('VIEW','LIKE','SAVE','FOLLOW','SHARE','INVESTOR_INTEREST','CUSTOMER_INTEREST') NOT NULL,
 indicative_amount DECIMAL(18,2) NULL, currency CHAR(3) NULL, contact_email VARCHAR(255) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX investment_interest(opportunity_id,signal_type,created_at),
 FOREIGN KEY(opportunity_id) REFERENCES investment_opportunities(id) ON DELETE CASCADE,
 FOREIGN KEY(actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
