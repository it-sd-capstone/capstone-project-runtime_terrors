<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Notification Settings</h4>
                </div>
                <div class="card-body">
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
                    
                    <form method="POST" action="<?= base_url('index.php/notification/settings') ?>">
                        <div class="mb-4">
                            <h5>Notification Methods</h5>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" 
                                       <?= isset($settings['email_notifications']) && $settings['email_notifications'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="email_notifications">
                                    Email Notifications
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications"
                                       <?= isset($settings['sms_notifications']) && $settings['sms_notifications'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sms_notifications">
                                    SMS Notifications
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Notification Types</h5>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="appointment_reminders" name="appointment_reminders"
                                       <?= isset($settings['appointment_reminders']) && $settings['appointment_reminders'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="appointment_reminders">
                                    Appointment Reminders
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="system_updates" name="system_updates"
                                       <?= isset($settings['system_updates']) && $settings['system_updates'] ? 'checked' : '' ?>>
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
                                    <option value="24" <?= isset($settings['reminder_time']) && $settings['reminder_time'] == 24 ? 'selected' : '' ?>>24 hours before</option>
                                    <option value="48" <?= isset($settings['reminder_time']) && $settings['reminder_time'] == 48 ? 'selected' : '' ?>>48 hours before</option>
                                    <option value="72" <?= isset($settings['reminder_time']) && $settings['reminder_time'] == 72 ? 'selected' : '' ?>>72 hours before</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>