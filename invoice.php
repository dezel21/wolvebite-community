<?php
/**
 * Wolvebite Community - Invoice Generator for Shop Orders
 */
require_once 'includes/functions.php';
requireLogin();

if (!isset($_GET['order_id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = (int) $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Get order
$order = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT o.*, u.username, u.email 
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     WHERE o.id = $order_id AND o.user_id = $user_id"
));

if (!$order) {
    setFlash('error', 'Order tidak ditemukan.');
    header('Location: orders.php');
    exit;
}

// Get order items
$items = mysqli_query(
    $conn,
    "SELECT oi.*, p.name, p.image 
     FROM order_items oi 
     JOIN products p ON oi.product_id = p.id 
     WHERE oi.order_id = $order_id"
);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order['order_number']; ?> - Wolvebite</title>
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
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
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
                <img src="assets/images/logo.png" alt="Wolvebite" onerror="this.style.display='none'">
                <span class="logo-text">Wolvebite</span>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <p>#<?php echo $order['order_number']; ?></p>
                <span
                    class="status-badge <?php echo $order['payment_status'] === 'paid' ? 'status-paid' : 'status-pending'; ?>">
                    <?php echo strtoupper($order['payment_status']); ?>
                </span>
            </div>
        </div>

        <div class="invoice-info">
            <div class="info-block">
                <h3>Tagihan Kepada</h3>
                <p>
                    <strong><?php echo sanitize($order['username']); ?></strong><br>
                    <?php echo sanitize($order['email']); ?><br>
                    <?php echo nl2br(sanitize($order['shipping_address'])); ?>
                </p>
            </div>
            <div class="info-block" style="text-align: right;">
                <h3>Detail Invoice</h3>
                <p>
                    <strong>Tanggal:</strong> <?php echo formatDate($order['created_at']); ?><br>
                    <strong>Status:</strong> <?php echo getStatusLabel($order['status']); ?><br>
                    <strong>Pembayaran:</strong> <?php echo ucfirst($order['payment_method'] ?? 'Transfer'); ?>
                </p>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($items)): ?>
                    <tr>
                        <td><?php echo sanitize($item['name']); ?></td>
                        <td class="text-right"><?php echo formatRupiah($item['price']); ?></td>
                        <td class="text-right"><?php echo $item['quantity']; ?></td>
                        <td class="text-right"><?php echo formatRupiah($item['price'] * $item['quantity']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="invoice-total">
            <div class="total-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span><?php echo formatRupiah($order['total_amount']); ?></span>
                </div>
                <div class="total-row">
                    <span>Ongkos Kirim</span>
                    <span>Rp 0</span>
                </div>
                <div class="total-row grand">
                    <span>Total</span>
                    <span><?php echo formatRupiah($order['total_amount']); ?></span>
                </div>
            </div>
        </div>

        <div class="invoice-footer">
            <p>Terima kasih telah berbelanja di <strong>Wolvebite Community</strong></p>
            <p>Untuk pertanyaan, hubungi: support@wolvebite.com</p>
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">
        🖨️ Print Invoice
    </button>
</body>

</html>