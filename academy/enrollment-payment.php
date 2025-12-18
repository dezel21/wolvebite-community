<?php
/** Wolvebite Academy - Enrollment Payment Page */
$pageTitle = 'Pembayaran Enrollment';
require_once 'includes/header.php';
requireLogin();

$enrollment_id = (int) ($_GET['id'] ?? 0);
if ($enrollment_id <= 0) {
    header('Location: my-enrollments.php');
    exit;
}

// Get enrollment
$result = mysqli_query($conn, "SELECT e.*, p.name as program_name, p.slug, p.price 
                               FROM academy_enrollments e 
                               JOIN academy_programs p ON e.program_id = p.id 
                               WHERE e.id = $enrollment_id AND e.user_id = {$_SESSION['user_id']}");

if (mysqli_num_rows($result) === 0) {
    setFlash('error', 'Enrollment tidak ditemukan.');
    header('Location: my-enrollments.php');
    exit;
}

$enrollment = mysqli_fetch_assoc($result);

// Already paid
if ($enrollment['payment_status'] === 'paid') {
    setFlash('info', 'Pembayaran sudah dikonfirmasi.');
    header('Location: my-enrollments.php');
    exit;
}

// Process payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // In production, integrate with real payment gateway
    // For now, simulate payment confirmation

    $payment_date = date('Y-m-d H:i:s');

    mysqli_query($conn, "UPDATE academy_enrollments SET payment_status = 'paid', payment_date = '$payment_date' WHERE id = $enrollment_id");

    setFlash('success', 'Pembayaran berhasil dikonfirmasi! Menunggu approval admin.');
    header('Location: my-enrollments.php');
    exit;
}
?>

<div class="container">
    <div style="margin-bottom: var(--space-lg);">
        <a href="my-enrollments.php" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-credit-card"></i> Pembayaran</h1>
        <p class="section-subtitle">Selesaikan pembayaran untuk program
            <?php echo sanitize($enrollment['program_name']); ?></p>
    </div>

    <div style="max-width: 600px; margin: 0 auto;">
        <!-- Payment Summary -->
        <div class="enrollment-summary">
            <h3>Detail Pembayaran</h3>
            <div class="enrollment-detail">
                <span>Program</span>
                <span class="value"><?php echo sanitize($enrollment['program_name']); ?></span>
            </div>
            <div class="enrollment-detail">
                <span>Tanggal Daftar</span>
                <span class="value"><?php echo formatDate($enrollment['enrollment_date']); ?></span>
            </div>
            <div class="enrollment-detail">
                <span>Status</span>
                <span class="badge <?php echo getStatusBadge($enrollment['payment_status']); ?>">
                    <?php echo getStatusLabel($enrollment['payment_status']); ?>
                </span>
            </div>
            <div class="enrollment-detail">
                <span>Total Bayar</span>
                <span class="value"><?php echo formatRupiah($enrollment['payment_amount']); ?></span>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: var(--space-lg);">Metode Pembayaran</h3>

                <div style="margin-bottom: var(--space-xl);">
                    <h4 style="margin-bottom: var(--space-md);">Transfer Bank</h4>
                    <div
                        style="background: var(--bg-color); padding: var(--space-lg); border-radius: var(--radius-md);">
                        <p style="margin-bottom: var(--space-sm);"><strong>Bank BCA</strong></p>
                        <p
                            style="font-size: var(--font-size-xl); font-weight: 700; color: var(--accent-color); margin-bottom: var(--space-sm);">
                            1234 5678 9012
                        </p>
                        <p style="color: var(--text-light);">a.n. Wolvebite Academy</p>
                    </div>
                </div>

                <div style="margin-bottom: var(--space-xl);">
                    <h4 style="margin-bottom: var(--space-md);">E-Wallet</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                        <div
                            style="background: var(--bg-color); padding: var(--space-md); border-radius: var(--radius-md); text-align: center;">
                            <p style="font-weight: 600;">GoPay</p>
                            <p style="color: var(--accent-color);">0812-3456-7890</p>
                        </div>
                        <div
                            style="background: var(--bg-color); padding: var(--space-md); border-radius: var(--radius-md); text-align: center;">
                            <p style="font-weight: 600;">OVO</p>
                            <p style="color: var(--accent-color);">0812-3456-7890</p>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info" style="margin-bottom: var(--space-xl);">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Instruksi:</strong>
                        <ol style="margin: var(--space-sm) 0 0 var(--space-lg); padding: 0;">
                            <li>Transfer sesuai nominal di atas</li>
                            <li>Simpan bukti pembayaran</li>
                            <li>Klik tombol "Konfirmasi Pembayaran"</li>
                            <li>Tunggu approval dari admin (1-2 hari kerja)</li>
                        </ol>
                    </div>
                </div>

                <form method="POST">
                    <button type="submit" name="confirm_payment" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-check-circle"></i> Konfirmasi Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>