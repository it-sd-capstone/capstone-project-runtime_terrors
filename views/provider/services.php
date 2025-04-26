<h4>Manage Your Services</h4>

<table class="table">
    <thead>
        <tr>
            <th>Service</th>
            <th>Description</th>
            <th>Duration (mins)</th>
            <th>Cost ($)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($services)) : ?>
        <?php foreach ($services as $service) : ?>
            <tr>
                <td><?= htmlspecialchars($service['service_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($service['description'] ?? '') ?></td>
                <td><?= htmlspecialchars($service['duration'] ?? '') ?></td>
                <td>$<?= htmlspecialchars($service['price'] ?? '') ?></td>
                <td>
                    <a href="<?= base_url('index.php/provider/editService/' . ($service['provider_service_id'] ?? '')) ?>" class="btn btn-info">Edit</a>
                    <form method="POST" action="<?= base_url('index.php/provider/processDeleteService') ?>" style="display:inline-block;">
                        <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['provider_service_id'] ?? '') ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr><td colspan="5">No services found.</td></tr>
    <?php endif; ?>
</tbody>
</table>

<a href="<?= base_url('index.php/provider/addService') ?>" class="btn btn-success">Add New Service</a>