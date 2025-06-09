<?php

include "db.php";

// CORS configuration for Render deployment
header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $admin_id = $_GET['admin_id'] ?? null;
        
        // Get services with queue counts
        $sql = "SELECT s.*, 
                COUNT(CASE WHEN q.queue_type_id = 1 AND q.status IN ('pending', 'waiting') THEN 1 END) as regular_queue,
                COUNT(CASE WHEN q.queue_type_id = 2 AND q.status IN ('pending', 'waiting') THEN 1 END) as priority_queue,
                COUNT(CASE WHEN q.queue_type_id = 3 AND q.status IN ('pending', 'waiting') THEN 1 END) as scheduled_queue,
                COUNT(CASE WHEN q.status = 'completed' THEN 1 END) as completed_queue
                FROM services s
                LEFT JOIN queues q ON s.id = q.service_id";

        if ($admin_id) {
            $sql .= " WHERE s.admin_id = ? AND s.is_archived = 0";
            $sql .= " GROUP BY s.id";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $admin_id);
        } else {
            $sql .= " WHERE s.is_archived = 0";
            $sql .= " GROUP BY s.id";
            $stmt = $conn->prepare($sql);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $departments = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode($departments);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>