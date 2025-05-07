<?php include VIEW_PATH . '/partials/header.php'; ?>
<div class="container mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body p-4">
                    <h2 class="text-primary mb-2">
                        <i class="fas fa-list-alt"></i> Manage Services
                    </h2>
                    <p class="text-muted">Create and manage the services you offer to patients.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
    // Display flash messages if any
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ' . $_SESSION['success'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ' . $_SESSION['error'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['error']);
    }
    $action = $_GET['action'] ?? 'view';
    
    switch ($action) {
        case 'add': ?>
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add a New Service</h5>
                        </div>
                        <div class="card-body p-4">
                            <!-- Updated form action to use service controller instead of provider -->
                            <form method="POST" action="<?= base_url('index.php/service/processService') ?>" class="needs-validation" novalidate>
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="add">
                                
                                <div class="mb-3">
                                    <label for="service_name" class="form-label fw-bold">Service Name:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                        <input type="text" id="service_name" name="service_name" class="form-control" required placeholder="e.g. Regular Checkup">
                                    </div>
                                    <div class="invalid-feedback">Please enter a service name.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">Description:</label>
                                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Describe the service details" required></textarea>
                                    <div class="invalid-feedback">Please provide a description.</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="duration" class="form-label fw-bold">Duration (mins):</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                            <input type="number" id="duration" name="duration" class="form-control" required min="5" max="480" value="30">
                                            <span class="input-group-text">mins</span>
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid duration (5-480 mins).</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label fw-bold">Cost ($):</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                            <input type="number" id="price" name="price" class="form-control" required min="0" step="0.01" value="50.00">
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid cost.</div>
                                    </div>
                                </div>
                                
                                <div class="d-flex mt-4">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-2"></i>Add Service
                                    </button>
                                    <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-secondary ms-2">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php break;
        
        case 'edit': ?>
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Service</h5>
                        </div>
                        <div class="card-body p-4">
                            <!-- Updated form action to use service controller instead of provider -->
                            <form method="POST" action="<?= base_url('index.php/service/processService') ?>" class="needs-validation" novalidate>
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['provider_service_id'] ?? '') ?>">
                                
                                <div class="mb-3">
                                    <label for="service_name" class="form-label fw-bold">Service Name:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                        <input type="text" id="service_name" name="service_name" class="form-control" value="<?= htmlspecialchars($service['service_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="invalid-feedback">Please enter a service name.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">Description:</label>
                                    <textarea id="description" name="description" class="form-control" rows="3" required><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
                                    <div class="invalid-feedback">Please provide a description.</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="duration" class="form-label fw-bold">Duration (mins):</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                            <input type="number" id="duration" name="duration" class="form-control" value="<?= htmlspecialchars($service['duration'] ?? '') ?>" required min="5" max="480">
                                            <span class="input-group-text">mins</span>
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid duration (5-480 mins).</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label fw-bold">Price ($):</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                            <input type="number" id="price" name="price" class="form-control" value="<?= htmlspecialchars($service['price'] ?? '') ?>" required min="0" step="0.01">
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid price.</div>
                                    </div>
                                </div>
                                
                                <div class="d-flex mt-4">
                                    <button type="submit" class="btn btn-info text-white">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                    <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-secondary ms-2">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php break;
        
        case 'delete': ?>
            <div class="row">
                <div class="col-lg-6 col-md-8 mx-auto">
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-trash-alt me-2"></i>Delete Service</h5>
                        </div>
                        <div class="card-body p-4 text-center">
                            <div class="mb-4">
                                <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                                <h5>Are you sure you want to delete this service?</h5>
                                <p class="text-muted mb-0">This action cannot be undone. All appointments associated with this service may be affected.</p>
                            </div>
                            
                            <div class="alert alert-secondary">
                                <strong><?= htmlspecialchars($service['service_name'] ?? 'This service') ?></strong>
                                <p class="mb-0 small"><?= htmlspecialchars($service['description'] ?? '') ?></p>
                            </div>
                            
                            <!-- Updated form action to use service controller instead of provider -->
                            <form method="POST" action="<?= base_url('index.php/service/processService') ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['provider_service_id'] ?? '') ?>">
                                
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="submit" class="btn btn-danger px-4">
                                        <i class="fas fa-trash-alt me-2"></i>Confirm Delete
                                    </button>
                                    <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-secondary px-4">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php break;
        
        default: ?>
            <div class="card shadow-sm rounded">
                <div class="card-header d-flex justify-content-between align-items-center bg-white py-3 rounded-top">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2 text-primary"></i>Your Services</h5>
                    <a href="<?= base_url('index.php/provider/services?action=add') ?>" class="btn btn-success">
                        <i class="fas fa-plus-circle me-2"></i>Add New Service
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($services)) : ?>
                        <div class="text-center p-5">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <h5>No services found</h5>
                            <p class="text-muted">You haven't created any services yet.</p>
                            <a href="<?= base_url('index.php/provider/services?action=add') ?>" class="btn btn-primary mt-2">
                                <i class="fas fa-plus-circle me-2"></i>Create Your First Service
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3">Service</th>
                                        <th class="px-4 py-3">Description</th>
                                        <th class="px-4 py-3">Duration</th>
                                        <th class="px-4 py-3">Cost</th>
                                        <th class="px-4 py-3 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services as $service) : ?>
                                        <tr>
                                            <td class="px-4 py-3">
                                                <strong><?= htmlspecialchars($service['service_name'] ?? '') ?></strong>
                                            </td>
                                            <td class="px-4 py-3">
                                                <?= htmlspecialchars($service['description'] ?? '') ?>
                                            </td>
                                            <td class="px-4 py-3">
                                                <?= htmlspecialchars($service['duration'] ?? '') ?> mins
                                            </td>
                                            <td class="px-4 py-3">
                                                $<?= htmlspecialchars($service['price'] ?? '') ?>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <a href="<?= base_url('index.php/provider/services?action=edit&id=' . $service['provider_service_id']) ?>" class="btn btn-info">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </a>
                                                <a href="<?= base_url('index.php/provider/services?action=delete&id=' . $service['provider_service_id']) ?>" class="btn btn-danger">
                                                    <i class="fas fa-trash-alt me-2"></i>Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php
    }
    ?>
</div>
<?php include VIEW_PATH . '/partials/footer.php'; ?>
