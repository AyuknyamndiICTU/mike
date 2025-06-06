<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Require admin access
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get user ID and new status from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$new_status = isset($_GET['status']) ? $_GET['status'] : '';

// Validate status
if (!in_array($new_status, ['active', 'inactive'])) {
    $_SESSION['error'] = "Invalid status";
    header("Location: users.php");
    exit();
}

try {
    // Check if user exists and is not an admin
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("User not found");
    }

    if ($user['role'] === 'admin') {
        throw new Exception("Cannot modify admin status");
    }

    // Update user status
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $user_id]);

    $_SESSION['success'] = "User status updated successfully";
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: users.php");
exit(); 