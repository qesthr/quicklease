<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/NotificationHelper.php';

try {
    $notificationHelper = new NotificationHelper($pdo);
    $notificationHelper->checkAndSendReturnReminders();
    echo "Return date reminders checked and sent successfully.\n";
} catch (Exception $e) {
    echo "Error checking return dates: " . $e->getMessage() . "\n";
}
?> 