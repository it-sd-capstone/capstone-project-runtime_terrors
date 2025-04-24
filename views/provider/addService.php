<h4>Add a New Service</h4>
<form method="POST" action="<?= base_url('index.php/provider/processAddService') ?>">
    <label>Service Name:</label>
    <input type="text" name="name" required>

    <label>Duration (mins):</label>
    <input type="number" name="duration" required>

    <label>Cost ($):</label>
    <input type="number" name="cost" required>

    <button type="submit" class="btn btn-success">Add Service</button>
</form>