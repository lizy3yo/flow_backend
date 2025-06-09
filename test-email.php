<?php

require '../vendor/autoload.php'; // Changed from 'vendor/autoload.php'
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$env = parse_ini_file('.env'); // Changed from '.env'

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $env['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $env['SMTP_USERNAME'];
    $mail->Password = $env['SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($env['SMTP_USERNAME'], $env['SMTP_FROM_NAME']);
    $mail->addAddress('dejesuskharl03@gmail.com'); // Replace with your email

    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email to verify SMTP settings';

    $mail->send();
    echo "Test email sent successfully";
} catch (Exception $e) {
    echo "Error sending email: {$mail->ErrorInfo}";
}