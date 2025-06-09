<?php

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $email = isset($_GET['email']) ? trim($_GET['email']) : '';
    
    if (empty($email)) {
        echo json_encode(['error' => 'Email is required']);
        exit;
    }

    // Get user ID
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $user = $result->fetch_assoc();
    
    // Get latest OTP
    $stmt = $conn->prepare("
        SELECT token, expiry, 
        CASE 
            WHEN expiry > NOW() THEN 'valid'
            ELSE 'expired'
        END as status,
        NOW() as current_time
        FROM password_resets 
        WHERE user_id = ?
        ORDER BY expiry DESC 
        LIMIT 1
    ");
    
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No OTP found']);
    }
}