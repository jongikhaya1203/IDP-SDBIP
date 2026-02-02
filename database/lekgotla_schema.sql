-- =============================================================================
-- Mayoral Lekgotla Schema - IDP Priority Management
-- Manages priority changes based on Mayoral Imbizo commitments
-- =============================================================================

USE sdbip_idp;

-- -----------------------------------------------------------------------------
-- Lekgotla Sessions Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS lekgotla_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financial_year_id INT UNSIGNED NOT NULL,
    session_name VARCHAR(255) NOT NULL,
    session_date DATE NOT NULL,
    venue VARCHAR(255),
    presided_by VARCHAR(255) DEFAULT 'Municipal Mayor',
    linked_imbizo_id INT UNSIGNED NULL,
    status ENUM('draft', 'in_progress', 'completed', 'approved') DEFAULT 'draft',
    resolution_number VARCHAR(50),
    minutes_document VARCHAR(255),
    created_by INT UNSIGNED,
    approved_by INT UNSIGNED,
    approved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id),
    FOREIGN KEY (linked_imbizo_id) REFERENCES imbizo_sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Priority Categories
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS priority_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color_code VARCHAR(7) DEFAULT '#0d6efd',
    icon VARCHAR(50) DEFAULT 'bi-flag',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default categories
INSERT INTO priority_categories (name, description, color_code, icon, display_order) VALUES
('Infrastructure Development', 'Roads, water, sanitation, electricity infrastructure', '#198754', 'bi-building', 1),
('Service Delivery', 'Basic municipal services to communities', '#0d6efd', 'bi-truck', 2),
('Economic Development', 'Job creation, SMME support, local economic growth', '#ffc107', 'bi-graph-up', 3),
('Social Development', 'Community welfare, youth, elderly, disabled support', '#dc3545', 'bi-people', 4),
('Good Governance', 'Transparency, accountability, public participation', '#6f42c1', 'bi-shield-check', 5),
('Environmental Management', 'Climate change, waste management, green initiatives', '#20c997', 'bi-tree', 6),
('Human Settlements', 'Housing, informal settlement upgrades', '#fd7e14', 'bi-house', 7),
('Safety and Security', 'Community safety, disaster management', '#6c757d', 'bi-shield-lock', 8);

-- -----------------------------------------------------------------------------
-- IDP Priorities Master Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS idp_priorities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financial_year_id INT UNSIGNED NOT NULL,
    priority_code VARCHAR(20) NOT NULL,
    priority_name VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT UNSIGNED,
    directorate_id INT UNSIGNED,
    strategic_objective_id INT UNSIGNED,

    -- Source tracking
    source_type ENUM('original', 'imbizo', 'lekgotla', 'council') DEFAULT 'original',
    source_imbizo_id INT UNSIGNED NULL,
    source_lekgotla_id INT UNSIGNED NULL,
    source_action_item_id INT UNSIGNED NULL,

    -- Status and lifecycle
    status ENUM('active', 'on_track', 'at_risk', 'completed', 'deferred', 'discarded') DEFAULT 'active',
    priority_level ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',

    -- Budget allocation
    budget_allocated DECIMAL(15,2) DEFAULT 0.00,
    budget_spent DECIMAL(15,2) DEFAULT 0.00,
    budget_source VARCHAR(100),

    -- Timeline
    start_date DATE,
    target_completion_date DATE,
    actual_completion_date DATE,

    -- Targets and progress
    annual_target VARCHAR(255),
    q1_target VARCHAR(100),
    q2_target VARCHAR(100),
    q3_target VARCHAR(100),
    q4_target VARCHAR(100),
    current_progress INT DEFAULT 0,

    -- Responsible parties
    responsible_directorate_id INT UNSIGNED,
    responsible_user_id INT UNSIGNED,

    -- Audit trail
    created_by INT UNSIGNED,
    last_modified_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id),
    FOREIGN KEY (category_id) REFERENCES priority_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (directorate_id) REFERENCES directorates(id) ON DELETE SET NULL,
    FOREIGN KEY (strategic_objective_id) REFERENCES idp_strategic_objectives(id) ON DELETE SET NULL,
    FOREIGN KEY (source_imbizo_id) REFERENCES imbizo_sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (source_lekgotla_id) REFERENCES lekgotla_sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (responsible_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Lekgotla Priority Changes (Comparison/Tracking Table)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS lekgotla_priority_changes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lekgotla_session_id INT UNSIGNED NOT NULL,
    priority_id INT UNSIGNED NULL,

    -- Change type
    change_type ENUM('retain', 'new', 'modify', 'discard', 'defer') NOT NULL,

    -- For new priorities
    new_priority_name VARCHAR(255),
    new_priority_description TEXT,
    new_category_id INT UNSIGNED,
    new_directorate_id INT UNSIGNED,
    new_priority_level ENUM('critical', 'high', 'medium', 'low'),

    -- For modifications
    field_changed VARCHAR(100),
    old_value TEXT,
    new_value TEXT,

    -- Budget impact
    previous_budget DECIMAL(15,2),
    new_budget DECIMAL(15,2),
    budget_variance DECIMAL(15,2),
    budget_justification TEXT,

    -- Imbizo linkage
    linked_imbizo_action_id INT UNSIGNED,
    imbizo_commitment TEXT,

    -- Justification
    change_reason TEXT,
    community_impact TEXT,
    risk_assessment TEXT,

    -- Approval workflow
    proposed_by INT UNSIGNED,
    reviewed_by INT UNSIGNED,
    approved_by INT UNSIGNED,
    status ENUM('proposed', 'under_review', 'approved', 'rejected') DEFAULT 'proposed',
    review_comments TEXT,
    approval_date DATETIME,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (lekgotla_session_id) REFERENCES lekgotla_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (priority_id) REFERENCES idp_priorities(id) ON DELETE SET NULL,
    FOREIGN KEY (new_category_id) REFERENCES priority_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (new_directorate_id) REFERENCES directorates(id) ON DELETE SET NULL,
    FOREIGN KEY (linked_imbizo_action_id) REFERENCES imbizo_action_items(id) ON DELETE SET NULL,
    FOREIGN KEY (proposed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Lekgotla Resolutions
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS lekgotla_resolutions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lekgotla_session_id INT UNSIGNED NOT NULL,
    resolution_number VARCHAR(50) NOT NULL,
    resolution_title VARCHAR(255) NOT NULL,
    resolution_text TEXT NOT NULL,
    resolution_type ENUM('priority_change', 'budget_adjustment', 'policy_directive', 'action_required', 'information') DEFAULT 'priority_change',

    -- Linked changes
    linked_priority_change_ids JSON,

    -- Implementation
    implementation_deadline DATE,
    responsible_directorate_id INT UNSIGNED,
    responsible_user_id INT UNSIGNED,
    implementation_status ENUM('pending', 'in_progress', 'completed', 'overdue') DEFAULT 'pending',
    implementation_notes TEXT,

    -- Council submission
    submitted_to_council BOOLEAN DEFAULT FALSE,
    council_meeting_date DATE,
    council_resolution_ref VARCHAR(50),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (lekgotla_session_id) REFERENCES lekgotla_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (responsible_directorate_id) REFERENCES directorates(id) ON DELETE SET NULL,
    FOREIGN KEY (responsible_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Priority Comparison Snapshots (For historical comparison)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS priority_snapshots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financial_year_id INT UNSIGNED NOT NULL,
    snapshot_date DATE NOT NULL,
    snapshot_type ENUM('beginning_of_year', 'pre_lekgotla', 'post_lekgotla', 'quarterly', 'year_end') NOT NULL,
    lekgotla_session_id INT UNSIGNED NULL,

    -- Snapshot data (JSON for flexibility)
    priorities_data JSON,
    total_priorities INT,
    total_budget DECIMAL(15,2),

    -- Summary counts
    active_count INT DEFAULT 0,
    on_track_count INT DEFAULT 0,
    at_risk_count INT DEFAULT 0,
    completed_count INT DEFAULT 0,
    discarded_count INT DEFAULT 0,

    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id),
    FOREIGN KEY (lekgotla_session_id) REFERENCES lekgotla_sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Indexes for Performance
-- -----------------------------------------------------------------------------
CREATE INDEX idx_priorities_fy ON idp_priorities(financial_year_id);
CREATE INDEX idx_priorities_status ON idp_priorities(status);
CREATE INDEX idx_priorities_source ON idp_priorities(source_type);
CREATE INDEX idx_changes_session ON lekgotla_priority_changes(lekgotla_session_id);
CREATE INDEX idx_changes_type ON lekgotla_priority_changes(change_type);
CREATE INDEX idx_snapshots_fy ON priority_snapshots(financial_year_id);

-- -----------------------------------------------------------------------------
-- Sample Data for Current Financial Year
-- -----------------------------------------------------------------------------
INSERT INTO idp_priorities (financial_year_id, priority_code, priority_name, description, category_id, source_type, status, priority_level, budget_allocated, annual_target, current_progress) VALUES
(1, 'IDP-001', 'Pothole Repair Programme', 'Repair all major potholes on municipal roads within 48 hours of reporting', 1, 'original', 'on_track', 'high', 5000000.00, '500 potholes repaired', 65),
(1, 'IDP-002', 'Water Infrastructure Upgrade', 'Replace aging water pipes in CBD and surrounding areas', 1, 'original', 'active', 'critical', 15000000.00, '25km pipe replacement', 40),
(1, 'IDP-003', 'Free Basic Services Expansion', 'Extend free basic water and electricity to additional 5000 indigent households', 2, 'original', 'on_track', 'high', 8000000.00, '5000 households', 55),
(1, 'IDP-004', 'Youth Employment Programme', 'Create 1000 job opportunities for youth through municipal projects', 3, 'original', 'at_risk', 'high', 12000000.00, '1000 jobs created', 30),
(1, 'IDP-005', 'Community Hall Renovation', 'Renovate 10 community halls across the municipality', 4, 'original', 'active', 'medium', 6000000.00, '10 halls renovated', 20),
(1, 'IDP-006', 'Waste Management Improvement', 'Implement weekly refuse collection in all wards', 6, 'original', 'on_track', 'high', 10000000.00, '100% weekly collection', 75),
(1, 'IDP-007', 'Housing Delivery', 'Deliver 500 RDP houses in identified settlements', 7, 'original', 'active', 'critical', 50000000.00, '500 houses', 25),
(1, 'IDP-008', 'Street Lighting Project', 'Install 1000 new street lights in priority areas', 8, 'original', 'on_track', 'medium', 4000000.00, '1000 lights installed', 60);
