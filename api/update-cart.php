<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to update cart']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$cart_id = isset($data['cart_id']) ? (int)$data['cart_id'] : 0;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;

// Validate input
if ($cart_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if cart item exists and belongs to user
    $check_sql = "SELECT c.*, e.available_seats 
                  FROM cart c 
                  JOIN events e ON c.event_id = e.id 
                  WHERE c.id = ? AND c.user_id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$cart_id, $_SESSION['user_id']]);
    $cart_item = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart_item) {
        throw new Exception('Cart item not found');
    }

    // Check if quantity is available
    if ($quantity > $cart_item['available_seats']) {
        throw new Exception('Not enough tickets available');
    }

    // Update quantity
    $update_sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);

    // Commit transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 