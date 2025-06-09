<?php

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $otp = $data['otp'] ?? '';

    if (empty($email) || empty($otp)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required']);
        exit;
    }

    try {
        // Get the latest OTP record for this email
        $stmt = $pdo->prepare("
            SELECT id, otp, expires_at 
            FROM otp_verifications 
            WHERE email = ? 
            AND expires_at > NOW() 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'OTP expired or not found']);
            exit;
        }
        
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
            
            // Delete other OTP records for this email
            $stmt = $pdo->prepare("DELETE FROM otp_verifications WHERE email = ? AND id != ?");
            $stmt->execute([$email, $row['id']]);
            
            echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Verification failed: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email']) && isset($_GET['otp'])) {
    $email = $_GET['email'];
    $otp = $_GET['otp'];

    // Get user ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
        exit;
    }

    $user_id = $user['id'];

    // Check OTP
    $stmt = $pdo->prepare("SELECT token, expiry FROM password_resets WHERE user_id = ? ORDER BY expiry DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo json_encode([
            'success' => true,
            'stored_otp' => $row['token'],
            'expiry' => $row['expiry'],
            'submitted_otp' => $otp,
            'match' => $row['token'] === $otp
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No OTP found']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}