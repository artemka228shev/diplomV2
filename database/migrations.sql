-- Миграции базы данных Habitify
-- Выполняются последовательно при развёртывании

-- 1. Создание базы данных
CREATE DATABASE IF NOT EXISTS habitify CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE habitify;

-- 2. Таблица пользователей
-- Хранит информацию о пользователях, ролях и подписках
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email для входа',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Хеш пароля bcrypt',
    role ENUM('user', 'admin') DEFAULT 'user' COMMENT 'Роль в системе',
    subscription_type ENUM('free', 'basic', 'premium') DEFAULT 'free' COMMENT 'Тип подписки',
    is_banned TINYINT(1) DEFAULT 0 COMMENT 'Признак бана',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата регистрации',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_subscription (subscription_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Таблица привычек
-- Основные данные о привычках пользователей
CREATE TABLE IF NOT EXISTS habits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'Владелец привычки',
    title VARCHAR(255) NOT NULL COMMENT 'Название',
    description TEXT COMMENT 'Описание',
    type ENUM('boolean', 'quantitative') DEFAULT 'boolean' COMMENT 'Тип: факт или число',
    unit VARCHAR(50) DEFAULT NULL COMMENT 'Единица измерения (мин, шт)',
    target_value DECIMAL(10,2) DEFAULT NULL COMMENT 'Целевое значение',
    frequency ENUM('daily', 'weekly', 'custom') DEFAULT 'daily' COMMENT 'Частота',
    days_of_week JSON DEFAULT NULL COMMENT 'Дни недели для custom',
    time_of_day TIME DEFAULT NULL COMMENT 'Время напоминания',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Активна ли привычка',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Таблица логов выполнения
-- Записи о фактическом выполнении привычек
CREATE TABLE IF NOT EXISTS habit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habit_id INT NOT NULL COMMENT 'Ссылка на привычку',
    date DATE NOT NULL COMMENT 'Дата выполнения',
    value DECIMAL(10,2) DEFAULT NULL COMMENT 'Фактическое значение',
    quality_rating TINYINT CHECK (quality_rating >= 1 AND quality_rating <= 5) COMMENT 'Оценка 1-5',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE,
    UNIQUE KEY unique_habit_date (habit_id, date),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Таблица напоминаний
-- Настройки уведомлений для пользователей
CREATE TABLE IF NOT EXISTS reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    habit_id INT DEFAULT NULL COMMENT 'Если NULL - общее напоминание',
    time TIME NOT NULL COMMENT 'Время уведомления',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Таблица истории подписок
-- Аудит изменений тарифов
CREATE TABLE IF NOT EXISTS subscriptions (
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

-- 7. Таблица кликов по рекламе
-- Аналитика для free-пользователей
CREATE TABLE IF NOT EXISTS ads_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ad_id VARCHAR(100) NOT NULL COMMENT 'Идентификатор баннера',
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, clicked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Тестовые данные
-- Admin: admin123
INSERT INTO users (email, password_hash, role, subscription_type) VALUES
('admin@habitify.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'premium');

-- User: user123
INSERT INTO users (email, password_hash, role, subscription_type) VALUES
('user@habitify.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'free');

-- 9. Пример привычки для тестового пользователя
INSERT INTO habits (user_id, title, type, frequency, target_value, unit, is_active) VALUES
(2, 'Чтение 30 минут', 'quantitative', 'daily', 30, 'мин', 1),
(2, 'Утренняя зарядка', 'boolean', 'daily', NULL, NULL, 1);

-- 10. Пример логов выполнения
INSERT INTO habit_logs (habit_id, date, value, quality_rating) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 30, 4),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 35, 5),
(1, CURDATE(), 30, NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 1, 3),
(2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1, 4),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 5),
(2, CURDATE(), 1, NULL);

-- 11. Таблица аудита действий администраторов
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL COMMENT 'Действие: user_ban, user_unban, user_make_admin, user_remove_admin, subscription_change',
    target_user_id INT DEFAULT NULL COMMENT 'ID пользователя, над которым выполнено действие',
    details JSON DEFAULT NULL COMMENT 'Дополнительные данные (старое/новое значение)',
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Журнал действий администраторов';
