<?php

session_start();
include "db.php";
require_once "Helper/notificationhelper.php";

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$adminId = $_SESSION['admin_id'];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get page and limit from query parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        try {
            // Get total count for pagination
            $countStmt = $conn->prepare("
                SELECT COUNT(*) as total 
                FROM admin_notifications 
                WHERE admin_id = ?
            ");
            $countStmt->bind_param("i", $adminId);
            $countStmt->execute();
            $totalCount = $countStmt->get_result()->fetch_assoc()['total'];
            $totalPages = ceil($totalCount / $limit);

            // Get notifications with pagination
            $stmt = $conn->prepare("
                SELECT * 
                FROM admin_notifications 
                WHERE admin_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("iii", $adminId, $limit, $offset);
            $stmt->execute();
            $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Get unread count
            $unreadStmt = $conn->prepare("
                SELECT COUNT(*) as unread 
                FROM admin_notifications 
                WHERE admin_id = ? AND read_at IS NULL
            ");
            $unreadStmt->bind_param("i", $adminId);
            $unreadStmt->execute();
            $unreadCount = $unreadStmt->get_result()->fetch_assoc()['unread'];

            echo json_encode([
                'notifications' => $notifications,
                'totalPages' => $totalPages,
                'unread' => $unreadCount,
                'totalCount' => $totalCount  // Add this line
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
                // Mark all notifications as read
                $stmt = $conn->prepare("
                    UPDATE admin_notifications 
                    SET read_at = NOW() 
                    WHERE admin_id = ? AND read_at IS NULL
                ");
                $stmt->bind_param("i", $adminId);
            } else if (isset($data['notification_id'])) {
                // Mark single notification as read
                $stmt = $conn->prepare("
                    UPDATE admin_notifications 
                    SET read_at = NOW() 
                    WHERE id = ? AND admin_id = ?
                ");
                $stmt->bind_param("ii", $data['notification_id'], $adminId);
            } else {
                throw new Exception('Invalid request');
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to update notification');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}