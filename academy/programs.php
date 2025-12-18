<?php
/** Wolvebite Academy - Programs Page */
$pageTitle = 'Program Latihan';
require_once 'includes/header.php';

// Filter by level
$levelFilter = isset($_GET['level']) ? escapeSQL($conn, $_GET['level']) : '';

$sql = "SELECT p.*, c.name as coach_name, 
        (SELECT COUNT(*) FROM academy_enrollments e WHERE e.program_id = p.id AND e.status IN ('approved', 'pending')) as enrolled_count
        FROM academy_programs p 
        LEFT JOIN academy_coaches c ON p.coach_id = c.id 
        WHERE p.status = 'active'";

if ($levelFilter) {
    $sql .= " AND p.level = '$levelFilter'";
}

$sql .= " ORDER BY p.level, p.name";
$programs = mysqli_query($conn, $sql);
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-basketball-ball"></i> Program Latihan</h1>
        <p class="section-subtitle">Pilih program yang sesuai dengan usia dan kemampuan Anda</p>
    </div>

    <!-- Level Filter -->
    <div
        style="display: flex; gap: var(--space-md); flex-wrap: wrap; justify-content: center; margin-bottom: var(--space-xl);">
        <a href="programs.php" class="btn <?php echo empty($levelFilter) ? 'btn-primary' : 'btn-outline'; ?>">Semua</a>
        <a href="programs.php?level=beginner"
            class="btn <?php echo $levelFilter === 'beginner' ? 'btn-primary' : 'btn-outline'; ?>">Pemula</a>
        <a href="programs.php?level=intermediate"
            class="btn <?php echo $levelFilter === 'intermediate' ? 'btn-primary' : 'btn-outline'; ?>">Menengah</a>
        <a href="programs.php?level=advanced"
            class="btn <?php echo $levelFilter === 'advanced' ? 'btn-primary' : 'btn-outline'; ?>">Lanjutan</a>
        <a href="programs.php?level=elite"
            class="btn <?php echo $levelFilter === 'elite' ? 'btn-primary' : 'btn-outline'; ?>">Elite</a>
    </div>

    <?php if (mysqli_num_rows($programs) > 0): ?>
        <div class="program-grid">
            <?php while ($program = mysqli_fetch_assoc($programs)): ?>
                <div class="program-card">
                    <div class="program-card-image">
                        🏀
                        <span class="level-badge badge <?php echo getLevelBadge($program['level']); ?>">
                            <?php echo formatLevel($program['level']); ?>
                        </span>
                    </div>
                    <div class="program-card-body">
                        <h3 class="program-card-title"><?php echo sanitize($program['name']); ?></h3>
                        <div class="program-card-meta">
                            <span><i class="fas fa-user-friends"></i> Usia
                                <?php echo $program['age_min']; ?>-<?php echo $program['age_max']; ?> thn</span>
                            <span><i class="fas fa-clock"></i> <?php echo $program['duration_weeks']; ?> minggu</span>
                        </div>
                        <div class="program-card-meta">
                            <span><i class="fas fa-calendar-week"></i>
                                <?php echo $program['sessions_per_week']; ?>x/minggu</span>
                            <span><i class="fas fa-users"></i>
                                <?php echo $program['enrolled_count']; ?>/<?php echo $program['max_participants']; ?>
                                siswa</span>
                        </div>
                        <p class="program-card-desc"><?php echo sanitize($program['description']); ?></p>
                        <div class="program-card-price"><?php echo formatRupiah($program['price']); ?></div>

                        <?php if ($program['coach_name']): ?>
                            <p style="font-size: var(--font-size-sm); color: var(--text-light); margin-bottom: var(--space-md);">
                                <i class="fas fa-user-tie"></i> Coach: <?php echo sanitize($program['coach_name']); ?>
                            </p>
                        <?php endif; ?>

                        <a href="program-detail.php?slug=<?php echo $program['slug']; ?>" class="btn btn-primary btn-block">
                            <i class="fas fa-info-circle"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-basketball-ball"></i>
            <h3>Tidak Ada Program</h3>
            <p>Belum ada program yang tersedia untuk filter ini.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>