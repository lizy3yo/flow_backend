<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once __DIR__ . '/db.php';

// Read .env file
$env = parse_ini_file('../.env');

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }

    $email = $data->email;
    
    try {
        // Check if email exists in database
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Email not found']);
            exit;
        }
        
        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); // OTP valid for 15 minutes
        
        // Delete any existing reset tokens for this user
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        
        // Hash the OTP before storing
        $hashedToken = password_hash($otp, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)");
        
        if (!$stmt->execute([$user['id'], $hashedToken, $expiry])) {
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
                'email' => $email
            ]);
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            echo json_encode(['success' => false, 'message' => "Failed to send email. Error: {$mail->ErrorInfo}"]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
}
?>