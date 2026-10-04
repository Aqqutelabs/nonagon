ALTER TABLE qhse_reusable_values DROP FOREIGN KEY qhse_reusable_creator_fk;
ALTER TABLE qhse_reusable_values MODIFY COLUMN created_by CHAR(36) NULL;
ALTER TABLE qhse_reusable_values ADD CONSTRAINT qhse_reusable_creator_retention_fk FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
