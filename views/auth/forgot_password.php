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
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php else: ?>
                    <p class="mb-3">Enter your email address and we'll send you a link to reset your password.</p>
                    <form method="POST" action="<?= base_url('index.php/auth/forgot_password') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <input type="email" class="form-control" id="email" name="email" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
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