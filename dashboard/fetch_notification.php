<?php
require_once '../db.php'; // Include database connection

// Set the correct content type for JSON response
header('Content-Type: application/json');

// Start session to get the logged-in customer's ID
session_start();

// Debugging: Check if the session is working
if (!isset($_SESSION['customer_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Customer ID not found in session. Debug: ' . print_r($_SESSION, true)
    ]);
    exit;
}

// Create notifications table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    role ENUM('admin', 'user'),
    message TEXT,
    is_read BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
$pdo->exec($createTable);

// Function to fetch notifications with improved filtering and sorting
function fetchNotifications($pdo, $user_id, $role, $limit = 10, $offset = 0, $type = null) {
    $params = [$user_id, $role];
    $typeCondition = '';
    
    if ($type) {
        $typeCondition = "AND type = ?";
        $params[] = $type;
    }

    $query = "
        SELECT 
            n.*,
            CASE 
                WHEN n.type = 'booking_request' AND n.reference_id IS NOT NULL THEN
                    (SELECT CONCAT(u.firstname, ' ', u.lastname) 
                     FROM bookings b 
                     JOIN users u ON b.users_id = u.id 
                     WHERE b.id = n.reference_id)
                WHEN n.type = 'car_added' AND n.reference_id IS NOT NULL THEN
                    (SELECT model FROM car WHERE id = n.reference_id)
                ELSE NULL
            END as reference_details
        FROM notifications n
        WHERE n.user_id = ? 
        AND n.role = ?
        $typeCondition
        AND (n.expires_at IS NULL OR n.expires_at > NOW())
        ORDER BY 
            n.is_read ASC,
            CASE n.priority
                WHEN 'high' THEN 1
                WHEN 'medium' THEN 2
                WHEN 'low' THEN 3
            END,
            n.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to count unread notifications with type filtering
function countUnread($pdo, $user_id, $role, $type = null) {
    $params = [$user_id, $role];
    $typeCondition = '';
    
    if ($type) {
        $typeCondition = "AND type = ?";
        $params[] = $type;
    }

    $query = "
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? 
        AND role = ? 
        AND is_read = 0
        $typeCondition
        AND (expires_at IS NULL OR expires_at > NOW())
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Function to mark notification as read
function markAsRead($pdo, $notification_id, $user_id) {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND user_id = ?
    ");
    return $stmt->execute([$notification_id, $user_id]);
}

// Handle different actions
$action = $_POST['action'] ?? '';
$user_id = $_POST['user_id'] ?? 0;
$role = $_POST['role'] ?? '';
$type = $_POST['type'] ?? null;
$page = max(0, intval($_POST['page'] ?? 0));
$limit = 10;
$offset = $page * $limit;

try {
    switch($action) {
        case 'fetch':
            $notifications = fetchNotifications($pdo, $user_id, $role, $limit, $offset, $type);
            $unreadCount = countUnread($pdo, $user_id, $role, $type);
            
            // Group notifications by date
            $grouped = [];
            foreach ($notifications as $notification) {
                $date = date('Y-m-d', strtotime($notification['created_at']));
                if (!isset($grouped[$date])) {
                    $grouped[$date] = [];
                }
                $grouped[$date][] = $notification;
            }
            
            echo json_encode([
                'status' => 'success',
                'notifications' => $grouped,
                'unread_count' => $unreadCount,
                'has_more' => count($notifications) === $limit
            ]);
            break;

        case 'mark_read':
            $notification_id = $_POST['notification_id'] ?? 0;
            $success = markAsRead($pdo, $notification_id, $user_id);
            echo json_encode([
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Marked as read' : 'Failed to mark as read'
            ]);
            break;

        case 'mark_all_read':
            $typeCondition = $type ? "AND type = ?" : "";
            $params = [$user_id, $role];
            if ($type) {
                $params[] = $type;
            }
            
            $stmt = $pdo->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = ? 
                AND role = ?
                $typeCondition
            ");
            $success = $stmt->execute($params);
            
            echo json_encode([
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'All notifications marked as read' : 'Failed to mark notifications as read'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>