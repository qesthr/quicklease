<div class="sidebar">
    <div class="logo">
        <img src="/quicklease/images/logo3.png" alt="QuickLease">
    </div>

    <ul class="sidebar-details">
        
        <li>
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'client_dashboard/profile_userdetails.php' ? 'active' : '' ?>" href="../client_dashboard/client_profile_userdetails.php">Profile</a>
        </li>
        <li>
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'client_cars.php' ? 'active' : '' ?>" href="../client_dashboard/client_cars.php">Cars</a>
        </li>
        <li>
            <a class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'client_booking.php' ? 'active' : '' ?>" href="../client_dashboard/client_booking.php">Booking</a>
        </li>
        
        <li >
            <button class="logout-btn" onclick="window.location.href='../loginpage/login.php'">Logout</button>
        </li>
    </ul>
</div>
