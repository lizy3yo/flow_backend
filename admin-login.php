<?php
include 'db.php';

// Set CORS headers first
header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
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
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($data['password'], $admin['password'])) {
        // Login successful
        session_start();
        $_SESSION['admin_id'] = $admin['id'];
        
        // Generate a session token
        $session_token = bin2hex(random_bytes(32));
        
        // Store session token in database
        $token_stmt = $conn->prepare("UPDATE admins SET session_token = ? WHERE id = ?");
        $token_stmt->bind_param("si", $session_token, $admin['id']);
        $token_stmt->execute();
        $token_stmt->close();
        
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

$stmt->close();
$conn->close();