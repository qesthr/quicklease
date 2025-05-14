<?php
class NotificationHandler {
    private $pdo;
    private $user_id;
    private $role;
    private $websocket_client;

    public function __construct($pdo, $user_id, $role) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
        $this->role = $role;
    }

    public function createNotification($message) {
        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (users_id, message)
            VALUES (?, ?)
        ");
        return $stmt->execute([$this->user_id, $message]);
    }

    public function getUnreadCount() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE users_id = ? 
                AND is_read = 0
            ");
            $stmt->execute([$this->user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (PDOException $e) {
            // If table doesn't exist, create it
            if ($e->getCode() == '42S02') {
                $this->createNotificationsTable();
                return 0;
            }
            throw $e;
        }
    }

    private function createNotificationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS notifications (
            id int(11) NOT NULL AUTO_INCREMENT,
            users_id varchar(20) NOT NULL,
            message text NOT NULL,
            is_read tinyint(1) DEFAULT 0,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (id),
            KEY users_id (users_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $this->pdo->exec($sql);
    }

    public function getNotifications($limit = 10, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT * 
            FROM notifications 
            WHERE users_id = ? 
            ORDER BY 
                is_read ASC,
                created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindParam(1, $this->user_id);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->bindParam(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($notification_id) {
        $stmt = $this->pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE id = ? AND users_id = ?
        ");
        return $stmt->execute([$notification_id, $this->user_id]);
    }

    public function markAllAsRead() {
        $stmt = $this->pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE users_id = ?
        ");
        return $stmt->execute([$this->user_id]);
    }

    public function deleteOldNotifications() {
        // You can implement cleanup logic here if needed
        return true;
    }
} 