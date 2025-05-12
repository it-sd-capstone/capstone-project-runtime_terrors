<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container-fluid my-4" style="min-height: 80vh; padding-bottom: 40px;">
    <!-- Success messages section -->
    <?php if (isset($_SESSION['success']) && isset($_SESSION['show_password'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= $_SESSION['success'] ?>
            <div class="mt-2">
                <strong>IMPORTANT:</strong> Please copy this password now. For security reasons, it will not be shown again.
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['show_password']); ?>
    <?php elseif (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Page header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Manage Providers</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= base_url('index.php/admin/addProvider') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Provider
            </a>
        </div>
    </div>
    
    <!-- Query parameter messages -->
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
    
    <!-- Main card with provider list -->
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h5 class="mb-0">Provider List</h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
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
                                <td colspan="8" class="text-center py-5">
                                    <p class="text-muted">No providers found.</p>
                                    <a href="<?= base_url('index.php/admin/addProvider') ?>" class="btn btn-primary mt-2">
                                        Add Your First Provider
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($providers as $provider): ?>
                                <tr>
                                    <td class="py-3"><?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?></td>
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
                                        <!-- Button to trigger modal -->
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                data-bs-toggle="modal" data-bs-target="#actionModal<?= $provider['user_id'] ?>">
                                            Actions <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        
                                        <!-- Modal for actions -->
                                        <div class="modal fade" id="actionModal<?= $provider['user_id'] ?>" tabindex="-1" 
                                             aria-labelledby="actionModalLabel<?= $provider['user_id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="actionModalLabel<?= $provider['user_id'] ?>">
                                                            Actions for <?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="list-group">
                                                            <a href="<?= base_url('index.php/admin/manageProviderServices/' . $provider['user_id']) ?>" 
                                                               class="list-group-item list-group-item-action">
                                                                <div class="d-flex w-100 justify-content-between">
                                                                    <h6 class="mb-1">Manage Services</h6>
                                                                    <i class="bi bi-gear"></i>
                                                                </div>
                                                                <small class="text-muted">Configure services offered by this provider</small>
                                                            </a>
                                                            
                                                            <a href="<?= base_url('index.php/admin/viewAvailability/' . $provider['user_id']) ?>" 
                                                               class="list-group-item list-group-item-action">
                                                                <div class="d-flex w-100 justify-content-between">
                                                                    <h6 class="mb-1">View Availability</h6>
                                                                    <i class="bi bi-calendar"></i>
                                                                </div>
                                                                <small class="text-muted">See provider's schedule and available time slots</small>
                                                            </a>
                                                            
                                                            <button type="button" class="list-group-item list-group-item-action" 
                                                                    onclick="submitForm('status-form-<?= $provider['user_id'] ?>')">
                                                                <div class="d-flex w-100 justify-content-between">
                                                                    <h6 class="mb-1"><?= $provider['is_active'] ? 'Deactivate' : 'Activate' ?> Account</h6>
                                                                    <i class="bi bi-<?= $provider['is_active'] ? 'slash-circle' : 'check-circle' ?>"></i>
                                                                </div>
                                                                <small class="text-muted">
                                                                    <?= $provider['is_active'] ? 'Prevent' : 'Allow' ?> provider from logging in and being shown to patients
                                                                </small>
                                                            </button>
                                                            
                                                            <button type="button" class="list-group-item list-group-item-action" 
                                                                    onclick="submitForm('patients-form-<?= $provider['user_id'] ?>')">
                                                                <div class="d-flex w-100 justify-content-between">
                                                                    <h6 class="mb-1"><?= isset($provider['accepting_new_patients']) && $provider['accepting_new_patients'] ? 'Stop' : 'Start' ?> Accepting Patients</h6>
                                                                    <i class="bi bi-person-<?= isset($provider['accepting_new_patients']) && $provider['accepting_new_patients'] ? 'slash' : 'plus' ?>"></i>
                                                                </div>
                                                                <small class="text-muted">
                                                                    <?= isset($provider['accepting_new_patients']) && $provider['accepting_new_patients'] ? 'Prevent' : 'Allow' ?> provider from being shown for new appointment bookings
                                                                </small>
                                                            </button>
                                                        </div>
                                                        
                                                        <!-- Hidden forms for actions -->
                                                        <form id="status-form-<?= $provider['user_id'] ?>" method="post" action="<?= base_url('index.php/admin/toggleUserStatus') ?>" style="display: none;">
                                                            <input type="hidden" name="user_id" value="<?= $provider['user_id'] ?>">
                                                        </form>
                                                        
                                                        <form id="patients-form-<?= $provider['user_id'] ?>" method="post" action="<?= base_url('index.php/admin/toggleAcceptingPatients') ?>" style="display: none;">
                                                            <input type="hidden" name="provider_id" value="<?= $provider['user_id'] ?>">
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted py-3">
            <div class="d-flex justify-content-between align-items-center">
                <span>Total Providers: <?= isset($providers) && is_array($providers) ? count($providers) : 0 ?></span>
                <!-- <a href="#" class="btn btn-sm btn-outline-secondary">Export List</a> -->
            </div>
        </div>
    </div>
</div>

<script>
// Function to submit forms for provider actions
function submitForm(formId) {
    document.getElementById(formId).submit();
}
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
