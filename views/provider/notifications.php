<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Notification Settings -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Notification Settings</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('index.php/provider/updateNotificationSettings') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-4">
                            <h5>Notification Methods</h5>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications"
                                    <?= isset($notificationSettings['email_notifications']) && $notificationSettings['email_notifications'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="email_notifications">
                                    Email Notifications
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications"
                                    <?= isset($notificationSettings['sms_notifications']) && $notificationSettings['sms_notifications'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sms_notifications">
                                    SMS Notifications
                                </label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h5>Notification Types</h5>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="appointment_reminders" name="appointment_reminders"
                                    <?= isset($notificationSettings['appointment_reminders']) && $notificationSettings['appointment_reminders'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="appointment_reminders">
                                    Appointment Reminders
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="system_updates" name="system_updates"
                                    <?= isset($notificationSettings['system_updates']) && $notificationSettings['system_updates'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="system_updates">
                                    System Updates
                                </label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h5>Reminder Preferences</h5>
                            <div class="mb-3">
                                <label for="reminder_time" class="form-label">Send appointment reminders</label>
                                <select class="form-select" id="reminder_time" name="reminder_time">
                                    <option value="24" <?= isset($notificationSettings['reminder_time']) && $notificationSettings['reminder_time'] == 24 ? 'selected' : '' ?>>24 hours before</option>
                                    <option value="48" <?= isset($notificationSettings['reminder_time']) && $notificationSettings['reminder_time'] == 48 ? 'selected' : '' ?>>48 hours before</option>
                                    <option value="72" <?= isset($notificationSettings['reminder_time']) && $notificationSettings['reminder_time'] == 72 ? 'selected' : '' ?>>72 hours before</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Notifications</h4>
                    <?php if (!empty($notifications)): ?>
                        <button id="markAllRead" class="btn btn-light btn-sm">Mark All as Read</button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($notifications)) : ?>
                        <div class="list-group">
                            <?php foreach ($notifications as $notification) : ?>
                                <?php 
                                    $isRead = $notification['is_read'] ?? false;
                                    $notificationClass = $isRead ? '' : 'list-group-item-primary';
                                    $notificationType = $notification['type'] ?? 'info';
                                    
                                    // Determine icon based on notification type
                                    $icon = 'info-circle';
                                    $iconClass = 'text-info';
                                    
                                    switch($notificationType) {
                                        case 'appointment_reminder':
                                            $icon = 'calendar-check';
                                            $iconClass = 'text-primary';
                                            break;
                                        case 'appointment_confirmed':
                                            $icon = 'check-circle';
                                            $iconClass = 'text-success';
                                            break;
                                        case 'appointment_canceled':
                                            $icon = 'times-circle';
                                            $iconClass = 'text-danger';
                                            break;
                                        case 'system_message':
                                            $icon = 'exclamation-circle';
                                            $iconClass = 'text-warning';
                                            break;
                                    }
                                    
                                    // Format date
                                    $createdAt = new DateTime($notification['created_at']);
                                    $formattedDate = $createdAt->format('M d, Y g:i A');
                                    
                                    // Calculate time ago
                                    $now = new DateTime();
                                    $interval = $createdAt->diff($now);
                                    
                                    if ($interval->d > 0) {
                                        $timeAgo = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                                    } elseif ($interval->h > 0) {
                                        $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                                    } elseif ($interval->i > 0) {
                                        $timeAgo = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
                                    } else {
                                        $timeAgo = 'just now';
                                    }
                                ?>
                                <div class="list-group-item list-group-item-action <?= $notificationClass ?>" 
                                     data-notification-id="<?= $notification['notification_id'] ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">
                                            <i class="fas fa-<?= $icon ?> <?= $iconClass ?> me-2"></i>
                                            <?= htmlspecialchars($notification['title'] ?? 'Notification') ?>
                                        </h5>
                                        <small class="text-muted" title="<?= $formattedDate ?>"><?= $timeAgo ?></small>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <?php if (isset($notification['appointment_id'])): ?>
                                            <a href="<?= base_url('index.php/provider/appointments/view/' . $notification['appointment_id']) ?>" 
                                               class="btn btn-sm btn-outline-info">
                                                View Appointment
                                            </a>
                                        <?php else: ?>
                                            <span></span>
                                        <?php endif; ?>
                                        <?php if (!$isRead): ?>
                                            <button class="btn btn-sm btn-outline-primary mark-read" 
                                                    data-id="<?= $notification['notification_id'] ?>">
                                                Mark as Read
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            <i class="fas fa-bell-slash me-2"></i>
                            <span>You have no new notifications.</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($notifications)): ?>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Showing <?= count($notifications) ?> notification(s)</small>
                            <a href="#settings" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-cog me-1"></i> Notification Settings
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark individual notification as read
    const markReadButtons = document.querySelectorAll('.mark-read');
    markReadButtons.forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.getAttribute('data-id');
            markAsRead(notificationId, this);
        });
    });

    // Mark all notifications as read
    const markAllButton = document.getElementById('markAllRead');
    if (markAllButton) {
        markAllButton.addEventListener('click', function() {
            markAllAsRead();
        });
    }

    // Function to mark a single notification as read
    function markAsRead(notificationId, buttonElement) {
        fetch('<?= base_url('index.php/notification/markAsRead') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'notification_id=' + notificationId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const notificationItem = buttonElement.closest('.list-group-item');
                notificationItem.classList.remove('list-group-item-primary');
                buttonElement.remove();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }

    // Function to mark all notifications as read
    function markAllAsRead() {
        fetch('<?= base_url('index.php/notification/markAsRead') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'mark_all=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                document.querySelectorAll('.list-group-item-primary').forEach(item => {
                    item.classList.remove('list-group-item-primary');
                });
                document.querySelectorAll('.mark-read').forEach(button => {
                    button.remove();
                });
                // Hide the mark all button
                document.getElementById('markAllRead').style.display = 'none';
            }
        })
        .catch(error => console.error('Error marking all notifications as read:', error));
    }
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
