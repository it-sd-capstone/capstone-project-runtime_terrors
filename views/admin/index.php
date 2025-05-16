<?php
/**
 * Admin Dashboard
 * This file displays the main administrator dashboard with statistics,
 * charts, and management tools.
 */
include VIEW_PATH . '/partials/header.php';
?>

<div class="admin-dashboard">
  <!-- Dashboard Header -->
  <header class="dashboard-header mb-4">
    <div class="alert alert-info">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="h4 mb-0">Welcome to the Admin Dashboard</h1>
          <p class="mb-0">You have access to manage users, appointments, and services.</p>
        </div>
        <?php if (!empty($stats['totalUsers'])): ?>
        <div class="text-end">
          <span class="badge bg-dark">Last updated: <?= date('M j, g:i A', time()) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main>
    <!-- Main Action Cards -->
    <section class="dashboard-cards mb-4" id="main-action-cards">
      <div class="row g-3">
        <!-- Users Card -->
        <div class="col-md-4">
          <div class="card h-100 border-primary border-top-0 border-end-0 border-bottom-0 border-3">
            <div class="card-body">
              <h2 class="card-title h5">
                <i class="bi bi-people-fill me-2"></i>Users
              </h2>
              <p class="card-text">Manage system users including patients, providers, and administrators.</p>
              <div class="d-grid">
                <a href="<?= base_url('index.php/admin/users') ?>" class="btn btn-primary">Manage Users</a>
              </div>
            </div>
            <div class="card-footer">
              <small class="text-muted">Total Users: <?= $stats['totalUsers'] ?? 0 ?></small>
            </div>
          </div>
        </div>
        
        <!-- Appointments Card -->
        <div class="col-md-4">
          <div class="card h-100 border-success border-top-0 border-end-0 border-bottom-0 border-3">
            <div class="card-body">
              <h2 class="card-title h5">
                <i class="bi bi-calendar-check me-2"></i>Appointments
              </h2>
              <p class="card-text">View and manage all appointments in the system.</p>
              <div class="d-grid">
                <a href="<?= base_url('index.php/admin/appointments') ?>" class="btn btn-success">Manage Appointments</a>
              </div>
            </div>
            <div class="card-footer">
              <small class="text-muted">Total Appointments: <?= $stats['totalAppointments'] ?? 0 ?></small>
            </div>
          </div>
        </div>
        
        <!-- Services Card -->
        <div class="col-md-4">
          <div class="card h-100 border-info border-top-0 border-end-0 border-bottom-0 border-3">
            <div class="card-body">
              <h2 class="card-title h5">
                <i class="bi bi-gear-fill me-2"></i>Services
              </h2>
              <p class="card-text">Manage available services and their details.</p>
              <div class="d-grid">
                <a href="<?= base_url('index.php/admin/services') ?>" class="btn btn-info">Manage Services</a>
              </div>
            </div>
            <div class="card-footer">
              <small class="text-muted">Total Services: <?= $stats['totalServices'] ?? 0 ?></small>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Statistics Section -->
    <section class="dashboard-statistics mb-4" id="system-statistics">
      <div class="card">
        <div class="card-header bg-light">
          <h2 class="h5 mb-0">System Statistics</h2>
        </div>
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-6 service-stats">
              <h3 class="h6">User Distribution</h3>
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Patients
                  <span class="badge bg-primary rounded-pill"><?= $stats['totalPatients'] ?? 0 ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Providers
                  <span class="badge bg-success rounded-pill"><?= $stats['totalProviders'] ?? 0 ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Administrators
                  <span class="badge bg-danger rounded-pill"><?= $stats['totalAdmins'] ?? 0 ?></span>
                </li>
              </ul>
            </div>

            <div class="col-md-6 provider-stats">
              <h3 class="h6">Appointment Status</h3>
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Scheduled
                  <span class="badge bg-info rounded-pill"><?= $stats['scheduledAppointments'] ?? 0 ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Confirmed
                  <span class="badge bg-warning rounded-pill"><?= $stats['confirmedAppointments'] ?? 0 ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Completed
                  <span class="badge bg-success rounded-pill"><?= $stats['completedAppointments'] ?? 0 ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Canceled
                  <span class="badge bg-danger rounded-pill"><?= $stats['canceledAppointments'] ?? 0 ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  No Show
                  <span class="badge bg-secondary rounded-pill"><?= $stats['noShowAppointments'] ?? 0 ?></span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Service & Provider Metrics -->
    <section class="dashboard-metrics mb-4" id="metrics-section">
      <div class="row g-4">
        <!-- Service Usage Metrics -->
        <div class="col-md-6 service-stats">
          <div class="card h-100 border-info border-top-0 border-end-0 border-bottom-0 border-3">
            <div class="card-header bg-light">
              <h2 class="h5 mb-0">Service Usage Metrics</h2>
            </div>
            <div class="card-body">
              <?php if (!empty($stats['topServices'])): ?>
                <h3 class="h6">Top Used Services</h3>
                <ul class="list-group">
                  <?php foreach ($stats['topServices'] as $service): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <?= htmlspecialchars($service['name']) ?>
                      <span class="badge bg-info rounded-pill"><?= $service['usage_count'] ?> appointments</span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="text-muted">No service usage data available yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <!-- Provider Availability Summary -->
        <div class="col-md-6 provider-stats">
          <div class="card h-100 border-success border-top-0 border-end-0 border-bottom-0 border-3">
            <div class="card-header bg-light">
              <h2 class="h5 mb-0">Provider Availability</h2>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <h3 class="h6">Booking Status</h3>
                <div class="progress mb-2">
                  <div class="progress-bar bg-success" role="progressbar"
                       style="width: <?= $stats['availabilityRate'] ?? 0 ?>%;"
                       aria-valuenow="<?= $stats['availabilityRate'] ?? 0 ?>"
                       aria-valuemin="0" aria-valuemax="100">
                    <?= $stats['availabilityRate'] ?? 0 ?>%
                  </div>
                </div>
                <small class="text-muted">
                  <?= $stats['bookedSlots'] ?? 0 ?> booked out of <?= $stats['totalAvailableSlots'] ?? 0 ?> available slots
                </small>
              </div>
              
              <?php if (!empty($stats['topProviders'])): ?>
                <h3 class="h6">Top Providers by Appointments</h3>
                <ul class="list-group">
                  <?php foreach ($stats['topProviders'] as $provider): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <?= htmlspecialchars($provider['provider_name']) ?>
                      <span class="badge bg-success rounded-pill">
                        <?= $provider['appointment_count'] ?> appointments
                      </span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="text-muted">No provider booking data available yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Analytics Section -->
    <section class="dashboard-analytics mb-4" id="analytics-section">
      <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h2 class="h5 mb-0">Appointment Analytics</h2>
          <div class="btn-group" role="group" aria-label="Time period selection">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-weekly">Weekly</button>
            <button type="button" class="btn btn-sm btn-outline-secondary active" id="btn-monthly">Monthly</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-yearly">Yearly</button>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-6">
              <canvas id="appointmentStatusChart" height="250" aria-label="Appointment status chart" role="img"></canvas>
            </div>
            <div class="col-md-6">
              <canvas id="appointmentTrendsChart" height="250" aria-label="Appointment trends chart" role="img"></canvas>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Notifications Section -->
    <section class="dashboard-notifications mb-4" id="notifications-section">
      <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h2 class="h5 mb-0">System Notifications</h2>
          <button class="btn btn-sm btn-outline-secondary refresh-notifications">
            <i class="bi bi-arrow-clockwise"></i> Refresh
          </button>
        </div>
        <div class="card-body">
          <div id="notifications-container">
            <p class="text-center text-muted">Loading notifications...</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Activity Log -->
    <section class="dashboard-activity mb-4" id="activity-section">
      <div class="card">
        <div class="card-header bg-light">
          <h2 class="h5 mb-0">Recent Activity</h2>
        </div>
        <div class="card-body">
          <?php if (empty($stats['recentActivity'])): ?>
            <p class="text-muted">No recent activity to display.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">Time</th>
                    <th scope="col">User</th>
                    <th scope="col">Category</th>
                    <th scope="col">Activity</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($stats['recentActivity'] as $activity): ?>
                  <tr>
                    <td><?= date('M d, H:i', strtotime($activity['created_at'])) ?></td>
                    <td><?= $activity['user_id'] ? htmlspecialchars($activity['user_name'] ?? 'Unknown User') : 'System' ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($activity['category'] ?? 'general') ?></span></td>
                    <td><?= htmlspecialchars($activity['description']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Quick Actions & Preferences -->
    <section class="dashboard-actions mb-4" id="actions-section">
      <div class="row g-4">
        <!-- Quick Actions -->
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header bg-light">
              <h2 class="h5 mb-0">Quick Actions</h2>
            </div>
            <div class="card-body">
              <div class="d-flex flex-wrap gap-2">
                               <a href="<?= base_url('index.php/admin/users/create') ?>" class="btn btn-outline-primary">
                  <i class="bi bi-person-plus"></i> Add New User
                </a>
                <a href="<?= base_url('index.php/admin/addProvider') ?>" class="btn btn-outline-success">
                  <i class="bi bi-person-badge"></i> Add New Provider
                </a>
                <a href="<?= base_url('index.php/admin/services/create') ?>" class="btn btn-outline-info">
                  <i class="bi bi-plus-circle"></i> Add New Service
                </a>
                <a href="<?= base_url('index.php/admin/appointments/create') ?>" class="btn btn-outline-secondary">
                  <i class="bi bi-calendar-plus"></i> Schedule Appointment
                </a>
                <!-- <a href="<?= base_url('index.php/admin/reports') ?>" class="btn btn-outline-dark">
                  <i class="bi bi-file-earmark-bar-graph"></i> View Reports
                </a> -->
              </div>
            </div>
          </div>
        </div>
        
      <!-- Dashboard Customization -->
        <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-light">
            <h2 class="h5 mb-0">Dashboard Customization</h2>
            </div>
            <div class="card-body">
            <form id="widget-preferences-form">
                <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                    <label class="form-check-label me-3" for="widget-appointments">Appointment Status & Analytics</label>
                    <input class="form-check-input" type="checkbox" id="widget-appointments" checked>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                    <label class="form-check-label me-3" for="widget-notifications">System Notifications</label>
                    <input class="form-check-input" type="checkbox" id="widget-notifications" checked>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                    <label class="form-check-label me-3" for="widget-providers">Provider Availability</label>
                    <input class="form-check-input" type="checkbox" id="widget-providers" checked>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                    <label class="form-check-label me-3" for="widget-services">Service Usage Metrics</label>
                    <input class="form-check-input" type="checkbox" id="widget-services" checked>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Preferences
                    </button>
                    <button type="button" id="reset-dashboard" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset to Default
                    </button>
                </div>
                </div>
            </form>
            </div>
        </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Test Container -->
  <?php include VIEW_PATH . '/admin/test_container.php'; ?>
</div>

<footer class="container-fluid mt-4 p-3 bg-light border-top">
  <?php include VIEW_PATH . '/partials/footer.php'; ?>
</footer>

<!-- Add Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Dashboard Enhancement Scripts -->
<script>
/** * Admin Dashboard JavaScript * Handles charts, notifications, and widget preferences */
document.addEventListener('DOMContentLoaded', function() {
  // Chart initialization
  initializeCharts();
 
  // Track if notifications are already being loaded to prevent duplicates
  window.isLoadingNotifications = false;
 
  // Load notifications
  loadNotifications();
 
  // Set up refresh timer for notifications
  setInterval(loadNotifications, 60000); // Refresh every minute
 
  // Event listeners for chart time period buttons
  document.getElementById('btn-weekly').addEventListener('click', function() {
    updateChartTimePeriod('weekly');
    this.blur(); // Remove focus after click
  });
 
  document.getElementById('btn-monthly').addEventListener('click', function() {
    updateChartTimePeriod('monthly');
    this.blur(); // Remove focus after click
  });
 
  document.getElementById('btn-yearly').addEventListener('click', function() {
    updateChartTimePeriod('yearly');
    this.blur(); // Remove focus after click
  });
 
  // Event listener for notification refresh button
  const refreshButton = document.querySelector('.refresh-notifications');
  if (refreshButton) {
    refreshButton.addEventListener('click', function() {
      loadNotifications();
      this.blur(); // Remove focus after click
    });
  }
 
  // Widget preferences form submission
  const preferencesForm = document.getElementById('widget-preferences-form');
  if (preferencesForm) {
    preferencesForm.addEventListener('submit', function(e) {
      e.preventDefault();
      saveWidgetPreferences();
    });
  }
 
  // Reset dashboard button
  const resetButton = document.getElementById('reset-dashboard');
  if (resetButton) {
    resetButton.addEventListener('click', function() {
      if (confirm('Reset all dashboard preferences to default settings?')) {
        resetWidgetPreferences();
      }
      this.blur(); // Remove focus after click
    });
  }
 
  // Apply saved widget preferences on page load
  applySavedWidgetPreferences();
});

/** * Initialize the dashboard charts */
function initializeCharts() {
    // Appointment Status Chart
    const statusCtx = document.getElementById('appointmentStatusChart');
    if (!statusCtx) return;
   
    const statusChart = new Chart(statusCtx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Scheduled', 'Confirmed', 'Completed', 'Canceled', 'No Show'],
            datasets: [{
                data: [0, 0, 0, 0, 0], // Will be populated with real data
                backgroundColor: [
                    '#36a2eb',  // Blue for scheduled
                    '#ffcd56',  // Yellow for confirmed
                    '#4bc0c0',  // Teal for completed
                    '#ff6384',  // Red for canceled
                    '#9966ff'   // Purple for no show
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                title: {
                    display: true,
                    text: 'Appointment Status Distribution'
                }
            }
        }
    });
   
    // Appointment Trends Chart
    const trendsCtx = document.getElementById('appointmentTrendsChart');
    if (!trendsCtx) return;
   
    const trendsChart = new Chart(trendsCtx.getContext('2d'), {
        type: 'line',
        data: {
            labels: [], // Will be populated with real data
            datasets: [{
                label: 'Appointments',
                data: [], // Will be populated with real data
                fill: false,
                borderColor: '#4bc0c0',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Appointment Trends (Monthly)'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
   
    // Store chart references globally for later updates
    window.adminCharts = {
        statusChart: statusChart,
        trendsChart: trendsChart
    };
   
    // Load initial data
    updateChartTimePeriod('monthly');
}

/** * Update chart data based on selected time period */
function updateChartTimePeriod(period) {
    // Update active button state
    document.querySelectorAll('#btn-weekly, #btn-monthly, #btn-yearly').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById('btn-' + period).classList.add('active');
   
    // Show loading state
    const trendsCtx = document.getElementById('appointmentTrendsChart');
    if (trendsCtx) {
        trendsCtx.style.opacity = 0.5;
    }
   
    // Fetch data from API
    fetch('<?= base_url("index.php/admin/getAppointmentAnalytics/") ?>' + period)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to load appointment analytics data');
                return;
            }
               
            // Update chart title based on period
            let titleText = 'Appointment Trends';
            switch(period) {
                case 'weekly':
                    titleText += ' (Weekly)';
                    break;
                case 'yearly':
                    titleText += ' (Yearly)';
                    break;
                default:
                    titleText += ' (Monthly)';
            }
               
            // Update appointment trends chart
            if (window.adminCharts && window.adminCharts.trendsChart) {
                window.adminCharts.trendsChart.data.labels = data.trends.labels;
                window.adminCharts.trendsChart.data.datasets[0].data = data.trends.data;
                window.adminCharts.trendsChart.options.plugins.title.text = titleText;
                window.adminCharts.trendsChart.update();
            }
               
            // Update appointment status chart
            if (window.adminCharts && window.adminCharts.statusChart) {
                window.adminCharts.statusChart.data.labels = data.status.labels.map(label =>
                    label.charAt(0).toUpperCase() + label.slice(1) // Capitalize first letter
                );
                window.adminCharts.statusChart.data.datasets[0].data = data.status.data;
                window.adminCharts.statusChart.update();
            }
               
            // Restore opacity
            if (trendsCtx) {
                trendsCtx.style.opacity = 1;
            }
        })
        .catch(error => {
            console.error('Error fetching appointment analytics:', error);
            // Restore opacity
            if (trendsCtx) {
                trendsCtx.style.opacity = 1;
            }
        });
}

/** * Load system notifications */
function loadNotifications() {
  // Prevent multiple simultaneous calls
  if (window.isLoadingNotifications) {
    console.log('Notifications already being loaded, skipping duplicate call');
    return;
  }
 
  window.isLoadingNotifications = true;
 
  // Display loading state
  const container = document.getElementById('notifications-container');
  if (!container) {
    window.isLoadingNotifications = false;
    return;
  }
 
  container.innerHTML = `
    <div class="d-flex justify-content-center py-3">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  `;
 
  // Fetch real notifications from the API
  fetch('<?= base_url('index.php/notification/getAdminNotifications') ?>')
    .then(response => response.json())
    .then(data => {
      if (!data.success) {
        container.innerHTML = `
          <div class="alert alert-warning">
            ${data.error || 'Failed to load notifications'}
          </div>
        `;
        window.isLoadingNotifications = false;
        return;
      }
       
      // Filter out duplicate notifications
      let notifications = data.notifications;
      const uniqueNotifications = [];
      const messageSet = new Set();
       
      // Only keep the first occurrence of each message
      notifications.forEach(notification => {
        if (!messageSet.has(notification.message)) {
          messageSet.add(notification.message);
          uniqueNotifications.push(notification);
        }
      });
       
      notifications = uniqueNotifications;
       
      // Render notifications
      if (notifications.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-3">No notifications at this time.</p>';
      } else {
        container.innerHTML = notifications.map(notification => `
          <div class="alert alert-${notification.type || 'info'} d-flex justify-content-between align-items-center">
            <span>${notification.message}</span>
            <small class="text-muted">${notification.time || 'Just now'}</small>
          </div>
        `).join('');
      }
       
      // Update unread badge if it exists
      const unreadBadge = document.getElementById('unread-notifications-badge');
      if (unreadBadge && data.total_unread) {
        unreadBadge.textContent = data.total_unread;
        unreadBadge.style.display = data.total_unread > 0 ? 'inline-block' : 'none';
      }
       
      window.isLoadingNotifications = false;
    })
    .catch(error => {
      console.error('Error fetching notifications:', error);
      container.innerHTML = `
        <div class="alert alert-danger">
          Error loading notifications. Please try again later.
        </div>
      `;
      window.isLoadingNotifications = false;
    });
}

/** * Save dashboard widget visibility preferences */
function saveWidgetPreferences() {
  // Get widget visibility preferences
  const preferences = {
    appointments: document.getElementById('widget-appointments').checked,
    notifications: document.getElementById('widget-notifications').checked,
    providers: document.getElementById('widget-providers').checked,
    services: document.getElementById('widget-services').checked
  };
 
  // Save to localStorage
  localStorage.setItem('adminDashboardPreferences', JSON.stringify(preferences));
 
  // Apply changes
  applySavedWidgetPreferences();
 
  // Create and show toast notification
  showToastMessage('Dashboard preferences saved successfully!', 'success');
}

/** * Reset dashboard widget preferences to defaults */
function resetWidgetPreferences() {
  // Default is all widgets visible
  const defaultPreferences = {
    appointments: true,
    notifications: true,
    providers: true,
    services: true
  };
 
  // Update checkboxes to match defaults
  document.getElementById('widget-appointments').checked = true;
  document.getElementById('widget-notifications').checked = true;
  document.getElementById('widget-providers').checked = true;
  document.getElementById('widget-services').checked = true;
 
  // Save to localStorage
  localStorage.setItem('adminDashboardPreferences', JSON.stringify(defaultPreferences));
 
  // Apply changes
  applySavedWidgetPreferences();
 
  // Show success message
  showToastMessage('Dashboard reset to default settings!', 'info');
}

/** * Show a toast message */
function showToastMessage(message, type = 'success') {
  // Create toast container if it doesn't exist
  let toastContainer = document.getElementById('dynamic-toast-container');
 
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'dynamic-toast-container';
    toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
    toastContainer.style.zIndex = '11';
    document.body.appendChild(toastContainer);
  }
 
  // Create toast element
  const toastId = 'toast-' + Date.now();
  const backgroundColor = type === 'success' ? 'bg-success' :
                         type === 'info' ? 'bg-info' :
                         type === 'warning' ? 'bg-warning' : 'bg-danger';
 
  const toastHtml = `
    <div id="${toastId}" class="toast align-items-center ${backgroundColor} text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <i class="bi bi-check-circle-fill me-2"></i> ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;
 
  toastContainer.innerHTML += toastHtml;
   
  // Initialize and show the toast
    const toastElement = document.getElementById(toastId);
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
      const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
      toast.show();
       
      // Remove toast element after it's hidden
      toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
      });
    } else {
      // Fallback for when Bootstrap is not available
      toastElement.style.display = 'block';
      setTimeout(() => {
        toastElement.remove();
      }, 3000);
    }
}

/** * Apply saved widget preferences to the dashboard */
function applySavedWidgetPreferences() {
  // Get saved preferences, or use defaults if none saved
  let preferences;
  try {
    const saved = localStorage.getItem('adminDashboardPreferences');
    preferences = saved ? JSON.parse(saved) : {
      appointments: true,
      notifications: true,
      providers: true,
      services: true
    };
  } catch (e) {
    // If there's an error (e.g., invalid JSON), use defaults
    console.error('Error loading preferences:', e);
    preferences = {
      appointments: true,
      notifications: true,
      providers: true,
      services: true
    };
  }
 
  // Set checkbox states to match preferences
  const checkboxes = {
    'appointments': document.getElementById('widget-appointments'),
    'notifications': document.getElementById('widget-notifications'),
    'providers': document.getElementById('widget-providers'),
    'services': document.getElementById('widget-services')
  };
 
  Object.keys(checkboxes).forEach(key => {
    if (checkboxes[key]) {
      checkboxes[key].checked = preferences[key];
    }
  });
 
  // Map preference keys to actual dashboard section keywords
  const sectionMappings = {
    'appointments': ['Appointment Analytics', 'Appointment Status', 'appointments'],
    'notifications': ['System Notifications', 'notifications', 'alerts'],
    'providers': ['Provider Availability', 'Top Providers', 'Provider Stats'],
    'services': ['Service Usage Metrics', 'Top Used Services', 'Service Stats']
  };
 
  // Get all cards on the dashboard
  const cards = document.querySelectorAll('.card');
 
  // Track if each type of section has been found
  const sectionFound = {
    'appointments': false,
    'notifications': false,
    'providers': false,
    'services': false
  };
 
  // Process each card to identify its type and apply visibility
  cards.forEach(card => {
    // Always show the welcome/intro card and dashboard customization
    if (containsText(card, 'Welcome to the Admin Dashboard') ||
        containsText(card, 'Dashboard Customization') ||
        containsText(card, 'Quick Actions')) {
      card.style.display = 'block';
      return; // Skip further processing
    }
   
    // Check each preference type against this card
    Object.keys(sectionMappings).forEach(prefKey => {
      const keywords = sectionMappings[prefKey];
       
      // Check if any keyword matches this card
      const isMatchingSection = keywords.some(keyword =>
        containsText(card, keyword)
      );
       
      if (isMatchingSection) {
        // Mark this type of section as found
        sectionFound[prefKey] = true;
           
        // Apply visibility based on preferences
        card.style.display = preferences[prefKey] ? 'block' : 'none';
           
        // Also add appropriate classes for future reference
        card.classList.add('dashboard-section');
        card.classList.add(`${prefKey}-section`);
           
        // Add ID if missing
        if (!card.id) {
          card.id = `${prefKey}-section`;
        }
      }
    });
   
    // Special handling for sections that might not be clearly identified
   
    // Appointment section detection
    if (!sectionFound['appointments'] &&
        (card.querySelector('canvas[id*="appointment"]') || 
         containsText(card, 'Appointment'))) {
      card.style.display = preferences.appointments ? 'block' : 'none';
      card.classList.add('dashboard-section', 'appointments-section');
      sectionFound['appointments'] = true;
    }
   
    // Provider section detection
    if (!sectionFound['providers'] &&
        (containsText(card, 'Provider') ||
         containsText(card, 'Providers') ||
         containsText(card, 'Booking Status'))) {
      card.style.display = preferences.providers ? 'block' : 'none';
      card.classList.add('dashboard-section', 'providers-section');
      sectionFound['providers'] = true;
    }
   
    // Service section detection
    if (!sectionFound['services'] &&
        (containsText(card, 'Service') ||
         containsText(card, 'Services'))) {
      card.style.display = preferences.services ? 'block' : 'none';
      card.classList.add('dashboard-section', 'services-section');
      sectionFound['services'] = true;
    }
  });
 
  // If all sections are hidden, show a message
  const allSectionsHidden = !preferences.appointments &&
                           !preferences.notifications &&
                           !preferences.providers &&
                           !preferences.services;
 
  const noWidgetsMessage = document.getElementById('no-widgets-message');
  if (noWidgetsMessage) {
    noWidgetsMessage.style.display = allSectionsHidden ? 'block' : 'none';
  } else if (allSectionsHidden) {
    // Create message if it doesn't exist
    const message = document.createElement('div');
    message.id = 'no-widgets-message';
    message.className = 'alert alert-info text-center my-4';
    message.innerHTML = `
      <i class="bi bi-info-circle-fill me-2"></i>
      All dashboard widgets are currently hidden.
      Use the Dashboard Customization panel to show widgets.
    `;
   
    // Find best place to insert the message
    const dashboardContainer = document.querySelector('.dashboard-container, main.content, .content-wrapper');
    if (dashboardContainer) {
      // Try to insert after welcome section or at start of container
      const welcomeSection = Array.from(dashboardContainer.querySelectorAll('.card')).find(
        card => containsText(card, 'Welcome')
      );
       
      if (welcomeSection && welcomeSection.nextElementSibling) {
        dashboardContainer.insertBefore(message, welcomeSection.nextElementSibling);
      } else {
        // Insert at beginning of dashboard container
        dashboardContainer.insertBefore(message, dashboardContainer.firstChild);
      }
    } else {
      // Last resort - append to body
      document.body.appendChild(message);
    }
  }
 
  console.log('Applied widget preferences:', preferences);
  console.log('Sections found:', sectionFound);
}

/** * Find and tag dashboard sections with appropriate IDs and classes * Updated to match actual dashboard structure */
function findAndTagDashboardSections() {
  console.log('Finding and tagging dashboard sections...');
 
  // Get all cards on the dashboard
  const cards = document.querySelectorAll('.card');
 
  // Section keyword mappings
  const sectionMappings = {
    'appointments': ['Appointment Analytics', 'Appointment Status'],
    'notifications': ['System Notifications'],
    'providers': ['Provider Availability', 'Top Providers'],
    'services': ['Service Usage Metrics', 'Top Used Services']
  };
 
  // Tag each card based on content
  cards.forEach(card => {
    // Welcome/intro section
    if (containsText(card, 'Welcome') ||
        containsText(card, 'User Distribution') ||
        containsText(card, 'System Statistics')) {
      if (!card.id) card.id = 'welcome-section';
      card.classList.add('dashboard-section', 'welcome-section');
    }
   
    // Dashboard customization section
    if (containsText(card, 'Dashboard Customization') ||
        containsText(card, 'Quick Actions')) {
      if (!card.id) card.id = 'dashboard-controls-section';
      card.classList.add('dashboard-section', 'dashboard-controls');
    }
   
    // Tag each section type
    Object.keys(sectionMappings).forEach(sectionType => {
      const keywords = sectionMappings[sectionType];
       
      if (keywords.some(keyword => containsText(card, keyword))) {
        if (!card.id) card.id = `${sectionType}-section`;
        card.classList.add('dashboard-section', `${sectionType}-section`);
      }
    });
   
    // Specific checks for harder-to-identify sections
    if (!card.classList.contains('dashboard-section')) {
      // Check for appointment section
      if (card.querySelector('canvas[id*="appointment"]') ||
          containsText(card, 'Appointment')) {
        if (!card.id) card.id = 'appointments-section';
        card.classList.add('dashboard-section', 'appointments-section');
      }
       
      // Check for provider section
      else if (containsText(card, 'Provider') ||
               containsText(card, 'Booking Status')) {
        if (!card.id) card.id = 'providers-section';
        card.classList.add('dashboard-section', 'providers-section');
      }
       
      // Check for service section
      else if (containsText(card, 'Service')) {
        if (!card.id) card.id = 'services-section';
        card.classList.add('dashboard-section', 'services-section');
      }
       
      // Check for notifications/activity section
      else if (containsText(card, 'Notification') ||
               containsText(card, 'Activity') ||
               containsText(card, 'Alerts')) {
        if (!card.id) card.id = 'notifications-section';
        card.classList.add('dashboard-section', 'notifications-section');
      }
    }
  });
 
  // Apply preferences after tagging
  applySavedWidgetPreferences();
}

/** * Helper function to check if an element contains text */
function containsText(element, text) {
  if (!element || !text) return false;
 
  // Case-insensitive search
  const lowerText = text.toLowerCase();
 
  // First check the element's text content
  if (element.textContent && element.textContent.toLowerCase().includes(lowerText)) {
    return true;
  }
 
  // Check headings (h1-h6)
  const headings = element.querySelectorAll('h1, h2, h3, h4, h5, h6');
  for (let i = 0; i < headings.length; i++) {
    if (headings[i].textContent &&
        headings[i].textContent.toLowerCase().includes(lowerText)) {
      return true;
    }
  }
 
  // Check other important elements
  const keyElements = element.querySelectorAll('.card-title, .card-header, .section-title, .widget-title');
  for (let i = 0; i < keyElements.length; i++) {
    if (keyElements[i].textContent &&
        keyElements[i].textContent.toLowerCase().includes(lowerText)) {
      return true;
    }
  }
 
  return false;
}

/** * Update element visibility by ID */
function updateVisibilityById(id, isVisible) {
  const element = document.getElementById(id);
  if (element) {
    element.style.display = isVisible ? 'block' : 'none';
    return true;
  }
  return false;
}

/** * Update elements visibility by class name */
function updateVisibilityByClass(className, isVisible) {
  const elements = document.getElementsByClassName(className);
  let found = false;
  if (elements.length > 0) {
    for (let i = 0; i < elements.length; i++) {
      elements[i].style.display = isVisible ? 'block' : 'none';
      found = true;
    }
  }
  return found;
}

/** * Find widgets based on content and update visibility */
function findAndUpdateWidgetVisibility(preferences) {
  // Find cards with specific content
  const cards = document.querySelectorAll('.card');
 
  cards.forEach(card => {
    // Check for appointment analytics widgets
    if (preferences.appointments !== undefined &&
        (card.querySelector('canvas[id*="appointment"]') || 
         containsText(card, 'Appointment') && containsText(card, 'Analytics'))) {
      card.style.display = preferences.appointments ? 'block' : 'none';
    }
   
    // Check for notification widgets
    if (preferences.notifications !== undefined &&
        (card.querySelector('#notifications-container') || 
         containsText(card, 'Notifications'))) {
      card.style.display = preferences.notifications ? 'block' : 'none';
    }
   
    // Check for provider widgets
    if (preferences.providers !== undefined &&
        (card.querySelector('.provider-stats') || 
         card.querySelector('.provider-list') || 
         containsText(card, 'Provider'))) {
           card.style.display = preferences.providers ? 'block' : 'none';
    }
    
    // Check for service widgets
    if (preferences.services !== undefined &&
        (card.querySelector('.service-stats') || 
         card.querySelector('.service-list') || 
         containsText(card, 'Service'))) {
      card.style.display = preferences.services ? 'block' : 'none';
    }
  });
}

/** * Responsive dashboard layout handler */
window.addEventListener('resize', function() {
  // Check if we're on a mobile device (< 768px width)
  const isMobile = window.innerWidth < 768;
  
  // Adjust card layouts and chart sizes for mobile
  const charts = document.querySelectorAll('canvas');
  charts.forEach(chart => {
    if (chart.chart) {
      chart.chart.resize();
    }
  });
  
  // Adjust notification entries for mobile
  const notificationContainer = document.getElementById('notifications-container');
  if (notificationContainer) {
    if (isMobile) {
      notificationContainer.classList.add('notifications-mobile');
    } else {
      notificationContainer.classList.remove('notifications-mobile');
    }
  }
});

/** * Initialize the responsive layout */
function initResponsiveLayout() {
  // Initial responsive adjustments based on viewport size
  if (window.innerWidth < 768) {
    document.body.classList.add('mobile-view');
    
    // Simplify notifications on mobile
    const notificationContainer = document.getElementById('notifications-container');
    if (notificationContainer) {
      notificationContainer.classList.add('notifications-mobile');
    }
  }
}

// Call responsive layout initialization once DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
  initResponsiveLayout();
});
// FORCEFUL FIX for toggle tests panel
(function() {
  // Execute immediately when script loads
  function forceFixTestsPanel() {
    const toggleButton = document.querySelector('[data-bs-target="#collapseTests"]');
    const testsPanel = document.getElementById('collapseTests');
    
    if (!toggleButton || !testsPanel) return;
    
    // Remove Bootstrap data attributes to prevent their JS from working
    toggleButton.removeAttribute('data-bs-toggle');
    
    // Override the click handler
    toggleButton.onclick = function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      // Force toggle the panel state
      if (testsPanel.classList.contains('show')) {
        // Force close
        testsPanel.style.display = 'none';
        setTimeout(() => {
          testsPanel.classList.remove('show');
          testsPanel.style.display = '';
          toggleButton.setAttribute('aria-expanded', 'false');
          toggleButton.classList.add('collapsed');
        }, 10);
      } else {
        // Force open
        testsPanel.style.display = 'block';
        setTimeout(() => {
          testsPanel.classList.add('show');
          toggleButton.setAttribute('aria-expanded', 'true');
          toggleButton.classList.remove('collapsed');
        }, 10);
      }
      
      return false;
    };
    
    // Add direct event handler to close when clicking outside
    document.addEventListener('click', function(e) {
      if (testsPanel.classList.contains('show') && 
          !testsPanel.contains(e.target) && 
          e.target !== toggleButton) {
        testsPanel.classList.remove('show');
        toggleButton.setAttribute('aria-expanded', 'false');
        toggleButton.classList.add('collapsed');
      }
    });
  }
  
  // Run immediately
  forceFixTestsPanel();
  
  // Also run when DOM is fully loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', forceFixTestsPanel);
  }
  
  // Also run again after a short delay to override any other scripts
  setTimeout(forceFixTestsPanel, 500);
})();
// FORCEFUL FIX for accordion buttons inside test panel
(function() {
  function forceFixAccordionButtons() {
    // Get all accordion buttons, especially ones in the tests panel
    const accordionButtons = document.querySelectorAll('.accordion-button');
    
    accordionButtons.forEach(button => {
      // Remove Bootstrap data attributes to prevent their default behavior
      button.removeAttribute('data-bs-toggle');
      
      // Override the click handler
      button.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Get the target panel
        const targetId = button.getAttribute('data-bs-target');
        if (!targetId) return false;
        
        const targetPanel = document.querySelector(targetId);
        if (!targetPanel) return false;
        
        // Get parent accordion to handle closing other items
        const accordion = button.closest('.accordion');
        
        // Toggle the panel state
        if (targetPanel.classList.contains('show')) {
          // Force close this panel
          targetPanel.style.display = 'none';
          setTimeout(() => {
            targetPanel.classList.remove('show');
            targetPanel.style.display = '';
            button.setAttribute('aria-expanded', 'false');
            button.classList.add('collapsed');
          }, 10);
        } else {
          // Close all other panels in this accordion
          if (accordion) {
            // Get all other panels and buttons
            const allPanels = accordion.querySelectorAll('.accordion-collapse');
            const allButtons = accordion.querySelectorAll('.accordion-button');
            
            // Close them
            allPanels.forEach(panel => {
              if (panel !== targetPanel) {
                panel.classList.remove('show');
              }
            });
            
            allButtons.forEach(btn => {
              if (btn !== button) {
                btn.setAttribute('aria-expanded', 'false');
                btn.classList.add('collapsed');
              }
            });
          }
          
          // Force open this panel
          targetPanel.style.display = 'block';
          setTimeout(() => {
            targetPanel.classList.add('show');
            button.setAttribute('aria-expanded', 'true');
            button.classList.remove('collapsed');
          }, 10);
        }
        
        return false;
      };
    });
  }
  
  // Run immediately
  forceFixAccordionButtons();
  
  // Also run when DOM is fully loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', forceFixAccordionButtons);
  }
  
  // Also run again after a short delay to override any other scripts
  setTimeout(forceFixAccordionButtons, 500);
})();

</script>

<style>
/* Dashboard styles */
.dashboard-wrapper {
  padding: 1rem;
}

.card {
  transition: all 0.3s ease;
  margin-bottom: 1.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-header {
  font-weight: 600;
  background-color: rgba(0, 0, 0, 0.03);
}

/* Chart containers */
.chart-container {
  position: relative;
  height: 300px;
  margin: 0 auto;
}

/* Notification styles */
.notification-item {
  border-left: 4px solid #007bff;
  margin-bottom: 0.5rem;
  padding: 0.75rem;
  background-color: rgba(0, 123, 255, 0.05);
  transition: background-color 0.2s ease;
}

.notification-item:hover {
  background-color: rgba(0, 123, 255, 0.1);
}

.notification-item.unread {
  font-weight: 500;
  background-color: rgba(0, 123, 255, 0.1);
}

.notification-item.success {
  border-left-color: #28a745;
  background-color: rgba(40, 167, 69, 0.05);
}

.notification-item.warning {
  border-left-color: #ffc107;
  background-color: rgba(255, 193, 7, 0.05);
}

.notification-item.danger {
  border-left-color: #dc3545;
  background-color: rgba(220, 53, 69, 0.05);
}

/* Custom toggle switch for preferences */
.custom-switch {
  padding-left: 2.25rem;
}

.custom-control-input:checked ~ .custom-control-label::before {
  background-color: #28a745;
  border-color: #28a745;
}

/* Responsive styles for mobile */
@media (max-width: 767.98px) {
  .chart-container {
    height: 220px;
  }
  
  .card-title {
    font-size: 1.1rem;
  }
  
  .notifications-mobile .notification-item {
    padding: 0.5rem;
  }
  
  .notifications-mobile .notification-time {
    display: none;
  }
}

/* Toast notification styling */
.toast {
  opacity: 1 !important;
}

/* Quick action buttons */
.quick-action-btn {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  border-radius: 0.25rem;
  padding: 0.875rem;
  margin-bottom: 0.5rem;
  color: #495057;
  background-color: #f8f9fa;
  transition: all 0.2s ease;
  text-decoration: none;
}

.quick-action-btn:hover {
  background-color: #e9ecef;
  transform: translateY(-2px);
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  color: #212529;
  text-decoration: none;
}

.quick-action-btn i {
  font-size: 1.25rem;
  margin-right: 0.75rem;
  opacity: 0.8;
}

/* Dashboard section appear animation */
.dashboard-section {
  animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Preference toggle switch styling */
.pref-switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}

.pref-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.pref-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}

.pref-slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .pref-slider {
  background-color: #2196F3;
}

input:focus + .pref-slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .pref-slider:before {
  transform: translateX(26px);
}
</style>
 

