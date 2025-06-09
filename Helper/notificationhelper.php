<?php

class NotificationHelper {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function createNotification($adminId, $type, $message, $action, $entityId = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO admin_notifications 
            (admin_id, type, message, action, entity_id, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param(
            "isssi",
            $adminId,
            $type,
            $message,
            $action,
            $entityId
        );
        
        return $stmt->execute();
    }

    public function formatServiceAction($action, $serviceName) {
        switch ($action) {
            case 'create':
                return "Created new service: $serviceName";
            case 'edit':
                return "Updated service: $serviceName";
            case 'archive':
                return "Archived service: $serviceName";
            case 'restore':
                return "Restored service: $serviceName";
            case 'delete':
                return "Permanently deleted service: $serviceName";
            default:
                return "Modified service: $serviceName";
        }
    }

    public function formatQueueAction($action, $queueNumber, $serviceName) {
        switch ($action) {
            case 'accept':
                return "Accepted queue $queueNumber for $serviceName";
            case 'decline':
                return "Declined queue $queueNumber for $serviceName";
            case 'serve':
                return "Started serving queue $queueNumber for $serviceName";
            case 'complete':
                return "Completed queue $queueNumber for $serviceName";
            case 'archive':
                return "Archived queue $queueNumber from $serviceName";
            case 'restore':
                return "Restored queue $queueNumber to $serviceName";
            case 'delete':
                return "Permanently deleted queue $queueNumber";
            default:
                return "Modified queue $queueNumber";
        }
    }

    public function formatStatusAction($newStatus) {
        return "Changed establishment status to: $newStatus";
    }

    public function formatProfileAction($field) {
        switch ($field) {
            case 'avatar':
                return "Updated profile picture";
            case 'name':
                return "Updated admin name";
            case 'email':
                return "Updated admin email";
            case 'password':
                return "Changed admin password";
            default:
                return "Updated profile information";
        }
    }

    public function formatEstablishmentAction($field) {
        switch ($field) {
            case 'description':
                return "Updated establishment description";
            case 'location':
                return "Updated establishment location";
            case 'hours':
                return "Updated operating hours";
            case 'contact':
                return "Updated contact information";
            default:
                return "Updated establishment information";
        }
    }
}