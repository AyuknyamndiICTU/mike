<?php
require_once 'config/database.php';

echo "<h2>Available Bookings for Testing</h2>";

try {
    $stmt = $pdo->query("SELECT b.id, b.user_id, e.title, b.qr_code 
                         FROM bookings b 
                         JOIN events e ON b.event_id = e.id 
                         ORDER BY b.id DESC 
                         LIMIT 10");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Booking ID</th><th>User ID</th><th>Event Title</th><th>QR Code</th><th>Test Links</th></tr>";
    
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['qr_code']) . "</td>";
        echo "<td>";
        echo "<a href='download-ticket.php?id=" . $row['id'] . "' target='_blank' style='margin: 2px; padding: 5px; background: #4f46e5; color: white; text-decoration: none; border-radius: 3px;'>Download</a> ";
        echo "<a href='verify-booking.php?id=" . $row['id'] . "' target='_blank' style='margin: 2px; padding: 5px; background: #10b981; color: white; text-decoration: none; border-radius: 3px;'>Verify</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<div style='margin: 20px 0; padding: 15px; background: #f0f9ff; border-radius: 5px;'>";
    echo "<h3>Testing Instructions:</h3>";
    echo "<ol>";
    echo "<li><strong>Download Test:</strong> Click any 'Download' link to test the new HTML ticket format</li>";
    echo "<li><strong>QR Code Test:</strong> Click any 'Verify' link to test QR code verification</li>";
    echo "<li><strong>Booking Confirmation:</strong> To test the booking confirmation page, you need to be logged in as the user who made the booking</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
