<?php
include 'db.php';

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$response = array();

if (isset($data['email']) && isset($data['password']) && isset($data['firstName']) && isset($data['lastName'])) {
    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $response = array('success' => false, 'message' => 'Invalid email format');
        echo json_encode($response);
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);

        if ($stmt->rowCount() > 0) {
            $response = array('success' => false, 'message' => 'Email already exists');
        } else {
            // Hash password
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['email'], $hashed_password, $data['firstName'], $data['lastName']]);
            
            $response = array(
                'success' => true,
                'message' => 'User registered successfully',
                'user_id' => $pdo->lastInsertId()
            );
        }
    } catch (Exception $e) {
        $response = array(
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        );
    }
} else {
    $response = array(
        'success' => false,
        'message' => 'All fields are required'
    );
}

echo json_encode($response);
?>