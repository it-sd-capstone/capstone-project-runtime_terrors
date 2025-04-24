<?php include '../layout/partials/header.php'; ?>
<?php include '../layout/partials/navigation.php'; ?>

<div class="container mt-4">
    <h2>Manage Your Services</h2>

    <!-- Add New Service Form -->
    <form id="addServiceForm">
        <input type="text" name="service_name" placeholder="Service Name" required class="form-control">
        <textarea name="description" placeholder="Service Description" class="form-control"></textarea>
        <input type="number" name="price" step="0.01" placeholder="Price" required class="form-control">
        <button type="submit" class="btn btn-success mt-3">Add Service</button>
    </form>

    <hr>

    <!-- List Existing Services -->
    <h3>Your Services</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th><th>Description</th><th>Price</th><th>Actions</th>
            </tr>
        </thead>
        <tbody id="servicesList">
            <?php foreach ($providerServices as $service) : ?>
                <tr>
                    <td><?= htmlspecialchars($service['service_name']) ?></td>
                    <td><?= htmlspecialchars($service['description']) ?></td>
                    <td>$<?= number_format($service['price'], 2) ?></td>
                    <td>
                        <button class="btn btn-danger deleteService" data-id="<?= $service['id'] ?>">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById("addServiceForm").addEventListener("submit", function(event) {
    event.preventDefault();
    let formData = new FormData(this);

    fetch("/provider/addService", {
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById("servicesList").innerHTML += `
                <tr>
                    <td>${data.service_name}</td>
                    <td>${data.description}</td>
                    <td>$${data.price.toFixed(2)}</td>
                    <td><button class="btn btn-danger deleteService" data-id="${data.id}">Delete</button></td>
                </tr>`;
        } else {
            alert("Error adding service!");
        }
    });
});
</script>

<?php include '../layout/partials/footer.php'; ?>