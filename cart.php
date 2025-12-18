<?php
/**
 * Wolvebite Community - Cart Page
 */
$pageTitle = 'Keranjang Belanja';
require_once 'includes/header.php';
requireLogin();

// Get cart items
$cartItems = getCartItems();
$cartTotal = getCartTotal();
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h1>
        <p class="section-subtitle">Review item sebelum checkout</p>
    </div>
    
    <?php if (!empty($cartItems)): ?>
        <div class="cart-container">
            <!-- Cart Items -->
            <div class="cart-items">
                <h3 style="margin-bottom: var(--space-lg);">Item dalam Keranjang (<?php echo count($cartItems); ?>)</h3>
                
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <?php if ($item['image'] && file_exists('uploads/products/' . $item['image'])): ?>
                                <img src="uploads/products/<?php echo $item['image']; ?>" alt="<?php echo sanitize($item['name']); ?>">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 2rem; background: var(--bg-color);">🏀</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cart-item-details">
                            <h4><?php echo sanitize($item['name']); ?></h4>
                            <p class="cart-item-price"><?php echo formatRupiah($item['price']); ?></p>
                            <p style="font-size: var(--font-size-sm); color: var(--text-light);">
                                Subtotal: <?php echo formatRupiah($item['price'] * $item['quantity']); ?>
                            </p>
                        </div>
                        
                        <div class="cart-item-actions">
                            <!-- Update Quantity Form -->
                            <form action="controllers/cart.php" method="POST" style="display: flex; align-items: center; gap: var(--space-sm);">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                
                                <div class="qty-control">
                                    <button type="button" class="qty-btn" data-action="decrease">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['stock']; ?>" class="qty-input">
                                    <button type="button" class="qty-btn" data-action="increase">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <button type="submit" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </form>
                            
                            <!-- Remove Item -->
                            <form action="controllers/cart.php" method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus item ini dari keranjang?">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Clear Cart -->
                <div style="padding-top: var(--space-lg); border-top: 1px solid var(--border-color);">
                    <form action="controllers/cart.php" method="POST">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline btn-sm" data-confirm="Kosongkan semua item dari keranjang?">
                            <i class="fas fa-trash-alt"></i> Kosongkan Keranjang
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="cart-summary">
                <h3>Ringkasan Belanja</h3>
                
                <div class="summary-row">
                    <span>Subtotal (<?php echo array_sum(array_column($cartItems, 'quantity')); ?> item)</span>
                    <span><?php echo formatRupiah($cartTotal); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Ongkos Kirim</span>
                    <span style="color: var(--success);">Gratis</span>
                </div>
                
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?php echo formatRupiah($cartTotal); ?></span>
                </div>
                
                <a href="checkout.php" class="btn btn-primary btn-lg btn-block" style="margin-top: var(--space-lg);">
                    <i class="fas fa-credit-card"></i> Lanjut ke Checkout
                </a>
                
                <a href="shop.php" class="btn btn-outline btn-block" style="margin-top: var(--space-sm);">
                    <i class="fas fa-arrow-left"></i> Lanjut Belanja
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-cart"></i>
            <h3>Keranjang Kosong</h3>
            <p>Anda belum menambahkan produk apapun ke keranjang.</p>
            <a href="shop.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Mulai Belanja
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
