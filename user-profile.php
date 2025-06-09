<?php
session_start();
include "db.php";

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

// Check if user is logged in and validate session token
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Verify session token
$stmt = $conn->prepare("SELECT session_token FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || empty($user['session_token'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid session']);
    exit();
}

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mimeType, $allowedMimes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type']);
        exit;
    }

    // Sanitize filename
    $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $newFileName = 'avatar_' . $user_id . '_' . time() . '.' . $fileExtension;
    
    // Change the upload directory to be inside the web root
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/flow-application-cc/uploads/avatars/';
    
    // Create directories if they don't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create upload directory']);
            exit;
        }
        chmod($uploadDir, 0777);
    }

    $targetFile = $uploadDir . $newFileName;

    // Check file type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExtension, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type']);
        exit;
    }

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
        // Store relative path in database
        $relativePath = '/flow-application-cc/uploads/avatars/' . $newFileName;
        
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param('si', $relativePath, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'avatar' => $relativePath
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update database']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
    }
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Updated GET to include avatar
        $sql = "SELECT id, first_name, last_name, email, role, avatar 
                FROM users 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data) {
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $sql = "UPDATE users SET";
            $types = "";
            $params = [];
            
            if (isset($data['first_name'])) {
                $sql .= " first_name = ?,";
                $types .= "s";
                $params[] = $data['first_name'];
            }
            
            if (isset($data['last_name'])) {
                $sql .= " last_name = ?,";
                $types .= "s";
                $params[] = $data['last_name'];
            }
            
            if (isset($data['email'])) {
                $sql .= " email = ?,";
                $types .= "s";
                $params[] = $data['email'];
            }
            
            if (isset($data['password']) && !empty($data['password'])) {
                $sql .= " password = ?,";
                $types .= "s";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $sql = rtrim($sql, ",");
            $sql .= " WHERE id = ?";
            $types .= "i";
            $params[] = $user_id;
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update profile');
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to update profile',
                'details' => $e->getMessage()
            ]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['action']) && $data['action'] === 'delete_avatar') {
            // Get current avatar path
            $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current = $result->fetch_assoc();
            
            if ($current && $current['avatar']) {
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $current['avatar'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                $stmt = $conn->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
                $stmt->bind_param('i', $user_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to update database']);
                }
            } else {
                echo json_encode(['success' => true]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid delete request']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>