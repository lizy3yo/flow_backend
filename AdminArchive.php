<?php
include "db.php";

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Handle GET requests - Fetch archives
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = $_GET['type'] ?? '';
    
    try {
        if ($type === 'services') {
            $stmt = $pdo->prepare("SELECT * FROM archived_services WHERE restored_at IS NULL ORDER BY archived_at DESC");
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("SELECT aq.*, s.name as service_name, u.first_name as user_first_name, u.last_name as user_last_name 
                      FROM archived_queues aq 
                      LEFT JOIN services s ON aq.service_id = s.id 
                      LEFT JOIN users u ON aq.user_id = u.id
                      WHERE aq.restored_at IS NULL 
                      ORDER BY aq.archived_at DESC");
            $stmt->execute();
        }
        
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Handle POST requests - Restore/Delete archives
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $type = $data['type'] ?? '';
    $id = $data['id'] ?? 0;
    
    try {
        if ($action === 'restore') {
            if ($type === 'service') {
                // First get the service ID from archived_services
                $stmt = $pdo->prepare("SELECT service_id FROM archived_services WHERE id = ?");
                $stmt->execute([$id]);
                $serviceData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($serviceData) {
                    // Update the original service
                    $stmt = $pdo->prepare("UPDATE services SET is_archived = 0 WHERE id = ?");
                    $stmt->execute([$serviceData['service_id']]);
                    
                    // Delete from archived_services
                    $stmt = $pdo->prepare("DELETE FROM archived_services WHERE id = ?");
                    $stmt->execute([$id]);
                }
            } else {
                // First get the archived queue data
                $stmt = $pdo->prepare("SELECT * FROM archived_queues WHERE id = ?");
                $stmt->execute([$id]);
                $queueData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($queueData) {
                    // Insert back into the queues table
                    $stmt = $pdo->prepare("INSERT INTO queues (
                        id, user_id, service_id, queue_number, queue_type_id, status, created_at,
                        scheduled_time, estimated_wait, notes, serving_start_time, elapsed_time
                    ) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, NULL, '00:00:00')");
                    
                    $stmt->execute([
                        $queueData['queue_id'],
                        $queueData['user_id'],
                        $queueData['service_id'],
                        $queueData['queue_number'],
                        $queueData['queue_type_id'],
                        $queueData['created_at'],
                        $queueData['scheduled_time'],
                        $queueData['estimated_wait'],
                        $queueData['notes']
                    ]);
                    
                    // Delete from archived_queues
                    $stmt = $pdo->prepare("DELETE FROM archived_queues WHERE id = ?");
                    $stmt->execute([$id]);
                }
            }
            
            echo json_encode(['success' => true]);
        }
        else if ($action === 'delete') {
            // Check if the item exists
            if ($type === 'service') {
                $stmt = $pdo->prepare("SELECT id FROM archived_services WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("SELECT id FROM archived_queues WHERE id = ?");
            }
            
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Item not found']);
                exit;
            }
            
            // Proceed with deletion
            if ($type === 'service') {
                $stmt = $pdo->prepare("DELETE FROM archived_services WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("DELETE FROM archived_queues WHERE id = ?");
            }
            
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Archive cron job function
function archiveOldQueues() {
    global $pdo;
    
    try {
        // Archive completed and declined queues older than 24 hours
        $stmt = $pdo->prepare("INSERT INTO archived_queues 
                  SELECT *, NOW(), NULL, NULL, 'Auto-archived after 24 hours'
                  FROM queues 
                  WHERE (status IN ('completed', 'declined') 
                  AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                  OR (scheduled_time < NOW() AND status = 'pending')");
        $stmt->execute();
        
        // Remove archived queues from main table
        $stmt = $pdo->prepare("DELETE FROM queues 
                    WHERE (status IN ('completed', 'declined') 
                    AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                    OR (scheduled_time < NOW() AND status = 'pending')");
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Archive error: " . $e->getMessage());
    }
}

// This can be called by a cron job
if (isset($_GET['cron']) && $_GET['cron'] === 'archive') {
    archiveOldQueues();
    echo json_encode(['success' => true, 'message' => 'Archive process completed']);
}
?>