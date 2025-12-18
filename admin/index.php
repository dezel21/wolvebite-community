<?php
/**
 * Wolvebite Community - Admin Dashboard
 */
$pageTitle = 'Admin Dashboard';
require_once '../includes/functions.php';
requireAdmin();

// Get statistics
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$totalBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
$pendingBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'"))['count'];
$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status = 'paid'"))['total'];

// Recent orders
$recentOrders = mysqli_query($conn, "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");

// Pending bookings
$pendingBookingsQuery = mysqli_query($conn, "SELECT b.*, u.username FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.status = 'pending' ORDER BY b.booking_date ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Wolvebite</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <a href="../index.php" class="nav-logo">
                <span class="logo-icon">🐺</span>
                <span class="logo-text">Wolvebite</span>
            </a>
            
            <nav class="admin-nav">
                <a href="index.php" class="admin-nav-link active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="users.php" class="admin-nav-link">
                    <i class="fas fa-users"></i> Kelola User
                </a>
                <a href="products.php" class="admin-nav-link">
                    <i class="fas fa-box"></i> Kelola Produk
                </a>
                <a href="orders.php" class="admin-nav-link">
                    <i class="fas fa-shopping-bag"></i> Pesanan
                </a>
                <a href="bookings.php" class="admin-nav-link">
                    <i class="fas fa-calendar-check"></i> Booking
                    <?php if ($pendingBookings > 0): ?>
                        <span class="badge badge-warning" style="margin-left: auto;"><?php echo $pendingBookings; ?></span>
                    <?php endif; ?>
                </a>
                <a href="uploads.php" class="admin-nav-link">
                    <i class="fas fa-file-upload"></i> Upload File
                </a>
                <div style="margin-top: auto; padding-top: var(--space-xl);">
                    <a href="../index.php" class="admin-nav-link">
                        <i class="fas fa-home"></i> Ke Website
                    </a>
                    <a href="../logout.php" class="admin-nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <p style="color: var(--text-light);">Selamat datang, <?php echo sanitize($_SESSION['username']); ?>!</p>
            </div>
            
            <?php displayFlash(); ?>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalUsers; ?></h3>
                        <p>Total Member</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalProducts; ?></h3>
                        <p>Total Produk</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalOrders; ?></h3>
                        <p>Total Pesanan</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo formatRupiah($totalRevenue); ?></h3>
                        <p>Total Pendapatan</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts and Tables -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-xl); margin-bottom: var(--space-xl);">
                <!-- Revenue Chart -->
                <div class="card">
                    <div class="card-body">
                        <h3 style="margin-bottom: var(--space-lg);">Statistik Pendapatan</h3>
                        <canvas id="revenueChart" height="200"></canvas>
                    </div>
                </div>
                
                <!-- Bookings Chart -->
                <div class="card">
                    <div class="card-body">
                        <h3 style="margin-bottom: var(--space-lg);">Statistik Booking</h3>
                        <canvas id="bookingChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-xl);">
                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
                            <h3>Pesanan Terbaru</h3>
                            <a href="orders.php" class="btn btn-sm btn-outline">Lihat Semua</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($recentOrders) > 0): ?>
                                        <?php while ($order = mysqli_fetch_assoc($recentOrders)): ?>
                                            <tr>
                                                <td><?php echo sanitize($order['order_number']); ?></td>
                                                <td><?php echo sanitize($order['username']); ?></td>
                                                <td><?php echo formatRupiah($order['total_amount']); ?></td>
                                                <td><span class="badge <?php echo getStatusBadge($order['status']); ?>"><?php echo getStatusLabel($order['status']); ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center">Belum ada pesanan</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Bookings -->
                <div class="card">
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
                            <h3>Booking Menunggu Approval</h3>
                            <a href="bookings.php" class="btn btn-sm btn-outline">Lihat Semua</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Member</th>
                                        <th>Lapangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($pendingBookingsQuery) > 0): ?>
                                        <?php while ($booking = mysqli_fetch_assoc($pendingBookingsQuery)): ?>
                                            <tr>
                                                <td><?php echo formatDate($booking['booking_date']); ?></td>
                                                <td><?php echo sanitize($booking['username']); ?></td>
                                                <td><?php echo sanitize($booking['court_name']); ?></td>
                                                <td>
                                                    <a href="bookings.php?approve=<?php echo $booking['id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center">Tidak ada booking pending</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Pendapatan (Juta Rp)',
                    data: [2.5, 3.2, 2.8, 4.1, 3.8, 5.2],
                    borderColor: '#ff7e5f',
                    backgroundColor: 'rgba(255, 126, 95, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Booking Chart
        const bookingCtx = document.getElementById('bookingChart').getContext('2d');
        new Chart(bookingCtx, {
            type: 'bar',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Jumlah Booking',
                    data: [8, 12, 10, 15, 18, 25, 22],
                    backgroundColor: '#1f4068',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
