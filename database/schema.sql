DROP DATABASE IF EXISTS habitify;
CREATE DATABASE habitify CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE habitify;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    subscription_type ENUM('free', 'basic', 'premium') DEFAULT 'free',
    is_banned TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_subscription (subscription_type),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE habits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('boolean', 'quantitative') DEFAULT 'boolean',
    unit VARCHAR(50) DEFAULT NULL,
    target_value DECIMAL(10,2) DEFAULT NULL,
    frequency ENUM('daily', 'weekly', 'custom') DEFAULT 'daily',
    days_of_week JSON DEFAULT NULL,
    time_of_day TIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE habit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habit_id INT NOT NULL,
    date DATE NOT NULL,
    value DECIMAL(10,2) DEFAULT NULL,
    quality_rating TINYINT CHECK (quality_rating >= 1 AND quality_rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE,
    UNIQUE KEY unique_habit_date (habit_id, date),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    habit_id INT DEFAULT NULL,
    time TIME NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('free', 'basic', 'premium') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ads_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ad_id VARCHAR(100) NOT NULL,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, clicked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_user_id INT DEFAULT NULL,
    details JSON DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, email, password_hash, role, subscription_type) VALUES
('admin', 'admin@habitify.local', '$2y$10$DVKSP8H2cMOuyDF3OoGPQ.xvEcZ4kS9L9kU7QHRfQzg8tTQfd0cE', 'admin', 'premium');

INSERT INTO users (username, email, password_hash, role, subscription_type) VALUES
('user', 'user@habitify.local', '$2y$10$DVKSP8H2cMOuyDF3OoGPQ.xvEcZ4kS9L9kU7QHRfQzg8tTQfd0cE', 'user', 'free');

INSERT INTO habits (user_id, title, description, type, frequency, target_value, unit, is_active) VALUES
(2, 'Чтение 30 минут', 'Каждый день читать минимум 30 минут', 'quantitative', 'daily', 30, 'мин', 1);

INSERT INTO habits (user_id, title, description, type, frequency, target_value, unit, is_active) VALUES
(2, 'Утренняя зарядка', 'Делать зарядку каждое утро', 'boolean', 'daily', NULL, NULL, 1);

INSERT INTO habit_logs (habit_id, date, value, quality_rating) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 30, 4),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 35, 5),
(1, CURDATE(), 30, NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 1, 3),
(2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1, 4),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 5),
(2, CURDATE(), 1, NULL);

