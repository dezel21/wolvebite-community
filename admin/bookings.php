<?php
/**
 * Wolvebite Community - Admin Bookings Management
 */
$pageTitle = 'Kelola Booking';
require_once '../includes/functions.php';
requireAdmin();

// Handle approve/reject booking
if (isset($_GET['approve'])) {
    $booking_id = (int) $_GET['approve'];
    mysqli_query($conn, "UPDATE bookings SET status = 'approved' WHERE id = $booking_id");
    setFlash('success', 'Booking berhasil disetujui.');
    header('Location: bookings.php');
    exit;
}

if (isset($_GET['reject'])) {
    $booking_id = (int) $_GET['reject'];
    mysqli_query($conn, "UPDATE bookings SET status = 'rejected' WHERE id = $booking_id");
    setFlash('success', 'Booking berhasil ditolak.');
    header('Location: bookings.php');
    exit;
}

// Filter by status
$statusFilter = isset($_GET['status']) ? escapeSQL($conn, $_GET['status']) : '';
$whereClause = $statusFilter ? "WHERE b.status = '$statusFilter'" : "";

// Get all bookings
$bookings = mysqli_query($conn, "SELECT b.*, u.username, u.email 
                                  FROM bookings b 
                                  JOIN users u ON b.user_id = u.id 
                                  $whereClause
                                  ORDER BY b.booking_date DESC, b.start_time DESC");

$pendingBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'"))['count'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Wolvebite Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                <a href="index.php" class="admin-nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="users.php" class="admin-nav-link"><i class="fas fa-users"></i> Kelola User</a>
                <a href="products.php" class="admin-nav-link"><i class="fas fa-box"></i> Kelola Produk</a>
                <a href="orders.php" class="admin-nav-link"><i class="fas fa-shopping-bag"></i> Pesanan</a>
                <a href="bookings.php" class="admin-nav-link active">
                    <i class="fas fa-calendar-check"></i> Booking
                    <?php if ($pendingBookings > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingBookings; ?></span><?php endif; ?>
                </a>
                <a href="uploads.php" class="admin-nav-link"><i class="fas fa-file-upload"></i> Upload File</a>
                <div style="margin-top: auto; padding-top: var(--space-xl);">
                    <a href="../index.php" class="admin-nav-link"><i class="fas fa-home"></i> Ke Website</a>
                    <a href="../logout.php" class="admin-nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Kelola Booking</h1>
            </div>

            <?php displayFlash(); ?>

            <!-- Filter -->
            <div style="display: flex; gap: var(--space-md); margin-bottom: var(--space-xl); flex-wrap: wrap;">
                <a href="bookings.php"
                    class="btn <?php echo empty($statusFilter) ? 'btn-primary' : 'btn-outline'; ?>">Semua</a>
                <a href="bookings.php?status=pending"
                    class="btn <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline'; ?>">Pending</a>
                <a href="bookings.php?status=approved"
                    class="btn <?php echo $statusFilter === 'approved' ? 'btn-primary' : 'btn-outline'; ?>">Approved</a>
                <a href="bookings.php?status=rejected"
                    class="btn <?php echo $statusFilter === 'rejected' ? 'btn-primary' : 'btn-outline'; ?>">Rejected</a>
                <a href="bookings.php?status=completed"
                    class="btn <?php echo $statusFilter === 'completed' ? 'btn-primary' : 'btn-outline'; ?>">Completed</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Lapangan</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($bookings) > 0): ?>
                                    <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>
                                        <tr>
                                            <td><?php echo $booking['id']; ?></td>
                                            <td>
                                                <strong><?php echo sanitize($booking['username']); ?></strong>
                                                <p style="font-size: var(--font-size-sm); color: var(--text-light); margin: 0;">
                                                    <?php echo sanitize($booking['email']); ?>
                                                </p>
                                            </td>
                                            <td><?php echo formatDate($booking['booking_date']); ?></td>
                                            <td><?php echo formatTime($booking['start_time']); ?> -
                                                <?php echo formatTime($booking['end_time']); ?></td>
                                            <td><?php echo sanitize($booking['court_name']); ?></td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadge($booking['status']); ?>">
                                                    <?php echo getStatusLabel($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $booking['notes'] ? sanitize(substr($booking['notes'], 0, 30)) : '-'; ?>
                                            </td>
                                            <td>
                                                <?php if ($booking['status'] === 'pending'): ?>
                                                    <a href="bookings.php?approve=<?php echo $booking['id']; ?>"
                                                        class="btn btn-sm btn-success" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="bookings.php?reject=<?php echo $booking['id']; ?>"
                                                        class="btn btn-sm btn-danger" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data booking</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>