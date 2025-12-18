<?php
/**
 * Wolvebite Academy - Homepage
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = 'Home';
require_once 'includes/header.php';

// Get featured programs
$programs = getPrograms(4);

// Get coaches
$coaches = getCoaches();
$coachCount = mysqli_num_rows($coaches);
mysqli_data_seek($coaches, 0);

// Stats
$totalPrograms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_programs WHERE status = 'active'"))['count'];
$totalEnrollments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_enrollments WHERE status = 'approved'"))['count'];
?>

<!-- Hero Section -->
<section class="academy-hero">
    <div class="container">
        <h1>Wolvebite <span style="color: var(--accent-color);">Academy</span></h1>
        <p>Akademi Basket Profesional untuk Semua Usia. Kembangkan skill basket Anda bersama pelatih berpengalaman.</p>
        <div style="display: flex; gap: var(--space-md); justify-content: center; flex-wrap: wrap;">
            <a href="programs.php" class="btn btn-primary btn-lg">
                <i class="fas fa-basketball-ball"></i> Lihat Program
            </a>
            <?php if (!isLoggedIn()): ?>
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-outline btn-lg"
                    style="border-color: #fff; color: #fff;">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section style="background: var(--card-bg);">
    <div class="container">
        <div class="academy-stats">
            <div class="academy-stat">
                <div class="academy-stat-number"><?php echo $totalPrograms; ?></div>
                <div class="academy-stat-label">Program Aktif</div>
            </div>
            <div class="academy-stat">
                <div class="academy-stat-number"><?php echo $coachCount; ?></div>
                <div class="academy-stat-label">Coach Profesional</div>
            </div>
            <div class="academy-stat">
                <div class="academy-stat-number"><?php echo $totalEnrollments; ?>+</div>
                <div class="academy-stat-label">Siswa Terdaftar</div>
            </div>
            <div class="academy-stat">
                <div class="academy-stat-number">10+</div>
                <div class="academy-stat-label">Tahun Pengalaman</div>
            </div>
        </div>
    </div>
</section>

<!-- Programs Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Program Latihan</h2>
            <p class="section-subtitle">Pilih program yang sesuai dengan usia dan level kemampuan Anda</p>
        </div>

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
                            <span><i class="fas fa-calendar-week"></i>
                                <?php echo $program['sessions_per_week']; ?>x/minggu</span>
                        </div>
                        <p class="program-card-desc"><?php echo sanitize($program['description']); ?></p>
                        <div class="program-card-price"><?php echo formatRupiah($program['price']); ?></div>
                        <a href="program-detail.php?slug=<?php echo $program['slug']; ?>" class="btn btn-primary btn-block">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div style="text-align: center; margin-top: var(--space-xl);">
            <a href="programs.php" class="btn btn-outline">Lihat Semua Program <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- Coaches Section -->
<section class="section" style="background: var(--bg-color);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tim Pelatih Kami</h2>
            <p class="section-subtitle">Dibimbing oleh pelatih profesional dan berpengalaman</p>
        </div>

        <div class="coach-grid">
            <?php while ($coach = mysqli_fetch_assoc($coaches)): ?>
                <div class="coach-card">
                    <div class="coach-avatar">
                        <?php if ($coach['photo']): ?>
                            <img src="uploads/coaches/<?php echo $coach['photo']; ?>"
                                alt="<?php echo sanitize($coach['name']); ?>">
                        <?php else: ?>
                            👨‍🏫
                        <?php endif; ?>
                    </div>
                    <h3 class="coach-name"><?php echo sanitize($coach['name']); ?></h3>
                    <p class="coach-specialization"><?php echo sanitize($coach['specialization']); ?></p>
                    <p class="coach-bio"><?php echo sanitize(substr($coach['bio'], 0, 100)); ?>...</p>
                    <div class="coach-experience">
                        <span><i class="fas fa-award"></i> <?php echo $coach['experience_years']; ?> Tahun</span>
                        <span><i class="fas fa-check-circle"></i> Verified</span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div style="text-align: center; margin-top: var(--space-xl);">
            <a href="coaches.php" class="btn btn-outline">Lihat Semua Coach <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section"
    style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); text-align: center;">
    <div class="container">
        <h2 style="font-size: 2.5rem; margin-bottom: var(--space-md);">Siap Bergabung dengan Academy?</h2>
        <p
            style="font-size: var(--font-size-lg); opacity: 0.9; margin-bottom: var(--space-xl); max-width: 600px; margin-left: auto; margin-right: auto;">
            Daftar sekarang dan mulai perjalanan Anda menjadi pemain basket yang lebih baik!
        </p>
        <div style="display: flex; gap: var(--space-md); justify-content: center; flex-wrap: wrap;">
            <a href="programs.php" class="btn btn-lg" style="background: #fff; color: var(--primary-dark);">
                <i class="fas fa-list"></i> Pilih Program
            </a>
            <a href="schedule.php" class="btn btn-outline btn-lg" style="border-color: #fff; color: #fff;">
                <i class="fas fa-calendar"></i> Lihat Jadwal
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>