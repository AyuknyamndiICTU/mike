<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to remove items from cart']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$cart_id = isset($data['cart_id']) ? (int)$data['cart_id'] : 0;

// Validate input
if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

try {
    // Check if cart item exists and belongs to user
    $check_sql = "SELECT id FROM cart WHERE id = ? AND user_id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$cart_id, $_SESSION['user_id']]);
    
    if (!$check_stmt->fetch()) {
        throw new Exception('Cart item not found');
    }

    // Remove item
    $delete_sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->execute([$cart_id, $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Item removed from cart']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 