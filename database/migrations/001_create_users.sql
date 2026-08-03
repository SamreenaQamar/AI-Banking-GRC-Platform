-- ============================================================
-- MIGRATION: 001_create_users.sql
-- Description: Create users, admins, and user_sessions tables
-- Author: GRC Platform Team
-- Date: 2026-08-03
-- ============================================================

-- ============================================================
-- TABLE: users
-- Purpose: Store all system users with complete profile
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique username for login',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'Unique email address',
    mobile VARCHAR(20) UNIQUE COMMENT 'Mobile number with country code',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed password',
    
    -- Personal Information
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    full_name VARCHAR(200) GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED,
    
    -- Employment Information
    employee_id VARCHAR(50) UNIQUE COMMENT 'Bank employee ID',
    department_id INT UNSIGNED COMMENT 'Reference to departments table',
    role_id INT UNSIGNED NOT NULL COMMENT 'Reference to roles table',
    
    -- Contact Information
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Pakistan',
    
    -- Account Status
    status ENUM('active', 'inactive', 'suspended', 'pending', 'locked') DEFAULT 'pending',
    email_verified BOOLEAN DEFAULT FALSE,
    mobile_verified BOOLEAN DEFAULT FALSE,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255) COMMENT 'TOTP secret key',
    two_factor_recovery_codes JSON COMMENT 'Array of recovery codes',
    
    -- Profile
    profile_image VARCHAR(255) COMMENT 'Path to profile image',
    last_login TIMESTAMP NULL,
    last_login_ip VARCHAR(45),
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    
    -- Preferences
    language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'Asia/Karachi',
    notification_preferences JSON COMMENT 'JSON of notification preferences',
    
    -- System
    created_by INT UNSIGNED COMMENT 'User who created this record',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    
    -- Audit
    remember_token VARCHAR(100),
    api_token VARCHAR(100) UNIQUE,
    
    INDEX idx_user_email (email),
    INDEX idx_user_username (username),
    INDEX idx_user_status (status),
    INDEX idx_user_role (role_id),
    INDEX idx_user_department (department_id),
    INDEX idx_user_deleted (deleted_at),
    INDEX idx_user_login (last_login),
    INDEX idx_user_2fa (two_factor_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: admins
-- Purpose: Extended admin information
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL COMMENT 'Reference to users table',
    admin_level ENUM('super', 'system', 'module') DEFAULT 'module',
    permissions JSON COMMENT 'Custom admin permissions override',
    last_activity TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_admin_user (user_id),
    INDEX idx_admin_level (admin_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: user_sessions
-- Purpose: Track active user sessions for security
-- ============================================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    session_id VARCHAR(100) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type VARCHAR(20) COMMENT 'desktop, mobile, tablet',
    browser VARCHAR(50),
    os VARCHAR(50),
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_session_user (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_session_active (is_active),
    INDEX idx_session_expires (expires_at),
    INDEX idx_session_device (device_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: password_resets
-- Purpose: Manage password reset tokens
-- ============================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_reset_email (email),
    INDEX idx_reset_token (token),
    INDEX idx_reset_expires (expires_at),
    INDEX idx_reset_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;