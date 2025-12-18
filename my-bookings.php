<?php
/**
 * Wolvebite Community - My Bookings Page
 */
$pageTitle = 'Booking Saya';
require_once 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle cancel booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = (int) $_POST['booking_id'];

    // Check booking belongs to user and is cancellable
    $check = mysqli_query($conn, "SELECT * FROM bookings WHERE id = $booking_id AND user_id = $user_id AND status IN ('pending', 'approved')");

    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE bookings SET status = 'cancelled' WHERE id = $booking_id");
        setFlash('success', 'Booking berhasil dibatalkan.');
    } else {
        setFlash('error', 'Tidak dapat membatalkan booking ini.');
    }

    header('Location: my-bookings.php');
    exit;
}

// Get user bookings
$bookingsQuery = mysqli_query($conn, "SELECT * FROM bookings WHERE user_id = $user_id ORDER BY booking_date DESC, start_time DESC");
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-calendar-alt"></i> Booking Saya</h1>
        <p class="section-subtitle">Riwayat dan status booking lapangan Anda</p>
    </div>

    <div style="margin-bottom: var(--space-xl);">
        <a href="booking.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Booking Baru
        </a>
    </div>

    <?php if (mysqli_num_rows($bookingsQuery) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Lapangan</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = mysqli_fetch_assoc($bookingsQuery)): ?>
                        <tr>
                            <td>
                                <strong><?php echo formatDate($booking['booking_date']); ?></strong>
                            </td>
                            <td>
                                <?php echo formatTime($booking['start_time']); ?> -
                                <?php echo formatTime($booking['end_time']); ?>
                            </td>
                            <td><?php echo sanitize($booking['court_name']); ?></td>
                            <td>
                                <span class="badge <?php echo getStatusBadge($booking['status']); ?>">
                                    <?php echo getStatusLabel($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $booking['notes'] ? sanitize(substr($booking['notes'], 0, 30)) . (strlen($booking['notes']) > 30 ? '...' : '') : '-'; ?>
                            </td>
                            <td>
                                <?php if ($booking['status'] === 'pending' || $booking['status'] === 'approved'): ?>
                                    <?php if (strtotime($booking['booking_date']) >= strtotime('today')): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="cancel_booking" class="btn btn-sm btn-danger"
                                                data-confirm="Yakin ingin membatalkan booking ini?">
                                                <i class="fas fa-times"></i> Batalkan
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-alt"></i>
            <h3>Belum Ada Booking</h3>
            <p>Anda belum melakukan booking lapangan.</p>
            <a href="booking.php" class="btn btn-primary">
                <i class="fas fa-calendar-plus"></i> Booking Sekarang
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>