-- ============================================================
-- MIGRATION: 009_create_notifications.sql
-- Description: Create notification management tables
-- Author: GRC Platform Team
-- Date: 2026-08-03
-- ============================================================

-- ============================================================
-- TABLE: notifications
-- Purpose: System-wide notification management
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    -- Type
    type ENUM('info', 'warning', 'success', 'error', 'compliance', 'risk', 'audit', 'policy', 'sbp', 'system', 'task', 'reminder', 'alert') DEFAULT 'info',
    
    -- Read Status
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    
    -- Action
    action_url VARCHAR(500),
    action_label VARCHAR(100),
    
    -- Priority
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    
    -- Expiry
    expires_at TIMESTAMP NULL,
    
    -- System
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_notification_user (user_id),
    INDEX idx_notification_read (is_read),
    INDEX idx_notification_type (type),
    INDEX idx_notification_priority (priority),
    INDEX idx_notification_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: reminders
-- Purpose: User reminders and schedules
-- ============================================================
CREATE TABLE IF NOT EXISTS reminders (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    remind_at TIMESTAMP NOT NULL,
    frequency ENUM('once', 'daily', 'weekly', 'monthly') DEFAULT 'once',
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    action_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_reminder_user (user_id),
    INDEX idx_reminder_remind_at (remind_at),
    INDEX idx_reminder_completed (is_completed),
    INDEX idx_reminder_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: alerts
-- Purpose: System alerts and warnings
-- ============================================================
CREATE TABLE IF NOT EXISTS alerts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    severity ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    status ENUM('active', 'resolved', 'dismissed') DEFAULT 'active',
    source VARCHAR(100) COMMENT 'Source module or system',
    source_id INT UNSIGNED,
    resolved_at TIMESTAMP NULL,
    resolved_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_alert_status (status),
    INDEX idx_alert_severity (severity),
    INDEX idx_alert_created (created_at),
    INDEX idx_alert_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;