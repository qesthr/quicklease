<div class="sidebar">
    <div class="logo"><img src="/quicklease/images/logo3.png" alt="QuickLease"></div>
    <a href="reports.php"><button class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">Reports</button></a>
    <a href="accounts.php"><button class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'accounts.php' ? 'active' : '' ?>">Accounts</button></a>
    <a href="cars.php"><button class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'cars.php' ? 'active' : '' ?>">Cars</button></a>
    <a href="bookings.php"><button class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : '' ?>">Bookings</button></a>
    <button class="logout-btn" onclick="window.location.href='../loginpage/login.php'">Logout</button>
</div>