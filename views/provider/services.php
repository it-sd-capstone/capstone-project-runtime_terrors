<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <h4>Manage Services</h4>

    <?php
    $action = $_GET['action'] ?? 'view';

    switch ($action) {
        case 'add': ?>
            <h4>Add a New Service</h4>
            <form method="POST" action="<?= base_url('index.php/provider/processService') ?>">
                <input type="hidden" name="action" value="add">
                <label>Service Name:</label>
                <input type="text" name="name" required>
                <label>Duration (mins):</label>
                <input type="number" name="duration" required>
                <label>Cost ($):</label>
                <input type="number" name="cost" required>
                <button type="submit" class="btn btn-success">Add Service</button>
            </form>
        <?php break;

        case 'edit': ?>
            <h4>Edit Service</h4>
            <form method="POST" action="<?= base_url('index.php/provider/processService') ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['provider_service_id'] ?? '') ?>">
                <label>Service Name:</label>
                <input type="text" name="service_name" value="<?= htmlspecialchars($service['service_name'] ?? '') ?>" required>
                <label>Description:</label>
                <textarea name="description" required><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
                <label>Price ($):</label>
                <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($service['price'] ?? '') ?>" required>
                <button type="submit" class="btn btn-success">Save Changes</button>
                <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        <?php break;

        case 'delete': ?>
            <h4>Delete Service</h4>
            <p>Are you sure you want to delete <strong><?= htmlspecialchars($service['name']) ?></strong>?</p>
            <form method="POST" action="<?= base_url('index.php/provider/processService') ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
                <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        <?php break;

        default: ?>
            <h4>Available Services</h4>
            <a href="<?= base_url('index.php/provider/services?action=add') ?>" class="btn btn-success">Add Service</a>
            <div class="table-responsive mt-3">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Cost</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service) : ?>
                            <tr>
                                <td><?= htmlspecialchars($service['service_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($service['description'] ?? '') ?></td>
                                <td><?= htmlspecialchars($service['duration'] ?? '') ?> mins</td>
                                <td>$<?= htmlspecialchars($service['price'] ?? '') ?></td>
                                <td>
                                    <a href="<?= base_url('index.php/provider/services?action=edit&id=' . $service['provider_service_id']) ?>" class="btn btn-info">Edit</a>
                                    <a href="<?= base_url('index.php/provider/services?action=delete&id=' . $service['provider_service_id']) ?>" class="btn btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php
    }
    ?>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>