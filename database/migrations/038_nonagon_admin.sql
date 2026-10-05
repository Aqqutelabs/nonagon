CREATE TABLE nonagon_admin_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, actor_user_id CHAR(36) NOT NULL,
 event_type VARCHAR(80) NOT NULL, entity_type VARCHAR(50) NOT NULL, entity_id CHAR(36) NOT NULL,
 event_data JSON NULL, created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 INDEX nonagon_admin_timeline(created_at,id), INDEX nonagon_admin_entity(entity_type,entity_id,created_at),
 FOREIGN KEY(actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
