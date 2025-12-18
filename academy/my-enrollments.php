<?php
/** Wolvebite Academy - My Enrollments Page */
$pageTitle = 'Pendaftaran Saya';
require_once 'includes/header.php';
requireLogin();

$enrollments = getUserEnrollments($_SESSION['user_id']);
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-graduation-cap"></i> Pendaftaran Saya</h1>
        <p class="section-subtitle">Riwayat pendaftaran program Anda</p>
    </div>

    <?php if (mysqli_num_rows($enrollments) > 0): ?>
        <div style="display: flex; flex-direction: column; gap: var(--space-lg);">
            <?php while ($enrollment = mysqli_fetch_assoc($enrollments)): ?>
                <div class="card">
                    <div class="card-body">
                        <div style="display: flex; gap: var(--space-xl); flex-wrap: wrap;">
                            <!-- Program Image -->
                            <div
                                style="width: 150px; height: 150px; background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 4rem;">
                                🏀
                            </div>

                            <!-- Enrollment Info -->
                            <div style="flex: 1; min-width: 250px;">
                                <h3 style="margin-bottom: var(--space-sm);"><?php echo sanitize($enrollment['program_name']); ?>
                                </h3>
                                <p style="color: var(--text-light); margin-bottom: var(--space-md);">
                                    <i class="fas fa-user-tie"></i> Coach:
                                    <?php echo sanitize($enrollment['coach_name'] ?? 'TBA'); ?>
                                </p>

                                <div
                                    style="display: flex; gap: var(--space-lg); flex-wrap: wrap; margin-bottom: var(--space-md);">
                                    <div>
                                        <span style="color: var(--text-light); font-size: var(--font-size-sm);">Status
                                            Pendaftaran</span>
                                        <div>
                                            <span class="badge <?php echo getStatusBadge($enrollment['status']); ?>">
                                                <?php echo getStatusLabel($enrollment['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-light); font-size: var(--font-size-sm);">Status
                                            Pembayaran</span>
                                        <div>
                                            <span class="badge <?php echo getStatusBadge($enrollment['payment_status']); ?>">
                                                <?php echo getStatusLabel($enrollment['payment_status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-light); font-size: var(--font-size-sm);">Tanggal
                                            Daftar</span>
                                        <div style="font-weight: 500;"><?php echo formatDate($enrollment['enrollment_date']); ?>
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex; gap: var(--space-sm); flex-wrap: wrap;">
                                    <?php if ($enrollment['payment_status'] === 'unpaid' && $enrollment['status'] === 'pending'): ?>
                                        <a href="enrollment-payment.php?id=<?php echo $enrollment['id']; ?>"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-credit-card"></i> Bayar Sekarang
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($enrollment['status'] === 'approved'): ?>
                                        <a href="booking.php?program=<?php echo $enrollment['program_id']; ?>"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-calendar-plus"></i> Book Kelas
                                        </a>
                                    <?php endif; ?>

                                    <a href="program-detail.php?slug=<?php echo $enrollment['program_id']; ?>"
                                        class="btn btn-outline btn-sm">
                                        <i class="fas fa-eye"></i> Detail Program
                                    </a>

                                    <?php if ($enrollment['payment_status'] === 'paid'): ?>
                                        <a href="invoice.php?enrollment_id=<?php echo $enrollment['id']; ?>"
                                            class="btn btn-secondary btn-sm" target="_blank">
                                            <i class="fas fa-file-invoice"></i> Invoice
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Price -->
                            <div style="text-align: right;">
                                <span style="color: var(--text-light); font-size: var(--font-size-sm);">Total</span>
                                <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--accent-color);">
                                    <?php echo formatRupiah($enrollment['payment_amount']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-graduation-cap"></i>
            <h3>Belum Ada Pendaftaran</h3>
            <p>Anda belum terdaftar di program apapun.</p>
            <a href="programs.php" class="btn btn-primary">
                <i class="fas fa-list"></i> Lihat Program
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>