<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Provider Schedule</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.js"></script>
</head>
<body>

<h2>Your Schedule</h2>

<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: '<?= base_url("index.php/provider/getProviderSchedules") ?>' // ✅ Fetch availability dynamically
    });
    calendar.render();
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>

</body>
</html>