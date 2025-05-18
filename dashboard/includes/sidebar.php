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
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>" href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
    </ul>

    <div style="position: absolute; bottom: 20px; width: 50%; padding: 0 15px; box-sizing: border-box;">
        <a class="logout-btn" href="../logout.php" style="display: block; text-align: center;">Logout</a>
    </div>
</div>
