<?php
class NotificationController {
  private $db;
  private $notificationModel;

  public function __construct() {
      $this->db = Database::getInstance()->getConnection();
      $this->notificationModel = new Notification($this->db);
  }

  // Retrieve notifications for a user
  public function notifications($user_id) {
      $notifications = $this->notificationModel->getUserNotifications($user_id);
      include VIEW_PATH . '/patient/notifications.php';
  }

  // Send pending notifications (automated task)
  public function sendPendingNotifications() {
      $pendingNotifications = $this->notificationModel->getPendingNotifications();

      foreach ($pendingNotifications as $notification) {
          // Simulated sending logic (email, SMS, etc.)
          $this->notificationModel->markNotificationSent($notification['notification_id']);
      }
  }
}
?>