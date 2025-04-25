<?php
require_once MODEL_PATH . '/Notification.php';

class NotificationController {
  private $db;
  private $notificationModel;

  public function __construct() {
      // Start session if not already started
      if (session_status() === PHP_SESSION_NONE) {
          session_start();
      }
      
      // Get database connection
      $this->db = get_db();
      
      // Initialize Notification model
      require_once MODEL_PATH . '/Notification.php';
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

  /**
   * Get latest system notifications for admin dashboard
   * 
   * @return void Outputs JSON response
   */
  public function getAdminNotifications() {
      error_log("NotificationController::getAdminNotifications called");
      
      // Ensure user is admin
      if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
          error_log("Access denied: User role is " . ($_SESSION['role'] ?? 'not set'));
          header('Content-Type: application/json');
          echo json_encode([
              'error' => 'Unauthorized access',
              'success' => false
          ]);
          exit;
      }
      
      try {
          error_log("Attempting to get latest system notifications");
          
          // Get latest system notifications
          $notifications = $this->notificationModel->getLatestSystemNotifications(10);
          error_log("Retrieved " . count($notifications) . " notifications");
          
          // Format notifications for display
          $formattedNotifications = [];
          foreach ($notifications as $notification) {
              // Debug notification data
              error_log("Processing notification: " . json_encode($notification));
              
              // Determine alert type based on notification type
              $alertType = 'info'; // Default
              
              switch ($notification['type']) {
                  case 'appointment_created':
                  case 'user_registered':
                      $alertType = 'info';
                      break;
                  case 'appointment_confirmed':
                  case 'appointment_completed':
                      $alertType = 'success';
                      break;
                  case 'appointment_canceled':
                  case 'system_warning':
                      $alertType = 'warning';
                      break;
                  case 'system_error':
                  case 'security_alert':
                      $alertType = 'danger';
                      break;
              }
              
              // Calculate time difference
              $created = new DateTime($notification['created_at']);
              $now = new DateTime();
              $diff = $now->diff($created);
              
              if ($diff->d > 0) {
                  $timeAgo = $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
              } elseif ($diff->h > 0) {
                  $timeAgo = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
              } elseif ($diff->i > 0) {
                  $timeAgo = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
              } else {
                  $timeAgo = 'just now';
              }
              
              $formattedNotifications[] = [
                  'id' => $notification['notification_id'],
                  'type' => $alertType,
                  'message' => $notification['message'],
                  'time' => $timeAgo,
                  'is_read' => (bool)$notification['is_read']
              ];
          }
          
          // Debug the results
          error_log("Formatted " . count($formattedNotifications) . " notifications for response");
          
          $unreadCount = $this->notificationModel->getUnreadCount('admin');
          error_log("Unread count: " . $unreadCount);
          
          // Return JSON response
          header('Content-Type: application/json');
          echo json_encode([
              'notifications' => $formattedNotifications,
              'success' => true,
              'total_unread' => $unreadCount
          ]);
          exit;
      } catch (Exception $e) {
          error_log("Error in getAdminNotifications: " . $e->getMessage());
          error_log("Stack trace: " . $e->getTraceAsString());
          
          header('Content-Type: application/json');
          echo json_encode([
              'error' => 'Internal server error: ' . $e->getMessage(),
              'success' => false
          ]);
          exit;
      }
  }
  
  /**
   * Mark notifications as read
   */
  public function markAsRead() {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          header('HTTP/1.1 405 Method Not Allowed');
          exit;
      }
      
      $notificationId = $_POST['notification_id'] ?? null;
      $markAll = isset($_POST['mark_all']) && $_POST['mark_all'] === '1';
      
      if ($markAll) {
          // Mark all notifications as read for this user
          $userId = $_SESSION['user_id'] ?? 0;
          $success = $this->notificationModel->markAllAsRead($userId);
      } elseif ($notificationId) {
          // Mark specific notification as read
          $success = $this->notificationModel->markAsRead($notificationId);
      } else {
          $success = false;
      }
      
      // Return JSON response
      header('Content-Type: application/json');
      echo json_encode(['success' => $success]);
      exit;
  }
  
  /**
   * Add a new system notification
   * 
   * @param string $type Notification type
   * @param string $message Notification message
   * @param int $userId User ID (if applicable)
   * @return bool Success status
   */
  public function addSystemNotification($type, $message, $userId = null) {
      return $this->notificationModel->addNotification([
          'type' => $type,
          'message' => $message,
          'user_id' => $userId,
          'is_system' => true
      ]);
  }
  
  /**
   * Log system activities for audit trail
   * 
   * @param string $action Action performed
   * @param string $details Additional details
   * @param int $userId User who performed the action
   * @return bool Success status
   */
  public function logActivity($action, $details, $userId = null) {
      if (!$userId && isset($_SESSION['user_id'])) {
          $userId = $_SESSION['user_id'];
      }
      
      return $this->notificationModel->logActivity($action, $details, $userId);
  }
}
?>