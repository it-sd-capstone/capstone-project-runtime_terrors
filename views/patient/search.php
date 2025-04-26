<h4>Find a Provider</h4>

<!-- Search Form -->
<form method="GET" action="<?= base_url('index.php/patient/search') ?>">
    <label>Specialty:</label>
    <select name="specialty">
        <option value="">All Specialties</option>
        <?php foreach ($specialties as $specialty) : ?>
            <option value="<?= htmlspecialchars($specialty['name']) ?>">
                <?= htmlspecialchars($specialty['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Location:</label>
    <input type="text" name="location" placeholder="Enter city or zip code">

    <button type="submit" class="btn btn-primary">Search</button>
</form>

<!-- Provider Results -->
<?php if (!empty($providers)) : ?>
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
                        <a href="<?= base_url('index.php/patient/viewProvider/' . $provider['provider_id']) ?>" class="btn btn-info">View Profile</a>
                        <a href="<?= base_url('index.php/patient/book/' . $provider['provider_id']) ?>" class="btn btn-success">Book Appointment</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>No providers found. Try adjusting your search criteria.</p>
<?php endif; ?>