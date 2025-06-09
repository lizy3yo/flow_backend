<?php
require_once __DIR__ . '/../vendor/autoload.php';
include 'db.php';

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$client_id = '423373752798-3lmutkhmfcs5a646l1up4gceciintqim.apps.googleusercontent.com';
$client_secret = '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';

try {
    if (!isset($data['token'])) {
        throw new Exception('No token provided');
    }

    $token_url = 'https://oauth2.googleapis.com/token';
    $params = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $data['token'],
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'https://flow-i3g6.vercel.app'
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    if ($response === false) {
        throw new Exception('cURL error: ' . curl_error($ch));
    }
    
    $auth_data = json_decode($response, true);
    if (!$auth_data) {
        throw new Exception('Invalid response from Google: ' . $response);
    }

    if (isset($auth_data['access_token'])) {
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $ch = curl_init($user_info_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $auth_data['access_token']]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $user_info_response = curl_exec($ch);
        $user_info = json_decode($user_info_response, true);

        if (!$user_info) {
            throw new Exception('Failed to get user info from Google');
        }

        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $user_info['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            // Create new user without password
            $stmt = $conn->prepare("INSERT INTO users (email, first_name, last_name, password) VALUES (?, ?, ?, '')");
            $stmt->bind_param("sss", 
                $user_info['email'], 
                $user_info['given_name'], // Change from name to given_name
                $user_info['family_name']  // Change from id to family_name
            );
            $stmt->execute();
            $user_id = $conn->insert_id;
            $needs_password = true;
        } else {
            $user_id = $user['id'];
            $needs_password = empty($user['password']);
        }

        // Start session
        session_start();
        $_SESSION['user_id'] = $user_id;

        // Generate session token
        $session_token = bin2hex(random_bytes(32));
        
        // Store session token in database
        $token_stmt = $conn->prepare("UPDATE users SET session_token = ? WHERE id = ?");
        $token_stmt->bind_param("si", $session_token, $user_id);
        $token_stmt->execute();

        // Return user data along with token
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user_id,
                'email' => $user_info['email'],
                'name' => $user_info['name']
            ],
            'user_id' => $user_id,
            'session_token' => $session_token,
            'needs_password' => $needs_password
        ]);
    } else {
        if (isset($auth_data['error'])) {
            throw new Exception($auth_data['error_description'] ?? $auth_data['error']);
        }
        throw new Exception('Failed to get access token from Google');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication failed: ' . $e->getMessage()
    ]);
}

$conn->close();
?>