<?php
/**
 * Development Server Launcher
 * SDBIP & IDP Management System
 *
 * Usage: php start.php
 * Access: http://localhost:3000
 */

$host = '127.0.0.1';
$port = 3000;
$docroot = __DIR__ . '/public';

// Check if port is available
$socket = @fsockopen($host, $port, $errno, $errstr, 1);
if ($socket) {
    fclose($socket);
    echo "\033[31mError: Port {$port} is already in use.\033[0m\n";
    echo "Please stop the service using this port or use a different port.\n";
    exit(1);
}

echo "\n";
echo "\033[32m╔══════════════════════════════════════════════════════════════╗\033[0m\n";
echo "\033[32m║     SDBIP & IDP Management System - Development Server       ║\033[0m\n";
echo "\033[32m╠══════════════════════════════════════════════════════════════╣\033[0m\n";
echo "\033[32m║                                                              ║\033[0m\n";
echo "\033[32m║  Server running at: \033[36mhttp://localhost:{$port}\033[32m                   ║\033[0m\n";
echo "\033[32m║                                                              ║\033[0m\n";
echo "\033[32m║  Document root: \033[33m{$docroot}\033[32m              ║\033[0m\n";
echo "\033[32m║                                                              ║\033[0m\n";
echo "\033[32m║  Default login:                                              ║\033[0m\n";
echo "\033[32m║    Username: \033[36madmin\033[32m                                           ║\033[0m\n";
echo "\033[32m║    Password: \033[36mpassword123\033[32m                                     ║\033[0m\n";
echo "\033[32m║                                                              ║\033[0m\n";
echo "\033[32m║  Press Ctrl+C to stop the server                             ║\033[0m\n";
echo "\033[32m╚══════════════════════════════════════════════════════════════╝\033[0m\n";
echo "\n";

// Copy .env.example to .env if .env doesn't exist
$envFile = __DIR__ . '/.env';
$envExample = __DIR__ . '/.env.example';
if (!file_exists($envFile) && file_exists($envExample)) {
    copy($envExample, $envFile);
    echo "\033[33mCreated .env file from .env.example\033[0m\n";
    echo "\033[33mPlease configure your database and LDAP settings in .env\033[0m\n\n";
}

// Start the PHP built-in server
$command = sprintf(
    'php -S %s:%d -t %s',
    $host,
    $port,
    escapeshellarg($docroot)
);

passthru($command);
