<h4>Delete Service</h4>
<p>Are you sure you want to delete <strong><?= htmlspecialchars($service['name']) ?></strong>?</p>
<form method="POST" action="<?= base_url('index.php/provider/processDeleteService') ?>">
    <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
    <button type="submit" class="btn btn-danger">Confirm Delete</button>
    <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-secondary">Cancel</a>
</form>