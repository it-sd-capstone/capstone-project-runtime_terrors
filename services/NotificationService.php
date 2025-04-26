<?php
/**
 * Notification Service
 * Handles sending notifications through different channels (email, etc.)
 */
class NotificationService {
    private $db;
    private $notificationModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->notificationModel = new Notification($db);
    }
    
    /**
     * Process and send all pending notifications
     * 
     * @return array Result statistics
     */
    public function processPendingNotifications() {
        $stats = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0
        ];
        
        // Get all pending notifications
        $notifications = $this->notificationModel->getPendingNotifications();
        $stats['total'] = count($notifications);
        
        foreach ($notifications as $notification) {
            $success = $this->sendNotificationEmail(
                $notification['user_id'],
                $notification['subject'],
                $notification['message']
            );
            
            if ($success) {
                $this->notificationModel->markNotificationSent($notification['notification_id']);
                $stats['sent']++;
            } else {
                $stats['failed']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Send a notification via email
     * 
     * @param int $userId Recipient user ID
     * @param string $subject Email subject
     * @param string $message Email message
     * @return bool Success status
     */
    public function sendNotificationEmail($userId, $subject, $message) {
        $userEmail = $this->notificationModel->getUserEmail($userId);
        
        if (!$userEmail) {
            error_log("Failed to send email: No email address found for user ID $userId");
            return false;
        }
        
        // Here you would integrate with your email service
        // For example, PHPMailer, SendGrid, or the built-in mail() function
        
        // Basic implementation with mail() function:
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: noreply@example.com' . "\r\n";
        
        $success = mail($userEmail, $subject, $message, $headers);
        
        if (!$success) {
            error_log("Failed to send email to $userEmail");
        }
        
        return $success;
    }
    
    /**
     * Create a system notification
     * 
     * @param string $subject Notification subject
     * @param string $message Notification message
     * @param string $type Notification type
     * @param string $audience Target audience (optional)
     * @return bool Success status
     */
    public function createSystemNotification($subject, $message, $type = 'system', $audience = null) {
        return $this->notificationModel->addNotification([
            'subject' => $subject,
            'message' => $message,
            'type' => $type,
            'is_system' => true,
            'audience' => $audience
        ]);
    }
}
?>