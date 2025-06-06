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
               u.name as user_name,
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
    /* Header Section Styles */
    .page-header {
        background: linear-gradient(135deg, var(--primary-color), #224abe);
        padding: 2rem 0;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .page-header .container {
        position: relative;
    }

    .page-header h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 600;
    }

    /* Table Styles */
    .table-container {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        padding: 1.5rem;
        margin-top: 2rem;
    }
    
    .table th {
        cursor: pointer;
        white-space: nowrap;
        padding: 1rem;
        background-color: #f8f9fc;
        border-bottom: 2px solid #e3e6f0;
    }
    
    .table th:hover {
        background-color: #eaecf4;
    }
    
    .status-badge {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
        border-radius: 50rem;
    }

    .btn-group .btn {
        border-radius: 50%;
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-3px);
    }
</style>

<!-- Page Header -->
<div class="page-header animate__animated animate__fadeIn">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="animate__animated animate__fadeInLeft">All Bookings</h1>
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
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($booking['event_title']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($booking['venue']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($booking['user_name']); ?>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($booking['user_email']); ?></small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                            <td>
                                <?php echo date('M d, Y', strtotime($booking['event_date'])); ?>
                                <br>
                                <small class="text-muted">
                                    <?php echo date('g:i A', strtotime($booking['event_time'])); ?>
                                </small>
                            </td>
                            <td><?php echo $booking['quantity']; ?> tickets</td>
                            <td><?php echo number_format($booking['quantity'] * $booking['price']); ?> FCFA</td>
                            <td>
                                <span class="badge <?php 
                                    echo match($booking['status']) {
                                        'confirmed' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'confirmed')" 
                                            class="btn btn-sm btn-success me-1" 
                                            title="Confirm"
                                            <?php echo $booking['status'] === 'confirmed' ? 'disabled' : ''; ?>>
                                        <i class="bi bi-check"></i>
                                    </button>
                                    <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'cancelled')" 
                                            class="btn btn-sm btn-danger" 
                                            title="Cancel"
                                            <?php echo $booking['status'] === 'cancelled' ? 'disabled' : ''; ?>>
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-calendar-x display-4 text-muted"></i>
                                <p class="mt-3">No bookings found</p>
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