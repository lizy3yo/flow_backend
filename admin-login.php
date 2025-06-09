<?php
include 'db.php';

// Fix: Update CORS headers to match your actual frontend domain
header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// Simple validation
if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->execute([$data['email']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($data['password'], $admin['password'])) {
        // Login successful
        session_start();
        $_SESSION['admin_id'] = $admin['id']; // Make sure this line exists
        
        // Generate a session token
        $session_token = bin2hex(random_bytes(32));
        
        // Store session token in database
        $token_stmt = $pdo->prepare("UPDATE admins SET session_token = ? WHERE id = ?");
        $token_stmt->execute([$session_token, $admin['id']]);
        
        // Remove password from response
        unset($admin['password']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'admin' => $admin,
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
        'message' => 'Server error'
    ]);
}
?>