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
            <!-- Enhanced Event Image -->
            <div class="event-image-container mb-4 animate__animated animate__fadeIn">
                <img src="<?php echo htmlspecialchars($event['image_url']); ?>"
                     alt="<?php echo htmlspecialchars($event['title']); ?>"
                     class="event-main-image">
                <div class="image-overlay">
                    <div class="event-category-badge">
                        <i class="bi bi-tag-fill"></i>
                        <?php echo htmlspecialchars($event['category']); ?>
                    </div>
                </div>
            </div>

            <!-- Enhanced Event Details -->
            <div class="event-details-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <div class="event-header">
                    <h1 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h1>

                    <div class="event-meta-info">
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div class="meta-content">
                                <span class="meta-label">Date</span>
                                <span class="meta-value"><?php echo date('F d, Y', strtotime($event['event_date'])); ?></span>
                            </div>
                        </div>

                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="meta-content">
                                <span class="meta-label">Time</span>
                                <span class="meta-value"><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                            </div>
                        </div>

                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div class="meta-content">
                                <span class="meta-label">Venue</span>
                                <span class="meta-value"><?php echo htmlspecialchars($event['venue']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="event-description">
                    <h4 class="section-title">About This Event</h4>
                    <p class="description-text"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>
            </div>

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

        <!-- Enhanced Booking Section -->
        <div class="col-md-4">
            <div class="booking-card sticky-top animate__animated animate__fadeInUp" style="top: 20px; animation-delay: 0.8s">
                <div class="booking-header">
                    <h3 class="booking-title">
                        <i class="bi bi-ticket-perforated me-2"></i>
                        Book Tickets
                    </h3>
                </div>
                <div class="booking-body">
                    
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
                            <div class="enhanced-ticket-card">
                                <div class="ticket-header">
                                    <h5 class="ticket-name"><?php echo htmlspecialchars($type['name']); ?></h5>
                                    <div class="ticket-price">
                                        <span class="price-amount"><?php echo number_format($type['price'], 0); ?></span>
                                        <span class="price-currency">FCFA</span>
                                    </div>
                                </div>

                                <?php if ($type['description']): ?>
                                    <div class="ticket-description">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <?php echo htmlspecialchars($type['description']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="ticket-availability">
                                    <?php if ($type['available_seats'] > 0): ?>
                                        <div class="availability-status available">
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span><?php echo $type['available_seats']; ?> tickets available</span>
                                        </div>

                                        <?php if (isLoggedIn()): ?>
                                            <form class="enhanced-booking-form" action="api/add-to-cart.php" method="POST">
                                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                <input type="hidden" name="ticket_type" value="<?php echo htmlspecialchars($type['name']); ?>">

                                                <div class="quantity-selector">
                                                    <label class="quantity-label">Quantity:</label>
                                                    <div class="quantity-controls">
                                                        <button type="button" class="quantity-btn minus" onclick="decreaseQuantity(this)">
                                                            <i class="bi bi-dash"></i>
                                                        </button>
                                                        <input type="number" class="quantity-input"
                                                               name="quantity" min="1" max="<?php echo $type['available_seats']; ?>"
                                                               value="1" readonly>
                                                        <button type="button" class="quantity-btn plus" onclick="increaseQuantity(this)">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <button type="submit" class="add-to-cart-btn">
                                                    <i class="bi bi-cart-plus me-2"></i>
                                                    Add to Cart
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="availability-status sold-out">
                                            <i class="bi bi-x-circle-fill"></i>
                                            <span>Sold Out!</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!isLoggedIn()): ?>
                            <div class="login-prompt">
                                <div class="login-icon">
                                    <i class="bi bi-person-lock"></i>
                                </div>
                                <div class="login-text">
                                    <h6>Login Required</h6>
                                    <p>Please <a href="login.php" class="login-link">login</a> to book tickets.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-tickets-message">
                            <div class="no-tickets-icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="no-tickets-text">
                                <h6>No Tickets Available</h6>
                                <p>No ticket types available for this event.</p>
                            </div>
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

// Quantity control functions
function increaseQuantity(button) {
    const input = button.parentElement.querySelector('.quantity-input');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQuantity(button) {
    const input = button.parentElement.querySelector('.quantity-input');
    const min = parseInt(input.getAttribute('min'));
    const current = parseInt(input.value);
    if (current > min) {
        input.value = current - 1;
    }
}

document.querySelectorAll('.enhanced-booking-form, .booking-form').forEach(form => {
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
/* Enhanced Event Image Styling */
.event-image-container {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    background: linear-gradient(145deg, #f8f9fc, #ffffff);
    padding: 8px;
}

.event-main-image {
    width: 100%;
    height: 450px;
    object-fit: cover;
    border-radius: 15px;
    transition: transform 0.3s ease;
}

.event-main-image:hover {
    transform: scale(1.02);
}

.image-overlay {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 2;
}

.event-category-badge {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
    padding: 8px 16px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.9rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Enhanced Event Details Card */
.event-details-card {
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.event-details-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--success-color));
}

.event-title {
    background: linear-gradient(135deg, var(--success-color) 0%, var(--accent-color) 50%, var(--primary-color) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    background-size: 200% 100%;
    animation: gradientShift 4s ease-in-out infinite;
    font-weight: 700;
    font-size: 2.5rem;
    margin-bottom: 25px;
    line-height: 1.2;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* Enhanced Meta Information */
.event-meta-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, rgba(var(--primary-color-rgb), 0.05), rgba(var(--accent-color-rgb), 0.05));
    border-radius: 15px;
    border: 1px solid rgba(var(--primary-color-rgb), 0.1);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border-left: 4px solid var(--primary-color);
}

.meta-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.meta-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(var(--primary-color-rgb), 0.3);
}

.meta-content {
    flex: 1;
}

.meta-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.meta-value {
    display: block;
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    line-height: 1.3;
}

/* Enhanced Description Section */
.event-description {
    margin-top: 30px;
}

.section-title {
    color: #333;
    font-weight: 700;
    font-size: 1.5rem;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    border-radius: 2px;
}

.description-text {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #555;
    background: rgba(var(--primary-color-rgb), 0.02);
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid var(--accent-color);
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

/* Enhanced Responsive Design */
@media (max-width: 768px) {
    .event-main-image {
        height: 300px;
    }

    .event-title {
        font-size: 2rem;
    }

    .event-meta-info {
        grid-template-columns: 1fr;
        gap: 15px;
        padding: 15px;
    }

    .meta-item {
        padding: 12px;
        gap: 12px;
    }

    .meta-icon {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }

    .meta-value {
        font-size: 1rem;
    }

    .event-details-card {
        padding: 20px;
    }

    .description-text {
        font-size: 1rem;
        padding: 15px;
    }

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

@media (max-width: 480px) {
    .event-main-image {
        height: 250px;
    }

    .event-title {
        font-size: 1.8rem;
    }

    .event-category-badge {
        font-size: 0.8rem;
        padding: 6px 12px;
    }

    .meta-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}

/* Enhanced Booking Section Styling */
.booking-card {
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border: none;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    position: relative;
}

.booking-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--success-color));
}

.booking-header {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
    padding: 20px 25px;
    margin: 0;
}

.booking-title {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 700;
    display: flex;
    align-items: center;
}

.booking-body {
    padding: 25px;
}

/* Enhanced Ticket Cards */
.enhanced-ticket-card {
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border: 1px solid rgba(var(--primary-color-rgb), 0.1);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.enhanced-ticket-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, var(--primary-color), var(--accent-color));
}

.enhanced-ticket-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.ticket-name {
    color: #333;
    font-weight: 700;
    font-size: 1.2rem;
    margin: 0;
    flex: 1;
}

.ticket-price {
    text-align: right;
    margin-left: 15px;
}

.price-amount {
    display: block;
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--primary-color);
    line-height: 1;
}

.price-currency {
    display: block;
    font-size: 0.9rem;
    color: #666;
    font-weight: 600;
    margin-top: 2px;
}

.ticket-description {
    background: rgba(var(--accent-color-rgb), 0.05);
    border-left: 3px solid var(--accent-color);
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    color: #666;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.ticket-availability {
    margin-top: 15px;
}

.availability-status {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
}

.availability-status.available {
    background: rgba(var(--success-color-rgb), 0.1);
    color: var(--success-color);
    border: 1px solid rgba(var(--success-color-rgb), 0.2);
}

.availability-status.sold-out {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border: 1px solid rgba(220, 53, 69, 0.2);
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

/* Quantity Selector Styling */
.quantity-selector {
    margin-bottom: 15px;
}

.quantity-label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.quantity-controls {
    display: flex;
    align-items: center;
    background: #f8f9fc;
    border-radius: 10px;
    padding: 5px;
    border: 2px solid rgba(var(--primary-color-rgb), 0.1);
    max-width: 140px;
}

.quantity-btn {
    background: var(--primary-color);
    color: white;
    border: none;
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.quantity-btn:hover {
    background: var(--accent-color);
    transform: scale(1.05);
}

.quantity-input {
    border: none;
    background: transparent;
    text-align: center;
    font-weight: 700;
    font-size: 1.1rem;
    color: #333;
    width: 50px;
    margin: 0 5px;
}

.quantity-input:focus {
    outline: none;
}

/* Add to Cart Button */
.add-to-cart-btn {
    background: linear-gradient(135deg, var(--success-color), #17a673);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1rem;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(var(--success-color-rgb), 0.3);
}

.add-to-cart-btn:hover {
    background: linear-gradient(135deg, #17a673, var(--success-color));
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(var(--success-color-rgb), 0.4);
}

.add-to-cart-btn:active {
    transform: translateY(0);
}

/* Login Prompt Styling */
.login-prompt {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(var(--primary-color-rgb), 0.05);
    border: 1px solid rgba(var(--primary-color-rgb), 0.1);
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
}

.login-icon {
    width: 50px;
    height: 50px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.login-text h6 {
    margin: 0 0 5px 0;
    color: #333;
    font-weight: 700;
}

.login-text p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.login-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.login-link:hover {
    color: var(--accent-color);
    text-decoration: underline;
}

/* No Tickets Message */
.no-tickets-message {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.2);
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
}

.no-tickets-icon {
    width: 50px;
    height: 50px;
    background: #ffc107;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.no-tickets-text h6 {
    margin: 0 0 5px 0;
    color: #333;
    font-weight: 700;
}

.no-tickets-text p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
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