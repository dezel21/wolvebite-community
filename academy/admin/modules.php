<?php
// Wolvebite Academy - Admin Modules
$pageTitle = 'Kelola Modul';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $module = mysqli_query($conn, "SELECT filename FROM academy_modules WHERE id = $id");
    if (mysqli_num_rows($module) > 0) {
        $data = mysqli_fetch_assoc($module);
        $filepath = '../uploads/modules/' . $data['filename'];
        if (file_exists($filepath))
            unlink($filepath);
        mysqli_query($conn, "DELETE FROM academy_modules WHERE id = $id");
        setFlash('success', 'Modul berhasil dihapus.');
    }
    header('Location: modules.php');
    exit;
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $program_id = (int) $_POST['program_id'] ?: 'NULL';
    $title = escapeSQL($conn, $_POST['title']);
    $description = escapeSQL($conn, $_POST['description']);
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $file = $_FILES['file'];

    $upload_dir = '../uploads/modules/';
    if (!is_dir($upload_dir))
        mkdir($upload_dir, 0755, true);

    $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        setFlash('error', 'Format file tidak diizinkan.');
    } elseif ($file['size'] > 10 * 1024 * 1024) {
        setFlash('error', 'Ukuran file maksimal 10MB.');
    } else {
        $filename = uniqid() . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $query = "INSERT INTO academy_modules (program_id, title, description, filename, original_name, file_type, file_size, is_public, uploaded_by) 
                      VALUES ($program_id, '$title', '$description', '$filename', '{$file['name']}', '{$file['type']}', {$file['size']}, $is_public, {$_SESSION['user_id']})";
            if (mysqli_query($conn, $query)) {
                setFlash('success', 'Modul berhasil diupload.');
            }
        }
    }
    header('Location: modules.php');
    exit;
}

$modules = mysqli_query($conn, "SELECT m.*, p.name as program_name 
                                FROM academy_modules m 
                                LEFT JOIN academy_programs p ON m.program_id = p.id 
                                ORDER BY m.created_at DESC");

$programs = mysqli_query($conn, "SELECT id, name FROM academy_programs WHERE status = 'active'");

$pendingEnrollments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_enrollments WHERE status = 'pending'"))['count'];
$pendingBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_bookings WHERE status = 'pending'"))['count'];

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
    <title><?php echo $pageTitle; ?> - Wolvebite Academy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="<?php echo SITE_URL; ?>/academy/" class="nav-logo">
                <span class="logo-icon">🎓</span>
                <span class="logo-text">Academy Admin</span>
            </a>
            <nav class="admin-nav">
                <a href="index.php" class="admin-nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="programs.php" class="admin-nav-link"><i class="fas fa-basketball-ball"></i> Programs</a>
                <a href="coaches.php" class="admin-nav-link"><i class="fas fa-user-tie"></i> Coaches</a>
                <a href="schedule.php" class="admin-nav-link"><i class="fas fa-calendar-alt"></i> Schedule</a>
                <a href="enrollments.php" class="admin-nav-link"><i class="fas fa-user-graduate"></i> Enrollments
                    <?php if ($pendingEnrollments > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingEnrollments; ?></span><?php endif; ?></a>
                <a href="bookings.php" class="admin-nav-link"><i class="fas fa-calendar-check"></i> Bookings
                    <?php if ($pendingBookings > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingBookings; ?></span><?php endif; ?></a>
                <a href="modules.php" class="admin-nav-link active"><i class="fas fa-book"></i> Modules</a>
                <div style="margin-top: auto; padding-top: var(--space-xl);">
                    <a href="<?php echo SITE_URL; ?>/academy/" class="admin-nav-link"><i class="fas fa-home"></i> Ke
                        Academy</a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="admin-nav-link"><i
                            class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1>Kelola Modul</h1>
                <button class="btn btn-primary"
                    onclick="document.getElementById('uploadModal').classList.add('active')">
                    <i class="fas fa-upload"></i> Upload Modul
                </button>
            </div>

            <?php displayFlash(); ?>

            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Program</th>
                                <th>Ukuran</th>
                                <th>Akses</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($m = mysqli_fetch_assoc($modules)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo sanitize($m['title']); ?></strong>
                                        <p style="font-size: var(--font-size-sm); color: var(--text-light); margin: 0;">
                                            <?php echo sanitize($m['original_name']); ?>
                                        </p>
                                    </td>
                                    <td><?php echo sanitize($m['program_name'] ?? 'Umum'); ?></td>
                                    <td><?php echo formatFileSize($m['file_size']); ?></td>
                                    <td>
                                        <span
                                            class="badge <?php echo $m['is_public'] ? 'badge-success' : 'badge-secondary'; ?>">
                                            <?php echo $m['is_public'] ? 'Public' : 'Member Only'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../uploads/modules/<?php echo $m['filename']; ?>"
                                            class="btn btn-sm btn-secondary" target="_blank"><i class="fas fa-eye"></i></a>
                                        <a href="modules.php?delete=<?php echo $m['id']; ?>" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Hapus modul ini?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="uploadModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Upload Modul Baru</h3>
                <button class="modal-close"
                    onclick="document.getElementById('uploadModal').classList.remove('active')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Judul Modul</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Program (opsional)</label>
                        <select name="program_id" class="form-control">
                            <option value="">Umum (tanpa program)</option>
                            <?php mysqli_data_seek($programs, 0);
                            while ($p = mysqli_fetch_assoc($programs)): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo sanitize($p['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">File</label>
                        <input type="file" name="file" class="form-control" required>
                        <span class="form-hint">Format: PDF, DOC, PPT, JPG, PNG (Maks. 10MB)</span>
                    </div>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer;">
                            <input type="checkbox" name="is_public">
                            <span>Akses Publik (dapat didownload tanpa enrollment)</span>
                        </label>
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