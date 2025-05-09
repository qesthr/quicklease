<header class="topbar">
    <h1>Profile</h1>

    <div class="search-container">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search car models..." 
                    value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if(!empty($search)): ?>
                <a href="client_cars.php" class="clear-search">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="user-info">
        <div class="notification">
            <i class="fa-regular fa-bell"></i>
        </div>

        <img class="profile-pic" src="../images/profile.jpg" alt="">
 
        <div class="user-details">
            <p>Welcome, <strong>Queen </strong></p>
            <!-- Optional: Display User Name -->
        </div>
    </div>
</header>