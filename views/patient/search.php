<h4>Find a Provider</h4>

<!-- Error display -->
<?php if (isset($error) && !empty($error)): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<!-- Search Form -->
<form method="GET" action="<?= base_url('index.php/patient/search') ?>">
    <div class="mb-3">
        <label for="specialty" class="form-label">Specialty:</label>
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
    
    <div class="mb-3">
        <label for="location" class="form-label">Location:</label>
        <input type="text" class="form-control" id="location" name="location" 
               placeholder="Enter city or zip code" value="<?= htmlspecialchars($location) ?>">
    </div>
    
    <!-- Add hidden field to indicate form submission -->
    <input type="hidden" name="search_submitted" value="1">
    
    <button type="submit" class="btn btn-primary">Search</button>
</form>

<!-- Provider Results -->
<?php if (!empty($providers)) : ?>
    <div class="mt-4">
        <h5>Search Results</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Provider</th>
                    <th>Specialty</th>
                    <th>Location</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($providers as $provider) : ?>
                    <tr>
                        <td><?= htmlspecialchars($provider['name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($provider['specialty'] ?? 'General') ?></td>
                        <td><?= htmlspecialchars($provider['location'] ?? 'Local Area') ?></td>
                        <td><?= htmlspecialchars($provider['next_available_date'] ?? 'No upcoming slots') ?></td>
                        <td>
                            <a href="<?= base_url('index.php/patient/viewProvider/' . $provider['provider_id']) ?>" class="btn btn-info btn-sm">View Profile</a>
                            <a href="<?= base_url('index.php/patient/book/' . $provider['provider_id']) ?>" class="btn btn-success btn-sm">Book Appointment</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php elseif (isset($_GET['search_submitted'])): ?>
    <div class="alert alert-info mt-3">
        <p>No providers found. Try adjusting your search criteria.</p>
    </div>
<?php endif; ?>