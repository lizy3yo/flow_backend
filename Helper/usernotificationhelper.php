<?php

class UserNotificationHelper {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function createNotification($userId, $type, $message, $queueNumber = null, $serviceId = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO user_notifications 
            (user_id, type, message, queue_number, service_id, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param("isssi", $userId, $type, $message, $queueNumber, $serviceId);
        return $stmt->execute();
    }

    // Queue Status Notifications
    public function notifyQueueJoined($userId, $queueNumber, $serviceName, $serviceId) {
        $message = "You have joined the queue for $serviceName. Your number is $queueNumber.";
        return $this->createNotification($userId, 'queue_joined', $message, $queueNumber, $serviceId);
    }

    public function notifyPositionUpdate($userId, $queueNumber, $position, $serviceId) {
        $message = "Your position in queue ($queueNumber) is now #$position.";
        return $this->createNotification($userId, 'queue_position', $message, $queueNumber, $serviceId);
    }

    public function notifyNextInLine($userId, $queueNumber, $serviceId) {
        $message = "You're next in line! Queue number $queueNumber.";
        return $this->createNotification($userId, 'queue_next', $message, $queueNumber, $serviceId);
    }

    public function notifyNowServing($userId, $queueNumber, $serviceId) {
        $message = "It's your turn! Queue number $queueNumber is now being served.";
        return $this->createNotification($userId, 'queue_served', $message, $queueNumber, $serviceId);
    }

    public function notifyQueueCompleted($userId, $queueNumber, $serviceId) {
        $message = "Your queue session ($queueNumber) has been completed.";
        return $this->createNotification($userId, 'queue_completed', $message, $queueNumber, $serviceId);
    }

    public function notifyQueueCancelled($userId, $queueNumber, $serviceId) {
        $message = "Your queue number $queueNumber has been cancelled.";
        return $this->createNotification($userId, 'queue_cancelled', $message, $queueNumber, $serviceId);
    }

    public function notifyQueueWaiting($userId, $queueNumber, $serviceId) {
        $message = "Your queue number $queueNumber is now in the waiting list.";
        return $this->createNotification($userId, 'queue_waiting', $message, $queueNumber, $serviceId);
    }

    // Scheduled Appointment Reminders
    public function notifyUpcomingAppointment($userId, $queueNumber, $serviceName, $scheduledTime, $serviceId) {
        $formattedTime = date('h:i A', strtotime($scheduledTime));
        $message = "Reminder: Your appointment at $serviceName is scheduled for $formattedTime.";
        return $this->createNotification($userId, 'appointment_reminder', $message, $queueNumber, $serviceId);
    }

    // Wait Time Updates
    public function notifyWaitTimeUpdate($userId, $queueNumber, $estimatedWait, $serviceId) {
        $message = "Estimated waiting time for queue $queueNumber is now $estimatedWait minutes.";
        return $this->createNotification($userId, 'wait_time', $message, $queueNumber, $serviceId);
    }

    // Admin-triggered Notifications
    public function notifyServiceStatus($userId, $serviceName, $status, $serviceId) {
        $message = "Service status for $serviceName has changed to: $status";
        return $this->createNotification($userId, 'service_status', $message, null, $serviceId);
    }

    // Helper method to get unread count
    public function getUnreadCount($userId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count 
            FROM user_notifications 
            WHERE user_id = ? AND read_at IS NULL
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}