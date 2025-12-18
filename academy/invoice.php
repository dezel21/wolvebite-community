<?php
/** Wolvebite Academy - Invoice Generator for Enrollments */
require_once 'includes/functions.php';
requireLogin();

if (!isset($_GET['enrollment_id'])) {
    header('Location: my-enrollments.php');
    exit;
}

$enrollment_id = (int) $_GET['enrollment_id'];
$user_id = $_SESSION['user_id'];

// Get enrollment
$enrollment = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT e.*, p.name as program_name, p.price, p.duration_weeks, p.sessions_per_week, 
            u.username, u.email, c.name as coach_name
     FROM academy_enrollments e 
     JOIN academy_programs p ON e.program_id = p.id 
     JOIN users u ON e.user_id = u.id 
     LEFT JOIN academy_coaches c ON p.coach_id = c.id
     WHERE e.id = $enrollment_id AND e.user_id = $user_id AND e.payment_status = 'paid'"
));

if (!$enrollment) {
    setFlash('error', 'Invoice tidak ditemukan atau belum dibayar.');
    header('Location: my-enrollments.php');
    exit;
}

$invoiceNumber = 'INV-ACD-' . date('Ymd', strtotime($enrollment['enrollment_date'])) . '-' . str_pad($enrollment_id, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo $invoiceNumber; ?> - Wolvebite Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .invoice {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 3px solid #1a1f3c;
            padding-bottom: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            height: 50px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #1a1f3c;
        }

        .logo-text span {
            color: #ff6b35;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            font-size: 28px;
            color: #1a1f3c;
        }

        .invoice-title p {
            color: #666;
        }

        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .info-block h3 {
            color: #1a1f3c;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
        }

        .info-block p {
            color: #333;
            line-height: 1.6;
        }

        .program-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .program-details h3 {
            color: #1a1f3c;
            margin-bottom: 15px;
        }

        .program-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .program-info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .program-info-item:last-child {
            border-bottom: none;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .invoice-table th {
            background: #1a1f3c;
            color: #fff;
            padding: 12px;
            text-align: left;
        }

        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .invoice-table .text-right {
            text-align: right;
        }

        .invoice-total {
            display: flex;
            justify-content: flex-end;
        }

        .total-box {
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .total-row.grand {
            font-size: 18px;
            font-weight: 700;
            color: #1a1f3c;
            border-top: 2px solid #1a1f3c;
            margin-top: 10px;
            padding-top: 15px;
        }

        .invoice-footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #d4edda;
            color: #155724;
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #ff6b35;
            color: #fff;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }

        .print-btn:hover {
            background: #e55a2b;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .invoice {
                box-shadow: none;
            }

            .print-btn {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="invoice">
        <div class="invoice-header">
            <div class="logo">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="Wolvebite"
                    onerror="this.style.display='none'">
                <span class="logo-text">Wolvebite <span>Academy</span></span>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <p><?php echo $invoiceNumber; ?></p>
                <span class="status-badge">LUNAS</span>
            </div>
        </div>

        <div class="invoice-info">
            <div class="info-block">
                <h3>Tagihan Kepada</h3>
                <p>
                    <strong><?php echo sanitize($enrollment['username']); ?></strong><br>
                    <?php echo sanitize($enrollment['email']); ?><br>
                    Telepon: <?php echo sanitize($enrollment['phone']); ?>
                </p>
            </div>
            <div class="info-block" style="text-align: right;">
                <h3>Detail Invoice</h3>
                <p>
                    <strong>Tanggal Daftar:</strong> <?php echo formatDate($enrollment['enrollment_date']); ?><br>
                    <strong>Tanggal Bayar:</strong> <?php echo formatDate($enrollment['payment_date']); ?><br>
                    <strong>Status:</strong> <?php echo getStatusLabel($enrollment['status']); ?>
                </p>
            </div>
        </div>

        <div class="program-details">
            <h3>📚 Detail Program</h3>
            <div class="program-info">
                <div class="program-info-item">
                    <span>Program</span>
                    <strong><?php echo sanitize($enrollment['program_name']); ?></strong>
                </div>
                <div class="program-info-item">
                    <span>Coach</span>
                    <strong><?php echo sanitize($enrollment['coach_name'] ?? '-'); ?></strong>
                </div>
                <div class="program-info-item">
                    <span>Durasi</span>
                    <strong><?php echo $enrollment['duration_weeks']; ?> Minggu</strong>
                </div>
                <div class="program-info-item">
                    <span>Sesi/Minggu</span>
                    <strong><?php echo $enrollment['sessions_per_week']; ?>x</strong>
                </div>
                <div class="program-info-item">
                    <span>Periode</span>
                    <strong><?php echo formatDate($enrollment['start_date'] ?? date('Y-m-d')); ?> -
                        <?php echo formatDate($enrollment['end_date'] ?? date('Y-m-d', strtotime('+' . $enrollment['duration_weeks'] . ' weeks'))); ?></strong>
                </div>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th class="text-right">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Pendaftaran Program <?php echo sanitize($enrollment['program_name']); ?></strong><br>
                        <small style="color: #666;">Durasi <?php echo $enrollment['duration_weeks']; ?> minggu,
                            <?php echo $enrollment['sessions_per_week']; ?>x sesi per minggu</small>
                    </td>
                    <td class="text-right"><?php echo formatRupiah($enrollment['payment_amount']); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="invoice-total">
            <div class="total-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span><?php echo formatRupiah($enrollment['payment_amount']); ?></span>
                </div>
                <div class="total-row">
                    <span>Diskon</span>
                    <span>Rp 0</span>
                </div>
                <div class="total-row grand">
                    <span>Total Dibayar</span>
                    <span><?php echo formatRupiah($enrollment['payment_amount']); ?></span>
                </div>
            </div>
        </div>

        <div class="invoice-footer">
            <p>Terima kasih telah mendaftar di <strong>Wolvebite Academy</strong></p>
            <p>Selamat berlatih dan tingkatkan skill basket Anda! 🏀</p>
            <p style="margin-top: 10px;">Untuk pertanyaan, hubungi: academy@wolvebite.com</p>
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">
        🖨️ Print Invoice
    </button>
</body>

</html>