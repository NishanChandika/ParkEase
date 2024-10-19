<?php
// Database configuration
define('DB_HOST', '74.225.254.113');  // External database IP address
define('DB_USER', 'root');            // Your database username
define('DB_PASS', '');                // Your database password (please replace with actual password)
define('DB_NAME', 'parking_reservation_system');  // Your database name

// Application settings
define('SITE_NAME', 'ParkEase');
define('SITE_URL', 'https://parkease-bwdwc6b8bhbwgzg0.centralindia-01.azurewebsites.net/');

// Start session
session_start();

// Set timezone to UTC for consistency across different regions
date_default_timezone_set('UTC');

// Error reporting
// For development, display all errors. Turn off in production environment.
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // In production, hide errors for security purposes.
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Define constant to prevent direct access to included files
define('ADMIN_PAGE', true);

// Database connection (Optional: You can handle connection here or separately in another file)
function db_connect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }
    return $conn;
}