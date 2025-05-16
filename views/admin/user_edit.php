<?php include VIEW_PATH . '/partials/header.php'; ?>
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="<?= base_url('index.php/admin/users') ?>" class="btn btn-secondary">« Back to Users</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Edit User</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error'] ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success'] ?>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('index.php/admin/users/update/' . $user['user_id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="patient" <?= $user['role'] === 'patient' ? 'selected' : '' ?>>Patient</option>
                                    <option value="provider" <?= $user['role'] === 'provider' ? 'selected' : '' ?>>Provider</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select" id="is_active" name="is_active" required>
                                    <option value="1" <?= $user['is_active'] == 1 ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $user['is_active'] == 0 ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Leave blank to keep current password.</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="password_change_required" name="password_change_required">
                                <label class="form-check-label" for="password_change_required">
                                    Require password change on next login
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update User</button>
                                                <!-- New Delete Button -->
                        <button type="button" class="btn btn-danger btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                            Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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