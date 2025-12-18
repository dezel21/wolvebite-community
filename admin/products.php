<?php
/**
 * Wolvebite Community - Admin Products Management
 */
$pageTitle = 'Kelola Produk';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete product
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];

    // Get product image to delete
    $product = mysqli_query($conn, "SELECT image FROM products WHERE id = $delete_id");
    $productData = mysqli_fetch_assoc($product);

    if (mysqli_query($conn, "DELETE FROM products WHERE id = $delete_id")) {
        // Delete product image
        if ($productData['image'] && file_exists('../uploads/products/' . $productData['image'])) {
            unlink('../uploads/products/' . $productData['image']);
        }
        setFlash('success', 'Produk berhasil dihapus.');
    } else {
        setFlash('error', 'Gagal menghapus produk.');
    }
    header('Location: products.php');
    exit;
}

// Handle add/edit product
$editProduct = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $edit_id");
    $editProduct = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = escapeSQL($conn, $_POST['name']);
    $description = escapeSQL($conn, $_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $category = escapeSQL($conn, $_POST['category']);

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024;

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_types)) {
            setFlash('error', 'Format gambar tidak valid.');
            header('Location: products.php');
            exit;
        }

        if ($_FILES['image']['size'] > $max_size) {
            setFlash('error', 'Ukuran gambar maksimal 5MB.');
            header('Location: products.php');
            exit;
        }

        // Create products upload folder
        $upload_dir = '../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $image = uniqid() . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
    }

    if ($id > 0) {
        // Update product
        $query = "UPDATE products SET name = '$name', description = '$description', price = $price, stock = $stock, category = '$category'";
        if ($image) {
            // Delete old image
            $oldProduct = mysqli_query($conn, "SELECT image FROM products WHERE id = $id");
            $oldData = mysqli_fetch_assoc($oldProduct);
            if ($oldData['image'] && file_exists('../uploads/products/' . $oldData['image'])) {
                unlink('../uploads/products/' . $oldData['image']);
            }
            $query .= ", image = '$image'";
        }
        $query .= " WHERE id = $id";

        if (mysqli_query($conn, $query)) {
            setFlash('success', 'Produk berhasil diperbarui.');
        } else {
            setFlash('error', 'Gagal memperbarui produk.');
        }
    } else {
        // Add new product
        $query = "INSERT INTO products (name, description, price, stock, image, category) VALUES ('$name', '$description', $price, $stock, '$image', '$category')";

        if (mysqli_query($conn, $query)) {
            setFlash('success', 'Produk berhasil ditambahkan.');
        } else {
            setFlash('error', 'Gagal menambahkan produk.');
        }
    }
    header('Location: products.php');
    exit;
}

// Get all products
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");
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
                <a href="products.php" class="admin-nav-link active"><i class="fas fa-box"></i> Kelola Produk</a>
                <a href="orders.php" class="admin-nav-link"><i class="fas fa-shopping-bag"></i> Pesanan</a>
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
                <h1>Kelola Produk</h1>
                <button class="btn btn-primary"
                    onclick="document.getElementById('productModal').classList.add('active')">
                    <i class="fas fa-plus"></i> Tambah Produk
                </button>
            </div>

            <?php displayFlash(); ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Gambar</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = mysqli_fetch_assoc($products)): ?>
                                    <tr>
                                        <td>
                                            <div
                                                style="width: 60px; height: 60px; background: var(--bg-color); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;">
                                                <?php if ($product['image'] && file_exists('../uploads/products/' . $product['image'])): ?>
                                                    <img src="../uploads/products/<?php echo $product['image']; ?>" alt=""
                                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-md);">
                                                <?php else: ?>
                                                    🏀
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo sanitize($product['name']); ?></strong>
                                            <p style="font-size: var(--font-size-sm); color: var(--text-light); margin: 0;">
                                                <?php echo sanitize(substr($product['description'], 0, 50)); ?>...
                                            </p>
                                        </td>
                                        <td><span
                                                class="badge badge-primary"><?php echo sanitize($product['category'] ?? 'Umum'); ?></span>
                                        </td>
                                        <td><?php echo formatRupiah($product['price']); ?></td>
                                        <td>
                                            <span class="<?php echo $product['stock'] < 10 ? 'text-danger' : ''; ?>">
                                                <?php echo $product['stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="products.php?edit=<?php echo $product['id']; ?>"
                                                class="btn btn-sm btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="products.php?delete=<?php echo $product['id']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Product Modal -->
    <div class="modal-overlay" id="productModal" <?php echo $editProduct ? 'style="opacity:1;visibility:visible;"' : ''; ?>>
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h3><?php echo $editProduct ? 'Edit Produk' : 'Tambah Produk'; ?></h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo $editProduct['id'] ?? ''; ?>">

                    <div class="form-group">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="name" class="form-control"
                            value="<?php echo sanitize($editProduct['name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control"
                            rows="3"><?php echo sanitize($editProduct['description'] ?? ''); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                        <div class="form-group">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control"
                                value="<?php echo $editProduct['price'] ?? ''; ?>" min="0" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stock" class="form-control"
                                value="<?php echo $editProduct['stock'] ?? 0; ?>" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-control">
                            <option value="">Pilih Kategori</option>
                            <option value="Bola" <?php echo ($editProduct['category'] ?? '') === 'Bola' ? 'selected' : ''; ?>>Bola</option>
                            <option value="Sepatu" <?php echo ($editProduct['category'] ?? '') === 'Sepatu' ? 'selected' : ''; ?>>Sepatu</option>
                            <option value="Apparel" <?php echo ($editProduct['category'] ?? '') === 'Apparel' ? 'selected' : ''; ?>>Apparel</option>
                            <option value="Aksesoris" <?php echo ($editProduct['category'] ?? '') === 'Aksesoris' ? 'selected' : ''; ?>>Aksesoris</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gambar Produk</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <span
                            class="form-hint"><?php echo $editProduct ? 'Kosongkan jika tidak ingin mengubah gambar' : 'Format: JPG, PNG, GIF (Maks. 5MB)'; ?></span>
                        <?php if ($editProduct && $editProduct['image']): ?>
                            <div style="margin-top: var(--space-sm);">
                                <img src="../uploads/products/<?php echo $editProduct['image']; ?>" alt=""
                                    style="max-width: 100px; border-radius: var(--radius-md);">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
            window.location.href = 'products.php';
        }
    </script>
</body>

</html>