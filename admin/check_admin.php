<?php
require_once '../config/database.php';

try {
    // Check database connection
    echo "Database connection: SUCCESS<br>";
    
    // Check if users table exists
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Found tables: " . implode(", ", $tables) . "<br><br>";
    
    // Check admin account
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "Admin account found:<br>";
        echo "ID: " . $admin['id'] . "<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Role: " . $admin['role'] . "<br>";
    } else {
        echo "No admin account found. Please run the database initialization script at:<br>";
        echo "/database/init.php";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 