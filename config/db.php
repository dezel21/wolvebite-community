<?php
/** Wolvebite Community - Database Configuration
 * Konfigurasi koneksi database MySQL
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wolvebite_db');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Base URL for the website
define('BASE_URL', 'http://localhost/pemweb/');
define('SITE_URL', 'http://localhost/pemweb');

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Session start if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>