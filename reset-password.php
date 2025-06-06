<?php
require_once 'includes/auth.php';
$page_title = "Reset Password - Event Booking System";
require_once 'includes/header.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Validate token
$token = $_GET['token'] ?? '';
if (!$token) {
    setFlashMessage('error', 'Invalid or missing reset token.');
    header("Location: login.php");
    exit();
}

try {
    // Check if token exists and is valid
    $stmt = $pdo->prepare("
        SELECT pr.*, u.email, u.username 
        FROM password_resets pr 
        JOIN users u ON pr.user_id = u.id 
        WHERE pr.token = ? 
        AND pr.expires_at > NOW() 
        AND pr.used = FALSE 
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        setFlashMessage('error', 'Invalid or expired reset token.');
        header("Location: login.php");
        exit();
    }

    if (isset($_POST['reset'])) {
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = "Invalid request. Please try again.";
        } else {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            // Validate password
            if (strlen($password) < 8) {
                $error = "Password must be at least 8 characters long.";
            } else if ($password !== $confirm_password) {
                $error = "Passwords do not match.";
            } else if (!preg_match("/[A-Z]/", $password) || 
                      !preg_match("/[a-z]/", $password) || 
                      !preg_match("/[0-9]/", $password)) {
                $error = "Password must contain at least one uppercase letter, one lowercase letter, and one number.";
            } else {
                try {
                    // Update password
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hash, $reset['user_id']]);

                    // Mark token as used
                    $stmt = $pdo->prepare("UPDATE password_resets SET used = TRUE WHERE id = ?");
                    $stmt->execute([$reset['id']]);

                    setFlashMessage('success', 'Your password has been reset successfully. Please login with your new password.');
                    header("Location: login.php");
                    exit();
                } catch (PDOException $e) {
                    error_log("Password Reset Error: " . $e->getMessage());
                    $error = "An error occurred. Please try again later.";
                }
            }
        }
    }
} catch (PDOException $e) {
    error_log("Password Reset Error: " . $e->getMessage());
    setFlashMessage('error', 'An error occurred. Please try again later.');
    header("Location: login.php");
    exit();
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

.btn-reset {
    height: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    margin-top: 20px;
}

.requirements {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 0.5rem;
}

.requirement {
    display: flex;
    align-items: center;
    margin-bottom: 0.25rem;
}

.requirement i {
    margin-right: 0.5rem;
    font-size: 0.8rem;
}

.requirement.met {
    color: var(--success-color);
}
</style>

<div class="container reset-container">
    <div class="reset-card">
        <div class="reset-header">
            <h2><i class="bi bi-shield-lock me-2"></i>Reset Password</h2>
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
                Hi <?php echo htmlspecialchars($reset['username']); ?>, please choose your new password.
            </p>
            
            <form method="POST" action="" id="resetForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-4">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn password-toggle-btn" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="requirements mt-2">
                        <div class="requirement" id="length">
                            <i class="bi bi-circle"></i>At least 8 characters
                        </div>
                        <div class="requirement" id="uppercase">
                            <i class="bi bi-circle"></i>One uppercase letter
                        </div>
                        <div class="requirement" id="lowercase">
                            <i class="bi bi-circle"></i>One lowercase letter
                        </div>
                        <div class="requirement" id="number">
                            <i class="bi bi-circle"></i>One number
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <button class="btn password-toggle-btn" type="button" id="toggleConfirmPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" name="reset" class="btn btn-primary btn-reset">
                        <i class="bi bi-check-circle me-2"></i>Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Password toggle functionality
function togglePasswordVisibility(inputId, buttonId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

document.getElementById('togglePassword').addEventListener('click', () => {
    togglePasswordVisibility('password', 'togglePassword');
});

document.getElementById('toggleConfirmPassword').addEventListener('click', () => {
    togglePasswordVisibility('confirm_password', 'toggleConfirmPassword');
});

// Password requirements validation
const password = document.getElementById('password');
const requirements = {
    length: { regex: /.{8,}/, element: document.getElementById('length') },
    uppercase: { regex: /[A-Z]/, element: document.getElementById('uppercase') },
    lowercase: { regex: /[a-z]/, element: document.getElementById('lowercase') },
    number: { regex: /[0-9]/, element: document.getElementById('number') }
};

password.addEventListener('input', () => {
    const value = password.value;
    
    for (const [key, requirement] of Object.entries(requirements)) {
        const isValid = requirement.regex.test(value);
        const icon = requirement.element.querySelector('i');
        
        if (isValid) {
            requirement.element.classList.add('met');
            icon.classList.remove('bi-circle');
            icon.classList.add('bi-check-circle-fill');
        } else {
            requirement.element.classList.remove('met');
            icon.classList.remove('bi-check-circle-fill');
            icon.classList.add('bi-circle');
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 