<?php include VIEW_PATH . '/partials/header.php'; ?>
<style>
/* Calendar styles */
.calendar-container {
  padding: 1.5rem;
  min-height: 650px;
}

.fc-event {
  cursor: pointer;
  padding: 2px 4px;
}

.regular-availability {
    background-color: #28a745 !important; /* Green */
    border: none !important;
}
.fc-event-title {
  font-weight: bold;
}

.fc-daygrid-day-number {
  font-weight: bold;
}

/* Make current day highlighted */
.fc-day-today {
  background-color: rgba(0, 123, 255, 0.1) !important;
}
</style>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body p-4">
                    <h2 class="text-primary mb-2">
                        <i class="fas fa-calendar-alt"></i> Manage Your Schedule
                    </h2>
                    <p class="text-muted">Set your availability and view upcoming appointments.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Forms Section -->
    <div class="row g-4 mb-4">
        <!-- Availability Update Form -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="fas fa-clock me-2"></i>
                    <h5 class="mb-0">Update Availability</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('index.php/provider/processUpdateAvailability') ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Date:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="date" class="form-control" name="availability_date" required min="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Start Time:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hourglass-start"></i></span>
                                    <input type="time" class="form-control" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">End Time:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hourglass-end"></i></span>
                                    <input type="time" class="form-control" name="end_time" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Availability Status:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                <select class="form-select" name="is_available">
                                    <option value="1">Available</option>
                                    <option value="0">Unavailable</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Availability
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recurring Schedule Form -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white d-flex align-items-center">
                    <i class="fas fa-sync me-2"></i>
                    <h5 class="mb-0">Set Recurring Availability</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('index.php/provider/processRecurringSchedule') ?>">
                        <div class="mb-3">
                        <label class="form-label fw-bold">Day of Week:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                            <select class="form-select" name="day_of_week" required>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                                <option value="0">Sunday</option>
                            </select>
                        </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Start Time:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hourglass-start"></i></span>
                                    <input type="time" class="form-control" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">End Time:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hourglass-end"></i></span>
                                    <input type="time" class="form-control" name="end_time" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                <select class="form-select" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="repeat_weekly" name="repeat_weekly">
                                <label class="form-check-label" for="repeat_weekly">
                                Repeat weekly
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="repeat_until" class="form-label fw-bold">Repeat until:</label>
                            <input type="date" class="form-control" id="repeat_until" name="repeat_until" min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Save Recurring Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <h5 class="mb-0">Your Schedule Calendar</h5>
                    </div>
                    <div id="calendar-loading" class="spinner-border spinner-border-sm text-light d-none" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="card-body calendar-container p-3">
                    <!-- Calendar Tips -->
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex">
                            <i class="fas fa-info-circle text-info me-2 mt-1"></i>
                            <div>
                                <strong>Tips:</strong> 
                                <ul class="mb-0 ps-3 mt-1">
                                    <li>Drag events to reschedule</li>
                                    <li>Click an event to delete it</li>
                                    <li>Events shown in blue are your scheduled availability</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Debug Info (only shown during development) -->
                    <div id="debug-info" class="alert alert-light border small d-none mb-3"></div>
                    <!-- Calendar will be rendered here -->
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap & FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show loading indicator
    document.getElementById('calendar-loading').classList.remove('d-none');
    
    var calendarEl = document.getElementById('calendar');

    var selectedDuration = 30; // Default service duration
    
    // Debug provider ID
    console.log("Provider ID being used:", <?= json_encode($provider_id ?? $_SESSION['user_id'] ?? 'undefined') ?>);

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 650,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        themeSystem: 'bootstrap5',
        aspectRatio: 1.35,
        contentHeight: "auto",
        editable: true,
        selectable: true,
        nowIndicator: true,
        dayMaxEvents: true,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },
        eventDisplay: 'block',
        eventSources: [
            {
                url: "<?= base_url('index.php/provider/getProviderSchedules') ?>",
                method: "GET",
                color: '#17a2b8', // info color
                textColor: 'white',
                failure: function() {
                    showNotification("Failed to load provider schedules", "danger");
                }
            }
        ],
        eventDidMount: function(info) {
            // Optionally add tooltips or other visual indicators
            if (info.event.classNames.includes('available-slot')) {
                // This is an individual available slot
                $(info.el).tooltip({
                    title: 'Available for booking',
                    placement: 'top'
                });
            }
        },
        eventResize: function(info) {
            updateAvailability(info.event);
        },
        eventDrop: function(info) {
            updateAvailability(info.event);
        },
        eventClick: function(info) {
            if (confirm("Do you want to remove this availability slot?")) {
                const eventId = info.event.id;
                const eventType = info.event.extendedProps?.type || 'regular';

                let endpoint = "<?= base_url('index.php/provider/deleteSchedule/') ?>" + eventId;

                fetch(endpoint, {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        type: eventType
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        info.event.remove();
                        showNotification("Availability removed successfully", "success");
                    } else {
                        showNotification("Failed to remove availability: " + (data.message || "Unknown error"), "danger");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    showNotification("An error occurred while removing availability", "danger");
                });
            }
        },
        loading: function(isLoading) {
            if (isLoading) {
                console.log("Calendar is loading events...");
            } else {
                // Hide loading indicator when calendar is loaded
                document.getElementById('calendar-loading')?.classList.add('d-none');
                console.log("Calendar finished loading events");
            }
            }
            });

          // Debug provider availability fetching
          fetch("<?= base_url('index.php/provider/getProviderSchedules') ?>")
          .then(response => response.json())
          .then(data => {
              console.log("Provider Schedules Data:", data);
              console.log("Total events displayed:", calendar.getEvents().length);

              // Show debug info during development
              if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                  const debugInfo = document.getElementById('debug-info');
                  if (debugInfo) {
                      debugInfo.classList.remove('d-none');
                      debugInfo.innerHTML = `<strong>Debug:</strong> Found ${data.length} availability slots`;
                  }
              }

              if (data.length === 0) {
                  console.warn("No provider schedules found! Check backend response.");
              }
          })
          .catch(error => {
              console.error("Error fetching provider schedules:", error);
              showNotification("Error loading your schedule data", "danger");
          });

          // Render calendar
          calendar.render();

          // Debug: Log event count after rendering
          setTimeout(function() {
              console.log("Total events displayed:", calendar.getEvents().length);
          }, 1000);

          // Function to update availability
          function updateAvailability(event) {
              const eventId = event.id;
              const eventType = event.extendedProps?.type || 'regular';

              // Format start and end times
              const startStr = event.start.toISOString();
              const endStr = event.end ? event.end.toISOString() :
                           new Date(event.start.getTime() + 30*60000).toISOString(); // Default 30 mins

              const updatedData = {
                  id: eventId,
                  type: eventType,
                  date: startStr.split('T')[0],
                  start_time: startStr.split('T')[1].substring(0, 5),
                  end_time: endStr.split('T')[1].substring(0, 5)
              };

              console.log("Updating event:", updatedData);
              fetch("<?= base_url('index.php/provider/updateSchedule') ?>", {
                  method: "POST",
                  body: JSON.stringify(updatedData),
                  headers: {
                      "Content-Type": "application/json",
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              })
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      showNotification("Availability updated successfully", "success");
                  } else {
                      showNotification("Failed to update availability: " + (data.message || "Unknown error"), "danger");
                      calendar.refetchEvents(); // Reload original events
                  }
              })
              .catch(error => {
                  console.error("Error:", error);
                  showNotification("An error occurred while updating availability", "danger");
                  calendar.refetchEvents(); // Reload original events
              });
          }

          // Function to show notification
          function showNotification(message, type = 'info') {
              // For modern browsers with support for notifications
              if ('Notification' in window && Notification.permission === 'granted') {
                  new Notification('Calendar Update', {
                      body: message
                  });
              }

              const notificationHtml = `
                  <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                      ${message}
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
              `;

              // Check if notification container exists, if not create it
              let notificationContainer = document.getElementById('notification-container');
              if (!notificationContainer) {
                  notificationContainer = document.createElement('div');
                  notificationContainer.id = 'notification-container';
                  notificationContainer.style.position = 'fixed';
                  notificationContainer.style.top = '20px';
                  notificationContainer.style.right = '20px';
                  notificationContainer.style.zIndex = '9999';
                  notificationContainer.style.maxWidth = '350px';
                  document.body.appendChild(notificationContainer);
              }

              // Add notification to container
              const notificationElement = document.createElement('div');
              notificationElement.innerHTML = notificationHtml;
              notificationContainer.appendChild(notificationElement);

              // Auto dismiss after 5 seconds
              setTimeout(() => {
                  notificationElement.querySelector('.alert').classList.remove('show');
                  setTimeout(() => {
                      if (notificationElement.parentNode) {
                          notificationElement.parentNode.removeChild(notificationElement);
                      }
                  }, 500); // Wait for fade out animation
              }, 5000);
          }

          // Add event listeners for form submissions to provide feedback
          document.querySelectorAll('form').forEach(form => {
              const formAction = form.getAttribute('action') || '';

              if (formAction.includes('processUpdateAvailability') || formAction.includes('processRecurringSchedule')) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formType = this.querySelector('button[type="submit"]')?.textContent.trim();
                    const loadingBtn = this.querySelector('button[type="submit"]');
                    let originalBtnText = ""; // <-- Declare outside

                    // Show loading state on button
                    if (loadingBtn) {
                        originalBtnText = loadingBtn.innerHTML; // <-- Assign here
                        loadingBtn.disabled = true;
                        loadingBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                    }


                    const formData = new FormData(this);

                    // If this is the recurring schedule form, append repeat fields
                    if (formAction.includes('processRecurringSchedule')) {
                        const repeatWeekly = this.querySelector('#repeat_weekly')?.checked ? '1' : '0';
                        const repeatUntil = this.querySelector('#repeat_until')?.value || '';
                        formData.append('repeat_weekly', repeatWeekly);
                        formData.append('repeat_until', repeatUntil);
                    }

                      fetch(this.action, {
                          method: 'POST',
                          body: formData
                      })
                      .then(response => response.json())
                      .then(data => {
                          if (data.success) {
                              showNotification("Operation completed successfully!", "success");
                              calendar.refetchEvents(); // Refresh calendar
                              this.reset(); // Reset form
                          } else {
                              showNotification("Operation failed: " + (data.message || "Unknown error"), "danger");
                          }

                          // Reset button state
                          if (loadingBtn) {
                              loadingBtn.innerHTML = originalBtnText;
                              loadingBtn.disabled = false;
                          }
                      })
                      .catch(error => {
                          console.error("Error:", error);
                          showNotification("An error occurred. Please try again.", "danger");

                          // Reset button state
                          if (loadingBtn) {
                              loadingBtn.innerHTML = originalBtnText;
                              loadingBtn.disabled = false;
                          }
                      });
                  });
              }
          });

          // Function to refresh calendar events
          function refreshCalendar() {
              calendar.refetchEvents();
              showNotification("Calendar refreshed", "info");
          }

          // Add a refresh button to the calendar header
          const refreshButton = document.createElement('button');
          refreshButton.className = 'btn btn-sm btn-light ms-2';
          refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i>';
          refreshButton.setAttribute('title', 'Refresh calendar');
          refreshButton.addEventListener('click', refreshCalendar);

          document.querySelector('.fc-toolbar-chunk:last-child').appendChild(refreshButton);

          // Event listeners for window focus to refresh calendar
          window.addEventListener('focus', function() {
              // Refresh calendar when window gets focus after 1 second delay
              setTimeout(function() {
                  calendar.refetchEvents();
              }, 1000);
          });

          // Handle errors for better user experience
          window.addEventListener('error', function(e) {
              console.error('Global error:', e.message);
              showNotification("An error occurred in the page. Please reload if calendar doesn't appear correctly.", "warning");
          });

          // Improve mobile experience
          function updateCalendarHeightForMobile() {
              if (window.innerWidth < 768) {
                  calendar.setOption('height', 500); // Lower height on mobile
              } else {
                  calendar.setOption('height', 650); // Default height
              }
              calendar.updateSize();
          }

          // Call on load and when window resizes
          updateCalendarHeightForMobile();
          window.addEventListener('resize', updateCalendarHeightForMobile);
          });</script>

          <!-- Additional Scripts for Better UX -->
          <script>
          // Form validation enhancement
          document.addEventListener('DOMContentLoaded', function() {
              // Validate that end time is after start time
              const startTimeInputs = document.querySelectorAll('input[name="start_time"]');
              const endTimeInputs = document.querySelectorAll('input[name="end_time"]');

              for (let i = 0; i < startTimeInputs.length; i++) {
                  const startInput = startTimeInputs[i];
                  const endInput = endTimeInputs[i];

                  const validateTimes = function() {
                      if (startInput.value && endInput.value) {
                          if (endInput.value <= startInput.value) {
                              endInput.setCustomValidity('End time must be after start time');
                          } else {
                              endInput.setCustomValidity('');
                          }
                      }
                  };

                  startInput.addEventListener('change', validateTimes);
                  endInput.addEventListener('change', validateTimes);
              }

              // Set a minimum date for date inputs to prevent selecting past dates
              const dateInputs = document.querySelectorAll('input[type="date"]');
              const today = new Date().toISOString().split('T')[0];

              dateInputs.forEach(input => {
                  input.min = today;
              });
          });
          </script>
<?php include VIEW_PATH . '/partials/footer.php'; ?>