<?php
// PHELS Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'phels_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// OpenStreetMap Configuration (Free Alternative to Google Maps)
define('USE_OPENSTREETMAP', true);
define('LEAFLET_VERSION', '1.9.4');

// Application Configuration
define('APP_NAME', 'PHELS - Public Health Emergency Logistics System');
define('APP_URL', 'http://localhost/PHELS%20Project');

// Session Configuration - These must be set before session_start()
// ini_set('session.cookie_httponly', 1);
// ini_set('session.use_only_cookies', 1);
// ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
