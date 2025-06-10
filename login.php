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
    // Debug: Log the login attempt
    error_log("Login attempt received");
    error_log("POST data: " . print_r($_POST, true));

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
        error_log("Login failed: Empty fields");
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            error_log("User found: " . ($user ? 'Yes' : 'No'));

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['LAST_ACTIVITY'] = time();

                // Debug: Log successful login
                error_log("Successful login for user: " . $user['username']);
                error_log("Session set, redirecting to index.php");

                // Clear any output buffer
                if (ob_get_level()) {
                    ob_end_clean();
                }

                // Simple redirect
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid email or password";
                error_log("Login failed: Invalid credentials for email: " . $email);
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $error = "An error occurred during login. Please try again.";
        }
    }
}

// Generate CSRF token (simplified for testing)
$csrf_token = 'test_token_' . time();

// Set the page title
$page_title = "Login - Event Booking System";

// Include the header
require_once 'includes/header.php';
?>

<style>
/* Enhanced Login Page Styles with Amazing Animations */
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.05"><circle cx="30" cy="30" r="4"/></g></svg>') repeat;
    animation: backgroundMove 20s linear infinite;
    pointer-events: none;
    z-index: 1;
}

@keyframes backgroundMove {
    0% { transform: translateX(0) translateY(0); }
    100% { transform: translateX(60px) translateY(60px); }
}

.login-container {
    max-width: 450px;
    margin: 40px auto;
    position: relative;
    z-index: 2;
    padding: 20px;
}

.login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 25px;
    box-shadow:
        0 25px 50px rgba(0,0,0,0.15),
        0 0 0 1px rgba(255,255,255,0.1);
    overflow: hidden;
    position: relative;
    animation: cardSlideIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    transform-origin: center;
}

@keyframes cardSlideIn {
    0% {
        opacity: 0;
        transform: translateY(50px) scale(0.9);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: cardShine 3s infinite;
    pointer-events: none;
}

@keyframes cardShine {
    0% { left: -100%; }
    100% { left: 100%; }
}

.login-header {
    background: linear-gradient(135deg, var(--primary-color), #224abe, #667eea);
    background-size: 200% 200%;
    animation: gradientFlow 4s ease infinite;
    padding: 40px 30px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}

@keyframes gradientFlow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.login-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: headerPulse 4s ease-in-out infinite;
    pointer-events: none;
}

@keyframes headerPulse {
    0%, 100% { transform: scale(0.8) rotate(0deg); opacity: 0.3; }
    50% { transform: scale(1.2) rotate(180deg); opacity: 0.1; }
}

.login-header h2 {
    margin: 0;
    font-size: 2.2rem;
    font-weight: 700;
    position: relative;
    z-index: 2;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    animation: titleBounce 1s ease-out 0.3s both;
}

@keyframes titleBounce {
    0% {
        opacity: 0;
        transform: translateY(-30px) scale(0.8);
    }
    60% {
        opacity: 1;
        transform: translateY(5px) scale(1.05);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.login-header i {
    font-size: 2.5rem;
    margin-right: 15px;
    animation: iconSpin 2s ease-in-out infinite;
    display: inline-block;
}

@keyframes iconSpin {
    0%, 100% { transform: rotate(0deg) scale(1); }
    50% { transform: rotate(10deg) scale(1.1); }
}

.login-body {
    padding: 50px 40px;
    position: relative;
}

.form-group {
    position: relative;
    margin-bottom: 30px;
    animation: formSlideUp 0.6s ease-out both;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }

@keyframes formSlideUp {
    0% {
        opacity: 0;
        transform: translateY(30px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-label {
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 8px;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-control {
    height: 55px;
    border-radius: 15px;
    border: 2px solid #e2e8f0;
    padding: 15px 25px;
    font-size: 1rem;
    background: rgba(255,255,255,0.9);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    backdrop-filter: blur(10px);
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow:
        0 0 0 3px rgba(102, 126, 234, 0.1),
        0 10px 25px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
    background: white;
}

.form-control:hover {
    border-color: #cbd5e0;
    transform: translateY(-1px);
}

.input-group {
    position: relative;
}

.password-toggle-btn {
    height: 55px;
    border-top-right-radius: 15px !important;
    border-bottom-right-radius: 15px !important;
    border: 2px solid #e2e8f0;
    border-left: none;
    background: rgba(255,255,255,0.9);
    color: #6c757d;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    min-width: 60px;
}

.password-toggle-btn:hover {
    color: var(--primary-color);
    background: white;
    transform: scale(1.05);
}

.password-toggle-btn:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-login {
    height: 55px;
    font-weight: 700;
    font-size: 1.1rem;
    margin-top: 25px;
    border-radius: 15px;
    background: linear-gradient(135deg, var(--primary-color), #224abe, #667eea);
    background-size: 200% 200%;
    border: none;
    color: white;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    animation: buttonSlideIn 0.6s ease-out 0.4s both;
}

@keyframes buttonSlideIn {
    0% {
        opacity: 0;
        transform: translateY(20px) scale(0.9);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
    animation: gradientFlow 2s ease infinite;
}

.btn-login:hover::before {
    left: 100%;
}

.btn-login:active {
    transform: translateY(-1px);
}

.links-section {
    text-align: center;
    margin-top: 30px;
    animation: linksSlideIn 0.6s ease-out 0.5s both;
}

@keyframes linksSlideIn {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.register-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    display: inline-block;
}

.register-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    left: 50%;
    background: linear-gradient(135deg, var(--primary-color), #667eea);
    transition: all 0.3s ease;
}

.register-link:hover {
    color: #224abe;
    transform: translateY(-2px);
}

.register-link:hover::after {
    width: 100%;
    left: 0;
}

/* Alert Animations */
.alert {
    border-radius: 15px;
    border: none;
    padding: 15px 20px;
    margin-bottom: 25px;
    animation: alertSlideDown 0.5s ease-out;
    backdrop-filter: blur(10px);
}

@keyframes alertSlideDown {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-danger {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border-left: 4px solid #dc3545;
}

.alert-success {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
    border-left: 4px solid #28a745;
}
</style>

<div class="container login-container">
    <div class="login-card">
        <div class="login-header">
            <h2><i class="bi bi-person-circle"></i>Welcome Back</h2>
            <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 1rem;">Sign in to your account</p>
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

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-2"></i>Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="Enter your email address"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-2"></i>Password
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Enter your password" required>
                        <button class="btn password-toggle-btn" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" name="login" class="btn btn-primary btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </div>
            </form>

            <div class="links-section">
                <p class="mb-2">Don't have an account? <a href="register.php" class="register-link">Create Account</a></p>
                <p class="mb-0">Forgot your password? <a href="forgot-password.php" class="register-link">Reset Password</a></p>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced Login Page JavaScript with Animations
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality with animation
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const icon = this.querySelector('i');

        // Add click animation
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
            this.style.transform = 'scale(1)';
        }, 150);

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

    // Form validation with visual feedback
    const form = document.getElementById('loginForm');
    const inputs = form.querySelectorAll('.form-control');

    inputs.forEach(input => {
        // Add focus animations
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
            this.parentElement.style.transition = 'transform 0.3s ease';
        });

        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';

            // Validate on blur
            if (this.value.trim() !== '') {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else if (this.hasAttribute('required')) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });

        // Real-time validation
        input.addEventListener('input', function() {
            if (this.type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailRegex.test(this.value)) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            }
        });
    });

    // Simple form submission with loading animation (no interference)
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('.btn-login');

        // Only add loading state, don't prevent submission
        setTimeout(() => {
            submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2" style="animation: spin 1s linear infinite;"></i>Signing In...';
            submitBtn.disabled = true;
        }, 10);

        // Add CSS for spin animation
        if (!document.getElementById('spin-animation')) {
            const style = document.createElement('style');
            style.id = 'spin-animation';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }

        // Let the form submit naturally - no preventDefault()
    });

    // Add floating particles effect
    createFloatingParticles();
});

function createFloatingParticles() {
    const particleCount = 15;
    const body = document.body;

    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: fixed;
            width: ${Math.random() * 6 + 2}px;
            height: ${Math.random() * 6 + 2}px;
            background: rgba(255, 255, 255, ${Math.random() * 0.3 + 0.1});
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
            left: ${Math.random() * 100}vw;
            top: ${Math.random() * 100}vh;
            animation: float ${Math.random() * 10 + 10}s linear infinite;
        `;

        body.appendChild(particle);

        // Remove particle after animation
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, (Math.random() * 10 + 10) * 1000);
    }
}

// Add CSS for floating particles
const particleStyle = document.createElement('style');
particleStyle.textContent = `
    @keyframes float {
        0% {
            transform: translateY(100vh) rotate(0deg);
            opacity: 0;
        }
        10% {
            opacity: 1;
        }
        90% {
            opacity: 1;
        }
        100% {
            transform: translateY(-100vh) rotate(360deg);
            opacity: 0;
        }
    }

    .form-control.is-valid {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }
`;
document.head.appendChild(particleStyle);

// Recreate particles every 20 seconds
setInterval(createFloatingParticles, 20000);
</script>

<?php require_once 'includes/footer.php'; ?> 