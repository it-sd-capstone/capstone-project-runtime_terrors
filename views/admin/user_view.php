<?php include VIEW_PATH . '/partials/admin_header.php'; ?>
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="<?= base_url('index.php/admin/users') ?>" class="btn btn-secondary">« Back to Users</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>User Details</h5>
                    <div>
                        <a href="<?= base_url('index.php/admin/users/edit/' . $user['user_id']) ?>" class="btn btn-primary btn-sm">Edit User</a>
                        <?php if ($user['is_active']): ?>
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#deactivateUserModal">
                                Deactivate User
                            </button>
                        <?php else: ?>
                            <a href="<?= base_url('index.php/admin/users/activate/' . $user['user_id']) ?>" class="btn btn-success btn-sm">
                                Activate User
                            </a>
                        <?php endif; ?>
                        <!-- New Delete Button -->
                        <button type="button" class="btn btn-danger btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                            Delete User
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Basic Information</h6>
                            <table class="table">
                                <tr>
                                    <th width="30%">User ID:</th>
                                    <td><?= $user['user_id'] ?></td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></td>
                                </tr>
                                <tr>
                                    <th>Role:</th>
                                    <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'provider' ? 'success' : 'primary') ?>"><?= ucfirst($user['role']) ?></span></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td><?= $user['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Account Information</h6>
                            <table class="table">
                                <tr>
                                    <th width="30%">Created:</th>
                                    <td><?= date('F j, Y', strtotime($user['created_at'] ?? 'now')) ?></td>
                                </tr>
                                <tr>
                                    <th>Last Login:</th>
                                    <td><?= isset($user['last_login']) ? date('F j, Y H:i', strtotime($user['last_login'])) : 'Never' ?></td>
                                </tr>
                                <tr>
                                    <th>Email Verified:</th>
                                    <td><?= isset($user['email_verified_at']) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>' ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if ($user['role'] === 'patient' && isset($patient_data)): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="fw-bold">Patient Information</h6>
                            <!-- Display patient-specific information -->
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($user['role'] === 'provider' && isset($provider_data)): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="fw-bold">Provider Information</h6>
                            <!-- Display provider-specific information -->
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate User Modal -->
<?php if ($user['is_active']): ?>
<div class="modal fade" id="deactivateUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deactivate User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>?</p>
                <p class="text-warning">The user will no longer be able to log in, but their data will be preserved.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?= base_url('index.php/admin/users/deactivate/' . $user['user_id']) ?>" class="btn btn-warning">Deactivate</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- NEW: Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Permanently Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i> Warning: This action cannot be undone.
                </div>
                <p>Are you sure you want to <strong>permanently delete</strong> the user account for <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>?</p>
                <p>All associated data including appointment history will be removed from the system.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?= base_url('index.php/admin/users/delete/' . $user['user_id']) ?>" class="btn btn-danger">Permanently Delete</a>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
