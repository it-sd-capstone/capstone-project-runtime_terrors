<h4>Update Availability</h4>
<form method="POST" action="<?= base_url('index.php/provider/processUpdateAvailability') ?>">
    <label>Select Date:</label>
    <input type="date" name="availability_date" required>

    <label>Start Time:</label>
    <input type="time" name="start_time" required>

    <label>End Time:</label>
    <input type="time" name="end_time" required>

    <label>Available:</label>
    <select name="is_available">
        <option value="1">Available</option>
        <option value="0">Unavailable</option>
    </select>

    <button type="submit" class="btn btn-success">Update Availability</button>
</form>