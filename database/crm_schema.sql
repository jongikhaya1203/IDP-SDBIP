-- CRM Schema for Reminder Management and SLA Tracking
-- SDBIP & IDP Management System

-- Create CRM Reminder Logs Table
CREATE TABLE IF NOT EXISTS crm_reminder_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reminder_type ENUM('submission', 'review', 'deadline', 'escalation', 'custom') NOT NULL DEFAULT 'submission',
    recipient_id INT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(255),
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    delivery_method ENUM('email', 'sms', 'both') NOT NULL DEFAULT 'email',
    status ENUM('pending', 'sent', 'failed', 'bounced') NOT NULL DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT,
    related_kpi_id INT NULL,
    related_quarter INT NULL,
    financial_year_id INT NOT NULL,
    directorate_id INT NULL,
    sent_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (related_kpi_id) REFERENCES kpis(id) ON DELETE SET NULL,
    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id) ON DELETE CASCADE,
    FOREIGN KEY (directorate_id) REFERENCES directorates(id) ON DELETE SET NULL,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_reminder_type (reminder_type),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at),
    INDEX idx_financial_year (financial_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create CRM SLA Configuration Table
CREATE TABLE IF NOT EXISTS crm_sla_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sla_type ENUM('submission_deadline', 'review_deadline', 'escalation_threshold', 'reminder_frequency') NOT NULL,
    sla_category ENUM('budget', 'internal_control', 'hr_vacancy', 'none') DEFAULT 'none',
    quarter INT NULL,
    days_before_deadline INT DEFAULT 7,
    days_after_deadline INT DEFAULT 3,
    reminder_interval_days INT DEFAULT 2,
    max_reminders INT DEFAULT 5,
    escalation_level INT DEFAULT 1,
    escalation_to_role ENUM('manager', 'director', 'admin') DEFAULT 'manager',
    email_template TEXT,
    sms_template VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sla_type (sla_type),
    INDEX idx_sla_category (sla_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create CRM Escalation History Table
CREATE TABLE IF NOT EXISTS crm_escalation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_user_id INT NOT NULL,
    escalated_to_user_id INT NOT NULL,
    escalation_level INT NOT NULL DEFAULT 1,
    reason TEXT NOT NULL,
    kpi_id INT NULL,
    quarter INT NOT NULL,
    financial_year_id INT NOT NULL,
    status ENUM('pending', 'acknowledged', 'resolved', 'expired') NOT NULL DEFAULT 'pending',
    acknowledged_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    resolution_notes TEXT,
    escalated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (original_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (escalated_to_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kpi_id) REFERENCES kpis(id) ON DELETE SET NULL,
    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id) ON DELETE CASCADE,
    FOREIGN KEY (escalated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_escalation_level (escalation_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create CRM Submission Deadlines Table
CREATE TABLE IF NOT EXISTS crm_submission_deadlines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    financial_year_id INT NOT NULL,
    quarter INT NOT NULL,
    submission_type ENUM('self_assessment', 'manager_review', 'independent_review', 'poe_upload') NOT NULL,
    deadline_date DATE NOT NULL,
    grace_period_days INT DEFAULT 3,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id) ON DELETE CASCADE,
    UNIQUE KEY unique_deadline (financial_year_id, quarter, submission_type),
    INDEX idx_quarter (quarter),
    INDEX idx_deadline (deadline_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default SLA configurations
INSERT INTO crm_sla_config (sla_type, sla_category, days_before_deadline, days_after_deadline, reminder_interval_days, max_reminders, escalation_level, escalation_to_role, email_template, is_active) VALUES
('submission_deadline', 'none', 7, 3, 2, 5, 1, 'manager',
 'Dear {recipient_name},\n\nThis is a reminder that your Q{quarter} KPI self-assessment submission is due on {deadline_date}.\n\nPlease log in to the SDBIP system and complete your submission.\n\nBest regards,\nSDBIP System', 1),
('submission_deadline', 'budget', 5, 2, 1, 7, 2, 'director',
 'Dear {recipient_name},\n\nURGENT: Budget-related KPIs require immediate attention.\n\nYour Q{quarter} submission for budget KPIs is due on {deadline_date}.\n\nBest regards,\nSDBIP System', 1),
('review_deadline', 'none', 5, 2, 2, 3, 1, 'director',
 'Dear {recipient_name},\n\nThis is a reminder that there are KPI assessments pending your review for Q{quarter}.\n\nPlease log in and complete your reviews by {deadline_date}.\n\nBest regards,\nSDBIP System', 1),
('escalation_threshold', 'none', 0, 7, 0, 0, 2, 'director',
 'Dear {recipient_name},\n\nESCALATION NOTICE: The following KPIs have not been submitted despite multiple reminders:\n\n{kpi_list}\n\nImmediate action is required.\n\nBest regards,\nSDBIP System', 1);

-- Insert default submission deadlines for current financial year
INSERT INTO crm_submission_deadlines (financial_year_id, quarter, submission_type, deadline_date, grace_period_days, description)
SELECT
    fy.id,
    q.quarter,
    st.submission_type,
    CASE
        WHEN q.quarter = 1 THEN DATE_ADD(fy.start_date, INTERVAL 4 MONTH)
        WHEN q.quarter = 2 THEN DATE_ADD(fy.start_date, INTERVAL 7 MONTH)
        WHEN q.quarter = 3 THEN DATE_ADD(fy.start_date, INTERVAL 10 MONTH)
        WHEN q.quarter = 4 THEN DATE_ADD(fy.start_date, INTERVAL 13 MONTH)
    END as deadline_date,
    CASE WHEN st.submission_type = 'self_assessment' THEN 5 ELSE 3 END as grace_period_days,
    CONCAT(st.description, ' - Q', q.quarter)
FROM financial_years fy
CROSS JOIN (SELECT 1 as quarter UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) q
CROSS JOIN (
    SELECT 'self_assessment' as submission_type, 'Self Assessment Deadline' as description
    UNION SELECT 'manager_review', 'Manager Review Deadline'
    UNION SELECT 'independent_review', 'Independent Review Deadline'
    UNION SELECT 'poe_upload', 'POE Upload Deadline'
) st
WHERE fy.is_current = 1
ON DUPLICATE KEY UPDATE deadline_date = VALUES(deadline_date);

-- Summary
SELECT 'CRM Schema created successfully' as status;
SELECT COUNT(*) as reminder_log_tables FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'crm_reminder_logs';
