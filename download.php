<?php
/**
 * Wolvebite Community - Download Page
 */
$pageTitle = 'Download';
require_once 'includes/header.php';
requireLogin();

// Get all uploaded files
$filesQuery = mysqli_query($conn, "SELECT u.*, us.username as uploader_name 
                                   FROM uploads u 
                                   JOIN users us ON u.uploaded_by = us.id 
                                   ORDER BY u.created_at DESC");

// Handle download
if (isset($_GET['download'])) {
    $file_id = (int) $_GET['download'];
    $file = mysqli_query($conn, "SELECT * FROM uploads WHERE id = $file_id");

    if (mysqli_num_rows($file) > 0) {
        $fileData = mysqli_fetch_assoc($file);
        $filepath = 'uploads/' . $fileData['filename'];

        if (file_exists($filepath)) {
            // Set headers for download
            header('Content-Type: ' . $fileData['file_type']);
            header('Content-Disposition: attachment; filename="' . $fileData['original_name'] . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: no-cache');

            readfile($filepath);
            exit;
        }
    }

    setFlash('error', 'File tidak ditemukan.');
    header('Location: download.php');
    exit;
}

// Get categories for filter
$categories = mysqli_query($conn, "SELECT DISTINCT category FROM uploads WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categoryFilter = isset($_GET['category']) ? escapeSQL($conn, $_GET['category']) : '';

if ($categoryFilter) {
    $filesQuery = mysqli_query($conn, "SELECT u.*, us.username as uploader_name 
                                       FROM uploads u 
                                       JOIN users us ON u.uploaded_by = us.id 
                                       WHERE u.category = '$categoryFilter'
                                       ORDER BY u.created_at DESC");
}

/**
 * Format file size
 */
function formatFileSize($bytes)
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Get file icon class
 */
function getFileIcon($file_type)
{
    if (strpos($file_type, 'pdf') !== false) {
        return 'pdf';
    } elseif (strpos($file_type, 'image') !== false) {
        return 'image';
    } else {
        return 'file';
    }
}
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-download"></i> Download Center</h1>
        <p class="section-subtitle">Materi, jadwal, dan dokumen komunitas</p>
    </div>

    <!-- Category Filter -->
    <div
        style="display: flex; gap: var(--space-md); flex-wrap: wrap; justify-content: center; margin-bottom: var(--space-xl);">
        <a href="download.php" class="btn <?php echo empty($categoryFilter) ? 'btn-primary' : 'btn-outline'; ?>">
            Semua File
        </a>
        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
            <a href="download.php?category=<?php echo urlencode($cat['category']); ?>"
                class="btn <?php echo $categoryFilter === $cat['category'] ? 'btn-primary' : 'btn-outline'; ?>">
                <?php echo sanitize($cat['category']); ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Files Grid -->
    <?php if (mysqli_num_rows($filesQuery) > 0): ?>
        <div class="file-grid">
            <?php while ($file = mysqli_fetch_assoc($filesQuery)): ?>
                <div class="file-card">
                    <div class="file-icon <?php echo getFileIcon($file['file_type']); ?>">
                        <?php if (strpos($file['file_type'], 'pdf') !== false): ?>
                            <i class="fas fa-file-pdf"></i>
                        <?php elseif (strpos($file['file_type'], 'image') !== false): ?>
                            <i class="fas fa-file-image"></i>
                        <?php else: ?>
                            <i class="fas fa-file"></i>
                        <?php endif; ?>
                    </div>

                    <div class="file-info">
                        <h4><?php echo sanitize($file['original_name']); ?></h4>
                        <div class="file-meta">
                            <span><i class="fas fa-hdd"></i> <?php echo formatFileSize($file['file_size']); ?></span>
                            <span><i class="fas fa-calendar"></i> <?php echo formatDate($file['created_at']); ?></span>
                        </div>
                        <?php if ($file['description']): ?>
                            <p class="file-desc"><?php echo sanitize($file['description']); ?></p>
                        <?php endif; ?>
                        <?php if ($file['category']): ?>
                            <span class="badge badge-primary" style="margin-bottom: var(--space-sm);">
                                <?php echo sanitize($file['category']); ?>
                            </span>
                        <?php endif; ?>
                        <a href="download.php?download=<?php echo $file['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>Tidak Ada File</h3>
            <p>Belum ada file yang tersedia untuk didownload.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>