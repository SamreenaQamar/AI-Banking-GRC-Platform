-- ============================================================
-- MIGRATION: 005_create_risks.sql
-- Description: Create risk management tables
-- Author: GRC Platform Team
-- Date: 2026-08-03
-- ============================================================

-- ============================================================
-- TABLE: risk_categories
-- Purpose: Classify risks by type
-- ============================================================
CREATE TABLE IF NOT EXISTS risk_categories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#2563EB',
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_category_code (code),
    INDEX idx_category_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: risk_register
-- Purpose: Central risk repository
-- ============================================================
CREATE TABLE IF NOT EXISTS risk_register (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    risk_code VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    -- Classification
    category_id INT UNSIGNED NOT NULL,
    sub_category VARCHAR(100),
    
    -- Inherent Risk
    inherent_likelihood TINYINT UNSIGNED CHECK (inherent_likelihood BETWEEN 1 AND 5),
    inherent_impact TINYINT UNSIGNED CHECK (inherent_impact BETWEEN 1 AND 5),
    inherent_risk_score DECIMAL(5,2) GENERATED ALWAYS AS ((inherent_likelihood * inherent_impact) / 25 * 100) STORED,
    
    -- Residual Risk
    residual_likelihood TINYINT UNSIGNED CHECK (residual_likelihood BETWEEN 1 AND 5),
    residual_impact TINYINT UNSIGNED CHECK (residual_impact BETWEEN 1 AND 5),
    residual_risk_score DECIMAL(5,2) GENERATED ALWAYS AS ((residual_likelihood * residual_impact) / 25 * 100) STORED,
    
    -- Controls
    control_description TEXT,
    control_effectiveness ENUM('high', 'medium', 'low', 'none') DEFAULT 'none',
    
    -- Ownership
    owner_department_id INT UNSIGNED NOT NULL,
    owner_user_id INT UNSIGNED,
    
    -- Status
    status ENUM('identified', 'assessed', 'mitigating', 'mitigated', 'monitored', 'review', 'closed', 'rejected') DEFAULT 'identified',
    risk_level ENUM('critical', 'high', 'medium', 'low', 'very_low') GENERATED ALWAYS AS (
        CASE 
            WHEN inherent_risk_score >= 80 THEN 'critical'
            WHEN inherent_risk_score >= 60 THEN 'high'
            WHEN inherent_risk_score >= 40 THEN 'medium'
            WHEN inherent_risk_score >= 20 THEN 'low'
            ELSE 'very_low'
        END
    ) STORED,
    
    -- Dates
    identification_date DATE NOT NULL,
    assessment_date DATE,
    review_date DATE,
    mitigation_date DATE,
    closure_date DATE NULL,
    
    -- Mitigation
    mitigation_plan TEXT,
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_risk_code (risk_code),
    INDEX idx_risk_status (status),
    INDEX idx_risk_level (risk_level),
    INDEX idx_risk_category (category_id),
    INDEX idx_risk_department (owner_department_id),
    INDEX idx_risk_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: risk_treatments
-- Purpose: Track risk treatment strategies
-- ============================================================
CREATE TABLE IF NOT EXISTS risk_treatments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    risk_id INT UNSIGNED NOT NULL,
    treatment_type ENUM('avoid', 'reduce', 'transfer', 'accept', 'mitigate') NOT NULL,
    description TEXT NOT NULL,
    action_plan TEXT,
    responsible_person INT UNSIGNED,
    start_date DATE,
    target_completion_date DATE,
    actual_completion_date DATE,
    status ENUM('planned', 'in_progress', 'completed', 'overdue', 'cancelled') DEFAULT 'planned',
    effectiveness_rating TINYINT UNSIGNED CHECK (effectiveness_rating BETWEEN 1 AND 5),
    notes TEXT,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (risk_id) REFERENCES risk_register(id) ON DELETE CASCADE,
    
    INDEX idx_treatment_risk (risk_id),
    INDEX idx_treatment_status (status),
    INDEX idx_treatment_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;