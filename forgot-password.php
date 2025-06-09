<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once __DIR__ . '/db.php';

// Read .env file
$env = parse_ini_file('../.env');

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }

    $email = $data->email;
    
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
        exit;
    }

    $user = $result->fetch_assoc();
    
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); // OTP valid for 15 minutes
    
    // Delete any existing reset tokens for this user
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    
    // Hash the OTP before storing
    $hashedToken = password_hash($otp, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user['id'], $hashedToken, $expiry);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to create reset token']);
        exit;
    }

    // Send email using PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $env['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $env['SMTP_USERNAME'];
        $mail->Password = $env['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($env['SMTP_USERNAME'], $env['SMTP_FROM_NAME']);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>Your OTP for password reset is: <strong>{$otp}</strong></p>
            <p>This OTP will expire in 15 minutes.</p>
            <p>If you did not request this password reset, please ignore this email.</p>
        ";

        $mail->send();
        echo json_encode([
            'success' => true, 
            'message' => 'OTP sent to email',
            'email' => $email // Send back email for the next step
        ]);
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        echo json_encode(['success' => false, 'message' => "Failed to send email. Error: {$mail->ErrorInfo}"]);
    }
}
?>