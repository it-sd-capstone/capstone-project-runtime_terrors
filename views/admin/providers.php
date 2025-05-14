<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container-fluid my-4">
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
                            <th>ID</th>
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
                                <td colspan="9" class="text-center">No providers found.</td>
                            </tr>
                        <?php else: ?>
                            <?php
                            // Using strict indexing to avoid any rendering issues
                            $providerCount = count($providers);
                            for ($i = 0; $i < $providerCount; $i++):
                            ?>
                                <tr>
                                    <td><?= $providers[$i]['user_id'] ?></td>
                                    <td><?= htmlspecialchars($providers[$i]['first_name'] . ' ' . $providers[$i]['last_name']) ?></td>
                                    <td><?= htmlspecialchars($providers[$i]['email']) ?></td>
                                    <td><?= htmlspecialchars($providers[$i]['specialization'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($providers[$i]['title'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= $providers[$i]['service_count'] ?? 0 ?> services
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= $providers[$i]['appointment_count'] ?? 0 ?> appointments
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($providers[$i]['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($providers[$i]['accepting_new_patients']) && $providers[$i]['accepting_new_patients']): ?>
                                            <span class="badge bg-info">Accepting Patients</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Not Accepting</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                data-bs-toggle="modal" data-bs-target="#actionModal<?= $providers[$i]['user_id'] ?>">
                                            Actions <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted py-3">
            <div class="d-flex justify-content-between align-items-center">
                <span>Total Providers: <?= count($providers) ?></span>
            </div>
        </div>
    </div>
    
    <!-- Action Modals - Separate from table for clean HTML -->
    <?php if (!empty($providers)): ?>
        <?php 
        $providerCount = count($providers);
        for ($i = 0; $i < $providerCount; $i++): 
        ?>
            <div class="modal fade" id="actionModal<?= $providers[$i]['user_id'] ?>" tabindex="-1" 
                 aria-labelledby="actionModalLabel<?= $providers[$i]['user_id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="actionModalLabel<?= $providers[$i]['user_id'] ?>">
                                Actions for <?= htmlspecialchars($providers[$i]['first_name'] . ' ' . $providers[$i]['last_name']) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="list-group">
                                <a href="<?= base_url('index.php/admin/manageProviderServices?id=' . $providers[$i]['user_id']) ?>"
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Manage Services</h6>
                                        <i class="bi bi-gear"></i>
                                    </div>
                                    <small class="text-muted">Configure services offered by this provider</small>
                                </a>
                                
                                <a href="<?= base_url('index.php/admin/viewAvailability?id=' . $providers[$i]['user_id']) ?>"
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">View Availability</h6>
                                        <i class="bi bi-calendar"></i>
                                    </div>
                                    <small class="text-muted">See provider's schedule and available time slots</small>
                                </a>
                                
                                <button type="button" class="list-group-item list-group-item-action"
                                        onclick="document.getElementById('status-form-<?= $providers[$i]['user_id'] ?>').submit()">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= $providers[$i]['is_active'] ? 'Deactivate' : 'Activate' ?> Account</h6>
                                        <i class="bi bi-<?= $providers[$i]['is_active'] ? 'slash-circle' : 'check-circle' ?>"></i>
                                    </div>
                                    <small class="text-muted">
                                        <?= $providers[$i]['is_active'] ? 'Prevent' : 'Allow' ?> provider from logging in and being shown to patients
                                    </small>
                                </button>
                                
                                <button type="button" class="list-group-item list-group-item-action"
                                        onclick="document.getElementById('patients-form-<?= $providers[$i]['user_id'] ?>').submit()">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= isset($providers[$i]['accepting_new_patients']) && $providers[$i]['accepting_new_patients'] ? 'Stop' : 'Start' ?> Accepting Patients</h6>
                                        <i class="bi bi-person-<?= isset($providers[$i]['accepting_new_patients']) && $providers[$i]['accepting_new_patients'] ? 'slash' : 'plus' ?>"></i>
                                    </div>
                                    <small class="text-muted">
                                        <?= isset($providers[$i]['accepting_new_patients']) && $providers[$i]['accepting_new_patients'] ? 'Prevent' : 'Allow' ?> provider from being shown for new appointment bookings
                                    </small>
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden forms for actions -->
            <form id="status-form-<?= $providers[$i]['user_id'] ?>" method="post" action="<?= base_url('index.php/admin/toggleUserStatus') ?>" style="display: none;">
                <input type="hidden" name="user_id" value="<?= $providers[$i]['user_id'] ?>">
                <input type="hidden" name="is_active" value="<?= $providers[$i]['is_active'] ? 0 : 1 ?>">
            </form>
            
            <form id="patients-form-<?= $providers[$i]['user_id'] ?>" method="post" action="<?= base_url('index.php/admin/toggleAcceptingPatients') ?>" style="display: none;">
                <input type="hidden" name="provider_id" value="<?= $providers[$i]['user_id'] ?>">
                <input type="hidden" name="accepting" value="<?= isset($providers[$i]['accepting_new_patients']) && $providers[$i]['accepting_new_patients'] ? 0 : 1 ?>">
            </form>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
