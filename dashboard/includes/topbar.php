<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../db.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or handle the error
    header("Location: /quicklease/loginpage/login.php");
    exit;
}

try {
    // Verify database connection
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }

    // Get unread notifications count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unreadCount = $stmt->fetchColumn();

    // Get recent notifications
    $stmt = $pdo->prepare("
        SELECT n.*, b.car_id, c.model as car_model 
        FROM notifications n 
        LEFT JOIN bookings b ON n.booking_id = b.id 
        LEFT JOIN car c ON b.car_id = c.id 
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error in topbar.php: " . $e->getMessage());
    $unreadCount = 0;
    $notifications = [];
} catch (Exception $e) {
    error_log("General Error in topbar.php: " . $e->getMessage());
    $unreadCount = 0;
    $notifications = [];
}
?>

<header class="topbar">
    <h1>Dashboard</h1>
    <div class="user-info">
        <div class="notification-wrapper">
            <div class="notification" id="notificationBell">
                <i class="fa-regular fa-bell"></i>
                <span class="notification-badge" style="display: none;"></span>
            </div>
            
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <button class="mark-all-read" onclick="markAllAsRead()" style="display: none;">Mark all as read</button>
                </div>
                <div class="notification-list">
                    <div class="no-notifications">Loading notifications...</div>
                </div>
            </div>
        </div>

        <img class="profile-pic" src="../images/profile.jpg" alt="">
 
        <div class="user-details">
            <p>Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        </div>
    </div>
</header>

<style>
.notification-wrapper {
    position: relative;
    display: inline-block;
}

.notification {
    cursor: pointer;
    padding: 10px;
    position: relative;
}

.notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
}

.notification-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    width: 300px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

.notification-header {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h3 {
    margin: 0;
    font-size: 16px;
}

.mark-all-read {
    background: none;
    border: none;
    color: #007bff;
    cursor: pointer;
    font-size: 12px;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: start;
    cursor: pointer;
    transition: background-color 0.3s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f0f7ff;
}

.notification-icon {
    margin-right: 15px;
    color: #007bff;
}

.notification-content p {
    margin: 0 0 5px 0;
    font-size: 14px;
}

.notification-content small {
    color: #6c757d;
    font-size: 12px;
}

.no-notifications {
    padding: 20px;
    text-align: center;
    color: #6c757d;
}
</style>

<script>
const notificationBell = document.getElementById('notificationBell');
const notificationDropdown = document.getElementById('notificationDropdown');
const notificationList = document.querySelector('.notification-list');
const notificationBadge = document.querySelector('.notification-badge');
let isNotificationsFetched = false;

function fetchNotifications() {
    fetch('../dashboard/fetch_notification.php')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                updateNotificationsUI(result.data);
            } else {
                notificationList.innerHTML = '<div class="no-notifications">Error: ' + (result.error || 'Failed to load notifications') + '</div>';
            }
        })
        .catch(error => {
            notificationList.innerHTML = '<div class="no-notifications">Error loading notifications</div>';
            console.error('Notification error:', error);
        });
}

function updateNotificationsUI(data) {
    // Update notification badge
    if (data.unreadCount > 0) {
        notificationBadge.style.display = 'block';
        notificationBadge.textContent = data.unreadCount;
    } else {
        notificationBadge.style.display = 'none';
    }

    // Update notification list
    if (!data.notifications || data.notifications.length === 0) {
        notificationList.innerHTML = '<div class="no-notifications">No notifications</div>';
        return;
    }

    notificationList.innerHTML = data.notifications.map(notif => `
        <div class="notification-item ${!notif.is_read ? 'unread' : ''}" 
             data-id="${notif.id}"
             onclick="markAsRead(${notif.id})">
            <div class="notification-icon">
                <i class="fas ${getNotificationIcon(notif.type)}"></i>
            </div>
            <div class="notification-content">
                <p>${notif.message}</p>
                ${notif.car_model ? `<small>Car: ${notif.car_model}</small><br>` : ''}
                ${notif.booking_dates ? `<small>${notif.booking_dates}</small><br>` : ''}
                <small class="notification-date">${notif.created_at}</small>
            </div>
        </div>
    `).join('');
}

function getNotificationIcon(type) {
    switch (type) {
        case 'booking_approved':
            return 'fa-check-circle';
        case 'booking_rejected':
            return 'fa-times-circle';
        case 'booking_completed':
            return 'fa-flag-checkered';
        case 'booking_cancelled':
            return 'fa-ban';
        default:
            return 'fa-bell';
    }
}

notificationBell.addEventListener('click', (e) => {
    e.stopPropagation();
    const isVisible = notificationDropdown.style.display === 'block';
    notificationDropdown.style.display = isVisible ? 'none' : 'block';
    
    if (!isNotificationsFetched || !isVisible) {
        fetchNotifications();
        isNotificationsFetched = true;
    }
});

document.addEventListener('click', (e) => {
    if (!notificationDropdown.contains(e.target) && e.target !== notificationBell) {
        notificationDropdown.style.display = 'none';
    }
});

function markAsRead(notificationId) {
    fetch('../includes/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchNotifications(); // Refresh notifications
        }
    });
}

function markAllAsRead() {
    fetch('../includes/mark_all_notifications_read.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchNotifications(); // Refresh notifications
        }
    });
}

// Fetch notifications every 30 seconds
setInterval(fetchNotifications, 30000);

// Initial fetch
fetchNotifications();
</script>