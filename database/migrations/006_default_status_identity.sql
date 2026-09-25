ALTER TABLE equipment_statuses ADD COLUMN default_state VARCHAR(20) AS (IF(is_default=1,operational_state,NULL)) STORED, ADD UNIQUE KEY default_status(owner_id,default_state);
