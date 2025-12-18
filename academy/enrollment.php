<?php
/** Wolvebite Academy - Enrollment Page */
$pageTitle = 'Pendaftaran Program';
require_once 'includes/header.php';
requireLogin();

$program_id = (int) ($_GET['program'] ?? 0);
if ($program_id <= 0) {
    setFlash('error', 'Program tidak valid.');
    header('Location: programs.php');
    exit;
}

$program = getProgram($program_id);
if (!$program || $program['status'] !== 'active') {
    setFlash('error', 'Program tidak tersedia.');
    header('Location: programs.php');
    exit;
}

// Check if already enrolled
if (isEnrolled($_SESSION['user_id'], $program_id)) {
    setFlash('info', 'Anda sudah terdaftar di program ini.');
    header('Location: my-enrollments.php');
    exit;
}

// Check available spots
$enrolledCount = getEnrollmentCount($program_id);
if ($enrolledCount >= $program['max_participants']) {
    setFlash('error', 'Program ini sudah penuh.');
    header('Location: program-detail.php?slug=' . $program['slug']);
    exit;
}

$user = getCurrentUser();
$errors = [];

// Process enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = (int) $_POST['age'];
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $agree = isset($_POST['agree']);

    // Validation
    if ($age < $program['age_min'] || $age > $program['age_max']) {
        $errors[] = "Program ini untuk usia {$program['age_min']}-{$program['age_max']} tahun.";
    }

    if (empty($phone)) {
        $errors[] = 'Nomor telepon wajib diisi.';
    }

    if (!$agree) {
        $errors[] = 'Anda harus menyetujui syarat dan ketentuan.';
    }

    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        $enrollment_date = date('Y-m-d');
        $payment_amount = $program['price'];
        $notes_escaped = escapeSQL($conn, $notes);

        // Update user phone if not set
        if (empty($user['phone'])) {
            $phone_escaped = escapeSQL($conn, $phone);
            mysqli_query($conn, "UPDATE users SET phone = '$phone_escaped' WHERE id = $user_id");
        }

        // Create enrollment
        $query = "INSERT INTO academy_enrollments (user_id, program_id, enrollment_date, payment_amount, notes) 
                  VALUES ($user_id, $program_id, '$enrollment_date', $payment_amount, '$notes_escaped')";

        if (mysqli_query($conn, $query)) {
            $enrollment_id = mysqli_insert_id($conn);
            setFlash('success', 'Pendaftaran berhasil! Silakan lakukan pembayaran.');
            header('Location: enrollment-payment.php?id=' . $enrollment_id);
            exit;
        } else {
            $errors[] = 'Gagal memproses pendaftaran. Silakan coba lagi.';
        }
    }
}
?>

<div class="container">
    <div style="margin-bottom: var(--space-lg);">
        <a href="program-detail.php?slug=<?php echo $program['slug']; ?>" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail Program
        </a>
    </div>

    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-user-plus"></i> Pendaftaran Program</h1>
        <p class="section-subtitle">Daftar untuk <?php echo sanitize($program['name']); ?></p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php foreach ($errors as $error): ?>
                    <div><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="enrollment-form">
        <!-- Program Summary -->
        <div class="enrollment-summary">
            <h3><i class="fas fa-basketball-ball"></i> <?php echo sanitize($program['name']); ?></h3>
            <div class="enrollment-detail">
                <span>Level</span>
                <span class="value"><?php echo formatLevel($program['level']); ?></span>
            </div>
            <div class="enrollment-detail">
                <span>Durasi</span>
                <span class="value"><?php echo $program['duration_weeks']; ?> Minggu</span>
            </div>
            <div class="enrollment-detail">
                <span>Sesi per Minggu</span>
                <span class="value"><?php echo $program['sessions_per_week']; ?>x</span>
            </div>
            <div class="enrollment-detail">
                <span>Coach</span>
                <span class="value"><?php echo sanitize($program['coach_name'] ?? 'TBA'); ?></span>
            </div>
            <div class="enrollment-detail">
                <span>Total Biaya</span>
                <span class="value"><?php echo formatRupiah($program['price']); ?></span>
            </div>
        </div>

        <!-- Enrollment Form -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: var(--space-xl);">Data Pendaftaran</h3>

                <form method="POST" id="enrollmentForm">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" value="<?php echo sanitize($user['username']); ?>"
                            readonly disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo sanitize($user['email']); ?>"
                            readonly disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Nomor Telepon *</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                            value="<?php echo sanitize($user['phone'] ?? ''); ?>" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="age">Usia Peserta *</label>
                        <input type="number" id="age" name="age" class="form-control"
                            min="<?php echo $program['age_min']; ?>" max="<?php echo $program['age_max']; ?>"
                            placeholder="Masukkan usia" required>
                        <span class="form-hint">Usia yang diizinkan:
                            <?php echo $program['age_min']; ?>-<?php echo $program['age_max']; ?> tahun</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="notes">Catatan (opsional)</label>
                        <textarea id="notes" name="notes" class="form-control"
                            placeholder="Informasi tambahan, riwayat cedera, dll."></textarea>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: flex-start; gap: var(--space-sm); cursor: pointer;">
                            <input type="checkbox" name="agree" required style="margin-top: 3px;">
                            <span>Saya menyetujui <a href="#">syarat dan ketentuan</a> program pelatihan Wolvebite
                                Academy</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-check"></i> Daftar & Lanjut ke Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>