<?php
header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {  
    try {
        $service_id = $_GET['service_id'] ?? null;
        $status = $_GET['status'] ?? null;

        if ($service_id && isset($_GET['count'])) {
            // Get count of all active queues for the service
            $stmt = $conn->prepare(
                "SELECT COUNT(*) as count 
                FROM queues 
                WHERE service_id = ? 
                AND status IN ('pending', 'waiting', 'serving')"
            );
            $stmt->bind_param('i', $service_id);
        } else if ($service_id && $status === 'active') {
            // Get all active queues for the service
            $stmt = $conn->prepare(
                "SELECT * FROM queues 
                WHERE service_id = ? 
                AND status IN ('pending', 'waiting', 'serving')"
            );
            $stmt->bind_param('i', $service_id);
        } else if ($service_id) {
            // Get all queues for the service
            $stmt = $conn->prepare("SELECT * FROM queues WHERE service_id = ?");
            $stmt->bind_param('i', $service_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (isset($_GET['count'])) {
            $count = $result->fetch_assoc()['count'];
            echo json_encode(['count' => (int)$count]);
        } else {
            $queues = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($queues);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}