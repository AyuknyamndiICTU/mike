<?php
$page_title = "Shopping Cart - Event Booking System";
require_once 'includes/header.php';
require_once 'config/database.php';

// Require login
requireLogin();

// Get cart items
$sql = "SELECT c.*, e.title, e.event_date, e.event_time, e.price, e.available_seats 
        FROM cart c 
        JOIN events e ON c.event_id = e.id 
        WHERE c.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['quantity'] * $item['price'];
}

// Handle remove item
if (isset($_POST['remove_item']) && isset($_POST['cart_id'])) {
    try {
        $remove_sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $remove_stmt = $pdo->prepare($remove_sql);
        $remove_stmt->execute([$_POST['cart_id'], $_SESSION['user_id']]);
        
        setFlashMessage('success', 'Item removed from cart.');
        header("Location: cart.php");
        exit();
    } catch (PDOException $e) {
        error_log("Remove Cart Item Error: " . $e->getMessage());
        $error = "Failed to remove item from cart.";
    }
}

// Handle update quantity
if (isset($_POST['update_quantity']) && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    try {
        // Check if quantity is valid
        if ($_POST['quantity'] < 1) {
            throw new Exception("Quantity must be at least 1");
        }
        
        // Get event available seats
        $event_sql = "SELECT e.available_seats 
                     FROM cart c 
                     JOIN events e ON c.event_id = e.id 
                     WHERE c.id = ?";
        $event_stmt = $pdo->prepare($event_sql);
        $event_stmt->execute([$_POST['cart_id']]);
        $event = $event_stmt->fetch();
        
        if ($_POST['quantity'] > $event['available_seats']) {
            throw new Exception("Not enough seats available");
        }
        
        // Update quantity
        $update_sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$_POST['quantity'], $_POST['cart_id'], $_SESSION['user_id']]);
        
        setFlashMessage('success', 'Cart updated successfully.');
        header("Location: cart.php");
        exit();
    } catch (Exception $e) {
        error_log("Update Cart Error: " . $e->getMessage());
        $error = $e->getMessage();
    }
}
?>

<!-- Custom CSS -->
<style>
.cart-container {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 0 30px rgba(0,0,0,0.1);
    padding: 2rem;
    margin: 2rem auto;
}

.cart-header {
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
}

.cart-item {
    background: #fff;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
    animation: slideIn 0.5s ease-out;
}

.cart-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-btn {
    background: #f8f9fa;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.quantity-btn:hover {
    background: #e9ecef;
}

.quantity-input {
    width: 60px;
    text-align: center;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 0.25rem;
}

.remove-btn {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.remove-btn:hover {
    background: #c82333;
    transform: rotate(90deg);
}

.cart-summary {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    position: sticky;
    top: 20px;
}

.cart-total {
    font-size: 1.5rem;
    font-weight: bold;
    color: #0d6efd;
    margin-bottom: 1rem;
}

.empty-cart {
    text-align: center;
    padding: 3rem;
}

.empty-cart i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
}

.action-buttons .btn {
    flex: 1;
    padding: 0.75rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.event-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.event-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
}

.event-details {
    display: flex;
    gap: 1rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.price-info {
    text-align: right;
}

.unit-price {
    color: #6c757d;
    font-size: 0.9rem;
}

.subtotal {
    font-weight: 600;
    color: #0d6efd;
    font-size: 1.1rem;
}
</style>

<div class="container">
    <div class="cart-container">
        <div class="cart-header">
            <h1 class="mb-0 animated-gradient-header">Shopping Cart</h1>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="bi bi-cart-x"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted">Looks like you haven't added any events to your cart yet.</p>
                <a href="events.php" class="btn btn-primary mt-3">
                    <i class="bi bi-calendar-event"></i> Browse Events
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <?php foreach ($cart_items as $index => $item): ?>
                        <div class="cart-item" style="animation-delay: <?php echo $index * 0.1; ?>s">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="event-info">
                                        <div class="event-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <div class="event-details">
                                            <span><i class="bi bi-calendar"></i> <?php echo date('M d, Y', strtotime($item['event_date'])); ?></span>
                                            <span><i class="bi bi-clock"></i> <?php echo date('g:i A', strtotime($item['event_time'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="quantity-control">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 'decrease')">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['available_seats']; ?>"
                                               onchange="updateQuantity(<?php echo $item['id']; ?>, 'set', this.value)">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 'increase')">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="price-info">
                                        <div class="unit-price"><?php echo number_format($item['price'], 0); ?> FCFA each</div>
                                        <div class="subtotal"><?php echo number_format($item['price'] * $item['quantity'], 0); ?> FCFA</div>
                                    </div>
                                </div>
                                <div class="col-md-1 text-end">
                                    <button class="remove-btn" onclick="removeItem(<?php echo $item['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4>Order Summary</h4>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Items:</span>
                            <span><?php echo array_sum(array_column($cart_items, 'quantity')); ?></span>
                        </div>
                        <div class="cart-total d-flex justify-content-between">
                            <span>Total:</span>
                            <span><?php echo number_format($total, 0); ?> FCFA</span>
                        </div>
                        <div class="action-buttons">
                            <a href="events.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left"></i> Continue Shopping
                            </a>
                            <a href="checkout.php" class="btn btn-primary">
                                Proceed to Checkout <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
async function updateQuantity(cartId, action, value = null) {
    try {
        let quantity;
        const input = document.querySelector(`input[onchange*="${cartId}"]`);
        const currentQty = parseInt(input.value);
        const maxQty = parseInt(input.max);

        if (action === 'increase') {
            quantity = currentQty + 1;
        } else if (action === 'decrease') {
            quantity = currentQty - 1;
        } else {
            quantity = parseInt(value);
        }

        // Validate quantity
        if (quantity < 1) quantity = 1;
        if (quantity > maxQty) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Not enough tickets available!'
            });
            quantity = maxQty;
        }

        const response = await fetch('api/update-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ cart_id: cartId, quantity: quantity })
        });

        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to update quantity'
        });
    }
}

async function removeItem(cartId) {
    try {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "This item will be removed from your cart",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove it!'
        });

        if (result.isConfirmed) {
            const response = await fetch('api/remove-from-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cart_id: cartId })
            });

            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                throw new Error(data.message);
            }
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to remove item'
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?> 