-- ============================================================
-- MIGRATION: 007_create_policies.sql
-- Description: Create policy management tables
-- Author: GRC Platform Team
-- Date: 2026-08-03
-- ============================================================

-- ============================================================
-- TABLE: policies
-- Purpose: Manage organizational policies
-- ============================================================
CREATE TABLE IF NOT EXISTS policies (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    policy_number VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    
    -- Version
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
    document_path VARCHAR(500),
    document_type VARCHAR(50),
    
    -- Compliance
    mandatory BOOLEAN DEFAULT TRUE,
    acknowledges_required BOOLEAN DEFAULT TRUE,
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_policy_number (policy_number),
    INDEX idx_policy_status (status),
    INDEX idx_policy_effective (effective_date),
    INDEX idx_policy_category (category),
    INDEX idx_policy_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: policy_versions
-- Purpose: Track policy versions
-- ============================================================
CREATE TABLE IF NOT EXISTS policy_versions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    policy_id INT UNSIGNED NOT NULL,
    version VARCHAR(20) NOT NULL,
    document_path VARCHAR(500),
    changes TEXT,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (policy_id) REFERENCES policies(id) ON DELETE CASCADE,
    
    INDEX idx_version_policy (policy_id),
    INDEX idx_version_number (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: policy_library
-- Purpose: Searchable policy library
-- ============================================================
CREATE TABLE IF NOT EXISTS policy_library (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    policy_id INT UNSIGNED NOT NULL,
    keywords TEXT,
    summary TEXT,
    tags JSON,
    view_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    last_accessed TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (policy_id) REFERENCES policies(id) ON DELETE CASCADE,
    
    FULLTEXT INDEX ft_library_keywords (keywords),
    FULLTEXT INDEX ft_library_summary (summary),
    INDEX idx_library_views (view_count),
    INDEX idx_library_downloads (download_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;