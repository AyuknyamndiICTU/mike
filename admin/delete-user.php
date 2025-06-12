<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Require admin access
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['error'] = "Invalid user ID";
    header("Location: users.php");
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // First, get the user details to check if it exists and role
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("User not found");
    }

    // Prevent deletion of admin users
    if (($user['role'] ?? 'user') === 'admin') {
        throw new Exception("Cannot delete admin users");
    }

    // Check if there are any bookings for this user
    $booking_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
    $booking_stmt->execute([$user_id]);
    $booking_count = $booking_stmt->fetchColumn();

    if ($booking_count > 0) {
        // Instead of deleting, we'll deactivate the user to preserve booking history
        $update_stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $update_stmt->execute([$user_id]);
        
        $_SESSION['success'] = "User has been deactivated (has existing bookings)";
    } else {
        // Delete the user if no bookings exist
        $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->execute([$user_id]);
        
        $_SESSION['success'] = "User deleted successfully";
    }

    // Commit transaction
    $pdo->commit();
    header("Location: users.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error deleting user: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header("Location: users.php");
    exit();
}
?>
