<?php
/**
 * Wolvebite Community - Admin Orders Management
 */
$pageTitle = 'Kelola Pesanan';
require_once '../includes/functions.php';
requireAdmin();

// Handle status update
if (isset($_GET['update_status'])) {
    $order_id = (int) $_GET['order_id'];
    $new_status = escapeSQL($conn, $_GET['update_status']);

    $valid_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        mysqli_query($conn, "UPDATE orders SET status = '$new_status' WHERE id = $order_id");
        setFlash('success', 'Status pesanan berhasil diperbarui.');
    }
    header('Location: orders.php');
    exit;
}

// Filter by status
$statusFilter = isset($_GET['status']) ? escapeSQL($conn, $_GET['status']) : '';
$whereClause = $statusFilter ? "WHERE o.status = '$statusFilter'" : "";

// Get all orders
$orders = mysqli_query($conn, "SELECT o.*, u.username, u.email 
                                FROM orders o 
                                JOIN users u ON o.user_id = u.id 
                                $whereClause
                                ORDER BY o.created_at DESC");

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
                <a href="orders.php" class="admin-nav-link active"><i class="fas fa-shopping-bag"></i> Pesanan</a>
                <a href="bookings.php" class="admin-nav-link">
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
                <h1>Kelola Pesanan</h1>
            </div>

            <?php displayFlash(); ?>

            <!-- Filter -->
            <div style="display: flex; gap: var(--space-md); margin-bottom: var(--space-xl); flex-wrap: wrap;">
                <a href="orders.php"
                    class="btn <?php echo empty($statusFilter) ? 'btn-primary' : 'btn-outline'; ?>">Semua</a>
                <a href="orders.php?status=pending"
                    class="btn <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline'; ?>">Pending</a>
                <a href="orders.php?status=processing"
                    class="btn <?php echo $statusFilter === 'processing' ? 'btn-primary' : 'btn-outline'; ?>">Processing</a>
                <a href="orders.php?status=shipped"
                    class="btn <?php echo $statusFilter === 'shipped' ? 'btn-primary' : 'btn-outline'; ?>">Shipped</a>
                <a href="orders.php?status=completed"
                    class="btn <?php echo $statusFilter === 'completed' ? 'btn-primary' : 'btn-outline'; ?>">Completed</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Pembayaran</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($orders) > 0): ?>
                                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                                        <tr>
                                            <td><strong><?php echo sanitize($order['order_number']); ?></strong></td>
                                            <td>
                                                <strong><?php echo sanitize($order['username']); ?></strong>
                                                <p style="font-size: var(--font-size-sm); color: var(--text-light); margin: 0;">
                                                    <?php echo sanitize($order['email']); ?>
                                                </p>
                                            </td>
                                            <td><?php echo formatRupiah($order['total_amount']); ?></td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadge($order['payment_status']); ?>">
                                                    <?php echo getStatusLabel($order['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadge($order['status']); ?>">
                                                    <?php echo getStatusLabel($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($order['created_at']); ?></td>
                                            <td>
                                                <select onchange="updateStatus(<?php echo $order['id']; ?>, this.value)"
                                                    class="form-control"
                                                    style="width: auto; padding: var(--space-xs); font-size: var(--font-size-sm);">
                                                    <option value="">Ubah Status</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="processing">Processing</option>
                                                    <option value="shipped">Shipped</option>
                                                    <option value="completed">Completed</option>
                                                    <option value="cancelled">Cancelled</option>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada pesanan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function updateStatus(orderId, status) {
            if (status) {
                window.location.href = 'orders.php?order_id=' + orderId + '&update_status=' + status;
            }
        }
    </script>
</body>

</html>