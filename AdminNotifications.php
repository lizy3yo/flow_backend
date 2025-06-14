<?php
session_start();
include "db.php";

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check admin authentication using multiple methods
$admin_id = null;

// Method 1: Check session
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
} else {
    // Method 2: Check Authorization header for token
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        
        // Verify token against database
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE session_token = ?");
        $stmt->execute([$token]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $admin_id = $admin['id'];
            $_SESSION['admin_id'] = $admin_id;
        }
    }
}

if (!$admin_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$adminId = $admin_id;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        try {
            // Get total count for pagination
            $countStmt = $pdo->prepare("
                SELECT COUNT(*) as total 
                FROM admin_notifications 
                WHERE admin_id = ?
            ");
            $countStmt->execute([$adminId]);
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalCount / $limit);

            // Get notifications with pagination
            $stmt = $pdo->prepare("
                SELECT id, admin_id, type, message, action, entity_id, created_at, read_at
                FROM admin_notifications 
                WHERE admin_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$adminId, $limit, $offset]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get unread count
            $unreadStmt = $pdo->prepare("
                SELECT COUNT(*) as unread 
                FROM admin_notifications 
                WHERE admin_id = ? AND read_at IS NULL
            ");
            $unreadStmt->execute([$adminId]);
            $unreadCount = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread'];

            echo json_encode([
                'notifications' => $notifications,
                'totalPages' => $totalPages,
                'unread' => $unreadCount,
                'totalCount' => $totalCount
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            if (isset($data['markAllAsRead'])) {
                $stmt = $pdo->prepare("
                    UPDATE admin_notifications 
                    SET read_at = NOW() 
                    WHERE admin_id = ? AND read_at IS NULL
                ");
                $stmt->execute([$adminId]);
            } else if (isset($data['notification_id'])) {
                $stmt = $pdo->prepare("
                    UPDATE admin_notifications 
                    SET read_at = NOW() 
                    WHERE id = ? AND admin_id = ?
                ");
                $stmt->execute([$data['notification_id'], $adminId]);
            } else {
                throw new Exception('Invalid request');
            }

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>