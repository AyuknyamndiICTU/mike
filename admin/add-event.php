<?php
$page_title = "Add Event - Admin Dashboard";
require_once '../includes/header.php';
require_once '../config/database.php';

// Require admin access
requireAdmin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $venue = sanitize($_POST['venue']);
    $venue_lat = floatval($_POST['venue_lat']);
    $venue_lng = floatval($_POST['venue_lng']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $organizer_name = sanitize($_POST['organizer_name']);
    $organizer_contact = sanitize($_POST['organizer_contact']);
    $total_seats = (int)$_POST['total_seats'];
    $price = floatval($_POST['price']);
    $category = sanitize($_POST['category']);
    
    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/events/';
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = 'uploads/events/' . $file_name;
        }
    }
    
    // Insert event
    $sql = "INSERT INTO events (title, description, image_url, venue, venue_lat, venue_lng, 
                               event_date, event_time, organizer_name, organizer_contact, 
                               total_seats, available_seats, price, category) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssddssssiids", 
        $title, $description, $image_url, $venue, $venue_lat, $venue_lng,
        $event_date, $event_time, $organizer_name, $organizer_contact,
        $total_seats, $total_seats, $price, $category
    );
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Event added successfully');
        header("Location: events.php");
        exit();
    } else {
        $error = "Failed to add event";
    }
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
    .form-container {
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

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    }

    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 0.5rem;
        display: none;
        margin-top: 1rem;
    }

    .btn-submit {
        padding: 0.75rem 2rem;
        font-weight: 500;
    }

    textarea.form-control {
        min-height: 120px;
    }

    .ticket-type-item {
        background: #f8f9fc;
        border: 1px solid #e3e6f0 !important;
        transition: all 0.3s ease;
    }

    .ticket-type-item:hover {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .ticket-type-item h6 {
        color: var(--primary-color);
        font-weight: 600;
    }

    #ticketTypesContainer {
        min-height: 100px;
    }

    .organizer-section {
        background: #f8f9fc;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
</style>

<!-- Page Header -->
<div class="page-header animate__animated animate__fadeIn">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="animate__animated animate__fadeInLeft">Add New Event</h1>
            <div class="header-actions animate__animated animate__fadeInRight">
                <a href="events.php" class="btn btn-back">
                    <i class="bi bi-arrow-left"></i> Back to Events
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="form-container">
        <form id="addEventForm" method="POST" action="process-event.php" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <!-- Basic Event Details -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Event Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Event Description</label>
                        <textarea class="form-control" id="description" name="description" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Event Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="time" class="form-label">Event Time</label>
                            <input type="time" class="form-control" id="time" name="time" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="venue" class="form-label">Venue Name</label>
                        <input type="text" class="form-control" id="venue" name="venue" required>
                    </div>

                    <div class="mb-3">
                        <label for="venue_address" class="form-label">Venue Address</label>
                        <textarea class="form-control" id="venue_address" name="venue_address" rows="2"></textarea>
                    </div>

                    <!-- Organizer Information -->
                    <div class="mb-4">
                        <h5 class="mb-3">Organizer Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="organizer_name" class="form-label">Organizer Name</label>
                                <input type="text" class="form-control" id="organizer_name" name="organizer_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="organizer_contact" class="form-label">Contact (Phone/Email)</label>
                                <input type="text" class="form-control" id="organizer_contact" name="organizer_contact" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="organizer_address" class="form-label">Organizer Address</label>
                            <textarea class="form-control" id="organizer_address" name="organizer_address" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Ticket Types Section -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Ticket Types</h5>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addTicketType()">
                                <i class="bi bi-plus-circle me-1"></i>Add Ticket Type
                            </button>
                        </div>
                        <div id="ticketTypesContainer">
                            <!-- Ticket types will be added here dynamically -->
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Event Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="conference">Conference</option>
                            <option value="seminar">Seminar</option>
                            <option value="workshop">Workshop</option>
                            <option value="concert">Concert</option>
                            <option value="exhibition">Exhibition</option>
                            <option value="sports">Sports</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Event Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                        <img id="imagePreview" class="image-preview" src="#" alt="Image preview">
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
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="latitude" name="latitude" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="longitude" name="longitude" readonly>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary btn-submit">Create Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
// Initialize map
var map = L.map('map').setView([0, 0], 2);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

var marker;

// Handle map clicks
map.on('click', function(e) {
    var lat = e.latlng.lat;
    var lng = e.latlng.lng;
    
    // Update form fields
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
    
    // Update or add marker
    if (marker) {
        marker.setLatLng(e.latlng);
    } else {
        marker = L.marker(e.latlng).addTo(map);
    }
});

// Image preview function
function previewImage(input) {
    var preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Ticket type management
let ticketTypeCount = 0;

function addTicketType() {
    ticketTypeCount++;
    const container = document.getElementById('ticketTypesContainer');
    const ticketTypeHtml = `
        <div class="ticket-type-item border rounded p-3 mb-3" id="ticketType${ticketTypeCount}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Ticket Type #${ticketTypeCount}</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTicketType(${ticketTypeCount})">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ticket_name_${ticketTypeCount}" class="form-label">Ticket Name</label>
                    <input type="text" class="form-control" id="ticket_name_${ticketTypeCount}"
                           name="ticket_types[${ticketTypeCount}][name]" placeholder="e.g., VIP, General, Student" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="ticket_price_${ticketTypeCount}" class="form-label">Price (FCFA)</label>
                    <input type="number" class="form-control" id="ticket_price_${ticketTypeCount}"
                           name="ticket_types[${ticketTypeCount}][price]" min="0" step="1" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="ticket_seats_${ticketTypeCount}" class="form-label">Available Seats</label>
                    <input type="number" class="form-control" id="ticket_seats_${ticketTypeCount}"
                           name="ticket_types[${ticketTypeCount}][seats]" min="1" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="ticket_description_${ticketTypeCount}" class="form-label">Description (Optional)</label>
                <textarea class="form-control" id="ticket_description_${ticketTypeCount}"
                          name="ticket_types[${ticketTypeCount}][description]" rows="2"
                          placeholder="Brief description of this ticket type"></textarea>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', ticketTypeHtml);
}

function removeTicketType(id) {
    const element = document.getElementById(`ticketType${id}`);
    if (element) {
        element.remove();
    }
}

// Add first ticket type by default
document.addEventListener('DOMContentLoaded', function() {
    addTicketType();
});

// Form validation
document.getElementById('addEventForm').addEventListener('submit', function(e) {
    if (!document.getElementById('latitude').value || !document.getElementById('longitude').value) {
        e.preventDefault();
        alert('Please select a location on the map');
        return;
    }

    // Check if at least one ticket type exists
    const ticketTypes = document.querySelectorAll('.ticket-type-item');
    if (ticketTypes.length === 0) {
        e.preventDefault();
        alert('Please add at least one ticket type');
        return;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 