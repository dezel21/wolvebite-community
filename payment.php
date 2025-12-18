<?php
/**
 * Wolvebite Community - Payment Page
 * Simulated payment gateway integration
 */
$pageTitle = 'Pembayaran';
require_once 'includes/header.php';
requireLogin();

$order_number = $_GET['order'] ?? '';

if (empty($order_number)) {
    setFlash('error', 'Pesanan tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Get order details
$user_id = $_SESSION['user_id'];
$order_number_escaped = escapeSQL($conn, $order_number);
$orderQuery = mysqli_query($conn, "SELECT * FROM orders WHERE order_number = '$order_number_escaped' AND user_id = $user_id");

if (mysqli_num_rows($orderQuery) === 0) {
    setFlash('error', 'Pesanan tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$order = mysqli_fetch_assoc($orderQuery);

// Get order items
$itemsQuery = mysqli_query($conn, "SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = {$order['id']}");

// Process payment confirmation (simulation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // Simulate payment verification
    $payment_proof = $_FILES['payment_proof'] ?? null;

    // Update order status to paid
    mysqli_query($conn, "UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE id = {$order['id']}");

    setFlash('success', 'Pembayaran berhasil dikonfirmasi! Pesanan Anda sedang diproses.');
    header("Location: orders.php");
    exit;
}
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-credit-card"></i> Pembayaran</h1>
        <p class="section-subtitle">Selesaikan pembayaran untuk pesanan Anda</p>
    </div>

    <div class="cart-container">
        <!-- Payment Instructions -->
        <div class="cart-items">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Pesanan Berhasil Dibuat!</strong>
                    <p style="margin: 0;">Nomor Pesanan:
                        <strong><?php echo sanitize($order['order_number']); ?></strong></p>
                </div>
            </div>

            <h3 style="margin-bottom: var(--space-lg);">Instruksi Pembayaran</h3>

            <?php if ($order['payment_method'] === 'transfer'): ?>
                <div class="card" style="margin-bottom: var(--space-lg);">
                    <div class="card-body">
                        <h4 style="margin-bottom: var(--space-md);">Transfer ke Rekening:</h4>

                        <div
                            style="background: var(--bg-color); padding: var(--space-lg); border-radius: var(--radius-lg); margin-bottom: var(--space-md);">
                            <p style="margin-bottom: var(--space-sm);"><strong>Bank BCA</strong></p>
                            <p
                                style="font-size: var(--font-size-2xl); font-weight: 700; color: var(--primary-dark); margin-bottom: var(--space-sm);">
                                1234 5678 9012
                            </p>
                            <p style="color: var(--text-light); margin: 0;">a.n. Wolvebite Community</p>
                        </div>

                        <div
                            style="background: var(--bg-color); padding: var(--space-lg); border-radius: var(--radius-lg);">
                            <p style="margin-bottom: var(--space-sm);"><strong>Bank Mandiri</strong></p>
                            <p
                                style="font-size: var(--font-size-2xl); font-weight: 700; color: var(--primary-dark); margin-bottom: var(--space-sm);">
                                9876 5432 1098
                            </p>
                            <p style="color: var(--text-light); margin: 0;">a.n. Wolvebite Community</p>
                        </div>
                    </div>
                </div>
            <?php elseif ($order['payment_method'] === 'ewallet'): ?>
                <div class="card" style="margin-bottom: var(--space-lg);">
                    <div class="card-body">
                        <h4 style="margin-bottom: var(--space-md);">Pembayaran E-Wallet:</h4>

                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-md);">
                            <div
                                style="background: var(--bg-color); padding: var(--space-lg); border-radius: var(--radius-lg); text-align: center;">
                                <p style="font-size: 2rem; margin-bottom: var(--space-sm);">💚</p>
                                <p style="font-weight: 600;">GoPay</p>
                                <p style="color: var(--text-light); font-size: var(--font-size-sm);">0812-3456-7890</p>
                            </div>
                            <div
                                style="background: var(--bg-color); padding: var(--space-lg); border-radius: var(--radius-lg); text-align: center;">
                                <p style="font-size: 2rem; margin-bottom: var(--space-sm);">💜</p>
                                <p style="font-weight: 600;">OVO</p>
                                <p style="color: var(--text-light); font-size: var(--font-size-sm);">0812-3456-7890</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Metode: COD (Bayar di Tempat)</strong>
                        <p style="margin: 0;">Siapkan uang pas sebesar <?php echo formatRupiah($order['total_amount']); ?>
                            saat barang diterima.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="alert alert-warning" style="margin-top: var(--space-lg);">
                <i class="fas fa-clock"></i>
                <div>
                    <strong>Batas Waktu Pembayaran</strong>
                    <p style="margin: 0;">Selesaikan pembayaran dalam 24 jam untuk menghindari pembatalan otomatis.</p>
                </div>
            </div>

            <?php if ($order['payment_method'] !== 'cod'): ?>
                <h3 style="margin: var(--space-xl) 0 var(--space-lg);">Konfirmasi Pembayaran</h3>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Upload Bukti Pembayaran (opsional)</label>
                        <input type="file" name="payment_proof" class="form-control" accept="image/*">
                        <span class="form-hint">Format: JPG, PNG (Maks. 2MB)</span>
                    </div>

                    <button type="submit" name="confirm_payment" class="btn btn-primary btn-lg">
                        <i class="fas fa-check"></i> Konfirmasi Sudah Bayar
                    </button>
                </form>
            <?php else: ?>
                <a href="orders.php" class="btn btn-primary btn-lg" style="margin-top: var(--space-lg);">
                    <i class="fas fa-list"></i> Lihat Pesanan Saya
                </a>
            <?php endif; ?>
        </div>

        <!-- Order Summary -->
        <div class="cart-summary">
            <h3>Detail Pesanan</h3>

            <div style="margin-bottom: var(--space-lg);">
                <p style="color: var(--text-light); font-size: var(--font-size-sm);">Nomor Pesanan</p>
                <p style="font-weight: 700; color: var(--primary-dark);"><?php echo sanitize($order['order_number']); ?>
                </p>
            </div>

            <div style="margin-bottom: var(--space-lg);">
                <p style="color: var(--text-light); font-size: var(--font-size-sm);">Tanggal Pesanan</p>
                <p style="font-weight: 500;"><?php echo formatDate($order['created_at']); ?></p>
            </div>

            <div style="margin-bottom: var(--space-lg);">
                <p style="color: var(--text-light); font-size: var(--font-size-sm);">Status</p>
                <span class="badge <?php echo getStatusBadge($order['payment_status']); ?>">
                    <?php echo getStatusLabel($order['payment_status']); ?>
                </span>
            </div>

            <hr style="border-color: var(--border-color); margin: var(--space-lg) 0;">

            <h4 style="margin-bottom: var(--space-md);">Item Pesanan</h4>

            <?php while ($item = mysqli_fetch_assoc($itemsQuery)): ?>
                <div
                    style="display: flex; gap: var(--space-md); padding: var(--space-sm) 0; border-bottom: 1px solid var(--border-color);">
                    <div style="flex: 1;">
                        <strong style="font-size: var(--font-size-sm);"><?php echo sanitize($item['name']); ?></strong>
                        <p style="color: var(--text-light); font-size: var(--font-size-xs); margin: 0;">
                            <?php echo $item['quantity']; ?> x <?php echo formatRupiah($item['price']); ?>
                        </p>
                    </div>
                    <span style="font-weight: 600; font-size: var(--font-size-sm);">
                        <?php echo formatRupiah($item['price'] * $item['quantity']); ?>
                    </span>
                </div>
            <?php endwhile; ?>

            <div class="summary-row total" style="margin-top: var(--space-lg);">
                <span>Total</span>
                <span><?php echo formatRupiah($order['total_amount']); ?></span>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>