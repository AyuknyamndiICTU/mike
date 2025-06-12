<?php
$page_title = "Analytics - Admin Dashboard";
require_once '../includes/header.php';
require_once '../config/database.php';

// Require admin access
requireAdmin();

try {
    // Get total events count
    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    $total_events = $stmt->fetchColumn();

    // Get total bookings count
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $total_bookings = $stmt->fetchColumn();

    // Get total revenue
    $stmt = $pdo->query("
        SELECT SUM(b.quantity * e.price) as total_revenue
        FROM bookings b
        JOIN events e ON b.event_id = e.id
        WHERE b.status = 'confirmed'
    ");
    $total_revenue = $stmt->fetchColumn() ?: 0;

    // Get total users count
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'");
    $total_users = $stmt->fetchColumn();

    // Get monthly bookings for the last 6 months
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(booking_date, '%Y-%m') as month,
               COUNT(*) as total_bookings,
               SUM(quantity) as tickets_sold,
               SUM(quantity * e.price) as revenue
        FROM bookings b
        JOIN events e ON b.event_id = e.id
        WHERE b.status = 'confirmed'
        AND booking_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY month
        ORDER BY month ASC
    ");
    $monthly_stats = $stmt->fetchAll();

    // Get top 5 events by bookings
    $stmt = $pdo->query("
        SELECT e.title,
               COUNT(DISTINCT b.id) as total_bookings,
               SUM(b.quantity) as tickets_sold,
               SUM(b.quantity * e.price) as revenue
        FROM events e
        LEFT JOIN bookings b ON e.id = b.event_id AND b.status = 'confirmed'
        GROUP BY e.id
        ORDER BY tickets_sold DESC
        LIMIT 5
    ");
    $top_events = $stmt->fetchAll();

    // Get category distribution
    $stmt = $pdo->query("
        SELECT category,
               COUNT(*) as total_events,
               SUM(CASE WHEN b.status = 'confirmed' THEN b.quantity ELSE 0 END) as tickets_sold
        FROM events e
        LEFT JOIN bookings b ON e.id = b.event_id
        GROUP BY category
        ORDER BY tickets_sold DESC
    ");
    $categories = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error fetching analytics: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching analytics data";
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

    /* Stats Cards */
    .stats-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
    }

    .stats-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .stats-value {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .stats-label {
        color: #858796;
        font-size: 0.875rem;
        margin: 0;
    }

    /* Chart Cards */
    .chart-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .chart-card h2 {
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
    }

    /* Table Styles */
    .table th {
        background-color: #f8f9fc;
        font-weight: 600;
    }
</style>

<!-- Page Header -->
<div class="page-header animate__animated animate__fadeIn">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="animate__animated animate__fadeInLeft">Analytics Dashboard</h1>
        </div>
    </div>
</div>

<div class="container">
    <!-- Stats Overview -->
    <div class="row">
        <div class="col-md-3">
            <div class="stats-card animate__animated animate__fadeInUp">
                <div class="stats-icon bg-primary text-white">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stats-value"><?php echo number_format($total_events); ?></div>
                <p class="stats-label">Total Events</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                <div class="stats-icon bg-success text-white">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div class="stats-value"><?php echo number_format($total_bookings); ?></div>
                <p class="stats-label">Total Bookings</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <div class="stats-icon bg-info text-white">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stats-value"><?php echo number_format($total_revenue); ?></div>
                <p class="stats-label">Total Revenue (FCFA)</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                <div class="stats-icon bg-warning text-white">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stats-value"><?php echo number_format($total_users); ?></div>
                <p class="stats-label">Total Users</p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <!-- Monthly Trends -->
        <div class="col-md-8">
            <div class="chart-card">
                <h2>Monthly Trends</h2>
                <canvas id="monthlyTrendsChart"></canvas>
            </div>
        </div>

        <!-- Category Distribution -->
        <div class="col-md-4">
            <div class="chart-card">
                <h2>Category Distribution</h2>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Events Table -->
    <div class="chart-card">
        <h2>Top Performing Events</h2>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Bookings</th>
                        <th>Tickets Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td><?php echo number_format($event['total_bookings']); ?></td>
                            <td><?php echo number_format($event['tickets_sold']); ?></td>
                            <td><?php echo number_format($event['revenue']); ?> FCFA</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Monthly Trends Chart
const monthlyData = <?php echo json_encode($monthly_stats); ?>;
const months = monthlyData.map(item => item.month);
const bookings = monthlyData.map(item => item.total_bookings);
const revenue = monthlyData.map(item => item.revenue);

new Chart(document.getElementById('monthlyTrendsChart'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Bookings',
            data: bookings,
            borderColor: '#4e73df',
            tension: 0.1,
            yAxisID: 'y'
        }, {
            label: 'Revenue (FCFA)',
            data: revenue,
            borderColor: '#1cc88a',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

// Category Distribution Chart
const categoryData = <?php echo json_encode($categories); ?>;
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: categoryData.map(item => item.category),
        datasets: [{
            data: categoryData.map(item => item.tickets_sold),
            backgroundColor: [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 