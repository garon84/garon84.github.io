CREATE TABLE links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    short_code VARCHAR(20) UNIQUE NOT NULL,
    original_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    clicks INT DEFAULT 0,
    expires_at DATETIME NULL
);

CREATE TABLE rate_limits (
    ip VARCHAR(45) PRIMARY KEY,
    count INT NOT NULL,
    last_reset TIMESTAMP NOT NULL
);
