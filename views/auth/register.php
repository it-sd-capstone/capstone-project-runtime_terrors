<?php
// Prevent direct access to view files
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}

// Check if reCAPTCHA is configured, if not, set default values
if (!defined('RECAPTCHA_SITE_KEY')) {
    define('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');  // Test key
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
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
        .requirement {
            margin-top: 5px;
            font-size: 0.85rem;
        }
        .requirement i {
            margin-right: 5px;
        }
        .valid-requirement {
            color: #198754;
        }
        .invalid-requirement {
            color: #6c757d;
        }
        .is-valid {
            border-color: #198754;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        /* Center the reCAPTCHA */
        .g-recaptcha {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
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
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Fields marked with * are required
                </div>
                
                <form action="<?= base_url('index.php/auth/register') ?>" method="post" id="registerForm">
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
                        <div class="requirement">
                            <span id="email-requirement" class="invalid-requirement">
                                <i class="bi bi-circle"></i> Must be a valid email address
                            </span>
                        </div>
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
                        <div class="mt-2">
                            <div class="requirement">
                                <span id="length-requirement" class="invalid-requirement">
                                    <i class="bi bi-circle"></i> At least 8 characters
                                </span>
                            </div>
                            <div class="requirement">
                                <span id="uppercase-requirement" class="invalid-requirement">
                                    <i class="bi bi-circle"></i> At least one uppercase letter
                                </span>
                            </div>
                            <div class="requirement">
                                <span id="lowercase-requirement" class="invalid-requirement">
                                    <i class="bi bi-circle"></i> At least one lowercase letter
                                </span>
                            </div>
                            <div class="requirement">
                                <span id="number-requirement" class="invalid-requirement">
                                    <i class="bi bi-circle"></i> At least one number
                                </span>
                            </div>
                            <div class="requirement">
                                <span id="special-requirement" class="invalid-requirement">
                                    <i class="bi bi-circle"></i> At least one special character
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="requirement">
                            <span id="match-requirement" class="invalid-requirement">
                                <i class="bi bi-circle"></i> Passwords must match
                            </span>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="<?= base_url('index.php/terms') ?>" target="_blank">Terms of Service</a> 
                            and <a href="<?= base_url('index.php/privacy') ?>" target="_blank">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <!-- Add reCAPTCHA -->
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="g-recaptcha" 
                            data-sitekey="6Leh-TgrAAAAAL6uiA8JcjGfuz75m6ra-V4kIy8f" 
                            data-callback="enableSubmitButton" 
                            data-expired-callback="disableSubmitButton"></div>
                    </div>
                    <div id="recaptcha-error" class="text-danger mt-2" style="display: none;">
                        Please complete the reCAPTCHA verification
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

        // Password validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const email = document.getElementById('email');
        
        // Requirement elements
        const lengthReq = document.getElementById('length-requirement');
        const uppercaseReq = document.getElementById('uppercase-requirement');
        const lowercaseReq = document.getElementById('lowercase-requirement');
        const numberReq = document.getElementById('number-requirement');
        const specialReq = document.getElementById('special-requirement');
        const matchReq = document.getElementById('match-requirement');
        const emailReq = document.getElementById('email-requirement');
        
        // Validation functions
        function validatePassword() {
            const value = password.value;
            
            // Check length
            if (value.length >= 8) {
                lengthReq.classList.remove('invalid-requirement');
                lengthReq.classList.add('valid-requirement');
                lengthReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> At least 8 characters';
            } else {
                lengthReq.classList.remove('valid-requirement');
                lengthReq.classList.add('invalid-requirement');
                lengthReq.innerHTML = '<i class="bi bi-circle"></i> At least 8 characters';
            }
            
            // Check uppercase
            if (/[A-Z]/.test(value)) {
                uppercaseReq.classList.remove('invalid-requirement');
                uppercaseReq.classList.add('valid-requirement');
                uppercaseReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> At least one uppercase letter';
            } else {
                uppercaseReq.classList.remove('valid-requirement');
                uppercaseReq.classList.add('invalid-requirement');
                uppercaseReq.innerHTML = '<i class="bi bi-circle"></i> At least one uppercase letter';
            }
            
            // Check lowercase
            if (/[a-z]/.test(value)) {
                lowercaseReq.classList.remove('invalid-requirement');
                lowercaseReq.classList.add('valid-requirement');
                lowercaseReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> At least one lowercase letter';
            } else {
                lowercaseReq.classList.remove('valid-requirement');
                lowercaseReq.classList.add('invalid-requirement');
                lowercaseReq.innerHTML = '<i class="bi bi-circle"></i> At least one lowercase letter';
            }
            
            // Check number
            if (/[0-9]/.test(value)) {
                numberReq.classList.remove('invalid-requirement');
                numberReq.classList.add('valid-requirement');
                numberReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> At least one number';
            } else {
                numberReq.classList.remove('valid-requirement');
                numberReq.classList.add('invalid-requirement');
                numberReq.innerHTML = '<i class="bi bi-circle"></i> At least one number';
            }
            
            // Check special character
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
                specialReq.classList.remove('invalid-requirement');
                specialReq.classList.add('valid-requirement');
                specialReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> At least one special character';
            } else {
                specialReq.classList.remove('valid-requirement');
                specialReq.classList.add('invalid-requirement');
                specialReq.innerHTML = '<i class="bi bi-circle"></i> At least one special character';
            }
            
            // Check if all requirements are met
            if (value.length >= 8 && 
                /[A-Z]/.test(value) && 
                /[a-z]/.test(value) && 
                /[0-9]/.test(value) && 
                /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
                password.classList.add('is-valid');
            } else {
                password.classList.remove('is-valid');
            }
            
            // Check password match if confirm password has value
            if (confirmPassword.value) {
                validatePasswordMatch();
            }
        }
        
        function validatePasswordMatch() {
            if (password.value === confirmPassword.value && password.value !== '') {
                matchReq.classList.remove('invalid-requirement');
                matchReq.classList.add('valid-requirement');
                matchReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> Passwords match';
                confirmPassword.classList.add('is-valid');
            } else {
                matchReq.classList.remove('valid-requirement');
                matchReq.classList.add('invalid-requirement');
                matchReq.innerHTML = '<i class="bi bi-circle"></i> Passwords must match';
                confirmPassword.classList.remove('is-valid');
            }
        }
        
        function validateEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailRegex.test(email.value)) {
                emailReq.classList.remove('invalid-requirement');
                emailReq.classList.add('valid-requirement');
                emailReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> Valid email address';
                email.classList.add('is-valid');
            } else {
                emailReq.classList.remove('valid-requirement');
                emailReq.classList.add('invalid-requirement');
                emailReq.innerHTML = '<i class="bi bi-circle"></i> Must be a valid email address';
                email.classList.remove('is-valid');
            }
        }
        
        // Add event listeners
        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', validatePasswordMatch);
        email.addEventListener('input', validateEmail);
        
        // Form submission validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            // Check if reCAPTCHA is completed
            const recaptchaResponse = grecaptcha.getResponse();
            if (recaptchaResponse.length === 0) {
                e.preventDefault();
                alert('Please complete the reCAPTCHA verification.');
                return;
            }
            
            // Validate all fields before submitting
            validatePassword();
            validatePasswordMatch();
            validateEmail();
            
            // Check if password meets all requirements
            const passwordValid = password.value.length >= 8 && 
                                 /[A-Z]/.test(password.value) && 
                                 /[a-z]/.test(password.value) && 
                                 /[0-9]/.test(password.value) && 
                                 /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password.value);
            
            // Check if passwords match
            const passwordsMatch = password.value === confirmPassword.value;
            
            // Check if email is valid
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const emailValid = emailRegex.test(email.value);
            
            // If any validation fails, prevent form submission
            if (!passwordValid || !passwordsMatch || !emailValid) {
                e.preventDefault();
                
                // Scroll to the first error
                if (!emailValid) {
                    email.focus();
                } else if (!passwordValid) {
                    password.focus();
                } else if (!passwordsMatch) {
                    confirmPassword.focus();
                }
            }
        });
        
        // Initialize validation on page load for any prefilled values
        if (email.value) validateEmail();
        if (password.value) validatePassword();
        if (confirmPassword.value) validatePasswordMatch();

        document.addEventListener('DOMContentLoaded', function() {
            // Find all name input fields
            const nameFields = document.querySelectorAll('input[name="first_name"], input[name="last_name"]');
            
            // Function to validate name fields
            function validateNameField(input) {
                const value = input.value.trim();
                
                // Check for titles (Dr., Mr., Mrs., etc.)
                const titlePattern = /^(Dr|Mr|Mrs|Ms|Prof|Rev|Hon)\.\s/i;
                if (titlePattern.test(value)) {
                    input.setCustomValidity("Please enter your name without titles (e.g., Dr., Mr., Mrs.)");
                    return false;
                }
                
                // Check for special characters (allowing letters, spaces, hyphens, and apostrophes)
                const specialCharPattern = /[^a-zA-Z\s\-\']/;
                if (specialCharPattern.test(value)) {
                    input.setCustomValidity("Name should only contain letters, spaces, hyphens, and apostrophes");
                    return false;
                }
                
                // Input is valid
                input.setCustomValidity("");
                return true;
            }
            
            // Add validation to all name fields
            nameFields.forEach(function(field) {
                // Validate on input
                field.addEventListener('input', function() {
                    validateNameField(this);
                });
                
                // Validate on blur (when leaving the field)
                field.addEventListener('blur', function() {
                    validateNameField(this);
                });
            });
            
            // Add form submission validation
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    let isValid = true;
                    
                    // Validate all name fields in this form
                    const formNameFields = this.querySelectorAll('input[name="first_name"], input[name="last_name"]');
                    formNameFields.forEach(function(field) {
                        if (!validateNameField(field)) {
                            isValid = false;
                            // Show validation message
                            field.reportValidity();
                        }
                    });
                    
                    // Prevent form submission if validation fails
                    if (!isValid) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>