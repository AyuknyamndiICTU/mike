<?php
require_once 'config/database.php';
session_start();

// Require login
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

// Get booking ID
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get booking details with more comprehensive information
    $sql = "SELECT b.*, e.title, e.description, e.event_date, e.event_time, e.venue, e.venue_address,
                   e.price, e.organizer_name, e.organizer_contact, e.organizer_address, e.category,
                   u.username, u.email as user_email, u.full_name as user_full_name
            FROM bookings b
            JOIN events e ON b.event_id = e.id
            JOIN users u ON b.user_id = u.id
            WHERE b.id = ? AND b.user_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        die("Booking not found");
    }

    // Get attendee information from session if available
    $attendee_name = $_SESSION['last_booking_attendee']['name'] ?? $booking['user_full_name'] ?? $booking['username'];
    $attendee_email = $_SESSION['last_booking_attendee']['email'] ?? $booking['user_email'];
    $attendee_phone = $_SESSION['last_booking_attendee']['phone'] ?? 'Not provided';

    // Generate QR code path for ticket
    $qr_code_path = '';
    if ($booking['qr_code'] && file_exists('uploads/qrcodes/' . $booking['qr_code'])) {
        $qr_code_path = 'uploads/qrcodes/' . $booking['qr_code'];
    }

    // Generate a more descriptive filename
    $event_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $booking['title']);
    $event_date = date('Y-m-d', strtotime($booking['event_date']));
    $filename = "Ticket_{$event_name}_{$event_date}_Booking_{$booking['id']}.html";

    // Set headers for file download
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    // Generate beautiful HTML ticket
    $ticket_html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticket - ' . htmlspecialchars($booking['title']) . '</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .ticket-header {
            background: linear-gradient(135deg, #10b981 0%, #06b6d4 50%, #4f46e5 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .ticket-header::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        .ticket-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }

        .ticket-subtitle {
            font-size: 1.2rem;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        .ticket-content {
            padding: 40px 30px;
            background: linear-gradient(145deg, #f8fafc, #ffffff);
        }

        .ticket-row {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .ticket-section {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            position: relative;
        }

        .ticket-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #06b6d4, #4f46e5);
            border-radius: 15px 15px 0 0;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #06b6d4, #4f46e5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.9rem;
        }

        .detail-value {
            font-weight: 500;
            color: #1e293b;
            text-align: right;
            max-width: 60%;
        }

        .qr-section {
            text-align: center;
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            border: 2px dashed #06b6d4;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
        }

        .qr-code {
            max-width: 200px;
            height: auto;
            border-radius: 10px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .instructions {
            background: linear-gradient(145deg, #fef3c7, #fde68a);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            border-left: 5px solid #f59e0b;
        }

        .instructions h3 {
            color: #92400e;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .instructions ul {
            list-style: none;
            padding: 0;
        }

        .instructions li {
            padding: 8px 0;
            color: #78350f;
            font-weight: 500;
            position: relative;
            padding-left: 25px;
        }

        .instructions li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #059669;
            font-weight: bold;
        }

        .ticket-footer {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }

        .footer-text {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .status-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .event-title-large {
            font-size: 1.3rem;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 15px;
            text-align: center;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .ticket-container {
                box-shadow: none;
                max-width: none;
            }

            .ticket-header::before {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .ticket-row {
                flex-direction: column;
                gap: 20px;
            }

            .ticket-section {
                min-width: auto;
            }

            .ticket-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="ticket-header">
            <h1 class="ticket-title">🎫 EVENT TICKET</h1>
            <p class="ticket-subtitle">Your Official Entry Pass</p>
        </div>

        <div class="ticket-content">
            <div class="event-title-large">' . htmlspecialchars($booking['title']) . '</div>

            <div class="ticket-row">
                <div class="ticket-section">
                    <h3 class="section-title">
                        <span class="section-icon">📅</span>
                        Event Information
                    </h3>
                    <div class="detail-item">
                        <span class="detail-label">Date</span>
                        <span class="detail-value">' . date('l, F d, Y', strtotime($booking['event_date'])) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Time</span>
                        <span class="detail-value">' . date('g:i A', strtotime($booking['event_time'])) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Venue</span>
                        <span class="detail-value">' . htmlspecialchars($booking['venue']) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Address</span>
                        <span class="detail-value">' . htmlspecialchars($booking['venue_address'] ?? 'Address not specified') . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Category</span>
                        <span class="detail-value">' . htmlspecialchars(ucfirst($booking['category'] ?? 'General')) . '</span>
                    </div>
                </div>

                <div class="ticket-section">
                    <h3 class="section-title">
                        <span class="section-icon">🎟️</span>
                        Booking Details
                    </h3>
                    <div class="detail-item">
                        <span class="detail-label">Booking ID</span>
                        <span class="detail-value">#' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Quantity</span>
                        <span class="detail-value">' . $booking['quantity'] . ' ticket(s)</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Paid</span>
                        <span class="detail-value">' . number_format($booking['total_amount'], 0) . ' FCFA</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Booking Date</span>
                        <span class="detail-value">' . date('M d, Y', strtotime($booking['booking_date'])) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <span class="detail-value"><span class="status-badge">' . htmlspecialchars($booking['status']) . '</span></span>
                    </div>
                </div>
            </div>

            <div class="ticket-row">
                <div class="ticket-section">
                    <h3 class="section-title">
                        <span class="section-icon">👤</span>
                        Attendee Information
                    </h3>
                    <div class="detail-item">
                        <span class="detail-label">Name</span>
                        <span class="detail-value">' . htmlspecialchars($attendee_name) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value">' . htmlspecialchars($attendee_email) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone</span>
                        <span class="detail-value">' . htmlspecialchars($attendee_phone) . '</span>
                    </div>
                </div>

                <div class="ticket-section">
                    <h3 class="section-title">
                        <span class="section-icon">🏢</span>
                        Organizer Information
                    </h3>
                    <div class="detail-item">
                        <span class="detail-label">Organizer</span>
                        <span class="detail-value">' . htmlspecialchars($booking['organizer_name']) . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Contact</span>
                        <span class="detail-value">' . htmlspecialchars($booking['organizer_contact'] ?? 'Contact not provided') . '</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Address</span>
                        <span class="detail-value">' . htmlspecialchars($booking['organizer_address'] ?? 'Address not provided') . '</span>
                    </div>
                </div>
            </div>';

    // Add QR code section if available
    if ($qr_code_path) {
        $qr_code_base64 = base64_encode(file_get_contents($qr_code_path));
        $ticket_html .= '
            <div class="qr-section">
                <h3 class="section-title" style="justify-content: center;">
                    <span class="section-icon">📱</span>
                    Digital Verification
                </h3>
                <img src="data:image/png;base64,' . $qr_code_base64 . '" alt="Booking QR Code" class="qr-code">
                <p style="color: #475569; font-weight: 500; margin-top: 15px;">
                    Scan this QR code at the venue for quick entry verification
                </p>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px;">
                    Reference: ' . htmlspecialchars($booking['qr_code']) . '
                </p>
            </div>';
    }

    $ticket_html .= '
            <div class="instructions">
                <h3>⚠️ Important Instructions</h3>
                <ul>
                    <li>Please arrive at least 30 minutes before the event start time</li>
                    <li>This ticket is valid ONLY for the specified date and time above</li>
                    <li>Present this ticket (printed or digital) at the venue entrance</li>
                    <li>Keep your reference code safe for verification purposes</li>
                    <li>This ticket is non-transferable and non-refundable</li>
                    <li>Contact the organizer for any event-related inquiries</li>
                    <li>In case of event cancellation, refund details will be communicated</li>
                </ul>
            </div>
        </div>

        <div class="ticket-footer">
            <p class="footer-text">
                🎉 Thank you for choosing our event booking system! Enjoy your event! 🎉<br>
                Generated on: ' . date('F d, Y \a\t g:i A') . ' | Verification ID: BOOK-' . $booking['id'] . '-' . date('Y', strtotime($booking['booking_date'])) . '
            </p>
        </div>
    </div>
</body>
</html>';

    // Output the HTML ticket
    echo $ticket_html;
    exit;

} catch (Exception $e) {
    error_log("Ticket Download Error: " . $e->getMessage());
    die("An error occurred while generating your ticket.");
}
?>