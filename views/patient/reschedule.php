<h4>Reschedule Appointment</h4>
<form method="POST" action="<?= base_url('index.php/patient/reschedule') ?>">
    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
    
    <label>Select New Date:</label>
    <input type="date" name="new_date" required>

    <label>Select New Time:</label>
    <input type="time" name="new_time" required>

    <button type="submit" class="btn btn-warning">Reschedule</button>
</form>