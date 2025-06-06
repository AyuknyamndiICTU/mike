<?php
// Set the path to your data directory
$dataPath = __DIR__ . '/data/event_booking/';
$outputFile = __DIR__ . '/full_backup.sql';

// First, include the database structure
$structure = file_get_contents(__DIR__ . '/database/event_booking.sql');
file_put_contents($outputFile, $structure . "\n\n");

// Function to read .frm files and extract table names
function getTableNames($dataPath) {
    $tables = [];
    $files = glob($dataPath . '*.ibd');
    foreach ($files as $file) {
        $tableName = basename($file, '.ibd');
        if ($tableName !== 'db' && $tableName !== 'mysql') {
            $tables[] = $tableName;
        }
    }
    return $tables;
}

echo "Starting database export...\n";
echo "Looking for tables in: " . $dataPath . "\n";

$tables = getTableNames($dataPath);
echo "Found tables: " . implode(", ", $tables) . "\n";

// Append table names to the backup file
file_put_contents($outputFile, "-- Found tables: " . implode(", ", $tables) . "\n\n", FILE_APPEND);

echo "Database structure and table list have been exported to: " . $outputFile . "\n";
echo "Please use this file along with the data in your data/event_booking folder to restore your database.\n";
?> 