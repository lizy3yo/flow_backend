<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

include "db.php";
require_once "Helper/notificationhelper.php";
$notificationHelper = new NotificationHelper($conn);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        try {
            $admin_id = $_GET['admin_id'] ?? null;
            $service_id = $_GET['id'] ?? null;
            
            if ($service_id) {
                $stmt = $conn->prepare("SELECT * FROM services WHERE id = ? AND is_archived = 0");
                $stmt->bind_param('i', $service_id);
            } else if ($admin_id) {
                $stmt = $conn->prepare("SELECT * FROM services WHERE admin_id = ? AND is_archived = 0 ORDER BY name");
                $stmt->bind_param('i', $admin_id);
            } else {
                $stmt = $conn->prepare("SELECT * FROM services WHERE is_archived = 0 ORDER BY name");
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($service_id) {
                $service = $result->fetch_assoc();
                echo json_encode($service);
            } else {
                $services = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode($services);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $data = json_decode(file_get_contents('php://input'));
            
            if (!$data || !$data->admin_id) {
                throw new Exception('Invalid JSON data or missing admin_id');
            }

            $stmt = $conn->prepare("INSERT INTO services 
                (admin_id, name, description, hours_start, hours_end, max_queues, 
                address, location, email, phone, ticket_prefix) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                throw new Exception($conn->error);
            }

            $stmt->bind_param("issssssssss", 
                $data->admin_id,
                $data->name,
                $data->description,
                $data->hours_start,
                $data->hours_end,
                $data->max_queues,
                $data->address,
                $data->location,
                $data->email,
                $data->phone,
                $data->ticket_prefix
            );
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            $newId = $conn->insert_id;
            $data->id = $newId;

            // Add notification
            $notificationHelper->createNotification(
                $data->admin_id,
                'service',
                $notificationHelper->formatServiceAction('create', $data->name),
                'create',
                $newId
            );

            echo json_encode([
                'success' => true,
                'message' => 'Service created successfully',
                'data' => $data
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        break;

    case 'PUT':
        try {
            $data = json_decode(file_get_contents('php://input'));
            $id = $_GET['id'];
            
            if (!$data || !$id) {
                throw new Exception('Invalid data or missing ID');
            }

            $stmt = $conn->prepare("UPDATE services SET 
                name = ?, 
                description = ?, 
                hours_start = ?, 
                hours_end = ?, 
                max_queues = ?, 
                address = ?, 
                location = ?, 
                email = ?, 
                phone = ?, 
                ticket_prefix = ? 
                WHERE id = ?");

            if (!$stmt) {
                throw new Exception($conn->error);
            }

            $stmt->bind_param("sssssssssss",
                $data->name,
                $data->description,
                $data->hours_start,
                $data->hours_end,
                $data->max_queues,
                $data->address,
                $data->location,
                $data->email,
                $data->phone,
                $data->ticket_prefix,
                $id
            );
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            // Add notification
            $notificationHelper->createNotification(
                $data->admin_id,
                'service',
                $notificationHelper->formatServiceAction('edit', $data->name),
                'edit',
                $id
            );

            if ($stmt->affected_rows === 0) {
                throw new Exception('No services found with ID: ' . $id);
            }

            echo json_encode([
                'success' => true,
                'message' => 'services updated successfully'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        break;

    case 'DELETE':
        try {
            $id = $_GET['id'];
            
            if (!$id) {
                throw new Exception('Missing ID parameter');
            }

            // First, get the service details
            $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $service = $stmt->get_result()->fetch_assoc();

            // Insert into archived_services
            $archiveStmt = $conn->prepare("INSERT INTO archived_services 
                (service_id, admin_id, name, description, hours_start, hours_end, 
                max_queues, address, location, email, phone, ticket_prefix, archive_reason) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $reason = "Manual archive by admin";
            $archiveStmt->bind_param("iissssissssss", 
                $service['id'],
                $service['admin_id'],
                $service['name'],
                $service['description'],
                $service['hours_start'],
                $service['hours_end'],
                $service['max_queues'],
                $service['address'],
                $service['location'],
                $service['email'],
                $service['phone'],
                $service['ticket_prefix'],
                $reason
            );
            
            $archiveStmt->execute();

            // Mark as archived instead of deleting
            $updateStmt = $conn->prepare("UPDATE services SET is_archived = 1 WHERE id = ?");
            $updateStmt->bind_param("i", $id);
            $updateStmt->execute();

            // Add notification
            $notificationHelper->createNotification(
                $service['admin_id'],
                'service',
                $notificationHelper->formatServiceAction('archive', $service['name']),
                'archive',
                $id
            );

            echo json_encode([
                'success' => true,
                'message' => 'Service archived successfully'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        break;
}

$conn->close();