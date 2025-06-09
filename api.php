<?php
include "db.php";

// Add these headers at the top of all PHP API files
header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = array('success' => false, 'message' => '');
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'users':
        // Handle user operations
        break;
    case 'queue':
        // Handle queue operations
        break;
    default:
        $response['message'] = 'Invalid action';
}

echo json_encode($response);
?>