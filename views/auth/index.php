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
    <title>Login - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .login-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .login-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .demo-login {
            margin-top: 30px;
            padding: 15px;
            border-radius: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card login-card">
            <div class="card-header bg-primary text-white text-center py-3">
                <h3>Login to Your Account</h3>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($errors) && is_array($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Login failed:</strong>
                        <ul class="mb-0">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="<?= base_url('index.php/auth/login') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">For demo purposes, use "demo" or "password" as the password</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
                    
                    <div class="mt-3 text-center">
                        <a href="<?= base_url('index.php/auth/register') ?>">Create an Account</a>
                        <span class="mx-2">|</span>
                        <a href="<?= base_url('index.php/auth/forgot_password') ?>">Forgot Password?</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Quick Role Selector for Demos/Testing -->
        <div class="demo-login">
            <h4 class="text-center mb-3">Quick Login for Testing</h4>
            <div class="d-flex justify-content-between">
                <a href="<?= base_url('index.php/auth/demo?role=patient') ?>" class="btn btn-outline-primary">Login as Patient</a>
                <a href="<?= base_url('index.php/auth/demo?role=provider') ?>" class="btn btn-outline-success">Login as Provider</a>
                <a href="<?= base_url('index.php/auth/demo?role=admin') ?>" class="btn btn-outline-dark">Login as Admin</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
