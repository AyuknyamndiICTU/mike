<?php
$page_title = "View Event - Admin Dashboard";
require_once '../includes/header.php';
require_once '../config/database.php';

// Require admin access
requireAdmin();

// Get event ID from URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch event details with booking count
try {
    $stmt = $pdo->prepare("
        SELECT e.*, 
               COUNT(b.id) as total_bookings,
               SUM(b.quantity) as booked_seats
        FROM events e
        LEFT JOIN bookings b ON e.id = b.event_id
        WHERE e.id = ?
        GROUP BY e.id
    ");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        $_SESSION['error'] = "Event not found";
        header("Location: dashboard.php");
        exit();
    }

    // Calculate available seats
    $available_seats = $event['total_seats'] - ($event['booked_seats'] ?? 0);
} catch (PDOException $e) {
    error_log("Error fetching event: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching event details";
    header("Location: dashboard.php");
    exit();
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
        color: white;
        text-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        animation: slideInFromLeft 1s ease-out;
    }

    .page-header p {
        color: rgba(255, 255, 255, 0.9);
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
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

    .header-actions .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 50rem;
        font-weight: 600;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 2px solid transparent;
        background-clip: padding-box;
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

    .btn-edit {
        background: white;
        color: var(--primary-color);
        border: none;
    }

    .btn-edit:hover {
        background: #f8f9fc;
        color: #224abe;
    }

    .btn-back {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-color: rgba(255, 255, 255, 0.3);
    }

    /* Enhanced Event Container */
    .event-container {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 1.5rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
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

    .event-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .event-container:hover::before {
        left: 100%;
    }

    .event-image {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        border-radius: 1rem;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .event-image:hover {
        transform: scale(1.02);
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    }

    .event-info {
        margin-bottom: 2rem;
        position: relative;
        z-index: 2;
    }

    .event-info h2 {
        color: #2c3e50;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .event-info p {
        color: #34495e;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        line-height: 1.6;
    }

    .text-muted {
        color: #5a6c7d !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .info-label {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border-radius: 20px;
    }

    .map-container {
        height: 400px;
        border-radius: 1rem;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-light);
        transition: all 0.3s ease;
    }

    .map-container:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-heavy);
    }

    /* Enhanced Stats Cards */
    .stats-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        animation: fadeInUp 0.8s ease-out;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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

    .stats-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        background: rgba(255, 255, 255, 0.98);
    }

    .stats-value {
        font-size: 2rem;
        font-weight: 700;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
        animation: countUp 2s ease-out;
    }

    @keyframes countUp {
        from { opacity: 0; transform: scale(0.5); }
        to { opacity: 1; transform: scale(1); }
    }

    .stats-label {
        color: #2c3e50;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced Delete Button */
    .btn-danger {
        background: var(--danger-gradient);
        border: none;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .btn-danger::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-danger:hover::before {
        left: 100%;
    }

    .btn-danger:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(250, 112, 154, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 2rem;
        }

        .event-container {
            padding: 1rem;
            border-radius: 1rem;
        }

        .stats-card {
            margin-bottom: 1rem;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header animate__animated animate__fadeIn">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="animate__animated animate__fadeInLeft">🎪 Event Details</h1>
                <p class="mb-0 opacity-75">Comprehensive event information and statistics</p>
            </div>
            <div class="header-actions animate__animated animate__fadeInRight">
                <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn btn-edit me-2">
                    <i class="bi bi-pencil"></i> Edit Event
                </a>
                <a href="dashboard.php" class="btn btn-back">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="event-container">
        <div class="row">
            <div class="col-lg-8">
                <?php if ($event['image_url']): ?>
                    <img src="../<?php echo htmlspecialchars($event['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($event['title']); ?>" 
                         class="event-image">
                <?php endif; ?>

                <div class="event-info">
                    <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                    <p class="text-muted">
                        <i class="bi bi-calendar-event me-2"></i>
                        <?php echo date('F d, Y', strtotime($event['event_date'])); ?> at 
                        <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                    </p>
                    
                    <div class="info-label">Description</div>
                    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    
                    <div class="info-label">Venue</div>
                    <p>
                        <i class="bi bi-geo-alt me-2"></i>
                        <?php echo htmlspecialchars($event['venue']); ?>
                    </p>
                    
                    <div class="info-label">Category</div>
                    <p>
                        <span class="badge bg-primary">
                            <?php echo ucfirst(htmlspecialchars($event['category'])); ?>
                        </span>
                    </p>
                </div>

                <div class="map-container">
                    <div id="map"></div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="stats-card">
                    <div class="stats-value"><?php echo number_format($event['price']); ?> FCFA</div>
                    <div class="stats-label">Ticket Price</div>
                </div>

                <div class="stats-card">
                    <div class="stats-value"><?php echo $event['total_seats']; ?></div>
                    <div class="stats-label">Total Seats</div>
                </div>

                <div class="stats-card">
                    <div class="stats-value"><?php echo $available_seats; ?></div>
                    <div class="stats-label">Available Seats</div>
                </div>

                <div class="stats-card">
                    <div class="stats-value"><?php echo $event['total_bookings']; ?></div>
                    <div class="stats-label">Total Bookings</div>
                </div>

                <div class="mt-4">
                    <button type="button" 
                            class="btn btn-danger btn-block w-100"
                            onclick="confirmDelete(<?php echo $event['id']; ?>)">
                        <i class="bi bi-trash"></i> Delete Event
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
// Initialize map with enhanced styling
var map = L.map('map', {
    zoomControl: true,
    scrollWheelZoom: true,
    doubleClickZoom: true,
    boxZoom: true,
    keyboard: true,
    dragging: true,
    touchZoom: true
}).setView([
    <?php echo $event['venue_lat'] ?: 0; ?>,
    <?php echo $event['venue_lng'] ?: 0; ?>
], 13);

// Add custom tile layer with better styling
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
    tileSize: 256,
    zoomOffset: 0
}).addTo(map);

// Create custom marker icon
var customIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;"><i class="bi bi-geo-alt-fill" style="color: white; font-size: 16px;"></i></div>',
    iconSize: [30, 30],
    iconAnchor: [15, 15]
});

// Add enhanced marker for event location
L.marker([<?php echo $event['venue_lat']; ?>, <?php echo $event['venue_lng']; ?>], {
    icon: customIcon
})
.addTo(map)
.bindPopup(`
    <div style="text-align: center; padding: 10px;">
        <h6 style="margin: 0 0 5px 0; color: #333;"><?php echo htmlspecialchars($event['venue']); ?></h6>
        <p style="margin: 0; color: #666; font-size: 12px;">📍 Event Location</p>
    </div>
`, {
    closeButton: false,
    offset: [0, -10]
});

// Enhanced delete confirmation with SweetAlert2
function confirmDelete(eventId) {
    Swal.fire({
        title: '🗑️ Delete Event?',
        text: "This action cannot be undone! All bookings will also be deleted.",
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
                text: 'Please wait while we delete the event',
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
    // Animate stats cards with staggered timing
    document.querySelectorAll('.stats-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.2}s`;
    });

    // Add hover effects to buttons
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });

        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Remove parallax effect - keep image static
    // const eventImage = document.querySelector('.event-image');
    // Parallax effect removed to prevent image movement during scroll
});

// Add SweetAlert2 CDN if not already included
if (typeof Swal === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    document.head.appendChild(script);
}
</script>

<?php require_once '../includes/footer.php'; ?> 