<?php
/**
 * Schema Import Script
 * Run this once to import imbizo and lekgotla schemas
 */

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'sdbip_idp';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Connected to database successfully.\n\n";

    // Import imbizo schema first
    echo "Importing imbizo_schema.sql...\n";
    $imbizoSql = file_get_contents(__DIR__ . '/database/imbizo_schema.sql');

    // Remove USE statement if present (we're already connected to the db)
    $imbizoSql = preg_replace('/USE\s+\w+;/i', '', $imbizoSql);

    // Split by semicolons but not inside strings
    $statements = array_filter(array_map('trim', preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $imbizoSql)));

    foreach ($statements as $stmt) {
        if (!empty($stmt) && !preg_match('/^--/', $stmt)) {
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                // Ignore "already exists" errors
                if (strpos($e->getMessage(), '1050') === false && strpos($e->getMessage(), '1062') === false) {
                    echo "Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    echo "Imbizo schema imported.\n\n";

    // Import lekgotla schema
    echo "Importing lekgotla_schema.sql...\n";
    $lekgotlaSql = file_get_contents(__DIR__ . '/database/lekgotla_schema.sql');

    $statements = array_filter(array_map('trim', preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $lekgotlaSql)));

    foreach ($statements as $stmt) {
        if (!empty($stmt) && !preg_match('/^--/', $stmt)) {
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), '1050') === false && strpos($e->getMessage(), '1062') === false) {
                    echo "Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    echo "Lekgotla schema imported.\n\n";

    echo "=== DONE ===\n";
    echo "All schemas imported successfully!\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
