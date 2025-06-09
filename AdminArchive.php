<?php

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');
include "db.php";


// Handle GET requests - Fetch archives
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = $_GET['type'] ?? '';
    
    if ($type === 'services') {
        $query = "SELECT * FROM archived_services WHERE restored_at IS NULL ORDER BY archived_at DESC";
    } else {
        $query = "SELECT aq.*, s.name as service_name, u.first_name as user_first_name, u.last_name as user_last_name 
                  FROM archived_queues aq 
                  LEFT JOIN services s ON aq.service_id = s.id 
                  LEFT JOIN users u ON aq.user_id = u.id
                  WHERE aq.restored_at IS NULL 
                  ORDER BY aq.archived_at DESC";
    }
    
    $result = mysqli_query($conn, $query);
    $items = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    echo json_encode($items);
}

// Handle POST requests - Restore/Delete archives
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $type = $data['type'] ?? '';
    $id = $data['id'] ?? 0;
    
    if ($action === 'restore') {
        if ($type === 'service') {
            // First get the service ID from archived_services
            $getServiceQuery = "SELECT service_id FROM archived_services WHERE id = ?";
            $stmt = mysqli_prepare($conn, $getServiceQuery);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $serviceData = mysqli_fetch_assoc($result);
            
            if ($serviceData) {
                // Update the original service
                $reactivateQuery = "UPDATE services SET is_archived = 0 WHERE id = ?";
                $stmt = mysqli_prepare($conn, $reactivateQuery);
                mysqli_stmt_bind_param($stmt, "i", $serviceData['service_id']);
                mysqli_stmt_execute($stmt);
                
                // Delete from archived_services instead of updating
                $deleteQuery = "DELETE FROM archived_services WHERE id = ?";
                $stmt = mysqli_prepare($conn, $deleteQuery);
                mysqli_stmt_bind_param($stmt, "i", $id);
                $success = mysqli_stmt_execute($stmt);
            }
        } else {
            // First get the archived queue data
            $getQueueQuery = "SELECT * FROM archived_queues WHERE id = ?";
            $stmt = mysqli_prepare($conn, $getQueueQuery);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $queueData = mysqli_fetch_assoc($result);
            
            if ($queueData) {
                // Insert back into the queues table
                $reactivateQuery = "INSERT INTO queues (
                    id,
                    user_id,
                    service_id,
                    queue_number,
                    queue_type_id,
                    status,
                    created_at,
                    scheduled_time,
                    estimated_wait,
                    notes,
                    serving_start_time,
                    elapsed_time
                ) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, NULL, '00:00:00')";
                
                $stmt = mysqli_prepare($conn, $reactivateQuery);
                mysqli_stmt_bind_param($stmt, "iiisissis",
                    $queueData['queue_id'],
                    $queueData['user_id'],
                    $queueData['service_id'],
                    $queueData['queue_number'],
                    $queueData['queue_type_id'],
                    $queueData['created_at'],
                    $queueData['scheduled_time'],
                    $queueData['estimated_wait'],
                    $queueData['notes']
                );
                mysqli_stmt_execute($stmt);
                
                // Delete from archived_queues
                $deleteQuery = "DELETE FROM archived_queues WHERE id = ?";
                $stmt = mysqli_prepare($conn, $deleteQuery);
                mysqli_stmt_bind_param($stmt, "i", $id);
                $success = mysqli_stmt_execute($stmt);
            }
        }
        
        echo json_encode(['success' => isset($success) && $success]);
    }
    
    else if ($action === 'delete') {
        // First check if the item exists
        if ($type === 'service') {
            $checkQuery = "SELECT id FROM archived_services WHERE id = ?";
        } else {
            $checkQuery = "SELECT id FROM archived_queues WHERE id = ?";
        }
        
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Item not found']);
            exit;
        }
        
        // Proceed with deletion
        if ($type === 'service') {
            $query = "DELETE FROM archived_services WHERE id = ?";
        } else {
            $query = "DELETE FROM archived_queues WHERE id = ?";
        }
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        $success = mysqli_stmt_execute($stmt);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Item deleted successfully' : 'Failed to delete item'
        ]);
    }
}

// Archive cron job function
function archiveOldQueues() {
    global $conn;
    
    // Archive completed and declined queues older than 24 hours
    $query = "INSERT INTO archived_queues 
              SELECT *, NOW(), NULL, NULL, 'Auto-archived after 24 hours'
              FROM queues 
              WHERE (status IN ('completed', 'declined') 
              AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))
              OR (scheduled_time < NOW() AND status = 'pending')";
    
    mysqli_query($conn, $query);
    
    // Remove archived queues from main table
    $cleanup = "DELETE FROM queues 
                WHERE (status IN ('completed', 'declined') 
                AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                OR (scheduled_time < NOW() AND status = 'pending')";
    
    mysqli_query($conn, $cleanup);
}

// This can be called by a cron job
if (isset($_GET['cron']) && $_GET['cron'] === 'archive') {
    archiveOldQueues();
    echo json_encode(['success' => true, 'message' => 'Archive process completed']);
}

mysqli_close($conn);
?>