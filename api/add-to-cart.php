<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

// Get POST data
$event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate input
if ($event_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

try {
    // Check if event exists and has available seats
    $event_sql = "SELECT id, price, available_seats FROM events WHERE id = ? AND status = 'active'";
    $event_stmt = $pdo->prepare($event_sql);
    $event_stmt->execute([$event_id]);
    $event = $event_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found or not available']);
        exit();
    }

    if ($event['available_seats'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough tickets available']);
        exit();
    }

    // Start transaction
    $pdo->beginTransaction();

    // Check if item already exists in cart
    $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND event_id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$_SESSION['user_id'], $event_id]);
    $cart_item = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        // Update existing cart item
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        if ($new_quantity > $event['available_seats']) {
            echo json_encode(['success' => false, 'message' => 'Cannot add more tickets than available']);
            $pdo->rollBack();
            exit();
        }
        
        $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$new_quantity, $cart_item['id']]);
    } else {
        // Add new cart item
        $insert_sql = "INSERT INTO cart (user_id, event_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([$_SESSION['user_id'], $event_id, $quantity]);
    }

    // Get updated cart count
    $count_sql = "SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute([$_SESSION['user_id']]);
    $cart_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['cart_count'] ?? 0;

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Added to cart successfully',
        'cartCount' => (int)$cart_count
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?> 