<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickLease | Admin Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>
    <div class="sidebar">
        <div class="logo"> <img src="/quicklease/images/logo.png" alt=""></div>
        <button class="nav-btn active">Reports</button>
        <button class="nav-btn">Accounts</button>
        <button class="nav-btn">Cars</button>
        <button class="nav-btn">Bookings</button>
        <button class="logout-btn">Logout</button>
    </div>

    <div class="main">
        <header>
            <h1>Reports</h1>
            <div class="user-info">
                <span class="notification"></span>
                <span class="profile-pic"></span>
            </div>
        </header>

        <section class="stats-cards">
            <div class="card">Total Bookings: <span>20</span></div>
            <div class="card">Total Revenue: <span>20</span></div>
            <div class="card">Active Rentals: <span>20</span></div>
            <div class="card">Cancelled Bookings: <span>10</span></div>
        </section>

        <section class="recent-bookings">
            <h2>Recent Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer Name</th>
                        <th>Car Model</th>
                        <th>Booking Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td>John Doe</td>
                        <td>Toyota Camry</td>
                        <td>April-28-2025</td>
                        <td>April-29-2025</td>
                        <td>Completed</td>
                    </tr>
                    <tr>
                        <td>002</td>
                        <td>Jane Smith</td>
                        <td>Honda Accord</td>
                        <td>April-28-2025</td>
                        <td>April-30-2025</td>
                        <td>Active</td>
                    </tr>
                    <tr>
                        <td>003</td>
                        <td>Bob Johnson</td>
                        <td>Nissan Navara</td>
                        <td>April-29-2025</td>
                        <td>April-29-2025</td>
                        <td>Cancelled</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="charts">
            <canvas id="pieChart" width="300" height="300"></canvas>
            <button class="print-btn">PRINT</button>
        </section>

        <section class="utilization">
            <div class="utilization-card">Fleet Utilization Rate: <span>50%</span></div>
            <div class="utilization-card">Fleet Utilization Rate: <span>50%</span></div>
        </section>
    </div>

    
</body>
</html>
