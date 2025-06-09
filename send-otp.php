<?php
// Set headers first, before any other output
header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only include dependencies after headers are set
require_once __DIR__ . '/db.php';
require '../vendor/autoload.php';

// Check if .env file exists
$envFile = '../.env';
if (!file_exists($envFile)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Configuration file not found']);
    exit();
}

$env = parse_ini_file($envFile);
if (!$env) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load configuration']);
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$email = $data['email'] ?? '';

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    // Check if user has already verified today
    $stmt = $pdo->prepare("
        SELECT verified_at 
        FROM otp_verifications 
        WHERE email = ? 
        AND verified = TRUE 
        AND DATE(verified_at) = CURRENT_DATE()
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // User already verified today, no need for OTP
        echo json_encode(['success' => true, 'message' => 'Already verified today', 'skipOtp' => true]);
        exit;
    }

    // Generate and send OTP only if not verified today
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
    
    // Store OTP in database
    $stmt = $pdo->prepare("INSERT INTO otp_verifications (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
    $stmt->execute([$email, $hashedOtp]);

    // Verify required SMTP configuration
    $requiredEnvVars = ['SMTP_HOST', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_FROM_NAME'];
    foreach ($requiredEnvVars as $var) {
        if (empty($env[$var])) {
            throw new Exception("Missing SMTP configuration: $var");
        }
    }

    // Send email with OTP
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $env['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $env['SMTP_USERNAME'];
    $mail->Password = $env['SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Add connection timeout
    $mail->Timeout = 30;
    $mail->SMTPKeepAlive = true;
    
    // Recipients
    $mail->setFrom($env['SMTP_USERNAME'], $env['SMTP_FROM_NAME']);
    $mail->addAddress($email);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Flow Verification Code';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2>Verification Code</h2>
            <p>Your verification code is: <strong style='font-size: 24px;'>{$otp}</strong></p>
            <p>This code will expire in 5 minutes.</p>
            <p>If you didn't request this code, please ignore this email.</p>
        </div>
    ";
    
    $mail->send();
    
    echo json_encode(['success' => true, 'message' => 'OTP sent successfully', 'skipOtp' => false]);
    
} catch (Exception $e) {
    error_log("OTP Send Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP: ' . $e->getMessage()]);
}
?>