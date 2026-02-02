<?php
require_once dirname(__DIR__) . '/config/app.php';

header('Content-Type: text/plain');

echo "=== Password Fix ===\n\n";

try {
    $db = db();

    // Generate fresh password hash
    $newPassword = 'password123';
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);

    echo "New hash for 'password123': $newHash\n\n";

    // Get current admin user
    $admin = $db->fetch("SELECT id, username, password_hash FROM users WHERE username = 'admin'");

    if ($admin) {
        echo "Current hash: " . $admin['password_hash'] . "\n\n";

        // Test if current hash works
        if (password_verify($newPassword, $admin['password_hash'])) {
            echo "✓ Current hash is VALID for 'password123'\n";
        } else {
            echo "✗ Current hash is INVALID\n";
            echo "Updating password...\n";

            $db->update('users', ['password_hash' => $newHash], 'id = ?', [$admin['id']]);
            echo "✓ Password updated!\n";
        }
    } else {
        echo "Admin user not found!\n";
    }

    echo "\n=== Done ===\n";
    echo "Try logging in with: admin / password123\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
