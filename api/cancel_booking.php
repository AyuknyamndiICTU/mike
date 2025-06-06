<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Validate booking ID
if (!isset($_POST['booking_id']) || !is_numeric($_POST['booking_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid booking ID']);
    exit();
}

$bookingId = (int)$_POST['booking_id'];
$userId = $_SESSION['user_id'];

try {
    // First check if the booking belongs to the user
    $checkQuery = "SELECT id FROM bookings WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute([$bookingId, $userId]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'You do not have permission to cancel this booking']);
        exit();
    }
    
    // Update the booking status to cancelled
    $updateQuery = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([$bookingId]);
    
    // Set success message
    setFlashMessage('success', 'Booking has been cancelled successfully');
    
    // Redirect back to bookings page
    header('Location: ../bookings.php');
    exit();
    
} catch (PDOException $e) {
    http_response_code(500);
    setFlashMessage('error', 'Error cancelling booking: ' . $e->getMessage());
    header('Location: ../bookings.php');
    exit();
} 