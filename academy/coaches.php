<?php
/**
 * Wolvebite Academy - Coaches Page
 */
$pageTitle = 'Tim Pelatih';
require_once 'includes/header.php';

$coaches = mysqli_query($conn, "SELECT c.*, 
                                 (SELECT COUNT(*) FROM academy_programs p WHERE p.coach_id = c.id AND p.status = 'active') as program_count
                                 FROM academy_coaches c 
                                 WHERE c.status = 'active' 
                                 ORDER BY c.experience_years DESC");
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-user-tie"></i> Tim Pelatih</h1>
        <p class="section-subtitle">Dibimbing oleh pelatih profesional dan berpengalaman</p>
    </div>

    <?php if (mysqli_num_rows($coaches) > 0): ?>
        <div class="coach-grid">
            <?php while ($coach = mysqli_fetch_assoc($coaches)): ?>
                <div class="coach-card">
                    <div class="coach-avatar">
                        <?php if ($coach['photo']): ?>
                            <img src="uploads/coaches/<?php echo $coach['photo']; ?>" alt="">
                        <?php else: ?>
                            👨‍🏫
                        <?php endif; ?>
                    </div>
                    <h3 class="coach-name"><?php echo sanitize($coach['name']); ?></h3>
                    <p class="coach-specialization"><?php echo sanitize($coach['specialization']); ?></p>
                    <p class="coach-bio"><?php echo sanitize($coach['bio']); ?></p>

                    <div class="coach-experience">
                        <span><i class="fas fa-award"></i> <?php echo $coach['experience_years']; ?> Tahun</span>
                        <span><i class="fas fa-basketball-ball"></i> <?php echo $coach['program_count']; ?> Program</span>
                    </div>

                    <?php if ($coach['email']): ?>
                        <div style="margin-top: var(--space-lg);">
                            <a href="mailto:<?php echo $coach['email']; ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-envelope"></i> Hubungi
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-user-tie"></i>
            <h3>Belum Ada Coach</h3>
            <p>Data pelatih belum tersedia.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>