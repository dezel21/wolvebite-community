<?php
/**
 * Wolvebite Community - Shop Page
 */
$pageTitle = 'Shop';
require_once 'includes/header.php';

// Get category filter
$category = isset($_GET['category']) ? escapeSQL($conn, $_GET['category']) : '';

// Build query
$query = "SELECT * FROM products WHERE stock > 0";
if ($category) {
    $query .= " AND category = '$category'";
}
$query .= " ORDER BY created_at DESC";

$products = mysqli_query($conn, $query);

// Get categories for filter
$categories = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
?>

<div class="container">
    <!-- Page Header -->
    <div class="section-header">
        <h1 class="section-title">🛒 Wolvebite Shop</h1>
        <p class="section-subtitle">Perlengkapan basket berkualitas untuk performa terbaik</p>
    </div>

    <!-- Category Filter -->
    <div
        style="display: flex; gap: var(--space-md); flex-wrap: wrap; justify-content: center; margin-bottom: var(--space-xl);">
        <a href="shop.php" class="btn <?php echo empty($category) ? 'btn-primary' : 'btn-outline'; ?>">
            Semua Produk
        </a>
        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
            <a href="shop.php?category=<?php echo urlencode($cat['category']); ?>"
                class="btn <?php echo $category === $cat['category'] ? 'btn-primary' : 'btn-outline'; ?>">
                <?php echo sanitize($cat['category']); ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Products Grid -->
    <div class="product-grid">
        <?php if (mysqli_num_rows($products) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($products)): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image'] && file_exists('uploads/products/' . $product['image'])): ?>
                            <img src="uploads/products/<?php echo $product['image']; ?>"
                                alt="<?php echo sanitize($product['name']); ?>">
                        <?php else: ?>
                            🏀
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?php echo sanitize($product['name']); ?></h3>
                        <p class="card-text" style="font-size: var(--font-size-sm); height: 40px; overflow: hidden;">
                            <?php echo sanitize(substr($product['description'], 0, 80)); ?>...
                        </p>
                        <p class="product-price"><?php echo formatRupiah($product['price']); ?></p>
                        <p class="product-stock <?php echo $product['stock'] < 5 ? 'low' : ''; ?>">
                            <i class="fas fa-box"></i> Stok: <?php echo $product['stock']; ?>
                        </p>

                        <?php if (isLoggedIn()): ?>
                            <form action="controllers/cart.php" method="POST" style="margin-top: var(--space-md);">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <div
                                    style="display: flex; gap: var(--space-sm); align-items: center; margin-bottom: var(--space-sm);">
                                    <label style="font-size: var(--font-size-sm);">Jumlah:</label>
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>"
                                        class="form-control" style="width: 70px; padding: var(--space-xs);">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-block" style="margin-top: var(--space-md);">
                                <i class="fas fa-sign-in-alt"></i> Login untuk Beli
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="fas fa-box-open"></i>
                <h3>Tidak Ada Produk</h3>
                <p>Belum ada produk yang tersedia saat ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>