<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../vendor/autoload.php'; // Path to Composer's autoload.php
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

    // Start mPDF
    $mpdf = new \Mpdf\Mpdf();

    // HTML content for the PDF
    $html = '
    <h1 style="text-align:center;">INVOICE</h1>
    <p style="text-align:center;">Invoice #' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT) . '</p>
    <p style="text-align:center;">Date: ' . $current_date->format('F d, Y') . '</p>
    <hr>
    <h3>Client Information</h3>
    <p>
        ' . htmlspecialchars($booking['firstname'] . ' ' . $booking['lastname']) . '<br>
        Email: ' . htmlspecialchars($booking['email']) . '<br>
        Phone: ' . htmlspecialchars($booking['customer_phone']) . '
    </p>
    <h3>Vehicle Information</h3>
    <img src="../../uploads/' . htmlspecialchars($booking['car_image']) . '" style="width:200px;"><br>
    <strong>Vehicle:</strong> ' . htmlspecialchars($booking['car_model']) . '<br>
    <strong>Booking Date:</strong> ' . $booking_date->format('F d, Y') . '<br>
    <strong>Return Date:</strong> ' . $return_date->format('F d, Y') . '<br>
    <strong>Status:</strong> ' . htmlspecialchars($booking['status']) . '<br>
    <hr>
    <table width="100%" border="1" cellspacing="0" cellpadding="5">
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
                <td>' . htmlspecialchars($booking['car_model']) . ' Rental</td>
                <td>₱' . number_format($booking['price_per_day'], 2) . '/day</td>
                <td>' . $booking['total_days'] . '</td>
                <td>₱' . number_format($booking['total_amount'], 2) . '</td>
            </tr>
        </tbody>
    </table>
    <h2 style="text-align:right;">Total Amount: ₱' . number_format($booking['total_amount'], 2) . '</h2>
    <p style="text-align:center;">Thank you for choosing QuickLease Car Rental!</p>
    ';

    $mpdf->WriteHTML($html);
    $mpdf->Output('invoice_' . $booking['id'] . '.pdf', 'I'); // 'I' for inline display, 'D' for download

} catch (PDOException $e) {
    die("Error generating invoice: " . $e->getMessage());
}
?> 