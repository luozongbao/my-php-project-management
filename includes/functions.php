<?php
/**
 * Common utility functions
 */

// Start session if not already started
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Require login - redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Get current user ID
function getCurrentUserId() {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = Database::getInstance();
    $user = $db->fetchOne(
        "SELECT id, name, username, email, created_at FROM users WHERE id = ?",
        [getCurrentUserId()]
    );
    return $user;
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Generate random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Convert UTC datetime to user timezone
function formatDateTime($utc_datetime, $format = 'Y-m-d H:i:s') {
    if (empty($utc_datetime)) return '';
    
    $utc = new DateTime($utc_datetime, new DateTimeZone('UTC'));
    $userTimezone = new DateTimeZone($_SESSION['timezone'] ?? TIMEZONE);
    $utc->setTimezone($userTimezone);
    return $utc->format($format);
}

// Convert user timezone datetime to UTC for storage
function toUTC($datetime, $user_timezone = null) {
    if (empty($datetime)) return null;
    
    $timezone = $user_timezone ?? $_SESSION['timezone'] ?? TIMEZONE;
    $dt = new DateTime($datetime, new DateTimeZone($timezone));
    $dt->setTimezone(new DateTimeZone('UTC'));
    return $dt->format('Y-m-d H:i:s');
}

// Redirect with message
function redirect($url, $message = null, $type = 'info') {
    if ($message) {
        startSession();
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Display flash message
function displayMessage() {
    startSession();
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        return "<div class='alert alert-{$type}'>{$message}</div>";
    }
    return '';
}

// Calculate completion percentage
function calculateCompletionPercentage($completed_items, $total_items) {
    if ($total_items == 0) return 0;
    return round(($completed_items / $total_items) * 100, 1);
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Format date for HTML date input
function formatDateForInput($utc_datetime) {
    if (empty($utc_datetime)) return '';
    $utc = new DateTime($utc_datetime, new DateTimeZone('UTC'));
    $userTimezone = new DateTimeZone($_SESSION['timezone'] ?? TIMEZONE);
    $utc->setTimezone($userTimezone);
    return $utc->format('Y-m-d');
}

// Escape output for HTML
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>