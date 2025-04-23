<h4>Book an Appointment</h4>
<form method="POST" action="<?= base_url('index.php/patient/confirmBooking') ?>">
    <label>Select Provider:</label>
    <select name="provider_id">
        <?php foreach ($providers as $provider) : ?>
            <option value="<?= $provider['user_id'] ?>"><?= htmlspecialchars($provider['name']) ?> - <?= htmlspecialchars($provider['specialty']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Select Date:</label>
    <input type="date" name="appointment_date" required>

    <label>Select Time:</label>
    <input type="time" name="appointment_time" required>

    <button type="submit" class="btn btn-success">Confirm Booking</button>
</form>