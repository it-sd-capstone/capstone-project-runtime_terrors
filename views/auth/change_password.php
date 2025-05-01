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
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['temp_user_id'])): ?>
                        <div class="alert alert-info">
                            Your account was created with a temporary password. Please set a new password to continue.
                        </div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">
                                Password must be at least 8 characters long and include uppercase letters, lowercase letters, and numbers.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                            <?php if (!isset($_SESSION['temp_user_id'])): ?>
                                <a href="<?= base_url('index.php/dashboard') ?>" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>