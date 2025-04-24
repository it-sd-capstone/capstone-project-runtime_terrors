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
    
        if (!$stmt->execute([$user_id, $appointment_id, $subject, $message, $type, $scheduled_for])) {
            error_log("Notification creation failed: " . implode(" | ", $stmt->errorInfo()));
            return false;
        }
    
        return true;
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
            if ($this->sendNotificationEmail($notification['user_id'], $notification['subject'], $notification['message'])) {
                $this->markNotificationSent($notification['notification_id']);
            }
        }
    }
    
    // Add a method to send notifications via email
    private function sendNotificationEmail($user_id, $subject, $message) {
        $user_email = $this->getUserEmail($user_id); // Fetch email dynamically
        if (!$user_email) {
            return false;
        }
    
        // Simulate sending email (Replace with actual email sending logic)
        mail($user_email, $subject, $message);
        return true;
    }
    
    // Get user email dynamically
    private function getUserEmail($user_id) {
        $stmt = $this->db->prepare("SELECT email FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }
    

    // Mark a notification as sent
    public function markNotificationSent($notification_id) {
        $stmt = $this->db->prepare("
            UPDATE notifications SET status = 'sent', sent_at = NOW() WHERE notification_id = ?
        ");
        return $stmt->execute([$notification_id]);
    }

    // Retrieve notifications for a user
    public function getUserNotifications($user_id, $limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>