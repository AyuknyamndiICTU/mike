<?php
$page_title = "Analytics & Reports - Admin Dashboard";
require_once '../includes/header.php';
require_once '../config/database.php';

// Require admin access
requireAdmin();

// Initialize variables with default values
$totalUsers = 0;
$totalEvents = 0;
$totalBookings = 0;
$bookingsByStatus = [];
$bookingsByMonth = [];
$popularEvents = [];
$error_details = '';

try {
    // Test database connection first
    if (!$pdo) {
        throw new PDOException("Database connection not established");
    }

    // Debug: Check if tables exist
    $tables_query = "SHOW TABLES LIKE '%'";
    $tables_result = $pdo->query($tables_query);
    $tables = $tables_result->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('users', $tables)) {
        throw new Exception("Users table does not exist");
    }
    if (!in_array('events', $tables)) {
        throw new Exception("Events table does not exist");
    }
    if (!in_array('bookings', $tables)) {
        throw new Exception("Bookings table does not exist");
    }

    // Get total number of users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Get total number of events
    $stmt = $pdo->query("SELECT COUNT(*) as total_events FROM events");
    $totalEvents = $stmt->fetch(PDO::FETCH_ASSOC)['total_events'];

    // Get total number of bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total_bookings FROM bookings");
    $totalBookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];

    // Get bookings by status - enhanced query with quantity totals
    $stmt = $pdo->query("
        SELECT 
            status,
            COUNT(*) as count,
            SUM(quantity) as total_tickets,
            SUM(total_amount) as total_revenue
        FROM bookings 
        GROUP BY status
        ORDER BY count DESC
    ");
    $bookingsByStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get bookings by month - using booking_date instead of date
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(booking_date, '%Y-%m') as month,
               COUNT(*) as count
        FROM bookings 
        GROUP BY month
        ORDER BY month DESC
        LIMIT 6
    ");
    $bookingsByMonth = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get most popular events - corrected join between events and bookings
    $stmt = $pdo->query("
        SELECT e.title, COUNT(b.id) as booking_count,
               SUM(b.quantity) as total_tickets
        FROM events e
        LEFT JOIN bookings b ON e.id = b.event_id
        WHERE b.status = 'confirmed'
        GROUP BY e.id, e.title
        ORDER BY total_tickets DESC, booking_count DESC
        LIMIT 5
    ");
    $popularEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error in reports.php: " . $e->getMessage());
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $error_details = "PDO Error: " . $e->getMessage();
    
    // Add table structure information to error details
    try {
        $error_details .= "\n\nTable Structures:\n";
        $tables = ['bookings', 'events'];
        foreach ($tables as $table) {
            $cols = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
            $error_details .= "\n$table table columns:\n";
            foreach ($cols as $col) {
                $error_details .= "{$col['Field']}, ";
            }
        }
    } catch (Exception $e2) {
        $error_details .= "\nCould not fetch table structure: " . $e2->getMessage();
    }
} catch (Exception $e) {
    error_log("General error in reports.php: " . $e->getMessage());
    $_SESSION['error'] = "Error: " . $e->getMessage();
    $error_details = "General Error: " . $e->getMessage();
}
?>

<!-- Add debug information at the top if there's an error -->
<?php if (!empty($error_details)): ?>
    <div class="container mt-3">
        <div class="alert alert-danger">
            <h5>Error Details:</h5>
            <pre><?php echo htmlspecialchars($error_details); ?></pre>
            <hr>
            <h5>Database Configuration:</h5>
            <ul>
                <li>Host: <?php echo htmlspecialchars(DB_HOST); ?></li>
                <li>Port: <?php echo htmlspecialchars(DB_PORT); ?></li>
                <li>Database: <?php echo htmlspecialchars(DB_NAME); ?></li>
                <li>User: <?php echo htmlspecialchars(DB_USER); ?></li>
            </ul>
            <hr>
            <h5>Available Tables:</h5>
            <ul>
                <?php 
                if (isset($tables) && is_array($tables)) {
                    foreach ($tables as $table) {
                        echo "<li>" . htmlspecialchars($table) . "</li>";
                    }
                } else {
                    echo "<li>Could not retrieve table list</li>";
                }
                ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<!-- Include required CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, var(--primary-color), #224abe);
        --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        --hover-transform: translateY(-5px);
    }

    .stats-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--primary-gradient);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 1;
    }

    .stats-card:hover {
        transform: var(--hover-transform);
    }

    .stats-card:hover::before {
        opacity: 0.1;
    }

    .stats-card .content {
        position: relative;
        z-index: 2;
    }

    .stats-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        transition: transform 0.3s ease;
    }

    .stats-card:hover .stats-icon {
        transform: scale(1.1);
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stats-label {
        color: #666;
        font-size: 1rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .chart-container {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }

    .chart-container:hover {
        transform: var(--hover-transform);
    }

    .chart-container h4 {
        color: #333;
        font-weight: 600;
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.5rem;
    }

    .chart-container h4::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--primary-gradient);
        border-radius: 3px;
    }

    .page-header {
        background: var(--primary-gradient);
        padding: 3rem 0;
        margin-bottom: 2rem;
        color: white;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('data:image/svg+xml,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" stroke="white" stroke-width="2" fill="none" opacity="0.1"/></svg>') repeat;
        animation: moveBackground 20s linear infinite;
    }

    @keyframes moveBackground {
        from { background-position: 0 0; }
        to { background-position: 100px 100px; }
    }

    .no-data {
        text-align: center;
        padding: 3rem;
        color: #666;
        position: relative;
        overflow: hidden;
    }

    .no-data i {
        font-size: 4rem;
        margin-bottom: 1rem;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: pulse 2s infinite;
    }

    .no-data .message {
        font-size: 1.2rem;
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .no-data .sub-message {
        color: #888;
        font-size: 0.9rem;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: #f8f9fc;
        border-bottom: 2px solid #e3e6f0;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fc;
        transform: scale(1.01);
    }

    .loading-animation {
        display: inline-block;
        position: relative;
        width: 80px;
        height: 80px;
    }

    .loading-animation div {
        position: absolute;
        border: 4px solid var(--primary-color);
        opacity: 1;
        border-radius: 50%;
        animation: loading-animation 1s cubic-bezier(0, 0.2, 0.8, 1) infinite;
    }

    @keyframes loading-animation {
        0% {
            top: 36px;
            left: 36px;
            width: 0;
            height: 0;
            opacity: 1;
        }
        100% {
            top: 0px;
            left: 0px;
            width: 72px;
            height: 72px;
            opacity: 0;
        }
    }

    .stats-number {
        position: relative;
        display: inline-block;
    }

    .stats-number.animate {
        animation: countUp 2s ease-out forwards;
    }

    @keyframes countUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<!-- Include required JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Page Header -->
<div class="page-header animate__animated animate__fadeIn">
    <div class="container">
        <h1 class="mb-0 animate__animated animate__slideInLeft">Analytics & Reports</h1>
    </div>
</div>

<div class="container">
    <!-- Error/Success Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger animate__animated animate__fadeIn">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="stats-card text-center">
                <div class="content">
                    <div class="stats-icon text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stats-number animate"><?php echo number_format($totalUsers); ?></div>
                    <div class="stats-label">Total Users</div>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="stats-card text-center">
                <div class="content">
                    <div class="stats-icon text-success">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="stats-number animate"><?php echo number_format($totalEvents); ?></div>
                    <div class="stats-label">Total Events</div>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="stats-card text-center">
                <div class="content">
                    <div class="stats-icon text-info">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>
                    <div class="stats-number animate"><?php echo number_format($totalBookings); ?></div>
                    <div class="stats-label">Total Bookings</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <!-- Bookings by Status -->
        <div class="col-md-6" data-aos="fade-right">
            <div class="chart-container">
                <h4>Bookings by Status</h4>
                <?php if (!empty($bookingsByStatus)): ?>
                    <canvas id="bookingsByStatusChart"></canvas>
                <?php else: ?>
                    <div class="no-data">
                        <i class="bi bi-pie-chart"></i>
                        <div class="message">No Booking Status Data Yet</div>
                        <div class="sub-message">Start creating events and getting bookings!</div>
                        <div class="loading-animation">
                            <div></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bookings by Month -->
        <div class="col-md-6" data-aos="fade-left">
            <div class="chart-container">
                <h4>Bookings Trend (Last 6 Months)</h4>
                <?php if (!empty($bookingsByMonth)): ?>
                    <canvas id="bookingsTrendChart"></canvas>
                <?php else: ?>
                    <div class="no-data">
                        <i class="bi bi-graph-up"></i>
                        <div class="message">No Booking Trends Yet</div>
                        <div class="sub-message">Your booking trends will appear here</div>
                        <div class="loading-animation">
                            <div></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Popular Events Table -->
    <div class="chart-container" data-aos="fade-up">
        <h4>Most Popular Events</h4>
        <div class="table-responsive">
            <?php if (!empty($popularEvents)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Total Bookings</th>
                            <th>Total Tickets</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popularEvents as $event): ?>
                            <tr class="animate__animated animate__fadeIn">
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo number_format($event['booking_count']); ?></td>
                                <td><?php echo number_format($event['total_tickets']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="bi bi-calendar-x"></i>
                    <div class="message">No Popular Events Yet</div>
                    <div class="sub-message">Create events and watch them become popular!</div>
                    <div class="loading-animation">
                        <div></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Initialize AOS
AOS.init({
    duration: 800,
    once: true
});

// Add number animation
document.addEventListener('DOMContentLoaded', function() {
    const numbers = document.querySelectorAll('.stats-number');
    numbers.forEach(number => {
        number.classList.add('animate');
    });
});

// Only initialize charts if data is available
<?php if (!empty($bookingsByStatus)): ?>
// Bookings by Status Chart with animation
const statusData = <?php echo json_encode($bookingsByStatus); ?>;
new Chart(document.getElementById('bookingsByStatusChart'), {
    type: 'doughnut',
    data: {
        labels: statusData.map(item => item.status.toUpperCase()),
        datasets: [{
            data: statusData.map(item => item.total_tickets),
            backgroundColor: [
                '#1cc88a', // confirmed
                '#4e73df', // pending
                '#e74a3b', // cancelled
                '#f6c23e'  // other statuses
            ],
            borderWidth: 2,
            borderColor: 'white'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const item = statusData[context.dataIndex];
                        return [
                            `Status: ${item.status.toUpperCase()}`,
                            `Tickets: ${item.total_tickets}`,
                            `Bookings: ${item.count}`,
                            `Revenue: $${parseFloat(item.total_revenue).toFixed(2)}`
                        ];
                    }
                }
            }
        },
        animation: {
            animateScale: true,
            animateRotate: true
        }
    }
});
<?php endif; ?>

<?php if (!empty($bookingsByMonth)): ?>
// Bookings Trend Chart with animation
const monthlyData = <?php echo json_encode($bookingsByMonth); ?>;
new Chart(document.getElementById('bookingsTrendChart'), {
    type: 'line',
    data: {
        labels: monthlyData.map(item => item.month),
        datasets: [{
            label: 'Number of Bookings',
            data: monthlyData.map(item => item.count),
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    drawBorder: false
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        animation: {
            duration: 2000,
            easing: 'easeInOutQuart'
        }
    }
});
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?> 