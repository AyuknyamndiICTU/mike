<?php
$page_title = "Admin Dashboard - Event Booking System";
require_once '../includes/header.php';
require_once '../config/database.php';

// Require admin access
requireAdmin();

// Get statistics
try {
    // Total events
    $events_sql = "SELECT COUNT(*) as total FROM events";
    $events_stmt = $pdo->query($events_sql);
    $total_events = $events_stmt->fetch()['total'];

    // Total users
    $users_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
    $users_stmt = $pdo->query($users_sql);
    $total_users = $users_stmt->fetch()['total'];

    // Total bookings
    $bookings_sql = "SELECT COUNT(*) as total FROM bookings";
    $bookings_stmt = $pdo->query($bookings_sql);
    $total_bookings = $bookings_stmt->fetch()['total'];

    // Recent events
    $recent_events_sql = "SELECT * FROM events ORDER BY created_at DESC LIMIT 5";
    $recent_events_stmt = $pdo->query($recent_events_sql);
} catch (PDOException $e) {
    error_log("Admin Dashboard Error: " . $e->getMessage());
    $error = "An error occurred while fetching dashboard data.";
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Admin Dashboard</h1>
        <a href="add-event.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add New Event
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Events</h5>
                    <h2 class="mb-0"><?php echo $total_events; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="mb-0"><?php echo $total_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Bookings</h5>
                    <h2 class="mb-0"><?php echo $total_bookings; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Events -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Events</h5>
            <a href="events.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($event = $recent_events_stmt->fetch()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($event['date'])); ?></td>
                                <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                <td>$<?php echo number_format($event['price'], 2); ?></td>
                                <td>
                                    <a href="edit-event.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="view-event.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteEvent(<?php echo $event['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function deleteEvent(eventId) {
    if (confirm('Are you sure you want to delete this event?')) {
        window.location.href = 'delete-event.php?id=' + eventId;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 