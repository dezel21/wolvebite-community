<?php
// Wolvebite Academy - Admin Coaches
$pageTitle = 'Kelola Coach';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "UPDATE academy_coaches SET status = 'inactive' WHERE id = $id");
    setFlash('success', 'Coach berhasil dinonaktifkan.');
    header('Location: coaches.php');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = escapeSQL($conn, $_POST['name']);
    $email = escapeSQL($conn, $_POST['email']);
    $phone = escapeSQL($conn, $_POST['phone']);
    $specialization = escapeSQL($conn, $_POST['specialization']);
    $bio = escapeSQL($conn, $_POST['bio']);
    $experience_years = (int) $_POST['experience_years'];
    $status = escapeSQL($conn, $_POST['status']);

    if ($id > 0) {
        $query = "UPDATE academy_coaches SET name = '$name', email = '$email', phone = '$phone', 
                  specialization = '$specialization', bio = '$bio', experience_years = $experience_years, status = '$status' 
                  WHERE id = $id";
    } else {
        $query = "INSERT INTO academy_coaches (name, email, phone, specialization, bio, experience_years, status) 
                  VALUES ('$name', '$email', '$phone', '$specialization', '$bio', $experience_years, '$status')";
    }

    if (mysqli_query($conn, $query)) {
        setFlash('success', $id > 0 ? 'Coach berhasil diupdate.' : 'Coach berhasil ditambahkan.');
    } else {
        setFlash('error', 'Gagal menyimpan data coach.');
    }
    header('Location: coaches.php');
    exit;
}

$coaches = mysqli_query($conn, "SELECT c.*, (SELECT COUNT(*) FROM academy_programs p WHERE p.coach_id = c.id) as programs 
                                FROM academy_coaches c ORDER BY c.status DESC, c.name");

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
                <a href="coaches.php" class="admin-nav-link active"><i class="fas fa-user-tie"></i> Coaches</a>
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
                <h1>Kelola Coach</h1>
                <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Tambah Coach</button>
            </div>

            <?php displayFlash(); ?>

            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Spesialisasi</th>
                                <th>Pengalaman</th>
                                <th>Program</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($coach = mysqli_fetch_assoc($coaches)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo sanitize($coach['name']); ?></strong>
                                        <p style="font-size: var(--font-size-sm); color: var(--text-light); margin: 0;">
                                            <?php echo sanitize($coach['email']); ?></p>
                                    </td>
                                    <td><?php echo sanitize($coach['specialization']); ?></td>
                                    <td><?php echo $coach['experience_years']; ?> tahun</td>
                                    <td><?php echo $coach['programs']; ?> program</td>
                                    <td><span
                                            class="badge <?php echo getStatusBadge($coach['status']); ?>"><?php echo $coach['status']; ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary"
                                            onclick="editCoach(<?php echo htmlspecialchars(json_encode($coach)); ?>)"><i
                                                class="fas fa-edit"></i></button>
                                        <a href="coaches.php?delete=<?php echo $coach['id']; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Nonaktifkan coach ini?')"><i
                                                class="fas fa-ban"></i></a>
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
    <div class="modal-overlay" id="coachModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Coach</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="id" id="coachId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" id="coachName" class="form-control" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="coachEmail" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="phone" id="coachPhone" class="form-control">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-md);">
                        <div class="form-group">
                            <label class="form-label">Spesialisasi</label>
                            <input type="text" name="specialization" id="coachSpec" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pengalaman (tahun)</label>
                            <input type="number" name="experience_years" id="coachExp" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" id="coachBio" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="coachStatus" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
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
            document.getElementById('coachModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Tambah Coach';
            document.getElementById('coachId').value = '';
            document.querySelector('#coachModal form').reset();
        }
        function closeModal() {
            document.getElementById('coachModal').classList.remove('active');
        }
        function editCoach(coach) {
            document.getElementById('coachModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Edit Coach';
            document.getElementById('coachId').value = coach.id;
            document.getElementById('coachName').value = coach.name;
            document.getElementById('coachEmail').value = coach.email || '';
            document.getElementById('coachPhone').value = coach.phone || '';
            document.getElementById('coachSpec').value = coach.specialization || '';
            document.getElementById('coachExp').value = coach.experience_years;
            document.getElementById('coachBio').value = coach.bio || '';
            document.getElementById('coachStatus').value = coach.status;
        }
    </script>
</body>

</html>