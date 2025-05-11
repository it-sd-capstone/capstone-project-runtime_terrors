<?php
// Prevent direct access to view files
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .terms-container {
            max-width: 800px;
            margin: 0 auto;
            margin-bottom: 50px;
        }
        .terms-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 40px;
        }
        .terms-content h2 {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #333;
        }
        .terms-content p, .terms-content li {
            line-height: 1.6;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container terms-container">
        <div class="card terms-card">
            <h1 class="text-center mb-4">Terms of Service</h1>
            
            <div class="terms-content">
                <p class="text-muted text-center mb-4">Last updated: <?= date('F d, Y') ?></p>
                
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using the Appointment System ("Service"), you accept and agree to be bound by the terms and conditions of this agreement. If you do not agree to these terms, you may not use the Service.</p>
                
                <h2>2. Description of Service</h2>
                <p>The Appointment System provides an online platform for scheduling and managing healthcare appointments. This includes:</p>
                <ul>
                    <li>Appointment booking and management</li>
                    <li>Provider search and selection</li>
                    <li>Appointment reminders and notifications</li>
                    <li>Profile management for patients and providers</li>
                </ul>
                
                <h2>3. User Registration</h2>
                <p>To use our services, you must:</p>
                <ul>
                    <li>Create an account with accurate information</li>
                    <li>Be at least 18 years of age</li>
                    <li>Maintain the security of your account credentials</li>
                    <li>Notify us immediately of any unauthorized access</li>
                </ul>
                
                <h2>4. User Responsibilities</h2>
                <p>As a user of the Service, you agree to:</p>
                <ul>
                    <li>Provide accurate and up-to-date information</li>
                    <li>Attend scheduled appointments or cancel in advance</li>
                    <li>Respect healthcare providers and other users</li>
                    <li>Not misuse or abuse the Service</li>
                    <li>Comply with all applicable laws and regulations</li>
                </ul>
                
                <h2>5. Healthcare Services</h2>
                <p>Please note that:</p>
                <ul>
                    <li>We are a scheduling platform, not a healthcare provider</li>
                    <li>All medical services are provided by independent healthcare professionals</li>
                    <li>We do not provide medical advice, diagnosis, or treatment</li>
                    <li>In case of emergency, call 911 or your local emergency services</li>
                </ul>
                
                <h2>6. Privacy and Data Protection</h2>
                <p>Your privacy is important to us. Please review our <a href="<?= base_url('index.php/privacy') ?>" target="_blank">Privacy Policy</a> to understand how we collect, use, and protect your personal information.</p>
                
                <h2>7. Cancellation Policy</h2>
                <p>Users must cancel appointments at least 24 hours in advance. Failure to do so may result in:</p>
                <ul>
                    <li>Cancellation fees (determined by the healthcare provider)</li>
                    <li>Temporary suspension of booking privileges</li>
                </ul>
                
                <h2>8. Intellectual Property</h2>
                <p>All content on this platform, including but not limited to text, graphics, logos, and software, is the property of the Appointment System and is protected by intellectual property laws.</p>
                
                <h2>9. Limitation of Liability</h2>
                <p>The Appointment System shall not be liable for:</p>
                <ul>
                    <li>Any indirect, incidental, or consequential damages</li>
                    <li>Loss of data or business interruption</li>
                    <li>Actions or omissions of healthcare providers</li>
                    <li>Service interruptions or technical issues</li>
                </ul>
                
                <h2>10. Modifications to Terms</h2>
                <p>We reserve the right to modify these terms at any time. Users will be notified of significant changes, and continued use of the Service constitutes acceptance of modified terms.</p>
                
                <h2>11. Termination</h2>
                <p>We may terminate or suspend access to our Service immediately, without prior notice, for any breach of these Terms.</p>
                
                <h2>12. Governing Law</h2>
                <p>These Terms shall be governed by and construed in accordance with applicable local laws, without regard to conflict of law provisions.</p>
                
                <h2>13. Contact Information</h2>
                <p>For questions about these Terms, please contact us at:</p>
                <ul>
                    <li>Email: legal@appointmentsystem.com</li>
                    <li>Phone: (555) 123-4567</li>
                    <li>Address: 123 Health St, Medical Center</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>