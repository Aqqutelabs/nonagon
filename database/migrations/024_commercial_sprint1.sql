CREATE TABLE commercial_profiles (
 organization_id CHAR(36) PRIMARY KEY, legal_name VARCHAR(255) NOT NULL, trading_name VARCHAR(255) NULL, registered_address TEXT NULL,
 email VARCHAR(255) NULL, phone VARCHAR(50) NULL, website VARCHAR(255) NULL, tin VARCHAR(100) NULL, rc_number VARCHAR(100) NULL,
 default_currency CHAR(3) NOT NULL DEFAULT 'NGN', default_vat DECIMAL(6,3) NOT NULL DEFAULT 7.500, default_payment_terms VARCHAR(255) NULL,
 quotation_terms TEXT NULL, invoice_terms TEXT NULL, authorized_signatory VARCHAR(255) NULL, quote_prefix VARCHAR(20) NOT NULL DEFAULT 'QT',
 invoice_prefix VARCHAR(20) NOT NULL DEFAULT 'INV', next_quote_number INT UNSIGNED NOT NULL DEFAULT 1, next_invoice_number INT UNSIGNED NOT NULL DEFAULT 1,
 updated_by CHAR(36) NOT NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(updated_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE commercial_bank_accounts (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL, account_name VARCHAR(255) NOT NULL, bank_name VARCHAR(255) NOT NULL,
 account_number VARCHAR(100) NOT NULL, currency CHAR(3) NOT NULL, swift_code VARCHAR(50) NULL, instructions TEXT NULL, is_default BOOLEAN NOT NULL DEFAULT FALSE,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX commercial_bank_owner(organization_id,currency),
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE commercial_customers (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, customer_code VARCHAR(100) NULL, rc_number VARCHAR(100) NULL,
 industry VARCHAR(150) NULL, website VARCHAR(255) NULL, primary_contact VARCHAR(255) NULL, finance_contact VARCHAR(255) NULL,
 procurement_contact VARCHAR(255) NULL, email VARCHAR(255) NULL, phone VARCHAR(50) NULL, billing_address TEXT NULL, service_address TEXT NULL,
 state_region VARCHAR(150) NULL, country VARCHAR(100) NULL, currency CHAR(3) NOT NULL DEFAULT 'NGN', payment_terms VARCHAR(255) NULL,
 credit_period INT UNSIGNED NULL, notes TEXT NULL, tin VARCHAR(100) NULL, tax_registered_name VARCHAR(255) NULL,
 tax_registered_address TEXT NULL, tax_jurisdiction VARCHAR(150) NULL, tax_authority VARCHAR(150) NULL,
 tax_verification_status ENUM('UNVERIFIED','PENDING','VERIFIED','REJECTED') NOT NULL DEFAULT 'UNVERIFIED', tax_last_verified_at DATETIME NULL,
 created_by CHAR(36) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY commercial_customer_code(organization_id,customer_code), INDEX commercial_customer_search(organization_id,name),
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE commercial_documents (
 id CHAR(36) PRIMARY KEY, organization_id CHAR(36) NOT NULL, customer_id CHAR(36) NOT NULL, document_type ENUM('QUOTATION','INVOICE') NOT NULL,
 document_number VARCHAR(80) NOT NULL, status VARCHAR(30) NOT NULL DEFAULT 'DRAFT', issue_date DATE NOT NULL, valid_until DATE NULL, due_date DATE NULL,
 currency CHAR(3) NOT NULL, customer_po VARCHAR(150) NULL, customer_reference VARCHAR(150) NULL, rfq_number VARCHAR(150) NULL,
 prepared_by CHAR(36) NOT NULL, converted_from_id CHAR(36) NULL, bank_account_id CHAR(36) NULL, payment_terms TEXT NULL, delivery_terms TEXT NULL,
 lead_time VARCHAR(255) NULL, notes TEXT NULL, subtotal DECIMAL(18,2) NOT NULL DEFAULT 0, discount_total DECIMAL(18,2) NOT NULL DEFAULT 0,
 tax_total DECIMAL(18,2) NOT NULL DEFAULT 0, adjustment_total DECIMAL(18,2) NOT NULL DEFAULT 0, grand_total DECIMAL(18,2) NOT NULL DEFAULT 0,
 issued_snapshot JSON NULL, issued_at DATETIME NULL, cancelled_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY commercial_document_number(organization_id,document_type,document_number), INDEX commercial_document_register(organization_id,document_type,status,issue_date),
 FOREIGN KEY(organization_id) REFERENCES owners(id) ON DELETE CASCADE, FOREIGN KEY(customer_id) REFERENCES commercial_customers(id),
 FOREIGN KEY(prepared_by) REFERENCES users(id), FOREIGN KEY(converted_from_id) REFERENCES commercial_documents(id), FOREIGN KEY(bank_account_id) REFERENCES commercial_bank_accounts(id)
) ENGINE=InnoDB;

CREATE TABLE commercial_line_items (
 id CHAR(36) PRIMARY KEY, document_id CHAR(36) NOT NULL, description TEXT NOT NULL, quantity DECIMAL(14,3) NOT NULL DEFAULT 1,
 unit VARCHAR(40) NOT NULL DEFAULT 'Each', duration DECIMAL(14,3) NOT NULL DEFAULT 1, rate DECIMAL(18,2) NOT NULL DEFAULT 0,
 discount_rate DECIMAL(6,3) NOT NULL DEFAULT 0, tax_rate DECIMAL(6,3) NOT NULL DEFAULT 0, base_amount DECIMAL(18,2) NOT NULL,
 discount_amount DECIMAL(18,2) NOT NULL, tax_amount DECIMAL(18,2) NOT NULL, total_amount DECIMAL(18,2) NOT NULL,
 source_type VARCHAR(50) NULL, source_id CHAR(36) NULL, source_snapshot JSON NULL, sort_order INT UNSIGNED NOT NULL DEFAULT 0,
 FOREIGN KEY(document_id) REFERENCES commercial_documents(id) ON DELETE CASCADE, INDEX commercial_line_document(document_id,sort_order)
) ENGINE=InnoDB;

CREATE TABLE commercial_tax_records (
 id CHAR(36) PRIMARY KEY, invoice_id CHAR(36) NOT NULL UNIQUE, vat_rate DECIMAL(6,3) NOT NULL DEFAULT 0, vat_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
 wht_applicable BOOLEAN NOT NULL DEFAULT FALSE, wht_rate DECIMAL(6,3) NOT NULL DEFAULT 0, expected_wht DECIMAL(18,2) NOT NULL DEFAULT 0,
 wht_status ENUM('NOT_APPLICABLE','EXPECTED','DEDUCTED','SUBMITTED','CREDIT_CONFIRMED','RECONCILED') NOT NULL DEFAULT 'NOT_APPLICABLE',
 customer_tin VARCHAR(100) NULL, tax_authority VARCHAR(150) NULL, submission_reference VARCHAR(255) NULL, reconciliation_date DATE NULL,
 FOREIGN KEY(invoice_id) REFERENCES commercial_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE commercial_document_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, document_id CHAR(36) NOT NULL, actor_user_id CHAR(36) NULL, event_type VARCHAR(80) NOT NULL,
 previous_value JSON NULL, new_value JSON NULL, created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX commercial_event_document(document_id,created_at,id), FOREIGN KEY(document_id) REFERENCES commercial_documents(id) ON DELETE CASCADE,
 FOREIGN KEY(actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB;
