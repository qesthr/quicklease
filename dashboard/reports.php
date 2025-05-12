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
        .recent-bookings-section { max-width: 900px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
<?php include 'includes/topbar.php'; ?>
<section class="dashboard-cards">
    <article class="card card1"><h3>Total Bookings</h3><p id="totalBookings">Loading...</p></article>
    <article class="card card2"><h3>Total Cars</h3><p id="totalCars">Loading...</p></article>
    <article class="card card3"><h3>No. Client</h3><p id="totalAccounts">Loading...</p></article>
    <article class="card card4" id="reportsSummaryCard" style="cursor:pointer;"><h3>Reports Summary</h3><p>View</p></article>
    <article class="card card5"><h3>Recent Bookings</h3><p id="recentBookingsCount">Loading...</p></article>
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
    function loadDashboardSummary() {
        fetch('fetch_today_transactions.php')
            .then(res => res.json())
            .then(data => {
                document.getElementById('totalBookings').textContent = data.totalBookings;
                document.getElementById('totalCars').textContent = data.totalCars;
                document.getElementById('totalAccounts').textContent = data.totalAccounts;
            });
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
        document.getElementById('filterDate').value = today;
        document.getElementById('pdfDate').value = today;
        loadDashboardSummary();
        loadRecentBookingsCount();
        loadRecentBookings();
    };

    function loadRecentBookings() {
        fetch('fetch_recent_bookings.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#recentBookingsTable tbody');
                tbody.innerHTML = '';
                if (data.bookings && data.bookings.length > 0) {
                    data.bookings.forEach(booking => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${booking.booking_id}</td>
                            <td>${booking.customer_name}</td>
                            <td>${booking.car_model}</td>
                            <td>${booking.booking_date}</td>
                            <td>${booking.return_date}</td>
                            <td>${booking.status}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6">No recent bookings found.</td></tr>';
                }
            })
            .catch(err => {
                console.error('Error loading recent bookings:', err);
                const tbody = document.querySelector('#recentBookingsTable tbody');
                tbody.innerHTML = '<tr><td colspan="6">Error loading recent bookings.</td></tr>';
            });
    }
</script>
<script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>
</body>
</html>
    };
    </script>
    <script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>
<script>
    function loadRecentBookings() {
        fetch('fetch_recent_bookings.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#recentBookingsTable tbody');
                tbody.innerHTML = '';
                if (data.bookings && data.bookings.length > 0) {
                    data.bookings.forEach(booking => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${booking.booking_id}</td>
                            <td>${booking.customer_name}</td>
                            <td>${booking.car_model}</td>
                            <td>${booking.booking_date}</td>
                            <td>${booking.return_date}</td>
                            <td>${booking.status}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6">No recent bookings found.</td></tr>';
                }
            })
            .catch(err => {
                console.error('Error loading recent bookings:', err);
                const tbody = document.querySelector('#recentBookingsTable tbody');
                tbody.innerHTML = '<tr><td colspan="6">Error loading recent bookings.</td></tr>';
            });
    }

    window.onload = () => {
        const today = getCurrentDate();
        document.getElementById('filterDate').value = today;
        document.getElementById('pdfDate').value = today;
        loadDashboardSummary();
        loadRecentBookings();
    };
</script>
