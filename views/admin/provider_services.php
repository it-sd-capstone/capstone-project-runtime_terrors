<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <!-- Admin Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('index.php/admin') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('index.php/admin/providers') ?>">Providers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Services</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="text-primary mb-2">
                                <i class="fas fa-list-alt"></i> Manage Services for <?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?>
                            </h2>
                            <p class="text-muted"><?= htmlspecialchars($provider['title'] ?? '') ?> - <?= htmlspecialchars($provider['specialization'] ?? 'No specialization') ?></p>
                        </div>
                        <a href="<?= base_url('index.php/admin/providers') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Providers
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php if ($_GET['success'] == 'added'): ?>
                Service added successfully.
            <?php elseif ($_GET['success'] == 'removed'): ?>
                Service removed successfully.
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php if ($_GET['error'] == 'add_failed'): ?>
                Failed to add service.
            <?php elseif ($_GET['error'] == 'remove_failed'): ?>
                Failed to remove service.
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Add Service Button -->
    <div class="row mb-4">
        <div class="col-lg-12 d-flex justify-content-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="fas fa-plus me-2"></i>Add Service to Provider
            </button>
        </div>
    </div>

    <!-- Current Provider Services -->
    <div class="card shadow-sm rounded">
        <div class="card-header d-flex justify-content-between align-items-center bg-white py-3 rounded-top">
            <h5 class="mb-0"><i class="fas fa-list-alt me-2 text-primary"></i>Current Services</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($services)): ?>
                <div class="text-center p-5">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <h5>No services found</h5>
                    <p class="text-muted">This provider isn't offering any services yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Service</th>
                                <th class="px-4 py-3">Duration</th>
                                <th class="px-4 py-3">Custom Duration</th>
                                <th class="px-4 py-3">Price</th>
                                <th class="px-4 py-3">Notes</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <strong><?= htmlspecialchars($service['name']) ?></strong>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= $service['custom_duration'] ? $service['custom_duration'] : $service['duration'] ?> min
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($service['custom_duration']): ?>
                                            <span class="badge bg-info"><?= $service['custom_duration'] ?> min</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Standard</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-secondary">$<?= number_format($service['price'], 2) ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if (!empty($service['custom_notes'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($service['custom_notes']) ?></small>
                                        <?php elseif (isset($service['description'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($service['description']) ?> <span class="badge bg-light text-dark">Default</span></small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="post" action="<?= base_url('index.php/admin/manageProviderServices/' . $provider['user_id']) ?>" onsubmit="return confirm('Are you sure you want to remove this service?')">
                                            <input type="hidden" name="action" value="remove_service">
                                            <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
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

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="<?= base_url('index.php/admin/manageProviderServices/' . $provider['user_id']) ?>" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="add_service">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addServiceModalLabel"><i class="fas fa-plus-circle me-2"></i>Add Service to Provider</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="service_id" class="form-label fw-bold">Select Service:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <select class="form-select" id="service_id" name="service_id" required>
                                    <option value="">-- Select a service --</option>
                                    <?php foreach ($availableServices as $service): ?>
                                        <option value="<?= $service['service_id'] ?>">
                                            <?= htmlspecialchars($service['name']) ?>
                                            (<?= $service['duration'] ?> min, $<?= number_format($service['price'], 2) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="invalid-feedback">Please select a service.</div>
                        </div>

                        <div class="mb-3">
                            <label for="custom_duration" class="form-label fw-bold">Custom Duration (minutes):</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                <input type="number" class="form-control" id="custom_duration" name="custom_duration"
                                       min="5" step="5" placeholder="Leave empty for default duration">
                                <span class="input-group-text">mins</span>
                            </div>
                            <div class="form-text">Override the standard service duration if needed.</div>
                        </div>

                        <div class="mb-3">
                            <label for="custom_notes" class="form-label fw-bold">Custom Notes:</label>
                            <textarea class="form-control" id="custom_notes" name="custom_notes" rows="3"
                                      placeholder="Any special information about this provider's service"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Add Service
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
