<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Profile</h4>
                    <a href="<?= base_url('index.php/patient') ?>" class="btn btn-light btn-sm">
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
                    
                    <form id="profileForm" method="POST" action="<?= base_url('index.php/patient/updateProfile') ?>" novalidate>
                        <?= csrf_field() ?>
                        <!-- Personal Information Section -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-3">
                                    <i class="fas fa-user me-2"></i>Personal Information
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                            value="<?= htmlspecialchars($patient['first_name'] ?? '') ?>" required>
                                        <div class="invalid-feedback">Please enter your first name.</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                            value="<?= htmlspecialchars($patient['last_name'] ?? '') ?>" required>
                                        <div class="invalid-feedback">Please enter your last name.</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control bg-light" id="email" name="email"
                                           value="<?= htmlspecialchars($patient['email'] ?? '') ?>" readonly>
                                    <div class="form-text text-muted">Email cannot be changed. Contact support if you need to update your email.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?= htmlspecialchars($patient['phone'] ?? '') ?>" 
                                           pattern="^\(\d{3}\) \d{3}-\d{4}$" placeholder="(123) 456-7890" required>
                                    <div class="invalid-feedback">Please enter a valid phone number in format: (123) 456-7890</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                           value="<?= htmlspecialchars($patient['date_of_birth'] ?? '') ?>"
                                           max="<?= date('Y-m-d') ?>">
                                    <div class="invalid-feedback">Please select a valid date of birth (must be in the past).</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address"
                                           value="<?= htmlspecialchars($patient['address'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Emergency Contact Information -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-danger mb-3">
                                    <i class="fas fa-first-aid me-2"></i>Emergency Contact
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="emergency_contact" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="emergency_contact" name="emergency_contact"
                                               value="<?= htmlspecialchars($patient['emergency_contact'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="emergency_contact_phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone"
                                               value="<?= htmlspecialchars($patient['emergency_contact_phone'] ?? '') ?>"
                                               pattern="^\(\d{3}\) \d{3}-\d{4}$" placeholder="(123) 456-7890">
                                        <div class="invalid-feedback">Please enter a valid phone number in format: (123) 456-7890</div>
                                    </div>
                                </div>
                                <div class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Emergency contact information is optional but recommended.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Medical Information -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-info mb-3">
                                    <i class="fas fa-heartbeat me-2"></i>Medical Information
                                </h5>
                                
                                <div class="mb-3">
                                    <label for="medical_conditions" class="form-label">Medical Conditions</label>
                                    <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="4"><?= htmlspecialchars($patient['medical_conditions'] ?? '') ?></textarea>
                                    <div class="form-text text-muted">Please include any allergies, chronic conditions, or previous surgeries.</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Insurance Information -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-success mb-3">
                                    <i class="fas fa-notes-medical me-2"></i>Insurance Information
                                </h5>
                                
                                <?php 
                                // Extract insurance info from JSON
                                $insuranceProvider = '';
                                $insurancePolicyNumber = '';
                                if (!empty($patient['insurance_info'])) {
                                    $insuranceInfo = json_decode($patient['insurance_info'], true);
                                    if (is_array($insuranceInfo)) {
                                        $insuranceProvider = $insuranceInfo['provider'] ?? '';
                                        $insurancePolicyNumber = $insuranceInfo['policy_number'] ?? '';
                                    }
                                }
                                ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="insurance_provider" class="form-label">Insurance Provider</label>
                                        <input type="text" class="form-control" id="insurance_provider" name="insurance_provider"
                                               value="<?= htmlspecialchars($insuranceProvider) ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="insurance_policy_number" class="form-label">Policy Number</label>
                                        <input type="text" class="form-control" id="insurance_policy_number" name="insurance_policy_number"
                                               value="<?= htmlspecialchars($insurancePolicyNumber) ?>">
                                    </div>
                                </div>
                                <div class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Insurance information is optional but can help streamline your appointments.
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

<!-- Phone number formatting script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('profileForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            // Remove all non-numeric characters
            let value = e.target.value.replace(/\D/g, '');
            
            // Format the number as (XXX) XXX-XXXX
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = '(' + value;
                } else if (value.length <= 6) {
                    value = '(' + value.substring(0, 3) + ') ' + value.substring(3);
                } else {
                    value = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 10);
                }
            }
            
            // Update the input value
            e.target.value = value;
            
            // Validate the pattern
            if (input.pattern) {
                const pattern = new RegExp(input.pattern);
                if (pattern.test(input.value)) {
                    input.setCustomValidity('');
                } else {
                    input.setCustomValidity('Please enter a valid phone number in format: (123) 456-7890');
                }
            }
        });
    });
    
    // Date of birth validation
    const dobInput = document.getElementById('date_of_birth');
    if (dobInput) {
        dobInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            
            if (selectedDate > today) {
                this.setCustomValidity('Date of birth cannot be in the future');
            } else {
                this.setCustomValidity('');
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