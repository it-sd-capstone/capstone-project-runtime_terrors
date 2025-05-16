
<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <h4 class="mb-0">My Profile</h4>
                </div>
                <div class="card-body">

                    <!-- Personal Information Section -->
                    <div class="card mb-4 border-0 bg-light">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">
                                <i class="fas fa-user me-2"></i>Personal Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>First Name:</strong> <?= htmlspecialchars($patient['first_name'] ?? '') ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Last Name:</strong> <?= htmlspecialchars($patient['last_name'] ?? '') ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <strong>Email:</strong> <?= htmlspecialchars($patient['email'] ?? '') ?>
                            </div>
                            <div class="mb-3">
                                <strong>Phone:</strong> <?= htmlspecialchars($patient['phone'] ?? '') ?>
                            </div>
                            <div class="mb-3">
                                <strong>Date of Birth:</strong> <?= htmlspecialchars($patient['date_of_birth'] ?? '') ?>
                            </div>
                            <div class="mb-3">
                                <strong>Address:</strong> <?= htmlspecialchars($patient['address'] ?? '') ?>
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
                                    <strong>Name:</strong> <?= htmlspecialchars($patient['emergency_contact'] ?? '') ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Phone:</strong> <?= htmlspecialchars($patient['emergency_contact_phone'] ?? '') ?>
                                </div>
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
                                <strong>Medical Conditions:</strong>
                                <div><?= nl2br(htmlspecialchars($patient['medical_conditions'] ?? '')) ?></div>
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
                                    <strong>Insurance Provider:</strong> <?= htmlspecialchars($insuranceProvider) ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Policy Number:</strong> <?= htmlspecialchars($insurancePolicyNumber) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('index.php/patient') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                        <a href="<?= base_url('index.php/patient/profile') ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
