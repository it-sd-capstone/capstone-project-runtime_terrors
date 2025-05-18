<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <?php 
                    // Get all flash messages for the global context
                    $flash_messages = get_flash_messages('global');

                    // Display flash messages, sorted by type
                    if (!empty($flash_messages)): 
                        foreach ($flash_messages as $flash):
                            $alert_class = match($flash['type']) {
                                'success' => 'alert-success',
                                'error' => 'alert-danger',
                                'warning' => 'alert-warning',
                                default => 'alert-info'
                            };
                    ?>
                        <div class="alert <?= $alert_class ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flash['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php 
                        endforeach;
                    endif; 
                    ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php else: ?>
                        <?php if (isset($_SESSION['temp_user_id'])): ?>
                            <!-- First-time login with temp password -->
                            <div class="alert alert-info">
                                Your account was created with a temporary password. Please set a new password to continue.
                            </div>
                            
                            <form method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">
                                        Password must be at least 8 characters long and include uppercase letters, lowercase letters, numbers, and special characters.
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
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <div class="requirement mt-2">
                                        <span id="match-requirement" class="invalid-requirement">
                                            <i class="bi bi-circle"></i> Passwords must match
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Set Password</button>
                                </div>
                            </form>
                        
                        <?php else: ?>
                            <!-- Regular user password change - requires verification -->
                            <form method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">
                                        Password must be at least 8 characters long and include uppercase letters, lowercase letters, numbers, and special characters.
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
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <div class="requirement mt-2">
                                        <span id="match-requirement" class="invalid-requirement">
                                            <i class="bi bi-circle"></i> Passwords must match
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                    <a href="<?= base_url('index.php/patient/profile') ?>" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                            
                            <div class="mt-3 text-center">
                                <a href="<?= base_url('index.php/auth/forgot_password') ?>">Forgot your password?</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Password validation styles */
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
        width: 0%;
    }
</style>

<script>
    // Password elements
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const strengthMeter = document.getElementById('passwordStrengthMeter');
    
    // Requirement elements
    const lengthReq = document.getElementById('length-requirement');
    const uppercaseReq = document.getElementById('uppercase-requirement');
    const lowercaseReq = document.getElementById('lowercase-requirement');
    const numberReq = document.getElementById('number-requirement');
    const specialReq = document.getElementById('special-requirement');
    const matchReq = document.getElementById('match-requirement');
    
    // Validation functions
    function validatePassword() {
        const value = newPassword.value;
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
        
        // Return validation status
        return (value.length >= 8 &&
                /[A-Z]/.test(value) &&
                /[a-z]/.test(value) &&
                /[0-9]/.test(value) &&
                /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value));
    }
    
    function validatePasswordMatch() {
        if (newPassword.value === confirmPassword.value && newPassword.value !== '') {
            matchReq.classList.remove('invalid-requirement');
            matchReq.classList.add('valid-requirement');
            matchReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> Passwords match';
            return true;
        } else {
            matchReq.classList.remove('valid-requirement');
            matchReq.classList.add('invalid-requirement');
            matchReq.innerHTML = '<i class="bi bi-circle"></i> Passwords must match';
            return false;
        }
    }
    
    // Add event listeners
    if (newPassword) {
        newPassword.addEventListener('input', validatePassword);
        newPassword.addEventListener('input', validatePasswordMatch);
    }
    
    if (confirmPassword) {
        confirmPassword.addEventListener('input', validatePasswordMatch);
    }
    
    // Form submission validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const passwordValid = validatePassword();
            const passwordsMatch = validatePasswordMatch();
            
            if (!passwordValid || !passwordsMatch) {
                e.preventDefault();
                
                // Create or update error message
                let errorDiv = document.querySelector('.password-validation-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger password-validation-error';
                    form.prepend(errorDiv);
                }
                
                errorDiv.innerHTML = !passwordValid ? 
                    'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.' :
                    'Passwords do not match.';
                
                // Focus on the appropriate field
                if (!passwordValid) {
                    newPassword.focus();
                } else {
                    confirmPassword.focus();
                }
            }
        });
    }
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>