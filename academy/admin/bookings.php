<?php
// Wolvebite Academy - Admin Bookings
$pageTitle = 'Kelola Booking';
require_once '../includes/functions.php';
requireAdmin();

// Handle confirm/cancel
if (isset($_GET['confirm'])) {
    $id = (int) $_GET['confirm'];
    mysqli_query($conn, "UPDATE academy_bookings SET status = 'confirmed' WHERE id = $id");
    setFlash('success', 'Booking dikonfirmasi.');
    header('Location: bookings.php');
    exit;
}

if (isset($_GET['cancel'])) {
    $id = (int) $_GET['cancel'];
    mysqli_query($conn, "UPDATE academy_bookings SET status = 'cancelled' WHERE id = $id");
    setFlash('success', 'Booking dibatalkan.');
    header('Location: bookings.php');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$where = $statusFilter ? "WHERE b.status = '" . escapeSQL($conn, $statusFilter) . "'" : "";

$bookings = mysqli_query($conn, "SELECT b.*, u.username, s.day_of_week, s.start_time, s.end_time, s.location, p.name as program_name 
                                 FROM academy_bookings b 
                                 JOIN users u ON b.user_id = u.id 
                                 JOIN academy_schedule s ON b.schedule_id = s.id 
                                 JOIN academy_programs p ON s.program_id = p.id 
                                 $where
                                 ORDER BY b.booking_date DESC");

$pendingEnrollments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_enrollments WHERE status = 'pending'"))['count'];
$pendingBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_bookings WHERE status = 'pending'"))['count'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Wolvebite Academy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="<?php echo SITE_URL; ?>/academy/" class="nav-logo">
                <span class="logo-icon">🎓</span>
                <span class="logo-text">Academy Admin</span>
            </a>
            <nav class="admin-nav">
                <a href="index.php" class="admin-nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="programs.php" class="admin-nav-link"><i class="fas fa-basketball-ball"></i> Programs</a>
                <a href="coaches.php" class="admin-nav-link"><i class="fas fa-user-tie"></i> Coaches</a>
                <a href="schedule.php" class="admin-nav-link"><i class="fas fa-calendar-alt"></i> Schedule</a>
                <a href="enrollments.php" class="admin-nav-link"><i class="fas fa-user-graduate"></i> Enrollments
                    <?php if ($pendingEnrollments > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingEnrollments; ?></span><?php endif; ?></a>
                <a href="bookings.php" class="admin-nav-link active"><i class="fas fa-calendar-check"></i> Bookings
                    <?php if ($pendingBookings > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingBookings; ?></span><?php endif; ?></a>
                <a href="modules.php" class="admin-nav-link"><i class="fas fa-book"></i> Modules</a>
                <div style="margin-top: auto; padding-top: var(--space-xl);">
                    <a href="<?php echo SITE_URL; ?>/academy/" class="admin-nav-link"><i class="fas fa-home"></i> Ke
                        Academy</a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="admin-nav-link"><i
                            class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1>Kelola Booking</h1>
            </div>

            <?php displayFlash(); ?>

            <div style="display: flex; gap: var(--space-md); margin-bottom: var(--space-xl); flex-wrap: wrap;">
                <a href="bookings.php"
                    class="btn <?php echo empty($statusFilter) ? 'btn-primary' : 'btn-outline'; ?>">Semua</a>
                <a href="bookings.php?status=pending"
                    class="btn <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline'; ?>">Pending</a>
                <a href="bookings.php?status=confirmed"
                    class="btn <?php echo $statusFilter === 'confirmed' ? 'btn-primary' : 'btn-outline'; ?>">Confirmed</a>
                <a href="bookings.php?status=attended"
                    class="btn <?php echo $statusFilter === 'attended' ? 'btn-primary' : 'btn-outline'; ?>">Attended</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Program</th>
                                <th>Jadwal</th>
                                <th>Tanggal</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($bookings) > 0): ?>
                                <?php while ($b = mysqli_fetch_assoc($bookings)): ?>
                                    <tr>
                                        <td><strong><?php echo sanitize($b['username']); ?></strong></td>
                                        <td><?php echo sanitize($b['program_name']); ?></td>
                                        <td><?php echo formatDay($b['day_of_week']); ?>
                                            <?php echo formatTime($b['start_time']); ?>-<?php echo formatTime($b['end_time']); ?>
                                        </td>
                                        <td><?php echo formatDate($b['booking_date']); ?></td>
                                        <td><?php echo sanitize($b['location']); ?></td>
                                        <td><span
                                                class="badge <?php echo getStatusBadge($b['status']); ?>"><?php echo getStatusLabel($b['status']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($b['status'] === 'pending'): ?>
                                                <a href="bookings.php?confirm=<?php echo $b['id']; ?>"
                                                    class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                                                <a href="bookings.php?cancel=<?php echo $b['id']; ?>"
                                                    class="btn btn-sm btn-danger"><i class="fas fa-times"></i></a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>