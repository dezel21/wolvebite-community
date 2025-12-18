<?php
/**
 * Wolvebite Community - Logout
 */
require_once 'includes/functions.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to login page with message
session_start();
setFlash('success', 'Anda telah berhasil logout.');
header('Location: login.php');
exit;
?>