<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<div class="container my-4">
    <h2>Manage My Services</h2>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>My Services</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($providerServices as $service): ?>
                                <tr>
                                    <td><?= htmlspecialchars($service['name']) ?></td>
                                    <td><?= $service['duration'] ?> min</td>
                                    <td>$<?= htmlspecialchars($service['price']) ?></td>
                                    <td>
                                        <form method="post" action="<?= base_url('index.php/provider/deleteService') ?>" onsubmit="return confirm('Remove this service?')">
                                            <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Add New Service</h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('index.php/provider/addService') ?>" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>