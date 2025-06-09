<?php
header('Content-Type: application/json');
include "db.php";

// Function to get current user from session
function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

class UserQueueArchive {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function archiveQueue($queueId, $reason = null) {
        try {
            $this->conn->begin_transaction();

            // Get queue details before archiving
            $getQueueSql = "SELECT * FROM queues WHERE id = ?";
            $stmt = $this->conn->prepare($getQueueSql);
            $stmt->bind_param("i", $queueId);
            $stmt->execute();
            $result = $stmt->get_result();
            $queue = $result->fetch_assoc();

            if (!$queue) {
                throw new Exception("Queue not found");
            }

            // Insert into archived_queues
            $archiveSql = "INSERT INTO archived_queues 
                          (queue_id, user_id, service_id, queue_number, queue_type_id, status, 
                           created_at, scheduled_time, serving_start_time, elapsed_time, archive_reason) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($archiveSql);
            $stmt->bind_param("iiisisssss", 
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
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to archive queue");
            }

            // Delete from queues table
            $deleteSql = "DELETE FROM queues WHERE id = ?";
            $stmt = $this->conn->prepare($deleteSql);
            $stmt->bind_param("i", $queueId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete queue");
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getArchivedQueues($userId, $limit = 20, $offset = 0) {
        $sql = "SELECT aq.*, s.name as service_name, s.location as service_location 
                FROM archived_queues aq 
                LEFT JOIN services s ON aq.service_id = s.id 
                WHERE aq.user_id = ? 
                AND aq.status IN ('completed', 'cancelled')
                ORDER BY aq.archived_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $archives = [];
        while ($row = $result->fetch_assoc()) {
            $archives[] = $row;
        }
        
        return $archives;
    }

    // Enhanced auto-archive function
    public function autoArchiveCompleted() {
        try {
            // Auto-archive completed and cancelled queues
            $sql = "SELECT * FROM queues WHERE status IN ('completed', 'cancelled')";
            $result = $this->conn->query($sql);
            
            $archivedCount = 0;
            while ($row = $result->fetch_assoc()) {
                $this->conn->begin_transaction();
                
                try {
                    // Insert into archived_queues
                    $archiveSql = "INSERT INTO archived_queues 
                                  (queue_id, user_id, service_id, queue_number, queue_type_id, status, 
                                   created_at, scheduled_time, serving_start_time, elapsed_time, archive_reason) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $this->conn->prepare($archiveSql);
                    $reason = "Auto-archived - status: " . $row['status'];
                    $stmt->bind_param("iiisisssss", 
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
                    );
                    
                    if ($stmt->execute()) {
                        // Delete from queues table
                        $deleteSql = "DELETE FROM queues WHERE id = ?";
                        $deleteStmt = $this->conn->prepare($deleteSql);
                        $deleteStmt->bind_param("i", $row['id']);
                        
                        if ($deleteStmt->execute()) {
                            $archivedCount++;
                            $this->conn->commit();
                        } else {
                            $this->conn->rollback();
                        }
                    } else {
                        $this->conn->rollback();
                    }
                } catch (Exception $e) {
                    $this->conn->rollback();
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

    $archive = new UserQueueArchive($conn);

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
            $checkSql = "SELECT user_id FROM queues WHERE id = ?";
            $stmt = $conn->prepare($checkSql);
            $stmt->bind_param("i", $data['queue_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $queueOwner = $result->fetch_assoc();
            
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

mysqli_close($conn);
?>