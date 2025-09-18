<?php
/**
 * Main entry point for Project Management System
 */

// Check if application is installed
if (!file_exists('config.php')) {
    header('Location: install.php');
    exit();
}

// Load configuration and includes
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/functions.php';

// Start session
startSession();

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to dashboard if logged in
    header('Location: dashboard.php');
} else {
    // Redirect to login if not logged in
    header('Location: login.php');
}
exit();
?>