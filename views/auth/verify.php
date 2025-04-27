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
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .verify-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .verify-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container verify-container">
        <div class="card verify-card">
            <div class="card-header bg-primary text-white text-center py-3">
                <h3>Email Verification</h3>
            </div>
            <div class="card-body p-4 text-center">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <p class="mt-3">
                        The verification link may have expired or is invalid. 
                        Please contact support or try registering again.
                    </p>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <p class="mt-3">
                        Your email has been successfully verified. You can now log in to your account.
                    </p>
                    <div class="mt-4">
                        <a href="<?= base_url('index.php/auth') ?>" class="btn btn-primary">
                            Go to Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>