-- 10. Таблица аудита действий администраторов

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
