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
    <!-- Added Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
        /* Fixed styling for password toggle button */
        .password-toggle {
            background: none;
            border: none;
            outline: none !important;
            box-shadow: none !important;
        }
        .password-toggle:hover, .password-toggle:focus {
            background: none !important;
            border: none !important;
        }
        .input-group-text {
            background-color: transparent;
            border-left: none;
        }
        /* Add styling for register section */
        .register-section {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
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
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success mt-3"><?= $_SESSION['success_message'] ?></div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger mt-3"><?= $_SESSION['error_message'] ?></div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($resent) && $resent): ?>
                    <div class="alert alert-success mt-3">
                        A new verification email has been sent. Please check your inbox.
                    </div>
                <?php endif; ?>                
                <form action="<?= base_url('index.php/auth/login') ?>" method="post" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <!-- Fixed password toggle button -->
                            <span class="input-group-text">
                                <button class="password-toggle" type="button" onclick="togglePasswordVisibility()">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </span>
                        </div>
                        <small class="text-muted">For demo purposes, use "demo" or "password" as the password</small>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <div class="mb-3 text-center">
                        <a href="<?= base_url('index.php/auth/forgot_password') ?>">Forgot your password?</a>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </div>
                    
                    <!-- Added Register Section -->
                    <div class="register-section">
                        <p>Don't have an account?</p>
                        <a href="<?= base_url('index.php/auth/register') ?>" class="btn btn-outline-primary">Create New Account</a>
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
    <!-- Add password toggle function -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>
