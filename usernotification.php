<?php
session_start();
include "db.php";
require_once "Helper/usernotificationhelper.php";

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

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
            $countStmt = $pdo->prepare("
                SELECT COUNT(*) as total 
                FROM user_notifications 
                WHERE user_id = ?
            ");
            $countStmt->execute([$userId]);
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalCount / $limit);

            // Get notifications with pagination
            $stmt = $pdo->prepare("
                SELECT n.*, s.name as service_name 
                FROM user_notifications n
                LEFT JOIN services s ON n.service_id = s.id 
                WHERE n.user_id = ? 
                ORDER BY n.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $limit, $offset]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get unread count
            $unreadStmt = $pdo->prepare("
                SELECT COUNT(*) as unread 
                FROM user_notifications 
                WHERE user_id = ? AND read_at IS NULL
            ");
            $unreadStmt->execute([$userId]);
            $unreadCount = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread'];

            // Get current active queues for real-time position updates
            $activeQueuesStmt = $pdo->prepare("
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
            $activeQueuesStmt->execute([$userId]);
            $activeQueues = $activeQueuesStmt->fetchAll(PDO::FETCH_ASSOC);

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
                $stmt = $pdo->prepare("
                    UPDATE user_notifications 
                    SET read_at = NOW() 
                    WHERE user_id = ? AND read_at IS NULL
                ");
                $stmt->execute([$userId]);
            } else if (isset($data['notification_id'])) {
                // Mark single notification as read
                $stmt = $pdo->prepare("
                    UPDATE user_notifications 
                    SET read_at = NOW() 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$data['notification_id'], $userId]);
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