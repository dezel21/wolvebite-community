<?php
/**
 * Wolvebite Community - Checkout Page
 */
$pageTitle = 'Checkout';
require_once 'includes/header.php';
requireLogin();

// Get cart items
$cartItems = getCartItems();
$cartTotal = getCartTotal();

// Redirect if cart is empty
if (empty($cartItems)) {
    setFlash('warning', 'Keranjang Anda kosong. Silakan tambah produk terlebih dahulu.');
    header('Location: shop.php');
    exit;
}

// Get user data for pre-filling form
$user = getCurrentUser();

// Process checkout
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'transfer';

    // Validation
    if (empty($shipping_address)) {
        $errors[] = 'Alamat pengiriman wajib diisi.';
    }

    if (empty($phone)) {
        $errors[] = 'Nomor telepon wajib diisi.';
    }

    if (empty($errors)) {
        // Generate order number
        $order_number = generateOrderNumber();
        $user_id = $_SESSION['user_id'];
        $shipping_address_escaped = escapeSQL($conn, $shipping_address);
        $notes_escaped = escapeSQL($conn, $notes);
        $payment_method_escaped = escapeSQL($conn, $payment_method);

        // Create order
        $orderQuery = "INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, shipping_address, notes) 
                       VALUES ($user_id, '$order_number', $cartTotal, 'pending', '$payment_method_escaped', '$shipping_address_escaped', '$notes_escaped')";

        if (mysqli_query($conn, $orderQuery)) {
            $order_id = mysqli_insert_id($conn);

            // Create order items and update stock
            $allItemsAdded = true;
            foreach ($cartItems as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];

                // Insert order item
                $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                              VALUES ($order_id, $product_id, $quantity, $price)";

                if (!mysqli_query($conn, $itemQuery)) {
                    $allItemsAdded = false;
                }

                // Update product stock
                mysqli_query($conn, "UPDATE products SET stock = stock - $quantity WHERE id = $product_id");
            }

            if ($allItemsAdded) {
                // Clear cart
                mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

                // Update user phone if empty
                if (empty($user['phone']) && !empty($phone)) {
                    $phone_escaped = escapeSQL($conn, $phone);
                    mysqli_query($conn, "UPDATE users SET phone = '$phone_escaped' WHERE id = $user_id");
                }

                setFlash('success', "Pesanan berhasil dibuat! Nomor pesanan: $order_number");
                header("Location: payment.php?order=$order_number");
                exit;
            } else {
                $errors[] = 'Terjadi kesalahan saat memproses pesanan.';
            }
        } else {
            $errors[] = 'Gagal membuat pesanan. Silakan coba lagi.';
        }
    }
}
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-credit-card"></i> Checkout</h1>
        <p class="section-subtitle">Selesaikan pesanan Anda</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php foreach ($errors as $error): ?>
                    <div><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <form id="checkoutForm" method="POST" action="">
        <div class="cart-container">
            <!-- Shipping Details -->
            <div class="cart-items">
                <h3 style="margin-bottom: var(--space-lg);">Informasi Pengiriman</h3>

                <div class="form-group">
                    <label class="form-label" for="name">Nama Penerima</label>
                    <input type="text" id="name" class="form-control" value="<?php echo sanitize($user['username']); ?>"
                        readonly>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" class="form-control" value="<?php echo sanitize($user['email']); ?>"
                        readonly>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon *</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                        value="<?php echo sanitize($user['phone'] ?? ''); ?>" placeholder="08xxxxxxxxxx" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="shipping_address">Alamat Pengiriman *</label>
                    <textarea id="shipping_address" name="shipping_address" class="form-control"
                        placeholder="Masukkan alamat lengkap termasuk kode pos"
                        required><?php echo sanitize($user['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Catatan (opsional)</label>
                    <textarea id="notes" name="notes" class="form-control"
                        placeholder="Catatan tambahan untuk pesanan..."></textarea>
                </div>

                <h3 style="margin: var(--space-xl) 0 var(--space-lg);">Metode Pembayaran</h3>

                <div style="display: grid; gap: var(--space-md);">
                    <label class="form-control"
                        style="display: flex; align-items: center; gap: var(--space-md); cursor: pointer; padding: var(--space-lg);">
                        <input type="radio" name="payment_method" value="transfer" checked>
                        <div>
                            <strong>Transfer Bank</strong>
                            <p style="color: var(--text-light); font-size: var(--font-size-sm); margin: 0;">
                                BCA, Mandiri, BNI, BRI
                            </p>
                        </div>
                    </label>

                    <label class="form-control"
                        style="display: flex; align-items: center; gap: var(--space-md); cursor: pointer; padding: var(--space-lg);">
                        <input type="radio" name="payment_method" value="ewallet">
                        <div>
                            <strong>E-Wallet</strong>
                            <p style="color: var(--text-light); font-size: var(--font-size-sm); margin: 0;">
                                GoPay, OVO, DANA, ShopeePay
                            </p>
                        </div>
                    </label>

                    <label class="form-control"
                        style="display: flex; align-items: center; gap: var(--space-md); cursor: pointer; padding: var(--space-lg);">
                        <input type="radio" name="payment_method" value="cod">
                        <div>
                            <strong>COD (Bayar di Tempat)</strong>
                            <p style="color: var(--text-light); font-size: var(--font-size-sm); margin: 0;">
                                Bayar saat barang diterima
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="cart-summary">
                <h3>Ringkasan Pesanan</h3>

                <div style="max-height: 300px; overflow-y: auto; margin-bottom: var(--space-lg);">
                    <?php foreach ($cartItems as $item): ?>
                        <div
                            style="display: flex; gap: var(--space-md); padding: var(--space-sm) 0; border-bottom: 1px solid var(--border-color);">
                            <div style="flex: 1;">
                                <strong
                                    style="font-size: var(--font-size-sm);"><?php echo sanitize($item['name']); ?></strong>
                                <p style="color: var(--text-light); font-size: var(--font-size-xs); margin: 0;">
                                    <?php echo $item['quantity']; ?> x <?php echo formatRupiah($item['price']); ?>
                                </p>
                            </div>
                            <span style="font-weight: 600; font-size: var(--font-size-sm);">
                                <?php echo formatRupiah($item['price'] * $item['quantity']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatRupiah($cartTotal); ?></span>
                </div>

                <div class="summary-row">
                    <span>Ongkos Kirim</span>
                    <span style="color: var(--success);">Gratis</span>
                </div>

                <div class="summary-row total">
                    <span>Total Pembayaran</span>
                    <span><?php echo formatRupiah($cartTotal); ?></span>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: var(--space-lg);">
                    <i class="fas fa-check-circle"></i> Buat Pesanan
                </button>

                <a href="cart.php" class="btn btn-outline btn-block" style="margin-top: var(--space-sm);">
                    <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                </a>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>