<?php
ini_set('display_errors', 2);
ini_set('display_startup_errors', 2);
error_reporting(E_ALL);

?>
<?php include VIEW_PATH . '/partials/header.php'; ?>
<div class="container">
    <h2>Manage Users</h2>
    
    <!-- Add filter controls here -->
    <div class="card mb-3">
        <div class="card-header">
            <h5>Filter Users</h5>
            
        </div>
        <div class="card-body">
            <form action="<?= base_url('index.php/admin/users') ?>" method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">All Roles</option>
                        <option value="patient" <?= (isset($_GET['role']) && $_GET['role'] === 'patient') ? 'selected' : '' ?>>Patient</option>
                        <option value="provider" <?= (isset($_GET['role']) && $_GET['role'] === 'provider') ? 'selected' : '' ?>>Provider</option>
                        <option value="admin" <?= (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?= (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (isset($_GET['status']) && $_GET['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>User List</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        Add New User
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['user_id'] ?></td>
                                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'provider' ? 'success' : 'primary') ?>"><?= ucfirst($user['role']) ?></span></td>
                                    <td><?= $user['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('index.php/admin/users/view/' . $user['user_id']) ?>" class="btn btn-outline-info">View</a>
                                            <a href="<?= base_url('index.php/admin/users/edit/' . $user['user_id']) ?>" class="btn btn-outline-primary">Edit</a>
                                            <?php if ($user['is_active']): ?>
                                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?= $user['user_id'] ?>">Deactivate</button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?= $user['user_id'] ?>">Activate</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('index.php/admin/users/add') ?>" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="patient">Patient</option>
                            <option value="provider">Provider</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modals -->
<?php foreach ($users as $user): ?>
<div class="modal fade" id="deleteUserModal<?= $user['user_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $user['is_active'] ? 'Deactivate' : 'Activate' ?> User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($user['is_active']): ?>
                    <p>Are you sure you want to deactivate the user: <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>?</p>
                    <p class="text-warning">The user will no longer be able to log in, but their data will be preserved.</p>
                <?php else: ?>
                    <p>Are you sure you want to activate the user: <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>?</p>
                    <p class="text-success">The user will be able to log in again.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <?php if ($user['is_active']): ?>
                    <a href="<?= base_url('index.php/admin/users/deactivate/' . $user['user_id']) ?>" class="btn btn-warning">Deactivate</a>
                <?php else: ?>
                    <a href="<?= base_url('index.php/admin/users/activate/' . $user['user_id']) ?>" class="btn btn-success">Activate</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
