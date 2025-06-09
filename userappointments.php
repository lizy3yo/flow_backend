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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize notification helper
$userNotificationHelper = new UserNotificationHelper($pdo);

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

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $queues = [];
            foreach ($result as $row) {
                $queues[] = [
                    'number' => $row['queue_number'],
                    'department' => $row['department_name'],
                    'status' => $row['status'],
                    'waitTime' => $row['wait_time'],
                    'priority' => $row['queue_type'] === 'priority' ? 'Yes' : 'No',
                    'created_at' => $row['created_at'],
                    'scheduled_time' => $row['scheduled_time'],
                    'serviceId' => $row['service_id'],
                    'departmentId' => $row['department_id']
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
                $pdo->beginTransaction();

                // 1. Update queue status to cancelled
                $stmt = $pdo->prepare("UPDATE queues SET status = 'cancelled' WHERE queue_number = ?");
                $stmt->execute([$queueNumber]);

                // Get queue details for notification
                $stmt = $pdo->prepare("SELECT service_id FROM queues WHERE queue_number = ?");
                $stmt->execute([$queueNumber]);
                $queueDetails = $stmt->fetch(PDO::FETCH_ASSOC);

                // Send notification for cancelled queue
                $userNotificationHelper->notifyQueueCancelled(
                    $user_id,
                    $queueNumber,
                    $queueDetails['service_id']
                );

                // 2. Insert into archived_queues
                $stmt = $pdo->prepare("INSERT INTO archived_queues 
                    (queue_id, service_id, user_id, queue_number, status, queue_type_id, 
                    scheduled_time, created_at, serving_start_time, elapsed_time, 
                    archive_reason)
                    SELECT id, service_id, user_id, queue_number, 'cancelled', queue_type_id,
                    scheduled_time, created_at, serving_start_time, elapsed_time,
                    'User cancelled queue'
                    FROM queues WHERE queue_number = ?");
                $stmt->execute([$queueNumber]);

                // 3. Delete from queues
                $stmt = $pdo->prepare("DELETE FROM queues WHERE queue_number = ?");
                $stmt->execute([$queueNumber]);

                $pdo->commit();
                echo json_encode(['success' => true]);

            } else {
                throw new Exception('Invalid action');
            }

        } catch (Exception $e) {
            $pdo->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
}
?>