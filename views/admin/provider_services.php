<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('index.php/admin') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('index.php/admin/providers') ?>">Providers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Services</li>
        </ol>
    </nav>

    <div class="row mb-3">
        <div class="col-md-8">
            <h2>Manage Services for <?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($provider['title'] ?? '') ?> - <?= htmlspecialchars($provider['specialization'] ?? 'No specialization') ?></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= base_url('index.php/admin/providers') ?>" class="btn btn-secondary">
                Back to Providers
            </a>
        </div>
    </div>
   
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
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
            <?php if ($_GET['error'] == 'add_failed'): ?>
                Failed to add service.
            <?php elseif ($_GET['error'] == 'remove_failed'): ?>
                Failed to remove service.
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
   
    <div class="row">
        <!-- Current Provider Services -->
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Current Services</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($services)): ?>
                        <p class="text-muted">This provider is not offering any services yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Duration</th>
                                        <th>Custom Duration</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services as $service): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($service['name']) ?></td>
                                            <td><?= $service['custom_duration'] ? $service['custom_duration'] : $service['duration'] ?> min</td>
                                            <td>
                                                <?php if ($service['custom_duration']): ?>
                                                    <span class="badge bg-info"><?= $service['custom_duration'] ?> min</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Standard</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($service['custom_notes']): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($service['custom_notes']) ?></small>
                                                <?php else: ?>
                                                    <small class="text-muted">-</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" action="<?= base_url('index.php/admin/manageProviderServices/' . $provider['user_id']) ?>" onsubmit="return confirm('Are you sure you want to remove this service?')">
                                                    <input type="hidden" name="action" value="remove_service">
                                                    <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i> Remove
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
       
        <!-- Add Service Form -->
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Add New Service</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('index.php/admin/manageProviderServices/' . $provider['user_id']) ?>">
                        <input type="hidden" name="action" value="add_service">
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Select Service</label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value="">-- Select a service --</option>
                                <?php foreach ($availableServices as $service): ?>
                                    <option value="<?= $service['service_id'] ?>">
                                        <?= htmlspecialchars($service['name']) ?> (<?= $service['duration'] ?> min)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                       
                        <div class="mb-3">
                            <label for="custom_duration" class="form-label">Custom Duration (minutes, optional)</label>
                            <input type="number" class="form-control" id="custom_duration" name="custom_duration" min="5" step="5" placeholder="Leave empty for default duration">
                            <div class="form-text">Override the standard service duration if needed.</div>
                        </div>
                       
                        <div class="mb-3">
                            <label for="custom_notes" class="form-label">Custom Notes (optional)</label>
                            <textarea class="form-control" id="custom_notes" name="custom_notes" rows="2" placeholder="Any special information about this provider's service"></textarea>
                        </div>
                       
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
