<div class="sidebar">
    <ul class="sidebar-details">
        <div class="logo">
            <img src="/quicklease/images/logo3.png" alt="QuickLease">
        </div>
        <li>
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" href="reports.php">Reports</a>
        </li>
        <li>
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'accounts.php' ? 'active' : '' ?>" href="accounts.php">Accounts</a>
        </li>
        <li>
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'cars.php' ? 'active' : '' ?>" href="cars.php">Cars</a>
        </li>
        <li>
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : '' ?>" href="bookings.php">Bookings</a>
        </li>
        <li>
            <button class="logout-btn" onclick="window.location.href='../loginpage/login.php'">Logout</button>
        </li>
    </ul>
</div>
