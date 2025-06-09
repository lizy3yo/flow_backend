<?php
header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->otp) || !isset($data->password) || !isset($data->email)) {
        echo json_encode(['success' => false, 'message' => 'OTP, email and new password are required']);
        exit;
    }

    $otp = trim($data->otp);
    $email = trim($data->email);
    $password = password_hash($data->password, PASSWORD_DEFAULT);

    try {
        // Start transaction
        $conn->begin_transaction();

        // First get the user ID from email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Invalid email address');
        }

        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Check if there are any reset tokens for this user
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM password_resets WHERE user_id = ?");
        $checkStmt->bind_param("i", $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $count = $checkResult->fetch_assoc()['count'];
        
        if ($count == 0) {
            throw new Exception('No reset request found. Please request a new password reset.');
        }

        // Get the most recent valid reset token
        $stmt = $conn->prepare("
            SELECT token, expiry 
            FROM password_resets 
            WHERE user_id = ? 
            ORDER BY expiry DESC 
            LIMIT 1
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('No valid reset token found');
        }

        $reset = $result->fetch_assoc();
        
        // Check if token is expired
        $now = new DateTime();
        $expiryTime = new DateTime($reset['expiry']);
        
        if ($now > $expiryTime) {
            throw new Exception('OTP has expired. Please request a new one.');
        }
        
        // Verify the OTP
        if (!password_verify($otp, $reset['token'])) {
            throw new Exception('Invalid OTP code. Please check and try again.');
        }

        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $password, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update password');
        }

        // Delete all reset tokens for this user
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Password reset successful'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
}
?>