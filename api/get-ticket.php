<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to view tickets']);
    exit();
}

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

// Validate input
if ($booking_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit();
}

// Get booking details
$sql = "SELECT b.*, e.title, e.event_date, e.event_time, e.venue, 
               GROUP_CONCAT(bd.attendee_name SEPARATOR '|') as attendees,
               GROUP_CONCAT(bd.attendee_email SEPARATOR '|') as emails
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        LEFT JOIN booking_details bd ON b.id = bd.booking_id
        WHERE b.id = ? AND b.user_id = ?
        GROUP BY b.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit();
}

$booking = $result->fetch_assoc();

// Format data for response
$response = [
    'success' => true,
    'event' => [
        'title' => $booking['title'],
        'date' => date('F d, Y', strtotime($booking['event_date'])),
        'time' => date('g:i A', strtotime($booking['event_time'])),
        'venue' => $booking['venue']
    ],
    'booking' => [
        'id' => $booking['id'],
        'status' => $booking['status'],
        'quantity' => $booking['quantity'],
        'total_amount' => $booking['total_amount'],
        'qr_code' => $booking['qr_code'],
        'attendees' => array_map(function($name, $email) {
            return ['name' => $name, 'email' => $email];
        }, explode('|', $booking['attendees']), explode('|', $booking['emails']))
    ]
];

echo json_encode($response);
?> 