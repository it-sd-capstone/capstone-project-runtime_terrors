<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container admin-dashboard">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h4>Welcome to the Admin Dashboard</h4>
                <p>You have access to manage users, appointments, and services.</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Users Card -->
        <div class="col-md-4">
            <div class="card admin-card">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
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
            <div class="card admin-card">
                <div class="card-body">
                    <h5 class="card-title">Appointments</h5>
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
            <div class="card admin-card">
                <div class="card-body">
                    <h5 class="card-title">Services</h5>
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
    
    <!-- System Stats -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>System Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>User Distribution</h6>
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
                        <div class="col-md-6">
                            <h6>Appointment Status</h6>
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
        </div>
    </div>
    
    <!-- NEW SECTION: Service Usage and Provider Availability -->
    <div class="row mt-4">
        <!-- Service Usage Metrics -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Service Usage Metrics</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['topServices'])): ?>
                        <h6>Top Used Services</h6>
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Provider Availability</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Booking Status</h6>
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
                        <h6>Top Providers by Appointments</h6>
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
    
    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Activity</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($stats['recentActivity'])): ?>
                        <p class="text-muted">No recent activity to display.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>User</th>
                                        <th>Category</th>
                                        <th>Activity</th>
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
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-4 mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('index.php/admin/users/create') ?>" class="btn btn-outline-primary">Add New User</a>
                        <a href="<?= base_url('index.php/admin/addProvider') ?>" class="btn btn-outline-success">Add New Provider</a>
                        <a href="<?= base_url('index.php/admin/services/create') ?>" class="btn btn-outline-info">Add New Service</a>
                        <a href="<?= base_url('index.php/admin/reports') ?>" class="btn btn-outline-secondary">Generate Reports</a>
                        <a href="<?= base_url('index.php/admin/settings') ?>" class="btn btn-outline-dark">System Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Enhancements: Charts & Analytics -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Appointment Analytics</h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary" id="btn-weekly">Weekly</button>
                        <button class="btn btn-sm btn-outline-secondary active" id="btn-monthly">Monthly</button>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-yearly">Yearly</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="appointmentStatusChart" height="250"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="appointmentTrendsChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Notifications System -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>System Notifications</h5>
                    <button class="btn btn-sm btn-outline-secondary refresh-notifications">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div id="notifications-container">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customizable Widgets -->
    <div class="row mt-4">
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5>Dashboard Customization</h5>
                </div>
                <div class="card-body">
                    <form id="widget-preferences-form" class="row g-3">
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="widget-appointments" checked>
                                <label class="form-check-label" for="widget-appointments">Appointment Analytics</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="widget-notifications" checked>
                                <label class="form-check-label" for="widget-notifications">System Notifications</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="widget-providers" checked>
                                <label class="form-check-label" for="widget-providers">Provider Stats</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="widget-services" checked>
                                <label class="form-check-label" for="widget-services">Service Stats</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Save Preferences</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Add Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Dashboard Enhancement Scripts -->
<script>
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
    });
    
    document.getElementById('btn-monthly').addEventListener('click', function() {
        updateChartTimePeriod('monthly');
    });
    
    document.getElementById('btn-yearly').addEventListener('click', function() {
        updateChartTimePeriod('yearly');
    });
    
    // Event listener for notification refresh button
    document.querySelector('.refresh-notifications').addEventListener('click', loadNotifications);
    
    // Widget preferences form submission
    document.getElementById('widget-preferences-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveWidgetPreferences();
    });
    
    // Apply saved widget preferences
    applySavedWidgetPreferences();
});

function initializeCharts() {
    // Appointment Status Chart
    const statusCtx = document.getElementById('appointmentStatusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Scheduled', 'Confirmed', 'Completed', 'Canceled', 'No Show'],
            datasets: [{
                data: [
                    <?= $stats['scheduledAppointments'] ?? 0 ?>, 
                    <?= $stats['confirmedAppointments'] ?? 0 ?>, 
                    <?= $stats['completedAppointments'] ?? 0 ?>, 
                    <?= $stats['canceledAppointments'] ?? 0 ?>, 
                    <?= $stats['noShowAppointments'] ?? 0 ?>
                ],
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
    
    // Appointment Trends Chart - Initial with dummy data
    // Will be populated with real data via AJAX
    const trendsCtx = document.getElementById('appointmentTrendsChart').getContext('2d');
    const trendsChart = new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Appointments',
                data: [65, 59, 80, 81, 56, 55, 40, 45, 50, 62, 71, 85],
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
}

function updateChartTimePeriod(period) {
    // Update active button state
    document.querySelectorAll('#btn-weekly, #btn-monthly, #btn-yearly').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById('btn-' + period).classList.add('active');
    
    // Simulate chart data update based on time period
    // In a real implementation, this would fetch data from the server
    let labels, data, titleText;
    
    if (period === 'weekly') {
        labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        data = [12, 19, 15, 17, 14, 8, 6];
        titleText = 'Appointment Trends (Weekly)';
    } else if (period === 'monthly') {
        labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        data = [65, 59, 80, 81, 56, 55, 40, 45, 50, 62, 71, 85];
        titleText = 'Appointment Trends (Monthly)';
    } else { // yearly
        labels = ['2018', '2019', '2020', '2021', '2022', '2023', '2024', '2025'];
        data = [280, 310, 275, 190, 320, 365, 430, 510];
        titleText = 'Appointment Trends (Yearly)';
    }
    
    // Update chart data
    window.adminCharts.trendsChart.data.labels = labels;
    window.adminCharts.trendsChart.data.datasets[0].data = data;
    window.adminCharts.trendsChart.options.plugins.title.text = titleText;
    window.adminCharts.trendsChart.update();
}

function loadNotifications() {
    // Prevent multiple simultaneous calls
    if (window.isLoadingNotifications) {
        console.log('Notifications already being loaded, skipping duplicate call');
        return;
    }
    
    window.isLoadingNotifications = true;
    
    // Display loading state
    const container = document.getElementById('notifications-container');
    container.innerHTML = `
        <div class="d-flex justify-content-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Fetch real notifications from the API - Using the correct route format for this application
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
                container.innerHTML = '<p class="text-muted text-center">No notifications at this time.</p>';
            } else {
                container.innerHTML = notifications.map(notification => `
                    <div class="alert alert-${notification.type} d-flex justify-content-between align-items-center">
                        <span>${notification.message}</span>
                        <small class="text-muted">${notification.time}</small>
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
    
    // Show success message
    alert('Dashboard preferences saved successfully!');
}

function applySavedWidgetPreferences() {
    // Get saved preferences or use defaults
    let preferences;
    try {
        preferences = JSON.parse(localStorage.getItem('adminDashboardPreferences')) || {
            appointments: true,
            notifications: true,
            providers: true,
            services: true
        };
    } catch (e) {
        preferences = {
            appointments: true,
            notifications: true,
            providers: true,
            services: true
        };
    }
    
    // Apply to checkboxes
    document.getElementById('widget-appointments').checked = preferences.appointments;
    document.getElementById('widget-notifications').checked = preferences.notifications;
    document.getElementById('widget-providers').checked = preferences.providers;
    document.getElementById('widget-services').checked = preferences.services;
    
    // Apply to widget visibility
    document.querySelector('.row:has(#appointmentStatusChart)').closest('.row').style.display = 
        preferences.appointments ? 'flex' : 'none';
    document.querySelector('.row:has(#notifications-container)').closest('.row').style.display = 
        preferences.notifications ? 'flex' : 'none';
    
    // Note: For provider and service widgets, you'd need to identify those elements
    // Assuming they have specific IDs or classes to target
    try {
        document.querySelector('.row:has(.provider-stats)').style.display = 
            preferences.providers ? 'flex' : 'none';
        document.querySelector('.row:has(.service-stats)').style.display = 
            preferences.services ? 'flex' : 'none';
    } catch (e) {
        // Elements might not exist yet
        console.log('Some widgets not found');
    }
}
</script>

<!-- Test Container -->
<?php include VIEW_PATH . '/admin/test_container.php'; ?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>