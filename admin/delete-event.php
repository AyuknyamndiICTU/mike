<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Require admin access
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get event ID from URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Start transaction
    $pdo->beginTransaction();

    // First, get the event details to check if it exists and get the image path
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        throw new Exception("Event not found");
    }

    // Check if there are any bookings for this event
    $booking_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE event_id = ?");
    $booking_stmt->execute([$event_id]);
    $booking_count = $booking_stmt->fetchColumn();

    if ($booking_count > 0) {
        throw new Exception("Cannot delete event: There are existing bookings for this event");
    }

    // Delete the event
    $delete_stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $delete_stmt->execute([$event_id]);

    // Delete the event image if it exists
    if ($event['image_url'] && file_exists('../' . $event['image_url'])) {
        unlink('../' . $event['image_url']);
    }

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = "Event deleted successfully";
    header("Location: dashboard.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error deleting event: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header("Location: dashboard.php");
    exit();
} 