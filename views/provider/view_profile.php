
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

                    <!-- Professional Information Section -->
                    <div class="card mb-4 border-0 bg-light">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">
                                <i class="fas fa-user-md me-2"></i>Professional Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">First Name:</label>
                                    <div><?= htmlspecialchars($provider['first_name'] ?? '') ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Last Name:</label>
                                    <div><?= htmlspecialchars($provider['last_name'] ?? '') ?></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email:</label>
                                <div><?= htmlspecialchars($provider['email'] ?? '') ?></div>
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
                                    <label class="form-label fw-bold">Specialty:</label>
                                    <div><?= htmlspecialchars($provider['specialty'] ?? $provider['specialization'] ?? '') ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Phone:</label>
                                    <div><?= htmlspecialchars($provider['phone'] ?? '') ?></div>
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
                                <label class="form-label fw-bold">Professional Bio:</label>
                                <div><?= nl2br(htmlspecialchars($provider['bio'] ?? '')) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Availability Settings Section -->
                    <div class="card mb-4 border-0 bg-light">
                        <div class="card-body">
                            <h5 class="card-title text-warning mb-3">
                                <i class="fas fa-calendar-check me-2"></i>Availability Settings
                            </h5>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Accepting New Patients:</label>
                                <div>
                                    <?= (isset($provider['accepting_new_patients']) && $provider['accepting_new_patients']) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Maximum Patients Per Day:</label>
                                <div><?= htmlspecialchars($provider['max_patients_per_day'] ?? '10') ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('index.php/auth/change_password') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-key me-1"></i> Change Password
                        </a>
                        <a href="<?= base_url('index.php/provider/profile') ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
