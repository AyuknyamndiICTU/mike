<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Require login
requireLogin();

// Get booking ID
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get booking details
    $sql = "SELECT b.*, e.title, e.event_date, e.event_time, e.venue, e.price, e.organizer_name 
            FROM bookings b 
            JOIN events e ON b.event_id = e.id 
            WHERE b.id = ? AND b.user_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        die("Booking not found");
    }

    // Generate ticket content
    $ticket_content = "
    EVENT BOOKING TICKET
    ===================

    Event: {$booking['title']}
    Date: " . date('F d, Y', strtotime($booking['event_date'])) . "
    Time: " . date('g:i A', strtotime($booking['event_time'])) . "
    Venue: {$booking['venue']}
    Organizer: {$booking['organizer_name']}

    ATTENDEE INFORMATION
    ===================
    Name: {$booking['attendee_name']}
    Email: {$booking['attendee_email']}
    Phone: {$booking['attendee_phone']}

    BOOKING DETAILS
    ==============
    Booking ID: #{$booking['id']}
    Quantity: {$booking['quantity']} ticket(s)
    Amount Paid: " . number_format($booking['total_amount'], 0) . " FCFA

    Reference Code: {$booking['qr_code']}

    IMPORTANT NOTES
    ==============
    1. Please arrive at least 30 minutes before the event start time.
    2. This ticket is valid only for the specified date and time.
    3. Please present this ticket (printed or digital) at the venue entrance.
    4. This ticket is non-transferable and non-refundable.
    ";

    // Set headers for file download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="Event_Ticket_' . $booking['id'] . '.txt"');
    header('Content-Length: ' . strlen($ticket_content));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    // Output ticket content
    echo $ticket_content;
    exit;

} catch (Exception $e) {
    error_log("Ticket Download Error: " . $e->getMessage());
    die("An error occurred while generating your ticket.");
}
?>