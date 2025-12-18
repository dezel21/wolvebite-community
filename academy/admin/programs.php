<?php
// Wolvebite Academy - Admin Programs
$pageTitle = 'Kelola Program';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM academy_programs WHERE id = $id");
    setFlash('success', 'Program berhasil dihapus.');
    header('Location: programs.php');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = escapeSQL($conn, $_POST['name']);
    $slug = escapeSQL($conn, strtolower(str_replace(' ', '-', $_POST['name'])));
    $description = escapeSQL($conn, $_POST['description']);
    $level = escapeSQL($conn, $_POST['level']);
    $age_min = (int) $_POST['age_min'];
    $age_max = (int) $_POST['age_max'];
    $duration_weeks = (int) $_POST['duration_weeks'];
    $sessions_per_week = (int) $_POST['sessions_per_week'];
    $price = (float) $_POST['price'];
    $max_participants = (int) $_POST['max_participants'];
    $coach_id = (int) $_POST['coach_id'] ?: 'NULL';
    $status = escapeSQL($conn, $_POST['status']);

    if ($id > 0) {
        $query = "UPDATE academy_programs SET 
                  name = '$name', slug = '$slug', description = '$description', level = '$level',
                  age_min = $age_min, age_max = $age_max, duration_weeks = $duration_weeks,
                  sessions_per_week = $sessions_per_week, price = $price, max_participants = $max_participants,
                  coach_id = $coach_id, status = '$status'
                  WHERE id = $id";
    } else {
        $query = "INSERT INTO academy_programs (name, slug, description, level, age_min, age_max, duration_weeks, sessions_per_week, price, max_participants, coach_id, status) 
                  VALUES ('$name', '$slug', '$description', '$level', $age_min, $age_max, $duration_weeks, $sessions_per_week, $price, $max_participants, $coach_id, '$status')";
    }

    if (mysqli_query($conn, $query)) {
        setFlash('success', $id > 0 ? 'Program berhasil diupdate.' : 'Program berhasil ditambahkan.');
    } else {
        setFlash('error', 'Gagal menyimpan program.');
    }
    header('Location: programs.php');
    exit;
}

$programs = mysqli_query($conn, "SELECT p.*, c.name as coach_name,
                                  (SELECT COUNT(*) FROM academy_enrollments e WHERE e.program_id = p.id AND e.status IN ('approved', 'pending')) as enrolled
                                  FROM academy_programs p 
                                  LEFT JOIN academy_coaches c ON p.coach_id = c.id 
                                  ORDER BY p.name");

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
                <a href="programs.php" class="admin-nav-link active"><i class="fas fa-basketball-ball"></i> Programs</a>
                <a href="coaches.php" class="admin-nav-link"><i class="fas fa-user-tie"></i> Coaches</a>
                <a href="schedule.php" class="admin-nav-link"><i class="fas fa-calendar-alt"></i> Schedule</a>
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
                <h1>Kelola Program</h1>
                <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Tambah
                    Program</button>
            </div>

            <?php displayFlash(); ?>

            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Level</th>
                                <th>Usia</th>
                                <th>Harga</th>
                                <th>Coach</th>
                                <th>Enrolled</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($program = mysqli_fetch_assoc($programs)): ?>
                                <tr>
                                    <td><strong><?php echo sanitize($program['name']); ?></strong></td>
                                    <td><span
                                            class="badge <?php echo getLevelBadge($program['level']); ?>"><?php echo formatLevel($program['level']); ?></span>
                                    </td>
                                    <td><?php echo $program['age_min']; ?>-<?php echo $program['age_max']; ?> thn</td>
                                    <td><?php echo formatRupiah($program['price']); ?></td>
                                    <td><?php echo sanitize($program['coach_name'] ?? '-'); ?></td>
                                    <td><?php echo $program['enrolled']; ?>/<?php echo $program['max_participants']; ?></td>
                                    <td><span
                                            class="badge <?php echo getStatusBadge($program['status']); ?>"><?php echo $program['status']; ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary"
                                            onclick="editProgram(<?php echo htmlspecialchars(json_encode($program)); ?>)"><i
                                                class="fas fa-edit"></i></button>
                                        <a href="programs.php?delete=<?php echo $program['id']; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin hapus program ini?')"><i
                                                class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="programModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Program</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="id" id="programId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nama Program</label>
                        <input type="text" name="name" id="programName" class="form-control" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                        <div class="form-group">
                            <label class="form-label">Level</label>
                            <select name="level" id="programLevel" class="form-control">
                                <option value="beginner">Pemula</option>
                                <option value="intermediate">Menengah</option>
                                <option value="advanced">Lanjutan</option>
                                <option value="elite">Elite</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Coach</label>
                            <select name="coach_id" id="programCoach" class="form-control">
                                <option value="">Pilih Coach</option>
                                <?php mysqli_data_seek($coaches, 0);
                                while ($coach = mysqli_fetch_assoc($coaches)): ?>
                                    <option value="<?php echo $coach['id']; ?>"><?php echo sanitize($coach['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                        <div class="form-group">
                            <label class="form-label">Usia Min</label>
                            <input type="number" name="age_min" id="programAgeMin" class="form-control" value="8">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Usia Max</label>
                            <input type="number" name="age_max" id="programAgeMax" class="form-control" value="18">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--space-md);">
                        <div class="form-group">
                            <label class="form-label">Durasi (minggu)</label>
                            <input type="number" name="duration_weeks" id="programDuration" class="form-control"
                                value="12">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sesi/Minggu</label>
                            <input type="number" name="sessions_per_week" id="programSessions" class="form-control"
                                value="2">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max Peserta</label>
                            <input type="number" name="max_participants" id="programMax" class="form-control"
                                value="20">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga (Rp)</label>
                        <input type="number" name="price" id="programPrice" class="form-control" value="1500000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="programDesc" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="programStatus" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="full">Full</option>
                        </select>
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
            document.getElementById('programModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Tambah Program';
            document.getElementById('programId').value = '';
            document.querySelector('#programModal form').reset();
        }

        function closeModal() {
            document.getElementById('programModal').classList.remove('active');
        }

        function editProgram(program) {
            document.getElementById('programModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Edit Program';
            document.getElementById('programId').value = program.id;
            document.getElementById('programName').value = program.name;
            document.getElementById('programLevel').value = program.level;
            document.getElementById('programCoach').value = program.coach_id || '';
            document.getElementById('programAgeMin').value = program.age_min;
            document.getElementById('programAgeMax').value = program.age_max;
            document.getElementById('programDuration').value = program.duration_weeks;
            document.getElementById('programSessions').value = program.sessions_per_week;
            document.getElementById('programMax').value = program.max_participants;
            document.getElementById('programPrice').value = program.price;
            document.getElementById('programDesc').value = program.description;
            document.getElementById('programStatus').value = program.status;
        }
    </script>
</body>

</html>