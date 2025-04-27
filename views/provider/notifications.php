<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Your Notifications</h2>
                <?php if (!empty($notifications) && $unreadCount > 0): ?>
                    <form method="POST" action="<?= base_url('index.php/notification/markAsRead') ?>">
                        <input type="hidden" name="mark_all" value="1">
                        <button type="submit" class="btn btn-outline-primary">
                            Mark All as Read <span class="badge bg-primary"><?= $unreadCount ?></span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <?php if (empty($notifications)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-bell-slash me-2"></i> You don't have any notifications at this time.
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="list-group-item <?= $notification['is_read'] ? '' : 'list-group-item-light' ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <h5 class="mb-1">
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="badge bg-primary me-2">New</span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($notification['title'] ?? 'Notification') ?>
                                    </h5>
                                    <small class="text-muted">
                                        <?= date('M d, Y g:i A', strtotime($notification['created_at'])) ?>
                                    </small>
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
                                    
                                    <?php if (!$notification['is_read']): ?>
                                        <form method="POST" action="<?= base_url('index.php/notification/markAsRead') ?>">
                                            <input type="hidden" name="notification_id" value="<?= $notification['notification_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                Mark as Read
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php if (count($notifications) > 10): ?>
                    <div class="d-flex justify-content-center mt-3">
                        <nav aria-label="Notification pagination">
                            <ul class="pagination">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                </li>
                                <li class="page-item active" aria-current="page">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Email Notification Settings -->
            <div class="card mt-4 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Notification Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('index.php/provider/updateNotificationSettings') ?>">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="emailNotifications" name="email_notifications" checked>
                            <label class="form-check-label" for="emailNotifications">
                                Receive email notifications
                            </label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="appointmentReminders" name="appointment_reminders" checked>
                            <label class="form-check-label" for="appointmentReminders">
                                Appointment reminders
                            </label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="systemUpdates" name="system_updates" checked>
                            <label class="form-check-label" for="systemUpdates">
                                System updates and announcements
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Notification Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendEmailModalLabel">Send Email Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="emailForm">
                <div class="modal-body">
                    <input type="hidden" id="appointment_id" name="appointment_id">
                    <div class="mb-3">
                        <label for="email_type" class="form-label">Notification Type</label>
                        <select class="form-select" id="email_type" name="email_type" required>
                            <option value="confirmation">Appointment Confirmation</option>
                            <option value="cancellation">Appointment Cancellation</option>
                            <option value="reminder">Appointment Reminder</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle email form submission
    const emailForm = document.getElementById('emailForm');
    if (emailForm) {
        emailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(emailForm);
            
            fetch('<?= base_url('index.php/provider/sendEmailNotification') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Email notification sent successfully!');
                    $('#sendEmailModal').modal('hide');
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the notification.');
            });
        });
    }
    
    // Initialize email modal with appointment data
    const sendEmailModal = document.getElementById('sendEmailModal');
    if (sendEmailModal) {
        sendEmailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const appointmentId = button.getAttribute('data-appointment-id');
            document.getElementById('appointment_id').value = appointmentId;
        });
    }
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
