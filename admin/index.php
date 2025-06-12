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

<style>
    /* CSS Variables for consistent theming */
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --glass-bg: rgba(255, 255, 255, 0.25);
        --glass-border: rgba(255, 255, 255, 0.18);
        --shadow-light: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        --shadow-heavy: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

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

    /* Floating particles animation */
    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image:
            radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    /* Enhanced Header Section */
    .page-header {
        background: var(--primary-gradient);
        padding: 3rem 0;
        margin-bottom: 2rem;
        color: white;
        box-shadow: var(--shadow-heavy);
        position: relative;
        overflow: hidden;
        border-radius: 0 0 2rem 2rem;
    }

    .page-header .container {
        position: relative;
        z-index: 2;
    }

    .page-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        animation: slideInFromLeft 1s ease-out;
        background: linear-gradient(135deg, #fff 0%, #f0f8ff 50%, #e6f3ff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        background-size: 200% 100%;
        animation: gradientShift 4s ease-in-out infinite, slideInFromLeft 1s ease-out;
    }

    @keyframes slideInFromLeft {
        0% {
            transform: translateX(-100px);
            opacity: 0;
        }
        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Stats Cards */
    .stats-overview {
        margin: -1rem 0 2rem 0;
        position: relative;
        z-index: 10;
    }

    .stat-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        animation: fadeInUp 0.8s ease-out;
        overflow: hidden;
        position: relative;
    }

    .stat-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: var(--shadow-heavy);
        background: rgba(255, 255, 255, 0.4);
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
        font-weight: 700;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: countUp 2s ease-out;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.8rem;
        margin: 0;
        font-weight: 600;
        color: #333;
    }

    @keyframes countUp {
        from { opacity: 0; transform: scale(0.5); }
        to { opacity: 1; transform: scale(1); }
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

    /* Enhanced Quick Action Buttons */
    .quick-actions {
        margin: 2rem 0;
    }

    .quick-action-btn {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        text-decoration: none;
        color: #333;
        display: block;
        position: relative;
        overflow: hidden;
    }

    .quick-action-btn:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: var(--shadow-heavy);
        background: rgba(255, 255, 255, 0.4);
        color: #333;
        text-decoration: none;
    }

    .quick-action-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Enhanced Action Buttons */
    .action-btn {
        width: 35px;
        height: 35px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin: 0 0.2rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 2px solid transparent;
        background-clip: padding-box;
    }

    .action-btn:hover {
        transform: translateY(-5px) scale(1.1);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    /* Enhanced Table Container */
    .table-container {
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: var(--shadow-light);
        padding: 2rem;
        margin-top: 2rem;
        position: relative;
        overflow: hidden;
        animation: slideInFromBottom 1s ease-out;
    }

    @keyframes slideInFromBottom {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .table tr {
        transition: all 0.3s ease;
        border: none;
    }

    .table tr:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.01);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 0.5rem;
    }

    .table td {
        padding: 1rem 1.2rem;
        border: none;
        vertical-align: middle;
        font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 2rem;
        }

        .stat-card {
            margin-bottom: 1rem;
        }

        .table-container {
            padding: 1rem;
            border-radius: 1rem;
        }

        .action-btn {
            width: 30px;
            height: 30px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>🎯 Admin Dashboard</h1>
                <p class="mb-0 opacity-75">Welcome back, <?php echo getCurrentUsername(); ?>! Here's what's happening with your events today.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-light btn-lg pulse" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger animate__animated animate__fadeIn"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="row">
            <div class="col-md-3">
                <a href="add-event.php" class="quick-action-btn animate__animated animate__fadeIn">
                    <i class="bi bi-plus-circle quick-action-icon"></i>
                    <h5 class="mb-0">Add Event</h5>
                </a>
            </div>
            <div class="col-md-3">
                <a href="users.php" class="quick-action-btn animate__animated animate__fadeIn">
                    <i class="bi bi-people quick-action-icon"></i>
                    <h5 class="mb-0">Manage Users</h5>
                </a>
            </div>
            <div class="col-md-3">
                <a href="bookings.php" class="quick-action-btn animate__animated animate__fadeIn">
                    <i class="bi bi-ticket-perforated quick-action-icon"></i>
                    <h5 class="mb-0">View Bookings</h5>
                </a>
            </div>
            <div class="col-md-3">
                <a href="reports.php" class="quick-action-btn animate__animated animate__fadeIn">
                    <i class="bi bi-graph-up quick-action-icon"></i>
                    <h5 class="mb-0">Analytics & Reports</h5>
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-overview">
        <div class="row">
            <div class="col-md-4">
                <div class="stat-card animate__animated animate__fadeInUp">
                    <i class="bi bi-calendar-event stat-icon"></i>
                    <h3 class="stat-value" data-value="<?php echo $total_events; ?>">0</h3>
                    <p class="stat-label">Total Events</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                    <i class="bi bi-people stat-icon"></i>
                    <h3 class="stat-value" data-value="<?php echo $total_users; ?>">0</h3>
                    <p class="stat-label">Total Users</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                    <i class="bi bi-ticket-perforated stat-icon"></i>
                    <h3 class="stat-value" data-value="<?php echo $total_bookings; ?>">0</h3>
                    <p class="stat-label">Total Bookings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Events -->
    <div class="table-container animate__animated animate__fadeIn" style="animation-delay: 0.6s">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">📅 Recent Events</h5>
            <a href="events.php" class="btn btn-primary btn-sm">
                <i class="bi bi-grid me-1"></i>View All
            </a>
        </div>
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
                                <div class="btn-group">
                                    <a href="edit-event.php?id=<?php echo $event['id']; ?>"
                                       class="action-btn btn btn-warning"
                                       data-bs-toggle="tooltip"
                                       title="Edit Event">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="view-event.php?id=<?php echo $event['id']; ?>"
                                       class="action-btn btn btn-success"
                                       data-bs-toggle="tooltip"
                                       title="View Event">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button"
                                            class="action-btn btn btn-danger"
                                            data-bs-toggle="tooltip"
                                            title="Delete Event"
                                            onclick="confirmDelete(<?php echo $event['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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

// Animate all stat values with staggered timing
document.querySelectorAll('.stat-value').forEach((element, index) => {
    const value = parseInt(element.dataset.value);
    setTimeout(() => {
        animateValue(element, 0, value, 2000);
    }, index * 200);
});

// Enhanced delete confirmation with SweetAlert2
function confirmDelete(eventId) {
    Swal.fire({
        title: '🗑️ Delete Event?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        background: 'rgba(255, 255, 255, 0.95)',
        backdrop: 'rgba(0, 0, 0, 0.4)',
        customClass: {
            popup: 'animated bounceIn'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });
            window.location.href = 'delete-event.php?id=' + eventId;
        }
    });
}

// Add enhanced animations on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add pulse animation class
    const refreshBtn = document.querySelector('.pulse');
    if (refreshBtn) {
        setInterval(() => {
            refreshBtn.classList.add('pulse');
            setTimeout(() => {
                refreshBtn.classList.remove('pulse');
            }, 2000);
        }, 10000);
    }

    // Add hover effects to quick action buttons
    document.querySelectorAll('.quick-action-btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });

        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});

// Add CSS for pulse animation
const style = document.createElement('style');
style.textContent = `
    .pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
`;
document.head.appendChild(style);
</script>

<!-- SweetAlert2 for better alerts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Admin JS (only include for admin pages) -->
<?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
<script src="/assets/js/admin.js"></script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>