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

// Custom CSS for dashboard
?>
<style>
    .stat-card {
        border-radius: 1rem;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-10px);
    }

    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.3;
        position: absolute;
        right: 1rem;
        top: 1rem;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.8rem;
        margin: 0;
    }

    .action-btn {
        width: 35px;
        height: 35px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin: 0 0.2rem;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        transform: translateY(-3px);
    }

    .table tr {
        transition: all 0.3s ease;
    }

    .table tr:hover {
        background-color: #f8f9fc;
        transform: scale(1.01);
    }

    .welcome-section {
        background: linear-gradient(135deg, var(--primary-color), #224abe);
        color: white;
        padding: 2rem;
        border-radius: 1rem;
        margin-bottom: 2rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .quick-actions {
        margin: 2rem 0;
    }

    .quick-action-btn {
        padding: 1rem;
        border-radius: 1rem;
        text-align: center;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .quick-action-btn:hover {
        transform: translateY(-5px);
    }

    .quick-action-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }
</style>

<div class="container mt-4">
    <!-- Welcome Section -->
    <div class="welcome-section animate__animated animate__fadeIn">
        <h1 class="mb-3">Welcome back, <?php echo getCurrentUsername(); ?>!</h1>
        <p class="mb-0">Here's what's happening with your events today.</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger animate__animated animate__fadeIn"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="row">
            <div class="col-md-3">
                <a href="add-event.php" class="quick-action-btn d-block text-decoration-none animate__animated animate__fadeIn">
                    <i class="bi bi-plus-circle quick-action-icon"></i>
                    <h5 class="mb-0">Add Event</h5>
                </a>
            </div>
            <div class="col-md-3">
                <a href="users.php" class="quick-action-btn d-block text-decoration-none animate__animated animate__fadeIn">
                    <i class="bi bi-people quick-action-icon"></i>
                    <h5 class="mb-0">Manage Users</h5>
                </a>
            </div>
            <div class="col-md-3">
                <a href="bookings.php" class="quick-action-btn d-block text-decoration-none animate__animated animate__fadeIn">
                    <i class="bi bi-ticket-perforated quick-action-icon"></i>
                    <h5 class="mb-0">View Bookings</h5>
                </a>
            </div>
            <div class="col-md-3">
                <a href="reports.php" class="quick-action-btn d-block text-decoration-none animate__animated animate__fadeIn">
                    <i class="bi bi-graph-up quick-action-icon"></i>
                    <h5 class="mb-0">Analytics</h5>
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card bg-primary text-white animate__animated animate__fadeInUp">
                <div class="card-body">
                    <i class="bi bi-calendar-event stat-icon"></i>
                    <h3 class="stat-value" data-value="<?php echo $total_events; ?>">0</h3>
                    <p class="stat-label">Total Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-success text-white animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <div class="card-body">
                    <i class="bi bi-people stat-icon"></i>
                    <h3 class="stat-value" data-value="<?php echo $total_users; ?>">0</h3>
                    <p class="stat-label">Total Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-info text-white animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                <div class="card-body">
                    <i class="bi bi-ticket-perforated stat-icon"></i>
                    <h3 class="stat-value" data-value="<?php echo $total_bookings; ?>">0</h3>
                    <p class="stat-label">Total Bookings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Events -->
    <div class="card animate__animated animate__fadeIn" style="animation-delay: 0.6s">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Events</h5>
            <a href="events.php" class="btn btn-primary btn-sm">
                <i class="bi bi-grid me-1"></i>View All
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
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
                            <tr class="animate__animated animate__fadeIn">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar2-event me-2 text-primary"></i>
                                        <?php echo htmlspecialchars($event['title']); ?>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                <td>
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?php echo htmlspecialchars($event['venue']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo number_format($event['price'], 0); ?> FCFA
                                    </span>
                                </td>
                                <td>
                                    <a href="edit-event.php?id=<?php echo $event['id']; ?>" 
                                       class="action-btn btn btn-outline-primary" 
                                       data-bs-toggle="tooltip" 
                                       title="Edit Event">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="view-event.php?id=<?php echo $event['id']; ?>" 
                                       class="action-btn btn btn-outline-info"
                                       data-bs-toggle="tooltip" 
                                       title="View Event">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" 
                                            class="action-btn btn btn-outline-danger"
                                            data-bs-toggle="tooltip"
                                            title="Delete Event"
                                            onclick="confirmDelete(<?php echo $event['id']; ?>)">
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
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

// Animate statistics
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Animate all stat values
document.querySelectorAll('.stat-value').forEach(element => {
    const value = parseInt(element.dataset.value);
    animateValue(element, 0, value, 2000);
});

// Delete event confirmation
function deleteEvent(eventId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'delete-event.php?id=' + eventId;
        }
    });
}
</script>

<!-- SweetAlert2 for better alerts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Admin JS (only include for admin pages) -->
<?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
<script src="/assets/js/admin.js"></script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?> 