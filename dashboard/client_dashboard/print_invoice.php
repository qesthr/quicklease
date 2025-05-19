<?php
require_once '../../includes/session_handler.php';
require_once '../../db.php';

// Start client session
startClientSession();

// Check if user is logged in and is a client
if (!isClient()) {
    die("Unauthorized access");
}

// Get booking ID
$booking_id = $_GET['id'] ?? null;

if (!$booking_id) {
    die("No booking ID provided");
}

try {
    // Fetch booking details with car information
    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            c.brand as car_brand,
            c.model as car_model,
            c.price as price_per_day,
            c.image as car_image,
            u.firstname,
            u.lastname,
            u.email,
            u.customer_phone,
            DATEDIFF(b.return_date, b.booking_date) as total_days,
            (DATEDIFF(b.return_date, b.booking_date) * c.price) as total_amount
        FROM bookings b
        JOIN car c ON b.car_id = c.id
        JOIN users u ON b.users_id = u.id
        WHERE b.id = ? AND b.users_id = ?
    ");
    
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        die("Booking not found or unauthorized");
    }

    // Format dates
    $booking_date = new DateTime($booking['booking_date']);
    $return_date = new DateTime($booking['return_date']);
    $current_date = new DateTime();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .car-details {
            margin-bottom: 30px;
        }
        .car-image {
            max-width: 300px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .total {
            text-align: right;
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 0.9em;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <img src="../../images/logo.png" alt="QuickLease Logo" class="logo">
            <h1>INVOICE</h1>
            <p>Invoice #<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></p>
            <p>Date: <?= $current_date->format('F d, Y') ?></p>
        </div>

        <div class="invoice-details">
            <div class="client-info">
                <h3>Client Information</h3>
                <p>
                    <?= htmlspecialchars($booking['firstname'] . ' ' . $booking['lastname']) ?><br>
                    Email: <?= htmlspecialchars($booking['email']) ?><br>
                    Phone: <?= htmlspecialchars($booking['customer_phone']) ?>
                </p>
            </div>
            <div class="company-info">
                <h3>Company Information</h3>
                <p>
                    QuickLease Car Rental<br>
                    123 Main Street<br>
                    Manila, Philippines<br>
                    Phone: (02) 123-4567
                </p>
            </div>
        </div>

        <div class="car-details">
            <h3>Vehicle Information</h3>
            <img src="../../uploads/cars/<?= htmlspecialchars($booking['car_image']) ?>" 
                 alt="<?= htmlspecialchars($booking['car_brand'] . ' ' . $booking['car_model']) ?>" 
                 class="car-image">
            <p>
                <strong>Vehicle:</strong> <?= htmlspecialchars($booking['car_brand'] . ' ' . $booking['car_model']) ?><br>
                <strong>Booking Date:</strong> <?= $booking_date->format('F d, Y') ?><br>
                <strong>Return Date:</strong> <?= $return_date->format('F d, Y') ?><br>
                <strong>Status:</strong> <?= htmlspecialchars($booking['status']) ?>
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Rate</th>
                    <th>Days</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($booking['car_brand'] . ' ' . $booking['car_model']) ?> Rental</td>
                    <td>₱<?= number_format($booking['price_per_day'], 2) ?>/day</td>
                    <td><?= $booking['total_days'] ?></td>
                    <td>₱<?= number_format($booking['total_amount'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="total">
            Total Amount: ₱<?= number_format($booking['total_amount'], 2) ?>
        </div>

        <div class="footer">
            <p>Thank you for choosing QuickLease Car Rental!</p>
            <p>For questions or concerns, please contact us at support@quicklease.com</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print Invoice</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>
<?php
} catch (PDOException $e) {
    error_log("Invoice error: " . $e->getMessage());
    die("Error generating invoice");
}
?> 