<?php
// Wolvebite Academy - Admin Schedule
$pageTitle = 'Kelola Jadwal';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM academy_schedule WHERE id = $id");
    setFlash('success', 'Jadwal berhasil dihapus.');
    header('Location: schedule.php');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $program_id = (int) $_POST['program_id'];
    $coach_id = (int) $_POST['coach_id'] ?: 'NULL';
    $day_of_week = escapeSQL($conn, $_POST['day_of_week']);
    $start_time = escapeSQL($conn, $_POST['start_time']);
    $end_time = escapeSQL($conn, $_POST['end_time']);
    $location = escapeSQL($conn, $_POST['location']);
    $max_capacity = (int) $_POST['max_capacity'];

    if ($id > 0) {
        $query = "UPDATE academy_schedule SET program_id = $program_id, coach_id = $coach_id, 
                  day_of_week = '$day_of_week', start_time = '$start_time', end_time = '$end_time',
                  location = '$location', max_capacity = $max_capacity WHERE id = $id";
    } else {
        $query = "INSERT INTO academy_schedule (program_id, coach_id, day_of_week, start_time, end_time, location, max_capacity) 
                  VALUES ($program_id, $coach_id, '$day_of_week', '$start_time', '$end_time', '$location', $max_capacity)";
    }

    if (mysqli_query($conn, $query)) {
        setFlash('success', $id > 0 ? 'Jadwal berhasil diupdate.' : 'Jadwal berhasil ditambahkan.');
    }
    header('Location: schedule.php');
    exit;
}

$schedules = mysqli_query($conn, "SELECT s.*, p.name as program_name, c.name as coach_name 
                                  FROM academy_schedule s 
                                  JOIN academy_programs p ON s.program_id = p.id 
                                  LEFT JOIN academy_coaches c ON s.coach_id = c.id 
                                  ORDER BY FIELD(s.day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday'), s.start_time");

$programs = mysqli_query($conn, "SELECT id, name FROM academy_programs WHERE status = 'active'");
$coaches = mysqli_query($conn, "SELECT id, name FROM academy_coaches WHERE status = 'active'");

$pendingEnrollments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_enrollments WHERE status = 'pending'"))['count'];
$pendingBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_bookings WHERE status = 'pending'"))['count'];
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
                <a href="schedule.php" class="admin-nav-link active"><i class="fas fa-calendar-alt"></i> Schedule</a>
                <a href="enrollments.php" class="admin-nav-link"><i class="fas fa-user-graduate"></i> Enrollments
                    <?php if ($pendingEnrollments > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingEnrollments; ?></span><?php endif; ?></a>
                <a href="bookings.php" class="admin-nav-link"><i class="fas fa-calendar-check"></i> Bookings
                    <?php if ($pendingBookings > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingBookings; ?></span><?php endif; ?></a>
                <a href="modules.php" class="admin-nav-link"><i class="fas fa-book"></i> Modules</a>
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
                <h1>Kelola Jadwal</h1>
                <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Tambah Jadwal</button>
            </div>

            <?php displayFlash(); ?>

            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Waktu</th>
                                <th>Program</th>
                                <th>Coach</th>
                                <th>Lokasi</th>
                                <th>Kapasitas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($s = mysqli_fetch_assoc($schedules)): ?>
                                <tr>
                                    <td class="schedule-day"><?php echo formatDay($s['day_of_week']); ?></td>
                                    <td><?php echo formatTime($s['start_time']); ?> -
                                        <?php echo formatTime($s['end_time']); ?></td>
                                    <td><?php echo sanitize($s['program_name']); ?></td>
                                    <td><?php echo sanitize($s['coach_name'] ?? '-'); ?></td>
                                    <td><?php echo sanitize($s['location']); ?></td>
                                    <td><?php echo $s['max_capacity']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary"
                                            onclick="editSchedule(<?php echo htmlspecialchars(json_encode($s)); ?>)"><i
                                                class="fas fa-edit"></i></button>
                                        <a href="schedule.php?delete=<?php echo $s['id']; ?>" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Hapus jadwal ini?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="scheduleModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Jadwal</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="id" id="scheduleId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Program</label>
                        <select name="program_id" id="scheduleProgram" class="form-control" required>
                            <?php mysqli_data_seek($programs, 0);
                            while ($p = mysqli_fetch_assoc($programs)): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo sanitize($p['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                        <div class="form-group">
                            <label class="form-label">Hari</label>
                            <select name="day_of_week" id="scheduleDay" class="form-control">
                                <option value="monday">Senin</option>
                                <option value="tuesday">Selasa</option>
                                <option value="wednesday">Rabu</option>
                                <option value="thursday">Kamis</option>
                                <option value="friday">Jumat</option>
                                <option value="saturday">Sabtu</option>
                                <option value="sunday">Minggu</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Coach</label>
                            <select name="coach_id" id="scheduleCoach" class="form-control">
                                <option value="">Pilih Coach</option>
                                <?php mysqli_data_seek($coaches, 0);
                                while ($c = mysqli_fetch_assoc($coaches)): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo sanitize($c['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                        <div class="form-group">
                            <label class="form-label">Jam Mulai</label>
                            <input type="time" name="start_time" id="scheduleStart" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jam Selesai</label>
                            <input type="time" name="end_time" id="scheduleEnd" class="form-control" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-md);">
                        <div class="form-group">
                            <label class="form-label">Lokasi</label>
                            <input type="text" name="location" id="scheduleLocation" class="form-control"
                                value="Court A">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kapasitas</label>
                            <input type="number" name="max_capacity" id="scheduleCap" class="form-control" value="20">
                        </div>
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
        function openModal() {
            document.getElementById('scheduleModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Tambah Jadwal';
            document.getElementById('scheduleId').value = '';
            document.querySelector('#scheduleModal form').reset();
        }
        function closeModal() {
            document.getElementById('scheduleModal').classList.remove('active');
        }
        function editSchedule(s) {
            document.getElementById('scheduleModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Edit Jadwal';
            document.getElementById('scheduleId').value = s.id;
            document.getElementById('scheduleProgram').value = s.program_id;
            document.getElementById('scheduleDay').value = s.day_of_week;
            document.getElementById('scheduleCoach').value = s.coach_id || '';
            document.getElementById('scheduleStart').value = s.start_time;
            document.getElementById('scheduleEnd').value = s.end_time;
            document.getElementById('scheduleLocation').value = s.location;
            document.getElementById('scheduleCap').value = s.max_capacity;
        }
    </script>
</body>

</html>