<?php
/**
 * Wolvebite Community - Order Detail Page
 */
$pageTitle = 'Detail Pesanan';
require_once 'includes/header.php';
requireLogin();

$order_id = (int) ($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    setFlash('error', 'Pesanan tidak ditemukan.');
    header('Location: orders.php');
    exit;
}

// Get order - check if belongs to user (unless admin)
$orderQuery = "SELECT o.*, u.username, u.email, u.phone as user_phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = $order_id";
if (!isAdmin()) {
    $orderQuery .= " AND o.user_id = $user_id";
}

$result = mysqli_query($conn, $orderQuery);
if (mysqli_num_rows($result) === 0) {
    setFlash('error', 'Pesanan tidak ditemukan.');
    header('Location: orders.php');
    exit;
}

$order = mysqli_fetch_assoc($result);

// Get order items
$items = mysqli_query($conn, "SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id");
?>

<div class="container">
    <div style="margin-bottom: var(--space-lg);">
        <a href="<?php echo isAdmin() ? 'admin/orders.php' : 'orders.php'; ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="section-header">
        <h1 class="section-title">Detail Pesanan</h1>
        <p class="section-subtitle">#<?php echo sanitize($order['order_number']); ?></p>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-xl);">
        <!-- Order Items -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: var(--space-lg);">Item Pesanan</h3>

                <div style="display: flex; flex-direction: column; gap: var(--space-md);">
                    <?php while ($item = mysqli_fetch_assoc($items)): ?>
                        <div
                            style="display: flex; gap: var(--space-lg); padding: var(--space-md); background: var(--bg-color); border-radius: var(--radius-md);">
                            <div
                                style="width: 80px; height: 80px; background: var(--card-bg); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;">
                                <?php if ($item['image'] && file_exists('uploads/products/' . $item['image'])): ?>
                                    <img src="uploads/products/<?php echo $item['image']; ?>" alt=""
                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-md);">
                                <?php else: ?>
                                    <span style="font-size: 2rem;">🏀</span>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin-bottom: var(--space-xs);"><?php echo sanitize($item['name']); ?></h4>
                                <p style="color: var(--text-light); font-size: var(--font-size-sm);">
                                    <?php echo $item['quantity']; ?> x <?php echo formatRupiah($item['price']); ?>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <p style="font-weight: 600; font-size: var(--font-size-lg);">
                                    <?php echo formatRupiah($item['price'] * $item['quantity']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <hr style="border-color: var(--border-color); margin: var(--space-xl) 0;">

                <div style="display: flex; justify-content: space-between; font-size: var(--font-size-xl);">
                    <strong>Total Pembayaran</strong>
                    <strong
                        style="color: var(--accent-color);"><?php echo formatRupiah($order['total_amount']); ?></strong>
                </div>
            </div>
        </div>

        <!-- Order Info -->
        <div>
            <div class="card" style="margin-bottom: var(--space-lg);">
                <div class="card-body">
                    <h3 style="margin-bottom: var(--space-lg);">Status Pesanan</h3>

                    <div style="display: flex; gap: var(--space-sm); margin-bottom: var(--space-lg);">
                        <span class="badge <?php echo getStatusBadge($order['status']); ?>"
                            style="font-size: var(--font-size-base);">
                            <?php echo getStatusLabel($order['status']); ?>
                        </span>
                        <span class="badge <?php echo getStatusBadge($order['payment_status']); ?>"
                            style="font-size: var(--font-size-base);">
                            <?php echo getStatusLabel($order['payment_status']); ?>
                        </span>
                    </div>

                    <div style="display: grid; gap: var(--space-md);">
                        <div>
                            <p style="color: var(--text-light); font-size: var(--font-size-sm);">Tanggal Pesanan</p>
                            <p style="font-weight: 500;"><?php echo formatDate($order['created_at']); ?></p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: var(--font-size-sm);">Metode Pembayaran</p>
                            <p style="font-weight: 500;">
                                <?php
                                $paymentMethods = [
                                    'transfer' => 'Transfer Bank',
                                    'ewallet' => 'E-Wallet',
                                    'cod' => 'COD (Bayar di Tempat)'
                                ];
                                echo $paymentMethods[$order['payment_method']] ?? ucfirst($order['payment_method']);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom: var(--space-lg);">
                <div class="card-body">
                    <h3 style="margin-bottom: var(--space-lg);">Informasi Pengiriman</h3>

                    <div style="display: grid; gap: var(--space-md);">
                        <div>
                            <p style="color: var(--text-light); font-size: var(--font-size-sm);">Nama Penerima</p>
                            <p style="font-weight: 500;"><?php echo sanitize($order['username']); ?></p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: var(--font-size-sm);">Email</p>
                            <p style="font-weight: 500;"><?php echo sanitize($order['email']); ?></p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: var(--font-size-sm);">Alamat Pengiriman</p>
                            <p style="font-weight: 500;"><?php echo sanitize($order['shipping_address']); ?></p>
                        </div>
                        <?php if ($order['notes']): ?>
                            <div>
                                <p style="color: var(--text-light); font-size: var(--font-size-sm);">Catatan</p>
                                <p style="font-weight: 500;"><?php echo sanitize($order['notes']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($order['payment_status'] === 'unpaid' && $order['status'] === 'pending'): ?>
                <a href="payment.php?order=<?php echo urlencode($order['order_number']); ?>"
                    class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-credit-card"></i> Bayar Sekarang
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>