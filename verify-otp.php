<?php
require_once __DIR__ . '/db.php';

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
    $data = json_decode(file_get_contents('php://input'), true);
    $email = trim($data['email'] ?? '');
    $otp = trim($data['otp'] ?? '');

    if (empty($email) || empty($otp)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required']);
        exit;
    }

    // Validate OTP format
    if (!preg_match('/^\d{6}$/', $otp)) {
        echo json_encode(['success' => false, 'message' => 'OTP must be exactly 6 digits']);
        exit;
    }

    try {
        // Get the latest OTP record for this email
        $stmt = $pdo->prepare("
            SELECT id, otp, expires_at, created_at
            FROM otp_verifications 
            WHERE email = ? 
            AND expires_at > NOW() 
            AND verified = FALSE
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'OTP expired or not found']);
            exit;
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify OTP
        if (password_verify($otp, $row['otp'])) {
            // Update verification status
            $stmt = $pdo->prepare("
                UPDATE otp_verifications 
                SET verified = TRUE,
                    verified_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$row['id']]);
            
            // Delete other OTP records for this email to prevent reuse
            $stmt = $pdo->prepare("DELETE FROM otp_verifications WHERE email = ? AND id != ?");
            $stmt->execute([$email, $row['id']]);
            
            echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid OTP code']);
        }
        
    } catch (Exception $e) {
        error_log("OTP Verification Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Verification failed: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>