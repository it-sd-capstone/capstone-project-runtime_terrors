document.addEventListener("DOMContentLoaded", function() {
  var calendarEl = document.getElementById("calendar");

  var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "timeGridWeek",
      events: "/api/getAppointments",
      selectable: true,
      editable: false,
      headerToolbar: {
          left: "prev,next today",
          center: "title",
          right: "timeGridWeek,timeGridDay"
      },
      eventClick: function(info) {
          alert(`Appointment Details:\n${info.event.title}\nDate: ${info.event.startStr}`);
      },
      select: function(info) {
          let confirmed = confirm(`Do you want to book an appointment on ${info.startStr}?`);
          if (confirmed) {
              window.location.href = `/auth/book?date=${info.startStr}`;
          }
      }
  });

  calendar.render();
});