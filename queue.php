<?php
// queue.php

session_start();
include "db.php";
require_once "Helper/notificationhelper.php";
require_once "Helper/usernotificationhelper.php"; // Already included

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

// Get session token from Authorization header
$headers = getallheaders();
$sessionToken = null;

if (isset($headers['Authorization'])) {
    $sessionToken = str_replace('Bearer ', '', $headers['Authorization']);
} elseif (isset($headers['authorization'])) {
    $sessionToken = str_replace('Bearer ', '', $headers['authorization']);
}

// If no token found, check if user is logged in via session
if (!$sessionToken && !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized - No token provided']);
    exit();
}

// Validate the session token if present
if ($sessionToken) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE session_token = ?");
    $stmt->bind_param("s", $sessionToken);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid session token']);
        exit();
    }
    
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Initialize both notification helpers
$notificationHelper = new NotificationHelper($conn);
$userNotificationHelper = new UserNotificationHelper($conn);

function generateQueueNumber($serviceId, $prefix, $queueType) {
    global $conn;
    
    $today = date('Y-m-d');
    
    // Get or create counter for today
    $sql = "INSERT INTO queue_counters (service_id, queue_type, date, last_number) 
            VALUES (?, ?, ?, 0)
            ON DUPLICATE KEY UPDATE last_number = last_number + 1";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $serviceId, $queueType, $today);
    $stmt->execute();
    
    // Get the current counter value
    $sql = "SELECT last_number FROM queue_counters 
            WHERE service_id = ? AND queue_type = ? AND date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $serviceId, $queueType, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $number = ($row['last_number'] + 1);
    
    // Format the queue number
    return $prefix . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['service_id'], $data['queue_type_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit();
        }

        try {
            // Use provided prefix from request instead of getting from services table
            $prefix = isset($data['ticket_prefix']) ? $data['ticket_prefix'] : 'A';
            
            // Generate queue number
            $queueNumber = generateQueueNumber($data['service_id'], $prefix, $data['queue_type_id']);

            // Insert new queue
            $stmt = $conn->prepare("
                INSERT INTO queues (
                    user_id, 
                    service_id, 
                    queue_number, 
                    queue_type_id, 
                    status,
                    scheduled_time,
                    estimated_wait,
                    notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $status = 'pending';
            $scheduledTime = isset($data['scheduled_time']) ? $data['scheduled_time'] : null;
            $estimatedWait = isset($data['estimated_wait']) ? $data['estimated_wait'] : null;
            $notes = isset($data['notes']) ? $data['notes'] : null;

            $stmt->bind_param(
                'iisissis',
                $_SESSION['user_id'],
                $data['service_id'],
                $queueNumber,
                $data['queue_type_id'],
                $status,
                $scheduledTime,
                $estimatedWait,
                $notes
            );

            if ($stmt->execute()) {
                $queueId = $conn->insert_id;
                
                // Get service name for notification
                $serviceStmt = $conn->prepare("SELECT name FROM services WHERE id = ?");
                $serviceStmt->bind_param('i', $data['service_id']);
                $serviceStmt->execute();
                $serviceName = $serviceStmt->get_result()->fetch_assoc()['name'];

                // Get admin_id for the service
                $adminStmt = $conn->prepare("SELECT admin_id FROM services WHERE id = ?");
                $adminStmt->bind_param('i', $data['service_id']);
                $adminStmt->execute();
                $adminId = $adminStmt->get_result()->fetch_assoc()['admin_id'];

                // Create notification for admin
                $notificationHelper->createNotification(
                    $adminId,
                    'queue',
                    $notificationHelper->formatQueueAction('accept', $queueNumber, $serviceName),
                    'new_queue',
                    $queueId
                );

                // Create notification for user
                $userNotificationHelper->notifyQueueJoined(
                    $_SESSION['user_id'], 
                    $queueNumber, 
                    $serviceName, 
                    $data['service_id']
                );

                // If queue has estimated wait, notify user
                if ($estimatedWait) {
                    $userNotificationHelper->notifyWaitTimeUpdate(
                        $_SESSION['user_id'],
                        $queueNumber,
                        $estimatedWait,
                        $data['service_id']
                    );
                }

                // If scheduled appointment, set a reminder notification
                if ($scheduledTime) {
                    $userNotificationHelper->notifyUpcomingAppointment(
                        $_SESSION['user_id'],
                        $queueNumber,
                        $serviceName,
                        $scheduledTime,
                        $data['service_id']
                    );
                }

                echo json_encode([
                    'success' => true,
                    'queue_number' => $queueNumber,
                    'message' => 'Queue created successfully'
                ]);
            } else {
                throw new Exception('Failed to create queue');
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'GET':
        try {
            $userId = $_SESSION['user_id'];
            $serviceId = isset($_GET['service_id']) ? $_GET['service_id'] : null;

            // Get user's queues
            $sql = "SELECT q.*, qt.name as queue_type 
                    FROM queues q
                    JOIN queue_types qt ON q.queue_type_id = qt.id
                    WHERE q.user_id = ? AND q.service_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $userId, $serviceId);
            $stmt->execute();
            $yourQueues = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Get all current queues for this service
            $sql = "SELECT q.* FROM queues q 
                    WHERE q.service_id = ? AND q.status IN ('pending', 'serving')
                    ORDER BY q.created_at ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $serviceId);
            $stmt->execute();
            $currentQueues = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Get currently serving queue
            $currentServing = '';
            foreach ($currentQueues as $queue) {
                if ($queue['status'] === 'serving') {
                    $currentServing = $queue['queue_number'];
                    break;
                }
            }

            // Get next in line
            $nextInLine = '';
            foreach ($currentQueues as $queue) {
                if ($queue['status'] === 'pending') {
                    $nextInLine = $queue['queue_number'];
                    break;
                }
            }

            echo json_encode([
                'yourQueues' => $yourQueues,
                'currentQueues' => $currentQueues,
                'currentServing' => $currentServing,
                'nextInLine' => $nextInLine,
                'totalInQueue' => count($currentQueues),
                'estimatedWait' => count($currentQueues) * 5 // Example: 5 mins per queue
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        // For status updates or cancellations
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['status']) && isset($data['queue_id'])) {
            try {
                // Get queue and service details before updating
                $queueStmt = $conn->prepare("
                    SELECT q.queue_number, q.user_id, q.service_id, s.name as service_name, q.status as current_status
                    FROM queues q 
                    JOIN services s ON q.service_id = s.id 
                    WHERE q.id = ?
                ");
                $queueStmt->bind_param("i", $data['queue_id']);
                $queueStmt->execute();
                $queueData = $queueStmt->get_result()->fetch_assoc();
                
                // Only proceed if status is changing
                if ($queueData['current_status'] != $data['status']) {
                    // Update the queue status
                    $stmt = $conn->prepare("UPDATE queues SET status = ? WHERE id = ?");
                    $stmt->bind_param("si", $data['status'], $data['queue_id']);
                    
                    if ($stmt->execute()) {
                        // Send appropriate notification based on status
                        if ($data['status'] === 'pending') {
                            $userNotificationHelper->notifyQueueJoined(
                                $queueData['user_id'],
                                $queueData['queue_number'],
                                $queueData['service_name'],
                                $queueData['service_id']
                            );
                        } elseif ($data['status'] === 'waiting') {
                            // Add notification for waiting status
                            $userNotificationHelper->createNotification(
                                $queueData['user_id'],
                                'queue_waiting',
                                "Your queue number {$queueData['queue_number']} is now in the waiting list.",
                                $queueData['queue_number'],
                                $queueData['service_id']
                            );
                        } elseif ($data['status'] === 'serving') {
                            $userNotificationHelper->notifyNowServing(
                                $queueData['user_id'],
                                $queueData['queue_number'],
                                $queueData['service_id']
                            );
                        } elseif ($data['status'] === 'completed') {
                            $userNotificationHelper->notifyQueueCompleted(
                                $queueData['user_id'],
                                $queueData['queue_number'],
                                $queueData['service_id']
                            );
                        } elseif ($data['status'] === 'cancelled') {
                            $userNotificationHelper->notifyQueueCancelled(
                                $queueData['user_id'],
                                $queueData['queue_number'],
                                $queueData['service_id']
                            );
                        }
                        
                        // After status change, update position notifications for remaining queues
                        if (in_array($data['status'], ['waiting', 'serving', 'completed', 'cancelled'])) {
                            // Get all affected users in the queue
                            $affectedUsersStmt = $conn->prepare("
                                SELECT 
                                    q.id,
                                    q.user_id,
                                    q.queue_number,
                                    q.service_id,
                                    ROW_NUMBER() OVER (ORDER BY q.created_at) as position
                                FROM queues q
                                WHERE q.service_id = ? 
                                AND q.status IN ('pending', 'waiting')
                                ORDER BY q.created_at
                            ");
                            $affectedUsersStmt->bind_param("i", $queueData['service_id']);
                            $affectedUsersStmt->execute();
                            $result = $affectedUsersStmt->get_result();
                            
                            // Notify each user of their updated position
                            while ($row = $result->fetch_assoc()) {
                                $userNotificationHelper->notifyPositionUpdate(
                                    $row['user_id'],
                                    $row['queue_number'],
                                    $row['position'],
                                    $row['service_id']
                                );
                                
                                // If they're next in line, send special notification
                                if ($row['position'] == 1) {
                                    $userNotificationHelper->notifyNextInLine(
                                        $row['user_id'],
                                        $row['queue_number'],
                                        $row['service_id']
                                    );
                                }
                            }
                        }

                        echo json_encode(['success' => true]);
                    } else {
                        throw new Exception('Failed to update queue status');
                    }
                } else {
                    // Status hasn't changed
                    echo json_encode(['success' => true, 'message' => 'Status unchanged']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        } elseif (isset($data['estimated_wait']) && isset($data['queue_id'])) {
            // Update estimated wait time
            try {
                $stmt = $conn->prepare("
                    UPDATE queues 
                    SET estimated_wait = ? 
                    WHERE id = ?
                ");
                $stmt->bind_param("ii", $data['estimated_wait'], $data['queue_id']);
                
                if ($stmt->execute()) {
                    // Get queue details
                    $queueStmt = $conn->prepare("
                        SELECT queue_number, user_id, service_id
                        FROM queues
                        WHERE id = ?
                    ");
                    $queueStmt->bind_param("i", $data['queue_id']);
                    $queueStmt->execute();
                    $queueData = $queueStmt->get_result()->fetch_assoc();
                    
                    // Notify user of updated wait time
                    $userNotificationHelper->notifyWaitTimeUpdate(
                        $queueData['user_id'],
                        $queueData['queue_number'],
                        $data['estimated_wait'],
                        $queueData['service_id']
                    );
                    
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Failed to update estimated wait time');
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
        }
        break;
}
?>