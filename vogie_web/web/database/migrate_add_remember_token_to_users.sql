ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL AFTER is_active;
ALTER TABLE users ADD COLUMN remember_token_expires DATETIME DEFAULT NULL AFTER remember_token;