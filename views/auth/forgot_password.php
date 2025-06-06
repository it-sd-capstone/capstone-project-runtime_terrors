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
    <title>Forgot Password - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .forgot-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .forgot-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container forgot-container">
        <div class="card forgot-card">
            <div class="card-header bg-primary text-white text-center py-3">
                <h3>Forgot Password</h3>
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
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php else: ?>
                    <p class="mb-3">Enter your email address and we'll send you a link to reset your password.</p>
                    <form method="POST" action="<?= base_url('index.php/auth/forgot_password_process') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Send Reset Link</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center py-3">
                <a href="<?= base_url('index.php/auth') ?>">Back to Login</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
