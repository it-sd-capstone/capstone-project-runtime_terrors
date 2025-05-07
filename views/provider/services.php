<?php include VIEW_PATH . '/partials/header.php'; ?>
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body p-4">
                    <h2 class="text-primary mb-2">
                        <i class="fas fa-list-alt"></i> Manage Your Services
                    </h2>
                    <p class="text-muted">Select which services you offer and customize them for your practice.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
            . $_SESSION['success'] .
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
            . $_SESSION['error'] .
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        unset($_SESSION['error']);
    }
    
    $action = $_GET['action'] ?? 'view';
    
    if ($action === 'add') {
    ?>
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
    <?php } else { ?>
    <!-- Add Service Form -->
    <div class="row mb-4">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add a Service You Offer</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= base_url('index.php/provider/addProviderService') ?>" class="needs-validation" novalidate>
                        <?php if (function_exists('csrf_token_field')) echo csrf_token_field(); ?>
                        <div class="mb-3">
                            <label for="service_id" class="form-label fw-bold">Select Service:</label>
                            <select id="service_id" name="service_id" class="form-select" required>
                                <option value="">-- Choose a service --</option>
                                <?php foreach ($available_services as $service): ?>
                                    <option value="<?= $service['service_id'] ?>">
                                        <?= htmlspecialchars($service['name']) ?> (<?= htmlspecialchars($service['description']) ?>, $<?= htmlspecialchars($service['price']) ?>, <?= htmlspecialchars($service['duration']) ?> mins)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a service.</div>
                        </div>
                        <div class="mb-3">
                            <label for="custom_duration" class="form-label fw-bold">Custom Duration (mins):</label>
                            <input type="number" id="custom_duration" name="custom_duration" class="form-control" min="5" max="480" placeholder="Leave blank to use default">
                            <div class="form-text">Leave blank to use the default duration for this service.</div>
                        </div>
                        <div class="mb-3">
                            <label for="custom_notes" class="form-label fw-bold">Custom Notes:</label>
                            <textarea id="custom_notes" name="custom_notes" class="form-control" rows="2" placeholder="Any notes for patients or staff"></textarea>
                        </div>
                        <div class="d-flex mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Add Service
                            </button>
                            <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-primary ms-2">
                                <i class="fas fa-plus me-2"></i>Create New Service
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    
    <!-- List of Provider's Services -->
    <div class="card shadow-sm rounded">
        <div class="card-header d-flex justify-content-between align-items-center bg-white py-3 rounded-top">
            <h5 class="mb-0"><i class="fas fa-list-alt me-2 text-primary"></i>Your Offered Services</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($services)) : ?>
                <div class="text-center p-5">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <h5>No services found</h5>
                    <p class="text-muted">You haven't added any services yet.</p>
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Service</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3">Default Duration</th>
                                <th class="px-4 py-3">Default Price</th>
                                <th class="px-4 py-3">Custom Duration</th>
                                <th class="px-4 py-3">Custom Notes</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service) : ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <strong><?= htmlspecialchars($service['name']) ?></strong>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars($service['description']) ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars($service['duration'] ?? '') ?> mins
                                    </td>
                                    <td class="px-4 py-3">
                                        $<?= htmlspecialchars($service['price']) ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= $service['custom_duration'] ? htmlspecialchars($service['custom_duration']) . ' mins' : '<span class="text-muted">Default</span>' ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= $service['custom_notes'] ? htmlspecialchars($service['custom_notes']) : '<span class="text-muted">None</span>' ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <!-- Edit Form (inline or modal, here as inline for simplicity) -->
                                        <form method="POST" action="<?= base_url('index.php/provider/editProviderService') ?>" class="d-inline-block" style="width: 120px;">
                                            <input type="hidden" name="provider_service_id" value="<?= $service['provider_service_id'] ?>">
                                            <?php if (function_exists('csrf_token_field')) echo csrf_token_field(); ?>
                                            <input type="number" name="custom_duration" class="form-control form-control-sm mb-1" 
                                                   min="5" max="480" placeholder="Duration" 
                                                   value="<?= isset($service['custom_duration']) ? htmlspecialchars($service['custom_duration']) : '' ?>">
                                            <input type="text" name="custom_notes" class="form-control form-control-sm mb-1" 
                                                   placeholder="Notes" 
                                                   value="<?= isset($service['custom_notes']) ? htmlspecialchars($service['custom_notes']) : '' ?>">
                                            <button type="submit" class="btn btn-info btn-sm w-100 mb-1"><i class="fas fa-save"></i> Update</button>
                                        </form>
                                        <!-- Delete Form -->
                                        <form method="POST" action="<?= base_url('index.php/provider/deleteProviderService') ?>" class="d-inline-block">
                                            <input type="hidden" name="provider_service_id" value="<?= $service['provider_service_id'] ?>">
                                            <?php if (function_exists('csrf_token_field')) echo csrf_token_field(); ?>
                                            <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Are you sure you want to remove this service?');">
                                                <i class="fas fa-trash-alt"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include VIEW_PATH . '/partials/footer.php'; ?>
