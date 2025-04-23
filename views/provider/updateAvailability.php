<h4>Update Your Availability</h4>
<form method="POST" action="<?= base_url('index.php/provider/processUpdateAvailability') ?>">
    <label>Select Date:</label>
    <input type="date" name="availability_date" required>

    <label>Start Time:</label>
    <input type="time" name="start_time" required>

    <label>End Time:</label>
    <input type="time" name="end_time" required>

    <button type="submit" class="btn btn-success">Update Availability</button>
</form>