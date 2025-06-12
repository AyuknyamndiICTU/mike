<?php
$page_title = "Edit Event - Admin Dashboard";
require_once '../includes/header.php';
require_once '../config/database.php';

// Require admin access
requireAdmin();

// Get event ID from URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch event details
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        $_SESSION['error'] = "Event not found";
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching event: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching event details";
    header("Location: dashboard.php");
    exit();
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

    /* Form Container Styles */
    .edit-form-container {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        padding: 2rem;
        margin-top: 2rem;
    }

    .map-container {
        height: 400px;
        border-radius: 0.5rem;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    #map {
        height: 100%;
        width: 100%;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #e3e6f0;
    }

    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 0.5rem;
        margin-top: 1rem;
    }

    .current-image {
        max-width: 200px;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
</style>

<!-- Page Header -->
<div class="page-header animate__animated animate__fadeIn">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="animate__animated animate__fadeInLeft">Edit Event</h1>
            <div class="header-actions animate__animated animate__fadeInRight">
                <a href="dashboard.php" class="btn btn-back">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="edit-form-container">
        <form id="editEventForm" method="POST" action="update-event.php" enctype="multipart/form-data">
            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Basic Event Details -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Event Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($event['title']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Event Description</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label">Event Date</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" 
                                   value="<?php echo $event['event_date']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="event_time" class="form-label">Event Time</label>
                            <input type="time" class="form-control" id="event_time" name="event_time" 
                                   value="<?php echo $event['event_time']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="venue" class="form-label">Venue Name</label>
                        <input type="text" class="form-control" id="venue" name="venue" 
                               value="<?php echo htmlspecialchars($event['venue']); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Ticket Price (FCFA)</label>
                            <input type="number" class="form-control" id="price" name="price" 
                                   value="<?php echo $event['price']; ?>" min="0" step="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="total_seats" class="form-label">Total Seats</label>
                            <input type="number" class="form-control" id="total_seats" name="total_seats" 
                                   value="<?php echo $event['total_seats']; ?>" min="1" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Event Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php
                            $categories = ['conference', 'seminar', 'workshop', 'concert', 'exhibition', 'sports', 'other'];
                            foreach ($categories as $cat) {
                                $selected = ($event['category'] === $cat) ? 'selected' : '';
                                echo "<option value=\"$cat\" $selected>" . ucfirst($cat) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <?php if ($event['image_url']): ?>
                            <div>
                                <img src="../<?php echo htmlspecialchars($event['image_url']); ?>" 
                                     alt="Event image" class="current-image">
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No image uploaded</p>
                        <?php endif; ?>
                        
                        <label for="image" class="form-label mt-3">Update Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Map and Location Details -->
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <div class="map-container">
                            <div id="map"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="venue_lat" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="venue_lat" name="venue_lat" 
                               value="<?php echo $event['venue_lat']; ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="venue_lng" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="venue_lng" name="venue_lng" 
                               value="<?php echo $event['venue_lng']; ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <a href="dashboard.php" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
            </div>
        </form>
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

var marker;
if (<?php echo $event['venue_lat']; ?> && <?php echo $event['venue_lng']; ?>) {
    marker = L.marker([<?php echo $event['venue_lat']; ?>, <?php echo $event['venue_lng']; ?>]).addTo(map);
}

// Handle map clicks
map.on('click', function(e) {
    var lat = e.latlng.lat;
    var lng = e.latlng.lng;
    
    // Update form fields
    document.getElementById('venue_lat').value = lat.toFixed(6);
    document.getElementById('venue_lng').value = lng.toFixed(6);
    
    // Update or add marker
    if (marker) {
        marker.setLatLng(e.latlng);
    } else {
        marker = L.marker(e.latlng).addTo(map);
    }
});

// Image preview function
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Add preview functionality to image input
document.getElementById('image').addEventListener('change', function() {
    previewImage(this);
});
</script>

<?php require_once '../includes/footer.php'; ?> 