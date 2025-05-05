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
                        
                        <!-- Additional fields can be added here following the same pattern -->
                        
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
    
    if (form) {
        // Convert to regular form submission with validation
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Phone validation
            const phoneField = document.getElementById('phone');
            if (phoneField && phoneField.value) {
                const phonePattern = /^\d{10}$|^\d{3}-\d{3}-\d{4}$/;
                if (!phonePattern.test(phoneField.value)) {
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
                }
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
