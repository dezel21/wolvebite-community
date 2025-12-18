<?php
// Wolvebite Academy - Admin Dashboard
$pageTitle = 'Dashboard Admin';
require_once '../includes/functions.php';
requireAdmin();

// Stats
$totalPrograms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_programs"))['count'];
$totalCoaches = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_coaches WHERE status = 'active'"))['count'];
$totalEnrollments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_enrollments WHERE status = 'approved'"))['count'];
$pendingEnrollments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_enrollments WHERE status = 'pending'"))['count'];
$pendingBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_bookings WHERE status = 'pending'"))['count'];
$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(payment_amount), 0) as total FROM academy_enrollments WHERE payment_status = 'paid'"))['total'];

// Recent enrollments
$recentEnrollments = mysqli_query($conn, "SELECT e.*, u.username, p.name as program_name 
                                          FROM academy_enrollments e 
                                          JOIN users u ON e.user_id = u.id 
                                          JOIN academy_programs p ON e.program_id = p.id 
                                          ORDER BY e.created_at DESC LIMIT 5");

// Pending bookings
$recentBookings = mysqli_query($conn, "SELECT b.*, u.username, s.day_of_week, s.start_time, p.name as program_name 
                                        FROM academy_bookings b 
                                        JOIN users u ON b.user_id = u.id 
                                        JOIN academy_schedule s ON b.schedule_id = s.id 
                                        JOIN academy_programs p ON s.program_id = p.id 
                                        WHERE b.status = 'pending' 
                                        ORDER BY b.booking_date ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Wolvebite Academy Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <a href="<?php echo SITE_URL; ?>/academy/" class="nav-logo">
                <span class="logo-icon">🎓</span>
                <span class="logo-text">Academy Admin</span>
            </a>

            <nav class="admin-nav">
                <a href="index.php" class="admin-nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="programs.php" class="admin-nav-link"><i class="fas fa-basketball-ball"></i> Programs</a>
                <a href="coaches.php" class="admin-nav-link"><i class="fas fa-user-tie"></i> Coaches</a>
                <a href="schedule.php" class="admin-nav-link"><i class="fas fa-calendar-alt"></i> Schedule</a>
                <a href="enrollments.php" class="admin-nav-link">
                    <i class="fas fa-user-graduate"></i> Enrollments
                    <?php if ($pendingEnrollments > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingEnrollments; ?></span><?php endif; ?>
                </a>
                <a href="bookings.php" class="admin-nav-link">
                    <i class="fas fa-calendar-check"></i> Bookings
                    <?php if ($pendingBookings > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingBookings; ?></span><?php endif; ?>
                </a>
                <a href="modules.php" class="admin-nav-link"><i class="fas fa-book"></i> Modules</a>
                <div style="margin-top: auto; padding-top: var(--space-xl);">
                    <a href="<?php echo SITE_URL; ?>/academy/" class="admin-nav-link"><i class="fas fa-home"></i> Ke
                        Academy</a>
                    <a href="<?php echo SITE_URL; ?>/admin/" class="admin-nav-link"><i class="fas fa-store"></i> Ke
                        Community Admin</a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="admin-nav-link"><i
                            class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Dashboard Academy</h1>
            </div>

            <?php displayFlash(); ?>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-basketball-ball"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalPrograms; ?></h3>
                        <p>Program</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalCoaches; ?></h3>
                        <p>Coach Aktif</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalEnrollments; ?></h3>
                        <p>Siswa Terdaftar</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ff7e5f, #feb47b);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo formatRupiah($totalRevenue); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>

            <div
                style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-xl); margin-top: var(--space-xl);">
                <!-- Recent Enrollments -->
                <div class="card">
                    <div class="card-body">
                        <h3 style="margin-bottom: var(--space-lg);">Pendaftaran Terbaru</h3>
                        <?php if (mysqli_num_rows($recentEnrollments) > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: var(--space-md);">
                                <?php while ($enrollment = mysqli_fetch_assoc($recentEnrollments)): ?>
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-sm) 0; border-bottom: 1px solid var(--border-color);">
                                        <div>
                                            <strong><?php echo sanitize($enrollment['username']); ?></strong>
                                            <p style="font-size: var(--font-size-sm); color: var(--text-light); margin: 0;">
                                                <?php echo sanitize($enrollment['program_name']); ?>
                                            </p>
                                        </div>
                                        <span class="badge <?php echo getStatusBadge($enrollment['status']); ?>">
                                            <?php echo getStatusLabel($enrollment['status']); ?>
                                        </span>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Belum ada pendaftaran.</p>
                        <?php endif; ?>
                        <a href="enrollments.php" class="btn btn-outline btn-sm btn-block"
                            style="margin-top: var(--space-lg);">
                            Lihat Semua <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Bookings -->
                <div class="card">
                    <div class="card-body">
                        <h3 style="margin-bottom: var(--space-lg);">Booking Pending</h3>
                        <?php if (mysqli_num_rows($recentBookings) > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: var(--space-md);">
                                <?php while ($booking = mysqli_fetch_assoc($recentBookings)): ?>
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-sm) 0; border-bottom: 1px solid var(--border-color);">
                                        <div>
                                            <strong><?php echo sanitize($booking['username']); ?></strong>
                                            <p style="font-size: var(--font-size-sm); color: var(--text-light); margin: 0;">
                                                <?php echo formatDay($booking['day_of_week']); ?>,
                                                <?php echo formatDate($booking['booking_date']); ?>
                                            </p>
                                        </div>
                                        <a href="bookings.php?confirm=<?php echo $booking['id']; ?>"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada booking pending.</p>
                        <?php endif; ?>
                        <a href="bookings.php" class="btn btn-outline btn-sm btn-block"
                            style="margin-top: var(--space-lg);">
                            Lihat Semua <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>