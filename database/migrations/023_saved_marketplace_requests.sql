CREATE TABLE marketplace_saved_requests (
 user_id CHAR(36) NOT NULL,
 request_id CHAR(36) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,request_id),
 INDEX saved_request(request_id,created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(request_id) REFERENCES marketplace_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB;
