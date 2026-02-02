-- =====================================================
-- MAYORAL IDP IMBIZO MODULE - Database Schema
-- =====================================================

USE sdbip_idp;

-- =====================================================
-- 1. IMBIZO SESSIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS imbizo_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NULL,
    ward_id INT UNSIGNED NULL,
    ward_name VARCHAR(100) NULL,
    venue VARCHAR(255) NULL,

    -- Livestream Links
    youtube_url VARCHAR(500) NULL,
    facebook_url VARCHAR(500) NULL,
    twitter_url VARCHAR(500) NULL,
    municipal_stream_url VARCHAR(500) NULL,

    -- AI Minutes
    ai_transcript TEXT NULL,
    ai_minutes TEXT NULL,
    ai_summary TEXT NULL,

    -- Status
    status ENUM('scheduled', 'live', 'completed', 'cancelled') DEFAULT 'scheduled',
    attendee_count INT DEFAULT 0,

    -- Metadata
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_date (session_date)
) ENGINE=InnoDB;

-- =====================================================
-- 2. IMBIZO ACTION ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS imbizo_action_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,

    -- Action Item Details
    item_number VARCHAR(20) NOT NULL,
    description TEXT NOT NULL,
    commitment TEXT NULL COMMENT 'Mayor''s commitment/promise',
    target_date DATE NULL,
    priority ENUM('high', 'medium', 'low') DEFAULT 'medium',

    -- Assignment
    assigned_directorate_id INT UNSIGNED NULL,
    assigned_department_id INT UNSIGNED NULL,
    assigned_user_id INT UNSIGNED NULL,

    -- Ward Info
    ward_id INT UNSIGNED NULL,
    ward_name VARCHAR(100) NULL,
    community_concern TEXT NULL COMMENT 'Original concern raised by community',

    -- Status Tracking
    status ENUM('pending', 'in_progress', 'completed', 'overdue', 'escalated') DEFAULT 'pending',
    progress_percentage INT DEFAULT 0,

    -- Timestamps
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (session_id) REFERENCES imbizo_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_directorate_id) REFERENCES directorates(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_session (session_id),
    INDEX idx_status (status),
    INDEX idx_directorate (assigned_directorate_id)
) ENGINE=InnoDB;

-- =====================================================
-- 3. IMBIZO COMMENTS/RESPONSES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS imbizo_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_item_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,

    -- Comment Content
    comment_type ENUM('response', 'update', 'escalation', 'completion') DEFAULT 'response',
    content TEXT NOT NULL,

    -- Status Update (optional)
    new_status VARCHAR(50) NULL,
    progress_update INT NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (action_item_id) REFERENCES imbizo_action_items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_action_item (action_item_id)
) ENGINE=InnoDB;

-- =====================================================
-- 4. IMBIZO POE (PROOF OF EVIDENCE) TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS imbizo_poe (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_item_id INT UNSIGNED NOT NULL,
    comment_id INT UNSIGNED NULL,

    -- File Details
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    description TEXT NULL,

    -- Status
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    reviewed_by INT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    review_notes TEXT NULL,

    -- Timestamps
    uploaded_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (action_item_id) REFERENCES imbizo_action_items(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES imbizo_comments(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_action_item (action_item_id)
) ENGINE=InnoDB;

-- =====================================================
-- 5. WARDS TABLE (if not exists)
-- =====================================================
CREATE TABLE IF NOT EXISTS wards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ward_number INT NOT NULL,
    ward_name VARCHAR(100) NOT NULL,
    councillor_name VARCHAR(255) NULL,
    councillor_contact VARCHAR(100) NULL,
    population_estimate INT NULL,
    area_description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ward (ward_number)
) ENGINE=InnoDB;

-- =====================================================
-- SAMPLE WARDS DATA
-- =====================================================
INSERT IGNORE INTO wards (ward_number, ward_name, councillor_name) VALUES
(1, 'Ward 1 - Central', 'Cllr. M. Molefe'),
(2, 'Ward 2 - Northern', 'Cllr. T. Nkuna'),
(3, 'Ward 3 - Southern', 'Cllr. S. Mabena'),
(4, 'Ward 4 - Eastern', 'Cllr. P. Khumalo'),
(5, 'Ward 5 - Western', 'Cllr. N. Dube'),
(6, 'Ward 6 - Industrial', 'Cllr. J. Botha'),
(7, 'Ward 7 - Residential', 'Cllr. L. Mokoena'),
(8, 'Ward 8 - Rural', 'Cllr. D. Sithole'),
(9, 'Ward 9 - Township', 'Cllr. B. Cele'),
(10, 'Ward 10 - Suburban', 'Cllr. A. Pillay');

-- =====================================================
-- VIEW: Action Items Summary
-- =====================================================
CREATE OR REPLACE VIEW vw_imbizo_action_summary AS
SELECT
    ai.id,
    ai.item_number,
    ai.description,
    ai.status,
    ai.priority,
    ai.target_date,
    ai.progress_percentage,
    ai.ward_name,
    s.title as session_title,
    s.session_date,
    d.name as directorate_name,
    d.code as directorate_code,
    CONCAT(u.first_name, ' ', u.last_name) as assigned_to,
    (SELECT COUNT(*) FROM imbizo_comments WHERE action_item_id = ai.id) as comment_count,
    (SELECT COUNT(*) FROM imbizo_poe WHERE action_item_id = ai.id) as poe_count
FROM imbizo_action_items ai
JOIN imbizo_sessions s ON s.id = ai.session_id
LEFT JOIN directorates d ON d.id = ai.assigned_directorate_id
LEFT JOIN users u ON u.id = ai.assigned_user_id;
