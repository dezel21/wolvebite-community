<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Step 1: Before header<br>";

$pageTitle = 'Test';
require_once 'includes/header.php';

echo "Step 2: After header - SUCCESS!";
?>