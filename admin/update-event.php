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
    $event_id = (int)$_POST['event_id'];
    
    // Validate event exists
    $check_stmt = $pdo->prepare("SELECT id FROM events WHERE id = ?");
    $check_stmt->execute([$event_id]);
    if (!$check_stmt->fetch()) {
        $_SESSION['error'] = "Event not found";
        header("Location: dashboard.php");
        exit();
    }

    // Sanitize and validate input
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $venue = htmlspecialchars(trim($_POST['venue']));
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $total_seats = (int)$_POST['total_seats'];
    $price = floatval($_POST['price']);
    $category = htmlspecialchars(trim($_POST['category']));
    $venue_lat = floatval($_POST['venue_lat']);
    $venue_lng = floatval($_POST['venue_lng']);

    // Validate required fields
    if (empty($title) || empty($description) || empty($venue) || empty($event_date) || 
        empty($event_time) || empty($total_seats) || empty($category)) {
        $_SESSION['error'] = 'All fields are required';
        header("Location: edit-event.php?id=" . $event_id);
        exit();
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Handle image upload if new image is provided
        $image_sql = "";
        $params = [
            'title' => $title,
            'description' => $description,
            'venue' => $venue,
            'venue_lat' => $venue_lat,
            'venue_lng' => $venue_lng,
            'event_date' => $event_date,
            'event_time' => $event_time,
            'total_seats' => $total_seats,
            'price' => $price,
            'category' => $category,
            'event_id' => $event_id
        ];

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
                // Get old image path to delete after successful update
                $old_image_stmt = $pdo->prepare("SELECT image_url FROM events WHERE id = ?");
                $old_image_stmt->execute([$event_id]);
                $old_image = $old_image_stmt->fetch()['image_url'];

                $image_sql = ", image_url = :image_url";
                $params['image_url'] = 'uploads/events/' . $file_name;

                // Delete old image if exists
                if ($old_image && file_exists('../' . $old_image)) {
                    unlink('../' . $old_image);
                }
            }
        }

        // Update event
        $sql = "UPDATE events SET 
                title = :title,
                description = :description,
                venue = :venue,
                venue_lat = :venue_lat,
                venue_lng = :venue_lng,
                event_date = :event_date,
                event_time = :event_time,
                total_seats = :total_seats,
                price = :price,
                category = :category" . $image_sql . "
                WHERE id = :event_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success'] = "Event updated successfully";
        header("Location: dashboard.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Error updating event: " . $e->getMessage());
        $_SESSION['error'] = "Error updating event: " . $e->getMessage();
        header("Location: edit-event.php?id=" . $event_id);
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
} 