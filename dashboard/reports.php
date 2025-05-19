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
    <!-- <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { 
            background-color: #fff; 
            margin: 2% auto; 
            padding: 25px;
            border-radius: 12px;
            width: 95%;
            max-width: 1400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-content h2 { margin-top: 0; text-align: center; color: #1818CA; font-size: 24px; margin-bottom: 20px; }
        .modal-content table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 14px;
            margin-top: 20px;
            background: white;
        }
        .modal-content th, .modal-content td { 
            border: 1px solid #e0e0e0; 
            padding: 12px; 
            text-align: left; 
        }
        .modal-content th { 
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .modal-content tr:hover { background-color: #f5f5f5; }
        .modal-content input[type="date"], .modal-content button { 
            margin: 10px 5px; 
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 6px;
        }
        .close { 
            position: absolute; 
            right: 25px; 
            top: 20px; 
            font-size: 28px; 
            font-weight: bold; 
            color: #666;
            transition: color 0.3s;
        }
        .close:hover { color: #f44336; cursor: pointer; }
        .error-message {
            background-color: #fee;
            color: #c00;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            display: none;
        }
        .no-data-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
            background: #f9f9f9;
            border-radius: 4px;
            margin: 20px 0;
            display: none;
        }
        .chart-container { 
            width: 100%; 
            height: 300px; 
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style> -->
</head>
<body class="reports-body">
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
        <div class="modal-header">
            <h2>Booking Transactions</h2>
        </div>

        <div class="filter-controls">
            <label for="filterDate">Select Date:</label>
            <input type="date" id="filterDate" value="">
            <button id="filterBtn" class="filter-btn">Filter</button>
            <form method="GET" action="generate_pdf_report.php" style="display:inline-block;">
                <input type="date" name="date" id="pdfDate" value="">
                <button type="submit" class="download-btn">Download PDF</button>
            </form>
        </div>

        <div id="errorMessage" class="error-message"></div>
        <div id="noDataMessage" class="no-data-message">No bookings found for the selected date.</div>

        <div class="table-container">
            <table id="transactionsTable" class="transactions-table">
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
        const errorMessage = document.getElementById('errorMessage');
        const noDataMessage = document.getElementById('noDataMessage');
        const tableBody = document.querySelector('#transactionsTable tbody');
        
        errorMessage.style.display = 'none';
        noDataMessage.style.display = 'none';
        
        fetch(`fetch_today_transactions.php?date=${date}`)
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                tableBody.innerHTML = '';
                
                if (!data.bookings || data.bookings.length === 0) {
                    noDataMessage.style.display = 'block';
                    return;
                }

                data.bookings.forEach(row => {
                    const tr = document.createElement('tr');
                    const statusClass = `status-${row.status.toLowerCase()}`;
                    tr.innerHTML = `
                        <td>${row.booking_id}</td>
                        <td>${row.customer_name}</td>
                        <td>${row.car_model}</td>
                        <td>${row.booking_date}</td>
                        <td>${row.return_date}</td>
                        <td>${row.location}</td>
                        <td class="${statusClass}">${row.status}</td>
                        <td>₱${row.total_cost}</td>
                    `;
                    tableBody.appendChild(tr);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.textContent = 'Error loading booking data. Please try again.';
                errorMessage.style.display = 'block';
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
<script>
    // Notification functionality
    document.addEventListener('DOMContentLoaded', function() {
        const bellIcon = document.querySelector('.notification i');
        const dropdown = document.getElementById('notificationDropdown');

        if (bellIcon && dropdown) {
            bellIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                
                if (dropdown.style.display === 'block') {
                    fetchNotifications();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && !bellIcon.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }

        function fetchNotifications() {
            fetch('fetch_notification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notificationList = document.getElementById('notificationList');
                        notificationList.innerHTML = '';

                        if (data.data.length === 0) {
                            notificationList.innerHTML = '<li class="no-notifications">No new notifications</li>';
                            return;
                        }

                        data.data.forEach(notification => {
                            const li = document.createElement('li');
                            li.textContent = notification.message;
                            if (!notification.is_read) {
                                li.classList.add('unread');
                            }
                            notificationList.appendChild(li);
                        });
                    } else {
                        console.error('Error fetching notifications:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    });
</script>
<script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>
</body>
</html>