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
    
    $query = "SELECT b.*, e.title as event_title, e.event_date, e.event_time,
              e.venue as event_venue, e.price as event_price, e.image_url as event_image
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

<style>
/* Animated Background */
body {
    background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    min-height: 100vh;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

:root {
    --primary-color: #4f46e5;
    --primary-color-rgb: 79, 70, 229;
    --success-color: #10b981;
    --success-color-rgb: 16, 185, 129;
    --accent-color: #06b6d4;
    --accent-color-rgb: 6, 182, 212;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --dark-color: #1e293b;
    --light-color: #f1f5f9;
    --card-bg: #ffffff;
    --border-radius: 12px;
    --border-radius-lg: 16px;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Animated Header */
.page-header {
    background: linear-gradient(135deg, var(--success-color) 0%, var(--accent-color) 50%, var(--primary-color) 100%);
    padding: 3rem 0;
    margin-bottom: 2rem;
    color: white;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.page-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg);
    animation: headerShine 4s infinite;
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 800;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: fadeInUp 0.8s ease-out;
    position: relative;
    z-index: 1;
}

/* Enhanced Booking Cards */
.booking-card {
    background: linear-gradient(145deg, var(--card-bg), var(--light-color));
    border: none;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    animation: slideInUp 0.6s ease-out;
}

.booking-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--success-color), var(--accent-color), var(--primary-color));
    animation: shimmer 3s infinite;
}

.booking-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: var(--shadow-xl);
}

.booking-card:hover::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(var(--primary-color-rgb), 0.05), rgba(var(--accent-color-rgb), 0.05));
    pointer-events: none;
}

/* Event Image Styling */
.event-image {
    height: 200px;
    object-fit: cover;
    transition: all 0.4s ease;
    position: relative;
}

.booking-card:hover .event-image {
    transform: scale(1.05);
}

/* Card Content */
.card-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 1rem;
    position: relative;
}

.card-title::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    border-radius: 2px;
}

/* Booking Details */
.booking-details p {
    display: flex;
    align-items: center;
    margin-bottom: 0.8rem;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
}

.booking-details p:hover {
    background: linear-gradient(135deg, rgba(var(--primary-color-rgb), 0.05), rgba(var(--accent-color-rgb), 0.05));
    transform: translateX(5px);
}

.booking-details i {
    width: 20px;
    margin-right: 10px;
    color: var(--accent-color);
    font-size: 1.1rem;
}

/* Enhanced Status Badges */
.booking-status .badge {
    font-size: 0.9rem;
    padding: 0.6rem 1.2rem;
    border-radius: 50px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    animation: pulse 2s infinite;
}

.booking-status .badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.booking-status .badge:hover::before {
    left: 100%;
}

.bg-success {
    background: linear-gradient(135deg, var(--success-color), #059669) !important;
    box-shadow: 0 4px 15px rgba(var(--success-color-rgb), 0.3);
}

.bg-warning {
    background: linear-gradient(135deg, var(--warning-color), #d97706) !important;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
}

.bg-danger {
    background: linear-gradient(135deg, var(--danger-color), #dc2626) !important;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

/* Enhanced Cancel Button */
.card-footer {
    background: linear-gradient(135deg, rgba(var(--light-color), 0.5), rgba(var(--card-bg), 0.8)) !important;
    border-top: 1px solid rgba(var(--accent-color-rgb), 0.1) !important;
    padding: 1rem 1.5rem;
}

.btn-outline-danger {
    border: 2px solid var(--danger-color);
    color: var(--danger-color);
    background: transparent;
    border-radius: 50px;
    padding: 0.6rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-outline-danger::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: var(--danger-color);
    transition: left 0.3s ease;
    z-index: -1;
}

.btn-outline-danger:hover::before {
    left: 0;
}

.btn-outline-danger:hover {
    color: white;
    border-color: var(--danger-color);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: linear-gradient(145deg, var(--card-bg), var(--light-color));
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
    animation: fadeInUp 0.8s ease-out;
}

.empty-state i {
    font-size: 4rem;
    color: var(--accent-color);
    margin-bottom: 1.5rem;
    animation: float 3s ease-in-out infinite;
}

.empty-state h3 {
    color: var(--dark-color);
    margin-bottom: 1rem;
    font-weight: 700;
}

.empty-state p {
    color: #64748b;
    margin-bottom: 2rem;
}

.empty-state .btn {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    border: none;
    border-radius: 50px;
    padding: 0.8rem 2rem;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
}

.empty-state .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(var(--primary-color-rgb), 0.3);
}

/* Animations */
@keyframes headerShine {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(200%) rotate(45deg); }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header h1 {
        font-size: 2rem;
    }

    .booking-card {
        margin-bottom: 1.5rem;
    }

    .card-title {
        font-size: 1.1rem;
    }

    .booking-details p {
        font-size: 0.9rem;
    }
}
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-ticket-alt me-3"></i>My Bookings</h1>
    </div>
</div>

<div class="container">
    <?php if (empty($bookings)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No Bookings Yet</h3>
            <p>You haven't made any bookings yet. Start exploring amazing events!</p>
            <a href="events.php" class="btn">
                <i class="fas fa-search me-2"></i>Browse Events
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($bookings as $index => $booking): ?>
                <div class="col-lg-6 mb-4" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                    <div class="card booking-card h-100">
                        <?php if ($booking['event_image']): ?>
                            <img src="<?php echo htmlspecialchars($booking['event_image']); ?>"
                                 class="card-img-top event-image"
                                 alt="<?php echo htmlspecialchars($booking['event_title']); ?>">
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($booking['event_title']); ?></h5>

                            <div class="booking-details">
                                <p>
                                    <i class="fas fa-calendar-alt"></i>
                                    <strong><?php echo date('F j, Y', strtotime($booking['event_date'])); ?></strong>
                                    at <strong><?php echo date('g:i A', strtotime($booking['event_time'])); ?></strong>
                                </p>

                                <p>
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($booking['event_venue']); ?>
                                </p>

                                <p>
                                    <i class="fas fa-ticket-alt"></i>
                                    <strong><?php echo $booking['quantity']; ?></strong> ticket<?php echo $booking['quantity'] > 1 ? 's' : ''; ?>
                                </p>

                                <p>
                                    <i class="fas fa-money-bill-wave"></i>
                                    Total: <strong><?php echo number_format($booking['quantity'] * $booking['event_price'], 0); ?> FCFA</strong>
                                </p>

                                <p>
                                    <i class="fas fa-clock"></i>
                                    Booked: <?php echo date('M j, Y g:i A', strtotime($booking['booking_date'])); ?>
                                </p>

                                <div class="booking-status mt-3">
                                    <?php
                                    $statusClass = match($booking['status']) {
                                        'confirmed' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $statusIcon = match($booking['status']) {
                                        'confirmed' => 'fa-check-circle',
                                        'pending' => 'fa-clock',
                                        'cancelled' => 'fa-times-circle',
                                        default => 'fa-question-circle'
                                    };
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <i class="fas <?php echo $statusIcon; ?> me-1"></i>
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <?php if ($booking['status'] !== 'cancelled'): ?>
                            <div class="card-footer">
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                    <i class="fas fa-times-circle me-2"></i>Cancel Booking
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