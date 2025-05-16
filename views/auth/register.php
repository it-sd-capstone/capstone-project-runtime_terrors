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
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4ecfa 100%);
            padding-top: 120px; /* Adjusted to match login form */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .register-container {
            max-width: 650px;
            margin: 0 auto;
        }
        
        .register-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .card-header {
            padding: 1.5rem 1rem;
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
        
        /* Password strength meter */
        .password-strength {
            height: 5px;
            margin-top: 10px;
            background-color: #e9ecef;
            border-radius: 3px;
            position: relative;
        }
        
        .password-strength-meter {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease-in-out, background-color 0.5s ease-in-out;
        }
        
        /* Demo verification link */
        .demo-verification-link {
            margin-top: 15px;
            padding: 12px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
        }
        
        .demo-verification-link .btn {
            margin-top: 10px;
        }
        
        /* Form sections */
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary);
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
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="card register-card">
            <div class="card-header bg-primary text-white text-center py-3">
                <h3>Create an Account</h3>
                <p class="mb-0 mt-2">Join us to schedule your appointments online</p>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors) && is_array($errors)): ?>
                    <div class="alert alert-danger">
                        <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php 
                // Process success message and extract verification link if present
                $verificationUrl = null;
                $successMessage = null;
                
                if(!empty($success)): 
                    // Extract the main success message (without the link)
                    $successMessage = strstr($success, '<a href=', true) ?: $success;
                    
                    // Extract verification URL if present
                    if(strpos($success, '<a href=') !== false) {
                        preg_match('/<a href=\'([^\']+)\'>(.*?)<\/a>/', $success, $matches);
                        if(isset($matches[1])) {
                            $verificationUrl = $matches[1];
                        }
                    }
                ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                    
                    <?php if($verificationUrl && ENVIRONMENT === 'development'): ?>
                    <div class="demo-verification-link">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill text-warning me-2 fs-5"></i>
                            <strong>Demo Mode: Email Verification</strong>
                        </div>
                        <p class="mt-2 mb-2">
                            In a real environment, a verification email would be sent. 
                            Since this is a demo, you can use this direct link instead:
                        </p>
                        <a href="<?= $verificationUrl ?>" class="btn btn-warning w-100">
                            <i class="bi bi-envelope-check me-2"></i>Verify Your Email Address
                        </a>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i> Fields marked with <span class="text-danger">*</span> are required
                </div>
                
                <form action="<?= base_url('index.php/auth/register') ?>" method="post" id="registerForm">
                    <?= csrf_field() ?>
                    
                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-person me-2"></i>Personal Information
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
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
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                                placeholder="(XXX) XXX-XXXX"
                                maxlength="14">
                            </div>
                            <div class="form-text">Optional - We'll only call if there are appointment changes</div>
                        </div>
                    </div>
                    
                    <!-- Account Security Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-shield-lock me-2"></i>Account Security
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="password-strength-meter" id="passwordStrengthMeter"></div>
                            </div>
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
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <div class="requirement">
                                <span id="match-requirement" class="invalid-requirement">
                                    <i class="bi bi-circle"></i> Passwords must match
                                </span>
                            </div>
                        </div>
                    </div>
                    
                                        <!-- Terms and Verification -->
                    <div class="form-section mb-0 pb-0 border-bottom-0">
                        <div class="form-section-title">
                            <i class="bi bi-check-circle me-2"></i>Terms & Verification
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="<?= base_url('index.php/terms') ?>" target="_blank">Terms of Service</a>
                                    and <a href="<?= base_url('index.php/privacy') ?>" target="_blank">Privacy Policy</a>
                                    <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- reCAPTCHA -->
                        <div class="mb-4">
                            <p class="text-center mb-2 text-muted small">Please verify that you are human</p>
                            <div class="d-flex justify-content-center">
                                <div class="g-recaptcha"
                                    data-sitekey="6Leh-TgrAAAAAL6uiA8JcjGfuz75m6ra-V4kIy8f"
                                    data-callback="enableSubmitButton"
                                    data-expired-callback="disableSubmitButton"></div>
                            </div>
                            <div id="recaptcha-error" class="text-danger text-center mt-2" style="display: none;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Please complete the reCAPTCHA verification
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3" id="registerButton">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center py-3">
                Already have an account? <a href="<?= base_url('index.php/auth') ?>" class="text-primary fw-bold">Log in</a>
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

        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Password validation and strength meter
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const email = document.getElementById('email');
        const strengthMeter = document.getElementById('passwordStrengthMeter');
        
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
            let strength = 0;
            
            // Check length
            if (value.length >= 8) {
                lengthReq.classList.remove('invalid-requirement');
                lengthReq.classList.add('valid-requirement');
                lengthReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> At least 8 characters';
                strength += 25;
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
                strength += 25;
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
                strength += 25;
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
                strength += 12.5;
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
                strength += 12.5;
            } else {
                specialReq.classList.remove('valid-requirement');
                specialReq.classList.add('invalid-requirement');
                specialReq.innerHTML = '<i class="bi bi-circle"></i> At least one special character';
            }
            
            // Update strength meter
            strengthMeter.style.width = strength + '%';
            
            // Set color based on strength
            if (strength < 40) {
                strengthMeter.style.backgroundColor = '#dc3545'; // Weak (red)
            } else if (strength < 70) {
                strengthMeter.style.backgroundColor = '#ffc107'; // Medium (yellow)
            } else {
                strengthMeter.style.backgroundColor = '#198754'; // Strong (green)
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
        
        // reCAPTCHA callback functions
        function enableSubmitButton() {
            document.getElementById('recaptcha-error').style.display = 'none';
        }
        
        function disableSubmitButton() {
            document.getElementById('recaptcha-error').style.display = 'block';
        }
        
        // Form submission validation
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            // Check if reCAPTCHA is completed
            const recaptchaResponse = grecaptcha.getResponse();
            if (recaptchaResponse.length === 0) {
                e.preventDefault();
                document.getElementById('recaptcha-error').style.display = 'block';
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
        
        // Name field validation
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
            
                        // Add validation listeners for each name field
            nameFields.forEach(input => {
                input.addEventListener('input', function() {
                    validateNameField(this);
                });
                
                input.addEventListener('blur', function() {
                    validateNameField(this);
                });
                
                // Check any prefilled values
                if (input.value) {
                    validateNameField(input);
                }
            });
        });
        
        // Helper function to show custom validation messages
        function showInvalidFeedback(input, message) {
            // Remove any existing feedback
            const existingFeedback = input.parentNode.querySelector('.invalid-feedback');
            if (existingFeedback) {
                existingFeedback.remove();
            }
            
            // Create and append new feedback message
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = message;
            input.parentNode.appendChild(feedback);
            
            // Add invalid class to input
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
        }
        
        // Helper function to remove validation feedback
        function removeInvalidFeedback(input) {
            const existingFeedback = input.parentNode.querySelector('.invalid-feedback');
            if (existingFeedback) {
                existingFeedback.remove();
            }
            
            // Remove invalid class
            input.classList.remove('is-invalid');
        }
        
        // Enhance form interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Automatic focus on first empty required field
            const firstEmptyField = document.querySelector('input[required]:not([value])');
            if (firstEmptyField) {
                firstEmptyField.focus();
            }
            
            // Add visual indicator when fields are being edited
            const formFields = document.querySelectorAll('.form-control');
            formFields.forEach(field => {
                field.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused-field');
                });
                
                field.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused-field');
                });
            });
            
            // Automatically check terms checkbox if user clicks on the label
            const termsLabel = document.querySelector('label[for="terms"]');
            if (termsLabel) {
                termsLabel.addEventListener('click', function(e) {
                    if (e.target.tagName !== 'A') { // Don't trigger if clicking on the link
                        const termsCheckbox = document.getElementById('terms');
                        termsCheckbox.checked = !termsCheckbox.checked;
                    }
                });
            }
        });
        
        
        // Helper function to check if form has user input
        function hasUserInput(form) {
            const formElements = form.elements;
            
            for (let i = 0; i < formElements.length; i++) {
                const element = formElements[i];
                
                // Skip buttons and hidden fields
                if (element.type === 'submit' || element.type === 'button' || element.type === 'hidden') {
                    continue;
                }
                
                // Check if text, email, password fields have value
                if ((element.type === 'text' || element.type === 'email' || element.type === 'password' || element.type === 'tel') 
                    && element.value.trim() !== '') {
                    return true;
                }
                
                // Check if checkbox is checked
                if (element.type === 'checkbox' && element.checked) {
                    return true;
                }
            }
            
            return false;
        }
    </script>
</body>
</html>
