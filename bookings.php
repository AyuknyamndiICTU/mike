<?php
$page_title = "My Bookings";
require_once 'includes/header.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to view your bookings.');
    header('Location: login.php');
    exit();
}

// Get user's bookings
require_once 'config/database.php';

try {
    $userId = $_SESSION['user_id'];
    
    $query = "SELECT b.*, e.title as event_title, e.date as event_date, e.time as event_time, 
              e.venue as event_venue, e.price as event_price, e.image as event_image 
              FROM bookings b 
              JOIN events e ON b.event_id = e.id 
              WHERE b.user_id = ? 
              ORDER BY b.booking_date DESC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    setFlashMessage('error', 'Error fetching bookings: ' . $e->getMessage());
    $bookings = [];
}
?>

<div class="container">
    <h1 class="mb-4">My Bookings</h1>
    
    <?php if (empty($bookings)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> You don't have any bookings yet. 
            <a href="events.php" class="alert-link">Browse available events</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($bookings as $booking): ?>
                <div class="col-md-6 mb-4">
                    <div class="card booking-card h-100">
                        <?php if ($booking['event_image']): ?>
                            <img src="<?php echo htmlspecialchars($booking['event_image']); ?>" 
                                 class="card-img-top event-image" 
                                 alt="<?php echo htmlspecialchars($booking['event_title']); ?>">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($booking['event_title']); ?></h5>
                            
                            <div class="booking-details">
                                <p class="mb-2">
                                    <i class="bi bi-calendar-event"></i> 
                                    <?php echo date('F j, Y', strtotime($booking['event_date'])); ?>
                                    at <?php echo date('g:i A', strtotime($booking['event_time'])); ?>
                                </p>
                                
                                <p class="mb-2">
                                    <i class="bi bi-geo-alt"></i> 
                                    <?php echo htmlspecialchars($booking['event_venue']); ?>
                                </p>
                                
                                <p class="mb-2">
                                    <i class="bi bi-ticket-perforated"></i>
                                    Tickets: <?php echo $booking['quantity']; ?>
                                </p>
                                
                                <p class="mb-2">
                                    <i class="bi bi-cash"></i>
                                    Total: <?php echo number_format($booking['quantity'] * $booking['event_price'], 0); ?> FCFA
                                </p>
                                
                                <p class="mb-2">
                                    <i class="bi bi-clock-history"></i>
                                    Booked on: <?php echo date('M j, Y g:i A', strtotime($booking['booking_date'])); ?>
                                </p>
                                
                                <div class="booking-status mt-3">
                                    <?php
                                    $statusClass = match($booking['status']) {
                                        'confirmed' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($booking['status'] !== 'cancelled'): ?>
                            <div class="card-footer bg-transparent border-top-0">
                                <button type="button" 
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                    <i class="bi bi-x-circle"></i> Cancel Booking
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this booking? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Booking</button>
                <form id="cancelBookingForm" method="POST" action="api/cancel_booking.php">
                    <input type="hidden" name="booking_id" id="cancelBookingId">
                    <button type="submit" class="btn btn-danger">Yes, Cancel Booking</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cancelBooking(bookingId) {
    document.getElementById('cancelBookingId').value = bookingId;
    var modal = new bootstrap.Modal(document.getElementById('cancelBookingModal'));
    modal.show();
}
</script>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

<?php require_once 'includes/footer.php'; ?> 