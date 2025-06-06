<?php
$page_title = "Booking Confirmation - Event Booking System";
require_once 'includes/header.php';
require_once 'config/database.php';

// Require login
requireLogin();

// Get latest booking
try {
    // Check what columns exist in events table
    $check_columns_sql = "SHOW COLUMNS FROM events";
    $check_stmt = $pdo->prepare($check_columns_sql);
    $check_stmt->execute();
    $columns = $check_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Build the SELECT query based on available columns
    $event_columns = ['e.title', 'e.price'];

    // Check for date/time columns
    if (in_array('event_date', $columns)) {
        $event_columns[] = 'e.event_date';
    } elseif (in_array('date', $columns)) {
        $event_columns[] = 'e.date as event_date';
    }

    if (in_array('event_time', $columns)) {
        $event_columns[] = 'e.event_time';
    } elseif (in_array('time', $columns)) {
        $event_columns[] = 'e.time as event_time';
    }

    if (in_array('venue', $columns)) {
        $event_columns[] = 'e.venue';
    } elseif (in_array('location', $columns)) {
        $event_columns[] = 'e.location as venue';
    }

    $sql = "SELECT b.*, " . implode(', ', $event_columns) . "
            FROM bookings b
            JOIN events e ON b.event_id = e.id
            WHERE b.user_id = ?
            ORDER BY b.booking_date DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        header("Location: bookings.php");
        exit();
    }

    // Generate QR code if not already generated
    if (!$booking['qr_code']) {
        // QR code data
        $qr_data = json_encode([
            'booking_id' => $booking['id'],
            'event_id' => $booking['event_id'],
            'user_id' => $booking['user_id'],
            'quantity' => $booking['quantity'],
            'event_date' => $booking['event_date'],
            'event_time' => $booking['event_time']
        ]);

        // Generate unique filename
        $qr_filename = 'qr_' . uniqid() . '.png';
        $qr_path = 'uploads/qrcodes/' . $qr_filename;
        
        // Create a simple text file with booking data
        file_put_contents($qr_path, $qr_data);

        // Update booking with QR code
        $update_sql = "UPDATE bookings SET qr_code = ? WHERE id = ?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$qr_filename, $booking['id']]);
        
        $booking['qr_code'] = $qr_filename;
    }

} catch (PDOException $e) {
    error_log("Booking Confirmation Error: " . $e->getMessage());
    $error = "An error occurred while fetching your booking details.";
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h2 class="mt-3 mb-4">Booking Confirmed!</h2>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php else: ?>
                        <div class="booking-details text-start">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5>Event Details</h5>
                                    <p>
                                        <strong><?php echo htmlspecialchars($booking['title']); ?></strong><br>
                                        <?php if (isset($booking['event_date']) && $booking['event_date']): ?>
                                        <i class="bi bi-calendar"></i>
                                        <?php echo date('F d, Y', strtotime($booking['event_date'])); ?><br>
                                        <?php endif; ?>
                                        <?php if (isset($booking['event_time']) && $booking['event_time']): ?>
                                        <i class="bi bi-clock"></i>
                                        <?php echo date('g:i A', strtotime($booking['event_time'])); ?><br>
                                        <?php endif; ?>
                                        <?php if (isset($booking['venue']) && $booking['venue']): ?>
                                        <i class="bi bi-geo-alt"></i>
                                        <?php echo htmlspecialchars($booking['venue']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Booking Information</h5>
                                    <p>
                                        <strong>Booking ID:</strong> #<?php echo $booking['id']; ?><br>
                                        <strong>Quantity:</strong> <?php echo $booking['quantity']; ?> ticket(s)<br>
                                        <strong>Total Amount:</strong> 
                                        <?php echo number_format($booking['total_amount'], 0); ?> FCFA<br>
                                        <strong>Status:</strong> 
                                        <span class="badge bg-success">Confirmed</span>
                                    </p>
                                </div>
                            </div>

                            <div class="text-center mb-4">
                                <h5>Your Booking Reference</h5>
                                <div class="alert alert-info">
                                    <?php echo $booking['qr_code']; ?>
                                </div>
                                <p class="text-muted mt-2">
                                    Show this reference code at the venue for entry
                                </p>
                            </div>

                            <?php if (isset($_SESSION['last_booking_attendee'])): ?>
                            <div class="attendee-info mb-4">
                                <h5>Attendee Information</h5>
                                <p>
                                    <strong>Name:</strong>
                                    <?php echo htmlspecialchars($_SESSION['last_booking_attendee']['name']); ?><br>
                                    <strong>Email:</strong>
                                    <?php echo htmlspecialchars($_SESSION['last_booking_attendee']['email']); ?><br>
                                    <strong>Phone:</strong>
                                    <?php echo htmlspecialchars($_SESSION['last_booking_attendee']['phone']); ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <div class="text-center">
                                <a href="download-ticket.php?id=<?php echo $booking['id']; ?>" 
                                   class="btn btn-primary me-2">
                                    <i class="bi bi-download"></i> Download Ticket
                                </a>
                                <a href="bookings.php" class="btn btn-outline-primary">
                                    <i class="bi bi-list"></i> View All Bookings
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 