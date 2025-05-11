<?php include VIEW_PATH . '/partials/header.php'; ?>

<!-- Add this at the top of your providers.php view, right after including the header -->
<?php if (isset($_SESSION['success']) && isset($_SESSION['show_password'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= $_SESSION['success'] ?>
        <div class="mt-2">
            <strong>IMPORTANT:</strong> Please copy this password now. For security reasons, it will not be shown again.
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
    // Remove the password display flag so it's only shown once
    unset($_SESSION['show_password']); 
    ?>
<?php elseif (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Change from container to container-fluid for full width -->
<div class="container-fluid my-4" style="min-height: 80vh; padding-bottom: 40px;">
    <div class="row mb-4">  <!-- Increased bottom margin -->
        <div class="col-md-6">
            <h2>Manage Providers</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= base_url('index.php/admin/addProvider') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Provider
            </a>
        </div>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php if ($_GET['success'] == 'updated'): ?>
                Provider status updated successfully.
            <?php elseif ($_GET['success'] == 'provider_added'): ?>
                New provider added successfully.
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php if ($_GET['error'] == 'update_failed'): ?>
                Failed to update provider status.
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm mb-4"> <!-- Added bottom margin -->
        <div class="card-header py-3"> <!-- Added padding to card header -->
            <h5 class="mb-0">Provider List</h5>
        </div>
        <div class="card-body p-4"> <!-- Increased padding in card body -->
            <div class="table-responsive" style="min-height: 400px;"> <!-- Set minimum height for table area -->
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Specialization</th>
                            <th>Title</th>
                            <th>Services</th>
                            <th>Upcoming Appts</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($providers)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5"> <!-- Increased padding for empty state -->
                                    <p class="text-muted">No providers found.</p>
                                    <a href="<?= base_url('index.php/admin/addProvider') ?>" class="btn btn-primary mt-2">
                                        Add Your First Provider
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($providers as $provider): ?>
                                <tr>
                                    <td class="py-3"><?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?></td> <!-- Added vertical padding to cells -->
                                    <td class="py-3"><?= htmlspecialchars($provider['email']) ?></td>
                                    <td class="py-3"><?= htmlspecialchars($provider['specialization'] ?? 'N/A') ?></td>
                                    <td class="py-3"><?= htmlspecialchars($provider['title'] ?? 'N/A') ?></td>
                                    <td class="py-3">
                                        <span class="badge bg-info">
                                            <?= $provider['service_count'] ?? 0 ?> services
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-primary">
                                            <?= $provider['appointment_count'] ?? 0 ?> appointments
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <?php if ($provider['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($provider['accepting_new_patients']) && $provider['accepting_new_patients']): ?>
                                            <span class="badge bg-info">Accepting Patients</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Not Accepting</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="<?= base_url('index.php/admin/manageProviderServices?id=' . $provider['user_id']) ?>">
                                                        Manage Services
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="<?= base_url('index.php/admin/viewAvailability?id=' . $provider['user_id']) ?>">
                                                        View Availability
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="post" action="<?= base_url('index.php/admin/toggleUserStatus') ?>" style="display: inline;">
                                                        <input type="hidden" name="user_id" value="<?= $provider['user_id'] ?>">
                                                        <button type="submit" class="dropdown-item">
                                                            <?= $provider['is_active'] ? 'Deactivate' : 'Activate' ?> Account
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="post" action="<?= base_url('index.php/admin/toggleAcceptingPatients') ?>" style="display: inline;">
                                                        <input type="hidden" name="provider_id" value="<?= $provider['user_id'] ?>">
                                                        <button type="submit" class="dropdown-item">
                                                            <?= isset($provider['accepting_new_patients']) && $provider['accepting_new_patients'] ? 'Stop' : 'Start' ?> Accepting Patients
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted py-3"> <!-- Added a card footer -->
            <div class="d-flex justify-content-between align-items-center">
                <span>Total Providers: <?= isset($providers) && is_array($providers) ? count($providers) : 0 ?></span>
                <a href="#" class="btn btn-sm btn-outline-secondary">Export List</a>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>