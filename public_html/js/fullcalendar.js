document.addEventListener("DOMContentLoaded", function() {
  var calendarEl = document.getElementById("calendar");

  // Add this function
  function getCsrfToken() {
      return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  }

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
              /* Example if you were to add a direct appointment booking AJAX call:
              $.ajax({
                  url: "/api/bookAppointment",
                  type: "POST",
                  data: {
                      start: info.startStr,
                      end: info.endStr,
                      csrf_token: getCsrfToken() // Include the CSRF token
                  },
                  success: function(response) {
                      // Handle success
                  },
                  error: function(error) {
                      // Handle error
                  }
              }); */
          }
      }
  });

  calendar.render();
});