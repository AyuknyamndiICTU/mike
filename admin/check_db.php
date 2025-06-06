<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Check if database exists
    $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('event_booking', $databases)) {
        die("Database 'event_booking' does not exist!");
    }

    // Use the database
    $pdo->query("USE event_booking");

    // Check required tables
    $required_tables = ['users', 'events', 'bookings'];
    $existing_tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $missing_tables = array_diff($required_tables, $existing_tables);
    
    if (!empty($missing_tables)) {
        die("Missing tables: " . implode(", ", $missing_tables));
    }

    // Check table structures
    foreach ($required_tables as $table) {
        $columns = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_COLUMN);
        echo "<h3>Table: $table</h3>";
        echo "Columns: " . implode(", ", $columns) . "<br><br>";
    }

    echo "Database check completed successfully!";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?> 