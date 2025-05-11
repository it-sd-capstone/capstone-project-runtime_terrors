<?php
// Prevent direct access to view files
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}

// Determine user role
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$role = $isLoggedIn ? $_SESSION['role'] : 'guest';
$userName = $isLoggedIn ? ($_SESSION['name'] ?? $_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Patient Appointment System - Book Your Healthcare Appointments Online</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero-section {
            background-color: #0d6efd;
            color: white;
            padding: 4rem 0;
            border-radius: 0 0 2rem 2rem;
        }
        .card {
            border-radius: 0.5rem;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 100%;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .service-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0d6efd;
        }
        .footer {
            background-color: #212529;
            color: white;
            padding: 3rem 0;
            margin-top: 3rem;
        }
        .footer a {
            color: white;
            text-decoration: none;
        }
        .footer a:hover {
            color: #0d6efd;
        }
        .user-dashboard {
            background-color: #f8f9fa;
            border-radius: 1rem;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .appointment-card {
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-card {
            padding: 1.5rem;
            text-align: center;
            border-radius: 0.5rem;
            color: white;
        }
        .stat-card.blue {
            background-color: #0d6efd;
        }
        .stat-card.green {
            background-color: #198754;
        }
        .stat-card.red {
            background-color: #dc3545;
        }
        .stat-card.yellow {
            background-color: #ffc107;
            color: #212529;
        }
        .stat-card h2 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .stat-card p {
            margin-bottom: 0;
            opacity: 0.8;
        }
        .availability-slot {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background-color: white;
            border-radius: 0.5rem;
            border-left: 4px solid #28a745;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        /* Testimonial carousel styles - ADD THESE NEW STYLES HERE */
        .carousel-control-prev-icon, .carousel-control-next-icon {
            background-color: rgba(13, 110, 253, 0.8);
            width: 3rem;
            height: 3rem;
            background-size: 1.5rem;
        }

        .carousel-indicators [data-bs-target] {
            background-color: #0d6efd;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 5px;
        }

        .carousel-item {
            padding: 1rem 4rem;
        }

        .carousel-item .card {
            box-shadow: 0 6px 10px rgba(0,0,0,0.08);
            border: none;
            transition: transform 0.3s ease;
        }

        .carousel-item .card:hover {
            transform: translateY(-5px);
        }

        @media (max-width: 767px) {
            .carousel-item {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php if (file_exists(VIEW_PATH . '/partials/navigation.php')): ?>
        <?php include_once VIEW_PATH . '/partials/navigation.php'; ?>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero-section text-center">
    <div class="container">
        <?php if ($isLoggedIn): ?>
            <!-- Personalized welcome message for logged-in users -->
            <h1 class="display-4 fw-bold mb-3">Welcome back, <?= htmlspecialchars($userName) ?>!</h1>
            
            <?php if ($role === 'patient'): ?>
                <p class="lead mb-4">Ready to schedule your next appointment?</p>
                <a href="<?= base_url('index.php/patient/selectService') ?>" class="btn btn-light btn-lg px-4 fw-bold">Book Appointment</a>
            <?php elseif ($role === 'provider'): ?>
                <p class="lead mb-4">Manage your schedule and patient appointments</p>
                <a href="<?= base_url('index.php/provider/schedule') ?>" class="btn btn-light btn-lg px-4 fw-bold">Manage Schedule</a>
            <?php elseif ($role === 'admin'): ?>
                <p class="lead mb-4">Manage the appointment system</p>
                <a href="<?= base_url('index.php/admin') ?>" class="btn btn-light btn-lg px-4 fw-bold">Admin Dashboard</a>
            <?php endif; ?>
        <?php else: ?>
            <!-- Default message for guests with sign-in requirement -->
            <h1 class="display-4 fw-bold mb-3">Simplify Your Healthcare Scheduling</h1>
            <p class="lead mb-4">Book appointments with your preferred healthcare providers in minutes. No phone calls, no waiting.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="<?= base_url('index.php/auth') ?>" class="btn btn-light btn-lg px-4 fw-bold">Sign In to Book</a>
                <!-- <a href="<?= base_url('index.php/auth') ?>" class="btn btn-outline-light btn-lg px-4">Login</a> -->
            </div>
        <?php endif; ?>
    </div>
    </section>
    <!-- For guests/non-logged in users -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-3">Looking for Healthcare Services?</h2>
                    <p class="lead mb-4">Sign up or log in to schedule appointments with our healthcare professionals.</p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="<?= base_url('index.php/auth/register') ?>" class="btn btn-primary btn-lg px-4 me-md-2">
                            <i class="fas fa-user-plus me-2"></i>Sign Up
                        </a>
                        <a href="<?= base_url('index.php/auth/login') ?>" class="btn btn-outline-primary btn-lg px-4">
                            <i class="fas fa-sign-in-alt me-2"></i>Log In
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="card border-0 shadow">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Our Services Include:</h4>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent border-0 ps-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>Primary Care
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>Specialist Consultations
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>Preventive Care
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>Follow-up Appointments
                                </li>
                                <li class="list-group-item bg-transparent border-0 ps-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>Virtual Consultations
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section (Only show for guests and patients) -->
    <?php if ($role === 'guest'): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-6 fw-bold">How It Works</h2>
                <p class="lead">Book your appointment in 4 simple steps</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-plus text-primary fs-2"></i>
                        </div>
                        <h3 class="h4">1. Register/Login</h3>
                        <p>Create an account or login to access our scheduling system.</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-list-check text-primary fs-2"></i>
                        </div>
                        <h3 class="h4">2. Select Service</h3>
                        <p>Choose from our range of healthcare services.</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-calendar-check text-primary fs-2"></i>
                        </div>
                        <h3 class="h4">3. Choose Time</h3>
                        <p>Select from available appointment slots that work for you.</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-check-circle text-primary fs-2"></i>
                        </div>
                        <h3 class="h4">4. Confirm</h3>
                        <p>Review and confirm your appointment details.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Provider Showcase with Avatars (Only show for guests and patients) -->
    <?php if ($role === 'guest'): ?>
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-6 fw-bold">Meet Our Providers</h2>
                <p class="lead">Experienced healthcare professionals dedicated to your wellbeing</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($featuredProviders as $key => $provider): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <?php 
                        // Define avatar colors for variety
                        $avatarColors = ['primary', 'success', 'info'];
                        $colorIndex = $key % count($avatarColors);
                        $avatarColor = $avatarColors[$colorIndex];
                        
                        // Get provider initials
                        $initials = strtoupper(substr($provider['first_name'], 0, 1) . substr($provider['last_name'], 0, 1));
                        ?>
                        <div class="text-center bg-<?= $avatarColor ?> text-white py-5">
                            <div class="display-1 fw-bold"><?= $initials ?></div>
                        </div>
                        <div class="card-body">
                            <h3 class="h4"><?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?></h3>
                            <p class="text-muted"><?= htmlspecialchars($provider['specialization']) ?></p>
                            <p><?= htmlspecialchars($provider['bio']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Testimonials Carousel (Show for all users) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">What Our Patients Say</h2>
            <p class="lead">Read testimonials from satisfied patients</p>
        </div>
        
        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($testimonials as $key => $testimonial): ?>
                    <div class="carousel-item <?= $key === 0 ? 'active' : '' ?>">
                        <div class="card mx-auto" style="max-width: 700px;">
                            <div class="card-body p-4 text-center">
                                <div class="mb-4">
                                    <i class="fas fa-quote-left fa-2x text-primary"></i>
                                </div>
                                <p class="lead mb-4">"<?= htmlspecialchars($testimonial['text']) ?>"</p>
                                <div class="d-flex justify-content-center align-items-center">
                                    <?php 
                                    // Define some avatar colors for variety
                                    $avatarColors = ['primary', 'success', 'danger', 'warning', 'info'];
                                    $colorIndex = $key % count($avatarColors);
                                    $avatarColor = $avatarColors[$colorIndex];
                                    ?>
                                    <div class="rounded-circle bg-<?= $avatarColor ?> text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-weight: bold;">
                                        <?= strtoupper(substr($testimonial['name'], 0, 1)) ?>
                                    </div>
                                    <div class="text-start">
                                        <h5 class="m-0"><?= htmlspecialchars($testimonial['name']) ?></h5>
                                        <small class="text-muted">Patient since <?= htmlspecialchars($testimonial['patient_since']) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-primary rounded-circle p-2" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-primary rounded-circle p-2" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <div class="carousel-indicators" style="bottom: -40px;">
                <?php foreach ($testimonials as $key => $testimonial): ?>
                    <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="<?= $key ?>" <?= $key === 0 ? 'class="active" aria-current="true"' : '' ?> aria-label="Testimonial <?= $key + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

    <!-- Role-Specific Call to Action -->
    <section class="py-5">
        <div class="container">
            <div class="card text-center bg-primary text-white">
                <div class="card-body py-5">
                    <?php if ($role === 'patient'): ?>
                        <h2 class="card-title mb-3">Ready to schedule your next appointment?</h2>
                        <p class="card-text mb-4">Take control of your healthcare journey by scheduling your next appointment.</p>
                        <a href="<?= base_url('index.php/patient/selectService') ?>" class="btn btn-light btn-lg">Book Now</a>
                    <?php elseif ($role === 'provider'): ?>
                        <h2 class="card-title mb-3">Update your availability</h2>
                        <p class="card-text mb-4">Keep your schedule up-to-date to ensure patients can book appointments when you're available.</p>
                        <a href="<?= base_url('index.php/provider/manage_availability') ?>" class="btn btn-light btn-lg">Manage Availability</a>
                    <?php elseif ($role === 'admin'): ?>
                        <h2 class="card-title mb-3">Manage your healthcare system</h2>
                        <p class="card-text mb-4">Access the administrative dashboard to manage users, services, and system settings.</p>
                        <a href="<?= base_url('index.php/admin') ?>" class="btn btn-light btn-lg">Admin Dashboard</a>
                    <?php else: ?>
                        <h2 class="card-title mb-3">Ready to schedule your first appointment?</h2>
                        <p class="card-text mb-4">Take the first step toward better health today.</p>
                        <a href="<?= base_url('index.php/auth') ?>" class="btn btn-light btn-lg">Sign In to Book</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4 class="mb-4">Appointment System</h4>
                    <p>Book and manage your healthcare appointments online, anytime, anywhere.</p>
                </div>
                
                <div class="col-md-2">
                    <h5 class="mb-3">Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= base_url('index.php/home') ?>">Home</a></li>
                        <li class="mb-2"><a href="<?= base_url('index.php/appointments') ?>">Appointments</a></li>
                        <?php if (!$isLoggedIn): ?>
                            <li class="mb-2"><a href="<?= base_url('index.php/auth') ?>">Login</a></li>
                        <?php else: ?>
                            <li class="mb-2"><a href="<?= base_url('index.php/auth/logout') ?>">Logout</a></li>
                        <?php endif; ?>
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
            
            <hr class="my-4 bg-light">
            
            <div class="text-center">
                <p>© <?= date('Y') ?> Patient Appointment System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>