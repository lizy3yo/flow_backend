<?php

require_once __DIR__ . '/db.php';

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
        $stmt = $conn->prepare("
            SELECT id, otp, expires_at 
            FROM otp_verifications 
            WHERE email = ? 
            AND expires_at > NOW() 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'OTP expired or not found']);
            exit;
        }
        
        $row = $result->fetch_assoc();
        
        // Verify OTP
        if (password_verify($otp, $row['otp'])) {
            // Update verification status
            $stmt = $conn->prepare("
                UPDATE otp_verifications 
                SET verified = TRUE,
                    verified_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
            
            // Delete other OTP records for this email
            $stmt = $conn->prepare("DELETE FROM otp_verifications WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $row['id']);
            $stmt->execute();
            
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
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
        exit;
    }

    $user = $result->fetch_assoc();
    $user_id = $user['id'];

    // Check OTP
    $stmt = $conn->prepare("SELECT token, expiry FROM password_resets WHERE user_id = ? ORDER BY expiry DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
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