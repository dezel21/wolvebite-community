<?php
/**
 * Wolvebite Community - Header Include
 */
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Wolvebite Community' : 'Wolvebite Community'; ?></title>
    <meta name="description"
        content="Wolvebite Community - Komunitas Basket Terbaik. Latihan, pertandingan, dan pengembangan skill basket bersama.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="nav-logo">
                <img src="assets/images/logo.png" alt="Wolvebite" class="logo-img" style="height: 40px; width: auto;">
                <span class="logo-text">Wolvebite</span>
            </a>

            <button class="nav-toggle" id="navToggle">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Beranda</a>
                </li>
                <li><a href="shop.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : ''; ?>">Shop</a>
                </li>
                <li><a href="booking.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'active' : ''; ?>">Booking</a>
                </li>
                <li><a href="download.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'download.php' ? 'active' : ''; ?>">Download</a>
                </li>
                <li><a href="academy/" class="nav-link" style="color: var(--accent-color);"><i
                            class="fas fa-graduation-cap"></i> Academy</a></li>

                <?php if (isLoggedIn()): ?>
                    <li class="nav-item-cart">
                        <a href="cart.php"
                            class="nav-link cart-link <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i>
                            <?php $cartCount = getCartCount();
                            if ($cartCount > 0): ?>
                                <span class="cart-badge"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-dropdown">
                        <a href="#" class="nav-link dropdown-toggle">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo sanitize($_SESSION['username']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (isAdmin()): ?>
                                <li><a href="admin/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</a></li>
                                <li class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a href="profile.php"><i class="fas fa-user"></i> Profil Saya</a></li>
                            <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Pesanan Saya</a></li>
                            <li><a href="my-bookings.php"><i class="fas fa-calendar-check"></i> Booking Saya</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link btn-nav-login">Login</a></li>
                    <li><a href="register.php" class="nav-link btn-nav-register">Daftar</a></li>
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