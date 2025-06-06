<?php
require_once '../config/database.php';

try {
    echo "<h2>Database Structure Check</h2>";
    
    // First check if tables exist
    $tables_query = $pdo->query("SHOW TABLES");
    $tables = $tables_query->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Available Tables:</h3>";
    echo "<pre>";
    print_r($tables);
    echo "</pre><hr>";
    
    // Check structure of each relevant table
    $relevant_tables = ['bookings', 'booking_details', 'events'];
    
    foreach ($relevant_tables as $table) {
        if (in_array($table, $tables)) {
            echo "<h3>Structure of '$table' table:</h3>";
            $columns = $pdo->query("SHOW COLUMNS FROM $table");
            echo "<pre>";
            print_r($columns->fetchAll(PDO::FETCH_ASSOC));
            echo "</pre>";
            
            // Show sample data
            echo "<h4>Sample data from '$table':</h4>";
            $sample = $pdo->query("SELECT * FROM $table LIMIT 1");
            echo "<pre>";
            print_r($sample->fetch(PDO::FETCH_ASSOC));
            echo "</pre>";
            echo "<hr>";
        } else {
            echo "<p style='color: red;'>Table '$table' does not exist!</p><hr>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 