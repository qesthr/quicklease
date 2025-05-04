<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../vendor/autoload.php'; // adjust if needed
    require __DIR__ . '/../db.php';

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
    <title>Admin | Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css"> 

</head>
<body>
    
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>

        <section class="dashboard-cards">
            <article class="card card1" aria-label="Total Bookings Card">
                <h3>Total Bookings</h3>
                <p>120</p>
            </article>
            
            <article class="card card2" aria-label="Total Cars Card">
                <h3>Total Cars</h3>
                <p>15</p>
            </article>
            
            <article class="card card3" aria-label="Total Accounts Card">
                <h3>Total Accounts</h3>
                <p>35</p>
            </article>

            <article class="card card4" aria-label="Reports Summary Card">
                <h3>Reports Summary</h3>
                <p>35</p>
            </article>

            <article class="card card5" aria-label="Recent Bookings Card">
                <h3>Recent Bookings</h3>
                <p>35</p>
            </article>
        </section>

    </div>

</body>
</html>
