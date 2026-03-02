CREATE TABLE IF NOT EXISTS registration_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    link_token VARCHAR(64) NOT NULL UNIQUE,
    qrcode_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id VARCHAR(50) NOT NULL,
    openid VARCHAR(64) NOT NULL,
    name VARCHAR(50) NOT NULL,
    company VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    has_car TINYINT(1) NOT NULL DEFAULT 0,
    plate_number VARCHAR(20) DEFAULT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_activity_openid (activity_id, openid),
    KEY idx_openid (openid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sign_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id VARCHAR(50) NOT NULL,
    sign_count TINYINT NOT NULL,
    start_time_1 DATETIME NOT NULL,
    end_time_1 DATETIME NOT NULL,
    start_time_2 DATETIME DEFAULT NULL,
    end_time_2 DATETIME DEFAULT NULL,
    sign_token VARCHAR(64) NOT NULL UNIQUE,
    qrcode_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_activity_rule (activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sign_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id VARCHAR(50) NOT NULL,
    openid VARCHAR(64) NOT NULL,
    sign_time DATETIME NOT NULL,
    sign_index TINYINT NOT NULL,
    status VARCHAR(20) NOT NULL,
    message VARCHAR(255) DEFAULT NULL,
    KEY idx_activity_openid (activity_id, openid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
