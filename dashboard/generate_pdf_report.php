<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

$date = $_GET['date'] ?? date('Y-m-d');

try {
    // Create database connection using existing db.php
    $sql = "SELECT 
                b.id AS booking_id,
                CONCAT(u.firstname, ' ', u.lastname) AS customer_name,
                c.model AS car_model,
                b.booking_date,
                b.return_date,
                b.location,
                b.status,
                DATEDIFF(b.return_date, b.booking_date) * c.price AS total_cost
            FROM bookings b
            JOIN users u ON b.users_id = u.id
            JOIN car c ON b.car_id = c.id
            WHERE DATE(b.booking_date) = :date
            ORDER BY b.booking_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Configure mPDF
    $mpdf = new Mpdf([
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 15,
        'margin_bottom' => 15
    ]);

    // Set document information
    $mpdf->SetTitle('Booking Report - ' . $date);
    $mpdf->SetAuthor('QuickLease Admin');
    $mpdf->SetCreator('QuickLease');

    // Set header
    $mpdf->SetHeader('QuickLease Car Rental||Page {PAGENO}');
    
    // Set footer
    $mpdf->SetFooter('Generated: ' . date('Y-m-d H:i:s'));

    // Prepare HTML content
    $html = '
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; color: #333; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
    </style>
    <h2>Booking Transactions Report</h2>
    <p><strong>Date:</strong> ' . date('F d, Y', strtotime($date)) . '</p>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Car Model</th>
                <th>Booking Date</th>
                <th>Return Date</th>
                <th>Location</th>
                <th>Status</th>
                <th>Total Cost</th>
            </tr>
        </thead>
        <tbody>';

    $totalAmount = 0;
    foreach ($result as $row) {
        $totalAmount += $row['total_cost'];
        $html .= '<tr>
            <td>' . $row['booking_id'] . '</td>
            <td>' . $row['customer_name'] . '</td>
            <td>' . $row['car_model'] . '</td>
            <td>' . date('M d, Y', strtotime($row['booking_date'])) . '</td>
            <td>' . date('M d, Y', strtotime($row['return_date'])) . '</td>
            <td>' . $row['location'] . '</td>
            <td style="text-transform: capitalize;">' . $row['status'] . '</td>
            <td>₱' . number_format($row['total_cost'], 2) . '</td>
        </tr>';
    }

    $html .= '<tr class="total-row">
        <td colspan="7" style="text-align: right;"><strong>Total Amount:</strong></td>
        <td><strong>₱' . number_format($totalAmount, 2) . '</strong></td>
    </tr>';
    $html .= '</tbody></table>';

    // Add generation timestamp
    $html .= '<div style="margin-top: 20px; font-size: 12px; color: #666; text-align: center;">
        Generated on ' . date('F d, Y h:i A') . '
    </div>';

    // Generate PDF
    $mpdf->WriteHTML($html);
    $mpdf->Output('QuickLease_Booking_Report_' . $date . '.pdf', 'D');
    exit;

} catch (Exception $e) {
    die("Error generating PDF: " . $e->getMessage());
}
?>
