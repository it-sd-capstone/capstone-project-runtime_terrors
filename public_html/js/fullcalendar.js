
document.addEventListener("DOMContentLoaded", function() {
    var calendarEl = document.getElementById("calendar");

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "timeGridWeek",
        events: "/api/getAvailableSlots.php",
        selectable: true,
        editable: false,
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "timeGridWeek,timeGridDay"
        },
        eventClick: function(info) {
            // Show details for all events
            alert(`Slot Details:\n${info.event.title}\nDate: ${info.event.startStr}`);

            // Allow deletion for both availability and unavailability (not booked)
            const type = info.event.extendedProps.type;
            const isBooked = info.event.extendedProps.isBooked;

            if ((type === 'availability' || type === 'unavailability') && !isBooked) {
                if (confirm(`Delete this slot (${info.event.title})?`)) {
                    fetch('/api/deleteAvailability.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: info.event.id, type: type })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            info.event.remove();
                        } else {
                            alert('Failed to delete slot: ' + (data.error || 'Unknown error.'));
                        }
                    })
                    .catch(err => {
                        alert('Error deleting slot.');
                        console.error(err);
                    });
                }
            }
        },
        select: function(info) {
            let confirmed = confirm(`Do you want to book an appointment on ${info.startStr}?`);
            if (confirmed) {
                window.location.href = `/auth/book?date=${info.startStr}`;
            }
        },
        eventDidMount: function(info) {
            // Color and label handled by backend, but you can override here if needed
            const isAvailable = info.event.extendedProps.is_available;
            const isBooked = info.event.extendedProps.isBooked;
            const type = info.event.extendedProps.type;

            if (type === 'unavailability' || isAvailable === 0) {
                info.el.style.backgroundColor = '#dc3545'; // Red for unavailable
                info.el.style.borderColor = '#dc3545';
                var titleEl = info.el.querySelector('.fc-event-title');
                if (titleEl) titleEl.textContent = 'Unavailable';
            }
            if (isBooked) {
                info.el.style.backgroundColor = '#6c757d'; // Gray for booked
                info.el.style.borderColor = '#6c757d';
                var titleEl = info.el.querySelector('.fc-event-title');
                if (titleEl) titleEl.textContent = 'Booked';
            }
            if (type === 'availability' && isAvailable === 1 && !isBooked) {
                info.el.style.backgroundColor = '#17a2b8'; // Blue for available
                info.el.style.borderColor = '#17a2b8';
                var titleEl = info.el.querySelector('.fc-event-title');
                if (titleEl) titleEl.textContent = 'Available';
            }
        }
    });

    calendar.render();
});
