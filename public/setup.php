<?php
/**
 * Database Setup Script
 * Access this via: http://localhost:3000/setup.php
 */

require_once dirname(__DIR__) . '/config/app.php';

header('Content-Type: text/plain');

echo "=== SDBIP/IDP Database Setup ===\n\n";

try {
    $db = db();
    echo "✓ Database connection successful\n";
    echo "  Host: " . DB_HOST . "\n";
    echo "  Port: " . DB_PORT . "\n";
    echo "  Database: " . DB_DATABASE . "\n\n";

    // Check if users table exists
    $tables = $db->fetchAll("SHOW TABLES");
    echo "Tables in database: " . count($tables) . "\n";

    // Check users count
    $userCount = $db->fetch("SELECT COUNT(*) as cnt FROM users");
    echo "Users in database: " . ($userCount['cnt'] ?? 0) . "\n\n";

    if (($userCount['cnt'] ?? 0) == 0) {
        echo "No users found. Creating admin user...\n";

        // Create admin user with password 'password123'
        $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $db->query("INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)",
            ['admin', 'admin@municipality.gov.za', $passwordHash, 'System', 'Administrator', 'admin', 1]);

        echo "✓ Admin user created!\n";
        echo "  Username: admin\n";
        echo "  Password: password123\n";
    } else {
        // Check if admin exists
        $admin = $db->fetch("SELECT id, username, is_active FROM users WHERE username = 'admin'");
        if ($admin) {
            echo "✓ Admin user exists (ID: {$admin['id']}, Active: {$admin['is_active']})\n";

            // Reset password
            $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
            $db->update('users', ['password_hash' => $passwordHash, 'is_active' => 1], 'id = ?', [$admin['id']]);
            echo "✓ Password reset to 'password123'\n";
        } else {
            echo "Admin user not found. Creating...\n";
            $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
            $db->query("INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active)
                        VALUES (?, ?, ?, ?, ?, ?, ?)",
                ['admin', 'admin@municipality.gov.za', $passwordHash, 'System', 'Administrator', 'admin', 1]);
            echo "✓ Admin user created!\n";
        }
    }

    echo "\n=== Setup Complete ===\n";
    echo "You can now login at http://localhost:3000/login\n";
    echo "Username: admin\n";
    echo "Password: password123\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
