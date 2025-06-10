<?php
require_once 'includes/auth.php';
$page_title = "Register - Event Booking System";
require_once 'includes/header.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['register'])) {
    // Debug: Log registration attempt
    error_log("Registration attempt received");
    error_log("POST data: " . print_r($_POST, true));

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = "Please fill in all required fields.";
        error_log("Registration failed: Missing required fields");
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
        error_log("Registration failed: Password too short");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
        error_log("Registration failed: Invalid email format");
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            // Check if username already exists
            $stmt2 = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt2->execute([$username]);

            if ($stmt->rowCount() > 0) {
                $error = "Email already exists. Please use a different email.";
                error_log("Registration failed: Email already exists");
            } elseif ($stmt2->rowCount() > 0) {
                $error = "Username already exists. Please choose a different username.";
                error_log("Registration failed: Username already exists");
            } else {
                // Insert new user
                $sql = "INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);

                if ($stmt->execute([$username, $email, $password_hash, $full_name, $phone])) {
                    error_log("Successful registration for user: " . $username);
                    error_log("Redirecting to login.php");

                    // Clear any output buffer
                    if (ob_get_level()) {
                        ob_end_clean();
                    }

                    // Simple redirect
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                    error_log("Registration failed: Database insert failed");
                }
            }
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            $error = "An error occurred during registration. Please try again.";
        }
    }
}

// Display flash messages if any
$flash = getFlashMessage();
?>

<!-- Enhanced Register Page Styles -->
<style>
/* Amazing Register Page Styles with Exciting Animations */
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.03"><polygon points="40,0 80,40 40,80 0,40"/></g></svg>') repeat;
    animation: backgroundFloat 25s linear infinite;
    pointer-events: none;
    z-index: 1;
}

@keyframes backgroundFloat {
    0% { transform: translateX(0) translateY(0) rotate(0deg); }
    100% { transform: translateX(80px) translateY(80px) rotate(360deg); }
}

.register-container {
    max-width: 550px;
    margin: 30px auto;
    position: relative;
    z-index: 2;
    padding: 20px;
}

.register-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px);
    border-radius: 30px;
    box-shadow:
        0 30px 60px rgba(0,0,0,0.2),
        0 0 0 1px rgba(255,255,255,0.1),
        inset 0 1px 0 rgba(255,255,255,0.2);
    overflow: hidden;
    position: relative;
    animation: cardEntrance 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    transform-origin: center;
}

@keyframes cardEntrance {
    0% {
        opacity: 0;
        transform: translateY(100px) scale(0.8) rotateX(30deg);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1) rotateX(0deg);
    }
}

.register-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: cardShimmer 4s infinite;
    pointer-events: none;
}

@keyframes cardShimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

.register-header {
    background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
    background-size: 300% 300%;
    animation: headerGradient 8s ease infinite;
    padding: 50px 40px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}

@keyframes headerGradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.register-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    animation: headerOrb 6s ease-in-out infinite;
    pointer-events: none;
}

@keyframes headerOrb {
    0%, 100% { transform: scale(0.5) rotate(0deg); opacity: 0.3; }
    50% { transform: scale(1.5) rotate(180deg); opacity: 0.1; }
}

.register-header h2 {
    margin: 0 0 10px 0;
    font-size: 2.5rem;
    font-weight: 800;
    position: relative;
    z-index: 2;
    text-shadow: 0 3px 15px rgba(0,0,0,0.3);
    animation: titleFloat 2s ease-in-out infinite;
}

@keyframes titleFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

.register-header p {
    margin: 0;
    font-size: 1.1rem;
    opacity: 0.9;
    position: relative;
    z-index: 2;
    animation: subtitleSlide 1s ease-out 0.5s both;
}

@keyframes subtitleSlide {
    0% {
        opacity: 0;
        transform: translateX(-30px);
    }
    100% {
        opacity: 0.9;
        transform: translateX(0);
    }
}

.register-header i {
    font-size: 3rem;
    margin-bottom: 15px;
    animation: iconBounce 2s ease-in-out infinite;
    display: block;
}

@keyframes iconBounce {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    25% { transform: translateY(-10px) rotate(5deg); }
    75% { transform: translateY(-5px) rotate(-5deg); }
}

.register-body {
    padding: 50px 40px;
    position: relative;
}

.form-group {
    position: relative;
    margin-bottom: 25px;
    animation: formSlideUp 0.6s ease-out both;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }
.form-group:nth-child(4) { animation-delay: 0.4s; }
.form-group:nth-child(5) { animation-delay: 0.5s; }
.form-group:nth-child(6) { animation-delay: 0.6s; }

@keyframes formSlideUp {
    0% {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.form-label {
    font-weight: 700;
    color: #4a5568;
    margin-bottom: 8px;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
}

.form-label i {
    color: var(--primary-color);
    font-size: 1.1rem;
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

.form-control.is-valid {
    border-color: #28a745;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

.form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
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

.password-strength {
    margin-top: 8px;
    animation: fadeIn 0.3s ease;
}

.strength-bar {
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 5px;
}

.strength-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #dc3545, #ffc107, #28a745);
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-text {
    font-size: 0.8rem;
    color: #6c757d;
    text-align: center;
}

.form-feedback {
    font-size: 0.85rem;
    margin-top: 5px;
    min-height: 20px;
    transition: all 0.3s ease;
}

.form-feedback.valid {
    color: #28a745;
}

.form-feedback.invalid {
    color: #dc3545;
}

.form-check {
    padding: 15px 20px;
    background: rgba(102, 126, 234, 0.05);
    border-radius: 15px;
    border: 2px solid rgba(102, 126, 234, 0.1);
    transition: all 0.3s ease;
}

.form-check:hover {
    background: rgba(102, 126, 234, 0.08);
    border-color: rgba(102, 126, 234, 0.2);
}

.form-check-input {
    width: 20px;
    height: 20px;
    margin-top: 0;
    margin-right: 10px;
    border: 2px solid var(--primary-color);
    border-radius: 5px;
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.form-check-label {
    font-size: 0.95rem;
    color: #4a5568;
    line-height: 1.4;
}

.terms-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.terms-link:hover {
    color: #224abe;
    text-decoration: underline;
}

.btn-register {
    height: 60px;
    font-weight: 700;
    font-size: 1.2rem;
    margin-top: 25px;
    border-radius: 15px;
    background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
    background-size: 300% 300%;
    border: none;
    color: white;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    animation: buttonSlideIn 0.6s ease-out 0.7s both;
}

@keyframes buttonSlideIn {
    0% {
        opacity: 0;
        transform: translateY(30px) scale(0.9);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.btn-register::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-register:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
    animation: gradientShift 2s ease infinite;
}

.btn-register:hover::before {
    left: 100%;
}

.btn-register:active {
    transform: translateY(-1px);
}

.links-section {
    text-align: center;
    margin-top: 30px;
    animation: linksSlideIn 0.6s ease-out 0.8s both;
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

.login-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    display: inline-block;
}

.login-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    left: 50%;
    background: linear-gradient(135deg, var(--primary-color), #667eea);
    transition: all 0.3s ease;
}

.login-link:hover {
    color: #224abe;
    transform: translateY(-2px);
}

.login-link:hover::after {
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

/* Responsive Design */
@media (max-width: 768px) {
    .register-container {
        margin: 20px auto;
        padding: 15px;
    }

    .register-body {
        padding: 30px 25px;
    }

    .register-header {
        padding: 40px 25px;
    }

    .register-header h2 {
        font-size: 2rem;
    }

    .form-control {
        height: 50px;
        padding: 12px 20px;
    }

    .password-toggle-btn {
        height: 50px;
    }

    .btn-register {
        height: 55px;
        font-size: 1.1rem;
    }
}
</style>

<div class="container register-container">
    <div class="register-card">
        <div class="register-header">
            <i class="bi bi-person-plus-fill"></i>
            <h2>Join Us Today</h2>
            <p>Create your account and start booking amazing events</p>
        </div>

        <div class="register-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <i class="bi bi-info-circle me-2"></i><?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="bi bi-person me-2"></i>Username
                            </label>
                            <input type="text" class="form-control" id="username" name="username"
                                   placeholder="Choose a unique username"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   required>
                            <div class="form-feedback"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="full_name" class="form-label">
                                <i class="bi bi-person-badge me-2"></i>Full Name
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   placeholder="Enter your full name"
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                   required>
                            <div class="form-feedback"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-2"></i>Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="Enter your email address"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                    <div class="form-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-2"></i>Password
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Create a strong password" required>
                        <button class="btn password-toggle-btn" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill"></div>
                        </div>
                        <div class="strength-text">Password strength</div>
                    </div>
                    <div class="form-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="bi bi-telephone me-2"></i>Phone Number
                    </label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           placeholder="Enter your phone number (optional)"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    <div class="form-feedback"></div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="terms-link">Terms of Service</a> and <a href="#" class="terms-link">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" name="register" class="btn btn-primary btn-register">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                </div>
            </form>

            <div class="links-section">
                <p class="mb-0">Already have an account? <a href="login.php" class="login-link">Sign In Here</a></p>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced Register Page JavaScript with Amazing Animations
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
    const form = document.getElementById('registerForm');
    const inputs = form.querySelectorAll('.form-control');

    inputs.forEach(input => {
        // Add focus animations
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
            this.parentElement.style.transition = 'transform 0.3s ease';

            // Add floating label effect
            const label = this.parentElement.querySelector('.form-label');
            if (label) {
                label.style.color = 'var(--primary-color)';
                label.style.transform = 'scale(1.05)';
            }
        });

        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';

            const label = this.parentElement.querySelector('.form-label');
            if (label) {
                label.style.color = '#4a5568';
                label.style.transform = 'scale(1)';
            }

            // Validate on blur
            validateField(this);
        });

        // Real-time validation
        input.addEventListener('input', function() {
            validateField(this);

            // Special handling for password strength
            if (this.id === 'password') {
                updatePasswordStrength(this.value);
            }
        });
    });

    // Field validation function (for visual feedback only, not blocking submission)
    function validateField(field) {
        const feedback = field.parentElement.querySelector('.form-feedback');
        if (!feedback) return true; // If no feedback element, don't validate

        let isValid = true;
        let message = '';

        // Only validate if field has content or is required and empty
        if (field.value.trim() === '') {
            if (field.hasAttribute('required')) {
                isValid = false;
                message = 'This field is required';
            } else {
                // Optional field, clear validation
                field.classList.remove('is-valid', 'is-invalid');
                feedback.textContent = '';
                feedback.className = 'form-feedback';
                return true;
            }
        } else {
            // Field has content, validate format
            if (field.type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    isValid = false;
                    message = 'Please enter a valid email address';
                }
            } else if (field.id === 'username') {
                if (field.value.length < 3) {
                    isValid = false;
                    message = 'Username must be at least 3 characters';
                } else if (!/^[a-zA-Z0-9_]+$/.test(field.value)) {
                    isValid = false;
                    message = 'Username can only contain letters, numbers, and underscores';
                }
            } else if (field.id === 'password') {
                if (field.value.length < 6) {
                    isValid = false;
                    message = 'Password must be at least 6 characters';
                }
            } else if (field.id === 'phone' && field.value.trim() !== '') {
                // More flexible phone validation
                const phoneRegex = /^[\+]?[\d\s\-\(\)]{7,}$/;
                if (!phoneRegex.test(field.value)) {
                    isValid = false;
                    message = 'Please enter a valid phone number';
                }
            }
        }

        // Update field appearance (visual feedback only)
        if (isValid && field.value.trim() !== '') {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
            feedback.textContent = '✓ Looks good!';
            feedback.className = 'form-feedback valid';
        } else if (!isValid) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            feedback.textContent = message;
            feedback.className = 'form-feedback invalid';
        } else {
            field.classList.remove('is-valid', 'is-invalid');
            feedback.textContent = '';
            feedback.className = 'form-feedback';
        }

        return isValid;
    }

    // Password strength indicator
    function updatePasswordStrength(password) {
        const strengthBar = document.querySelector('.strength-fill');
        const strengthText = document.querySelector('.strength-text');

        let strength = 0;
        let strengthLabel = 'Very Weak';

        if (password.length >= 6) strength += 20;
        if (password.length >= 8) strength += 20;
        if (/[a-z]/.test(password)) strength += 20;
        if (/[A-Z]/.test(password)) strength += 20;
        if (/[0-9]/.test(password)) strength += 10;
        if (/[^A-Za-z0-9]/.test(password)) strength += 10;

        if (strength >= 80) strengthLabel = 'Very Strong';
        else if (strength >= 60) strengthLabel = 'Strong';
        else if (strength >= 40) strengthLabel = 'Medium';
        else if (strength >= 20) strengthLabel = 'Weak';

        strengthBar.style.width = strength + '%';
        strengthText.textContent = `Password strength: ${strengthLabel}`;

        // Color coding
        if (strength >= 60) {
            strengthBar.style.background = '#28a745';
        } else if (strength >= 40) {
            strengthBar.style.background = '#ffc107';
        } else {
            strengthBar.style.background = '#dc3545';
        }
    }

    // Simple form submission with loading animation (no interference)
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('.btn-register');

        // Check terms checkbox only
        const termsCheckbox = document.getElementById('terms');
        if (!termsCheckbox.checked) {
            e.preventDefault();
            alert('Please accept the Terms of Service and Privacy Policy');
            termsCheckbox.focus();
            return false;
        }

        // Only add loading state, don't prevent submission
        setTimeout(() => {
            submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2" style="animation: spin 1s linear infinite;"></i>Creating Account...';
            submitBtn.disabled = true;
        }, 10);

        // Add CSS for spin animation
        if (!document.getElementById('spin-animation-register')) {
            const style = document.createElement('style');
            style.id = 'spin-animation-register';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }

        // Let the form submit naturally - no preventDefault() except for terms
    });

    // Add floating particles effect
    createFloatingParticles();

    // Add typing effect to header
    typeWriterEffect();
});

function createFloatingParticles() {
    const particleCount = 20;
    const body = document.body;

    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: fixed;
            width: ${Math.random() * 8 + 3}px;
            height: ${Math.random() * 8 + 3}px;
            background: rgba(255, 255, 255, ${Math.random() * 0.4 + 0.1});
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
            left: ${Math.random() * 100}vw;
            top: ${Math.random() * 100}vh;
            animation: float ${Math.random() * 15 + 15}s linear infinite;
        `;

        body.appendChild(particle);

        // Remove particle after animation
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, (Math.random() * 15 + 15) * 1000);
    }
}

// Add CSS for floating particles
const particleStyle = document.createElement('style');
particleStyle.textContent = `
    @keyframes float {
        0% {
            transform: translateY(100vh) rotate(0deg) scale(0);
            opacity: 0;
        }
        10% {
            opacity: 1;
            transform: translateY(90vh) rotate(36deg) scale(1);
        }
        90% {
            opacity: 1;
            transform: translateY(-10vh) rotate(324deg) scale(1);
        }
        100% {
            transform: translateY(-100vh) rotate(360deg) scale(0);
            opacity: 0;
        }
    }
`;
document.head.appendChild(particleStyle);

// Recreate particles every 25 seconds
setInterval(createFloatingParticles, 25000);

function typeWriterEffect() {
    const text = "Join Us Today";
    const element = document.querySelector('.register-header h2');
    if (!element) return;

    element.textContent = '';
    let i = 0;

    function typeWriter() {
        if (i < text.length) {
            element.textContent += text.charAt(i);
            i++;
            setTimeout(typeWriter, 100);
        }
    }

    setTimeout(typeWriter, 1000);
}
</script>

<?php require_once 'includes/footer.php'; ?>