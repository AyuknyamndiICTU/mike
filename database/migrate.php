<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Check if columns need to be renamed
    $check_columns_sql = "SHOW COLUMNS FROM events";
    $check_stmt = $pdo->query($check_columns_sql);
    $columns = $check_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns in events table: " . implode(', ', $columns) . "<br>";
    
    // If we have 'date' and 'time' columns, rename them to 'event_date' and 'event_time'
    if (in_array('date', $columns) && !in_array('event_date', $columns)) {
        echo "Renaming 'date' column to 'event_date'...<br>";
        $pdo->exec("ALTER TABLE events CHANGE COLUMN `date` `event_date` DATE NOT NULL");
        echo "✓ Renamed 'date' to 'event_date'<br>";
    }
    
    if (in_array('time', $columns) && !in_array('event_time', $columns)) {
        echo "Renaming 'time' column to 'event_time'...<br>";
        $pdo->exec("ALTER TABLE events CHANGE COLUMN `time` `event_time` TIME NOT NULL");
        echo "✓ Renamed 'time' to 'event_time'<br>";
    }
    
    // Check if we need to rename 'image' to 'image_url'
    if (in_array('image', $columns) && !in_array('image_url', $columns)) {
        echo "Renaming 'image' column to 'image_url'...<br>";
        $pdo->exec("ALTER TABLE events CHANGE COLUMN `image` `image_url` VARCHAR(255)");
        echo "✓ Renamed 'image' to 'image_url'<br>";
    }
    
    // Add missing columns if they don't exist
    if (!in_array('organizer_name', $columns)) {
        echo "Adding 'organizer_name' column...<br>";
        $pdo->exec("ALTER TABLE events ADD COLUMN organizer_name VARCHAR(255) NOT NULL DEFAULT 'Event Organizer'");
        echo "✓ Added 'organizer_name' column<br>";
    }
    
    if (!in_array('organizer_contact', $columns)) {
        echo "Adding 'organizer_contact' column...<br>";
        $pdo->exec("ALTER TABLE events ADD COLUMN organizer_contact VARCHAR(100)");
        echo "✓ Added 'organizer_contact' column<br>";
    }
    
    echo "<br>Database migration completed successfully!<br>";
    
} catch (PDOException $e) {
    echo "Migration error: " . $e->getMessage() . "<br>";
}
?>
