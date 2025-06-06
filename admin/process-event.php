<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Require admin access
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Sanitize and validate input
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $venue = sanitize($_POST['venue']);
        $venue_address = sanitize($_POST['venue_address'] ?? '');
        $event_date = $_POST['date'];
        $event_time = $_POST['time'];
        $organizer_name = sanitize($_POST['organizer_name']);
        $organizer_contact = sanitize($_POST['organizer_contact']);
        $organizer_address = sanitize($_POST['organizer_address'] ?? '');
        $category = sanitize($_POST['category']);
        $venue_lat = floatval($_POST['latitude']);
        $venue_lng = floatval($_POST['longitude']);

        // Validate required fields
        if (empty($title) || empty($description) || empty($venue) || empty($event_date) ||
            empty($event_time) || empty($organizer_name) || empty($organizer_contact) || empty($category)) {
            throw new Exception('All required fields must be filled');
        }

        // Validate ticket types
        if (!isset($_POST['ticket_types']) || !is_array($_POST['ticket_types']) || empty($_POST['ticket_types'])) {
            throw new Exception('At least one ticket type is required');
        }

        // Handle image upload
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/events/';

            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Invalid image format. Allowed formats: JPG, JPEG, PNG, GIF');
            }

            $file_name = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads/events/' . $file_name;
            } else {
                throw new Exception('Failed to upload image');
            }
        }

        // Calculate total seats and minimum price from ticket types
        $total_seats = 0;
        $min_price = PHP_INT_MAX;

        foreach ($_POST['ticket_types'] as $ticket_type) {
            if (!empty($ticket_type['seats']) && !empty($ticket_type['price'])) {
                $total_seats += intval($ticket_type['seats']);
                $min_price = min($min_price, floatval($ticket_type['price']));
            }
        }

        if ($min_price === PHP_INT_MAX) {
            $min_price = 0;
        }

        // Insert event into database
        $event_sql = "INSERT INTO events (title, description, venue, venue_address, event_date, event_time,
                                         organizer_name, organizer_contact, organizer_address, category,
                                         venue_lat, venue_lng, image_url, total_seats, available_seats, price, status)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";

        $event_stmt = $pdo->prepare($event_sql);
        $event_stmt->execute([
            $title, $description, $venue, $venue_address, $event_date, $event_time,
            $organizer_name, $organizer_contact, $organizer_address, $category,
            $venue_lat, $venue_lng, $image_url, $total_seats, $total_seats, $min_price
        ]);

        $event_id = $pdo->lastInsertId();

        // Process ticket types
        $ticket_sql = "INSERT INTO ticket_types (event_id, type_name, description, price, available_seats)
                       VALUES (?, ?, ?, ?, ?)";
        $ticket_stmt = $pdo->prepare($ticket_sql);

        foreach ($_POST['ticket_types'] as $ticket_type) {
            if (!empty($ticket_type['name']) && !empty($ticket_type['price']) && !empty($ticket_type['seats'])) {
                $ticket_name = sanitize($ticket_type['name']);
                $ticket_description = sanitize($ticket_type['description'] ?? '');
                $ticket_price = floatval($ticket_type['price']);
                $ticket_seats = intval($ticket_type['seats']);

                $ticket_stmt->execute([
                    $event_id, $ticket_name, $ticket_description, $ticket_price, $ticket_seats
                ]);
            }
        }

        // Commit transaction
        $pdo->commit();

        $_SESSION['success'] = 'Event created successfully with ticket types!';
        header("Location: dashboard.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollback();

        error_log("Event creation error: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to create event: ' . $e->getMessage();
        header("Location: add-event.php");
        exit();
    }
} else {
    // If not POST request, redirect to add event page
    header("Location: add-event.php");
    exit();
} 