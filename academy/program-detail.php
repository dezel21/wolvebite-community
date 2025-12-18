<?php
/** Wolvebite Academy - Program Detail Page */
$pageTitle = 'Detail Program';
require_once 'includes/header.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: programs.php');
    exit;
}

$program = getProgram($slug);
if (!$program) {
    setFlash('error', 'Program tidak ditemukan.');
    header('Location: programs.php');
    exit;
}

$pageTitle = $program['name'];

// Get schedule for this program
$schedules = getProgramSchedule($program['id']);

// Get modules
$modules = getProgramModules($program['id'], isLoggedIn() ? $_SESSION['user_id'] : null);

// Check enrollment
$isUserEnrolled = isLoggedIn() && isEnrolled($_SESSION['user_id'], $program['id']);
$enrolledCount = getEnrollmentCount($program['id']);
$spotsLeft = $program['max_participants'] - $enrolledCount;
?>

<div class="container">
    <!-- Breadcrumb -->
    <div style="margin-bottom: var(--space-lg);">
        <a href="programs.php" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Programs
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-xl);">
        <!-- Main Content -->
        <div>
            <!-- Program Header -->
            <div class="card" style="margin-bottom: var(--space-xl);">
                <div class="program-card-image"
                    style="height: 250px; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                    🏀
                    <span class="level-badge badge <?php echo getLevelBadge($program['level']); ?>"
                        style="font-size: var(--font-size-base); padding: var(--space-sm) var(--space-md);">
                        <?php echo formatLevel($program['level']); ?>
                    </span>
                </div>
                <div class="card-body">
                    <h1 style="margin-bottom: var(--space-md);"><?php echo sanitize($program['name']); ?></h1>
                    <p
                        style="font-size: var(--font-size-lg); color: var(--text-light); margin-bottom: var(--space-lg);">
                        <?php echo sanitize($program['description']); ?>
                    </p>

                    <div
                        style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-lg); padding: var(--space-lg); background: var(--bg-color); border-radius: var(--radius-md);">
                        <div style="text-align: center;">
                            <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--accent-color);">
                                <?php echo $program['age_min']; ?>-<?php echo $program['age_max']; ?>
                            </div>
                            <div style="font-size: var(--font-size-sm); color: var(--text-light);">Tahun</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--accent-color);">
                                <?php echo $program['duration_weeks']; ?>
                            </div>
                            <div style="font-size: var(--font-size-sm); color: var(--text-light);">Minggu</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--accent-color);">
                                <?php echo $program['sessions_per_week']; ?>x
                            </div>
                            <div style="font-size: var(--font-size-sm); color: var(--text-light);">Per Minggu</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--accent-color);">
                                <?php echo $spotsLeft; ?>
                            </div>
                            <div style="font-size: var(--font-size-sm); color: var(--text-light);">Slot Tersisa</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule -->
            <div class="card" style="margin-bottom: var(--space-xl);">
                <div class="card-body">
                    <h3 style="margin-bottom: var(--space-lg);"><i class="fas fa-calendar-alt"></i> Jadwal Latihan</h3>

                    <?php if (mysqli_num_rows($schedules) > 0): ?>
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Waktu</th>
                                    <th>Lokasi</th>
                                    <th>Coach</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($schedule = mysqli_fetch_assoc($schedules)): ?>
                                    <tr>
                                        <td class="schedule-day"><?php echo formatDay($schedule['day_of_week']); ?></td>
                                        <td><?php echo formatTime($schedule['start_time']); ?> -
                                            <?php echo formatTime($schedule['end_time']); ?></td>
                                        <td><?php echo sanitize($schedule['location']); ?></td>
                                        <td><?php echo sanitize($schedule['coach_name'] ?? 'TBA'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">Jadwal belum tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modules -->
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: var(--space-lg);"><i class="fas fa-book"></i> Modul Latihan</h3>

                    <?php if (mysqli_num_rows($modules) > 0): ?>
                        <div class="module-list">
                            <?php while ($module = mysqli_fetch_assoc($modules)): ?>
                                <div class="module-item">
                                    <div class="module-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <div class="module-info">
                                        <div class="module-title"><?php echo sanitize($module['title']); ?></div>
                                        <div class="module-desc"><?php echo sanitize($module['description']); ?></div>
                                    </div>
                                    <?php if ($isUserEnrolled || $module['is_public'] || isAdmin()): ?>
                                        <a href="modules.php?download=<?php echo $module['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fas fa-lock"></i> Enroll</span>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada modul untuk program ini.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Price Card -->
            <div class="card" style="position: sticky; top: 100px;">
                <div class="card-body">
                    <div style="text-align: center; margin-bottom: var(--space-lg);">
                        <p style="color: var(--text-light); font-size: var(--font-size-sm);">Biaya Program</p>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--accent-color);">
                            <?php echo formatRupiah($program['price']); ?>
                        </div>
                        <p style="color: var(--text-light); font-size: var(--font-size-sm);">untuk
                            <?php echo $program['duration_weeks']; ?> minggu</p>
                    </div>

                    <?php if ($isUserEnrolled): ?>
                        <div class="alert alert-success" style="margin-bottom: var(--space-md);">
                            <i class="fas fa-check-circle"></i> Anda sudah terdaftar di program ini.
                        </div>
                        <a href="my-enrollments.php" class="btn btn-secondary btn-block">
                            <i class="fas fa-list"></i> Lihat Enrollment
                        </a>
                    <?php elseif ($spotsLeft <= 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Program ini sudah penuh.
                        </div>
                    <?php elseif (isLoggedIn()): ?>
                        <a href="enrollment.php?program=<?php echo $program['id']; ?>"
                            class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login untuk Daftar
                        </a>
                    <?php endif; ?>

                    <hr style="margin: var(--space-lg) 0; border-color: var(--border-color);">

                    <!-- Coach Info -->
                    <?php if ($program['coach_name']): ?>
                        <div style="text-align: center;">
                            <p
                                style="color: var(--text-light); font-size: var(--font-size-sm); margin-bottom: var(--space-sm);">
                                Head Coach</p>
                            <div class="coach-avatar"
                                style="width: 80px; height: 80px; font-size: 2rem; margin-bottom: var(--space-sm);">
                                <?php if ($program['coach_photo']): ?>
                                    <img src="uploads/coaches/<?php echo $program['coach_photo']; ?>" alt="">
                                <?php else: ?>
                                    👨‍🏫
                                <?php endif; ?>
                            </div>
                            <h4><?php echo sanitize($program['coach_name']); ?></h4>
                            <p style="color: var(--accent-color); font-size: var(--font-size-sm);">
                                <?php echo sanitize($program['specialization']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>