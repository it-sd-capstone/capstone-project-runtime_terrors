<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

require_once "../models/Appointment.php";
require_once "../core/Database.php";

session_start();
$db = Database::getInstance()->getConnection();
$appointmentModel = new Appointment($db);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $appointment_id = $_POST['appointment_id'];
    $email_type = $_POST['email_type']; // 'confirmation' or 'cancellation'

    $appointmentDetails = $appointmentModel->getById($appointment_id);

    if (!$appointmentDetails) {
        echo json_encode(["error" => "Invalid appointment ID"]);
        exit;
    }

    $provider_email = $appointmentDetails['provider_email'];
    $patient_email = $appointmentDetails['patient_email'];

    $subject = ($email_type === "confirmation") ? "Appointment Confirmation" : "Appointment Cancellation";
    $message = ($email_type === "confirmation") ?
        "Hello, your appointment with {$appointmentDetails['provider_name']} on {$appointmentDetails['appointment_date']} at {$appointmentDetails['start_time']} has been confirmed." :
        "Unfortunately, your appointment with {$appointmentDetails['provider_name']} on {$appointmentDetails['appointment_date']} at {$appointmentDetails['start_time']} has been canceled.";

    sendEmailNotification($provider_email, $subject, $message);
    sendEmailNotification($patient_email, $subject, $message);

    echo json_encode(["success" => "Notification sent"]);
    exit;
}

function sendEmailNotification($recipientEmail, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // Replace with SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com'; // Replace with actual credentials
        $mail->Password = 'your_password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your_email@example.com', 'Appointment System');
        $mail->addAddress($recipientEmail);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
    }
}
?>