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

                    <!-- Search Form -->
                    <form method="GET" action="<?= base_url('index.php/patient/search') ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="specialty" class="form-label">Specialty</label>
                                <select name="specialty" id="specialty" class="form-select">
                                    <option value="">All Specialties</option>
                                    <?php foreach ($specialties as $spec) : ?>
                                        <!-- Check if $spec is a string or an array -->
                                        <?php if (is_array($spec)): ?>
                                            <option value="<?= htmlspecialchars($spec['name'] ?? $spec['specialization'] ?? '') ?>"
                                                <?= ($specialty == ($spec['name'] ?? $spec['specialization'] ?? '')) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($spec['name'] ?? $spec['specialization'] ?? '') ?>
                                            </option>
                                        <?php else: ?>
                                            <option value="<?= htmlspecialchars($spec) ?>"
                                                <?= ($specialty == $spec) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($spec) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       placeholder="Enter city or zip code" value="<?= htmlspecialchars($location ?? '') ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="date" class="form-label">Preferred Date</label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($date ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="gender" class="form-label">Provider Gender</label>
                                <select name="gender" id="gender" class="form-select">
                                    <option value="">Any Gender</option>
                                    <option value="male" <?= (isset($gender) && $gender == 'male') ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= (isset($gender) && $gender == 'female') ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="language" class="form-label">Language</label>
                                <select name="language" id="language" class="form-select">
                                    <option value="">Any Language</option>
                                    <option value="english" <?= (isset($language) && $language == 'english') ? 'selected' : '' ?>>English</option>
                                    <option value="spanish" <?= (isset($language) && $language == 'spanish') ? 'selected' : '' ?>>Spanish</option>
                                    <option value="french" <?= (isset($language) && $language == 'french') ? 'selected' : '' ?>>French</option>
                                    <option value="mandarin" <?= (isset($language) && $language == 'mandarin') ? 'selected' : '' ?>>Mandarin</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="insurance" class="form-label">Insurance Accepted</label>
                                <select name="insurance" id="insurance" class="form-select">
                                    <option value="">Any Insurance</option>
                                    <option value="medicare" <?= (isset($insurance) && $insurance == 'medicare') ? 'selected' : '' ?>>Medicare</option>
                                    <option value="medicaid" <?= (isset($insurance) && $insurance == 'medicaid') ? 'selected' : '' ?>>Medicaid</option>
                                    <option value="blue_cross" <?= (isset($insurance) && $insurance == 'blue_cross') ? 'selected' : '' ?>>Blue Cross Blue Shield</option>
                                    <option value="aetna" <?= (isset($insurance) && $insurance == 'aetna') ? 'selected' : '' ?>>Aetna</option>
                                    <option value="cigna" <?= (isset($insurance) && $insurance == 'cigna') ? 'selected' : '' ?>>Cigna</option>
                                </select>
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
                                        <th>Location</th>
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
                                                        <div class="small text-muted">
                                                            <?= !empty($provider['rating']) ? str_repeat('★', $provider['rating']) . str_repeat('☆', 5 - $provider['rating']) : '' ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($provider['specialty'] ?? 'General') ?></td>
                                            <td><?= htmlspecialchars($provider['location'] ?? 'Local Area') ?></td>
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
                                                    <a href="<?= base_url('index.php/patient/viewProvider/' . htmlspecialchars($provider['provider_id'])) ?>" 
                                                    class="btn btn-outline-primary"> Profile</a>
                                                    <a href="<?= base_url('index.php/patient/book/' . htmlspecialchars($provider['provider_id'])) ?>" 
                                                    class="btn btn-primary"> Book</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Provider details unavailable</span>
                                                <?php endif; ?>
                                                    </a>
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
                                                <a href="<?= base_url('index.php/patient/book/' . $provider['provider_id']) ?>" class="card-link">Book Appointment</a>
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
    
    // Example: Auto-suggest locations as user types
    if (locationInput) {
        locationInput.addEventListener('input', function() {
            // This would typically call an API to get location suggestions
            // For demonstration purposes, we're just logging the input
            console.log('Location search:', this.value);
        });
    }
    
    // Example: Filter specialties based on other selections
    if (specialtySelect) {
        specialtySelect.addEventListener('change', function() {
            console.log('Selected specialty:', this.value);
        });
    }
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>