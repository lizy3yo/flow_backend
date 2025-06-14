<?php
session_start();
include "db.php";

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get all queues for a specific service
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $service_id = isset($_GET['service_id']) ? $_GET['service_id'] : null;
    
    if (!$service_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Service ID is required']);
        exit();
    }

    try {
        // Get regular queues
        $regularQuery = "SELECT q.*, CONCAT(u.first_name, ' ', u.last_name) as name,
                        TIME_FORMAT(q.created_at, '%h:%i %p') as time,
                        u.avatar
                        FROM queues q
                        JOIN users u ON q.user_id = u.id
                        WHERE q.service_id = ? AND q.queue_type_id = 1
                        ORDER BY q.created_at ASC";
        
        $stmt = $pdo->prepare($regularQuery);
        $stmt->execute([$service_id]);
        $regularQueues = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get priority queues
        $priorityQuery = "SELECT q.*, CONCAT(u.first_name, ' ', u.last_name) as name,
                         TIME_FORMAT(q.created_at, '%h:%i %p') as time,
                         u.avatar
                         FROM queues q
                         JOIN users u ON q.user_id = u.id
                         WHERE q.service_id = ? AND q.queue_type_id = 2
                         ORDER BY q.created_at ASC";
        
        $stmt = $pdo->prepare($priorityQuery);
        $stmt->execute([$service_id]);
        $priorityQueues = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get scheduled queues
        $scheduledQuery = "SELECT q.*, CONCAT(u.first_name, ' ', u.last_name) as name,
                          DATE_FORMAT(q.scheduled_time, '%Y-%m-%d') as appointmentDate,
                          TIME_FORMAT(q.scheduled_time, '%h:%i %p') as time,
                          u.avatar
                          FROM queues q
                          JOIN users u ON q.user_id = u.id
                          WHERE q.service_id = ? AND q.queue_type_id = 3
                          ORDER BY q.scheduled_time ASC";
        
        $stmt = $pdo->prepare($scheduledQuery);
        $stmt->execute([$service_id]);
        $scheduledQueues = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'regular' => $regularQueues,
            'priority' => $priorityQueues,
            'scheduled' => $scheduledQueues
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Update queue status
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['queue_id']) || !isset($data['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Queue ID and status are required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE queues SET status = ? WHERE id = ?");
        
        if ($stmt->execute([$data['status'], $data['queue_id']])) {
            // If updating to 'serving', record the start time
            if ($data['status'] === 'serving') {
                $stmt = $pdo->prepare("UPDATE queues SET serving_start_time = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$data['queue_id']]);
            }
            
            // If updating to 'completed', calculate and store elapsed time
            if ($data['status'] === 'completed') {
                $stmt = $pdo->prepare("UPDATE queues SET 
                    elapsed_time = TIMEDIFF(CURRENT_TIMESTAMP, serving_start_time)
                    WHERE id = ?");
                $stmt->execute([$data['queue_id']]);
            }
            
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to update queue status');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Delete (archive) queue
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $queue_id = $_GET['id'] ?? null;
    
    if (!$queue_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Queue ID is required']);
        exit();
    }

    try {
        // Archive the queue first
        $reason = "Manually archived by admin";
        if (archiveQueue($pdo, $queue_id, $reason)) {
            // Then delete from main queue table
            $stmt = $pdo->prepare("DELETE FROM queues WHERE id = ?");
            
            if ($stmt->execute([$queue_id])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to delete queue');
            }
        } else {
            throw new Exception('Failed to archive queue');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Function to archive a queue
function archiveQueue($pdo, $queue_id, $reason) {
    // Get queue details
    $stmt = $pdo->prepare("SELECT * FROM queues WHERE id = ?");
    $stmt->execute([$queue_id]);
    $queue = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$queue) {
        return false;
    }

    // Insert into archived_queues
    $archiveStmt = $pdo->prepare("INSERT INTO archived_queues 
        (queue_id, service_id, user_id, queue_number, status, queue_type_id,
        scheduled_time, created_at, serving_start_time, elapsed_time, archive_reason) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    return $archiveStmt->execute([
        $queue['id'],
        $queue['service_id'],
        $queue['user_id'],
        $queue['queue_number'],
        $queue['status'],
        $queue['queue_type_id'],
        $queue['scheduled_time'],
        $queue['created_at'],
        $queue['serving_start_time'],
        $queue['elapsed_time'],
        $reason
    ]);
}
?>