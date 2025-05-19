<?php
require_once '../../includes/session_handler.php';
require_once '../../db.php';

// Start client session and check access
startClientSession();
requireClient();

$user_id = $_SESSION['user_id'];

try {
    // Fetch user details
    $stmt = $pdo->prepare("SELECT firstname, lastname, username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Updated query to fetch all required information
    $stmt = $pdo->prepare("
        SELECT 
            b.id as booking_id,
            b.booking_date,
            b.return_date,
            c.model as car_model,
            c.plate_number,
            c.type as car_type,
            c.price as price_per_day,
            (DATEDIFF(b.return_date, b.booking_date)) as number_of_days,
            (DATEDIFF(b.return_date, b.booking_date) * c.price) as total_amount
        FROM bookings b
        JOIN car c ON b.car_id = c.id
        WHERE b.users_id = ?
        ORDER BY b.booking_date DESC
    ");
    $stmt->execute([$user_id]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Invoices - QuickLease</title>
    <link rel="stylesheet" href="../../css/client.css">
    <link rel="stylesheet" href="../../css/client-account.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .invoice-box {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .invoice-header {
            font-size: 18px;
            font-weight: bold;
            color: #2200c0;
            margin-bottom: 15px;
        }
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .detail-line {
            font-size: 16px;
            color: #333;
            padding: 5px 0;
        }
        .detail-line strong {
            color: #555;
        }
        .total-amount {
            font-weight: bold;
            color: #2200c0;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        .print-button {
            background-color: #2200c0;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            margin-top: 15px;
        }
        .print-button:hover {
            background-color: #1a008c;
        }
    </style>
</head>
<body class="client-body">
    <div class="container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="content">
            <header>
                <?php include __DIR__ . '/includes/topbar.php'; ?>
            </header>

            <section class="profile-container">
                <div class="profile-details">
                    <div class="profile-card">
                        <h2>Invoices</h2>

                        <!-- Invoices List -->
                        <div class="invoices-list">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php elseif (empty($invoices)): ?>
                                <div class="alert alert-info">No invoices found.</div>
                            <?php else: ?>
                                <?php foreach ($invoices as $invoice): ?>
                                    <div class="invoice-box" id="invoice-<?= $invoice['booking_id'] ?>">
                                        <div class="invoice-header">
                                            Booking #<?= str_pad($invoice['booking_id'], 6, '0', STR_PAD_LEFT) ?> Active
                                        </div>
                                        <div class="invoice-details">
                                            <div class="detail-line">
                                                <?= htmlspecialchars($invoice['car_type']) ?>
                                            </div>
                                            <div class="detail-line">
                                                <?= htmlspecialchars($invoice['car_model']) ?>
                                            </div>
                                            <div class="detail-line">
                                                Plate No: <?= htmlspecialchars($invoice['plate_number']) ?>
                                            </div>
                                            <div class="detail-line">
                                                <strong>Booking Date:</strong> <?= date('F d, Y', strtotime($invoice['booking_date'])) ?>
                                            </div>
                                            <div class="detail-line">
                                                <strong>Return Date:</strong> <?= date('F d, Y', strtotime($invoice['return_date'])) ?>
                                            </div>
                                            <div class="detail-line">
                                                <strong>Location:</strong> Joenil's Car Rental
                                            </div>
                                            <div class="detail-line">
                                                <strong>Price per day:</strong> ₱<?= number_format($invoice['price_per_day'], 2) ?>
                                            </div>
                                            <div class="detail-line">
                                                <strong>Number of days:</strong> <?= $invoice['number_of_days'] ?> <?= $invoice['number_of_days'] == 1 ? 'day' : 'days' ?>
                                            </div>
                                            <div class="detail-line total-amount">
                                                <strong>Total Amount:</strong> ₱<?= number_format($invoice['total_amount'], 2) ?>
                                            </div>
                                            <div style="text-align: right;">
                                                <button onclick="printInvoice(<?= $invoice['booking_id'] ?>)" class="print-button">
                                                    Print Invoice
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
    function printInvoice(bookingId) {
        const element = document.getElementById(`invoice-${bookingId}`);
        const opt = {
            margin: 1,
            filename: `invoice-${bookingId}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save();
    }
    </script>
</body>
</html> 