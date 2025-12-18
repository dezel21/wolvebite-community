<?php
/** Wolvebite Academy - Modules Page */
$pageTitle = 'Modul Latihan';
require_once 'includes/header.php';

// Handle download
if (isset($_GET['download']) && isLoggedIn()) {
    $module_id = (int) $_GET['download'];
    $module = mysqli_query($conn, "SELECT * FROM academy_modules WHERE id = $module_id");

    if (mysqli_num_rows($module) > 0) {
        $moduleData = mysqli_fetch_assoc($module);

        // Check access (public, enrolled, or admin)
        $hasAccess = $moduleData['is_public'] || isAdmin();

        if (!$hasAccess && $moduleData['program_id']) {
            $hasAccess = isEnrolled($_SESSION['user_id'], $moduleData['program_id']);
        }

        if ($hasAccess) {
            $filepath = 'uploads/modules/' . $moduleData['filename'];

            if (file_exists($filepath)) {
                header('Content-Type: ' . $moduleData['file_type']);
                header('Content-Disposition: attachment; filename="' . $moduleData['original_name'] . '"');
                header('Content-Length: ' . filesize($filepath));
                readfile($filepath);
                exit;
            }
        }
    }

    setFlash('error', 'File tidak ditemukan atau akses ditolak.');
    header('Location: modules.php');
    exit;
}

// Get programs with modules
$programs = mysqli_query($conn, "SELECT p.*, 
                                  (SELECT COUNT(*) FROM academy_modules m WHERE m.program_id = p.id) as module_count
                                  FROM academy_programs p 
                                  WHERE p.status = 'active'
                                  ORDER BY p.name");

// Get public modules
$publicModules = mysqli_query($conn, "SELECT m.*, p.name as program_name 
                                      FROM academy_modules m 
                                      LEFT JOIN academy_programs p ON m.program_id = p.id
                                      WHERE m.is_public = TRUE
                                      ORDER BY m.created_at DESC");
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-book"></i> Modul Latihan</h1>
        <p class="section-subtitle">Materi pembelajaran untuk mengembangkan skill basket Anda</p>
    </div>

    <!-- Public Modules -->
    <div class="card" style="margin-bottom: var(--space-xl);">
        <div class="card-body">
            <h3 style="margin-bottom: var(--space-lg);">
                <i class="fas fa-unlock"></i> Modul Gratis
            </h3>

            <?php if (mysqli_num_rows($publicModules) > 0): ?>
                <div class="module-list">
                    <?php while ($module = mysqli_fetch_assoc($publicModules)): ?>
                        <div class="module-item">
                            <div class="module-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="module-info">
                                <div class="module-title"><?php echo sanitize($module['title']); ?></div>
                                <div class="module-desc">
                                    <?php echo sanitize($module['description']); ?>
                                    <?php if ($module['program_name']): ?>
                                        <span class="badge badge-primary" style="margin-left: var(--space-sm);">
                                            <?php echo sanitize($module['program_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (isLoggedIn()): ?>
                                <a href="modules.php?download=<?php echo $module['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            <?php else: ?>
                                <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-sm btn-outline">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Belum ada modul gratis.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Program Modules -->
    <h3 style="margin-bottom: var(--space-lg);">
        <i class="fas fa-lock"></i> Modul Premium (untuk Member)
    </h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-lg);">
        <?php while ($program = mysqli_fetch_assoc($programs)): ?>
            <?php if ($program['module_count'] > 0): ?>
                <div class="card">
                    <div class="card-body">
                        <h4 style="margin-bottom: var(--space-sm);"><?php echo sanitize($program['name']); ?></h4>
                        <p style="color: var(--text-light); font-size: var(--font-size-sm); margin-bottom: var(--space-md);">
                            <?php echo $program['module_count']; ?> modul tersedia
                        </p>

                        <?php if (isLoggedIn() && isEnrolled($_SESSION['user_id'], $program['id'])): ?>
                            <a href="program-detail.php?slug=<?php echo $program['slug']; ?>#modules"
                                class="btn btn-primary btn-sm btn-block">
                                <i class="fas fa-eye"></i> Akses Modul
                            </a>
                        <?php else: ?>
                            <a href="program-detail.php?slug=<?php echo $program['slug']; ?>"
                                class="btn btn-outline btn-sm btn-block">
                                <i class="fas fa-info-circle"></i> Lihat Program
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>

    <div style="margin-top: var(--space-xl); text-align: center;">
        <p style="color: var(--text-light); margin-bottom: var(--space-md);">
            Daftar program untuk mengakses semua modul premium.
        </p>
        <a href="programs.php" class="btn btn-primary">
            <i class="fas fa-graduation-cap"></i> Lihat Semua Program
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>