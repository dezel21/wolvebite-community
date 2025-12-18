<?php
/**
 * Wolvebite Community - Admin Uploads Management
 */
$pageTitle = 'Upload File';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete file
if (isset($_GET['delete'])) {
    $file_id = (int) $_GET['delete'];

    $file = mysqli_query($conn, "SELECT * FROM uploads WHERE id = $file_id");
    if (mysqli_num_rows($file) > 0) {
        $fileData = mysqli_fetch_assoc($file);

        if (mysqli_query($conn, "DELETE FROM uploads WHERE id = $file_id")) {
            // Delete physical file
            $filepath = '../uploads/' . $fileData['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            setFlash('success', 'File berhasil dihapus.');
        } else {
            setFlash('error', 'Gagal menghapus file.');
        }
    }
    header('Location: uploads.php');
    exit;
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $description = escapeSQL($conn, $_POST['description'] ?? '');
    $category = escapeSQL($conn, $_POST['category'] ?? '');
    $user_id = $_SESSION['user_id'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
        $max_size = 10 * 1024 * 1024;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_types)) {
            setFlash('error', 'Format file tidak diizinkan.');
        } elseif ($file['size'] > $max_size) {
            setFlash('error', 'Ukuran file maksimal 10MB.');
        } else {
            // Create uploads folder
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $original_name = $file['name'];
            $filename = uniqid() . '_' . time() . '.' . $ext;
            $file_type = $file['type'];
            $file_size = $file['size'];

            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $query = "INSERT INTO uploads (uploaded_by, filename, original_name, file_type, file_size, description, category) 
                          VALUES ($user_id, '$filename', '$original_name', '$file_type', $file_size, '$description', '$category')";

                if (mysqli_query($conn, $query)) {
                    setFlash('success', 'File berhasil diupload!');
                } else {
                    unlink($upload_dir . $filename);
                    setFlash('error', 'Gagal menyimpan data file.');
                }
            } else {
                setFlash('error', 'Gagal mengupload file.');
            }
        }
    } else {
        setFlash('error', 'Terjadi kesalahan saat upload.');
    }

    header('Location: uploads.php');
    exit;
}

// Get all uploads
$uploads = mysqli_query($conn, "SELECT u.*, us.username as uploader_name 
                                 FROM uploads u 
                                 JOIN users us ON u.uploaded_by = us.id 
                                 ORDER BY u.created_at DESC");

$pendingBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'"))['count'];

function formatFileSize($bytes)
{
    if ($bytes >= 1048576)
        return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)
        return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}
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
                <a href="orders.php" class="admin-nav-link"><i class="fas fa-shopping-bag"></i> Pesanan</a>
                <a href="bookings.php" class="admin-nav-link">
                    <i class="fas fa-calendar-check"></i> Booking
                    <?php if ($pendingBookings > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingBookings; ?></span><?php endif; ?>
                </a>
                <a href="uploads.php" class="admin-nav-link active"><i class="fas fa-file-upload"></i> Upload File</a>
                <div style="margin-top: auto; padding-top: var(--space-xl);">
                    <a href="../index.php" class="admin-nav-link"><i class="fas fa-home"></i> Ke Website</a>
                    <a href="../logout.php" class="admin-nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Upload File</h1>
                <button class="btn btn-primary"
                    onclick="document.getElementById('uploadModal').classList.add('active')">
                    <i class="fas fa-upload"></i> Upload File Baru
                </button>
            </div>

            <?php displayFlash(); ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Kategori</th>
                                    <th>Ukuran</th>
                                    <th>Uploader</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($uploads) > 0): ?>
                                    <?php while ($upload = mysqli_fetch_assoc($uploads)): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo sanitize($upload['original_name']); ?></strong>
                                                <?php if ($upload['description']): ?>
                                                    <p style="font-size: var(--font-size-sm); color: var(--text-light); margin: 0;">
                                                        <?php echo sanitize(substr($upload['description'], 0, 40)); ?>...
                                                    </p>
                                                <?php endif; ?>
                                            </td>
                                            <td><span
                                                    class="badge badge-primary"><?php echo sanitize($upload['category'] ?? 'Umum'); ?></span>
                                            </td>
                                            <td><?php echo formatFileSize($upload['file_size']); ?></td>
                                            <td><?php echo sanitize($upload['uploader_name']); ?></td>
                                            <td><?php echo formatDate($upload['created_at']); ?></td>
                                            <td>
                                                <a href="../download.php?download=<?php echo $upload['id']; ?>"
                                                    class="btn btn-sm btn-secondary" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="uploads.php?delete=<?php echo $upload['id']; ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Yakin ingin menghapus file ini?')" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada file yang diupload</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Upload Modal -->
    <div class="modal-overlay" id="uploadModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Upload File Baru</h3>
                <button class="modal-close"
                    onclick="document.getElementById('uploadModal').classList.remove('active')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Pilih File</label>
                        <input type="file" name="file" class="form-control" required>
                        <span class="form-hint">Format: PDF, JPG, PNG, DOC, XLS (Maks. 10MB)</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-control">
                            <option value="">Pilih Kategori</option>
                            <option value="Jadwal">Jadwal</option>
                            <option value="Materi">Materi</option>
                            <option value="Dokumen">Dokumen</option>
                            <option value="Formulir">Formulir</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control"
                            placeholder="Deskripsi singkat file..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline"
                        onclick="document.getElementById('uploadModal').classList.remove('active')">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>