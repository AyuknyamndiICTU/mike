<?php
// Only configure session if it hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    // Configure session settings
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', '3600');
    
    // Start the session
    session_start();
}

// CSRF Protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate Limiting
function checkRateLimit($key, $limit = 5, $interval = 300) {
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = ['count' => 0, 'first_attempt' => time()];
    }
    
    $rate = &$_SESSION['rate_limits'][$key];
    
    if (time() - $rate['first_attempt'] > $interval) {
        $rate = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    if ($rate['count'] >= $limit) {
        return false;
    }
    
    $rate['count']++;
    return true;
}

// Check if user is logged in
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check if session is expired
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 3600)) {
        logoutUser();
        return false;
    }
    
    // Update last activity time
    $_SESSION['LAST_ACTIVITY'] = time();
    return true;
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get current username
function getCurrentUsername() {
    return $_SESSION['username'] ?? '';
}

// Set flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get and clear flash message
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to access this page');
        header("Location: /mike/login.php");
        exit();
    }
}

// Function to require admin access
function requireAdmin() {
    requireLogin(); // First check if logged in
    if (!isAdmin()) {
        setFlashMessage('error', 'Access denied. Admin privileges required.');
        header("Location: /mike/index.php");
        exit();
    }
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to logout user
function logoutUser() {
    // Clear all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();

    // Start new session for flash message
    session_start();
    setFlashMessage('success', 'You have been successfully logged out.');
    
    // Redirect to login page
    header("Location: /mike/login.php");
    exit();
}
?> 