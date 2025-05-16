<?php
// Updated reports.php with mPDF support and filterable report
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css"> 
    <link rel="stylesheet" href="../css/reports.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #fff; margin: 5% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 1000px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative; }
        .modal-content h2 { margin-top: 0; text-align: center; }
        .modal-content table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .modal-content th, .modal-content td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .modal-content th { background-color: #f2f2f2; }
        .modal-content input[type="date"], .modal-content button { margin: 10px 5px; padding: 5px 10px; font-size: 13px; }
        .close { position: absolute; right: 20px; top: 15px; font-size: 28px; font-weight: bold; color: #000; }
        .close:hover { color: red; cursor: pointer; }
        .chart-container { 
            width: 100%; 
            height: 300px; 
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
<?php include 'includes/topbar.php'; ?>
<section class="dashboard-cards">
    <article class="card card1"><h3>Total Bookings</h3><p id="totalBookings">Loading...</p></article>
    <article class="card card2"><h3>Total Cars</h3><p id="totalCars">Loading...</p></article>
    <article class="card card3"><h3>Total Accounts</h3><p id="totalAccounts">Loading...</p></article>
    <article class="card card4" id="reportsSummaryCard" style="cursor:pointer;"><h3>Reports Summary</h3><p>View</p></article>
    <article class="card card5">
        <h3>Booking Activities</h3>
        <div class="chart-container">
            <canvas id="bookingChart"></canvas>
        </div>
    </article>
</section>

<!-- Modal -->
<div id="reportsSummaryModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeReportsSummaryModal">&times;</span>
        <h2>Booking Transactions</h2>

        <label for="filterDate">Select Date:</label>
        <input type="date" id="filterDate" value="">
        <button id="filterBtn">Filter</button>
        <form method="GET" action="generate_pdf_report.php" style="display:inline-block;">
            <input type="date" name="date" id="pdfDate" value="">
            <button type="submit">Download PDF</button>
        </form>

        <table id="transactionsTable">
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
            <tbody>
                <!-- Filled dynamically -->
            </tbody>
        </table>
    </div>
</div>

<script>
    let bookingChart;

    function loadDashboardSummary() {
        fetch('fetch_today_transactions.php')
            .then(res => res.json())
            .then(data => {
                document.getElementById('totalBookings').textContent = data.totalBookings;
                document.getElementById('totalCars').textContent = data.totalCars;
                document.getElementById('totalAccounts').textContent = data.totalAccounts;
            });
    }

    function loadBookingChart() {
        // Fetch booking statistics
        fetch('fetch_booking_stats.php')
            .then(res => res.json())
            .then(data => {
                const ctx = document.getElementById('bookingChart').getContext('2d');
                
                if (bookingChart) {
                    bookingChart.destroy();
                }

                bookingChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.values,
                            backgroundColor: [
                                '#4CAF50',  // Green
                                '#2196F3',  // Blue
                                '#FFC107',  // Yellow
                                '#FF5722',  // Orange
                                '#9C27B0',  // Purple
                                '#E91E63',  // Pink
                                '#795548'   // Brown
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            title: {
                                display: true,
                                text: 'Booking Distribution',
                                font: {
                                    size: 16
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value * 100) / total).toFixed(1);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
    }

    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    function loadTransactionsByDate(date) {
        fetch(`fetch_today_transactions.php?date=${date}`)
            .then(res => res.json())
            .then(data => {
                console.log('Booking transactions data:', data);
                const tbody = document.querySelector('#transactionsTable tbody');
                tbody.innerHTML = '';
                data.bookings.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.booking_id}</td>
                        <td>${row.customer_name}</td>
                        <td>${row.car_model}</td>
                        <td>${row.booking_date}</td>
                        <td>${row.return_date}</td>
                        <td>${row.location}</td>
                        <td>${row.status}</td>
                        <td>${row.total_cost}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    function getCurrentDate() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    document.getElementById('reportsSummaryCard').addEventListener('click', () => {
        const today = getCurrentDate();
        console.log('Setting date inputs to:', today);
        document.getElementById('filterDate').value = today;
        document.getElementById('pdfDate').value = today;
        loadTransactionsByDate(today);
        document.getElementById('reportsSummaryModal').style.display = 'block';
    });

    document.getElementById('filterBtn').addEventListener('click', () => {
        const date = document.getElementById('filterDate').value;
        if (date) {
            loadTransactionsByDate(date);
            document.getElementById('pdfDate').value = date;
        }
    });

    document.getElementById('closeReportsSummaryModal').addEventListener('click', () => {
        document.getElementById('reportsSummaryModal').style.display = 'none';
    });

    window.onload = () => {
        const today = getCurrentDate();
        console.log('Window onload setting date inputs to:', today);
        document.getElementById('filterDate').value = today;
        document.getElementById('pdfDate').value = today;
        loadDashboardSummary();
        loadBookingChart();
    };

    // Refresh chart every 5 minutes
    setInterval(loadBookingChart, 300000);
</script>
<script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>
</body>
</html>