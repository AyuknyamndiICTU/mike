<?php
$page_title = "My Bookings - Admin Dashboard";
require_once '../includes/header.php';
require_once '../config/database.php';

// Require admin access
requireAdmin();

// Get sorting parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'booking_date';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort column to prevent SQL injection
$allowed_sort_columns = ['booking_date', 'event_date', 'status'];
if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'booking_date';
}

// Validate order
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

try {
    // Fetch all bookings with event and user details
    $stmt = $pdo->prepare("
        SELECT b.*,
               e.title as event_title,
               e.event_date,
               e.event_time,
               e.venue,
               e.price,
               COALESCE(u.full_name, u.username) as user_name,
               u.email as user_email
        FROM bookings b
        JOIN events e ON b.event_id = e.id
        JOIN users u ON b.user_id = u.id
        ORDER BY {$sort} {$order}
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching bookings: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching bookings";
    $bookings = [];
}

// Function to toggle sort order
function getSortLink($column, $currentSort, $currentOrder) {
    $newOrder = ($currentSort === $column && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    return "?sort={$column}&order={$newOrder}";
}

// Function to get sort icon
function getSortIcon($column, $currentSort, $currentOrder) {
    if ($currentSort !== $column) {
        return '<i class="bi bi-arrow-down-up text-muted"></i>';
    }
    return $currentOrder === 'ASC' 
        ? '<i class="bi bi-arrow-up text-primary"></i>'
        : '<i class="bi bi-arrow-down text-primary"></i>';
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

/* Animated Header Section */
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

.page-header .container {
    position: relative;
    z-index: 1;
}

.page-header h1 {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 800;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: fadeInUp 0.8s ease-out;
}

/* Enhanced Table Container */
.table-container {
    background: linear-gradient(145deg, var(--card-bg), var(--light-color));
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-xl);
    padding: 2rem;
    margin-top: 2rem;
    position: relative;
    overflow: hidden;
    animation: slideInUp 0.8s ease-out;
}

.table-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--success-color), var(--accent-color), var(--primary-color));
    animation: shimmer 3s infinite;
}

/* Modern Table Styles */
.table {
    margin-bottom: 0;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.table th {
    cursor: pointer;
    white-space: nowrap;
    padding: 1.2rem 1rem;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    border: none;
    font-weight: 700;
    color: var(--dark-color);
    position: relative;
    transition: all 0.3s ease;
}

.table th:hover {
    background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    transform: translateY(-2px);
}

.table th::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.table th:hover::after {
    transform: scaleX(1);
}

.table tbody tr {
    transition: all 0.3s ease;
    animation: fadeInRow 0.6s ease-out;
}

.table tbody tr:hover {
    background: linear-gradient(135deg, rgba(var(--primary-color-rgb), 0.05), rgba(var(--accent-color-rgb), 0.05));
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.table td {
    padding: 1rem;
    border: none;
    vertical-align: middle;
    position: relative;
}

/* Animated Status Badges */
.status-badge {
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    animation: pulse 2s infinite;
}

.status-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.status-badge:hover::before {
    left: 100%;
}

.bg-success {
    background: linear-gradient(135deg, var(--success-color), #059669) !important;
    color: white;
    box-shadow: 0 4px 15px rgba(var(--success-color-rgb), 0.3);
}

.bg-warning {
    background: linear-gradient(135deg, var(--warning-color), #d97706) !important;
    color: white;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
}

.bg-danger {
    background: linear-gradient(135deg, var(--danger-color), #dc2626) !important;
    color: white;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

/* Enhanced Action Buttons */
.btn-group .btn {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    margin: 0 2px;
    position: relative;
    overflow: hidden;
}

.btn-group .btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255,255,255,0.3);
    border-radius: 50%;
    transition: all 0.3s ease;
    transform: translate(-50%, -50%);
}

.btn-group .btn:hover::before {
    width: 100%;
    height: 100%;
}

.btn-group .btn:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.btn-success {
    background: linear-gradient(135deg, var(--success-color), #059669);
    border: none;
}

.btn-danger {
    background: linear-gradient(135deg, var(--danger-color), #dc2626);
    border: none;
}

/* Booking ID Styling */
.booking-id {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: var(--primary-color);
    background: linear-gradient(135deg, rgba(var(--primary-color-rgb), 0.1), rgba(var(--accent-color-rgb), 0.1));
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
    border: 1px solid rgba(var(--primary-color-rgb), 0.2);
}

/* Event Title Styling */
.event-title {
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 0.3rem;
}

.event-venue {
    color: #64748b;
    font-size: 0.9rem;
    font-style: italic;
}

/* User Info Styling */
.user-name {
    font-weight: 600;
    color: var(--dark-color);
}

.user-email {
    color: #64748b;
    font-size: 0.9rem;
}

/* Amount Styling */
.amount {
    font-weight: 700;
    color: var(--success-color);
    font-size: 1.1rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #64748b;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
    animation: float 3s ease-in-out infinite;
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

@keyframes fadeInRow {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
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

    .table-container {
        padding: 1rem;
        margin-top: 1rem;
    }

    .table th, .table td {
        padding: 0.8rem 0.5rem;
        font-size: 0.9rem;
    }

    .btn-group .btn {
        width: 35px;
        height: 35px;
    }
}
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-calendar-check me-3"></i>All Bookings</h1>
        </div>
    </div>
</div>

<div class="container">
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Event</th>
                        <th>User</th>
                        <th>
                            <a href="<?php echo getSortLink('booking_date', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Booked On <?php echo getSortIcon('booking_date', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('event_date', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Event Date <?php echo getSortIcon('event_date', $sort, $order); ?>
                            </a>
                        </th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>
                            <a href="<?php echo getSortLink('status', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Status <?php echo getSortIcon('status', $sort, $order); ?>
                            </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $index => $booking): ?>
                        <tr style="animation-delay: <?php echo $index * 0.1; ?>s;">
                            <td>
                                <span class="booking-id">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></span>
                            </td>
                            <td>
                                <div class="event-title"><?php echo htmlspecialchars($booking['event_title']); ?></div>
                                <div class="event-venue">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($booking['venue']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="user-name"><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                <div class="user-email">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?php echo htmlspecialchars($booking['user_email']); ?>
                                </div>
                            </td>
                            <td>
                                <i class="fas fa-calendar me-2"></i>
                                <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                            </td>
                            <td>
                                <div>
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    <?php echo date('M d, Y', strtotime($booking['event_date'])); ?>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('g:i A', strtotime($booking['event_time'])); ?>
                                </small>
                            </td>
                            <td>
                                <i class="fas fa-ticket-alt me-2"></i>
                                <strong><?php echo $booking['quantity']; ?></strong> tickets
                            </td>
                            <td>
                                <span class="amount">
                                    <?php echo number_format($booking['quantity'] * $booking['price']); ?> FCFA
                                </span>
                            </td>
                            <td>
                                <span class="badge status-badge <?php
                                    echo match($booking['status']) {
                                        'confirmed' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                ?>">
                                    <i class="fas <?php
                                        echo match($booking['status']) {
                                            'confirmed' => 'fa-check-circle',
                                            'pending' => 'fa-clock',
                                            'cancelled' => 'fa-times-circle',
                                            default => 'fa-question-circle'
                                        };
                                    ?> me-1"></i>
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'confirmed')"
                                            class="btn btn-success"
                                            title="Confirm Booking"
                                            <?php echo $booking['status'] === 'confirmed' ? 'disabled' : ''; ?>>
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'cancelled')"
                                            class="btn btn-danger"
                                            title="Cancel Booking"
                                            <?php echo $booking['status'] === 'cancelled' ? 'disabled' : ''; ?>>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="9" class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h4>No Bookings Found</h4>
                                <p>There are currently no bookings in the system.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function updateBookingStatus(bookingId, newStatus) {
    const message = `Are you sure you want to ${newStatus} this booking?`;
    if (confirm(message)) {
        window.location.href = `update-booking-status.php?id=${bookingId}&status=${newStatus}`;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 