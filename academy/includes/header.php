<?php
/** Wolvebite Academy - Header*/
if (!isset($pageTitle)) {
    $pageTitle = 'Academy';
}

require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Wolvebite Academy - Akademi Basket Profesional">
    <title><?php echo $pageTitle; ?> - Wolvebite Academy</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/academy/assets/css/academy.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="<?php echo SITE_URL; ?>/academy/" class="nav-logo">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="Wolvebite Academy" class="logo-img"
                    style="height: 40px; width: auto;">
                <span class="logo-text">Wolvebite <span style="color: var(--accent-color);">Academy</span></span>
            </a>

            <button class="nav-toggle" id="navToggle">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo SITE_URL; ?>/academy/" class="nav-link">Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>/academy/programs.php" class="nav-link">Programs</a></li>
                <li><a href="<?php echo SITE_URL; ?>/academy/coaches.php" class="nav-link">Coaches</a></li>
                <li><a href="<?php echo SITE_URL; ?>/academy/schedule.php" class="nav-link">Schedule</a></li>
                <li><a href="<?php echo SITE_URL; ?>/academy/modules.php" class="nav-link">Modules</a></li>
                <li><a href="<?php echo SITE_URL; ?>/" class="nav-link"><i class="fas fa-arrow-left"></i> Community</a>
                </li>

                <?php if (isLoggedIn()): ?>
                    <li class="nav-dropdown">
                        <a href="#" class="nav-link dropdown-toggle">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo sanitize($_SESSION['username']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo SITE_URL; ?>/academy/admin/"><i class="fas fa-tachometer-alt"></i> Admin
                                        Academy</a></li>
                                <li class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a href="<?php echo SITE_URL; ?>/academy/my-enrollments.php"><i
                                        class="fas fa-graduation-cap"></i> My Enrollments</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/academy/my-bookings.php"><i
                                        class="fas fa-calendar-check"></i> My Bookings</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a href="<?php echo SITE_URL; ?>/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/logout.php" class="logout-link"><i
                                        class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>/login.php" class="nav-link btn-nav-login">Login</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/register.php" class="nav-link btn-nav-register">Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container">
        <?php displayFlash(); ?>
    </div>

    <!-- Main Content -->
    <main class="main-content">