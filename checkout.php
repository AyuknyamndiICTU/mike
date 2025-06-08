<?php
$page_title = "Checkout - Event Booking System";
require_once 'includes/header.php';
?>

<!-- Font Awesome for card icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php
require_once 'config/database.php';

// Require login
requireLogin();

// Check if user is logged in properly
if (!isset($_SESSION['user_id'])) {
    setFlashMessage('error', 'Please login to access checkout.');
    header("Location: login.php");
    exit();
}

// Get cart items
try {
    // First check what columns exist in events table
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

    // Check for seats columns
    if (in_array('available_seats', $columns)) {
        $event_columns[] = 'e.available_seats';
    } elseif (in_array('capacity', $columns)) {
        $event_columns[] = 'e.capacity as available_seats';
    }

    $sql = "SELECT c.*, " . implode(', ', $event_columns) . "
            FROM cart c
            JOIN events e ON c.event_id = e.id
            WHERE c.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);

    // Calculate total
    $total = 0;
    $cart_items = [];
    while ($item = $stmt->fetch()) {
        $item['subtotal'] = $item['quantity'] * $item['price'];
        $total += $item['subtotal'];
        $cart_items[] = $item;
    }

    if (empty($cart_items)) {
        setFlashMessage('error', 'Your cart is empty. Please add some events to your cart before checkout.');
        header("Location: cart.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Checkout Error: " . $e->getMessage());
    $error = "An error occurred while processing your checkout.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Validate required POST data
        if (empty($_POST['attendee_name']) || empty($_POST['attendee_email']) || empty($_POST['attendee_phone'])) {
            throw new Exception("Please fill in all required attendee information.");
        }

        // Create booking record for each cart item
        foreach ($cart_items as $item) {

            // Based on the actual table structure: user_id, event_id, quantity, total_amount, status
            $booking_sql = "INSERT INTO bookings (user_id, event_id, quantity, total_amount, status)
                           VALUES (?, ?, ?, ?, ?)";

            $booking_stmt = $pdo->prepare($booking_sql);
            $booking_stmt->execute([
                $_SESSION['user_id'],
                $item['event_id'],
                $item['quantity'],
                $item['subtotal'],
                'confirmed'
            ]);

            // Store attendee information in session for confirmation page
            $_SESSION['last_booking_attendee'] = [
                'name' => $_POST['attendee_name'],
                'email' => $_POST['attendee_email'],
                'phone' => $_POST['attendee_phone']
            ];

            // Update event available seats (check column name first)
            $check_seats_sql = "SHOW COLUMNS FROM events LIKE 'available_seats'";
            $check_seats_stmt = $pdo->prepare($check_seats_sql);
            $check_seats_stmt->execute();

            if ($check_seats_stmt->rowCount() > 0) {
                $update_sql = "UPDATE events
                              SET available_seats = available_seats - ?
                              WHERE id = ?";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([$item['quantity'], $item['event_id']]);
            } else {
                // Try alternative column name
                $check_capacity_sql = "SHOW COLUMNS FROM events LIKE 'capacity'";
                $check_capacity_stmt = $pdo->prepare($check_capacity_sql);
                $check_capacity_stmt->execute();

                if ($check_capacity_stmt->rowCount() > 0) {
                    $update_sql = "UPDATE events
                                  SET capacity = capacity - ?
                                  WHERE id = ?";
                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->execute([$item['quantity'], $item['event_id']]);
                }
            }
        }

        // Clear cart
        $clear_sql = "DELETE FROM cart WHERE user_id = ?";
        $clear_stmt = $pdo->prepare($clear_sql);
        $clear_stmt->execute([$_SESSION['user_id']]);

        // Commit transaction
        $pdo->commit();

        // Redirect to success page
        setFlashMessage('success', 'Your booking has been confirmed!');
        header("Location: booking-confirmation.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Booking Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());

        // More specific error messages for debugging
        if (strpos($e->getMessage(), 'attendee_name') !== false) {
            $error = "Database schema mismatch. Please contact administrator.";
        } elseif (strpos($e->getMessage(), 'ticket_type_id') !== false) {
            $error = "Missing ticket type information. Please try again.";
        } elseif (strpos($e->getMessage(), 'fill in all required') !== false) {
            $error = $e->getMessage();
        } elseif (strpos($e->getMessage(), 'payment_status') !== false) {
            $error = "Database column 'payment_status' not found. Please contact administrator.";
        } else {
            // Show the actual error message for debugging
            $error = "Booking error: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <!-- Checkout Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Checkout</h3>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" id="checkoutForm">
                        <!-- Attendee Information -->
                        <h5 class="mb-3">Attendee Information</h5>
                        <div class="mb-3">
                            <label for="attendee_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="attendee_name" name="attendee_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="attendee_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="attendee_email" name="attendee_email" required>
                        </div>

                        <div class="mb-3">
                            <label for="attendee_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="attendee_phone" name="attendee_phone" required>
                        </div>

                        <!-- Payment Information -->
                        <h5 class="mb-3 mt-4">Payment Information</h5>
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <div class="card-input-container">
                                <input type="text" class="form-control" id="card_number"
                                       placeholder="1234 5678 9012 3456"
                                       title="Enter your card number (Visa, MasterCard, American Express, etc.)"
                                       maxlength="23" required>
                                <div class="card-type-indicator" id="card_type_indicator">
                                    <span class="card-icons">
                                        <i class="fab fa-cc-visa" title="Visa"></i>
                                        <i class="fab fa-cc-mastercard" title="MasterCard"></i>
                                        <i class="fab fa-cc-amex" title="American Express"></i>
                                        <i class="fab fa-cc-discover" title="Discover"></i>
                                    </span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Accepts all major cards: Visa, MasterCard, American Express, Discover, Diners Club, JCB, UnionPay, and Maestro
                                <br><strong>Test cards:</strong> 4111111111111111 (Visa), 5555555555554444 (MasterCard), 378282246310005 (Amex)
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expiry" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiry"
                                       placeholder="MM/YY" pattern="^(0[1-9]|1[0-2])\/([0-9]{2})$"
                                       title="Please enter date in MM/YY format (e.g., 12/25)"
                                       maxlength="5" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv"
                                       pattern="[0-9]{3,4}" placeholder="123"
                                       title="Enter the 3 or 4 digit security code on your card"
                                       maxlength="4" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">
                            Pay <?php echo number_format($total, 0); ?> FCFA
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Enhanced Order Summary -->
        <div class="col-md-4">
            <div class="order-summary-card">
                <div class="order-summary-header">
                    <div class="summary-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h5 class="summary-title">Order Summary</h5>
                    <div class="summary-badge">
                        <?php echo count($cart_items); ?> item<?php echo count($cart_items) > 1 ? 's' : ''; ?>
                    </div>
                </div>

                <div class="order-summary-body">
                    <?php $delay = 0; foreach ($cart_items as $item): ?>
                        <div class="order-item animate-item" style="animation-delay: <?php echo $delay; ?>s">
                            <div class="item-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="item-details">
                                <h6 class="item-title"><?php echo htmlspecialchars($item['title']); ?></h6>
                                <div class="item-meta">
                                    <span class="quantity-badge">
                                        <i class="fas fa-times"></i>
                                        <?php echo $item['quantity']; ?>
                                    </span>
                                    <span class="ticket-type">ticket<?php echo $item['quantity'] > 1 ? 's' : ''; ?></span>
                                </div>
                            </div>
                            <div class="item-price">
                                <span class="price-amount"><?php echo number_format($item['subtotal'], 0); ?></span>
                                <span class="price-currency">FCFA</span>
                            </div>
                        </div>
                    <?php $delay += 0.1; endforeach; ?>
                </div>

                <div class="order-summary-footer">
                    <div class="summary-divider"></div>
                    <div class="total-section">
                        <div class="total-row">
                            <span class="total-label">
                                <i class="fas fa-calculator"></i>
                                Total Amount
                            </span>
                            <div class="total-amount">
                                <span class="total-price"><?php echo number_format($total, 0); ?></span>
                                <span class="total-currency">FCFA</span>
                            </div>
                        </div>
                        <div class="savings-info">
                            <i class="fas fa-info-circle"></i>
                            Secure payment processing
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Form validation styling */
.form-control:invalid {
    border-color: #dc3545;
}

.form-control:valid {
    border-color: #28a745;
}

.form-control:focus:invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-control:focus:valid {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Enhanced form styling */
.form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-color-rgb), 0.25);
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

/* Button styling */
.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), #224abe);
    border: none;
    border-radius: 10px;
    padding: 15px 30px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(var(--primary-color-rgb), 0.3);
}

.btn-primary:disabled {
    transform: none;
    opacity: 0.7;
}

/* Card input styling */
.card-input-container {
    position: relative;
}

.card-type-indicator {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    gap: 5px;
}

.card-icons {
    display: flex;
    gap: 8px;
    align-items: center;
}

.card-icons i {
    font-size: 1.5rem;
    opacity: 0.3;
    transition: all 0.3s ease;
    color: #666;
}

.card-icons i.active {
    opacity: 1;
    transform: scale(1.1);
    color: var(--primary-color);
}

.card-icons i.visa.active {
    color: #1a1f71;
}

.card-icons i.mastercard.active {
    color: #eb001b;
}

.card-icons i.amex.active {
    color: #006fcf;
}

.card-icons i.discover.active {
    color: #ff6000;
}

/* Enhanced form validation feedback */
.form-control.is-valid {
    border-color: #28a745;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.94-.94 2.94 2.94L7.83 7.09 8 7.26 5.17 10.09z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 1.4 1.4M7.2 4.6l-1.4 1.4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

/* Enhanced Order Summary Styling */
.order-summary-card {
    background: linear-gradient(145deg, #ffffff, #f8f9fc);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
    position: relative;
    border: none;
    animation: slideInRight 0.6s ease-out;
}

.order-summary-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), #224abe, #28a745);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
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

.order-summary-header {
    background: linear-gradient(135deg, var(--primary-color), #224abe);
    color: white;
    padding: 25px;
    position: relative;
    overflow: hidden;
}

.order-summary-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg);
    animation: headerShine 3s infinite;
}

@keyframes headerShine {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(100%) rotate(45deg); }
}

.summary-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    margin-bottom: 15px;
    animation: iconPulse 2s infinite;
}

@keyframes iconPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.summary-icon i {
    font-size: 1.5rem;
    color: white;
}

.summary-title {
    margin: 0 0 10px 0;
    font-weight: 700;
    font-size: 1.4rem;
    position: relative;
    z-index: 1;
}

.summary-badge {
    background: rgba(255,255,255,0.2);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    display: inline-block;
    position: relative;
    z-index: 1;
}

.order-summary-body {
    padding: 25px;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    margin-bottom: 15px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.order-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(to bottom, var(--primary-color), #28a745);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.order-item:hover::before {
    transform: scaleY(1);
}

.order-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.animate-item {
    opacity: 0;
    animation: slideInUp 0.6s ease-out forwards;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.item-icon {
    flex-shrink: 0;
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, var(--primary-color), #224abe);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    animation: iconRotate 4s infinite linear;
}

@keyframes iconRotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.item-details {
    flex: 1;
}

.item-title {
    font-weight: 600;
    color: #333;
    margin: 0 0 8px 0;
    font-size: 1rem;
    line-height: 1.3;
}

.item-meta {
    display: flex;
    align-items: center;
    gap: 10px;
}

.quantity-badge {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.ticket-type {
    color: #666;
    font-size: 0.85rem;
    font-style: italic;
}

.item-price {
    text-align: right;
    flex-shrink: 0;
}

.price-amount {
    display: block;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
}

.price-currency {
    font-size: 0.8rem;
    color: #666;
    font-weight: 500;
}

.order-summary-footer {
    padding: 0 25px 25px;
}

.summary-divider {
    height: 2px;
    background: linear-gradient(90deg, var(--primary-color), #224abe, #28a745);
    margin: 20px 0;
    border-radius: 2px;
    animation: dividerGlow 2s infinite alternate;
}

@keyframes dividerGlow {
    from { box-shadow: 0 0 5px rgba(var(--primary-color-rgb), 0.3); }
    to { box-shadow: 0 0 15px rgba(var(--primary-color-rgb), 0.6); }
}

.total-section {
    background: linear-gradient(135deg, #f8f9fc, #ffffff);
    padding: 20px;
    border-radius: 15px;
    border: 2px solid #eef0f5;
    position: relative;
    overflow: hidden;
}

.total-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(var(--primary-color-rgb), 0.1), transparent);
    animation: totalShine 3s infinite;
}

@keyframes totalShine {
    0% { left: -100%; }
    100% { left: 100%; }
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    position: relative;
    z-index: 1;
}

.total-label {
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.1rem;
}

.total-label i {
    color: var(--primary-color);
    animation: iconBounce 2s infinite;
}

@keyframes iconBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

.total-amount {
    text-align: right;
}

.total-price {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--primary-color);
    display: block;
    line-height: 1;
    animation: priceGlow 2s infinite alternate;
}

@keyframes priceGlow {
    from { text-shadow: 0 0 5px rgba(var(--primary-color-rgb), 0.3); }
    to { text-shadow: 0 0 15px rgba(var(--primary-color-rgb), 0.6); }
}

.total-currency {
    font-size: 1rem;
    color: #666;
    font-weight: 600;
}

.savings-info {
    color: #28a745;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    position: relative;
    z-index: 1;
}

.savings-info i {
    animation: infoSpin 4s infinite linear;
}

@keyframes infoSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .card-icons i {
        font-size: 1.2rem;
        gap: 5px;
    }

    .card-type-indicator {
        right: 10px;
    }

    .order-summary-header {
        padding: 20px;
    }

    .order-summary-body {
        padding: 20px;
    }

    .order-item {
        padding: 15px;
        gap: 12px;
    }

    .item-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .total-price {
        font-size: 1.5rem;
    }

    .summary-title {
        font-size: 1.2rem;
    }
}
</style>

<script>
// Enhanced order summary animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate order items on load
    const orderItems = document.querySelectorAll('.order-item');
    orderItems.forEach((item, index) => {
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Add hover effects to order items
    orderItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
            this.style.boxShadow = '0 15px 35px rgba(0,0,0,0.15)';
        });

        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.05)';
        });
    });

    // Add subtle pulse to order summary card
    const orderSummaryCard = document.querySelector('.order-summary-card');
    if (orderSummaryCard) {
        setInterval(() => {
            orderSummaryCard.style.transform = 'scale(1.005)';
            setTimeout(() => {
                orderSummaryCard.style.transform = 'scale(1)';
            }, 1000);
        }, 5000);
    }

    // Animate total price on load
    const totalPrice = document.querySelector('.total-price');
    if (totalPrice) {
        const finalValue = totalPrice.textContent;
        const numericValue = parseInt(finalValue.replace(/,/g, ''));
        let currentValue = 0;
        const increment = numericValue / 50;

        const countUp = setInterval(() => {
            currentValue += increment;
            if (currentValue >= numericValue) {
                currentValue = numericValue;
                clearInterval(countUp);
            }
            totalPrice.textContent = Math.floor(currentValue).toLocaleString();
        }, 30);
    }
});

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Enhanced payment processing animation
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    const orderSummary = document.querySelector('.order-summary-card');

    // Add processing state to order summary
    orderSummary.style.transform = 'scale(0.98)';
    orderSummary.style.opacity = '0.8';

    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing Payment...';
    button.style.background = 'linear-gradient(135deg, #28a745, #20c997)';

    setTimeout(() => {
        this.submit();
    }, 2000);
});

// Card number validation and formatting
function detectCardType(number) {
    const patterns = {
        visa: /^4[0-9]{12}(?:[0-9]{3})?$/,
        mastercard: /^5[1-5][0-9]{14}$/,
        amex: /^3[47][0-9]{13}$/,
        discover: /^6(?:011|5[0-9]{2})[0-9]{12}$/,
        diners: /^3[0689][0-9]{11}$/,
        jcb: /^(?:2131|1800|35\d{3})\d{11}$/,
        unionpay: /^(62|88)[0-9]{14,17}$/,
        maestro: /^(5018|5020|5038|6304|6759|6761|6763)[0-9]{8,15}$/
    };

    for (let type in patterns) {
        if (patterns[type].test(number)) {
            return type;
        }
    }
    return null;
}

function formatCardNumber(value) {
    // Remove all non-digits
    const cleaned = value.replace(/\D/g, '');

    // Detect card type for proper formatting
    const cardType = detectCardType(cleaned);

    // Format based on card type
    if (cardType === 'amex') {
        // American Express: 4-6-5 format
        return cleaned.replace(/(\d{4})(\d{6})(\d{5})/, '$1 $2 $3');
    } else if (cardType === 'diners') {
        // Diners Club: 4-6-4 format
        return cleaned.replace(/(\d{4})(\d{6})(\d{4})/, '$1 $2 $3');
    } else {
        // Most cards: 4-4-4-4 format
        return cleaned.replace(/(\d{4})(?=\d)/g, '$1 ');
    }
}

function validateCardNumber(number) {
    // Remove spaces and check length
    const cleaned = number.replace(/\s/g, '');

    // Check if it's all digits
    if (!/^\d+$/.test(cleaned)) {
        return false;
    }

    // Check length (13-19 digits for most cards)
    if (cleaned.length < 13 || cleaned.length > 19) {
        return false;
    }

    // Luhn algorithm validation
    let sum = 0;
    let isEven = false;

    for (let i = cleaned.length - 1; i >= 0; i--) {
        let digit = parseInt(cleaned.charAt(i));

        if (isEven) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }

        sum += digit;
        isEven = !isEven;
    }

    return sum % 10 === 0;
}

// This is handled in the combined input listener below

// Add comprehensive validation for card number
document.getElementById('card_number').addEventListener('blur', function(e) {
    const value = this.value.trim();

    if (!value) {
        this.setCustomValidity('Please enter your card number');
        return;
    }

    if (!validateCardNumber(value)) {
        this.setCustomValidity('Please enter a valid card number');
        return;
    }

    // If we get here, the card is valid
    this.setCustomValidity('');

    // Show detected card type visually
    const cleaned = value.replace(/\s/g, '');
    const cardType = detectCardType(cleaned);
    updateCardTypeIndicator(cardType);

    // Add visual validation feedback
    if (validateCardNumber(value)) {
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    } else {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    }
});

function updateCardTypeIndicator(cardType) {
    const icons = document.querySelectorAll('.card-icons i');

    // Reset all icons
    icons.forEach(icon => {
        icon.classList.remove('active');
        icon.classList.remove('visa', 'mastercard', 'amex', 'discover');
    });

    // Highlight the detected card type
    if (cardType) {
        const iconMap = {
            'visa': 'fa-cc-visa',
            'mastercard': 'fa-cc-mastercard',
            'amex': 'fa-cc-amex',
            'discover': 'fa-cc-discover'
        };

        const targetIcon = document.querySelector(`.card-icons .${iconMap[cardType]}`);
        if (targetIcon) {
            targetIcon.classList.add('active', cardType);
        }
    }
}

// Also update card type indicator during input
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '');

    // Limit to 19 digits (longest card number)
    if (value.length > 19) value = value.slice(0, 19);

    // Format the number
    this.value = formatCardNumber(value);

    // Update card type indicator in real-time
    const cardType = detectCardType(value);
    updateCardTypeIndicator(cardType);

    // Clear any previous validation messages while typing
    this.setCustomValidity('');
    this.classList.remove('is-valid', 'is-invalid');
});

// Format expiry date input
document.getElementById('expiry').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '');

    // Limit to 4 digits
    if (value.length > 4) value = value.slice(0, 4);

    // Add slash after month
    if (value.length > 2) {
        value = value.slice(0, 2) + '/' + value.slice(2);
    }

    this.value = value;
});

// Add validation on blur for expiry date
document.getElementById('expiry').addEventListener('blur', function(e) {
    const value = this.value;
    const pattern = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;

    if (value && !pattern.test(value)) {
        this.setCustomValidity('Please enter a valid date in MM/YY format (e.g., 12/25)');
    } else if (value) {
        // Check if the date is not in the past
        const [month, year] = value.split('/');
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear() % 100; // Get last 2 digits
        const currentMonth = currentDate.getMonth() + 1;

        const inputYear = parseInt(year);
        const inputMonth = parseInt(month);

        if (inputYear < currentYear || (inputYear === currentYear && inputMonth < currentMonth)) {
            this.setCustomValidity('Please enter a future expiry date');
        } else {
            this.setCustomValidity('');
        }
    } else {
        this.setCustomValidity('');
    }
});

// Format CVV input
document.getElementById('cvv').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '');
    if (value.length > 4) value = value.slice(0, 4);
    this.value = value;
});

// Add validation for CVV
document.getElementById('cvv').addEventListener('blur', function(e) {
    const value = this.value;
    if (value && (value.length < 3 || value.length > 4)) {
        this.setCustomValidity('CVV must be 3 or 4 digits');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 