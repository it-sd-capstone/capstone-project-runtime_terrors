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
    <title>Register - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .register-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .register-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="card register-card">
            <div class="card-header bg-primary text-white text-center py-3">
                <h3>Create an Account</h3>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors) && is_array($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Please fix the following errors:</strong>
                        <ul>
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>                
                <?php if(!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php 
                        // Display only the first part of the success message
                        $successText = strstr($success, '<a href=', true);
                        echo $successText ?: $success;
                        
                        // Extract and display the verification link if present
                        if(strpos($success, '<a href=') !== false) {
                            preg_match('/<a href=\'([^\']+)\'>(.*?)<\/a>/', $success, $matches);
                            if(isset($matches[1]) && isset($matches[2])) {
                                echo '<a href="' . $matches[1] . '">' . $matches[2] . '</a> (for demonstration only)';
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Registration Requirements</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>All fields marked with * are required</li>
                            <li>Email address must be valid and not already registered</li>
                            <li>Password must be at least 8 characters long</li>
                            <li>Password must contain at least one uppercase letter</li>
                            <li>Password must contain at least one lowercase letter</li>
                            <li>Password must contain at least one number</li>
                            <li>Password must contain at least one special character</li>
                            <li>You must agree to the Terms of Service</li>
                        </ul>
                    </div>
                </div>
                
                <form action="<?= base_url('index.php/auth/register') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                            value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                            value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                        value="<?= htmlspecialchars($old['phone'] ?? '') ?>" 
                        placeholder="(XXX) XXX-XXXX" 
                        maxlength="14">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">
                            Password must be at least 8 characters and include uppercase, lowercase,
                            number, and special character.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the Terms of Service and Privacy Policy
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Register</button>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                Already have an account? <a href="<?= base_url('index.php/auth') ?>">Log in</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            let formattedValue = '';
            
            if (value.length > 0) {
                // Format: (XXX) 
                formattedValue = '(' + value.substring(0, 3);
                
                if (value.length > 3) {
                    // Format: (XXX) XXX
                    formattedValue += ') ' + value.substring(3, 6);
                    
                    if (value.length > 6) {
                        // Format: (XXX) XXX-XXXX
                        formattedValue += '-' + value.substring(6, 10);
                    }
                }
            }
            
            e.target.value = formattedValue;
        });
        
        // Handle special cases like backspace and delete
        document.getElementById('phone').addEventListener('keydown', function(e) {
            // Allow backspace, delete, tab, escape, enter and navigation keys
            if (e.key === 'Backspace' || e.key === 'Delete') {
                let value = e.target.value;
                let caretPos = e.target.selectionStart;
                
                // If cursor is after a formatting character, move it past the character
                if (
                    (caretPos === 1 && value.charAt(0) === '(') ||
                    (caretPos === 6 && value.charAt(5) === ')') ||
                    (caretPos === 11 && value.charAt(10) === '-')
                ) {
                    e.preventDefault();
                    e.target.setSelectionRange(caretPos - 1, caretPos - 1);
                }
            }
        });
    </script>
</body>
</html>
