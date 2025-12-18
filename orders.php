<?php
/**
 * Wolvebite Community - My Orders Page
 */
$pageTitle = 'Pesanan Saya';
require_once 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user orders
$ordersQuery = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-shopping-bag"></i> Pesanan Saya</h1>
        <p class="section-subtitle">Riwayat dan status pesanan Anda</p>
    </div>

    <?php if (mysqli_num_rows($ordersQuery) > 0): ?>
        <div style="display: grid; gap: var(--space-lg);">
            <?php while ($order = mysqli_fetch_assoc($ordersQuery)): ?>
                <?php
                // Get order items
                $itemsQuery = mysqli_query($conn, "SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = {$order['id']}");
                $itemCount = mysqli_num_rows($itemsQuery);
                ?>

                <div class="card">
                    <div class="card-body">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-md); margin-bottom: var(--space-lg);">
                            <div>
                                <p
                                    style="color: var(--text-light); font-size: var(--font-size-sm); margin-bottom: var(--space-xs);">
                                    Pesanan #<?php echo sanitize($order['order_number']); ?>
                                </p>
                                <p style="font-size: var(--font-size-sm); color: var(--text-light);">
                                    <i class="fas fa-calendar"></i> <?php echo formatDate($order['created_at']); ?>
                                </p>
                            </div>
                            <div style="display: flex; gap: var(--space-sm);">
                                <span class="badge <?php echo getStatusBadge($order['status']); ?>">
                                    <?php echo getStatusLabel($order['status']); ?>
                                </span>
                                <span class="badge <?php echo getStatusBadge($order['payment_status']); ?>">
                                    <?php echo getStatusLabel($order['payment_status']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Order Items Preview -->
                        <div
                            style="display: flex; gap: var(--space-md); flex-wrap: wrap; padding: var(--space-md) 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
                            <?php
                            $displayCount = 0;
                            while ($item = mysqli_fetch_assoc($itemsQuery)):
                                if ($displayCount < 3):
                                    ?>
                                    <div style="display: flex; align-items: center; gap: var(--space-sm);">
                                        <div
                                            style="width: 50px; height: 50px; background: var(--bg-color); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;">
                                            <?php if ($item['image'] && file_exists('uploads/products/' . $item['image'])): ?>
                                                <img src="uploads/products/<?php echo $item['image']; ?>" alt=""
                                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-md);">
                                            <?php else: ?>
                                                🏀
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p style="font-weight: 500; font-size: var(--font-size-sm); margin: 0;">
                                                <?php echo sanitize(substr($item['name'], 0, 20)); ?>
                                                <?php echo strlen($item['name']) > 20 ? '...' : ''; ?>
                                            </p>
                                            <p style="color: var(--text-light); font-size: var(--font-size-xs); margin: 0;">
                                                x<?php echo $item['quantity']; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php
                                endif;
                                $displayCount++;
                            endwhile;
                            ?>
                            <?php if ($itemCount > 3): ?>
                                <div
                                    style="display: flex; align-items: center; color: var(--text-light); font-size: var(--font-size-sm);">
                                    +<?php echo $itemCount - 3; ?> item lainnya
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Order Footer -->
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-top: var(--space-md); flex-wrap: wrap; gap: var(--space-md);">
                            <div>
                                <span style="color: var(--text-light); font-size: var(--font-size-sm);">Total: </span>
                                <span style="font-size: var(--font-size-xl); font-weight: 700; color: var(--accent-color);">
                                    <?php echo formatRupiah($order['total_amount']); ?>
                                </span>
                            </div>

                            <div style="display: flex; gap: var(--space-sm);">
                                <?php if ($order['payment_status'] === 'unpaid' && $order['status'] === 'pending'): ?>
                                    <a href="payment.php?order=<?php echo urlencode($order['order_number']); ?>"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-credit-card"></i> Bayar
                                    </a>
                                <?php endif; ?>
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <?php if ($order['payment_status'] === 'paid'): ?>
                                    <a href="invoice.php?order_id=<?php echo $order['id']; ?>" class="btn btn-secondary btn-sm"
                                        target="_blank">
                                        <i class="fas fa-file-invoice"></i> Invoice
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <h3>Belum Ada Pesanan</h3>
            <p>Anda belum melakukan pemesanan apapun.</p>
            <a href="shop.php" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Mulai Belanja
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>