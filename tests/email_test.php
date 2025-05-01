<?php
// Simple test script to verify email configuration

// Include Composer autoloader - adjust path to go up one directory
require_once __DIR__ . '/../vendor/autoload.php';

// Include helpers for base_url function
require_once __DIR__ . '/../core/helpers.php';

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load email configuration
$config = require_once __DIR__ . '/../config/email_config.php';

echo "<h1>Email Configuration Test</h1>";

// FIRST TEST - Internal Email Test
try {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Set debug output level
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    
    // Set up SMTP
    $mail->isSMTP();
    $mail->Host       = $config['smtp']['host'];
    $mail->SMTPAuth   = $config['smtp']['auth'];
    $mail->Username   = $config['smtp']['username'];
    $mail->Password   = $config['smtp']['password'];
    $mail->SMTPSecure = $config['smtp']['secure'];
    $mail->Port       = $config['smtp']['port'];
    
    // Set sender
    $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
    
    // Add recipient (your own email for testing)
    $mail->addAddress("admin@capstone.mommabearsweetz.com");
    
    // Set email content
    $mail->isHTML(true);
    $mail->Subject = "Test Email from Appointment System";
    $mail->Body    = "This is a test email to verify your email configuration is working.<br><br>If you're seeing this, it means your email settings are correct!";
    $mail->AltBody = "This is a test email to verify your email configuration is working. If you're seeing this, it means your email settings are correct!";
    
    // Send the email
    $mail->send();
    echo "<div style='color:green;font-weight:bold;'>Test email sent successfully to admin@capstone.mommabearsweetz.com!</div>";
    echo "<p>Check your inbox (and spam folder) for the test email.</p>";
    
} catch (Exception $e) {
    echo "<div style='color:red;font-weight:bold;'>Test failed! Email could not be sent to internal address.</div>";
    echo "<p>Error: " . $mail->ErrorInfo . "</p>";
    echo "<p>Double-check your email configuration in config/email_config.php</p>";
}

echo "<hr><h2>External Email Test</h2>";

// SECOND TEST - External Email Test to kalebholley43@gmail.com
try {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Set debug output level
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    
    // Set up SMTP
    $mail->isSMTP();
    $mail->Host       = $config['smtp']['host'];
    $mail->SMTPAuth   = $config['smtp']['auth'];
    $mail->Username   = $config['smtp']['username'];
    $mail->Password   = $config['smtp']['password'];
    $mail->SMTPSecure = $config['smtp']['secure'];
    $mail->Port       = $config['smtp']['port'];
    
    // Set sender
    $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
    
    // Add recipient (your personal email)
    $mail->addAddress("kalebholley43@gmail.com");
    
    // Set email content
    $mail->isHTML(true);
    $mail->Subject = "External Test - Appointment System Email Verification";
    $mail->Body    = "This is a test email sent to your personal address to verify external email delivery.<br><br>
                     If you're seeing this, it means your email system can send to external addresses!<br><br>
                     <strong>Time sent:</strong> " . date('Y-m-d H:i:s') . "<br>
                     <strong>From:</strong> " . $config['smtp']['from_email'];
    $mail->AltBody = "This is a test email sent to your personal address to verify external email delivery. 
                      If you're seeing this, it means your email system can send to external addresses!";
    
    // Send the email
    $mail->send();
    echo "<div style='color:green;font-weight:bold;'>Test email sent successfully to kalebholley43@gmail.com!</div>";
    echo "<p>Check your personal email inbox (and spam folder) for the test email.</p>";
    
} catch (Exception $e) {
    echo "<div style='color:red;font-weight:bold;'>Test failed! Email could not be sent to external address.</div>";
    echo "<p>Error: " . $mail->ErrorInfo . "</p>";
    echo "<p>Double-check your email configuration and server settings for external email delivery.</p>";
}