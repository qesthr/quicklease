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
    <link rel="stylesheet" href="../css/reports.css">

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

        <article class="card card4" aria-label="Reports Summary Card" id="reportsSummaryCard" style="cursor:pointer;">
            <h3>Reports Summary</h3>
            <p>35</p>
        </article>

        <article class="card card5" aria-label="Recent Bookings Card">
            <h3>Recent Bookings</h3>
            <p>35</p>
        </article>
    </section>

    <!-- Reports Summary Modal -->
    <div id="reportsSummaryModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-height: 80vh; overflow-y: auto;">
            <span class="close" id="closeReportsSummaryModal" style="cursor:pointer; float:right; font-size: 24px;">&times;</span>
            <h2>Today's Transactions</h2>
            <button id="printReportsBtn" style="margin-bottom: 10px;">Print</button>
            <table id="transactionsTable" border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Transactions will be dynamically inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('reportsSummaryCard').addEventListener('click', function() {
            fetch('fetch_today_transactions.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#transactionsTable tbody');
                    tbody.innerHTML = '';
                    data.forEach(tx => {
                        const tr = document.createElement('tr');
                        const timeTd = document.createElement('td');
                        timeTd.textContent = tx.time;
                        const typeTd = document.createElement('td');
                        typeTd.textContent = tx.type;
                        const detailsTd = document.createElement('td');
                        detailsTd.textContent = tx.details;
                        tr.appendChild(timeTd);
                        tr.appendChild(typeTd);
                        tr.appendChild(detailsTd);
                        tbody.appendChild(tr);
                    });
                    document.getElementById('reportsSummaryModal').style.display = 'block';
                })
                .catch(error => {
                    alert('Failed to fetch transactions: ' + error);
                });
        });

        document.getElementById('closeReportsSummaryModal').addEventListener('click', function() {
            document.getElementById('reportsSummaryModal').style.display = 'none';
        });

        document.getElementById('printReportsBtn').addEventListener('click', function() {
            const printContents = document.getElementById('reportsSummaryModal').innerHTML;
            const originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        });
    </script>

    </div>


    <script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>
</body>
</html>
