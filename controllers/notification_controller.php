<?php
require_once MODEL_PATH . '/Notification.php';

/**
 * NotificationController
 * Handles notification-related requests and operations
 */

class NotificationController {
  private $db;
  private $notificationModel;

  /**
   * Initialize controller with database connection and models
   */
  public function __construct() {
      // Start session if not already started
      if (session_status() === PHP_SESSION_NONE) {
          session_start();
      }
      
      // Get database connection
      $this->db = get_db();
      
      // Initialize Notification model
      $this->notificationModel = new Notification($this->db);
  }

  /**
   * Retrieve and display notifications for a user
   * 
   * @param int $user_id The user ID
   * @return void Loads the notifications view
   */
  public function notifications($user_id) {
      // Authorize access
      if (!$this->canAccessUserNotifications($user_id)) {
          $_SESSION['error'] = "You don't have permission to view these notifications";
          header('Location: ' . base_url('index.php'));
          exit;
      }
      
      $notifications = $this->notificationModel->getUserNotifications($user_id);
      include VIEW_PATH . '/patient/notifications.php';
  }

  /**
   * Send pending notifications (automated task)
   * Can be called from a cron job or task scheduler
   * 
   * @return void
   */
  public function sendPendingNotifications() {
      // For automated tasks, add security check
      $apiKey = $_GET['api_key'] ?? $_POST['api_key'] ?? null;
      if (!$this->isValidApiRequest($apiKey)) {
          header('HTTP/1.1 403 Forbidden');
          echo json_encode(['error' => 'Unauthorized access']);
          exit;
      }
      
      $stats = ['processed' => 0, 'sent' => 0, 'failed' => 0];
      $pendingNotifications = $this->notificationModel->getPendingNotifications();
      $stats['processed'] = count($pendingNotifications);
      
      foreach ($pendingNotifications as $notification) {
          // Here you would call your email/SMS service
          // For now, we're just marking them as sent
          if ($this->notificationModel->markNotificationSent($notification['notification_id'])) {
              $stats['sent']++;
          } else {
              $stats['failed']++;
          }
      }
      
      // Log the activity
      $this->logActivity(
          'send_notifications',
          "Processed {$stats['processed']} notifications. Sent: {$stats['sent']}, Failed: {$stats['failed']}"
      );
      
      // Return result
      header('Content-Type: application/json');
      echo json_encode([
          'success' => true,
          'stats' => $stats
      ]);
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
   * 
   * @return void Outputs JSON response
   */
  public function markAsRead() {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          header('HTTP/1.1 405 Method Not Allowed');
          echo json_encode(['error' => 'Method not allowed']);
          exit;
      }
      
      // Validate session
      if (!isset($_SESSION['user_id'])) {
          header('HTTP/1.1 401 Unauthorized');
          echo json_encode(['error' => 'User not authenticated']);
          exit;
      }
      
      $notificationId = $_POST['notification_id'] ?? null;
      $markAll = isset($_POST['mark_all']) && $_POST['mark_all'] === '1';
      $userId = $_SESSION['user_id'] ?? 0;
      $success = false;
      
      if ($markAll) {
          // Mark all notifications as read for this user
          $success = $this->notificationModel->markAllAsRead($userId);
          
          // Log the activity
          $this->logActivity('mark_all_read', "User marked all notifications as read");
      } elseif ($notificationId) {
          // Mark specific notification as read
          $success = $this->notificationModel->markAsRead($notificationId);
          
          // Log the activity
          $this->logActivity('mark_read', "User marked notification #$notificationId as read");
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
      // Construct a proper subject line based on the type
      $subject = $this->getSubjectFromType($type);
      
      return $this->notificationModel->addNotification([
          'type' => $type,
          'subject' => $subject,
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
      
      // Get IP address for better tracking
      $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
      
      return $this->notificationModel->logActivity($action, $details, $userId, $ipAddress);
  }
  
  /**
   * Check if the current user can access notifications for a specific user
   * 
   * @param int $user_id The user ID to check
   * @return bool True if access is allowed
   */
  private function canAccessUserNotifications($user_id) {
      // Admin can access all notifications
      if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
          return true;
      }
      
      // Users can only access their own notifications
      return isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id;
  }
  
  /**
   * Validate API requests for automated tasks
   * 
   * @param string $apiKey API key from request
   * @return bool True if request is valid
   */
  private function isValidApiRequest($apiKey) {
      // In production, use a secure API key validation mechanism
      // For this example, we're using a simple check
      $validApiKey = getenv('API_KEY') ?: 'your-secure-api-key-here';
      
      return $apiKey === $validApiKey;
  }
  
  /**
   * Generate a subject line from notification type
   * 
   * @param string $type Notification type
   * @return string Subject line
   */
  private function getSubjectFromType($type) {
      $subjects = [
          'appointment_created' => 'New Appointment Created',
          'appointment_confirmed' => 'Appointment Confirmed',
          'appointment_completed' => 'Appointment Completed',
          'appointment_canceled' => 'Appointment Canceled',
          'user_registered' => 'New User Registration',
          'system_warning' => 'System Warning',
          'system_error' => 'System Error',
          'security_alert' => 'Security Alert'
      ];
      
      return $subjects[$type] ?? 'System Notification';
  }
  
  /**
   * Display and manage notification settings for the current user
   */
  public function settings() {
      // Check if user is logged in
      if (!isset($_SESSION['user_id'])) {
          $_SESSION['error'] = "You must be logged in to manage notification settings";
          header('Location: ' . base_url('index.php/auth'));
          exit;
      }
      
      $user_id = $_SESSION['user_id'];
      
      // Handle form submission to update settings
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
          $smsNotifications = isset($_POST['sms_notifications']) ? 1 : 0;
          $appointmentReminders = isset($_POST['appointment_reminders']) ? 1 : 0;
          $systemUpdates = isset($_POST['system_updates']) ? 1 : 0;
          $reminderTime = isset($_POST['reminder_time']) ? intval($_POST['reminder_time']) : 24;
          
          // Update notification preferences in the database
          $success = $this->notificationModel->updateNotificationPreferences(
              $user_id, 
              $emailNotifications, 
              $smsNotifications, 
              $appointmentReminders, 
              $systemUpdates,
              $reminderTime
          );
          
          if ($success) {
              $_SESSION['success'] = "Notification settings updated successfully";
          } else {
              $_SESSION['error'] = "Failed to update notification settings";
          }
      }
      
      // Get current notification settings
      $settings = $this->notificationModel->getNotificationPreferences($user_id);
      
      // Include the view
      include VIEW_PATH . '/patient/notification_settings.php';
  }
}
?>