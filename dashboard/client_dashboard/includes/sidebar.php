<div class="sidebar">
    <div class="logo">
        <img src="/quicklease/images/logo3.png" alt="QuickLease">
    </div>

    <ul class="sidebar-details">
        
        <li>
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'client_account.php' ? 'active' : '' ?>" href="reports.php">Account</a>
        </li>
        <li>
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'client_cars.php' ? 'active' : '' ?>" href="accounts.php">Cars</a>
        </li>
        <li>
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'client_booking.php' ? 'active' : '' ?>" href="cars.php">Booking</a>
        </li>
        
        <li >
            <button class="logout-btn" onclick="window.location.href='../loginpage/login.php'">Logout</button>
        </li>
    </ul>
</div>
