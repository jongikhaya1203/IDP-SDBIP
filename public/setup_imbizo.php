<?php
/**
 * Imbizo Module Setup
 * Creates tables for Mayoral IDP Imbizo module
 */

require_once dirname(__DIR__) . '/config/app.php';

header('Content-Type: text/plain');

echo "=== Mayoral IDP Imbizo Setup ===\n\n";

try {
    $db = db();
    $pdo = $db->getConnection();

    echo "Creating Imbizo tables...\n\n";

    // Create Wards table
    $pdo->exec("CREATE TABLE IF NOT EXISTS wards (
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
    ) ENGINE=InnoDB");
    echo "✓ wards table created\n";

    // Create Sessions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS imbizo_sessions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        session_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NULL,
        ward_id INT UNSIGNED NULL,
        ward_name VARCHAR(100) NULL,
        venue VARCHAR(255) NULL,
        youtube_url VARCHAR(500) NULL,
        facebook_url VARCHAR(500) NULL,
        twitter_url VARCHAR(500) NULL,
        municipal_stream_url VARCHAR(500) NULL,
        ai_transcript TEXT NULL,
        ai_minutes TEXT NULL,
        ai_summary TEXT NULL,
        status ENUM('scheduled', 'live', 'completed', 'cancelled') DEFAULT 'scheduled',
        attendee_count INT DEFAULT 0,
        created_by INT UNSIGNED NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_date (session_date)
    ) ENGINE=InnoDB");
    echo "✓ imbizo_sessions table created\n";

    // Create Action Items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS imbizo_action_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        session_id INT UNSIGNED NOT NULL,
        item_number VARCHAR(20) NOT NULL,
        description TEXT NOT NULL,
        commitment TEXT NULL,
        target_date DATE NULL,
        priority ENUM('high', 'medium', 'low') DEFAULT 'medium',
        assigned_directorate_id INT UNSIGNED NULL,
        assigned_department_id INT UNSIGNED NULL,
        assigned_user_id INT UNSIGNED NULL,
        ward_id INT UNSIGNED NULL,
        ward_name VARCHAR(100) NULL,
        community_concern TEXT NULL,
        status ENUM('pending', 'in_progress', 'completed', 'overdue', 'escalated') DEFAULT 'pending',
        progress_percentage INT DEFAULT 0,
        completed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_session (session_id),
        INDEX idx_status (status),
        INDEX idx_directorate (assigned_directorate_id)
    ) ENGINE=InnoDB");
    echo "✓ imbizo_action_items table created\n";

    // Create Comments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS imbizo_comments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        action_item_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        comment_type ENUM('response', 'update', 'escalation', 'completion') DEFAULT 'response',
        content TEXT NOT NULL,
        new_status VARCHAR(50) NULL,
        progress_update INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_action_item (action_item_id)
    ) ENGINE=InnoDB");
    echo "✓ imbizo_comments table created\n";

    // Create POE table
    $pdo->exec("CREATE TABLE IF NOT EXISTS imbizo_poe (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        action_item_id INT UNSIGNED NOT NULL,
        comment_id INT UNSIGNED NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        file_size INT UNSIGNED NOT NULL,
        description TEXT NULL,
        status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
        reviewed_by INT UNSIGNED NULL,
        reviewed_at TIMESTAMP NULL,
        review_notes TEXT NULL,
        uploaded_by INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_action_item (action_item_id)
    ) ENGINE=InnoDB");
    echo "✓ imbizo_poe table created\n";

    // Insert sample wards
    $pdo->exec("INSERT IGNORE INTO wards (ward_number, ward_name, councillor_name) VALUES
        (1, 'Ward 1 - Central', 'Cllr. M. Molefe'),
        (2, 'Ward 2 - Northern', 'Cllr. T. Nkuna'),
        (3, 'Ward 3 - Southern', 'Cllr. S. Mabena'),
        (4, 'Ward 4 - Eastern', 'Cllr. P. Khumalo'),
        (5, 'Ward 5 - Western', 'Cllr. N. Dube'),
        (6, 'Ward 6 - Industrial', 'Cllr. J. Botha'),
        (7, 'Ward 7 - Residential', 'Cllr. L. Mokoena'),
        (8, 'Ward 8 - Rural', 'Cllr. D. Sithole'),
        (9, 'Ward 9 - Township', 'Cllr. B. Cele'),
        (10, 'Ward 10 - Suburban', 'Cllr. A. Pillay')");
    echo "✓ Sample wards inserted\n";

    // Insert sample imbizo session
    $pdo->exec("INSERT IGNORE INTO imbizo_sessions (id, title, description, session_date, start_time, venue, ward_name, status, created_by) VALUES
        (1, 'Ward 5 Community Engagement - Service Delivery', 'Mayoral visit to address water and sanitation concerns', CURDATE(), '09:00:00', 'Ward 5 Community Hall', 'Ward 5 - Western', 'completed', 1)");
    echo "✓ Sample session created\n";

    // Insert sample action items
    $pdo->exec("INSERT IGNORE INTO imbizo_action_items (session_id, item_number, description, commitment, target_date, priority, assigned_directorate_id, ward_name, community_concern, status, progress_percentage) VALUES
        (1, 'AI-001', 'Repair burst water pipe on Main Street', 'Will be fixed within 48 hours', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'high', 4, 'Ward 5 - Western', 'Residents reported burst pipe causing water wastage', 'in_progress', 60),
        (1, 'AI-002', 'Install streetlights on 5th Avenue', 'Will include in next quarter capital budget', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'medium', 4, 'Ward 5 - Western', 'Area is dark and unsafe at night', 'pending', 0),
        (1, 'AI-003', 'Clear illegal dumping site near school', 'Will mobilize cleaning team this week', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'high', 5, 'Ward 5 - Western', 'Health hazard for school children', 'completed', 100)");
    echo "✓ Sample action items created\n";

    // Create upload directory
    $uploadDir = UPLOAD_PATH . '/imbizo';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "✓ Upload directory created\n";
    }

    echo "\n=== Setup Complete ===\n";
    echo "\nYou can now access the Imbizo module at:\n";
    echo "http://localhost:3000/imbizo\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
