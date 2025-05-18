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
        
      
    </ul>
    <div style="position: absolute; bottom: 20px; width: 50%; padding: 0 15px; box-sizing: border-box;">
        <a class="logout-btn" href="../../dashboard/logout.php" style="display: block; text-align: center;">Logout</a>
    </div>
</div>
