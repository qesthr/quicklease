<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection if not already included
if (!isset($pdo)) {
    require_once __DIR__ . '/../../db.php';
}

require_once __DIR__ . '/NotificationHandler.php';

// Get user information from session
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? 'user';
$user_name = $_SESSION['name'] ?? 'Guest';

// Initialize notification handler
$notificationHandler = new NotificationHandler($pdo, $user_id, $user_role);

// Get initial unread count
$unreadCount = $notificationHandler->getUnreadCount();
?>

<header class="topbar">
    <h1>Dashboard</h1>
    <div class="user-info">
        <div class="notification-wrapper">
            <div class="notification-bell" id="notificationBell">
                <i class="fa-solid fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="unread-badge"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </div>
            <div class="notification-container" id="notificationContainer">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <?php if ($unreadCount > 0): ?>
                        <button class="mark-all-read" onclick="notificationManager.markAllAsRead()">
                            Mark all as read
                        </button>
                    <?php endif; ?>
                </div>
                <div class="notification-list" id="notificationList">
                    <div class="notification-loading">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        Loading notifications...
                    </div>
                </div>
            </div>
        </div>

        <img class="profile-pic" src="../images/profile.jpg" alt="">
 
        <div class="user-details">
            <p>Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong></p>
        </div>
    </div>
</header>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Initialize Notification System -->
<script src="../javascript/notifications.js"></script>
<script>
let notificationManager;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize notification manager with user info
    notificationManager = new NotificationManager(
        <?php echo $user_id; ?>,
        '<?php echo $user_role; ?>'
    );
});
</script>

<!-- Add notification styles -->
<style>
.notification-wrapper {
    position: relative;
    margin-right: 20px;
}

.notification-bell {
    cursor: pointer;
    position: relative;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s;
}

.notification-bell:hover {
    background-color: rgba(0, 0, 0, 0.1);
}

.notification-bell i {
    font-size: 1.2rem;
    color: #333;
}

.unread-badge {
    position: absolute;
    top: 0;
    right: 0;
    background-color: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: translate(25%, -25%);
}

.notification-container {
    display: none;
    position: absolute;
    top: calc(100% + 10px);
    right: -10px;
    width: 320px;
    max-height: 400px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
}

.notification-container:before {
    content: '';
    position: absolute;
    top: -6px;
    right: 24px;
    width: 12px;
    height: 12px;
    background: white;
    transform: rotate(45deg);
    border-left: 1px solid rgba(0,0,0,0.1);
    border-top: 1px solid rgba(0,0,0,0.1);
}

.notification-container.show {
    display: block;
    animation: slideIn 0.2s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}

.notification-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.notification-list {
    max-height: 350px;
    overflow-y: auto;
    padding: 8px 0;
}

.notification-loading {
    padding: 20px;
    text-align: center;
    color: #666;
}

.notification-loading i {
    margin-right: 8px;
}

.notification-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-item.unread {
    background-color: #f0f7ff;
}

.notification-item:hover {
    background-color: #f5f5f5;
}

.notification-content p {
    margin: 0 0 4px 0;
    font-size: 14px;
    color: #333;
    line-height: 1.4;
}

.notification-content small {
    color: #666;
    font-size: 12px;
}

.mark-all-read {
    padding: 4px 8px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: background-color 0.2s;
}

.mark-all-read:hover {
    background: #0056b3;
}

.notification-empty {
    padding: 20px;
    text-align: center;
    color: #666;
    font-size: 14px;
}

.priority-high .notification-content p {
    color: #dc3545;
    font-weight: 500;
}

.priority-medium .notification-content p {
    color: #333;
}

.priority-low .notification-content p {
    color: #666;
}

/* Scrollbar Styling */
.notification-list::-webkit-scrollbar {
    width: 6px;
}

.notification-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.notification-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.notification-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Add notification popup styles */
.notification-popup {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 15px;
    z-index: 1100;
    max-width: 300px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease-out;
}

.notification-popup.show {
    opacity: 1;
    transform: translateX(0);
}

.notification-popup-content {
    display: flex;
    align-items: flex-start;
}

.notification-popup-content p {
    margin: 0;
    font-size: 14px;
    line-height: 1.4;
    color: #333;
}

/* Add date group styles */
.notification-date-group {
    margin-bottom: 15px;
}

.notification-date {
    padding: 8px 15px;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    background: #f5f5f5;
    border-radius: 4px;
    margin-bottom: 8px;
}
</style>