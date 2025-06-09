<?php

session_start();
include "db.php";

header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $admin_id = isset($_GET['admin_id']) ? $_GET['admin_id'] : null;
        
        if ($admin_id) {
            // Get specific establishment by admin_id
            $stmt = $conn->prepare("SELECT e.*, a.name, a.email 
                                  FROM establishments e 
                                  JOIN admins a ON e.admin_id = a.id 
                                  WHERE e.admin_id = ?");
            $stmt->bind_param('i', $admin_id);
        } else {
            // Get all establishments
            $stmt = $conn->prepare("SELECT e.*, a.name, a.email 
                                  FROM establishments e 
                                  JOIN admins a ON e.admin_id = a.id");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $establishments = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode($establishments);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>