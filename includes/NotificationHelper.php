<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationHelper {
    private $pdo;
    private $mailer;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->initializeMailer();
    }

    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['SMTP_USERNAME'] ?? '';
        $this->mailer->Password = $_ENV['SMTP_PASSWORD'] ?? '';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['SMTP_PORT'] ?? 587;
        $this->mailer->setFrom($_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@quicklease.com', 'QuickLease');
    }

    /**
     * Notify client about booking approval
     */
    public function notifyBookingApproval($bookingId) {
        try {
            // Get booking details
            $stmt = $this->pdo->prepare("
                SELECT 
                    b.*,
                    c.model as car_model,
                    u.users_id,
                    DATE_FORMAT(b.booking_date, '%M %d, %Y') as formatted_booking_date,
                    DATE_FORMAT(b.return_date, '%M %d, %Y') as formatted_return_date
                FROM bookings b
                JOIN car c ON b.car_id = c.id
                JOIN users u ON b.users_id = u.users_id
                WHERE b.id = ?
            ");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception("Booking not found");
            }

            // Create notification message
            $message = "Your booking for {$booking['car_model']} has been approved! Ready for pickup on {$booking['formatted_booking_date']}.";
            
            // Add notification to database
            $this->addNotification($booking['users_id'], $message);

            return true;
        } catch (Exception $e) {
            error_log("Error in notifyBookingApproval: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify client about return date reminder
     */
    public function notifyReturnReminder($bookingId) {
        try {
            // Get booking details
            $stmt = $this->pdo->prepare("
                SELECT 
                    b.*,
                    c.model as car_model,
                    u.users_id,
                    DATE_FORMAT(b.return_date, '%M %d, %Y') as formatted_return_date
                FROM bookings b
                JOIN car c ON b.car_id = c.id
                JOIN users u ON b.users_id = u.users_id
                WHERE b.id = ?
            ");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception("Booking not found");
            }

            // Create reminder message
            $message = "Reminder: Your rental of {$booking['car_model']} is due for return on {$booking['formatted_return_date']}.";
            
            // Add notification to database
            $this->addNotification($booking['users_id'], $message);

            return true;
        } catch (Exception $e) {
            error_log("Error in notifyReturnReminder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check and send return date reminders for all upcoming returns
     */
    public function checkAndSendReturnReminders() {
        try {
            // Get bookings with return dates coming up in the next 24 hours
            $stmt = $this->pdo->prepare("
                SELECT id 
                FROM bookings 
                WHERE return_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
                AND status = 'active'
                AND reminder_sent = 0
            ");
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($bookings as $booking) {
                $this->notifyReturnReminder($booking['id']);
                
                // Mark reminder as sent
                $updateStmt = $this->pdo->prepare("
                    UPDATE bookings 
                    SET reminder_sent = 1 
                    WHERE id = ?
                ");
                $updateStmt->execute([$booking['id']]);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error in checkAndSendReturnReminders: " . $e->getMessage());
            return false;
        }
    }

    public function getUnreadNotifications($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markNotificationAsRead($notificationId) {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$notificationId]);
    }

    /**
     * Add a new notification
     * @param string $users_id - The ID of the user to notify
     * @param string $message - The notification message
     * @return bool - Success status
     */
    public function addNotification($users_id, $message) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO notifications (users_id, message, is_read, created_at)
                VALUES (?, ?, 0, CURRENT_TIMESTAMP)
            ");
            return $stmt->execute([$users_id, $message]);
        } catch (PDOException $e) {
            error_log("Error adding notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark a notification as read
     * @param int $notification_id - The ID of the notification
     * @param string $users_id - The user ID for security check
     * @return bool - Success status
     */
    public function markAsRead($notification_id, $users_id) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE id = ? AND users_id = ?
            ");
            return $stmt->execute([$notification_id, $users_id]);
        } catch (PDOException $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all notifications as read for a user
     * @param string $users_id - The user ID
     * @return bool - Success status
     */
    public function markAllAsRead($users_id) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE users_id = ?
            ");
            return $stmt->execute([$users_id]);
        } catch (PDOException $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get notifications for a user
     * @param string $users_id - The user ID
     * @param int $limit - Maximum number of notifications to return
     * @return array - Array of notifications
     */
    public function getNotifications($users_id, $limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    id,
                    message,
                    is_read,
                    created_at,
                    DATE_FORMAT(created_at, '%M %d, %Y %H:%i') as formatted_date
                FROM notifications 
                WHERE users_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$users_id, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unread notification count for a user
     * @param string $users_id - The user ID
     * @return int - Number of unread notifications
     */
    public function getUnreadCount($users_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM notifications 
                WHERE users_id = ? AND is_read = 0
            ");
            $stmt->execute([$users_id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }
} 