<header class="topbar">
    <h1>
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $page_titles = [
            'reports.php' => 'Reports',
            'accounts.php' => 'Accounts',
            'cars.php' => 'Car Catalogue',
            'bookings.php' => 'Bookings',
            'settings.php' => 'Admin Settings'
        ];
        echo $page_titles[$current_page] ?? 'Dashboard';
        ?>
    </h1>
    <div class="user-info">
        <div class="notification">
            <i class="fas fa-bell"></i>
            <div id="notificationDropdown" class="notification-dropdown">
                <h3>Notifications</h3>
                <ul id="notificationList"></ul>
            </div>
        </div>
        <img class="profile-pic" src="../images/profile.jpg" alt="Profile Picture">
        <div class="user-details">
            <p>Welcome, <strong><?php echo isset($_SESSION['firstname']) ? htmlspecialchars($_SESSION['firstname']) : 'Administrator'; ?></strong></p>
        </div>
    </div>
</header>