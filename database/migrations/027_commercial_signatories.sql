ALTER TABLE commercial_documents
 ADD signatory_name VARCHAR(255) NULL AFTER notes,
 ADD signatory_title VARCHAR(255) NULL AFTER signatory_name,
 ADD signature_mime VARCHAR(50) NULL AFTER signatory_title,
 ADD signature_data MEDIUMBLOB NULL AFTER signature_mime;

ALTER TABLE commercial_profiles
 ADD authorized_signatory_title VARCHAR(255) NULL AFTER authorized_signatory,
 ADD signature_mime VARCHAR(50) NULL AFTER authorized_signatory_title,
 ADD signature_data MEDIUMBLOB NULL AFTER signature_mime;

ALTER TABLE public_commercial_submissions
 ADD signature_mime VARCHAR(50) NULL AFTER logo_data,
 ADD signature_data MEDIUMBLOB NULL AFTER signature_mime;
