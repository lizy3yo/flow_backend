<?php
session_start();
include "db.php";
require_once "Helper/notificationhelper.php";
$notificationHelper = new NotificationHelper($pdo);

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    // Use session admin_id instead of hardcoded value
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/flow-application-cc/uploads/avatars/';
    
    // Create directories recursively if they don't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create upload directory']);
            exit;
        }
        chmod($uploadDir, 0777); // Ensure write permissions
    }

    $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $newFileName = 'avatar_' . $admin_id . '_' . time() . '.' . $fileExtension;
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
        
        $stmt = $pdo->prepare("UPDATE establishments SET avatar = ? WHERE admin_id = ?");
        $stmt->execute([$relativePath, $admin_id]);
        
        // Add notification
        $notificationHelper->createNotification(
            $admin_id,
            'profile',
            $notificationHelper->formatProfileAction('avatar'),
            'update_avatar',
            null
        );
        echo json_encode([
            'success' => true,
            'avatar' => $relativePath
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
    }
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Fetch both admin and establishment data
        $sql = "SELECT a.*, e.description, e.queue_status, e.location, e.address, 
                e.building_type, e.hours_start, e.hours_end, e.avatar 
                FROM admins a 
                LEFT JOIN establishments e ON a.id = e.admin_id 
                WHERE a.id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            unset($data['password']);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Admin not found']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Check if this is just a status update
        if (isset($data['action']) && $data['action'] === 'update_status_only') {
            // Only update the queue_status field
            $stmt = $pdo->prepare("UPDATE establishments SET queue_status = ? WHERE admin_id = ?");
            $stmt->execute([$data['queue_status'], $admin_id]);
            
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            exit;
        }
        
        // Begin transaction
        $pdo->beginTransaction();
        
        try {
            // Handle admin account updates
            if (isset($data['name']) || isset($data['email']) || isset($data['password'])) {
                $sql = "UPDATE admins SET";
                $params = [];
                
                if (isset($data['name'])) {
                    $sql .= " name = ?, ";
                    $params[] = $data['name'];
                }
                
                if (isset($data['email'])) {
                    $sql .= " email = ?, ";
                    $params[] = $data['email'];
                }
                
                if (isset($data['password']) && !empty($data['password'])) {
                    $sql .= " password = ?, ";
                    $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
                }
                
                $sql = rtrim($sql, ", ");
                $sql .= " WHERE id = ?";
                $params[] = $admin_id;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }

            // Handle establishment updates
            if (isset($data['description']) || isset($data['queue_status']) || 
                isset($data['location']) || isset($data['address']) || 
                isset($data['building_type']) || isset($data['hours_start']) || 
                isset($data['hours_end'])) {
                
                // Check if establishment exists
                $check = $pdo->prepare("SELECT id FROM establishments WHERE admin_id = ?");
                $check->execute([$admin_id]);
                $exists = $check->rowCount() > 0;
                
                if ($exists) {
                    // Update existing establishment
                    $sql = "UPDATE establishments SET 
                            description = ?, 
                            queue_status = ?, 
                            location = ?, 
                            address = ?, 
                            building_type = ?, 
                            hours_start = ?, 
                            hours_end = ? 
                            WHERE admin_id = ?";
                } else {
                    // Insert new establishment
                    $sql = "INSERT INTO establishments 
                            (description, queue_status, location, address, 
                             building_type, hours_start, hours_end, admin_id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                }
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['description'],
                    $data['queue_status'],
                    $data['location'],
                    $data['address'],
                    $data['building_type'],
                    $data['hours_start'],
                    $data['hours_end'],
                    $admin_id
                ]);
            }
            
            // For admin profile updates
            if (isset($data['name']) || isset($data['email']) || isset($data['password'])) {
                if (isset($data['name'])) {
                    $notificationHelper->createNotification(
                        $admin_id,
                        'profile',
                        $notificationHelper->formatProfileAction('name'),
                        'update_name',
                        null
                    );
                }
                if (isset($data['email'])) {
                    $notificationHelper->createNotification(
                        $admin_id,
                        'profile',
                        $notificationHelper->formatProfileAction('email'),
                        'update_email',
                        null
                    );
                }
                if (isset($data['password'])) {
                    $notificationHelper->createNotification(
                        $admin_id,
                        'profile',
                        $notificationHelper->formatProfileAction('password'),
                        'update_password',
                        null
                    );
                }
            }

            // For establishment updates
            if (isset($data['description']) || isset($data['queue_status']) || 
                isset($data['location']) || isset($data['address'])) {
                
                if (isset($data['description'])) {
                    $notificationHelper->createNotification(
                        $admin_id,
                        'establishment',
                        $notificationHelper->formatEstablishmentAction('description'),
                        'update_description',
                        null
                    );
                }
                if (isset($data['location'])) {
                    $notificationHelper->createNotification(
                        $admin_id,
                        'establishment',
                        $notificationHelper->formatEstablishmentAction('location'),
                        'update_location',
                        null
                    );
                }
                if (isset($data['queue_status'])) {
                    $notificationHelper->createNotification(
                        $admin_id,
                        'status',
                        $notificationHelper->formatStatusAction($data['queue_status']),
                        'update_status',
                        null
                    );
                }
            }
            
            $pdo->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
            
        } catch (Exception $e) {
            $pdo->rollback();
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
            $stmt = $pdo->prepare("SELECT avatar FROM establishments WHERE admin_id = ?");
            $stmt->execute([$admin_id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($current && $current['avatar']) {
                // Use document root to get absolute file path
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $current['avatar'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Update database to remove avatar reference
                $stmt = $pdo->prepare("UPDATE establishments SET avatar = NULL WHERE admin_id = ?");
                $stmt->execute([$admin_id]);
                
                echo json_encode(['success' => true]);
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
