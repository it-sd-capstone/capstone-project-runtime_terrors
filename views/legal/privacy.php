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
    <title>Privacy Policy - Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .privacy-container {
            max-width: 800px;
            margin: 0 auto;
            margin-bottom: 50px;
        }
        .privacy-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 40px;
        }
        .privacy-content h2 {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #333;
        }
        .privacy-content p, .privacy-content li {
            line-height: 1.6;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container privacy-container">
        <div class="card privacy-card">
            <h1 class="text-center mb-4">Privacy Policy</h1>
            
            <div class="privacy-content">
                <p class="text-muted text-center mb-4">Last updated: <?= date('F d, Y') ?></p>
                
                <h2>1. Introduction</h2>
                <p>Welcome to the Appointment System. We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our appointment scheduling platform.</p>
                
                <h2>2. Information We Collect</h2>
                <p>We collect information that you provide directly to us, including:</p>
                <ul>
                    <li>Personal identification information (name, email address, phone number)</li>
                    <li>Medical appointment details and history</li>
                    <li>Healthcare provider preferences</li>
                    <li>Account credentials</li>
                    <li>Communication preferences</li>
                </ul>
                
                <h2>3. How We Use Your Information</h2>
                <p>We use the information we collect to:</p>
                <ul>
                    <li>Provide and maintain our appointment scheduling services</li>
                    <li>Communicate with you about appointments and updates</li>
                    <li>Send appointment reminders and notifications</li>
                    <li>Improve our services and user experience</li>
                    <li>Comply with legal obligations</li>
                    <li>Protect against fraud and abuse</li>
                </ul>
                
                <h2>4. Information Sharing and Disclosure</h2>
                <p>We may share your information with:</p>
                <ul>
                    <li>Healthcare providers you schedule appointments with</li>
                    <li>Service providers who assist us in operating our platform</li>
                    <li>Legal authorities when required by law</li>
                    <li>Business partners with your consent</li>
                </ul>
                <p>We do not sell, trade, or rent your personal information to third parties.</p>
                
                <h2>5. Data Security</h2>
                <p>We implement appropriate technical and organizational measures to protect your personal information, including:</p>
                <ul>
                    <li>Encryption of data in transit and at rest</li>
                    <li>Secure servers and databases</li>
                    <li>Regular security audits and assessments</li>
                    <li>Access controls and authentication</li>
                    <li>Employee training on data protection</li>
                </ul>
                
                <h2>6. Your Rights and Choices</h2>
                <p>You have the right to:</p>
                <ul>
                    <li>Access and review your personal information</li>
                    <li>Correct or update inaccurate information</li>
                    <li>Request deletion of your data</li>
                    <li>Opt-out of certain communications</li>
                    <li>Export your data in a portable format</li>
                </ul>
                
                <h2>7. Cookies and Tracking Technologies</h2>
                <p>We use cookies and similar tracking technologies to:</p>
                <ul>
                    <li>Maintain your session and preferences</li>
                    <li>Analyze platform usage and performance</li>
                    <li>Provide personalized experiences</li>
                    <li>Prevent fraud and enhance security</li>
                </ul>
                
                <h2>8. Third-Party Services</h2>
                <p>Our platform may contain links to third-party websites and services. We are not responsible for the privacy practices of these third parties. We encourage you to review their privacy policies before providing any personal information.</p>
                
                <h2>9. Children's Privacy</h2>
                <p>Our services are not directed to individuals under 18 years of age. We do not knowingly collect personal information from children. If you are a parent or guardian and believe your child has provided us with personal information, please contact us.</p>
                
                <h2>10. International Data Transfers</h2>
                <p>If you access our services from outside the United States, please be aware that your information may be transferred to, stored, and processed in the United States or other countries.</p>
                
                <h2>11. Data Retention</h2>
                <p>We retain your personal information for as long as necessary to provide our services and comply with legal obligations. Appointment history is typically retained for the period required by healthcare regulations.</p>
                
                <h2>12. Changes to This Privacy Policy</h2>
                <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
                
                <h2>13. Contact Us</h2>
                <p>If you have questions or concerns about this Privacy Policy or our data practices, please contact us at:</p>
                <ul>
                    <li>Email: privacy@appointmentsystem.com</li>
                    <li>Phone: (555) 123-4567</li>
                    <li>Address: 123 Health St, Medical Center</li>
                    <li>Data Protection Officer: dpo@appointmentsystem.com</li>
                </ul>
                
                <h2>14. Compliance with Laws</h2>
                <p>We comply with applicable data protection laws, including HIPAA for health information privacy and other relevant regulations.</p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>