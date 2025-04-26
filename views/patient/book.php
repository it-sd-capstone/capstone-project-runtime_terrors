<h4>Book an Appointment</h4>

<!-- Calendar for Available Slots -->
<div id="calendar"></div>

<!-- Appointment Booking Form -->

<form id="book form" method="POST" action="<?= base_url('index.php/patient/confirmBooking') ?>">
    <label>Select Provider:</label>
    <select name="provider_id" required>
        <?php foreach ($providers as $provider) : ?>
            <option value="<?= $provider['provider_id'] ?>">
                <?= htmlspecialchars($provider['provider_name']) ?> - <?= htmlspecialchars($provider['specialization']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Select Service:</label>
    <select name="service_id" required>
        <?php foreach ($services as $service) : ?>
            <option value="<?= $service['service_id'] ?>">
                <?= htmlspecialchars($service['name']) ?> ($<?= htmlspecialchars($service['price']) ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label>Select Date:</label>
    <input type="date" name="appointment_date" required>

    <label>Select Time:</label>
    <input type="time" name="start_time" required>

    <button type="submit" class="btn btn-success">Confirm Booking</button>
</form>
    <script>
    document.getElementById("bookForm").addEventListener("submit", function(event) {
        event.preventDefault();
        var selectedDate = document.getElementById("appointment_date").value;
        var selectedTime = document.getElementById("start_time").value;

        if (!selectedDate || !selectedTime) {
            alert("Please select both date and time.");
            return;
        }

        fetch("<?= base_url('index.php/patient/checkAvailability') ?>", {
            method: "POST",
            body: JSON.stringify({ provider_id: selectedProvider, date: selectedDate, time: selectedTime }),
            headers: { "Content-Type": "application/json" }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.available) {
                alert("Selected time is unavailable. Please pick a different slot.");
            } else {
                alert("Booking confirmed! Proceeding...");
                document.getElementById("bookForm").submit();
            }
        })
        .catch(error => alert("Server error. Try again later."));
    });

</script>
<!-- FullCalendar Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: "<?= base_url('index.php/scheduler/getAvailableSlots') ?>", // Get provider & open availability
        editable: false,
        eventClick: function(info) {
            if (info.event.extendedProps.is_booked) {
                alert("This slot is already booked.");
                return;
            }
            // Automatically fill the booking form with selected event details
            document.getElementById("appointment_date").value = info.event.start.toISOString().split('T')[0];
            document.getElementById("appointment_time").value = info.event.start.toISOString().split('T')[1].substring(0, 5);

            // Ask for confirmation before navigating
            if (confirm("Would you like to book this appointment?")) {
                window.location.href = "<?= base_url('index.php/patient/bookAppointment/') ?>" + info.event.id;
            }
        },
        eventContent: function(info) {
            return {
                html: info.event.extendedProps.is_booked 
                    ? `<span style="color: red;">Booked</span>` 
                    : `<span style="color: green;">Available</span>`
            };
        }

    });

    calendar.render();
});
</script>