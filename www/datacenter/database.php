<?php 
/**
 * Database Connection
 * 
 * This file loads database credentials from environment variables or config file
 * Never commit actual credentials to version control
 */

// Try to load from config file first (for development)
$config_file = __DIR__ . '/database.config.php';
if (file_exists($config_file)) {
    $config = require $config_file;
} else {
    // Fallback to environment variables (for production)
    $config = [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'database' => getenv('DB_NAME') ?: 'ipnz_db',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        'timeout' => getenv('DB_TIMEOUT') ?: 300
    ];
}

ini_set('mysql.connect_timeout', $config['timeout']);
ini_set('default_socket_timeout', $config['timeout']);

// Enable mysqli error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $connection = new mysqli(
        $config['host'],
        $config['user'],
        $config['password'],
        $config['database']
    );
    
    // Set charset to prevent SQL injection via charset
    $connection->set_charset($config['charset']);
    
} catch (Exception $e) {
    // Log error securely, don't expose to users
    error_log("Database connection failed: " . $e->getMessage());
    die("Unable to connect to database. Please try again later.");
}

?>