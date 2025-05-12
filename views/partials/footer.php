</div> 

<footer class="footer bg-dark text-white">
    <div class="container">
        <div class="row py-4">
            <div class="col-md-4">
                <h4 class="mb-4">Appointment System</h4>
                <p>Book and manage your healthcare appointments online, anytime, anywhere.</p>
            </div>
            
            <div class="col-md-2">
                <h5 class="mb-3">Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= base_url('index.php/home') ?>" class="text-white">Home</a></li>
                    <li class="mb-2">
                        <?php
                        // Dynamically set the Appointments link based on user role
                        $isLoggedIn = isset($_SESSION['user_id']) && ($_SESSION['logged_in'] ?? false) === true;
                        $userRole = $isLoggedIn ? ($_SESSION['role'] ?? 'guest') : 'guest';
                        $appointmentsUrl = base_url('index.php/appointments'); // Default for guests and patients
                        if ($isLoggedIn) {
                            if ($userRole === 'admin') {
                                $appointmentsUrl = base_url('index.php/admin/appointments');
                            } elseif ($userRole === 'provider') {
                                $appointmentsUrl = base_url('index.php/provider/appointments');
                            }
                            // Patient uses the default appointments URL (index.php/appointments)
                        }
                        ?>
                        <a href="<?= $appointmentsUrl ?>" class="text-white">Appointments</a>
                    </li>
                    <?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']): ?>
                        <li class="mb-2"><a href="<?= base_url('index.php/auth') ?>" class="text-white">Login</a></li>
                    <?php else: ?>
                        <li class="mb-2"><a href="<?= base_url('index.php/auth/logout') ?>" class="text-white">Logout</a></li>
                    <?php endif; ?>
                    <li class="mb-2"><a href="<?= base_url('index.php/terms') ?>" class="text-white" target="_blank">Terms of Service</a></li>
                    <li class="mb-2"><a href="<?= base_url('index.php/privacy') ?>" class="text-white" target="_blank">Privacy Policy</a></li>
                </ul>
            </div>
                        
            <div class="col-md-3">
                <h5 class="mb-3">Office Hours</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">Monday - Friday: 8:00 AM - 6:00 PM</li>
                    <li class="mb-2">Saturday: 9:00 AM - 1:00 PM</li>
                    <li class="mb-2">Sunday: Closed</li>
                </ul>
            </div>
            
            <div class="col-md-3">
                <h5 class="mb-3">Contact</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Health St, Medical Center</li>
                    <li class="mb-2"><i class="fas fa-phone me-2"></i> (555) 123-4567</li>
                    <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@example.com</li>
                </ul>
            </div>
        </div>
        
        <hr class="border-light my-3">
        
        <div class="text-center pb-3">
            <p class="mb-0">© <?= date('Y') ?> Patient Appointment System. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.container {
    flex: 1 0 auto;
}

.footer {
    flex-shrink: 0;
    margin-top: auto;
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: 0;
}
</style>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>