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
                        <div class="alert alert-success">
                            <?= $_SESSION['success'] ?>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error'] ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= base_url('index.php/patient/updateProfile') ?>">
                        <?= csrf_field() ?>
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       value="<?= htmlspecialchars($patient['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       value="<?= htmlspecialchars($patient['last_name'] ?? '') ?>" required>
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
                                   value="<?= htmlspecialchars($patient['phone'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                   value="<?= htmlspecialchars($patient['date_of_birth'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address"
                                   value="<?= htmlspecialchars($patient['address'] ?? '') ?>">
                        </div>
                        
                        <!-- Emergency Contact Information -->
                        <h5 class="mt-4 mb-3">Emergency Contact</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact" class="form-label">Name</label>
                                <input type="text" class="form-control" id="emergency_contact" name="emergency_contact"
                                       value="<?= htmlspecialchars($patient['emergency_contact'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone"
                                       value="<?= htmlspecialchars($patient['emergency_contact_phone'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <!-- Medical Information -->
                        <h5 class="mt-4 mb-3">Medical Information</h5>
                        <div class="mb-3">
                            <label for="medical_conditions" class="form-label">Medical Conditions</label>
                            <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="4"><?= htmlspecialchars($patient['medical_conditions'] ?? '') ?></textarea>
                            <div class="form-text text-muted">Please include any allergies, chronic conditions, or previous surgeries.</div>
                        </div>
                        
                        <!-- Insurance Information -->
                        <h5 class="mt-4 mb-3">Insurance Information</h5>
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

<?php include VIEW_PATH . '/partials/footer.php'; ?>