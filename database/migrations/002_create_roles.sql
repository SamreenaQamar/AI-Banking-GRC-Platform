-- ============================================================
-- MIGRATION: 002_create_roles.sql
-- Description: Create roles and role_permissions tables
-- Author: GRC Platform Team
-- Date: 2026-08-03
-- ============================================================

-- ============================================================
-- TABLE: roles
-- Purpose: Define system roles with hierarchical access
-- ============================================================
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Role slug e.g., super_admin',
    display_name VARCHAR(100) NOT NULL COMMENT 'Human readable role name',
    description TEXT COMMENT 'Role description',
    level INT NOT NULL DEFAULT 1 COMMENT 'Hierarchical level (higher = more privileges)',
    is_system BOOLEAN DEFAULT FALSE COMMENT 'System role cannot be deleted',
    permissions JSON COMMENT 'JSON array of permission slugs',
    created_by INT UNSIGNED COMMENT 'User who created this record',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    
    INDEX idx_role_name (name),
    INDEX idx_role_level (level),
    INDEX idx_role_system (is_system),
    INDEX idx_role_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: role_permissions
-- Purpose: Many-to-many relationship between roles and permissions
-- ============================================================
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    
    UNIQUE KEY uk_role_permission (role_id, permission_id),
    INDEX idx_rp_role (role_id),
    INDEX idx_rp_permission (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERT DEFAULT ROLES
-- ============================================================
INSERT INTO roles (name, display_name, description, level, is_system, permissions, created_at) VALUES
('super_admin', 'Super Administrator', 'Full system access with all privileges', 10, TRUE, '["*"]', NOW()),
('admin', 'Administrator', 'Administrative access with user management', 8, TRUE, '["user_*", "role_*", "compliance_*", "risk_*", "audit_*", "policy_*", "settings_*"]', NOW()),
('compliance_officer', 'Compliance Officer', 'Manage compliance tasks and regulations', 6, TRUE, '["compliance_*", "risk_view", "audit_view", "policy_view", "sbp_*", "report_compliance"]', NOW()),
('risk_manager', 'Risk Manager', 'Manage risk assessments and register', 6, TRUE, '["risk_*", "compliance_view", "audit_view", "report_risk"]', NOW()),
('internal_auditor', 'Internal Auditor', 'Conduct internal audits', 5, TRUE, '["audit_*", "compliance_view", "risk_view", "report_audit"]', NOW()),
('department_head', 'Department Head', 'Manage department compliance', 4, TRUE, '["compliance_*", "risk_*", "policy_*", "department_*"]', NOW()),
('branch_manager', 'Branch Manager', 'Manage branch operations', 3, TRUE, '["compliance_view", "risk_view", "policy_view", "branch_*"]', NOW()),
('user', 'Standard User', 'Regular user access', 2, TRUE, '["profile_*", "policy_view", "notification_*", "ai_chat"]', NOW());