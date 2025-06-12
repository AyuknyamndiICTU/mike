<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$verification_result = null;
$booking_details = null;

if ($booking_id > 0) {
    try {
        // Get booking details for verification
        $sql = "SELECT b.*, e.title, e.event_date, e.event_time, e.venue, e.venue_address,
                       e.organizer_name, e.organizer_contact, u.username, u.full_name
                FROM bookings b
                JOIN events e ON b.event_id = e.id
                JOIN users u ON b.user_id = u.id
                WHERE b.id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        $booking_details = $stmt->fetch();

        if ($booking_details) {
            $verification_result = [
                'status' => 'success',
                'message' => 'Booking verified successfully!',
                'booking' => $booking_details
            ];
        } else {
            $verification_result = [
                'status' => 'error',
                'message' => 'Booking not found or invalid.'
            ];
        }
    } catch (PDOException $e) {
        $verification_result = [
            'status' => 'error',
            'message' => 'Verification failed. Please try again.'
        ];
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="verification-card">
                <div class="verification-header">
                    <div class="verification-icon">
                        <?php if ($verification_result && $verification_result['status'] === 'success'): ?>
                            <i class="fas fa-check-circle text-success"></i>
                        <?php elseif ($verification_result && $verification_result['status'] === 'error'): ?>
                            <i class="fas fa-times-circle text-danger"></i>
                        <?php else: ?>
                            <i class="fas fa-qrcode text-primary"></i>
                        <?php endif; ?>
                    </div>
                    <h1 class="verification-title">Booking Verification</h1>
                    <?php if (!$verification_result): ?>
                        <p class="verification-subtitle">Scan QR code or enter booking ID to verify</p>
                    <?php endif; ?>
                </div>

                <div class="verification-content">
                    <?php if ($verification_result): ?>
                        <div class="alert alert-<?php echo $verification_result['status'] === 'success' ? 'success' : 'danger'; ?>">
                            <i class="fas fa-<?php echo $verification_result['status'] === 'success' ? 'check' : 'exclamation-triangle'; ?>"></i>
                            <?php echo htmlspecialchars($verification_result['message']); ?>
                        </div>

                        <?php if ($verification_result['status'] === 'success' && $booking_details): ?>
                            <div class="booking-verification-details">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="verification-section">
                                            <h5><i class="fas fa-calendar-alt"></i> Event Details</h5>
                                            <div class="detail-item">
                                                <strong>Event:</strong> <?php echo htmlspecialchars($booking_details['title']); ?>
                                            </div>
                                            <div class="detail-item">
                                                <strong>Date:</strong> <?php echo date('M d, Y', strtotime($booking_details['event_date'])); ?>
                                            </div>
                                            <div class="detail-item">
                                                <strong>Time:</strong> <?php echo date('g:i A', strtotime($booking_details['event_time'])); ?>
                                            </div>
                                            <div class="detail-item">
                                                <strong>Venue:</strong> <?php echo htmlspecialchars($booking_details['venue']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="verification-section">
                                            <h5><i class="fas fa-ticket-alt"></i> Booking Details</h5>
                                            <div class="detail-item">
                                                <strong>Booking ID:</strong> #<?php echo str_pad($booking_details['id'], 6, '0', STR_PAD_LEFT); ?>
                                            </div>
                                            <div class="detail-item">
                                                <strong>Attendee:</strong> <?php echo htmlspecialchars($booking_details['full_name'] ?? $booking_details['username']); ?>
                                            </div>
                                            <div class="detail-item">
                                                <strong>Quantity:</strong> <?php echo $booking_details['quantity']; ?> ticket(s)
                                            </div>
                                            <div class="detail-item">
                                                <strong>Status:</strong> 
                                                <span class="badge bg-<?php echo $booking_details['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($booking_details['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="GET" class="verification-form">
                            <div class="mb-3">
                                <label for="booking_id" class="form-label">Booking ID</label>
                                <input type="number" class="form-control" id="booking_id" name="id" 
                                       placeholder="Enter booking ID to verify" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Verify Booking
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="verification-actions mt-4">
                        <a href="events.php" class="btn btn-outline-primary">
                            <i class="fas fa-calendar"></i> Browse Events
                        </a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="bookings.php" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> My Bookings
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.verification-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.verification-header {
    background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
    color: white;
    padding: 40px 30px;
    text-align: center;
}

.verification-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.verification-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.verification-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
}

.verification-content {
    padding: 30px;
}

.verification-section {
    background: #f8fafc;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.verification-section h5 {
    color: #1e293b;
    margin-bottom: 15px;
    font-weight: 600;
}

.detail-item {
    padding: 8px 0;
    border-bottom: 1px solid #e2e8f0;
}

.detail-item:last-child {
    border-bottom: none;
}

.verification-form {
    background: #f8fafc;
    padding: 25px;
    border-radius: 10px;
    border: 2px dashed #06b6d4;
}

.verification-actions {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.verification-actions .btn {
    margin: 0 10px;
}

.booking-verification-details {
    background: linear-gradient(145deg, #f0f9ff, #e0f2fe);
    border-radius: 10px;
    padding: 25px;
    margin-top: 20px;
    border: 1px solid #0ea5e9;
}
</style>

<?php require_once 'includes/footer.php'; ?>
