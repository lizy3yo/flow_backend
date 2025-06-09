<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$data = json_decode(file_get_contents('php://input'), true);
$response = array();

if (isset($data['email']) && isset($data['password']) && isset($data['firstName']) && isset($data['lastName'])) {
    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $response = array('success' => false, 'message' => 'Invalid email format');
        echo json_encode($response);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = array('success' => false, 'message' => 'Email already exists');
    } else {
        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data['email'], $hashed_password, $data['firstName'], $data['lastName']);
        
        if ($stmt->execute()) {
            $response = array(
                'success' => true,
                'message' => 'User registered successfully',
                'user_id' => $stmt->insert_id
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Registration failed'
            );
        }
    }
    $stmt->close();
} else {
    $response = array(
        'success' => false,
        'message' => 'All fields are required'
    );
}

echo json_encode($response);
?>