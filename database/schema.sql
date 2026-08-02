-- ============================================================
-- AI BANKING GRC PLATFORM - ENTERPRISE DATABASE SCHEMA
-- ============================================================
-- Database: grc_platform
-- Version: 1.0.0
-- Author: GRC Platform Team
-- Date: 2026-08-02
-- Description: Complete normalized schema for banking GRC platform
-- ============================================================

-- Drop existing database if exists
DROP DATABASE IF EXISTS grc_platform;

-- Create database with UTF-8 encoding
CREATE DATABASE grc_platform 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE grc_platform;

-- ============================================================
-- CORE TABLES (Authentication & User Management)
-- ============================================================

-- Table: roles
-- Purpose: Define system roles with hierarchical access
CREATE TABLE roles (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Role name e.g., admin, compliance_officer',
    display_name VARCHAR(100) NOT NULL COMMENT 'Human readable role name',
    description TEXT COMMENT 'Role description',
    level INT NOT NULL DEFAULT 1 COMMENT 'Hierarchical level (higher = more privileges)',
    is_system BOOLEAN DEFAULT FALSE COMMENT 'System role cannot be deleted',
    permissions JSON COMMENT 'JSON array of permission slugs',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role_name (name),
    INDEX idx_role_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: permissions
-- Purpose: Granular permissions for fine-grained access control
CREATE TABLE permissions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Permission slug e.g., user_create',
    display_name VARCHAR(100) NOT NULL COMMENT 'Human readable permission',
    module VARCHAR(50) NOT NULL COMMENT 'Module name e.g., users, compliance',
    description TEXT COMMENT 'Permission description',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_permission_name (name),
    INDEX idx_permission_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
-- Purpose: System users with comprehensive profile
CREATE TABLE users (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'Unique email for login',
    mobile VARCHAR(20) UNIQUE COMMENT 'Mobile number with country code',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed password',
    
    -- Personal Information
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    full_name VARCHAR(200) GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED,
    
    -- Employment Information
    employee_id VARCHAR(50) UNIQUE COMMENT 'Bank employee ID',
    department_id INT UNSIGNED COMMENT 'Foreign key to departments',
    role_id INT UNSIGNED NOT NULL COMMENT 'Foreign key to roles',
    
    -- Contact Information
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Pakistan',
    
    -- Account Status
    status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'pending',
    email_verified BOOLEAN DEFAULT FALSE,
    mobile_verified BOOLEAN DEFAULT FALSE,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255) COMMENT 'TOTP secret',
    
    -- Profile
    profile_image VARCHAR(255) COMMENT 'Profile image path',
    last_login TIMESTAMP NULL,
    last_login_ip VARCHAR(45),
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    
    -- System
    created_by INT UNSIGNED COMMENT 'User who created this record',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete',
    
    -- Audit
    remember_token VARCHAR(100),
    api_token VARCHAR(100) UNIQUE,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user_email (email),
    INDEX idx_user_username (username),
    INDEX idx_user_employee (employee_id),
    INDEX idx_user_role (role_id),
    INDEX idx_user_department (department_id),
    INDEX idx_user_status (status),
    INDEX idx_user_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: user_sessions
-- Purpose: Track active user sessions for security
CREATE TABLE user_sessions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    session_id VARCHAR(100) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_user (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_session_active (is_active),
    INDEX idx_session_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: password_resets
-- Purpose: Manage password reset tokens
CREATE TABLE password_resets (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_reset_email (email),
    INDEX idx_reset_token (token),
    INDEX idx_reset_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ORGANIZATION STRUCTURE TABLES
-- ============================================================

-- Table: banks
-- Purpose: Bank information for multi-bank support
CREATE TABLE banks (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Bank code/SWIFT',
    registration_number VARCHAR(50) UNIQUE COMMENT 'SECP/SBP registration',
    headquarters_address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    website VARCHAR(255),
    logo VARCHAR(255),
    status ENUM('active', 'inactive', 'under_review') DEFAULT 'active',
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_bank_code (code),
    INDEX idx_bank_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: departments
-- Purpose: Bank departments structure
CREATE TABLE departments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    bank_id INT UNSIGNED NOT NULL,
    parent_id INT UNSIGNED NULL COMMENT 'Parent department for hierarchical structure',
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    head_user_id INT UNSIGNED COMMENT 'Department head',
    status ENUM('active', 'inactive') DEFAULT 'active',
    level INT DEFAULT 1 COMMENT 'Hierarchical level',
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (bank_id) REFERENCES banks(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (head_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_department_bank (bank_id),
    INDEX idx_department_parent (parent_id),
    INDEX idx_department_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- COMPLIANCE MODULE TABLES
-- ============================================================

-- Table: compliance_categories
-- Purpose: Categorize compliance requirements
CREATE TABLE compliance_categories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    weight DECIMAL(5,2) DEFAULT 1.00 COMMENT 'Weight for risk calculation',
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: compliance_frameworks
-- Purpose: Compliance frameworks like Basel III, SBP regulations
CREATE TABLE compliance_frameworks (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL COMMENT 'e.g., BASEL3, SBP-GRC',
    version VARCHAR(20) NOT NULL,
    issuing_body VARCHAR(200) NOT NULL,
    description TEXT,
    effective_date DATE,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_framework_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: compliance_tasks
-- Purpose: Track compliance obligations and tasks
CREATE TABLE compliance_tasks (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reference_number VARCHAR(50) UNIQUE COMMENT 'Unique compliance reference',
    category_id INT UNSIGNED NOT NULL,
    framework_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    
    -- Task Details
    priority ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'overdue', 'cancelled') DEFAULT 'pending',
    
    -- Dates
    due_date DATE NOT NULL,
    completed_date DATE NULL,
    reminder_date DATE NULL,
    
    -- Compliance Metrics
    compliance_score DECIMAL(5,2) DEFAULT 0 COMMENT '0-100 percentage',
    evidence_required BOOLEAN DEFAULT TRUE,
    auto_review BOOLEAN DEFAULT FALSE,
    
    -- Assignment
    assigned_to INT UNSIGNED,
    assigned_by INT UNSIGNED,
    reviewed_by INT UNSIGNED,
    review_date DATE NULL,
    
    -- System
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (category_id) REFERENCES compliance_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (framework_id) REFERENCES compliance_frameworks(id) ON DELETE RESTRICT,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_task_reference (reference_number),
    INDEX idx_task_status (status),
    INDEX idx_task_priority (priority),
    INDEX idx_task_due_date (due_date),
    INDEX idx_task_assigned (assigned_to),
    INDEX idx_task_department (department_id),
    INDEX idx_task_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: compliance_status_history
-- Purpose: Track compliance task status changes
CREATE TABLE compliance_status_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    task_id INT UNSIGNED NOT NULL,
    old_status ENUM('pending', 'in_progress', 'completed', 'overdue', 'cancelled'),
    new_status ENUM('pending', 'in_progress', 'completed', 'overdue', 'cancelled') NOT NULL,
    remarks TEXT,
    changed_by INT UNSIGNED NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (task_id) REFERENCES compliance_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_history_task (task_id),
    INDEX idx_history_change (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: compliance_evidence
-- Purpose: Store evidence for compliance tasks
CREATE TABLE compliance_evidence (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    task_id INT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT UNSIGNED,
    description TEXT,
    uploaded_by INT UNSIGNED NOT NULL,
    verified_by INT UNSIGNED NULL,
    verification_date TIMESTAMP NULL,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (task_id) REFERENCES compliance_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_evidence_task (task_id),
    INDEX idx_evidence_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- RISK MANAGEMENT TABLES
-- ============================================================

-- Table: risk_categories
-- Purpose: Classify risks by type
CREATE TABLE risk_categories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL COMMENT 'e.g., CREDIT, MARKET, OPERATIONAL',
    description TEXT,
    color VARCHAR(7) DEFAULT '#2563EB',
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: risk_register
-- Purpose: Central risk repository
CREATE TABLE risk_register (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    risk_code VARCHAR(50) UNIQUE NOT NULL COMMENT 'Unique risk identifier',
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    -- Risk Classification
    category_id INT UNSIGNED NOT NULL,
    sub_category VARCHAR(100),
    
    -- Risk Assessment (Inherent)
    inherent_likelihood TINYINT UNSIGNED CHECK (inherent_likelihood BETWEEN 1 AND 5) COMMENT '1=Very Low, 5=Very High',
    inherent_impact TINYINT UNSIGNED CHECK (inherent_impact BETWEEN 1 AND 5) COMMENT '1=Very Low, 5=Very High',
    inherent_risk_score DECIMAL(5,2) GENERATED ALWAYS AS ((inherent_likelihood * inherent_impact) / 25 * 100) STORED,
    
    -- Risk Assessment (Residual)
    residual_likelihood TINYINT UNSIGNED CHECK (residual_likelihood BETWEEN 1 AND 5),
    residual_impact TINYINT UNSIGNED CHECK (residual_impact BETWEEN 1 AND 5),
    residual_risk_score DECIMAL(5,2) GENERATED ALWAYS AS ((residual_likelihood * residual_impact) / 25 * 100) STORED,
    
    -- Risk Control
    control_description TEXT,
    control_effectiveness ENUM('high', 'medium', 'low', 'none') DEFAULT 'none',
    
    -- Risk Owner
    owner_department_id INT UNSIGNED NOT NULL,
    owner_user_id INT UNSIGNED,
    
    -- Status
    status ENUM('identified', 'assessed', 'mitigated', 'monitored', 'closed') DEFAULT 'identified',
    risk_level ENUM('critical', 'high', 'medium', 'low') GENERATED ALWAYS AS (
        CASE 
            WHEN inherent_risk_score >= 80 THEN 'critical'
            WHEN inherent_risk_score >= 60 THEN 'high'
            WHEN inherent_risk_score >= 40 THEN 'medium'
            ELSE 'low'
        END
    ) STORED,
    
    -- Dates
    identification_date DATE NOT NULL,
    assessment_date DATE,
    review_date DATE,
    closure_date DATE NULL,
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (category_id) REFERENCES risk_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (owner_department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_risk_code (risk_code),
    INDEX idx_risk_status (status),
    INDEX idx_risk_level (risk_level),
    INDEX idx_risk_category (category_id),
    INDEX idx_risk_department (owner_department_id),
    INDEX idx_risk_assessment (assessment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: risk_assessments
-- Purpose: Detailed risk assessments with scoring
CREATE TABLE risk_assessments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    risk_id INT UNSIGNED NOT NULL,
    assessment_date DATE NOT NULL,
    assessor_id INT UNSIGNED NOT NULL,
    
    -- Detailed Assessment
    likelihood_score TINYINT UNSIGNED CHECK (likelihood_score BETWEEN 1 AND 5),
    impact_score TINYINT UNSIGNED CHECK (impact_score BETWEEN 1 AND 5),
    velocity_score TINYINT UNSIGNED CHECK (velocity_score BETWEEN 1 AND 5) COMMENT 'Speed of occurrence',
    persistence_score TINYINT UNSIGNED CHECK (persistence_score BETWEEN 1 AND 5) COMMENT 'Duration of impact',
    
    -- Controls
    control_effectiveness_score TINYINT UNSIGNED CHECK (control_effectiveness_score BETWEEN 1 AND 5),
    mitigation_plans TEXT,
    
    -- Scoring
    inherent_risk_score DECIMAL(5,2) GENERATED ALWAYS AS (
        (likelihood_score * impact_score * velocity_score * persistence_score) / 625 * 100
    ) STORED,
    
    -- Recommendations
    recommendations TEXT,
    action_required BOOLEAN DEFAULT TRUE,
    action_deadline DATE,
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (risk_id) REFERENCES risk_register(id) ON DELETE CASCADE,
    FOREIGN KEY (assessor_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_assessment_risk (risk_id),
    INDEX idx_assessment_date (assessment_date),
    INDEX idx_assessment_score (inherent_risk_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- AUDIT MODULE TABLES
-- ============================================================

-- Table: audit_plans
-- Purpose: Define audit plans and schedules
CREATE TABLE audit_plans (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    reference_number VARCHAR(50) UNIQUE NOT NULL,
    
    -- Audit Type
    audit_type ENUM('internal', 'external', 'regulatory', 'forensic') DEFAULT 'internal',
    audit_frequency ENUM('annual', 'semi_annual', 'quarterly', 'monthly', 'adhoc') DEFAULT 'annual',
    
    -- Scope
    scope_description TEXT NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    
    -- Schedule
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    planned_duration_days INT GENERATED ALWAYS AS (DATEDIFF(end_date, start_date)) STORED,
    
    -- Team
    lead_auditor_id INT UNSIGNED NOT NULL,
    audit_team TEXT COMMENT 'Comma-separated auditor IDs or names',
    
    -- Status
    status ENUM('planned', 'in_progress', 'completed', 'cancelled') DEFAULT 'planned',
    
    -- Budget & Resources
    estimated_budget DECIMAL(15,2),
    actual_cost DECIMAL(15,2),
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    FOREIGN KEY (lead_auditor_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_audit_reference (reference_number),
    INDEX idx_audit_status (status),
    INDEX idx_audit_department (department_id),
    INDEX idx_audit_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: audit_findings
-- Purpose: Record audit findings and observations
CREATE TABLE audit_findings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    audit_plan_id INT UNSIGNED NOT NULL,
    finding_code VARCHAR(50) UNIQUE NOT NULL,
    
    -- Finding Details
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    -- Severity
    severity ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    impact_description TEXT,
    
    -- Root Cause
    root_cause TEXT,
    evidence_path VARCHAR(500) COMMENT 'Link to evidence files',
    
    -- Recommendation
    recommendation TEXT NOT NULL,
    management_response TEXT,
    
    -- Status
    status ENUM('open', 'in_progress', 'resolved', 'closed', 'accepted_risk') DEFAULT 'open',
    
    -- Assignment
    assigned_to INT UNSIGNED,
    assigned_by INT UNSIGNED,
    
    -- Dates
    finding_date DATE NOT NULL,
    resolution_date DATE,
    review_date DATE,
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (audit_plan_id) REFERENCES audit_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_finding_code (finding_code),
    INDEX idx_finding_status (status),
    INDEX idx_finding_severity (severity),
    INDEX idx_finding_audit (audit_plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- POLICIES MANAGEMENT TABLES
-- ============================================================

-- Table: policies
-- Purpose: Manage organizational policies
CREATE TABLE policies (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    policy_number VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    
    -- Policy Details
    version VARCHAR(20) NOT NULL DEFAULT '1.0',
    effective_date DATE NOT NULL,
    review_date DATE,
    expiry_date DATE,
    
    -- Approval
    approved_by INT UNSIGNED,
    approval_date DATE,
    
    -- Status
    status ENUM('draft', 'review', 'approved', 'active', 'archived', 'expired') DEFAULT 'draft',
    
    -- Document
    document_path VARCHAR(500) COMMENT 'Path to policy document',
    document_type VARCHAR(50) COMMENT 'PDF, DOCX, etc.',
    
    -- Compliance
    mandatory BOOLEAN DEFAULT TRUE,
    acknowledges_required BOOLEAN DEFAULT TRUE,
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_policy_number (policy_number),
    INDEX idx_policy_status (status),
    INDEX idx_policy_effective (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: policy_acknowledgements
-- Purpose: Track policy acknowledgements by users
CREATE TABLE policy_acknowledgements (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    policy_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    acknowledged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    FOREIGN KEY (policy_id) REFERENCES policies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY uk_policy_user (policy_id, user_id),
    INDEX idx_ack_policy (policy_id),
    INDEX idx_ack_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SBP CIRCULARS TABLES
-- ============================================================

-- Table: sbp_circulars
-- Purpose: Track State Bank of Pakistan circulars and regulations
CREATE TABLE sbp_circulars (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    circular_number VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    -- Classification
    category ENUM('prudential', 'operational', 'compliance', 'risk', 'governance') DEFAULT 'compliance',
    priority ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    
    -- Dates
    issuance_date DATE NOT NULL,
    effective_date DATE NOT NULL,
    compliance_deadline DATE NOT NULL,
    review_date DATE,
    
    -- Document
    document_path VARCHAR(500),
    document_type VARCHAR(50),
    
    -- Status
    status ENUM('active', 'pending', 'implemented', 'superseded', 'withdrawn') DEFAULT 'pending',
    
    -- Compliance Tracking
    implemented_by INT UNSIGNED,
    implementation_date DATE,
    implementation_notes TEXT,
    
    -- Supersedes
    supersedes_circular_id INT UNSIGNED,
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (implemented_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (supersedes_circular_id) REFERENCES sbp_circulars(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_circular_number (circular_number),
    INDEX idx_circular_status (status),
    INDEX idx_circular_category (category),
    INDEX idx_circular_effective (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATIONS TABLES
-- ============================================================

-- Table: notifications
-- Purpose: System-wide notification management
CREATE TABLE notifications (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    -- Notification Type
    type ENUM('info', 'warning', 'success', 'error', 'compliance', 'risk', 'audit') DEFAULT 'info',
    
    -- Read Status
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    
    -- Action
    action_url VARCHAR(500) COMMENT 'Link to related resource',
    action_label VARCHAR(100),
    
    -- Priority
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    
    -- Expiry
    expires_at TIMESTAMP NULL,
    
    -- System
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_notification_user (user_id),
    INDEX idx_notification_read (is_read),
    INDEX idx_notification_type (type),
    INDEX idx_notification_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- REPORTS & ANALYTICS TABLES
-- ============================================================

-- Table: reports
-- Purpose: Store generated reports
CREATE TABLE reports (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    report_type VARCHAR(50) NOT NULL COMMENT 'compliance, risk, audit, etc.',
    
    -- Report Details
    parameters JSON COMMENT 'Report parameters in JSON',
    file_path VARCHAR(500),
    file_type VARCHAR(20) COMMENT 'PDF, XLSX, CSV',
    
    -- Generation
    generated_by INT UNSIGNED NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Scheduling
    is_scheduled BOOLEAN DEFAULT FALSE,
    schedule_config JSON COMMENT 'Cron schedule configuration',
    next_run TIMESTAMP NULL,
    
    -- Sharing
    is_public BOOLEAN DEFAULT FALSE,
    share_with JSON COMMENT 'Array of user IDs or roles',
    
    -- System
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_report_type (report_type),
    INDEX idx_report_generated (generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ACTIVITY LOGS & AUDIT TRAIL
-- ============================================================

-- Table: activity_logs
-- Purpose: Complete audit trail of all system activities
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NULL COMMENT 'NULL for system actions',
    
    -- Activity Details
    action VARCHAR(100) NOT NULL COMMENT 'e.g., login, create, update, delete',
    module VARCHAR(50) NOT NULL COMMENT 'e.g., users, compliance, risk',
    description TEXT,
    
    -- Target
    target_type VARCHAR(50) COMMENT 'Table or entity name',
    target_id INT UNSIGNED NULL COMMENT 'ID of affected record',
    target_name VARCHAR(255) COMMENT 'Human readable target',
    
    -- Request Details
    ip_address VARCHAR(45),
    user_agent TEXT,
    referer VARCHAR(500),
    
    -- Data
    old_data JSON COMMENT 'Previous state',
    new_data JSON COMMENT 'New state',
    diff_data JSON COMMENT 'Changes made',
    
    -- System
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_log_user (user_id),
    INDEX idx_log_module (module),
    INDEX idx_log_action (action),
    INDEX idx_log_created (created_at),
    INDEX idx_log_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SYSTEM CONFIGURATION
-- ============================================================

-- Table: settings
-- Purpose: System-wide configuration settings
CREATE TABLE settings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    setting_type ENUM('string', 'integer', 'boolean', 'json', 'text') DEFAULT 'string',
    description TEXT,
    is_editable BOOLEAN DEFAULT TRUE,
    is_encrypted BOOLEAN DEFAULT FALSE,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_setting_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERT DEFAULT DATA
-- ============================================================

-- Insert default roles
INSERT INTO roles (name, display_name, description, level, is_system, permissions) VALUES
('super_admin', 'Super Administrator', 'Full system access with all privileges', 10, TRUE, '["*"]'),
('admin', 'Administrator', 'Administrative access with user management', 8, TRUE, '["user_*", "role_*", "compliance_*", "risk_*", "audit_*", "policy_*"]'),
('compliance_officer', 'Compliance Officer', 'Manage compliance tasks and regulations', 6, TRUE, '["compliance_*", "risk_view", "audit_view", "policy_view", "sbp_*"]'),
('risk_manager', 'Risk Manager', 'Manage risk assessments and register', 6, TRUE, '["risk_*", "compliance_view", "audit_view"]'),
('internal_auditor', 'Internal Auditor', 'Conduct internal audits', 5, TRUE, '["audit_*", "compliance_view", "risk_view"]'),
('department_head', 'Department Head', 'Manage department compliance', 4, TRUE, '["compliance_*", "risk_*", "policy_*", "department_*"]'),
('user', 'Standard User', 'Regular user access', 2, TRUE, '["profile_*", "policy_view", "notification_*"]');

-- Insert default permissions
INSERT INTO permissions (name, display_name, module, description) VALUES
-- User Management
('user_create', 'Create Users', 'users', 'Ability to create new users'),
('user_view', 'View Users', 'users', 'Ability to view user profiles'),
('user_update', 'Update Users', 'users', 'Ability to update user information'),
('user_delete', 'Delete Users', 'users', 'Ability to delete users'),
('user_role_assign', 'Assign Roles', 'users', 'Ability to assign roles to users'),

-- Compliance
('compliance_create', 'Create Compliance Tasks', 'compliance', 'Ability to create compliance tasks'),
('compliance_view', 'View Compliance Tasks', 'compliance', 'Ability to view compliance tasks'),
('compliance_update', 'Update Compliance Tasks', 'compliance', 'Ability to update compliance tasks'),
('compliance_delete', 'Delete Compliance Tasks', 'compliance', 'Ability to delete compliance tasks'),
('compliance_approve', 'Approve Compliance', 'compliance', 'Ability to approve compliance tasks'),

-- Risk Management
('risk_create', 'Create Risk Entries', 'risk', 'Ability to create risk entries'),
('risk_view', 'View Risk Entries', 'risk', 'Ability to view risk entries'),
('risk_update', 'Update Risk Entries', 'risk', 'Ability to update risk entries'),
('risk_delete', 'Delete Risk Entries', 'risk', 'Ability to delete risk entries'),
('risk_assess', 'Assess Risks', 'risk', 'Ability to perform risk assessments'),

-- Audit
('audit_create', 'Create Audit Plans', 'audit', 'Ability to create audit plans'),
('audit_view', 'View Audit Plans', 'audit', 'Ability to view audit plans'),
('audit_update', 'Update Audit Plans', 'audit', 'Ability to update audit plans'),
('audit_delete', 'Delete Audit Plans', 'audit', 'Ability to delete audit plans'),
('audit_execute', 'Execute Audits', 'audit', 'Ability to execute audit plans'),

-- Policies
('policy_create', 'Create Policies', 'policies', 'Ability to create policies'),
('policy_view', 'View Policies', 'policies', 'Ability to view policies'),
('policy_update', 'Update Policies', 'policies', 'Ability to update policies'),
('policy_delete', 'Delete Policies', 'policies', 'Ability to delete policies'),
('policy_approve', 'Approve Policies', 'policies', 'Ability to approve policies'),

-- SBP Circulars
('sbp_create', 'Create SBP Circulars', 'sbp', 'Ability to create SBP circulars'),
('sbp_view', 'View SBP Circulars', 'sbp', 'Ability to view SBP circulars'),
('sbp_update', 'Update SBP Circulars', 'sbp', 'Ability to update SBP circulars'),
('sbp_implement', 'Implement SBP Circulars', 'sbp', 'Ability to mark circulars as implemented'),

-- Reports
('report_create', 'Create Reports', 'reports', 'Ability to create reports'),
('report_view', 'View Reports', 'reports', 'Ability to view reports'),
('report_export', 'Export Reports', 'reports', 'Ability to export reports');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_group, setting_type, description, is_editable) VALUES
('app_name', 'AI Banking GRC Platform', 'general', 'string', 'Application name', TRUE),
('app_version', '1.0.0', 'general', 'string', 'Application version', FALSE),
('app_timezone', 'Asia/Karachi', 'general', 'string', 'Application timezone', TRUE),
('session_lifetime', '3600', 'security', 'integer', 'Session lifetime in seconds', TRUE),
('max_login_attempts', '5', 'security', 'integer', 'Maximum login attempts before lockout', TRUE),
('lockout_duration', '15', 'security', 'integer', 'Lockout duration in minutes', TRUE),
('password_min_length', '12', 'security', 'integer', 'Minimum password length', TRUE),
('require_special_chars', 'true', 'security', 'boolean', 'Require special characters in password', TRUE),
('two_factor_enabled', 'false', 'security', 'boolean', 'Enable two-factor authentication', TRUE),
('maintenance_mode', 'false', 'system', 'boolean', 'Enable maintenance mode', TRUE),
('audit_log_retention', '365', 'system', 'integer', 'Days to keep audit logs', TRUE),
('max_file_size', '10485760', 'system', 'integer', 'Maximum file size in bytes (10MB)', TRUE),
('allowed_file_types', '["pdf","docx","xlsx","jpeg","png"]', 'system', 'json', 'Allowed file types for uploads', TRUE);

-- Insert default system user (Super Admin)
-- Password: Secure@123456
INSERT INTO users (
    username, email, first_name, last_name, employee_id, role_id, 
    status, email_verified, password_hash, created_by
) VALUES (
    'admin', 
    'admin@grc-platform.com', 
    'System', 
    'Administrator',
    'SYS-001',
    1, -- Super Admin role
    'active',
    TRUE,
    '$2y$12$8q.E5ZzQrJZ5tqQWKVAqWuxVYH7WuZL9ZMr4WqRrVXy5yVqY5yVq', -- Placeholder hash
    NULL
);

-- ============================================================
-- STORED PROCEDURES AND TRIGGERS
-- ============================================================

-- Trigger: Auto-update compliance task status to overdue
DELIMITER //
CREATE TRIGGER update_compliance_overdue 
BEFORE UPDATE ON compliance_tasks
FOR EACH ROW
BEGIN
    IF NEW.status != 'completed' AND NEW.status != 'cancelled' AND NEW.due_date < CURDATE() THEN
        SET NEW.status = 'overdue';
    END IF;
END//
DELIMITER ;

-- Trigger: Log activity for user actions
DELIMITER //
CREATE TRIGGER log_user_activity 
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.status != OLD.status OR NEW.password_hash != OLD.password_hash THEN
        INSERT INTO activity_logs (user_id, action, module, description, target_type, target_id, old_data, new_data)
        VALUES (
            NEW.id, 
            'update_user', 
            'users', 
            CONCAT('User account updated: ', NEW.username),
            'users',
            NEW.id,
            JSON_OBJECT('status', OLD.status, 'password_changed', OLD.password_hash != NEW.password_hash),
            JSON_OBJECT('status', NEW.status)
        );
    END IF;
END//
DELIMITER ;

-- ============================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- ============================================================

-- Additional composite indexes for better query performance
CREATE INDEX idx_compliance_assigned_status ON compliance_tasks(assigned_to, status);
CREATE INDEX idx_compliance_department_status ON compliance_tasks(department_id, status);
CREATE INDEX idx_risk_status_category ON risk_register(status, category_id);
CREATE INDEX idx_audit_status_department ON audit_plans(status, department_id);
CREATE INDEX idx_finding_severity_status ON audit_findings(severity, status);
CREATE INDEX idx_notification_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_log_user_created ON activity_logs(user_id, created_at);

-- Full-text indexes for searching
CREATE FULLTEXT INDEX ft_compliance_description ON compliance_tasks(title, description);
CREATE FULLTEXT INDEX ft_risk_description ON risk_register(title, description);
CREATE FULLTEXT INDEX ft_audit_findings ON audit_findings(title, description, recommendation);

-- ============================================================
-- VIEWS FOR COMMON QUERIES
-- ============================================================

-- View: Active compliance tasks with assigned users
CREATE VIEW vw_active_compliance AS
SELECT 
    ct.id, ct.title, ct.reference_number, ct.priority, ct.status,
    ct.due_date, ct.compliance_score,
    CONCAT(u.first_name, ' ', u.last_name) AS assigned_to_name,
    d.name AS department_name,
    cc.name AS category_name,
    cf.name AS framework_name,
    CASE 
        WHEN ct.due_date < CURDATE() AND ct.status != 'completed' 
        THEN 'overdue'
        ELSE 'on_track'
    END AS compliance_health
FROM compliance_tasks ct
LEFT JOIN users u ON ct.assigned_to = u.id
LEFT JOIN departments d ON ct.department_id = d.id
LEFT JOIN compliance_categories cc ON ct.category_id = cc.id
LEFT JOIN compliance_frameworks cf ON ct.framework_id = cf.id
WHERE ct.deleted_at IS NULL;

-- View: Risk dashboard summary
CREATE VIEW vw_risk_summary AS
SELECT 
    risk_level,
    COUNT(*) AS risk_count,
    AVG(inherent_risk_score) AS avg_inherent_risk,
    AVG(residual_risk_score) AS avg_residual_risk,
    COUNT(CASE WHEN status = 'identified' THEN 1 END) AS identified,
    COUNT(CASE WHEN status = 'assessed' THEN 1 END) AS assessed,
    COUNT(CASE WHEN status = 'mitigated' THEN 1 END) AS mitigated,
    COUNT(CASE WHEN status = 'monitored' THEN 1 END) AS monitored,
    COUNT(CASE WHEN status = 'closed' THEN 1 END) AS closed
FROM risk_register
WHERE deleted_at IS NULL
GROUP BY risk_level;

-- ============================================================
-- END OF SCHEMA
-- ============================================================