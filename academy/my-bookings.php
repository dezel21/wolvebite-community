<?php
/** Wolvebite Academy - My Bookings Page */
$pageTitle = 'Booking Saya';
require_once 'includes/header.php';
requireLogin();

// Handle cancel
if (isset($_GET['cancel'])) {
    $booking_id = (int) $_GET['cancel'];
    $result = mysqli_query($conn, "SELECT * FROM academy_bookings 
                                   WHERE id = $booking_id AND user_id = {$_SESSION['user_id']}");

    if (mysqli_num_rows($result) > 0) {
        $booking = mysqli_fetch_assoc($result);
        if (in_array($booking['status'], ['pending', 'confirmed']) && strtotime($booking['booking_date']) >= strtotime(date('Y-m-d'))) {
            mysqli_query($conn, "UPDATE academy_bookings SET status = 'cancelled' WHERE id = $booking_id");
            setFlash('success', 'Booking berhasil dibatalkan.');
        }
    }
    header('Location: my-bookings.php');
    exit;
}

$bookings = getUserAcademyBookings($_SESSION['user_id']);
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-calendar-check"></i> Booking Saya</h1>
        <p class="section-subtitle">Riwayat booking kelas latihan</p>
    </div>

    <div style="margin-bottom: var(--space-xl);">
        <a href="booking.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Booking Baru
        </a>
    </div>

    <?php if (mysqli_num_rows($bookings) > 0): ?>
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Program</th>
                            <th>Waktu</th>
                            <th>Lokasi</th>
                            <th>Coach</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>
                            <tr>
                                <td>
                                    <strong><?php echo formatDay($booking['day_of_week']); ?></strong><br>
                                    <span style="color: var(--text-light); font-size: var(--font-size-sm);">
                                        <?php echo formatDate($booking['booking_date']); ?>
                                    </span>
                                </td>
                                <td><?php echo sanitize($booking['program_name']); ?></td>
                                <td><?php echo formatTime($booking['start_time']); ?> -
                                    <?php echo formatTime($booking['end_time']); ?></td>
                                <td><?php echo sanitize($booking['location']); ?></td>
                                <td><?php echo sanitize($booking['coach_name'] ?? 'TBA'); ?></td>
                                <td>
                                    <span class="badge <?php echo getStatusBadge($booking['status']); ?>">
                                        <?php echo getStatusLabel($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (in_array($booking['status'], ['pending', 'confirmed']) && strtotime($booking['booking_date']) >= strtotime(date('Y-m-d'))): ?>
                                        <a href="my-bookings.php?cancel=<?php echo $booking['id']; ?>" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin ingin membatalkan booking ini?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>Belum Ada Booking</h3>
            <p>Anda belum melakukan booking kelas.</p>
            <a href="booking.php" class="btn btn-primary">Book Kelas</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>