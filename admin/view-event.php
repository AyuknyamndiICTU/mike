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

    .header-actions .btn {
        padding: 0.5rem 1.25rem;
        border-radius: 50rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .header-actions .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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

    .event-container {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        padding: 2rem;
        margin-top: 2rem;
    }
    
    .event-image {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        border-radius: 0.5rem;
        margin-bottom: 2rem;
    }
    
    .event-info {
        margin-bottom: 2rem;
    }
    
    .info-label {
        font-weight: 600;
        color: #4e73df;
        margin-bottom: 0.5rem;
    }
    
    .map-container {
        height: 400px;
        border-radius: 0.5rem;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .stats-card {
        background: #f8f9fc;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: transform 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .stats-value {
        font-size: 2rem;
        font-weight: 700;
        color: #4e73df;
        margin-bottom: 0.5rem;
    }
    
    .stats-label {
        color: #858796;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
</style>

<!-- Page Header -->
<div class="page-header animate__animated animate__fadeIn">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="animate__animated animate__fadeInLeft">Event Details</h1>
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
// Initialize map
var map = L.map('map').setView([
    <?php echo $event['venue_lat'] ?: 0; ?>, 
    <?php echo $event['venue_lng'] ?: 0; ?>
], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Add marker for event location
L.marker([<?php echo $event['venue_lat']; ?>, <?php echo $event['venue_lng']; ?>])
 .addTo(map)
 .bindPopup("<?php echo htmlspecialchars($event['venue']); ?>");

// Delete confirmation
function confirmDelete(eventId) {
    if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
        window.location.href = 'delete-event.php?id=' + eventId;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 