<?php
// Prevent direct access to view files
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .reset-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .reset-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container reset-container">
        <div class="card reset-card">
            <div class="card-header bg-primary text-white text-center py-3">
                <h3>Reset Your Password</h3>
            </div>
            <div class="card-body p-4">
                <?php 
                // Get all flash messages for the global context
                $flash_messages = get_flash_messages('global');

                // Display flash messages, sorted by type
                if (!empty($flash_messages)): 
                    foreach ($flash_messages as $flash):
                        $alert_class = match($flash['type']) {
                            'success' => 'alert-success',
                            'error' => 'alert-danger',
                            'warning' => 'alert-warning',
                            default => 'alert-info'
                        };
                ?>
                        <div class="alert <?= $alert_class ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flash['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                <?php 
                    endforeach;
                endif; 
                ?>

                <!-- Keep your existing error handling for backward compatibility -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mt-3">
                        <?php foreach ($errors as $err): ?>
                            <div><?= $err ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <div class="text-center mt-3">
                        <a href="<?= base_url('index.php/auth') ?>" class="btn btn-primary">Go to Login</a>
                    </div>
                <?php else: ?>
                    <form action="<?= base_url('index.php/auth/reset_password_process?token=' . htmlspecialchars($token ?? '')) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Password must be at least 8 characters and include uppercase, lowercase, 
                                number, and special character.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Reset Password</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
    </script>
</body>
</html>
