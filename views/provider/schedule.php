<h4>Manage Your Schedule</h4>
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