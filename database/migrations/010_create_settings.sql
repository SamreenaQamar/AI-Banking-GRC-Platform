-- ============================================================
-- MIGRATION: 010_create_settings.sql
-- Description: Create settings and system tables
-- Author: GRC Platform Team
-- Date: 2026-08-03
-- ============================================================

-- ============================================================
-- TABLE: company_settings
-- Purpose: Company-wide settings
-- ============================================================
CREATE TABLE IF NOT EXISTS company_settings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    setting_type ENUM('string', 'integer', 'boolean', 'json', 'text', 'email', 'url') DEFAULT 'string',
    description TEXT,
    is_editable BOOLEAN DEFAULT TRUE,
    is_encrypted BOOLEAN DEFAULT FALSE,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_setting_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: security_settings
-- Purpose: Security configuration
-- ============================================================
CREATE TABLE IF NOT EXISTS security_settings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    category VARCHAR(50) DEFAULT 'general',
    is_encrypted BOOLEAN DEFAULT TRUE,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_security_key (setting_key),
    INDEX idx_security_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: api_settings
-- Purpose: API configuration
-- ============================================================
CREATE TABLE IF NOT EXISTS api_settings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    is_encrypted BOOLEAN DEFAULT TRUE,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_api_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: system_logs
-- Purpose: System activity logs
-- ============================================================
CREATE TABLE IF NOT EXISTS system_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    log_level ENUM('debug', 'info', 'warning', 'error', 'critical') DEFAULT 'info',
    message TEXT NOT NULL,
    context JSON COMMENT 'Additional context data',
    user_id INT UNSIGNED NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_log_level (log_level),
    INDEX idx_log_user (user_id),
    INDEX idx_log_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERT DEFAULT SETTINGS
-- ============================================================
INSERT INTO company_settings (setting_key, setting_value, setting_group, setting_type, description, is_editable) VALUES
('app_name', 'AI Banking GRC Platform', 'general', 'string', 'Application name', TRUE),
('app_version', '1.0.0', 'general', 'string', 'Application version', FALSE),
('app_timezone', 'Asia/Karachi', 'general', 'string', 'Application timezone', TRUE),
('app_locale', 'en', 'general', 'string', 'Default locale', TRUE),
('company_name', 'AI Banking GRC Solutions', 'company', 'string', 'Company name', TRUE),
('company_email', 'info@grc-platform.com', 'company', 'email', 'Company email', TRUE),
('company_phone', '+92-21-1234567', 'company', 'string', 'Company phone', TRUE);

INSERT INTO security_settings (setting_key, setting_value, category, description) VALUES
('session_lifetime', '3600', 'session', 'Session lifetime in seconds'),
('max_login_attempts', '5', 'authentication', 'Maximum login attempts before lockout'),
('lockout_duration', '15', 'authentication', 'Lockout duration in minutes'),
('password_min_length', '12', 'password', 'Minimum password length'),
('require_special_chars', 'true', 'password', 'Require special characters in password'),
('two_factor_enabled', 'false', '2fa', 'Enable two-factor authentication');