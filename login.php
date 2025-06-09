<?php

// Set CORS headers before any other output
header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Simple validation
if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($data['password'], $user['password'])) {
        
        // Check if user has completed OTP verification today
        $otp_stmt = $conn->prepare("
            SELECT verified_at 
            FROM otp_verifications 
            WHERE email = ? 
            AND verified = TRUE 
            AND DATE(verified_at) = CURRENT_DATE()
            LIMIT 1
        ");
        $otp_stmt->bind_param("s", $data['email']);
        $otp_stmt->execute();
        $otp_result = $otp_stmt->get_result();
        
        if ($otp_result->num_rows === 0) {
            // User hasn't completed OTP verification today
            echo json_encode([
                'success' => false,
                'message' => 'OTP verification required',
                'requireOtp' => true,
                'email' => $data['email']
            ]);
            exit();
        }
        
        // Start session
        session_start();
        $_SESSION['user_id'] = $user['id'];
        
        // Generate session token
        $session_token = bin2hex(random_bytes(32));
        
        // Update session token in database with error checking
        $token_stmt = $conn->prepare("UPDATE users SET session_token = ? WHERE id = ?");
        if (!$token_stmt) {
            throw new Exception("Failed to prepare token update statement");
        }
        
        $token_stmt->bind_param("si", $session_token, $user['id']);
        if (!$token_stmt->execute()) {
            throw new Exception("Failed to update session token");
        }
        $token_stmt->close();
        
        // Remove sensitive data
        unset($user['password']);
        unset($user['session_token']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
            'user_id' => $user['id'],
            'session_token' => $session_token
        ]);
    } else {
        // Login failed
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>