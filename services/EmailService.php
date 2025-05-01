<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Service
 *
 * Handles email delivery for the application including verification emails,
 * password reset emails, and general notifications.
 */
class EmailService {
    private $fromEmail;
    private $fromName;
    private $smtpHost;
    private $smtpPort;
    private $smtpUser;
    private $smtpPass;
    private $smtpSecure;
    private $config;
    
/**
     * Constructor
     */
    public function __construct() {
        // Load email configuration from file
        $this->config = require APP_ROOT . '/config/email_config.php';
        
        // Debug: Check if SendGrid API key is loaded
        error_log("SendGrid API Key in constructor: " . (getenv('SENDGRID_API_KEY') ? substr(getenv('SENDGRID_API_KEY'), 0, 5) . '...' : 'NOT FOUND'));
        
        // Set up from array-based config
        if (isset($this->config['smtp'])) {
            $smtp = $this->config['smtp'];
            
            // Set SMTP configuration from array
            $this->fromEmail = $smtp['from_email'] ?? 'noreply@appointmentsystem.com';
            $this->fromName = $smtp['from_name'] ?? 'Patient Appointment System';
            $this->smtpHost = $smtp['host'] ?? '';
            $this->smtpPort = $smtp['port'] ?? 587;
            $this->smtpUser = $smtp['username'] ?? '';
            $this->smtpPass = $smtp['password'] ?? '';
            $this->smtpSecure = $smtp['secure'] ?? 'tls';
            
            // Debug: Log SMTP settings
            error_log("SMTP Host: {$this->smtpHost}");
            error_log("SMTP User: {$this->smtpUser}");
            error_log("SMTP Password: " . ($this->smtpPass ? substr($this->smtpPass, 0, 5) . '...' : 'EMPTY'));
        } else {
            // Fallback to constants if config file doesn't have SMTP section
            $this->fromEmail = defined('EMAIL_FROM') ? EMAIL_FROM : 'noreply@appointmentsystem.com';
            $this->fromName = defined('EMAIL_NAME') ? EMAIL_NAME : 'Patient Appointment System';
            $this->smtpHost = defined('SMTP_HOST') ? SMTP_HOST : '';
            $this->smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
            $this->smtpUser = defined('SMTP_USER') ? SMTP_USER : '';
            $this->smtpPass = defined('SMTP_PASS') ? SMTP_PASS : '';
            $this->smtpSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
        }
    }
    
    /**
     * Send verification email to a newly registered user
     *
     * @param string $email Recipient email address
     * @param string $name Recipient name
     * @param string $token Verification token
     * @return bool Success status
     */
    public function sendVerificationEmail($email, $name, $token) {
        $subject = "Verify Your Email - Patient Appointment System";
        
        // Create verification link
        $verifyUrl = base_url("index.php/auth/verify?token=$token");
        
        // Prepare email body
        $message = $this->getEmailTemplate('verification');
        $message = str_replace('{{name}}', $name, $message);
        $message = str_replace('{{verification_link}}', $verifyUrl, $message);
        
        // Send the email
        return $this->send($email, $subject, $message);
    }
    
    /**
     * Send password reset email
     *
     * @param string $email Recipient email address
     * @param string $name Recipient name
     * @param string $token Reset token
     * @return bool Success status
     */
    public function sendPasswordResetEmail($email, $name, $token) {
        $subject = "Password Reset - Patient Appointment System";
        
        // Create reset link
        $resetUrl = base_url("index.php/auth/reset_password?token=$token");
        
        // Prepare email body
        $message = $this->getEmailTemplate('password_reset');
        $message = str_replace('{{name}}', $name, $message);
        $message = str_replace('{{reset_link}}', $resetUrl, $message);
        
        // Send the email
        return $this->send($email, $subject, $message);
    }
    
    /**
     * Send appointment confirmation email
     *
     * @param string $email Recipient email address
     * @param string $name Recipient name
     * @param array $appointment Appointment details
     * @return bool Success status
     */
    public function sendAppointmentConfirmation($email, $name, $appointment) {
        $subject = "Appointment Confirmation - Patient Appointment System";
        
        // Prepare email body
        $message = $this->getEmailTemplate('appointment_confirmation');
        $message = str_replace('{{name}}', $name, $message);
        $message = str_replace('{{date}}', date('F j, Y', strtotime($appointment['appointment_date'])), $message);
        $message = str_replace('{{time}}', date('g:i A', strtotime($appointment['start_time'])), $message);
        $message = str_replace('{{provider}}', $appointment['provider_name'], $message);
        $message = str_replace('{{service}}', $appointment['service_name'], $message);
        
        // Send the email
        return $this->send($email, $subject, $message);
    }
    
    /**
     * Send an email
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email body (HTML)
     * @return bool Success status
     */
    private function send($to, $subject, $message) {
        // For development/testing without sending actual emails
        // Fixed: Check if ENVIRONMENT is defined first
        if ((defined('ENVIRONMENT') && ENVIRONMENT === 'development') && !defined('SEND_EMAILS')) {
            error_log("Email would be sent to: $to, Subject: $subject");
            return true;
        }
        
        // Check if we should use SMTP
        if (!empty($this->smtpHost) && !empty($this->smtpUser)) {
            return $this->sendSmtp($to, $subject, $message);
        }
        
        // Fall back to PHP's mail() function
        return $this->sendMail($to, $subject, $message);
    }
    
    /**
     * Send email using PHP's mail() function
     */
    private function sendMail($to, $subject, $message) {
        // Headers
        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Try to send the email
        $result = mail($to, $subject, $message, $headers);
        
        // Log the result
        if (!$result) {
            error_log("Failed to send email to $to using mail() function");
        }
        
        return $result;
    }
    /**
     * Get configuration
     * 
     * @return array Configuration array
     */
    private function getConfig() {
        return $this->config;
    }
    /**
     * Send email using SMTP
     */
    private function sendSmtp($to, $subject, $message, $altMessage = '') {
        // Debug - remove in production
        // error_log("Using SMTP Host: {$this->smtpHost}");
        // error_log("Using SMTP User: {$this->smtpUser}");
        // error_log("SMTP Password Length: " . (strlen($this->smtpPass) > 0 ? strlen($this->smtpPass) : "EMPTY"));
        // error_log("SMTP Password First 10 chars: " . (strlen($this->smtpPass) > 10 ? substr($this->smtpPass, 0, 10) : "TOO SHORT"));
        $smtp = $this->config['smtp'] ?? [];
        
        $mail = new PHPMailer(true);
        try {
            // Server settings
            if (isset($smtp['debug']) && $smtp['debug'] > 0) {
                $mail->SMTPDebug = $smtp['debug']; // Enable verbose debug output
            }
            
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpUser;
            $mail->Password   = $this->smtpPass;
            $mail->SMTPSecure = $this->smtpSecure;
            $mail->Port       = $this->smtpPort;
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = $altMessage ?: strip_tags($message);
            
            $result = $mail->send();
            if ($result) {
                error_log("Email successfully sent to: $to");
            }
            return $result;
        } catch (Exception $e) {
            error_log("Email send failed: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Get email template
     *
     * @param string $template Template name
     * @return string Template HTML
     */
    private function getEmailTemplate($template) {
        // Path to email templates
        $templatePath = APP_ROOT . "/views/emails/{$template}.php";
        
        // Check if template file exists
        if (file_exists($templatePath)) {
            // Buffer output and include the template
            ob_start();
            include $templatePath;
            return ob_get_clean();
        }
        
        // Use default template if file doesn't exist
        switch ($template) {
            case 'verification':
                return $this->getDefaultVerificationTemplate();
            case 'password_reset':
                return $this->getDefaultPasswordResetTemplate();
            case 'appointment_confirmation':
                return $this->getDefaultAppointmentConfirmationTemplate();
            default:
                return '<p>{{message}}</p>';
        }
    }
    
    /**
     * Get default verification email template
     */
    private function getDefaultVerificationTemplate() {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 15px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .button { display: inline-block; padding: 10px 20px; background: #3498db; color: white;
                 text-decoration: none; border-radius: 5px; margin: 15px 0; }
        .footer { font-size: 12px; color: #888; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Email Verification</h2>
        </div>
        <div class="content">
            <p>Hello {{name}},</p>
            <p>Thank you for registering with our Patient Appointment System. Please verify your email address by clicking the button below:</p>
            
            <p style="text-align: center;">
                <a href="{{verification_link}}" class="button">Verify Email Address</a>
            </p>
            
            <p>If the button above doesn't work, copy and paste this link into your browser:</p>
            <p>{{verification_link}}</p>
            
            <p>This link will expire in 24 hours.</p>
            
            <p>If you did not register for an account, please ignore this email.</p>
        </div>
        <div class="footer">
            <p>© Patient Appointment System</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get default password reset email template
     */
    private function getDefaultPasswordResetTemplate() {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 15px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .button { display: inline-block; padding: 10px 20px; background: #3498db; color: white;
                 text-decoration: none; border-radius: 5px; margin: 15px 0; }
        .footer { font-size: 12px; color: #888; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Password Reset</h2>
        </div>
        <div class="content">
            <p>Hello {{name}},</p>
            <p>We received a request to reset your password. Click the button below to set a new password:</p>
            
            <p style="text-align: center;">
                <a href="{{reset_link}}" class="button">Reset Password</a>
            </p>
            
            <p>If the button above doesn't work, copy and paste this link into your browser:</p>
            <p>{{reset_link}}</p>
            
            <p>This link will expire in 1 hour. If you did not request a password reset, please ignore this email.</p>
        </div>
        <div class="footer">
            <p>© Patient Appointment System</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get default appointment confirmation email template
     */
    private function getDefaultAppointmentConfirmationTemplate() {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 15px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .appointment-details { background: #fff; padding: 15px; border: 1px solid #ddd; margin: 15px 0; }
        .footer { font-size: 12px; color: #888; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Appointment Confirmation</h2>
        </div>
        <div class="content">
            <p>Hello {{name}},</p>
            <p>Your appointment has been confirmed. Here are the details:</p>
            
            <div class="appointment-details">
                <p><strong>Date:</strong> {{date}}</p>
                <p><strong>Time:</strong> {{time}}</p>
                <p><strong>Provider:</strong> {{provider}}</p>
                <p><strong>Service:</strong> {{service}}</p>
            </div>
            
            <p>If you need to reschedule or cancel your appointment, please log in to your account or contact us.</p>
            
            <p>Thank you for choosing our services.</p>
        </div>
        <div class="footer">
            <p>© Patient Appointment System</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
