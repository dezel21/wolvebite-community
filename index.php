<?php
/**
 * Wolvebite Community - Landing Page
 */
$pageTitle = 'Beranda';
require_once 'includes/header.php';

// Get featured products
$featuredProducts = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Bergabung dengan <span class="highlight">Wolvebite</span> Community
                </h1>
                <p class="hero-subtitle">
                    Komunitas basket terbaik untuk mengasah skill, mengikuti kompetisi, dan bertemu dengan sesama
                    pecinta basket.
                    Booking lapangan, belanja perlengkapan, dan akses materi latihan eksklusif.
                </p>
                <div class="hero-buttons">
                    <?php if (isLoggedIn()): ?>
                        <a href="booking.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-check"></i> Booking Lapangan
                        </a>
                        <a href="shop.php" class="btn btn-outline btn-lg" style="border-color: #fff; color: #fff;">
                            <i class="fas fa-shopping-bag"></i> Kunjungi Shop
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </a>
                        <a href="login.php" class="btn btn-outline btn-lg" style="border-color: #fff; color: #fff;">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-image">
                <div style="font-size: 12rem; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.3));">🏀</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section" style="background: var(--card-bg);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Kenapa Wolvebite?</h2>
            <p class="section-subtitle">Fasilitas dan layanan terbaik untuk komunitas basket</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-basketball-ball"></i>
                </div>
                <div class="stat-info">
                    <h3>Latihan Rutin</h3>
                    <p>Program latihan terstruktur setiap minggu</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Booking Mudah</h3>
                    <p>Pesan lapangan basket secara online</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-info">
                    <h3>Shop Lengkap</h3>
                    <p>Perlengkapan basket berkualitas</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Komunitas Solid</h3>
                    <p>Jaringan pecinta basket se-Indonesia</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Produk Unggulan</h2>
            <p class="section-subtitle">Perlengkapan basket terbaik untuk performa maksimal</p>
        </div>

        <div class="product-grid">
            <?php if (mysqli_num_rows($featuredProducts) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($featuredProducts)): ?>
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
                            <p class="product-price"><?php echo formatRupiah($product['price']); ?></p>
                            <p class="product-stock <?php echo $product['stock'] < 5 ? 'low' : ''; ?>">
                                Stok: <?php echo $product['stock']; ?> tersedia
                            </p>
                            <?php if (isLoggedIn()): ?>
                                <form action="controllers/cart.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary btn-block">
                                    <i class="fas fa-sign-in-alt"></i> Login untuk Beli
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-box-open"></i>
                    <h3>Produk Belum Tersedia</h3>
                    <p>Produk akan segera ditambahkan.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="shop.php" class="btn btn-secondary btn-lg">
                Lihat Semua Produk <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section" style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff;">
    <div class="container text-center">
        <h2 style="font-size: var(--font-size-3xl); margin-bottom: var(--space-md);">
            Siap Bergabung dengan Komunitas?
        </h2>
        <p
            style="font-size: var(--font-size-lg); opacity: 0.9; margin-bottom: var(--space-xl); max-width: 600px; margin-left: auto; margin-right: auto;">
            Daftarkan diri Anda sekarang dan nikmati semua fasilitas eksklusif Wolvebite Community.
        </p>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus"></i> Daftar Gratis
            </a>
        <?php else: ?>
            <a href="booking.php" class="btn btn-primary btn-lg">
                <i class="fas fa-calendar-check"></i> Booking Lapangan Sekarang
            </a>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>