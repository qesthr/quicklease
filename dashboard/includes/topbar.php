<header class="topbar">
    <h1>
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $page_titles = [
            'reports.php' => 'Reports Dashboard',
            'accounts.php' => 'Account Management',
            'cars.php' => 'Car Management',
            'bookings.php' => 'Booking Management',
            'settings.php' => 'Admin Settings'
        ];
        echo $page_titles[$current_page] ?? 'Dashboard';
        ?>
    </h1>
    <div class="user-info">
        <div class="notification">
            <i class="fas fa-bell"></i>
        </div>
        <img class="profile-pic" src="../images/profile.jpg" alt="Profile Picture">
        <div class="user-details">
            <p>Welcome, <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></strong></p>
        </div>
    </div>
</header>