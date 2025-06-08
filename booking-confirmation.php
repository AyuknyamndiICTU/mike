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
        <div class="col-md-10">
            <div class="confirmation-card">
                <div class="confirmation-header">
                    <div class="success-animation">
                        <div class="checkmark-circle">
                            <div class="checkmark"></div>
                        </div>
                    </div>
                    <h1 class="confirmation-title">Booking Confirmed!</h1>
                    <p class="confirmation-subtitle">Your tickets have been successfully booked</p>
                </div>
                    
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger animate-error"><?php echo $error; ?></div>
                <?php else: ?>
                    <div class="confirmation-content">
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="info-card event-card animate-slide-left">
                                    <div class="card-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <h5 class="card-title">Event Details</h5>
                                    <div class="card-content">
                                        <h6 class="event-title"><?php echo htmlspecialchars($booking['title']); ?></h6>
                                        <?php if (isset($booking['event_date']) && $booking['event_date']): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo date('F d, Y', strtotime($booking['event_date'])); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (isset($booking['event_time']) && $booking['event_time']): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo date('g:i A', strtotime($booking['event_time'])); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (isset($booking['venue']) && $booking['venue']): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($booking['venue']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card booking-card animate-slide-right">
                                    <div class="card-icon">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <h5 class="card-title">Booking Information</h5>
                                    <div class="card-content">
                                        <div class="detail-item">
                                            <i class="fas fa-hashtag"></i>
                                            <span><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-users"></i>
                                            <span><strong>Quantity:</strong> <?php echo $booking['quantity']; ?> ticket(s)</span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <span><strong>Total Amount:</strong> <?php echo number_format($booking['total_amount'], 0); ?> FCFA</span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-check-circle"></i>
                                            <span><strong>Status:</strong> <span class="status-badge confirmed">Confirmed</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="qr-section animate-fade-up">
                            <div class="qr-card">
                                <div class="qr-header">
                                    <i class="fas fa-qrcode"></i>
                                    <h5>Your Booking Reference</h5>
                                </div>
                                <div class="qr-code-container">
                                    <div class="qr-code-display">
                                        <i class="fas fa-qrcode qr-icon"></i>
                                        <div class="qr-code-text"><?php echo $booking['qr_code']; ?></div>
                                    </div>
                                </div>
                                <p class="qr-instruction">
                                    <i class="fas fa-info-circle"></i>
                                    Show this reference code at the venue for entry
                                </p>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['last_booking_attendee'])): ?>
                        <div class="attendee-section animate-fade-up" style="animation-delay: 0.3s;">
                            <div class="info-card attendee-card">
                                <div class="card-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h5 class="card-title">Attendee Information</h5>
                                <div class="card-content">
                                    <div class="detail-item">
                                        <i class="fas fa-user-circle"></i>
                                        <span><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['last_booking_attendee']['name']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['last_booking_attendee']['email']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-phone"></i>
                                        <span><strong>Phone:</strong> <?php echo htmlspecialchars($_SESSION['last_booking_attendee']['phone']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="action-buttons animate-fade-up" style="animation-delay: 0.6s;">
                            <div class="button-group">
                                <a href="download-ticket.php?id=<?php echo $booking['id']; ?>"
                                   class="btn btn-primary btn-action">
                                    <i class="fas fa-download"></i>
                                    <span>Download Ticket</span>
                                </a>
                                <a href="bookings.php" class="btn btn-secondary btn-action">
                                    <i class="fas fa-list"></i>
                                    <span>View All Bookings</span>
                                </a>
                                <a href="events.php" class="btn btn-outline btn-action">
                                    <i class="fas fa-calendar-plus"></i>
                                    <span>Book More Events</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 