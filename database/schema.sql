-- =====================================================
-- SDBIP & IDP Management System Database Schema
-- Compliant with SA National Treasury MFMA Regulations
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+02:00";

-- Create database
CREATE DATABASE IF NOT EXISTS sdbip_idp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sdbip_idp;

-- =====================================================
-- 1. DIRECTORATES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS directorates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    head_user_id INT UNSIGNED NULL,
    budget_allocation DECIMAL(15,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- =====================================================
-- 2. DEPARTMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    directorate_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) NOT NULL,
    manager_id INT UNSIGNED NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (directorate_id) REFERENCES directorates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dept_code (directorate_id, code),
    INDEX idx_directorate (directorate_id)
) ENGINE=InnoDB;

-- =====================================================
-- 3. USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'director', 'manager', 'employee', 'independent_assessor') NOT NULL DEFAULT 'employee',
    directorate_id INT UNSIGNED NULL,
    department_id INT UNSIGNED NULL,
    employee_number VARCHAR(50) NULL,
    job_title VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    ldap_dn VARCHAR(500) NULL,
    is_ldap_user TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (directorate_id) REFERENCES directorates(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    INDEX idx_role (role),
    INDEX idx_directorate (directorate_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Add foreign key for directorate head
ALTER TABLE directorates ADD FOREIGN KEY (head_user_id) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE departments ADD FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL;

-- =====================================================
-- 4. FINANCIAL YEARS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS financial_years (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    year_label VARCHAR(20) NOT NULL UNIQUE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('planning', 'active', 'closed', 'archived') DEFAULT 'planning',
    is_current TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_current (is_current)
) ENGINE=InnoDB;

-- =====================================================
-- 5. IDP STRATEGIC OBJECTIVES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS idp_strategic_objectives (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financial_year_id INT UNSIGNED NOT NULL,
    objective_code VARCHAR(20) NOT NULL,
    objective_name VARCHAR(500) NOT NULL,
    description TEXT,
    national_priority_alignment VARCHAR(255) NULL COMMENT 'Links to NDP/MTSF priorities',
    provincial_priority_alignment VARCHAR(255) NULL,
    idp_goal VARCHAR(255) NULL,
    directorate_id INT UNSIGNED NOT NULL,
    weight DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Percentage weight for scoring',
    is_active TINYINT(1) DEFAULT 1,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id) ON DELETE CASCADE,
    FOREIGN KEY (directorate_id) REFERENCES directorates(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_objective (financial_year_id, objective_code),
    INDEX idx_fy (financial_year_id),
    INDEX idx_directorate (directorate_id)
) ENGINE=InnoDB;

-- =====================================================
-- 6. KEY PERFORMANCE INDICATORS (KPIs) TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS kpis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    strategic_objective_id INT UNSIGNED NOT NULL,
    kpi_code VARCHAR(30) NOT NULL,
    kpi_name VARCHAR(500) NOT NULL,
    description TEXT,
    unit_of_measure VARCHAR(100) NOT NULL COMMENT 'e.g., %, Number, Rand, Days',
    baseline VARCHAR(100) NULL COMMENT 'Previous year or baseline value',
    annual_target VARCHAR(100) NOT NULL,
    q1_target VARCHAR(100) NULL,
    q2_target VARCHAR(100) NULL,
    q3_target VARCHAR(100) NULL,
    q4_target VARCHAR(100) NULL,
    sla_category ENUM('budget', 'internal_control', 'hr_vacancy', 'none') DEFAULT 'none' COMMENT 'SLA dependency type',
    budget_required DECIMAL(15,2) DEFAULT 0.00,
    budget_allocated DECIMAL(15,2) DEFAULT 0.00,
    responsible_user_id INT UNSIGNED NULL,
    directorate_id INT UNSIGNED NOT NULL,
    data_source VARCHAR(255) NULL COMMENT 'Where evidence/data comes from',
    calculation_method TEXT NULL COMMENT 'How the KPI is calculated',
    reporting_frequency ENUM('monthly', 'quarterly', 'annually') DEFAULT 'quarterly',
    is_strategic TINYINT(1) DEFAULT 0 COMMENT 'Top-layer SDBIP indicator',
    is_active TINYINT(1) DEFAULT 1,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (strategic_objective_id) REFERENCES idp_strategic_objectives(id) ON DELETE CASCADE,
    FOREIGN KEY (responsible_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (directorate_id) REFERENCES directorates(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_kpi (strategic_objective_id, kpi_code),
    INDEX idx_objective (strategic_objective_id),
    INDEX idx_directorate (directorate_id),
    INDEX idx_responsible (responsible_user_id),
    INDEX idx_sla (sla_category)
) ENGINE=InnoDB;

-- =====================================================
-- 7. KPI QUARTERLY ACTUALS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS kpi_quarterly_actuals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kpi_id INT UNSIGNED NOT NULL,
    quarter TINYINT NOT NULL COMMENT '1, 2, 3, or 4',
    financial_year_id INT UNSIGNED NOT NULL,
    target_value VARCHAR(100) NULL,
    actual_value VARCHAR(100) NULL,
    variance DECIMAL(10,2) NULL COMMENT 'Percentage variance from target',
    achievement_status ENUM('achieved', 'partially_achieved', 'not_achieved', 'pending') DEFAULT 'pending',

    -- Self Assessment
    self_rating TINYINT NULL COMMENT '1-5 scale',
    self_comments TEXT NULL,
    self_submitted_at TIMESTAMP NULL,
    self_submitted_by INT UNSIGNED NULL,

    -- Manager Assessment
    manager_rating TINYINT NULL COMMENT '1-5 scale',
    manager_comments TEXT NULL,
    manager_reviewed_at TIMESTAMP NULL,
    manager_user_id INT UNSIGNED NULL,

    -- Independent Assessment
    independent_rating TINYINT NULL COMMENT '1-5 scale',
    independent_comments TEXT NULL,
    independent_reviewed_at TIMESTAMP NULL,
    independent_user_id INT UNSIGNED NULL,

    -- Aggregated Score
    aggregated_rating DECIMAL(3,2) NULL COMMENT 'Weighted average',

    -- Workflow Status
    status ENUM('draft', 'submitted', 'manager_review', 'independent_review', 'approved', 'rejected') DEFAULT 'draft',
    rejection_reason TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (kpi_id) REFERENCES kpis(id) ON DELETE CASCADE,
    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id) ON DELETE CASCADE,
    FOREIGN KEY (self_submitted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (independent_user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_quarterly (kpi_id, quarter, financial_year_id),
    INDEX idx_kpi (kpi_id),
    INDEX idx_quarter (quarter),
    INDEX idx_fy (financial_year_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================
-- 8. PROOF OF EVIDENCE (POE) TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS proof_of_evidence (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kpi_quarterly_id INT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL COMMENT 'Size in bytes',
    description TEXT NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    version INT DEFAULT 1 COMMENT 'For resubmissions',

    -- Manager Review
    manager_status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    manager_feedback TEXT NULL,
    manager_reviewed_by INT UNSIGNED NULL,
    manager_reviewed_at TIMESTAMP NULL,

    -- Independent Review
    independent_status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    independent_feedback TEXT NULL,
    independent_reviewed_by INT UNSIGNED NULL,
    independent_reviewed_at TIMESTAMP NULL,

    -- Resubmission
    resubmission_required TINYINT(1) DEFAULT 0,
    resubmission_deadline DATE NULL,
    parent_poe_id INT UNSIGNED NULL COMMENT 'Links to original POE if resubmission',

    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (kpi_quarterly_id) REFERENCES kpi_quarterly_actuals(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (manager_reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (independent_reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_poe_id) REFERENCES proof_of_evidence(id) ON DELETE SET NULL,
    INDEX idx_quarterly (kpi_quarterly_id),
    INDEX idx_uploader (uploaded_by),
    INDEX idx_manager_status (manager_status),
    INDEX idx_independent_status (independent_status)
) ENGINE=InnoDB;

-- =====================================================
-- 9. BUDGET PROJECTIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS budget_projections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financial_year_id INT UNSIGNED NOT NULL,
    directorate_id INT UNSIGNED NOT NULL,
    month TINYINT NOT NULL COMMENT '1-12 (July=1 to June=12)',

    -- Revenue
    revenue_source VARCHAR(255) NOT NULL,
    projected_revenue DECIMAL(15,2) DEFAULT 0.00,
    actual_revenue DECIMAL(15,2) DEFAULT 0.00,
    revenue_variance DECIMAL(15,2) GENERATED ALWAYS AS (actual_revenue - projected_revenue) STORED,

    -- Operating Expenditure
    operating_expenditure_projected DECIMAL(15,2) DEFAULT 0.00,
    operating_expenditure_actual DECIMAL(15,2) DEFAULT 0.00,
    operating_variance DECIMAL(15,2) GENERATED ALWAYS AS (operating_expenditure_actual - operating_expenditure_projected) STORED,

    -- Capital Expenditure
    capital_expenditure_projected DECIMAL(15,2) DEFAULT 0.00,
    capital_expenditure_actual DECIMAL(15,2) DEFAULT 0.00,
    capital_variance DECIMAL(15,2) GENERATED ALWAYS AS (capital_expenditure_actual - capital_expenditure_projected) STORED,

    notes TEXT NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id) ON DELETE CASCADE,
    FOREIGN KEY (directorate_id) REFERENCES directorates(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_budget (financial_year_id, directorate_id, month, revenue_source),
    INDEX idx_fy (financial_year_id),
    INDEX idx_directorate (directorate_id),
    INDEX idx_month (month)
) ENGINE=InnoDB;

-- =====================================================
-- 10. CAPITAL PROJECTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS capital_projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financial_year_id INT UNSIGNED NOT NULL,
    directorate_id INT UNSIGNED NOT NULL,
    project_name VARCHAR(500) NOT NULL,
    project_code VARCHAR(50) NOT NULL,
    description TEXT,
    ward_number VARCHAR(50) NULL COMMENT 'Ward location if applicable',

    -- Budget
    total_budget DECIMAL(15,2) NOT NULL,
    q1_budget DECIMAL(15,2) DEFAULT 0.00,
    q2_budget DECIMAL(15,2) DEFAULT 0.00,
    q3_budget DECIMAL(15,2) DEFAULT 0.00,
    q4_budget DECIMAL(15,2) DEFAULT 0.00,

    -- Expenditure
    q1_spent DECIMAL(15,2) DEFAULT 0.00,
    q2_spent DECIMAL(15,2) DEFAULT 0.00,
    q3_spent DECIMAL(15,2) DEFAULT 0.00,
    q4_spent DECIMAL(15,2) DEFAULT 0.00,

    -- Progress
    status ENUM('planning', 'procurement', 'in_progress', 'completed', 'on_hold', 'cancelled') DEFAULT 'planning',
    completion_percentage DECIMAL(5,2) DEFAULT 0.00,
    start_date DATE NULL,
    expected_completion DATE NULL,
    actual_completion DATE NULL,

    -- Funding
    funding_source VARCHAR(255) NULL COMMENT 'MIG, Own Revenue, Grant, etc.',

    project_manager_id INT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id) ON DELETE CASCADE,
    FOREIGN KEY (directorate_id) REFERENCES directorates(id) ON DELETE CASCADE,
    FOREIGN KEY (project_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_project (financial_year_id, project_code),
    INDEX idx_fy (financial_year_id),
    INDEX idx_directorate (directorate_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================
-- 11. PERFORMANCE AGREEMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS performance_agreements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    financial_year_id INT UNSIGNED NOT NULL,
    agreement_date DATE NOT NULL,
    linked_kpis JSON NULL COMMENT 'Array of KPI IDs linked to this agreement',

    -- Signatures
    signed_by_employee TINYINT(1) DEFAULT 0,
    employee_signed_date DATE NULL,
    signed_by_manager TINYINT(1) DEFAULT 0,
    manager_signed_date DATE NULL,
    manager_id INT UNSIGNED NULL,

    -- Status
    status ENUM('draft', 'pending_signature', 'active', 'completed', 'terminated') DEFAULT 'draft',

    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id) ON DELETE CASCADE,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_agreement (user_id, financial_year_id),
    INDEX idx_user (user_id),
    INDEX idx_fy (financial_year_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================
-- 12. AUDIT LOG TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action ENUM('create', 'update', 'delete', 'login', 'logout', 'export', 'approve', 'reject') NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id INT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_table (table_name),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- 13. NOTIFICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('info', 'warning', 'success', 'error', 'task', 'deadline', 'review') DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500) NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_type (type)
) ENGINE=InnoDB;

-- =====================================================
-- 14. AI REPORTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS ai_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financial_year_id INT UNSIGNED NOT NULL,
    quarter TINYINT NULL COMMENT '1-4 for quarterly, NULL for annual',
    report_type ENUM('quarterly_performance', 'annual_performance', 'directorate_analysis', 'budget_analysis', 'risk_assessment') NOT NULL,
    directorate_id INT UNSIGNED NULL COMMENT 'NULL for organization-wide reports',

    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL COMMENT 'AI-generated report content in HTML/Markdown',
    summary TEXT NULL COMMENT 'Executive summary',
    recommendations JSON NULL COMMENT 'Array of AI recommendations',
    risk_flags JSON NULL COMMENT 'Identified risks and concerns',

    generated_by INT UNSIGNED NULL,
    model_used VARCHAR(100) NULL COMMENT 'e.g., gpt-4',
    generation_tokens INT NULL,

    is_published TINYINT(1) DEFAULT 0,
    published_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id) ON DELETE CASCADE,
    FOREIGN KEY (directorate_id) REFERENCES directorates(id) ON DELETE SET NULL,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_fy (financial_year_id),
    INDEX idx_quarter (quarter),
    INDEX idx_type (report_type),
    INDEX idx_directorate (directorate_id)
) ENGINE=InnoDB;

-- =====================================================
-- 15. SETTINGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description VARCHAR(500) NULL,
    is_public TINYINT(1) DEFAULT 0,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================
-- 16. USER SESSIONS TABLE (for local auth)
-- =====================================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    payload TEXT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_activity (last_activity)
) ENGINE=InnoDB;

-- =====================================================
-- VIEWS FOR REPORTING
-- =====================================================

-- View: Directorate Performance Summary
CREATE OR REPLACE VIEW vw_directorate_performance AS
SELECT
    d.id AS directorate_id,
    d.name AS directorate_name,
    d.code AS directorate_code,
    fy.id AS financial_year_id,
    fy.year_label,
    qa.quarter,
    COUNT(DISTINCT k.id) AS total_kpis,
    SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) AS achieved_count,
    SUM(CASE WHEN qa.achievement_status = 'partially_achieved' THEN 1 ELSE 0 END) AS partial_count,
    SUM(CASE WHEN qa.achievement_status = 'not_achieved' THEN 1 ELSE 0 END) AS not_achieved_count,
    ROUND(AVG(qa.aggregated_rating), 2) AS avg_rating,
    ROUND((SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) / COUNT(DISTINCT k.id)) * 100, 2) AS achievement_percentage
FROM directorates d
LEFT JOIN kpis k ON k.directorate_id = d.id
LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id
LEFT JOIN financial_years fy ON fy.id = qa.financial_year_id
WHERE d.is_active = 1
GROUP BY d.id, d.name, d.code, fy.id, fy.year_label, qa.quarter;

-- View: KPI Status Overview
CREATE OR REPLACE VIEW vw_kpi_status AS
SELECT
    k.id AS kpi_id,
    k.kpi_code,
    k.kpi_name,
    k.sla_category,
    d.name AS directorate_name,
    so.objective_name,
    fy.year_label,
    qa.quarter,
    qa.target_value,
    qa.actual_value,
    qa.achievement_status,
    qa.self_rating,
    qa.manager_rating,
    qa.independent_rating,
    qa.aggregated_rating,
    qa.status AS review_status
FROM kpis k
JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
JOIN directorates d ON d.id = k.directorate_id
JOIN financial_years fy ON fy.id = so.financial_year_id
LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id
WHERE k.is_active = 1;

-- View: Budget Summary
CREATE OR REPLACE VIEW vw_budget_summary AS
SELECT
    d.id AS directorate_id,
    d.name AS directorate_name,
    fy.year_label,
    SUM(bp.projected_revenue) AS total_projected_revenue,
    SUM(bp.actual_revenue) AS total_actual_revenue,
    SUM(bp.operating_expenditure_projected) AS total_opex_projected,
    SUM(bp.operating_expenditure_actual) AS total_opex_actual,
    SUM(bp.capital_expenditure_projected) AS total_capex_projected,
    SUM(bp.capital_expenditure_actual) AS total_capex_actual
FROM directorates d
LEFT JOIN budget_projections bp ON bp.directorate_id = d.id
LEFT JOIN financial_years fy ON fy.id = bp.financial_year_id
GROUP BY d.id, d.name, fy.year_label;

-- =====================================================
-- DEFAULT SETTINGS
-- =====================================================
INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('municipality_name', 'Sample Municipality', 'string', 'Name of the municipality', 1),
('municipality_code', 'DC99', 'string', 'Municipal demarcation code', 1),
('province', 'Gauteng', 'string', 'Province name', 1),
('rating_self_weight', '0.20', 'string', 'Weight for self-rating in aggregated score', 0),
('rating_manager_weight', '0.40', 'string', 'Weight for manager rating in aggregated score', 0),
('rating_independent_weight', '0.40', 'string', 'Weight for independent rating in aggregated score', 0),
('poe_resubmission_days', '7', 'integer', 'Days allowed for POE resubmission', 0),
('openai_api_key', '', 'string', 'OpenAI API key for AI reports', 0),
('openai_model', 'gpt-4', 'string', 'OpenAI model to use', 0),
('ldap_enabled', 'true', 'boolean', 'Enable LDAP authentication', 0),
('session_timeout', '3600', 'integer', 'Session timeout in seconds', 0);

-- =====================================================
-- END OF SCHEMA
-- =====================================================
