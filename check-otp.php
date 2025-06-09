<?php
header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $email = isset($_GET['email']) ? trim($_GET['email']) : '';
    
    if (empty($email)) {
        echo json_encode(['error' => 'Email is required']);
        exit;
    }

    try {
        // Get user ID
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        // Get latest OTP
        $stmt = $pdo->prepare("
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
        
        $stmt->execute([$user['id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'No OTP found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>