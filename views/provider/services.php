<h4>Manage Your Services</h4>
<table class="table">
    <thead>
        <tr>
            <th>Service</th>
            <th>Duration</th>
            <th>Cost</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($services as $service) : ?>
            <tr>
                <td><?= htmlspecialchars($service['name']) ?></td>
                <td><?= htmlspecialchars($service['duration']) ?> mins</td>
                <td>$<?= htmlspecialchars($service['cost']) ?></td>
                <td>
                    <a href="<?= base_url('index.php/provider/deleteService/' . $service['id']) ?>" class="btn btn-danger">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="<?= base_url('index.php/provider/addService') ?>" class="btn btn-success">Add New Service</a>