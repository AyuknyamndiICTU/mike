<?php
define('DB_USER', 'root');
define('DB_PASS', 'admin237');
define('DB_NAME', 'event_booking');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

    // Test if database exists, create if it doesn't
    try {
        $pdo->query("USE " . DB_NAME);
    } catch (PDOException $e) {
        // Connect without database to create it
        $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE " . DB_NAME);
    }

} catch (PDOException $e) {
    error_log("Database Connection Error in " . __FILE__ . " : " . $e->getMessage());
    die("Database connection failed. Please check your database settings and make sure MySQL is running. Error: " . $e->getMessage());
}

// Function to sanitize input
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function to handle database errors
function handleDatabaseError($error) {
    error_log("Database Error: " . $error);
    return "An error occurred. Please try again later.";
}
?> 