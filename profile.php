<?php
require_once 'includes/auth.php';
$page_title = "My Profile - Event Booking System";
require_once 'includes/header.php';
require_once 'config/database.php';

// Require login to access profile
requireLogin();

$user_id = getCurrentUserId();
$success = false;
$error = null;

// Get current user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Profile Error: " . $e->getMessage());
    $error = "Failed to load profile data. Please try again.";
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    
    try {
        // Check if email is taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        
        // Check if username is taken by another user
        $stmt2 = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt2->execute([$username, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Email already exists";
        } elseif ($stmt2->rowCount() > 0) {
            $error = "Username already exists";
        } else {
            // Update user data
            $sql = "UPDATE users SET username = ?, email = ?, full_name = ?, phone = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$username, $email, $full_name, $phone, $user_id])) {
                $_SESSION['username'] = $username; // Update session username
                setFlashMessage('success', 'Profile updated successfully!');
                header("Location: profile.php");
                exit();
            } else {
                $error = "Failed to update profile. Please try again.";
            }
        }
    } catch (PDOException $e) {
        error_log("Profile Update Error: " . $e->getMessage());
        $error = "An error occurred while updating your profile. Please try again.";
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match";
    } else {
        try {
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($stmt->execute([$hashed_password, $user_id])) {
                    setFlashMessage('success', 'Password updated successfully!');
                    header("Location: profile.php");
                    exit();
                } else {
                    $error = "Failed to update password. Please try again.";
                }
            } else {
                $error = "Current password is incorrect";
            }
        } catch (PDOException $e) {
            error_log("Password Update Error: " . $e->getMessage());
            $error = "An error occurred while updating your password. Please try again.";
        }
    }
}
?>

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

.card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}
</style>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">My Profile</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Profile Information Form -->
                <form method="POST" action="" class="mb-5">
                    <h4 class="mb-3">Profile Information</h4>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>

                <!-- Change Password Form -->
                <form method="POST" action="">
                    <h4 class="mb-3">Change Password</h4>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="change_password" class="btn btn-secondary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 