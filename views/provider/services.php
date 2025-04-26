<!-- At the top of the file, add a back button for admin -->
<?php if (isset($isAdmin) && $isAdmin): ?>
<div class="mb-3">
    <a href="<?= base_url('index.php/admin/providers') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Providers
    </a>
</div>
<?php endif; ?>

<!-- Maybe add a heading indicating admin management -->
<?php if (isset($isAdmin) && $isAdmin): ?>
<div class="alert alert-info">
    You are managing services for provider: <?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?>
</div>
<?php endif; ?>

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
        <?php foreach ($services as $service) : ?>
            <tr>
                <td><?= htmlspecialchars($service['service_name']) ?></td>
                <td><?= htmlspecialchars($service['description']) ?></td>
                <td><?= htmlspecialchars($service['duration']) ?></td>
                <td>$<?= htmlspecialchars($service['price']) ?></td>
                <td>
                    <a href="<?= base_url('index.php/provider/editService/' . $service['provider_service_id']) ?>" class="btn btn-info">Edit</a>
                    <form method="POST" action="<?= base_url('index.php/provider/processDeleteService') ?>" style="display:inline-block;">
                        <input type="hidden" name="service_id" value="<?= $service['provider_service_id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="<?= base_url('index.php/provider/addService') ?>" class="btn btn-success">Add New Service</a>