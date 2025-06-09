<?php

include "db.php";

header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $admin_id = $_GET['admin_id'] ?? null;
        
        // Always ensure we return an array, even if empty
        if (!$admin_id) {
            echo json_encode([]);
            exit();
        }
        
        // Get services with queue counts
        $sql = "SELECT s.*, 
                COUNT(CASE WHEN q.queue_type_id = 1 AND q.status IN ('pending', 'waiting') THEN 1 END) as regular_queue,
                COUNT(CASE WHEN q.queue_type_id = 2 AND q.status IN ('pending', 'waiting') THEN 1 END) as priority_queue,
                COUNT(CASE WHEN q.queue_type_id = 3 AND q.status IN ('pending', 'waiting') THEN 1 END) as scheduled_queue,
                COUNT(CASE WHEN q.status = 'completed' THEN 1 END) as completed_queue
                FROM services s
                LEFT JOIN queues q ON s.id = q.service_id
                WHERE s.admin_id = ? AND s.is_archived = 0
                GROUP BY s.id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_id]);
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Always return an array
        echo json_encode($departments ?: []);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>