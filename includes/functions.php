<?php
/**
 * Wolvebite Community - Helper Functions
 */

require_once __DIR__ . '/../config/db.php';

// ========================================
// Authentication Helpers
// ========================================

/** Check if user is logged in*/
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/** Check if current user is admin */
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/** Require login - redirect to login page if not logged in */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/** Require admin - redirect if not admin */
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

/** Get current user data */
function getCurrentUser()
{
    global $conn;
    if (!isLoggedIn())
        return null;

    $user_id = $_SESSION['user_id'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
    return mysqli_fetch_assoc($result);
}


// Security Helpers

/** Sanitize input to prevent XSS */
function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/** Escape for SQL (use prepared statements when possible) */
function escapeSQL($conn, $input)
{
    return mysqli_real_escape_string($conn, trim($input));
}

// Formatting Helpers

/** Format price in Indonesian Rupiah */
function formatRupiah($amount)
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/** Format date in Indonesian format */
function formatDate($date)
{
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[(int) date('m', $timestamp)];
    $year = date('Y', $timestamp);
    return "$day $month $year";
}

/** Format time */
function formatTime($time)
{
    return date('H:i', strtotime($time));
}

/** Generate unique order number */
function generateOrderNumber()
{
    return 'WLV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// ========================================
// Cart Helpers
// ========================================

/**Get cart count for current user */
function getCartCount()
{
    global $conn;
    if (!isLoggedIn())
        return 0;

    $user_id = $_SESSION['user_id'];
    $result = mysqli_query($conn, "SELECT SUM(quantity) as total FROM cart_items WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

/**Get cart items for current user */
function getCartItems()
{
    global $conn;
    if (!isLoggedIn())
        return [];

    $user_id = $_SESSION['user_id'];
    $query = "SELECT ci.*, p.name, p.price, p.image, p.stock 
              FROM cart_items ci 
              JOIN products p ON ci.product_id = p.id 
              WHERE ci.user_id = $user_id";
    $result = mysqli_query($conn, $query);

    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    return $items;
}

/**
 * Calculate cart total
 */
function getCartTotal()
{
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// ========================================
// File Upload Helpers
// ========================================

/** Validate uploaded file */
function validateUpload($file, $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'], $max_size = 5242880)
{
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Terjadi kesalahan saat upload file.';
        return $errors;
    }

    // Check file size (default 5MB)
    if ($file['size'] > $max_size) {
        $errors[] = 'Ukuran file terlalu besar. Maksimal ' . ($max_size / 1024 / 1024) . 'MB.';
    }

    // Check file type
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) {
        $errors[] = 'Tipe file tidak diizinkan. Tipe yang diizinkan: ' . implode(', ', $allowed_types);
    }

    return $errors;
}

/** Generate unique filename */
function generateFilename($original_name)
{
    $ext = pathinfo($original_name, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $ext;
}

// ========================================
// Flash Messages
// ========================================

/**Set flash message */
function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/** Get and clear flash message */
function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlash()
{
    $flash = getFlash();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        echo "<div class='alert alert-$type'>$message</div>";
    }
}

// ========================================
// Status Badge Helpers
// ========================================

/**
 * Get status badge class
 */
function getStatusBadge($status)
{
    $badges = [
        'pending' => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-danger',
        'completed' => 'badge-success',
        'cancelled' => 'badge-secondary',
        'paid' => 'badge-success',
        'unpaid' => 'badge-warning',
        'processing' => 'badge-info',
        'shipped' => 'badge-primary'
    ];
    return $badges[$status] ?? 'badge-secondary';
}

/**
 * Get status label in Indonesian
 */
function getStatusLabel($status)
{
    $labels = [
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'paid' => 'Dibayar',
        'unpaid' => 'Belum Dibayar',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'failed' => 'Gagal',
        'refunded' => 'Dikembalikan'
    ];
    return $labels[$status] ?? ucfirst($status);
}
?>