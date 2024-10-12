<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Your database username
define('DB_PASS', '');      // Your database password
define('DB_NAME', 'parking_reservation_system');

// Application settings
define('SITE_NAME', 'ParkEase');
define('SITE_URL', 'http://localhost/parking-reservation-system');

// Start session
session_start();

// Set timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constant to prevent direct access to included files
define('ADMIN_PAGE', true);