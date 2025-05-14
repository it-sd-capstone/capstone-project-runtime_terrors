<?php include VIEW_PATH . '/partials/header.php'; ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Provider Profile</h4>
                    <a href="<?= base_url('index.php/provider/index') ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $_SESSION['success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="providerProfileForm" method="POST" action="<?= base_url('index.php/provider/processUpdateProfile') ?>">
                        <!-- Personal Information Section -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-3">
                                    <i class="fas fa-user-md me-2"></i>Professional Information
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                            value="<?= htmlspecialchars($provider['first_name'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                            value="<?= htmlspecialchars($provider['last_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control bg-light" id="email" name="email"
                                           value="<?= htmlspecialchars($provider['email'] ?? '') ?>" readonly>
                                    <div class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i> Email cannot be changed. Contact admin if you need to update your email.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Specialty and Contact Section -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-success mb-3">
                                    <i class="fas fa-stethoscope me-2"></i>Specialty & Contact
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="specialization" class="form-label">Specialty</label>
                                        <input type="text" class="form-control" id="specialization" name="specialization"
                                            value="<?= htmlspecialchars($provider['specialization'] ?? '') ?>" required>
                                            <?php if (empty($provider['specialization'])): ?>
                                                <div class="text-danger mt-1">Specialty is required.</div>
                                            <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            value="<?= htmlspecialchars($provider['phone'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Professional Bio Section -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-info mb-3">
                                    <i class="fas fa-address-card me-2"></i>Professional Bio
                                </h5>
                                
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Professional Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4"><?= htmlspecialchars($provider['bio'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Availability Settings Section -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-warning mb-3">
                                    <i class="fas fa-calendar-check me-2"></i>Availability Settings
                                </h5>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="accepting_new_patients" 
                                           name="accepting_new_patients" <?= isset($provider['accepting_new_patients']) && $provider['accepting_new_patients'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="accepting_new_patients">
                                        Currently accepting new patients
                                    </label>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="max_patients_per_day" class="form-label">Maximum patients per day</label>
                                    <input type="number" class="form-control" id="max_patients_per_day" name="max_patients_per_day"
                                           value="<?= htmlspecialchars($provider['max_patients_per_day'] ?? '10') ?>" min="1" max="50">
                                    <div class="form-text">
                                        Limit the number of appointments you can have scheduled per day.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('index.php/auth/change_password') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-key me-1"></i> Change Password
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Client-side validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('providerProfileForm');
    const phoneField = document.getElementById('phone');
    
    // Phone field formatting
    if (phoneField) {
        // Format existing value when page loads
        if (phoneField.value) {
            phoneField.value = formatPhoneNumber(phoneField.value);
        }
        
        // Format as user types
        phoneField.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 0) {
                // Don't exceed 10 digits
                value = value.substring(0, 10);
                
                // Format the number as (XXX) XXX-XXXX
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = '(' + value.substring(0, 3) + ') ' + value.substring(3);
                } else {
                    value = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6);
                }
            }
            
            e.target.value = value;
        });
        
        // Handle special cases like backspace and delete
        phoneField.addEventListener('keydown', function(e) {
            // Allow backspace, delete, tab, escape, enter and navigation keys
            if (e.key === 'Backspace' || e.key === 'Delete') {
                let value = e.target.value;
                let caretPos = e.target.selectionStart;
                
                // If cursor is after a formatting character, move it past the character
                if (
                    (caretPos === 5 && value.charAt(4) === ' ') ||
                    (caretPos === 10 && value.charAt(9) === '-') ||
                    (caretPos === 2 && value.charAt(1) === '(') ||
                    (caretPos === 6 && value.charAt(5) === ')')
                ) {
                    if (e.key === 'Backspace') {
                        e.target.setSelectionRange(caretPos - 1, caretPos - 1);
                    } else { // Delete
                        e.target.setSelectionRange(caretPos + 1, caretPos + 1);
                    }
                    e.preventDefault();
                }
            }
        });
    }
    
    // Helper function to format existing phone numbers
    function formatPhoneNumber(value) {
        // Strip all non-digits
        const phoneDigits = value.replace(/\D/g, '');
        
        // Format based on number of digits
        if (phoneDigits.length === 0) {
            return '';
        } else if (phoneDigits.length <= 3) {
            return phoneDigits;
        } else if (phoneDigits.length <= 6) {
            return '(' + phoneDigits.substring(0, 3) + ') ' + phoneDigits.substring(3);
        } else {
            return '(' + phoneDigits.substring(0, 3) + ') ' + phoneDigits.substring(3, 6) + '-' + phoneDigits.substring(6, 10);
        }
    }
    
    if (form) {
        // Form validation on submit
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Phone validation - updated to match our format
            if (phoneField && phoneField.value) {
                // Phone should now be in format (XXX) XXX-XXXX or have at least 10 digits
                const formattedPhonePattern = /^\(\d{3}\)\s\d{3}-\d{4}$|^\d{10}$/;
                const digitsOnly = phoneField.value.replace(/\D/g, '');
                
                if (!formattedPhonePattern.test(phoneField.value) && digitsOnly.length !== 10) {
                    isValid = false;
                    phoneField.classList.add('is-invalid');
                    
                    // Add error message if it doesn't exist
                    if (!phoneField.nextElementSibling || !phoneField.nextElementSibling.classList.contains('invalid-feedback')) {
                        const feedback = document.createElement('div');
                        feedback.classList.add('invalid-feedback');
                        feedback.textContent = 'Please enter a valid 10-digit phone number';
                        phoneField.parentNode.insertBefore(feedback, phoneField.nextSibling);
                    }
                } else {
                    phoneField.classList.remove('is-invalid');
                    phoneField.classList.add('is-valid');
                    
                    // Update hidden field with digits-only version for database storage
                    const phoneDigitsOnly = document.createElement('input');
                    phoneDigitsOnly.type = 'hidden';
                    phoneDigitsOnly.name = 'phone_digits';
                    phoneDigitsOnly.value = digitsOnly;
                    form.appendChild(phoneDigitsOnly);
                }
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});

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
<?php include VIEW_PATH . '/partials/footer.php'; ?>
