<?php


session_start();
include "db.php";
require_once "Helper/usernotificationhelper.php";

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

// Check user authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];

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
                FROM user_notifications 
                WHERE user_id = ?
            ");
            $countStmt->bind_param("i", $userId);
            $countStmt->execute();
            $totalCount = $countStmt->get_result()->fetch_assoc()['total'];
            $totalPages = ceil($totalCount / $limit);

            // Get notifications with pagination
            $stmt = $conn->prepare("
                SELECT n.*, s.name as service_name 
                FROM user_notifications n
                LEFT JOIN services s ON n.service_id = s.id 
                WHERE n.user_id = ? 
                ORDER BY n.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("iii", $userId, $limit, $offset);
            $stmt->execute();
            $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Get unread count
            $unreadStmt = $conn->prepare("
                SELECT COUNT(*) as unread 
                FROM user_notifications 
                WHERE user_id = ? AND read_at IS NULL
            ");
            $unreadStmt->bind_param("i", $userId);
            $unreadStmt->execute();
            $unreadCount = $unreadStmt->get_result()->fetch_assoc()['unread'];

            // Get current active queues for real-time position updates
            $activeQueuesStmt = $conn->prepare("
                SELECT 
                    q.id,
                    q.queue_number,
                    s.name as service_name,
                    q.status,
                    COUNT(q2.id) as position,
                    (COUNT(q2.id) * 15) as estimated_wait
                FROM queues q
                JOIN services s ON q.service_id = s.id
                LEFT JOIN queues q2 ON q2.service_id = q.service_id 
                    AND q2.status = 'pending' 
                    AND q2.created_at < q.created_at
                WHERE q.user_id = ? AND q.status IN ('pending', 'serving')
                GROUP BY q.id
                ORDER BY q.created_at DESC
            ");
            $activeQueuesStmt->bind_param("i", $userId);
            $activeQueuesStmt->execute();
            $activeQueues = $activeQueuesStmt->get_result()->fetch_all(MYSQLI_ASSOC);

            echo json_encode([
                'notifications' => $notifications,
                'totalPages' => $totalPages,
                'unread' => $unreadCount,
                'activeQueues' => $activeQueues
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
                    UPDATE user_notifications 
                    SET read_at = NOW() 
                    WHERE user_id = ? AND read_at IS NULL
                ");
                $stmt->bind_param("i", $userId);
            } else if (isset($data['notification_id'])) {
                // Mark single notification as read
                $stmt = $conn->prepare("
                    UPDATE user_notifications 
                    SET read_at = NOW() 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->bind_param("ii", $data['notification_id'], $userId);
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