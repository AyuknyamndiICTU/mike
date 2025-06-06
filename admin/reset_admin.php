<?php
require_once __DIR__ . '/../config/database.php';

try {
    $admin_email = "admin@example.com";
    $new_password = "admin123";
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update admin password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'admin'");
    $result = $stmt->execute([$hashed_password, $admin_email]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo "Admin password reset successfully!<br>";
        echo "Email: " . $admin_email . "<br>";
        echo "New Password: " . $new_password . "<br>";
    } else {
        echo "No admin account found to reset.";
    }
} catch (PDOException $e) {
    echo "Error resetting password: " . $e->getMessage();
}
?> 