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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4361ee;
            --light-bg: #f8f9fa;
        }
                
        body {
            background-color: var(--light-bg);
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4ecfa 100%);
            padding-top: 120px; /* Increased from 50px to 120px */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
                
        .login-container {
            max-width: 450px;
            margin: 0 auto;
            margin-top: 30px; /* Added margin-top */
        }
                
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
        }
                
        .card-header {
            padding: 1.5rem 1rem;
        }
                
        .demo-login {
            margin-top: 30px;
            padding: 20px;
            border-radius: 15px;
            background-color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
                
        /* Password toggle styling */
        .password-toggle {
            background: none;
            border: none;
            outline: none !important;
            box-shadow: none !important;
            padding: 0;
            color: #6c757d;
            transition: color 0.2s;
        }
                
        .password-toggle:hover, .password-toggle:focus {
            background: none !important;
            border: none !important;
            color: var(--primary);
        }
                
        .input-group-text {
            background-color: transparent;
            border-left: none;
        }
                
        /* Register section styling */
        .register-section {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
                
        /* Button styling */
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 0.6rem 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }
                
        .btn-primary:hover {
            background-color: #3a56d4;
            border-color: #3a56d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.2);
        }
                
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
                
        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: white;
        }
                
        /* Alert styling */
        .alert {
            border-radius: 10px;
            border: none;
        }
                
        /* Footer styling */
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 0.85rem;
        }
                
        /* Form control styling */
        .form-control:focus {
            border-color: #a3b9ff;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card login-card">
            <div class="card-header bg-primary text-white text-center py-3">
                <h3 class="mb-0">Login to Your Account</h3>
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
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mt-3">
                        <?php foreach ($errors as $err): ?>
                            <div><?= $err ?></div>
                        <?php endforeach; ?>
                    </div>
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
                        <input type="email" class="form-control" id="email" name="email" required
                               placeholder="Enter your email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required
                                   placeholder="Enter your password">
                            <span class="input-group-text">
                                <button class="password-toggle" type="button" onclick="togglePasswordVisibility()">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </span>
                        </div>
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
                    
                    <!-- Register Section -->
                    <div class="register-section">
                        <p class="mb-2">Don't have an account?</p>
                        <a href="<?= base_url('index.php/auth/register') ?>" class="btn btn-outline-primary">Create New Account</a>
                    </div>
                    <div class="text-center mb-3">
                        <br><a href="<?= base_url('index.php/home') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-house-door me-1"></i> Back to Home
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Quick Role Selector for Demos/Testing -->
        <div class="demo-login">
            <h5 class="text-center mb-3">Quick Login for Testing</h5>
            <div class="d-flex justify-content-between">
                <a href="<?= base_url('index.php/auth/demo?role=patient') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-person me-1"></i> Patient
                </a>
                <a href="<?= base_url('index.php/auth/demo?role=provider') ?>" class="btn btn-outline-success">
                    <i class="bi bi-clipboard-pulse me-1"></i> Provider
                </a>
                <a href="<?= base_url('index.php/auth/demo?role=admin') ?>" class="btn btn-outline-dark">
                    <i class="bi bi-shield-lock me-1"></i> Admin
                </a>
            </div>
            <div class="mt-3 text-center text-muted small">
                <p class="mb-2">For demo purposes:</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="1" class="align-middle bg-light">Admin</td>
                                <td><code>admin@example.com</code></td>
                                <td><code>Admin123@</code></td>
                            </tr>
                            <tr>
                                <td rowspan="3" class="align-middle bg-light">Provider</td>
                                <td><code>provider@example.com</code></td>
                                <td rowspan="3" class="align-middle"><code>Provider123@</code></td>
                            </tr>
                            <tr>
                                <td><code>provider2@example.com</code></td>
                            </tr>
                            <tr>
                                <td><code>provider3@example.com</code></td>
                            </tr>
                            <tr>
                                <td rowspan="3" class="align-middle bg-light">Patient</td>
                                <td><code>patient@example.com</code></td>
                                <td rowspan="3" class="align-middle"><code>Patient123@</code></td>
                            </tr>
                            <tr>
                                <td><code>patient2@example.com</code></td>
                            </tr>
                            <tr>
                                <td><code>patient3@example.com</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer mt-3">
            <p>&copy; <?= date('Y') ?> Appointment System. All rights reserved.</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
    
    // Password toggle function
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