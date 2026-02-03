<?php
require_once __DIR__ . '/config/app.php';

echo "Testing Database Connection...\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_PORT: " . DB_PORT . "\n";
echo "DB_DATABASE: " . DB_DATABASE . "\n";

try {
    $db = db();
    echo "Database connection: OK\n\n";

    $user = $db->fetch("SELECT id, username, is_active, password_hash FROM users WHERE username = 'admin'");

    if ($user) {
        echo "Admin user found:\n";
        echo "  ID: " . $user['id'] . "\n";
        echo "  Username: " . $user['username'] . "\n";
        echo "  Is Active: " . $user['is_active'] . "\n";
        echo "  Password Hash: " . substr($user['password_hash'], 0, 20) . "...\n\n";

        // Test password verification
        $testPassword = 'password123';
        if (password_verify($testPassword, $user['password_hash'])) {
            echo "Password verification: OK - 'password123' is correct!\n";
        } else {
            echo "Password verification: FAILED\n";
            echo "Expected hash for 'password123' should start with \$2y\$10\$\n";
        }
    } else {
        echo "Admin user NOT FOUND in database!\n";

        // Check total users
        $count = $db->fetch("SELECT COUNT(*) as cnt FROM users");
        echo "Total users in database: " . $count['cnt'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
