<?php

session_start();
include "db.php";
require_once "Helper/usernotificationhelper.php"; // Add this line

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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize notification helper
$userNotificationHelper = new UserNotificationHelper($conn);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            // Get all queues for the user with service details
            $sql = "SELECT q.*, s.name as department_name, s.admin_id as department_id, 
                    qt.name as queue_type,
                    TIME_FORMAT(TIMEDIFF(CURRENT_TIMESTAMP, q.created_at), '%H:%i:%s') as wait_time
                    FROM queues q
                    JOIN services s ON q.service_id = s.id
                    JOIN queue_types qt ON q.queue_type_id = qt.id
                    WHERE q.user_id = ?
                    ORDER BY q.created_at DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $queues = [];
            while ($row = $result->fetch_assoc()) {
                $queues[] = [
                    'number' => $row['queue_number'],
                    'department' => $row['department_name'],
                    'status' => $row['status'],
                    'waitTime' => $row['wait_time'],
                    'priority' => $row['queue_type'] === 'priority' ? 'Yes' : 'No',
                    'created_at' => $row['created_at'],
                    'scheduled_time' => $row['scheduled_time'],
                    'serviceId' => $row['service_id'],
                    'departmentId' => $row['department_id']  // Add this line
                ];
            }

            echo json_encode($queues);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $queueNumber = $data['queue_number'];
            
            if ($data['action'] === 'cancel_and_archive') {
                // Begin transaction
                $conn->begin_transaction();

                // 1. Update queue status to cancelled
                $updateSql = "UPDATE queues SET status = 'cancelled' WHERE queue_number = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("s", $queueNumber);
                $stmt->execute();

                // Get queue details for notification
                $queueDetailsSql = "SELECT service_id FROM queues WHERE queue_number = ?";
                $stmt = $conn->prepare($queueDetailsSql);
                $stmt->bind_param("s", $queueNumber);
                $stmt->execute();
                $queueDetails = $stmt->get_result()->fetch_assoc();

                // Send notification for cancelled queue
                $userNotificationHelper->notifyQueueCancelled(
                    $user_id,
                    $queueNumber,
                    $queueDetails['service_id']
                );

                // 2. Insert into archived_queues
                $archiveSql = "INSERT INTO archived_queues 
                    (queue_id, service_id, user_id, queue_number, status, queue_type_id, 
                    scheduled_time, created_at, serving_start_time, elapsed_time, 
                    archive_reason)
                    SELECT id, service_id, user_id, queue_number, 'cancelled', queue_type_id,
                    scheduled_time, created_at, serving_start_time, elapsed_time,
                    'User cancelled queue'
                    FROM queues WHERE queue_number = ?";
                $stmt = $conn->prepare($archiveSql);
                $stmt->bind_param("s", $queueNumber);
                $stmt->execute();

                // 3. Delete from queues
                $deleteSql = "DELETE FROM queues WHERE queue_number = ?";
                $stmt = $conn->prepare($deleteSql);
                $stmt->bind_param("s", $queueNumber);
                $stmt->execute();

                $conn->commit();
                echo json_encode(['success' => true]);

            } else {
                throw new Exception('Invalid action');
            }

        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
}
?>