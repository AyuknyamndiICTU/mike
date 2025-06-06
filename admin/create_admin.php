<?php
require_once __DIR__ . '/../config/database.php';

// Set admin account details
$admin_username = "admin";
$admin_email = "admin@example.com";
$admin_password = "admin123"; // You should change this
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

try {
    // Check if admin already exists
    $check_sql = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
    $check_stmt = $pdo->query($check_sql);
    
    if ($check_stmt->rowCount() === 0) {
        // Create admin account
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_username, $admin_email, $hashed_password]);
        
        echo "Admin account created successfully!<br>";
        echo "Username: " . $admin_username . "<br>";
        echo "Email: " . $admin_email . "<br>";
        echo "Password: " . $admin_password . "<br>";
        echo "<br>Please change these credentials immediately after logging in!";
    } else {
        echo "Admin account already exists.";
    }
} catch (PDOException $e) {
    echo "Error creating admin account: " . $e->getMessage();
}
?> 