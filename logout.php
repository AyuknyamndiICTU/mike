<?php
require_once 'includes/auth.php';
logoutUser();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page with success message
session_start(); // Start a new session to set flash message
setFlashMessage('success', 'You have been successfully logged out.');
header("Location: login.php");
exit(); 