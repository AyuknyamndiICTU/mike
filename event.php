<?php
$page_title = "Event Details - Event Booking System";
require_once 'includes/header.php';
require_once 'config/database.php';

// Get event ID from URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get event details
$sql = "SELECT e.*, GROUP_CONCAT(
            CONCAT(tt.type_name, ':', tt.price, ':', tt.available_seats, ':', COALESCE(tt.description, ''))
            SEPARATOR '||'
        ) as ticket_types 
    FROM events e 
    LEFT JOIN ticket_types tt ON e.id = tt.event_id 
    WHERE e.id = ? AND e.status = 'active'
    GROUP BY e.id";
$stmt = $pdo->prepare($sql);
$stmt->execute([$event_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-danger">Event not found or no longer available.</div>';
    echo '<a href="events.php" class="btn btn-primary">Back to Events</a>';
    echo '</div>';
    require_once 'includes/footer.php';
    exit();
}

$event = $result;
$page_title = htmlspecialchars($event['title']) . " - Event Booking System";
?>

<!-- Add SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Page Header Styles */
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
        background: linear-gradient(135deg, var(--success-color) 0%, var(--accent-color) 50%, white 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        background-size: 200% 100%;
        animation: gradientShift 4s ease-in-out infinite;
        font-weight: 700;
        position: relative;
        display: inline-block;
        overflow: hidden;
        margin: 0;
        font-size: 1.75rem;
    }

    .page-header h1::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.15), transparent);
        transform: rotate(45deg);
        animation: headerShine 4s infinite;
        pointer-events: none;
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

    .breadcrumb-custom {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50rem;
        padding: 0.5rem 1rem;
        margin-top: 1rem;
    }

    .breadcrumb-custom .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }

    .breadcrumb-custom .breadcrumb-item a:hover {
        color: white;
    }

    .breadcrumb-custom .breadcrumb-item.active {
        color: white;
    }

    .breadcrumb-custom .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.6);
    }
</style>

<!-- Page Header -->
<div class="page-header animate__animated animate__fadeIn">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="animate__animated animate__fadeInLeft">Event Details</h1>
                <nav aria-label="breadcrumb" class="breadcrumb-custom animate__animated animate__fadeInLeft" style="animation-delay: 0.2s">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="events.php">Events</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($event['title']); ?></li>
                    </ol>
                </nav>
            </div>
            <div class="header-actions animate__animated animate__fadeInRight">
                <a href="events.php" class="btn btn-back">
                    <i class="bi bi-arrow-left"></i> Back to Events
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">

    <div class="row">
        <!-- Event Image and Basic Info -->
        <div class="col-md-8">
            <img src="<?php echo htmlspecialchars($event['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($event['title']); ?>"
                 class="img-fluid rounded event-image animate__animated animate__fadeIn">

            <div class="event-details p-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <h1><?php echo htmlspecialchars($event['title']); ?></h1>
                
                <div class="d-flex flex-wrap gap-4 text-muted mb-4">
                    <div>
                        <i class="bi bi-calendar"></i>
                        <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                    </div>
                    <div>
                        <i class="bi bi-clock"></i>
                        <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                    </div>
                    <div>
                        <i class="bi bi-geo-alt"></i>
                        <?php echo htmlspecialchars($event['venue']); ?>
                    </div>
                    <div>
                        <i class="bi bi-tag"></i>
                        <?php echo htmlspecialchars($event['category']); ?>
                    </div>
                </div>

                <h4>About This Event</h4>
                <p class="lead"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>

                <div class="organizer-info card mb-4 animate__animated animate__fadeInLeft" style="animation-delay: 0.4s">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge me-2"></i>Event Organizer
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="organizer-detail-item">
                                    <div class="detail-icon">
                                        <i class="bi bi-person-circle"></i>
                                    </div>
                                    <div class="detail-content">
                                        <label class="detail-label">Organizer Name</label>
                                        <div class="detail-value"><?php echo htmlspecialchars($event['organizer_name']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="organizer-detail-item">
                                    <div class="detail-icon">
                                        <i class="bi bi-telephone-fill"></i>
                                    </div>
                                    <div class="detail-content">
                                        <label class="detail-label">Contact Information</label>
                                        <div class="detail-value"><?php echo htmlspecialchars($event['organizer_contact']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($event['organizer_address'])): ?>
                            <div class="col-12">
                                <div class="organizer-detail-item">
                                    <div class="detail-icon">
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </div>
                                    <div class="detail-content">
                                        <label class="detail-label">Organizer Address</label>
                                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($event['organizer_address'])); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Venue Information -->
                <div class="venue-info card mb-4 animate__animated animate__fadeInRight" style="animation-delay: 0.6s">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-geo-alt me-2"></i>Venue Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="venue-detail-item">
                                    <div class="detail-icon bg-success">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="detail-content">
                                        <label class="detail-label">Venue Name</label>
                                        <div class="detail-value"><?php echo htmlspecialchars($event['venue']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($event['venue_address'])): ?>
                            <div class="col-md-6">
                                <div class="venue-detail-item">
                                    <div class="detail-icon bg-success">
                                        <i class="bi bi-pin-map-fill"></i>
                                    </div>
                                    <div class="detail-content">
                                        <label class="detail-label">Venue Address</label>
                                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($event['venue_address'])); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="col-12">
                                <div class="venue-detail-item">
                                    <div class="detail-icon bg-success">
                                        <i class="bi bi-map"></i>
                                    </div>
                                    <div class="detail-content">
                                        <label class="detail-label">Location on Map</label>
                                        <div class="map-container">
                                            <div id="map"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Section -->
        <div class="col-md-4">
            <div class="card sticky-top animate__animated animate__fadeInUp" style="top: 20px; animation-delay: 0.8s">
                <div class="card-body">
                    <h3 class="card-title mb-4">Book Tickets</h3>
                    
                    <?php if ($event['ticket_types']): ?>
                        <?php
                        $ticket_types = array_map(function($type) {
                            list($name, $price, $seats, $desc) = explode(':', $type);
                            return [
                                'name' => $name,
                                'price' => $price,
                                'available_seats' => $seats,
                                'description' => $desc
                            ];
                        }, explode('||', $event['ticket_types']));
                        ?>
                        
                        <?php foreach ($ticket_types as $type): ?>
                            <div class="ticket-type-card mb-3">
                                <h5 class="ticket-type-name"><?php echo htmlspecialchars($type['name']); ?></h5>
                                <div class="ticket-price mb-2">
                                    <h4 class="text-primary"><?php echo number_format($type['price'], 0); ?> FCFA</h4>
                                </div>
                                <?php if ($type['description']): ?>
                                    <p class="ticket-description text-muted small mb-2">
                                        <?php echo htmlspecialchars($type['description']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($type['available_seats'] > 0): ?>
                                    <p class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <?php echo $type['available_seats']; ?> tickets available
                                    </p>
                                    
                                    <?php if (isLoggedIn()): ?>
                                        <form class="booking-form" action="api/add-to-cart.php" method="POST">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <input type="hidden" name="ticket_type" value="<?php echo htmlspecialchars($type['name']); ?>">
                                            
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <label class="form-label mb-0">Quantity:</label>
                                                <input type="number" class="form-control form-control-sm quantity-input" 
                                                       name="quantity" min="1" max="<?php echo $type['available_seats']; ?>" 
                                                       value="1" style="width: 80px;">
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                Add to Cart
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-danger py-2">Sold Out!</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!isLoggedIn()): ?>
                            <div class="alert alert-info">
                                Please <a href="login.php">login</a> to book tickets.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            No ticket types available for this event.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    const lat = <?php echo $event['venue_lat'] ?? 0; ?>;
    const lng = <?php echo $event['venue_lng'] ?? 0; ?>;
    const venue = <?php echo json_encode($event['venue']); ?>;
    initMap(lat, lng, venue);
});

document.querySelectorAll('.booking-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const button = this.querySelector('button[type="submit"]');
        const originalText = button.innerHTML;
        
        try {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            
            const formData = new FormData(this);
            const response = await fetch('api/add-to-cart.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const result = await response.json();
            
            if (result.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Tickets added to cart successfully',
                    icon: 'success',
                    confirmButtonText: 'View Cart',
                    showCancelButton: true,
                    cancelButtonText: 'Continue Shopping'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'cart.php';
                    }
                });

                if (result.cartCount) {
                    const cartCounter = document.querySelector('.cart-count');
                    if (cartCounter) {
                        cartCounter.textContent = result.cartCount;
                    }
                }
            } else {
                throw new Error(result.message || 'Failed to add to cart');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to add tickets to cart. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    });
});
</script>

<style>
.event-details h1 {
    background: linear-gradient(135deg, var(--success-color) 0%, var(--accent-color) 50%, var(--primary-color) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    background-size: 200% 100%;
    animation: gradientShift 4s ease-in-out infinite;
    position: relative;
    display: inline-block;
    overflow: hidden;
    font-weight: 700;
}

.event-details h1::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.15), transparent);
    transform: rotate(45deg);
    animation: headerShine 4s infinite;
    pointer-events: none;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

@keyframes headerShine {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(100%) rotate(45deg); }
}

.organizer-info {
    background: #f8f9fa;
    border: none;
    border-radius: 10px;
}

.organizer-info .card-title {
    color: #333;
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
}

.organizer-info {
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 15px;
    overflow: hidden;
}

.organizer-info .card-header {
    background: linear-gradient(135deg, var(--primary-color), #224abe) !important;
    border: none;
    padding: 1.2rem 1.5rem;
}

.organizer-info .card-header h5 {
    font-weight: 600;
    margin: 0;
    font-size: 1.1rem;
}

.organizer-info .card-body {
    padding: 1.5rem;
}

.organizer-detail-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fc;
    border-radius: 10px;
    border-left: 4px solid var(--primary-color);
    transition: all 0.3s ease;
}

.organizer-detail-item:hover {
    background: #e3f2fd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.detail-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
}

.detail-content {
    flex: 1;
}

.detail-label {
    display: block;
    color: #666;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.3rem;
}

.detail-value {
    color: #333;
    font-weight: 500;
    font-size: 1rem;
    line-height: 1.4;
}

/* Venue Information Styling */
.venue-info {
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 15px;
    overflow: hidden;
}

.venue-info .card-header {
    background: linear-gradient(135deg, var(--success-color), #17a673) !important;
    border: none;
    padding: 1.2rem 1.5rem;
}

.venue-info .card-header h5 {
    font-weight: 600;
    margin: 0;
    font-size: 1.1rem;
}

.venue-info .card-body {
    padding: 1.5rem;
}

.venue-detail-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fc;
    border-radius: 10px;
    border-left: 4px solid var(--success-color);
    transition: all 0.3s ease;
}

.venue-detail-item:hover {
    background: #e8f5e8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.detail-icon.bg-success {
    background: var(--success-color) !important;
}

.map-container {
    margin-top: 0.5rem;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

#map {
    height: 300px;
    width: 100%;
    border-radius: 10px;
}

/* Responsive improvements for organizer and venue sections */
@media (max-width: 768px) {
    .organizer-detail-item,
    .venue-detail-item {
        padding: 0.8rem;
        gap: 0.8rem;
    }

    .detail-icon {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }

    .detail-label {
        font-size: 0.8rem;
    }

    .detail-value {
        font-size: 0.9rem;
    }

    #map {
        height: 250px;
    }
}

.ticket-type-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.ticket-type-name {
    color: #333;
    margin-bottom: 10px;
}

.ticket-price {
    color: var(--primary-color);
}

.ticket-description {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 15px;
}

.quantity-input {
    width: 80px !important;
    text-align: center;
}

.booking-form {
    margin-top: 15px;
}
</style>

<!-- Leaflet CSS and JS for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
// Initialize map when page loads
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($event['venue_lat']) && !empty($event['venue_lng'])): ?>
    // Initialize map with event coordinates
    var map = L.map('map').setView([<?php echo $event['venue_lat']; ?>, <?php echo $event['venue_lng']; ?>], 15);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add marker for venue
    var marker = L.marker([<?php echo $event['venue_lat']; ?>, <?php echo $event['venue_lng']; ?>])
        .addTo(map)
        .bindPopup('<strong><?php echo htmlspecialchars($event['venue']); ?></strong><br><?php echo htmlspecialchars($event['venue_address'] ?? ''); ?>')
        .openPopup();
    <?php else: ?>
    // If no coordinates, show a default map
    var map = L.map('map').setView([3.8480, 11.5021], 6); // Cameroon center

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add a note about missing coordinates
    document.getElementById('map').innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #f8f9fc; color: #666; font-size: 14px;"><i class="bi bi-geo-alt me-2"></i>Map coordinates not available for this venue</div>';
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>