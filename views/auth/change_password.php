<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php else: ?>
                        <?php if (isset($_SESSION['temp_user_id'])): ?>
                            <!-- First-time login with temp password -->
                            <div class="alert alert-info">
                                Your account was created with a temporary password. Please set a new password to continue.
                            </div>
                            
                            <form method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">
                                        Password must be at least 8 characters long and include uppercase letters, lowercase letters, numbers, and special characters.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Set Password</button>
                                </div>
                            </form>
                        
                        <?php else: ?>
                            <!-- Regular user password change - requires verification -->
                            <form method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">
                                        Password must be at least 8 characters long and include uppercase letters, lowercase letters, numbers, and special characters.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                    <a href="<?= base_url('index.php/patient/profile') ?>" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                            
                            <div class="mt-3 text-center">
                                <a href="<?= base_url('index.php/auth/forgot_password') ?>">Forgot your password?</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>