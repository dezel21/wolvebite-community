<?php
/** Wolvebite Academy - Booking Kelas Page */
$pageTitle = 'Booking Kelas';
require_once 'includes/header.php';
requireLogin();

$program_id = (int) ($_GET['program'] ?? 0);
$errors = [];

// If program specified, check enrollment
if ($program_id > 0) {
    if (!isEnrolled($_SESSION['user_id'], $program_id)) {
        setFlash('error', 'Anda harus terdaftar di program ini untuk booking kelas.');
        header('Location: programs.php');
        exit;
    }
}

// Get user's enrolled programs
$enrolledPrograms = mysqli_query($conn, "SELECT p.* FROM academy_programs p 
                                          JOIN academy_enrollments e ON p.id = e.program_id 
                                          WHERE e.user_id = {$_SESSION['user_id']} AND e.status = 'approved'");

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = (int) $_POST['schedule_id'];
    $booking_date = $_POST['booking_date'];

    // Validate schedule exists
    $scheduleResult = mysqli_query($conn, "SELECT s.*, p.id as program_id FROM academy_schedule s 
                                           JOIN academy_programs p ON s.program_id = p.id 
                                           WHERE s.id = $schedule_id");

    if (mysqli_num_rows($scheduleResult) === 0) {
        $errors[] = 'Jadwal tidak valid.';
    } else {
        $schedule = mysqli_fetch_assoc($scheduleResult);

        // Check enrollment
        if (!isEnrolled($_SESSION['user_id'], $schedule['program_id'])) {
            $errors[] = 'Anda tidak terdaftar di program ini.';
        }

        // Validate date
        if (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
            $errors[] = 'Tanggal tidak boleh di masa lalu.';
        }

        // Check day matches
        $dayOfWeek = strtolower(date('l', strtotime($booking_date)));
        if ($dayOfWeek !== $schedule['day_of_week']) {
            $errors[] = 'Tanggal tidak sesuai dengan hari jadwal.';
        }

        // Check duplicate
        $duplicate = mysqli_query($conn, "SELECT id FROM academy_bookings 
                                          WHERE user_id = {$_SESSION['user_id']} 
                                          AND schedule_id = $schedule_id 
                                          AND booking_date = '$booking_date'");
        if (mysqli_num_rows($duplicate) > 0) {
            $errors[] = 'Anda sudah booking untuk sesi ini.';
        }
    }

    if (empty($errors)) {
        $query = "INSERT INTO academy_bookings (user_id, schedule_id, booking_date) 
                  VALUES ({$_SESSION['user_id']}, $schedule_id, '$booking_date')";

        if (mysqli_query($conn, $query)) {
            setFlash('success', 'Booking berhasil! Menunggu konfirmasi.');
            header('Location: my-bookings.php');
            exit;
        } else {
            $errors[] = 'Gagal melakukan booking.';
        }
    }
}

// Get schedules for enrolled programs
$schedules = mysqli_query($conn, "SELECT s.*, p.name as program_name, c.name as coach_name 
                                  FROM academy_schedule s 
                                  JOIN academy_programs p ON s.program_id = p.id 
                                  JOIN academy_enrollments e ON p.id = e.program_id 
                                  LEFT JOIN academy_coaches c ON s.coach_id = c.id 
                                  WHERE e.user_id = {$_SESSION['user_id']} 
                                  AND e.status = 'approved' 
                                  AND s.status = 'active'
                                  " . ($program_id > 0 ? "AND p.id = $program_id" : "") . "
                                  ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time");
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-calendar-plus"></i> Booking Kelas</h1>
        <p class="section-subtitle">Pilih sesi latihan yang ingin Anda ikuti</p>
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

    <?php if (mysqli_num_rows($enrolledPrograms) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-graduation-cap"></i>
            <h3>Belum Terdaftar</h3>
            <p>Anda harus terdaftar dan diapprove di sebuah program untuk booking kelas.</p>
            <a href="programs.php" class="btn btn-primary">Lihat Program</a>
        </div>
    <?php elseif (mysqli_num_rows($schedules) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>Tidak Ada Jadwal</h3>
            <p>Belum ada jadwal tersedia untuk program Anda.</p>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <form method="POST" id="bookingForm">
                    <div class="form-group">
                        <label class="form-label">Pilih Sesi Latihan</label>
                        <select name="schedule_id" id="scheduleSelect" class="form-control" required>
                            <option value="">-- Pilih Jadwal --</option>
                            <?php while ($schedule = mysqli_fetch_assoc($schedules)): ?>
                                <option value="<?php echo $schedule['id']; ?>"
                                    data-day="<?php echo $schedule['day_of_week']; ?>">
                                    <?php echo formatDay($schedule['day_of_week']); ?>
                                    (<?php echo formatTime($schedule['start_time']); ?> -
                                    <?php echo formatTime($schedule['end_time']); ?>)
                                    - <?php echo $schedule['program_name']; ?>
                                    @ <?php echo $schedule['location']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Booking</label>
                        <input type="date" name="booking_date" class="form-control" min="<?php echo date('Y-m-d'); ?>"
                            required>
                        <span class="form-hint">Pilih tanggal yang sesuai dengan hari jadwal</span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-check"></i> Konfirmasi Booking
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>