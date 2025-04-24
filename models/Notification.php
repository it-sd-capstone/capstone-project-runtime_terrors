<?php
class Notification {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Store a new notification
    public function createNotification($user_id, $appointment_id, $subject, $message, $type, $scheduled_for = null) {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, appointment_id, subject, message, type, status, scheduled_for) 
            VALUES (?, ?, ?, ?, ?, 'pending', ?)
        ");
        return $stmt->execute([$user_id, $appointment_id, $subject, $message, $type, $scheduled_for]);
    }

    // Retrieve pending notifications for processing
    public function getPendingNotifications() {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications WHERE status = 'pending' AND scheduled_for <= NOW()
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function sendNotifications() {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications WHERE status = 'pending' AND scheduled_for <= NOW()
        ");
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        foreach ($notifications as $notification) {
            // Simulate sending notification
            $this->markNotificationSent($notification['notification_id']);
        }
    }
    

    // Mark a notification as sent
    public function markNotificationSent($notification_id) {
        $stmt = $this->db->prepare("
            UPDATE notifications SET status = 'sent', sent_at = NOW() WHERE notification_id = ?
        ");
        return $stmt->execute([$notification_id]);
    }

    // Retrieve notifications for a user
    public function getUserNotifications($user_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>