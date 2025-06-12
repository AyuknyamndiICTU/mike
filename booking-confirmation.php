<?php
// Suppress PHP warnings for cleaner display
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

$page_title = "Booking Confirmation - Event Booking System";
require_once 'includes/header.php';
require_once 'config/database.php';

// Add Font Awesome for icons
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">';

// Require login
requireLogin();

// Get latest booking
try {
    // Check what columns exist in events table
    $check_columns_sql = "SHOW COLUMNS FROM events";
    $check_stmt = $pdo->prepare($check_columns_sql);
    $check_stmt->execute();
    $columns = $check_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Build the SELECT query based on available columns
    $event_columns = ['e.title', 'e.price'];

    // Check for date/time columns
    if (in_array('event_date', $columns)) {
        $event_columns[] = 'e.event_date';
    } elseif (in_array('date', $columns)) {
        $event_columns[] = 'e.date as event_date';
    }

    if (in_array('event_time', $columns)) {
        $event_columns[] = 'e.event_time';
    } elseif (in_array('time', $columns)) {
        $event_columns[] = 'e.time as event_time';
    }

    if (in_array('venue', $columns)) {
        $event_columns[] = 'e.venue';
    } elseif (in_array('location', $columns)) {
        $event_columns[] = 'e.location as venue';
    }

    $sql = "SELECT b.*, " . implode(', ', $event_columns) . "
            FROM bookings b
            JOIN events e ON b.event_id = e.id
            WHERE b.user_id = ?
            ORDER BY b.booking_date DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        header("Location: bookings.php");
        exit();
    }

    // Generate QR code if not already generated
    if (!$booking['qr_code']) {
        // Create directory if it doesn't exist
        if (!file_exists('uploads/qrcodes/')) {
            mkdir('uploads/qrcodes/', 0755, true);
        }

        // Simple QR code data - just the booking reference
        $qr_reference = 'BOOK-' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT) . '-' . date('Y', strtotime($booking['booking_date']));

        // Try to generate QR code using Google Charts API
        $qr_url = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($qr_reference) . '&choe=UTF-8';

        // Generate unique filename
        $qr_filename = 'qr_' . $booking['id'] . '_' . time() . '.png';
        $qr_path = 'uploads/qrcodes/' . $qr_filename;

        // Try to download and save the QR code image
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        $qr_image_data = @file_get_contents($qr_url, false, $context);

        if ($qr_image_data !== false && !empty($qr_image_data)) {
            // Successfully downloaded QR code image
            if (@file_put_contents($qr_path, $qr_image_data)) {
                // Update booking with QR code filename
                $update_sql = "UPDATE bookings SET qr_code = ? WHERE id = ?";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([$qr_filename, $booking['id']]);
                $booking['qr_code'] = $qr_filename;
            } else {
                // File write failed, use reference
                $update_sql = "UPDATE bookings SET qr_code = ? WHERE id = ?";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([$qr_reference, $booking['id']]);
                $booking['qr_code'] = $qr_reference;
            }
        } else {
            // QR code generation failed, use text reference
            $update_sql = "UPDATE bookings SET qr_code = ? WHERE id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$qr_reference, $booking['id']]);
            $booking['qr_code'] = $qr_reference;
        }
    }

} catch (PDOException $e) {
    error_log("Booking Confirmation Error: " . $e->getMessage());
    $error = "An error occurred while fetching your booking details.";
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="confirmation-card">
                <div class="confirmation-header">
                    <div class="success-animation">
                        <div class="checkmark-circle">
                            <div class="checkmark"></div>
                        </div>
                    </div>
                    <h1 class="confirmation-title">Booking Confirmed!</h1>
                    <p class="confirmation-subtitle">Your tickets have been successfully booked</p>
                </div>
                    
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger animate-error"><?php echo $error; ?></div>
                <?php else: ?>
                    <div class="confirmation-content">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="info-card event-card animate-slide-left">
                                    <div class="card-header-compact">
                                        <div class="card-icon-small">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <h6 class="card-title-compact">Event Details</h6>
                                    </div>
                                    <div class="card-content-compact">
                                        <h6 class="event-title-compact"><?php echo htmlspecialchars($booking['title']); ?></h6>
                                        <?php if (isset($booking['event_date']) && $booking['event_date']): ?>
                                        <div class="detail-item-compact">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (isset($booking['event_time']) && $booking['event_time']): ?>
                                        <div class="detail-item-compact">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo date('g:i A', strtotime($booking['event_time'])); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (isset($booking['venue']) && $booking['venue']): ?>
                                        <div class="detail-item-compact">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($booking['venue']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card booking-card animate-slide-right">
                                    <div class="card-header-compact">
                                        <div class="card-icon-small">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                        <h6 class="card-title-compact">Booking Information</h6>
                                    </div>
                                    <div class="card-content-compact">
                                        <div class="detail-item-compact">
                                            <i class="fas fa-hashtag"></i>
                                            <span><strong>ID:</strong> #<?php echo $booking['id']; ?></span>
                                        </div>
                                        <div class="detail-item-compact">
                                            <i class="fas fa-users"></i>
                                            <span><strong>Qty:</strong> <?php echo $booking['quantity']; ?> ticket(s)</span>
                                        </div>
                                        <div class="detail-item-compact">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <span><strong>Total:</strong> <?php echo number_format($booking['total_amount'], 0); ?> FCFA</span>
                                        </div>
                                        <div class="detail-item-compact">
                                            <i class="fas fa-check-circle"></i>
                                            <span><strong>Status:</strong> <span class="status-badge confirmed">Confirmed</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <div class="qr-card-compact animate-fade-up">
                                    <div class="qr-header-compact">
                                        <i class="fas fa-qrcode"></i>
                                        <h6>Your Booking Reference</h6>
                                    </div>
                                    <div class="qr-content-compact">
                                        <div class="qr-code-display-compact">
                                            <?php
                                            // Check if QR code is a file or text reference
                                            $qr_file_path = 'uploads/qrcodes/' . $booking['qr_code'];
                                            $is_qr_image = file_exists($qr_file_path) &&
                                                          (pathinfo($booking['qr_code'], PATHINFO_EXTENSION) === 'png' ||
                                                           pathinfo($booking['qr_code'], PATHINFO_EXTENSION) === 'jpg');

                                            if ($is_qr_image): ?>
                                                <img src="<?php echo $qr_file_path; ?>"
                                                     alt="Booking QR Code"
                                                     class="qr-code-image"
                                                     style="max-width: 150px; height: auto; border-radius: 8px; display: block; margin: 0 auto;">
                                            <?php else: ?>
                                                <!-- Generate QR code on the fly using Google Charts API -->
                                                <?php
                                                $qr_data = $booking['qr_code'];
                                                $qr_url = 'https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=' . urlencode($qr_data) . '&choe=UTF-8';
                                                ?>
                                                <img src="<?php echo $qr_url; ?>"
                                                     alt="Booking QR Code"
                                                     class="qr-code-image"
                                                     style="max-width: 150px; height: auto; border-radius: 8px; display: block; margin: 0 auto;"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <div class="qr-fallback" style="display: none;">
                                                    <i class="fas fa-qrcode qr-icon-compact"></i>
                                                    <div class="qr-code-text-compact"><?php echo htmlspecialchars($booking['qr_code']); ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="qr-instruction-compact">
                                            <i class="fas fa-info-circle"></i>
                                            Scan QR code or show reference at venue for entry
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <?php if (isset($_SESSION['last_booking_attendee'])): ?>
                            <div class="col-md-5">
                                <div class="info-card attendee-card animate-fade-up" style="animation-delay: 0.2s;">
                                    <div class="card-header-compact">
                                        <div class="card-icon-small">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <h6 class="card-title-compact">Attendee Info</h6>
                                    </div>
                                    <div class="card-content-compact">
                                        <div class="detail-item-compact">
                                            <i class="fas fa-user-circle"></i>
                                            <span><?php echo htmlspecialchars($_SESSION['last_booking_attendee']['name']); ?></span>
                                        </div>
                                        <div class="detail-item-compact">
                                            <i class="fas fa-envelope"></i>
                                            <span><?php echo htmlspecialchars($_SESSION['last_booking_attendee']['email']); ?></span>
                                        </div>
                                        <div class="detail-item-compact">
                                            <i class="fas fa-phone"></i>
                                            <span><?php echo htmlspecialchars($_SESSION['last_booking_attendee']['phone']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="action-buttons-compact animate-fade-up" style="animation-delay: 0.4s;">
                            <div class="button-group-compact">
                                <a href="download-ticket.php?id=<?php echo $booking['id']; ?>"
                                   class="btn btn-primary btn-action-compact">
                                    <i class="fas fa-download"></i>
                                    <span>Download</span>
                                </a>
                                <a href="bookings.php" class="btn btn-secondary btn-action-compact">
                                    <i class="fas fa-list"></i>
                                    <span>All Bookings</span>
                                </a>
                                <a href="events.php" class="btn btn-outline btn-action-compact">
                                    <i class="fas fa-plus"></i>
                                    <span>Book More</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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

:root {
    --primary-color: #4f46e5;
    --primary-color-rgb: 79, 70, 229;
    --success-color: #10b981;
    --success-color-rgb: 16, 185, 129;
    --accent-color: #06b6d4;
    --accent-color-rgb: 6, 182, 212;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --dark-color: #1e293b;
    --light-color: #f1f5f9;
    --card-bg: #ffffff;
    --border-radius: 12px;
    --border-radius-lg: 16px;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Main confirmation card */
.confirmation-card {
    background: linear-gradient(145deg, var(--card-bg), var(--light-color));
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    position: relative;
    animation: slideInUp 0.8s ease-out;
    max-height: 100vh;
}

.confirmation-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--success-color), var(--accent-color), var(--primary-color));
    animation: shimmer 3s infinite;
}

/* Header section - more compact */
.confirmation-header {
    background: linear-gradient(135deg, var(--success-color) 0%, var(--accent-color) 50%, var(--primary-color) 100%);
    color: white;
    padding: 40px 30px 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.confirmation-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.15), transparent);
    transform: rotate(45deg);
    animation: headerShine 4s infinite;
}

/* Success animation - more compact */
.success-animation {
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}

.checkmark-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    margin: 0 auto;
    position: relative;
    animation: scaleIn 0.6s ease-out 0.3s both;
    backdrop-filter: blur(10px);
}

.checkmark {
    width: 40px;
    height: 40px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.checkmark::before {
    content: '✓';
    font-size: 32px;
    font-weight: bold;
    color: white;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: checkmarkDraw 0.8s ease-out 0.6s both;
}

.confirmation-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0 0 10px 0;
    position: relative;
    z-index: 1;
    animation: fadeInUp 0.8s ease-out 0.4s both;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.confirmation-subtitle {
    font-size: 1.1rem;
    opacity: 0.95;
    margin: 0;
    position: relative;
    z-index: 1;
    animation: fadeInUp 0.8s ease-out 0.6s both;
}

/* Content section - more compact */
.confirmation-content {
    padding: 25px 30px;
    background: linear-gradient(145deg, #f8fafc, #ffffff);
    border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);
}

/* Info cards - compact version with light background like QR section */
.info-card {
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--shadow-md);
    border: 1px solid rgba(var(--accent-color-rgb), 0.2);
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--success-color), var(--accent-color));
}

.info-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}

/* Compact card headers */
.card-header-compact {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.card-icon-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: white;
}

.event-card .card-icon-small {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
}

.booking-card .card-icon-small {
    background: linear-gradient(135deg, var(--success-color), var(--accent-color));
}

.attendee-card .card-icon-small {
    background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
}

.card-title-compact {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.card-content-compact {
    padding-left: 0;
}

.event-title-compact {
    font-size: 1rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.detail-item-compact {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    padding: 4px 0;
}

.detail-item-compact i {
    width: 16px;
    color: var(--accent-color);
    font-size: 0.9rem;
}

.detail-item-compact span {
    color: #334155;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-badge {
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.confirmed {
    background: linear-gradient(135deg, rgba(var(--success-color-rgb), 0.15), rgba(var(--accent-color-rgb), 0.1));
    color: var(--success-color);
    border: 1px solid rgba(var(--success-color-rgb), 0.2);
}

/* QR Code section - compact with better contrast */
.qr-card-compact {
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border-radius: var(--border-radius);
    padding: 20px;
    text-align: center;
    box-shadow: var(--shadow-md);
    border: 2px dashed rgba(var(--accent-color-rgb), 0.3);
    position: relative;
    overflow: hidden;
    height: 100%;
}

.qr-card-compact::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(var(--accent-color-rgb), 0.1), transparent);
    animation: qrShine 3s infinite;
}

.qr-header-compact {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 15px;
}

.qr-header-compact i {
    font-size: 1.3rem;
    color: var(--accent-color);
    animation: pulse 2s infinite;
}

.qr-header-compact h6 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
}

.qr-content-compact {
    position: relative;
    z-index: 1;
}

.qr-code-display-compact {
    background: linear-gradient(145deg, white, var(--light-color));
    border-radius: 10px;
    padding: 15px;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(var(--accent-color-rgb), 0.2);
    display: inline-block;
    position: relative;
    margin-bottom: 10px;
}

.qr-icon-compact {
    font-size: 2rem;
    color: var(--accent-color);
    margin-bottom: 8px;
    animation: qrPulse 2s infinite;
}

.qr-code-text-compact {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
    background: linear-gradient(145deg, #f1f5f9, #e2e8f0);
    padding: 8px 12px;
    border-radius: 6px;
    margin-top: 8px;
}

.qr-instruction-compact {
    color: #475569;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0;
    position: relative;
    z-index: 1;
}

.qr-instruction-compact i {
    color: var(--accent-color);
    margin-right: 6px;
}

/* Action buttons - compact */
.action-buttons-compact {
    margin-top: 20px;
    text-align: center;
}

.button-group-compact {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-action-compact {
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-width: 120px;
    justify-content: center;
}

.btn-action-compact::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.btn-action-compact:hover::before {
    left: 100%;
}

.btn-primary.btn-action-compact {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
    border: none;
}

.btn-primary.btn-action-compact:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(var(--primary-color-rgb), 0.3);
}

.btn-secondary.btn-action-compact {
    background: linear-gradient(135deg, var(--success-color), var(--accent-color));
    color: white;
    border: none;
}

.btn-secondary.btn-action-compact:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(var(--success-color-rgb), 0.3);
}

.btn-outline.btn-action-compact {
    background: transparent;
    color: var(--accent-color);
    border: 2px solid var(--accent-color);
}

.btn-outline.btn-action-compact:hover {
    background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(var(--accent-color-rgb), 0.3);
}

/* Animations */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes checkmarkDraw {
    from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes headerShine {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(100%) rotate(45deg); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

@keyframes qrPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
}

@keyframes qrShine {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Animation classes */
.animate-slide-left {
    animation: slideInLeft 0.8s ease-out;
}

.animate-slide-right {
    animation: slideInRight 0.8s ease-out;
}

.animate-fade-up {
    animation: fadeInUp 0.8s ease-out;
}

.animate-error {
    animation: shake 0.5s ease-in-out;
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Responsive design */
@media (max-width: 768px) {
    .confirmation-header {
        padding: 30px 20px 25px;
    }

    .confirmation-title {
        font-size: 2rem;
    }

    .confirmation-subtitle {
        font-size: 1rem;
    }

    .confirmation-content {
        padding: 20px 15px;
    }

    .info-card {
        padding: 15px;
        margin-bottom: 15px;
    }

    .qr-card-compact {
        padding: 15px;
        margin-bottom: 15px;
    }

    .button-group-compact {
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .btn-action-compact {
        width: 100%;
        max-width: 250px;
        justify-content: center;
    }

    .checkmark-circle {
        width: 60px;
        height: 60px;
    }

    .checkmark::before {
        font-size: 24px;
    }

    .card-header-compact {
        gap: 8px;
    }

    .card-icon-small {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }

    .card-title-compact {
        font-size: 1rem;
    }

    .detail-item-compact {
        gap: 8px;
        margin-bottom: 6px;
    }

    .qr-header-compact {
        gap: 8px;
    }

    .qr-icon-compact {
        font-size: 1.5rem;
    }

    .qr-code-text-compact {
        font-size: 0.8rem;
        padding: 6px 10px;
    }
}

/* Dark mode support - keeping light backgrounds for better visibility */
@media (prefers-color-scheme: dark) {
    .confirmation-card {
        background: linear-gradient(145deg, #1f2937, #111827);
    }

    .info-card {
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border-color: rgba(var(--accent-color-rgb), 0.2);
    }

    .card-title-compact,
    .detail-item-compact span {
        color: #1e293b;
    }

    .qr-card {
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border-color: rgba(var(--accent-color-rgb), 0.2);
    }

    .qr-code-display {
        background: linear-gradient(145deg, #f1f5f9, #e2e8f0);
        border-color: rgba(var(--accent-color-rgb), 0.3);
    }

    .qr-code-text-compact {
        background: linear-gradient(145deg, #f1f5f9, #e2e8f0);
        color: #1e293b;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>