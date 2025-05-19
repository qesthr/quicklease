<?php
require_once '../db.php';

// Get the current user's information
$user_id = $_SESSION['user_id'] ?? null;
$user = null;

if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'admin'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

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
        <img class="profile-pic" 
             src="<?php 
                if (isset($user) && is_array($user) && !empty($user['profile_picture'])) {
                    echo '../uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']);
                } else {
                    echo '../images/profile.jpg';
                }
             ?>" 
             alt="Profile Picture"
             onclick="openProfileUploadModal(<?= $user_id ?>)"
             style="cursor: pointer;">
             
        <div class="user-details">
            <p>Welcome, 
            <strong>
                <?php 
                if (isset($user) && is_array($user) && isset($user['firstname'])) {
                    echo htmlspecialchars($user['firstname']);
                } else {
                    echo "Admin";
                }
                ?>
            </strong>
        </p>
        </div>
    </div>
</header>

<style>
.profile-pic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e0e0e0;
    transition: all 0.3s ease;
}

.profile-pic:hover {
    opacity: 0.8;
    transform: scale(1.05);
    border-color: #4CAF50;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-details strong {
    color: #fff;
    font-size: 0.9rem;
}
</style>

<script>
// Add this if you want to make the profile picture clickable to open the upload modal
function openProfileUploadModal(userId) {
    // Check if the modal exists (it should be defined in settings.php)
    if (typeof window.openProfileUploadModal === 'function') {
        window.openProfileUploadModal(userId);
    }
}
</script>