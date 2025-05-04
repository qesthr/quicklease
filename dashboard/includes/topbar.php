<header class="topbar">
    <h1>Dashboard</h1>
    <div class="user-info">
        <div class="notification">
            <i class="fa-regular fa-bell"></i>
        </div>

        <img class="profile-pic" src="../images/profile.jpg" alt="">
 
        <div class="user-details">
            <p>Welcome, <strong><?= htmlspecialchars($_SESSION['username'])?> </strong></p>
            <!-- Optional: Display User Name -->
        </div>
    </div>
</header>