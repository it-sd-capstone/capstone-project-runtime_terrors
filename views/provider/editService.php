<h4>Edit Service</h4>

<form method="POST" action="<?= base_url('index.php/provider/processEditService') ?>">
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

<script>
document.getElementById("editServiceForm").addEventListener("submit", function(event) {
    event.preventDefault();
    var formData = new FormData(this);

    fetch("<?= base_url('index.php/provider/processEditService') ?>", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById("statusMessage").innerHTML = "<p class='success'>Service updated successfully!</p>";
        } else {
            document.getElementById("statusMessage").innerHTML = "<p class='error'>Update failed.</p>";
        }
    })
    .catch(error => console.error("Error:", error));
});
</script>
