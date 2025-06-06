<?php
require_once 'includes/auth.php';
$page_title = "Forgot Password - Event Booking System";
require_once 'includes/header.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['reset'])) {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } 
    // Check rate limit (3 attempts per 15 minutes)
    else if (!checkRateLimit('password_reset', 3, 900)) {
        $error = "Too many reset attempts. Please try again later.";
    } else {
        $email = sanitize($_POST['email']);
        
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate unique token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database
                $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expires]);
                
                // Send reset email
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/mike/reset-password.php?token=" . $token;
                $to = $email;
                $subject = "Password Reset Request";
                $message = "Hello " . $user['username'] . ",\n\n";
                $message .= "You have requested to reset your password. Click the link below to reset it:\n\n";
                $message .= $resetLink . "\n\n";
                $message .= "This link will expire in 1 hour.\n\n";
                $message .= "If you did not request this reset, please ignore this email.\n\n";
                $message .= "Best regards,\nEvent Booking System";
                $headers = "From: noreply@eventbooking.com";
                
                mail($to, $subject, $message, $headers);
                
                setFlashMessage('success', 'If an account exists with that email, you will receive password reset instructions.');
                header("Location: login.php");
                exit();
            } else {
                // For security, show same message even if email doesn't exist
                setFlashMessage('success', 'If an account exists with that email, you will receive password reset instructions.');
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Password Reset Error: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Display flash messages if any
$flash = getFlashMessage();
?>

<style>
.reset-container {
    max-width: 500px;
    margin: 60px auto;
}

.reset-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.reset-header {
    background: linear-gradient(135deg, var(--primary-color), #224abe);
    padding: 30px;
    text-align: center;
    color: white;
}

.reset-header h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 600;
}

.reset-body {
    padding: 40px;
}

.form-control {
    height: 50px;
    border-radius: 10px;
    border: 2px solid #eef0f5;
    padding: 10px 20px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-color-rgb), 0.15);
}

.btn-reset {
    height: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    margin-top: 20px;
}

.login-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.login-link:hover {
    color: #224abe;
    text-decoration: underline;
}
</style>

<div class="container reset-container">
    <div class="reset-card">
        <div class="reset-header">
            <h2><i class="bi bi-key me-2"></i>Reset Password</h2>
        </div>
        
        <div class="reset-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <i class="bi bi-info-circle me-2"></i><?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>
            
            <p class="text-muted mb-4">
                Enter your email address and we'll send you instructions to reset your password.
            </p>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-4">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                </div>
                
                <div class="d-grid">
                    <button type="submit" name="reset" class="btn btn-primary btn-reset">
                        <i class="bi bi-envelope me-2"></i>Send Reset Instructions
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p class="mb-0">Remember your password? <a href="login.php" class="login-link">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 