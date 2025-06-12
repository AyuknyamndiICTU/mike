<?php
$page_title = "All Events - Admin Dashboard";
require_once '../includes/header.php';
require_once '../config/database.php';

// Require admin access
requireAdmin();

// Get sorting parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'event_date';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Validate sort column to prevent SQL injection
$allowed_sort_columns = ['title', 'event_date', 'venue', 'category', 'price', 'total_seats'];
if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'event_date';
}

// Validate order
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

try {
    // Fetch all events with booking counts
    $stmt = $pdo->prepare("
        SELECT e.*, 
               COUNT(DISTINCT b.id) as total_bookings,
               COALESCE(SUM(b.quantity), 0) as booked_seats
        FROM events e
        LEFT JOIN bookings b ON e.id = b.event_id
        GROUP BY e.id
        ORDER BY {$sort} {$order}
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching events";
    $events = [];
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
    /* CSS Variables for consistent theming */
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
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

    .header-actions {
        animation: slideInFromRight 1s ease-out;
    }

    @keyframes slideInFromRight {
        0% {
            transform: translateX(100px);
            opacity: 0;
        }
        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .header-actions .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 50rem;
        font-weight: 600;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .header-actions .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .header-actions .btn:hover::before {
        left: 100%;
    }

    .header-actions .btn:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .btn-create {
        background: white;
        color: var(--primary-color);
        border: none;
        box-shadow: 0 4px 15px rgba(255,255,255,0.3);
    }

    .btn-create:hover {
        background: #f8f9fc;
        color: #224abe;
        box-shadow: 0 8px 25px rgba(255,255,255,0.4);
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
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .stat-card:hover::before {
        left: 100%;
    }

    .stat-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: var(--shadow-heavy);
        background: rgba(255, 255, 255, 0.4);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: countUp 2s ease-out;
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

    .table-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .table-container:hover::before {
        left: 100%;
    }

    /* Enhanced Table Styles */
    .table {
        position: relative;
        z-index: 2;
    }

    .table th {
        cursor: pointer;
        white-space: nowrap;
        padding: 1.2rem;
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: none;
        border-bottom: 2px solid rgba(255,255,255,0.3);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .table th:hover {
        background: rgba(255,255,255,0.4);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .table th::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: var(--primary-gradient);
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    .table th:hover::after {
        width: 100%;
    }

    .table tbody tr {
        transition: all 0.3s ease;
        border: none;
        animation: fadeInRow 0.5s ease-out;
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

    .table tbody tr:hover {
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

    /* Enhanced Event Images */
    .event-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .event-image:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }

    /* Enhanced Status Badges */
    .status-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 50rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .status-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .status-badge:hover::before {
        left: 100%;
    }

    .bg-success {
        background: var(--success-gradient) !important;
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
    }

    .bg-warning {
        background: var(--warning-gradient) !important;
        box-shadow: 0 4px 15px rgba(67, 233, 123, 0.3);
    }

    .bg-danger {
        background: var(--danger-gradient) !important;
        box-shadow: 0 4px 15px rgba(250, 112, 154, 0.3);
    }

    .bg-info {
        background: var(--info-gradient) !important;
        box-shadow: 0 4px 15px rgba(168, 237, 234, 0.3);
    }

    .bg-primary {
        background: var(--primary-gradient) !important;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    /* Enhanced Action Buttons */
    .btn-group {
        display: flex;
        gap: 0.5rem;
    }

    .btn-group .btn {
        border-radius: 50%;
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
        background-clip: padding-box;
    }

    .btn-group .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: inherit;
        border-radius: inherit;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-5px) scale(1.1);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .btn-group .btn:hover::before {
        opacity: 0.2;
    }

    .btn-group .btn:active {
        transform: translateY(-2px) scale(1.05);
    }

    .btn-info {
        background: var(--info-gradient);
        border-color: transparent;
    }

    .btn-primary {
        background: var(--primary-gradient);
        border-color: transparent;
    }

    .btn-danger {
        background: var(--danger-gradient);
        border-color: transparent;
    }

    /* Loading Animation */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Pulse Animation for Important Elements */
    .pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    /* Price styling */
    .price-badge {
        background: var(--success-gradient);
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 50rem;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3);
    }

    /* Seats info styling */
    .seats-info {
        font-weight: 600;
    }

    .seats-available {
        color: #28a745;
    }

    .seats-limited {
        color: #ffc107;
    }

    .seats-full {
        color: #dc3545;
    }

    /* Empty state styling */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
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

        .btn-group .btn {
            width: 35px;
            height: 35px;
        }

        .event-image {
            width: 45px;
            height: 45px;
        }

        .header-actions .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    }

    /* Tooltip styling */
    .tooltip {
        font-size: 0.8rem;
    }

    .tooltip-inner {
        background: var(--primary-gradient);
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>🎉 All Events</h1>
                <p class="mb-0 opacity-75">Manage and monitor all your events</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-light btn-sm me-2 pulse" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                </button>
                <a href="add-event.php" class="btn btn-create">
                    <i class="bi bi-plus-circle me-2"></i> Create New Event
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stats Overview -->
<div class="container">
    <div class="stats-overview">
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number" data-target="<?php echo count($events); ?>">0</div>
                    <div class="stat-label">Total Events</div>
                    <i class="bi bi-calendar-event-fill text-primary" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number" data-target="<?php echo count(array_filter($events, function($e) { return ($e['total_seats'] - $e['booked_seats']) > 0; })); ?>">0</div>
                    <div class="stat-label">Available Events</div>
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number" data-target="<?php echo array_sum(array_column($events, 'total_bookings')); ?>">0</div>
                    <div class="stat-label">Total Bookings</div>
                    <i class="bi bi-ticket-perforated-fill text-warning" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number" data-target="<?php echo array_sum(array_column($events, 'booked_seats')); ?>">0</div>
                    <div class="stat-label">Seats Booked</div>
                    <i class="bi bi-people-fill text-info" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

<div class="container">
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>
                            <a href="<?php echo getSortLink('title', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Title <?php echo getSortIcon('title', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('event_date', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Date <?php echo getSortIcon('event_date', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('venue', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Venue <?php echo getSortIcon('venue', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('price', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Price <?php echo getSortIcon('price', $sort, $order); ?>
                            </a>
                        </th>
                        <th>Seats</th>
                        <th>Status</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <?php 
                        $available_seats = $event['total_seats'] - $event['booked_seats'];
                        $status_class = 'bg-success';
                        $status_text = 'Available';
                        
                        if ($available_seats <= 0) {
                            $status_class = 'bg-danger';
                            $status_text = 'Sold Out';
                        } elseif ($available_seats <= 10) {
                            $status_class = 'bg-warning';
                            $status_text = 'Limited';
                        }
                        ?>
                        <tr>
                            <td>
                                <?php if ($event['image_url']): ?>
                                    <img src="../<?php echo htmlspecialchars($event['image_url']); ?>" 
                                         alt="Event image" class="event-image">
                                <?php else: ?>
                                    <div class="event-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td>
                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                <br>
                                <small class="text-muted">
                                    <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                </small>
                            </td>
                            <td>
                                <i class="bi bi-geo-alt me-1 text-primary"></i>
                                <?php echo htmlspecialchars($event['venue']); ?>
                            </td>
                            <td>
                                <span class="price-badge">
                                    <?php echo number_format($event['price']); ?> FCFA
                                </span>
                            </td>
                            <td>
                                <div class="seats-info <?php echo $available_seats <= 0 ? 'seats-full' : ($available_seats <= 10 ? 'seats-limited' : 'seats-available'); ?>">
                                    <?php echo $available_seats; ?> / <?php echo $event['total_seats']; ?>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-ticket me-1"></i><?php echo $event['total_bookings']; ?> bookings
                                </small>
                            </td>
                            <td>
                                <span class="badge <?php echo $status_class; ?> status-badge">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="view-event.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit-event.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-primary" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?php echo $event['id']; ?>)" 
                                            class="btn btn-sm btn-danger" 
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="bi bi-calendar-x"></i>
                                <h4>No events found</h4>
                                <p>Start by creating your first event!</p>
                                <a href="add-event.php" class="btn btn-primary mt-2">
                                    <i class="bi bi-plus-circle me-2"></i>Create Event
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Enhanced JavaScript with animations and interactions

// Animated counter function
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;

    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

// Initialize animations when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Animate stat counters
    const counters = document.querySelectorAll('.stat-number[data-target]');

    // Use Intersection Observer for better performance
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    });

    counters.forEach(counter => observer.observe(counter));

    // Add staggered animation to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.1}s`;
    });

    // Add loading state to buttons
    const actionButtons = document.querySelectorAll('.btn-group .btn');
    actionButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!this.classList.contains('loading')) {
                this.classList.add('loading');
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'loading-spinner';

                // Restore original state after action
                setTimeout(() => {
                    this.classList.remove('loading');
                    icon.className = originalClass;
                }, 1000);
            }
        });
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Enhanced delete confirmation
function confirmDelete(eventId) {
    const confirmDialog = document.createElement('div');
    confirmDialog.innerHTML = `
        <div class="custom-modal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        ">
            <div class="modal-content" style="
                background: white;
                padding: 2rem;
                border-radius: 1rem;
                box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                text-align: center;
                max-width: 400px;
                animation: slideIn 0.3s ease;
            ">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🗑️</div>
                <h3 style="margin-bottom: 1rem; color: #dc3545;">Delete Event</h3>
                <p style="margin-bottom: 2rem; color: #666;">Are you sure you want to delete this event? This action cannot be undone.</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button class="btn btn-secondary" onclick="this.closest('.custom-modal').remove()">
                        Cancel
                    </button>
                    <button class="btn btn-danger" onclick="
                        this.closest('.custom-modal').remove();
                        window.location.href = 'delete-event.php?id=${eventId}';
                    ">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(confirmDialog);
}

// Refresh data function
function refreshData() {
    const refreshBtn = document.querySelector('[onclick="refreshData()"]');
    const icon = refreshBtn.querySelector('i');

    icon.style.animation = 'spin 1s linear infinite';
    refreshBtn.disabled = true;

    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Add CSS for modal animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from { transform: translateY(-50px) scale(0.9); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }
`;
document.head.appendChild(style);
</script>

<?php require_once '../includes/footer.php'; ?>