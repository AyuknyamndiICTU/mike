<?php
// Include auth first (which handles session)
require_once 'includes/auth.php';

// Start output buffering
ob_start();

require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = null;
$flash = getFlashMessage();

if (isset($_POST['login'])) {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } 
    // Check rate limit (5 attempts per 5 minutes)
    else if (!checkRateLimit('login', 5, 300)) {
        $error = "Too many login attempts. Please try again later.";
    } else {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Reset rate limit on successful login
                unset($_SESSION['rate_limits']['login']);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['LAST_ACTIVITY'] = time();
                
                // Redirect to home page
                setFlashMessage('success', 'Welcome back, ' . $user['username'] . '!');
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid email or password";
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $error = "An error occurred during login. Please try again.";
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Set the page title
$page_title = "Login - Event Booking System";

// Include the header
require_once 'includes/header.php';
?>

<style>
.login-container {
    max-width: 500px;
    margin: 60px auto;
}

.login-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.login-header {
    background: linear-gradient(135deg, var(--primary-color), #224abe);
    padding: 30px;
    text-align: center;
    color: white;
}

.login-header h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 600;
    background: linear-gradient(135deg, var(--success-color) 0%, var(--accent-color) 50%, white 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    background-size: 200% 100%;
    animation: gradientShift 4s ease-in-out infinite;
    position: relative;
    display: inline-block;
    overflow: hidden;
}

.login-header h2::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.15), transparent);
    transform: rotate(45deg);
    animation: headerShine 4s infinite;
    pointer-events: none;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

@keyframes headerShine {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(100%) rotate(45deg); }
}

.login-body {
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

.password-toggle-btn {
    height: 50px;
    border-top-right-radius: 10px !important;
    border-bottom-right-radius: 10px !important;
    border: 2px solid #eef0f5;
    border-left: none;
    background: white;
    color: #6c757d;
    transition: all 0.3s ease;
}

.password-toggle-btn:hover {
    color: var(--primary-color);
}

.btn-login {
    height: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    margin-top: 20px;
}

.register-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.register-link:hover {
    color: #224abe;
    text-decoration: underline;
}
</style>

<div class="container login-container">
    <div class="login-card">
        <div class="login-header">
            <h2><i class="bi bi-person-circle me-2"></i>Login</h2>
        </div>
        
        <div class="login-body">
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
            
            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-4">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn password-toggle-btn" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" name="login" class="btn btn-primary btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p class="mb-2">Don't have an account? <a href="register.php" class="register-link">Register here</a></p>
                <p class="mb-0">Forgot your password? <a href="forgot-password.php" class="register-link">Reset it here</a></p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 