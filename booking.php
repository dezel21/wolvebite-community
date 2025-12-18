<?php
/**
 * Wolvebite Community - Booking Page
 */
$pageTitle = 'Booking Lapangan';
require_once 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Court options
$courts = [
    'Court A' => 'Lapangan Indoor - AC, Lantai Vinyl',
    'Court B' => 'Lapangan Indoor - Standar',
    'Court C' => 'Lapangan Outdoor - Full Court'
];

// Time slots
$timeSlots = [];
for ($h = 7; $h <= 21; $h++) {
    $timeSlots[] = sprintf('%02d:00', $h);
}

// Process booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $court_name = $_POST['court_name'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    // Validation
    if (empty($booking_date)) {
        $errors[] = 'Tanggal booking wajib diisi.';
    } elseif (strtotime($booking_date) < strtotime('today')) {
        $errors[] = 'Tanggal booking tidak boleh di masa lalu.';
    }

    if (empty($start_time)) {
        $errors[] = 'Waktu mulai wajib dipilih.';
    }

    if (empty($end_time)) {
        $errors[] = 'Waktu selesai wajib dipilih.';
    } elseif ($end_time <= $start_time) {
        $errors[] = 'Waktu selesai harus lebih dari waktu mulai.';
    }

    if (empty($court_name) || !array_key_exists($court_name, $courts)) {
        $errors[] = 'Lapangan wajib dipilih.';
    }

    // Check for booking conflicts
    if (empty($errors)) {
        $booking_date_escaped = escapeSQL($conn, $booking_date);
        $court_name_escaped = escapeSQL($conn, $court_name);

        $conflictQuery = "SELECT id FROM bookings 
                          WHERE booking_date = '$booking_date_escaped' 
                          AND court_name = '$court_name_escaped'
                          AND status IN ('pending', 'approved')
                          AND (
                              (start_time < '$end_time' AND end_time > '$start_time')
                          )";

        $conflicts = mysqli_query($conn, $conflictQuery);

        if (mysqli_num_rows($conflicts) > 0) {
            $errors[] = 'Jadwal ini sudah terisi. Silakan pilih waktu atau lapangan lain.';
        }
    }

    // Create booking
    if (empty($errors)) {
        $notes_escaped = escapeSQL($conn, $notes);

        $insertQuery = "INSERT INTO bookings (user_id, booking_date, start_time, end_time, court_name, notes, status) 
                        VALUES ($user_id, '$booking_date_escaped', '$start_time', '$end_time', '$court_name_escaped', '$notes_escaped', 'pending')";

        if (mysqli_query($conn, $insertQuery)) {
            setFlash('success', 'Booking berhasil dibuat! Menunggu konfirmasi admin.');
            header('Location: my-bookings.php');
            exit;
        } else {
            $errors[] = 'Gagal membuat booking. Silakan coba lagi.';
        }
    }
}

// Get today's booked slots for display
$today = date('Y-m-d');
$bookedSlots = [];

$bookedQuery = mysqli_query($conn, "SELECT court_name, start_time, end_time, booking_date FROM bookings WHERE booking_date >= '$today' AND status IN ('pending', 'approved')");
while ($slot = mysqli_fetch_assoc($bookedQuery)) {
    $key = $slot['booking_date'] . '-' . $slot['court_name'];
    if (!isset($bookedSlots[$key])) {
        $bookedSlots[$key] = [];
    }
    $bookedSlots[$key][] = $slot['start_time'] . ' - ' . $slot['end_time'];
}
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-calendar-check"></i> Booking Lapangan</h1>
        <p class="section-subtitle">Reservasi lapangan basket untuk latihan atau pertandingan</p>
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

    <div class="booking-container">
        <!-- Booking Form -->
        <div class="booking-form-card">
            <h3 style="margin-bottom: var(--space-xl);">Form Booking</h3>

            <form id="bookingForm" method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="booking_date">Tanggal Booking *</label>
                    <input type="date" id="booking_date" name="booking_date" class="form-control"
                        min="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo isset($_POST['booking_date']) ? sanitize($_POST['booking_date']) : ''; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="court_name">Pilih Lapangan *</label>
                    <select id="court_name" name="court_name" class="form-control" required>
                        <option value="">-- Pilih Lapangan --</option>
                        <?php foreach ($courts as $name => $desc): ?>
                            <option value="<?php echo $name; ?>" <?php echo (isset($_POST['court_name']) && $_POST['court_name'] === $name) ? 'selected' : ''; ?>>
                                <?php echo $name; ?> - <?php echo $desc; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                    <div class="form-group">
                        <label class="form-label" for="start_time">Waktu Mulai *</label>
                        <select id="start_time" name="start_time" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($timeSlots as $time): ?>
                                <option value="<?php echo $time; ?>:00" <?php echo (isset($_POST['start_time']) && $_POST['start_time'] === "$time:00") ? 'selected' : ''; ?>>
                                    <?php echo $time; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="end_time">Waktu Selesai *</label>
                        <select id="end_time" name="end_time" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($timeSlots as $time): ?>
                                <?php if ($time !== '07'): ?>
                                    <option value="<?php echo $time; ?>:00" <?php echo (isset($_POST['end_time']) && $_POST['end_time'] === "$time:00") ? 'selected' : ''; ?>>
                                        <?php echo $time; ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <option value="22:00:00">22:00</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Catatan (opsional)</label>
                    <textarea id="notes" name="notes" class="form-control"
                        placeholder="Misal: Latihan tim, pertandingan friendly, dll."><?php echo isset($_POST['notes']) ? sanitize($_POST['notes']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-calendar-plus"></i> Buat Booking
                </button>
            </form>
        </div>

        <!-- Booking Info -->
        <div class="booking-info-card">
            <h3 style="margin-bottom: var(--space-lg);">Informasi Lapangan</h3>

            <div class="court-list">
                <?php foreach ($courts as $name => $desc): ?>
                    <div class="court-item">
                        <i class="fas fa-basketball-ball"></i>
                        <div>
                            <strong><?php echo $name; ?></strong>
                            <p style="font-size: var(--font-size-sm); opacity: 0.8; margin: 0;"><?php echo $desc; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr style="border-color: rgba(255,255,255,0.2); margin: var(--space-xl) 0;">

            <h4 style="margin-bottom: var(--space-md);">Jam Operasional</h4>
            <p style="opacity: 0.9;"><i class="fas fa-clock"></i> 07:00 - 22:00 WIB</p>

            <h4 style="margin: var(--space-lg) 0 var(--space-md);">Ketentuan</h4>
            <ul style="opacity: 0.9; padding-left: var(--space-lg);">
                <li>Booking minimal 1 jam</li>
                <li>Booking maksimal 3 jam per sesi</li>
                <li>Pembatalan H-1 tidak dikenakan biaya</li>
                <li>Harap datang 10 menit sebelum jadwal</li>
            </ul>

            <div style="margin-top: var(--space-xl);">
                <a href="my-bookings.php" class="btn btn-outline" style="border-color: #fff; color: #fff;">
                    <i class="fas fa-list"></i> Lihat Booking Saya
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>