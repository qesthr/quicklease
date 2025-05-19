<div class="sidebar">
    <div class="logo">
        <img src="/quicklease/images/logo3.png" alt="QuickLease">
    </div>

    <ul class="sidebar-details">
        
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
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>" href="settings.php">Settings</a>
        </li>
    </ul>

    <div class="logout-container" style="margin-top: 32vh;">
        <a class="logout-btn" href="../quicklease/dashboard/logout.php" style="text-decoration: none;" style="padding: 12px 10px;">Logout</a>
    </div>
</div>
