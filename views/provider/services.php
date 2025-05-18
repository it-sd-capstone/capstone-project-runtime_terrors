<?php
echo "<!-- DEBUG SESSION: ";
print_r($_SESSION);
echo " -->";
?>
<?php include VIEW_PATH . '/partials/header.php'; ?>

<!-- Add only targeted CSS for mobile improvements -->
<style>
    /* Mobile optimizations */
    @media screen and (max-width: 767px) {
        /* Make the create button full-width on small screens */
        @media (max-width: 576px) {
            .col-lg-8.col-md-10.mx-auto {
                width: 100%;
            }
            .col-lg-8.col-md-10.mx-auto .btn {
                width: 100%;
            }
            .justify-content-end {
                justify-content: center !important;
            }
        }
        
        /* Table adjustments for mobile */
        .table-responsive {
            border: 0;
            overflow-x: auto;
        }
        
        /* Stack the buttons on small screens */
        .edit-service-btn {
            display: inline-block;
            margin-bottom: 0.5rem !important;
            margin-right: 0.5rem;
        }
        
        .table th, .table td {
            white-space: normal;
        }
        
        /* Compact the description on mobile */
        .table td:nth-child(2) {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Make buttons more touch-friendly */
        .btn-sm {
            padding: 0.375rem 0.5rem;
        }
    }
</style>

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
    ?>

    <!-- Add Service Modal Trigger -->
    <div class="row mb-4">
        <div class="col-lg-8 col-md-10 mx-auto d-flex justify-content-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="fas fa-plus me-2"></i>Create New Service
            </button>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="<?= base_url('index.php/service/processService') ?>" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addServiceModalLabel"><i class="fas fa-plus-circle me-2"></i>Add a New Service</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
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

    <!-- Edit Service Modal -->
    <div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="<?= base_url('index.php/service/processService') ?>" class="needs-validation" novalidate id="editServiceForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="service_id" id="edit_service_id">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="editServiceModalLabel"><i class="fas fa-edit me-2"></i>Edit Service</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_service_name" class="form-label fw-bold">Service Name:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" id="edit_service_name" name="service_name" class="form-control" required>
                            </div>
                            <div class="invalid-feedback">Please enter a service name.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label fw-bold">Description:</label>
                            <textarea id="edit_description" name="description" class="form-control" rows="3" required></textarea>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_duration" class="form-label fw-bold">Duration (mins):</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <input type="number" id="edit_duration" name="duration" class="form-control" required min="5" max="480">
                                    <span class="input-group-text">mins</span>
                                </div>
                                <div class="invalid-feedback">Please enter a valid duration (5-480 mins).</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_price" class="form-label fw-bold">Cost ($):</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" id="edit_price" name="price" class="form-control" required min="0" step="0.01">
                                </div>
                                <div class="invalid-feedback">Please enter a valid cost.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                                <th class="px-4 py-3">Duration</th>
                                <th class="px-4 py-3">Price</th>
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
                                    <td class="px-4 py-3 text-center">
                                        <!-- Edit Button -->
                                        <button 
                                            class="btn btn-info btn-sm mb-1 edit-service-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editServiceModal"
                                            data-service-id="<?= $service['service_id'] ?>"
                                            data-service-name="<?= htmlspecialchars($service['name'], ENT_QUOTES) ?>"
                                            data-description="<?= htmlspecialchars($service['description'], ENT_QUOTES) ?>"
                                            data-duration="<?= $service['duration'] ?>"
                                            data-price="<?= $service['price'] ?>"
                                        >
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <!-- Delete Form -->
                                        <form method="POST" action="<?= base_url('index.php/service/deleteProviderService') ?>" class="d-inline-block">
                                            <input type="hidden" name="provider_service_id" value="<?= $service['provider_service_id'] ?>">
                                            <?= csrf_field() ?>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fill the edit modal with the correct service data
    document.querySelectorAll('.edit-service-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit_service_id').value = btn.getAttribute('data-service-id');
            document.getElementById('edit_service_name').value = btn.getAttribute('data-service-name');
            document.getElementById('edit_description').value = btn.getAttribute('data-description');
            document.getElementById('edit_duration').value = btn.getAttribute('data-duration');
            document.getElementById('edit_price').value = btn.getAttribute('data-price');
        });
    });
    
    // Add this simple tap-to-expand functionality for descriptions on mobile
    if (window.innerWidth < 768) {
        document.querySelectorAll('.table td:nth-child(2)').forEach(function(cell) {
            cell.addEventListener('click', function() {
                if (this.style.whiteSpace === 'normal') {
                    this.style.whiteSpace = 'nowrap';
                    this.style.maxWidth = '150px';
                } else {
                    this.style.whiteSpace = 'normal';
                    this.style.maxWidth = 'none';
                }
            });
        });
    }
});
</script>
<?php include VIEW_PATH . '/partials/footer.php'; ?>
