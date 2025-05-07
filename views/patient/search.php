<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Find a Provider</h4>
                    <a href="<?= base_url('index.php/patient') ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <!-- Error display -->
                    <?php if (isset($error) && !empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Search Form (Simplified to match database) -->
                    <form method="GET" action="<?= base_url('index.php/patient/search') ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="specialty" class="form-label">Specialty</label>
                                <select name="specialty" id="specialty" class="form-select">
                                    <option value="">All Specialties</option>
                                    <?php foreach ($specialties as $spec) : ?>
                                        <?php if (is_array($spec) && isset($spec['specialization'])): ?>
                                            <option value="<?= htmlspecialchars($spec['specialization']) ?>"
                                                <?= (isset($searchParams['specialty']) && $searchParams['specialty'] == $spec['specialization']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($spec['specialization']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Preferred Date</label>
                                <input type="date" class="form-control" id="date" name="date"
                                       min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($searchParams['date'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="location" class="form-label">Search by Name or Phone</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       placeholder="Enter provider name or phone" value="<?= htmlspecialchars($searchParams['location'] ?? '') ?>">
                                <small class="text-muted">
                                    Search will match provider name or phone number
                                </small>
                            </div>
                        </div>

                        <!-- Add hidden field to indicate form submission -->
                        <input type="hidden" name="search_submitted" value="1">
                        
                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Search Providers
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Provider Results -->
            <?php if (!empty($providers)) : ?>
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Search Results (<?= count($providers) ?> providers found)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Provider</th>
                                        <th>Specialty</th>
                                        <th>Title</th>
                                        <th>Next Available</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($providers as $provider) : ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($provider['profile_image'])): ?>
                                                        <img src="<?= base_url('uploads/' . $provider['profile_image']) ?>"
                                                             class="rounded-circle me-2" width="40" height="40" alt="Provider">
                                                    <?php else: ?>
                                                        <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-user-md"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($provider['name'] ?? 'Unknown') ?></div>
                                                        <?php if (isset($provider['accepting_new_patients']) && $provider['accepting_new_patients']): ?>
                                                            <div class="small text-success">
                                                                <i class="fas fa-check-circle"></i> Accepting new patients
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="small text-muted">
                                                                Not accepting new patients
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($provider['specialty'] ?? 'General') ?></td>
                                            <td><?= htmlspecialchars($provider['title'] ?? 'Not specified') ?></td>
                                            <td>
                                                <?php if (!empty($provider['next_available_date'])): ?>
                                                    <span class="badge bg-success">
                                                        <?= date('M d', strtotime($provider['next_available_date'])) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No slots</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                <?php if (!empty($provider['provider_id'])): ?>
                                                    <a href="<?= base_url('index.php/patient/selectService') ?>"
                                                       class="btn btn-outline-primary"> Profile</a>
                                                    <a href="<?= base_url('index.php/patient/selectService') ?>"
                                                       class="btn btn-primary"> Book</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Provider details unavailable</span>
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
            <?php elseif (isset($_GET['search_submitted'])): ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <span>No providers found matching your criteria. Try adjusting your search filters.</span>
                </div>
                
                <!-- Suggested providers section -->
                <?php if (!empty($suggested_providers)): ?>
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Suggested Providers</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($suggested_providers as $provider): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($provider['name']) ?></h5>
                                                <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($provider['specialty']) ?></h6>
                                                <p class="card-text small"><?= htmlspecialchars($provider['bio'] ?? 'No bio available.') ?></p>
                                                <a href="<?= base_url('index.php/patient/viewProvider/' . $provider['provider_id']) ?>" class="card-link">View Profile</a>
                                                <a href="<?= base_url('index.php/patient/selectService') ?>" class="card-link">Book Appointment</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any interactive elements
    const specialtySelect = document.getElementById('specialty');
    const locationInput = document.getElementById('location');
    
    // Example: Auto-suggest providers as user types
    if (locationInput) {
        locationInput.addEventListener('input', function() {
            console.log('Provider search:', this.value);
        });
    }
    
    // Example: Handle specialty selection
    if (specialtySelect) {
        specialtySelect.addEventListener('change', function() {
            console.log('Selected specialty:', this.value);
        });
    }
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>