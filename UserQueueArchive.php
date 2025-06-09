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

// Function to get current user from session
function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

class UserQueueArchive {
    private $pdo;

    public function __construct($connection) {
        $this->pdo = $connection;
    }

    public function archiveQueue($queueId, $reason = null) {
        try {
            $this->pdo->beginTransaction();

            // Get queue details before archiving
            $stmt = $this->pdo->prepare("SELECT * FROM queues WHERE id = ?");
            $stmt->execute([$queueId]);
            $queue = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$queue) {
                throw new Exception("Queue not found");
            }

            // Insert into archived_queues
            $stmt = $this->pdo->prepare("INSERT INTO archived_queues 
                          (queue_id, user_id, service_id, queue_number, queue_type_id, status, 
                           created_at, scheduled_time, serving_start_time, elapsed_time, archive_reason) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $queue['id'],
                $queue['user_id'], 
                $queue['service_id'],
                $queue['queue_number'],
                $queue['queue_type_id'],
                $queue['status'],
                $queue['created_at'],
                $queue['scheduled_time'],
                $queue['serving_start_time'],
                $queue['elapsed_time'],
                $reason
            ]);

            // Delete from queues table
            $stmt = $this->pdo->prepare("DELETE FROM queues WHERE id = ?");
            $stmt->execute([$queueId]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }

    public function getArchivedQueues($userId, $limit = 20, $offset = 0) {
        $stmt = $this->pdo->prepare("SELECT aq.*, s.name as service_name, s.location as service_location 
                FROM archived_queues aq 
                LEFT JOIN services s ON aq.service_id = s.id 
                WHERE aq.user_id = ? 
                AND aq.status IN ('completed', 'cancelled')
                ORDER BY aq.archived_at DESC 
                LIMIT ? OFFSET ?");
        
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Enhanced auto-archive function
    public function autoArchiveCompleted() {
        try {
            // Auto-archive completed and cancelled queues
            $stmt = $this->pdo->prepare("SELECT * FROM queues WHERE status IN ('completed', 'cancelled')");
            $stmt->execute();
            $queues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $archivedCount = 0;
            foreach ($queues as $row) {
                $this->pdo->beginTransaction();
                
                try {
                    // Insert into archived_queues
                    $stmt = $this->pdo->prepare("INSERT INTO archived_queues 
                                  (queue_id, user_id, service_id, queue_number, queue_type_id, status, 
                                   created_at, scheduled_time, serving_start_time, elapsed_time, archive_reason) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $reason = "Auto-archived - status: " . $row['status'];
                    $stmt->execute([
                        $row['id'],
                        $row['user_id'], 
                        $row['service_id'],
                        $row['queue_number'],
                        $row['queue_type_id'],
                        $row['status'],
                        $row['created_at'],
                        $row['scheduled_time'],
                        $row['serving_start_time'],
                        $row['elapsed_time'],
                        $reason
                    ]);
                    
                    // Delete from queues table
                    $deleteStmt = $this->pdo->prepare("DELETE FROM queues WHERE id = ?");
                    $deleteStmt->execute([$row['id']]);
                    
                    $archivedCount++;
                    $this->pdo->commit();
                } catch (Exception $e) {
                    $this->pdo->rollback();
                    error_log("Error archiving queue {$row['id']}: " . $e->getMessage());
                }
            }
            
            return $archivedCount;

        } catch (Exception $e) {
            error_log("Auto archive error: " . $e->getMessage());
            return 0;
        }
    }
}

// Handle requests
try {
    $user = getCurrentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }

    $archive = new UserQueueArchive($pdo);

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['action']) && $_GET['action'] === 'auto-archive') {
                // Auto archive completed/cancelled queues
                $count = $archive->autoArchiveCompleted();
                echo json_encode(['success' => true, 'archived_count' => $count]);
            } else {
                // Get user's archived queues
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                $archives = $archive->getArchivedQueues($user['id'], $limit, $offset);
                echo json_encode($archives);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['queue_id'])) {
                throw new Exception("Queue ID is required");
            }
            
            // Verify the queue belongs to the current user
            $stmt = $pdo->prepare("SELECT user_id FROM queues WHERE id = ?");
            $stmt->execute([$data['queue_id']]);
            $queueOwner = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$queueOwner || $queueOwner['user_id'] != $user['id']) {
                throw new Exception("Queue not found or access denied");
            }
            
            $reason = $data['reason'] ?? 'Manual archive by user';
            $archive->archiveQueue($data['queue_id'], $reason);
            echo json_encode(['success' => true, 'message' => 'Queue archived successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>