<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../vendor/autoload.php'; // adjust if needed
    require_once __DIR__ . '/../loginpage/includes/db.php';

    $mpdf = new \Mpdf\Mpdf();
    header('Content-Type: application/pdf');

    $stmt = $conn->query("SELECT b.id, a.full_name, c.model, b.start_date, b.end_date, b.status 
                          FROM bookings b 
                          JOIN accounts a ON b.account_id = a.id 
                          JOIN cars c ON b.car_id = c.id 
                          ORDER BY b.start_date DESC");

    $bookings = $stmt->fetch_all(MYSQLI_ASSOC);
    $count = 1;

    $html = '
    <html>
        <head>
            <style>
                body {
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                    font-size: 12px;
                    padding: 20px;
                    color: #333;
                }
                h4 {
                    text-align: center;
                    margin-bottom: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 11px;
                }
                th, td {
                    background-color: #f8f9fa;
                    border: 1px solid #ccc;
                    padding: 8px;
                    text-align: left;
                }
                .signature-section {
                    margin-top: 40px;
                    display: flex;
                    justify-content: space-between;
                    font-size: 11px;
                }
                .signature {
                    width: 50%;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <h4>Bookings Report</h4>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Booking ID</th>
                        <th>Customer Name</th>
                        <th>Car Model</th>
                        <th>Booking Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

                foreach ($bookings as $row) {
                    $html .= '
                        <tr>
                            <td>' . $count++ . '</td>
                            <td>' . htmlspecialchars($row['id']) . '</td>
                            <td>' . htmlspecialchars($row['full_name']) . '</td>
                            <td>' . htmlspecialchars($row['model']) . '</td>
                            <td>' . htmlspecialchars($row['start_date']) . '</td>
                            <td>' . htmlspecialchars($row['end_date']) . '</td>
                            <td>' . htmlspecialchars($row['status']) . '</td>
                        </tr>';
                }

    $html .= '
                </tbody>
            </table>

            <div class="signature-section">
                <div class="signature">
                    <p>_________________________________________</p>
                    <p><strong>General Manager</strong></p>
                </div>
            </div>
        </body>
    </html>';

    $mpdf->SetHTMLFooter('<div style="text-align: left;">Page {PAGENO}/{nbpg}</div>');
    $mpdf->WriteHTML($html);
    $mpdf->Output('', 'I');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Booking Report</title>
    <link rel="stylesheet" href="../css/dashboard.css"> <!-- Optional styling -->
</head>
<body>
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="main">
        <h1>Booking Reports</h1>
        <form action="" method="POST">
            <button type="submit" class="btn btn-primary">Print Report as PDF</button>
        </form>
    </div>
</body>
</html>
