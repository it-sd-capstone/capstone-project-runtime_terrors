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
    <title>Verify Email - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --success-color: #4cc9f0;
            --danger-color: #ff4d6d;
        }
        
        body {
            background-color: #f8f9fa;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4ecfa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }
        
        .verify-container {
            max-width: 550px;
            margin: 0 auto;
        }
        
        .verify-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .card-header {
            background: var(--primary-color);
            padding: 25px 20px;
            position: relative;
        }
        
        .card-header::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 20px;
            background: var(--primary-color);
            transform: skewY(-1deg);
            z-index: 0;
        }
        
        .card-body {
            padding: 35px 25px 25px;
            position: relative;
            z-index: 1;
        }
        
        .icon-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .alert {
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
            border: none;
        }
        
        .alert-success {
            background-color: rgba(76, 201, 240, 0.2);
            color: #0a58ca;
        }
        
        .alert-danger {
            background-color: rgba(255, 77, 109, 0.2);
            color: #dc3545;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: #3a56d4;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }
    </style>
</head>
<body>
    <div class="container verify-container">
        <div class="card verify-card">
            <div class="card-header bg-primary text-white text-center py-4">
                <h2 class="mb-0">Email Verification</h2>
            </div>
            <div class="card-body p-4 text-center">
                <?php if (!empty($error)): ?>
                    <div class="icon-circle mb-4">
                        <i class="fas fa-exclamation-triangle text-danger fa-3x"></i>
                    </div>
                    <div class="alert alert-danger">
                        <h4 class="alert-heading mb-2">Verification Failed</h4>
                        <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                    </div>
                    <div class="mt-4">
                        <a href="<?= base_url('index.php/auth/register') ?>" class="btn btn-outline-primary me-2">
                            <i class="fas fa-user-plus me-2"></i>Register Again
                        </a>
                        <a href="<?= base_url('index.php/auth') ?>" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="icon-circle mb-4">
                        <i class="fas fa-check-circle text-success fa-3x"></i>
                    </div>
                    <div class="alert alert-success">
                        <h4 class="alert-heading mb-2">Success!</h4>
                        <p class="mb-0"><?= $success ?></p>
                    </div>
                    <div class="mt-4">
                        <a href="<?= base_url('index.php/auth') ?>" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Your Account
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-center mt-3 text-muted">
            <small>&copy; <?= date('Y') ?> Patient Appointment System. All rights reserved.</small>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
