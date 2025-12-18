<?php
// Wolvebite Academy - Admin Enrollments
$pageTitle = 'Kelola Pendaftaran';
require_once '../includes/functions.php';
requireAdmin();

// Handle approve/reject
if (isset($_GET['approve'])) {
    $id = (int) $_GET['approve'];
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+12 weeks'));
    mysqli_query($conn, "UPDATE academy_enrollments SET status = 'approved', start_date = '$start_date', end_date = '$end_date' WHERE id = $id");
    setFlash('success', 'Pendaftaran disetujui.');
    header('Location: enrollments.php');
    exit;
}

if (isset($_GET['reject'])) {
    $id = (int) $_GET['reject'];
    mysqli_query($conn, "UPDATE academy_enrollments SET status = 'rejected' WHERE id = $id");
    setFlash('success', 'Pendaftaran ditolak.');
    header('Location: enrollments.php');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$where = $statusFilter ? "WHERE e.status = '" . escapeSQL($conn, $statusFilter) . "'" : "";

$enrollments = mysqli_query($conn, "SELECT e.*, u.username, u.email, p.name as program_name, p.price 
                                    FROM academy_enrollments e 
                                    JOIN users u ON e.user_id = u.id 
                                    JOIN academy_programs p ON e.program_id = p.id 
                                    $where
                                    ORDER BY e.created_at DESC");

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
                <a href="enrollments.php" class="admin-nav-link active"><i class="fas fa-user-graduate"></i> Enrollments
                    <?php if ($pendingEnrollments > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingEnrollments; ?></span><?php endif; ?></a>
                <a href="bookings.php" class="admin-nav-link"><i class="fas fa-calendar-check"></i> Bookings
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
                <h1>Kelola Pendaftaran</h1>
            </div>

            <?php displayFlash(); ?>

            <!-- Filter -->
            <div style="display: flex; gap: var(--space-md); margin-bottom: var(--space-xl); flex-wrap: wrap;">
                <a href="enrollments.php"
                    class="btn <?php echo empty($statusFilter) ? 'btn-primary' : 'btn-outline'; ?>">Semua</a>
                <a href="enrollments.php?status=pending"
                    class="btn <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline'; ?>">Pending</a>
                <a href="enrollments.php?status=approved"
                    class="btn <?php echo $statusFilter === 'approved' ? 'btn-primary' : 'btn-outline'; ?>">Approved</a>
                <a href="enrollments.php?status=rejected"
                    class="btn <?php echo $statusFilter === 'rejected' ? 'btn-primary' : 'btn-outline'; ?>">Rejected</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Program</th>
                                <th>Tanggal Daftar</th>
                                <th>Pembayaran</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($enrollments) > 0): ?>
                                <?php while ($e = mysqli_fetch_assoc($enrollments)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo sanitize($e['username']); ?></strong>
                                            <p style="font-size: var(--font-size-sm); color: var(--text-light); margin: 0;">
                                                <?php echo sanitize($e['email']); ?></p>
                                        </td>
                                        <td><?php echo sanitize($e['program_name']); ?></td>
                                        <td><?php echo formatDate($e['enrollment_date']); ?></td>
                                        <td>
                                            <?php echo formatRupiah($e['payment_amount']); ?>
                                            <span class="badge <?php echo getStatusBadge($e['payment_status']); ?>"
                                                style="margin-left: var(--space-xs);">
                                                <?php echo $e['payment_status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadge($e['status']); ?>">
                                                <?php echo getStatusLabel($e['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($e['status'] === 'pending'): ?>
                                                <a href="enrollments.php?approve=<?php echo $e['id']; ?>"
                                                    class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                                                <a href="enrollments.php?reject=<?php echo $e['id']; ?>"
                                                    class="btn btn-sm btn-danger"><i class="fas fa-times"></i></a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data</td>
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