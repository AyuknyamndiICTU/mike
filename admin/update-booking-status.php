<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Require admin access
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get booking ID and new status from URL
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$new_status = isset($_GET['status']) ? $_GET['status'] : '';

// Validate status
if (!in_array($new_status, ['confirmed', 'cancelled', 'pending'])) {
    $_SESSION['error'] = "Invalid status";
    header("Location: bookings.php");
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get booking details
    $stmt = $pdo->prepare("
        SELECT b.*, e.total_seats, e.available_seats 
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception("Booking not found");
    }

    // Update event available seats based on status change
    if ($booking['status'] !== $new_status) {
        $seats_adjustment = 0;

        // If cancelling a confirmed booking, add seats back
        if ($booking['status'] === 'confirmed' && $new_status === 'cancelled') {
            $seats_adjustment = $booking['quantity'];
        }
        // If confirming a cancelled/pending booking, remove seats
        elseif (($booking['status'] === 'cancelled' || $booking['status'] === 'pending') && $new_status === 'confirmed') {
            $seats_adjustment = -$booking['quantity'];
        }

        if ($seats_adjustment !== 0) {
            $stmt = $pdo->prepare("
                UPDATE events 
                SET available_seats = available_seats + ? 
                WHERE id = ? AND available_seats + ? >= 0
            ");
            $result = $stmt->execute([$seats_adjustment, $booking['event_id'], $seats_adjustment]);

            if (!$result || $stmt->rowCount() === 0) {
                throw new Exception("Not enough available seats");
            }
        }
    }

    // Update booking status
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $booking_id]);

    // Commit transaction
    $pdo->commit();
    $_SESSION['success'] = "Booking status updated successfully";
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: bookings.php");
exit(); 