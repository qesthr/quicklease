<?php
require_once __DIR__ . '/../loginpage/vendor/autoload.php';
require_once __DIR__ . '/../db.php';

use Mpdf\Mpdf;

$date = $_GET['date'] ?? date('Y-m-d');

try {
    // Use existing $pdo from db.php
    // Fetch bookings for the given date
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

    // Prepare HTML for PDF
    $html = "<h2 style='text-align:center;'>Booking Transactions - $date</h2>";
    $html .= "<table border='1' cellpadding='8' cellspacing='0' width='100%'>
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
            <tbody>";

    foreach ($result as $row) {
        $html .= "<tr>
                    <td>{$row['booking_id']}</td>
                    <td>{$row['customer_name']}</td>
                    <td>{$row['car_model']}</td>
                    <td>{$row['booking_date']}</td>
                    <td>{$row['return_date']}</td>
                    <td>{$row['location']}</td>
                    <td>{$row['status']}</td>
                    <td>₱" . number_format($row['total_cost'], 2) . "</td>
                  </tr>";
    }

    $html .= "</tbody></table>";

    // Generate and output the PDF
    $mpdf = new Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output("Booking_Report_$date.pdf", 'D');
    exit;
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>
