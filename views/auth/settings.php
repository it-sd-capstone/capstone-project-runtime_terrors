<?php
// Prevent direct access to view files
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}

// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: /appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .settings-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container settings-container">
        <h2 class="text-center mb-4">Account Settings</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <!-- Profile Information -->
        <div class="card settings-card">
            <div class="card-header bg-primary text-white">
                <h4 class="m-0">Profile Information</h4>
            </div>
            <div class="card-body p-4">
                <form action="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth/settings" method="post">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($userData['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($userData['last_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" readonly>
                        <div class="form-text">Email cannot be changed. Contact support for assistance.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="card settings-card">
            <div class="card-header bg-secondary text-white">
                <h4 class="m-0">Change Password</h4>
            </div>
            <div class="card-body p-4">
                <form action="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth/settings" method="post">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">
                            Password must be at least 8 characters and include uppercase, lowercase, 
                            number, and special character.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">Update Password</button>
                </form>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-between mt-4">
            <?php if ($_SESSION['role'] === 'patient'): ?>
                <a href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/appointments" class="btn btn-outline-primary">My Appointments</a>
            <?php elseif ($_SESSION['role'] === 'provider'): ?>
                <a href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/provider" class="btn btn-outline-primary">Provider Dashboard</a>
            <?php elseif ($_SESSION['role'] === 'admin'): ?>
                <a href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/admin" class="btn btn-outline-primary">Admin Dashboard</a>
            <?php endif; ?>
            <a href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth/logout" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>