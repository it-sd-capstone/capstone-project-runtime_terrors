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
                        <?php 
                        // Pagination logic
                        $items_per_page = 10; // Number of providers to display per page
                        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $current_page = max(1, $current_page); // Ensure page is at least 1
                        
                        $total_items = count($providers);
                        $total_pages = ceil($total_items / $items_per_page);
                        
                        // Ensure current page doesn't exceed total pages
                        if ($current_page > $total_pages && $total_pages > 0) {
                            $current_page = $total_pages;
                        }
                        
                        $start_index = ($current_page - 1) * $items_per_page;
                        $paged_providers = array_slice($providers, $start_index, $items_per_page);
                        
                        if (empty($paged_providers)): 
                        ?>
                            <tr>
                                <td colspan="9" class="text-center">No providers found.</td>
                            </tr>
                        <?php else: ?>
                            <?php
                            // Using strict indexing to avoid any rendering issues
                            $providerCount = count($paged_providers);
                            for ($i = 0; $i < $providerCount; $i++):
                            ?>
                                <tr>
                                    <td><?= $paged_providers[$i]['user_id'] ?></td>
                                    <td><?= htmlspecialchars($paged_providers[$i]['first_name'] . ' ' . $paged_providers[$i]['last_name']) ?></td>
                                    <td><?= htmlspecialchars($paged_providers[$i]['email']) ?></td>
                                    <td><?= htmlspecialchars($paged_providers[$i]['specialization'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($paged_providers[$i]['title'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= $paged_providers[$i]['service_count'] ?? 0 ?> services
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= $paged_providers[$i]['appointment_count'] ?? 0 ?> appointments
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($paged_providers[$i]['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($paged_providers[$i]['accepting_new_patients']) && $paged_providers[$i]['accepting_new_patients']): ?>
                                            <span class="badge bg-info">Accepting Patients</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Not Accepting</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                 data-bs-toggle="modal" data-bs-target="#actionModal<?= $paged_providers[$i]['user_id'] ?>">
                                            Actions <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination controls -->
            <?php if ($total_pages > 1): 
                // Preserve any existing filter parameters
                $query_params = $_GET;
                unset($query_params['page']); // Remove the page parameter to rebuild it
                $query_string = http_build_query($query_params);
                $url = base_url('index.php/admin/providers') . ($query_string ? "?$query_string&" : "?");
            ?>
            <nav aria-label="Provider pagination">
                <ul class="pagination justify-content-center mt-4">
                    <!-- Previous page link -->
                    <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $url ?>page=<?= $current_page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <!-- Page number links -->
                    <?php 
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    // Show first page if not included in the range
                    if ($start_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $url ?>page=1">1</a>
                        </li>
                        <?php if ($start_page > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Display page links in the calculated range -->
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $url ?>page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Show last page if not included in the range -->
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $url ?>page=<?= $total_pages ?>"><?= $total_pages ?></a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Next page link -->
                    <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $url ?>page=<?= $current_page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
        <div class="card-footer text-muted py-3">
            <div class="d-flex justify-content-between align-items-center">
                <span>Total Providers: <?= $total_items ?></span>
                <?php if ($total_pages > 0): ?>
                <span>Page <?= $current_page ?> of <?= $total_pages ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Action Modals - Separate from table for clean HTML -->
    <?php if (!empty($providers)): ?>
        <?php 
        // We need to use the full providers array for modals to ensure all modals exist
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
