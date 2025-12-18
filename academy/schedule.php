<?php
/** Wolvebite Academy - Schedule Page */
$pageTitle = 'Jadwal Latihan';
require_once 'includes/header.php';

$schedules = getAllSchedules();

// Group by day
$scheduleByDay = [];
while ($schedule = mysqli_fetch_assoc($schedules)) {
    $day = $schedule['day_of_week'];
    if (!isset($scheduleByDay[$day])) {
        $scheduleByDay[$day] = [];
    }
    $scheduleByDay[$day][] = $schedule;
}

$dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-calendar-alt"></i> Jadwal Latihan</h1>
        <p class="section-subtitle">Jadwal mingguan semua program latihan</p>
    </div>

    <?php if (!empty($scheduleByDay)): ?>
        <div class="card">
            <div class="card-body" style="overflow-x: auto;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Hari</th>
                            <th>Waktu</th>
                            <th>Program</th>
                            <th>Coach</th>
                            <th>Lokasi</th>
                            <th>Kapasitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dayOrder as $day): ?>
                            <?php if (isset($scheduleByDay[$day])): ?>
                                <?php $first = true; ?>
                                <?php foreach ($scheduleByDay[$day] as $schedule): ?>
                                    <tr>
                                        <?php if ($first): ?>
                                            <td class="schedule-day" rowspan="<?php echo count($scheduleByDay[$day]); ?>"
                                                style="vertical-align: middle; border-right: 2px solid var(--accent-color);">
                                                <?php echo formatDay($day); ?>
                                            </td>
                                            <?php $first = false; ?>
                                        <?php endif; ?>
                                        <td>
                                            <strong><?php echo formatTime($schedule['start_time']); ?></strong>
                                            - <?php echo formatTime($schedule['end_time']); ?>
                                        </td>
                                        <td>
                                            <a href="program-detail.php?slug=<?php echo $schedule['program_id']; ?>"
                                                style="color: var(--accent-color); text-decoration: none;">
                                                <?php echo sanitize($schedule['program_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo sanitize($schedule['coach_name'] ?? 'TBA'); ?></td>
                                        <td><?php echo sanitize($schedule['location']); ?></td>
                                        <td><?php echo $schedule['max_capacity']; ?> orang</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: var(--space-xl); text-align: center;">
            <p style="color: var(--text-light); margin-bottom: var(--space-md);">
                <i class="fas fa-info-circle"></i> Jadwal dapat berubah sewaktu-waktu. Untuk booking kelas, silakan daftar
                program terlebih dahulu.
            </p>
            <a href="programs.php" class="btn btn-primary">
                <i class="fas fa-list"></i> Lihat Program
            </a>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>Tidak Ada Jadwal</h3>
            <p>Jadwal latihan belum tersedia.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>