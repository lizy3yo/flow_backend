<?php
header('Access-Control-Allow-Origin: https://flow-i3g6.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
        $pdo->beginTransaction();

        // First get the user ID from email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception('Invalid email address');
        }

        $user_id = $result['id'];

        // Check if there are any reset tokens for this user
        $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM password_resets WHERE user_id = ?");
        $checkStmt->execute([$user_id]);
        $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $count = $checkResult['count'];
        
        if ($count == 0) {
            throw new Exception('No reset request found. Please request a new password reset.');
        }

        // Get the most recent valid reset token
        $stmt = $pdo->prepare("
            SELECT token, expiry 
            FROM password_resets 
            WHERE user_id = ? 
            ORDER BY expiry DESC 
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception('No valid reset token found');
        }

        // Check if token is expired
        $now = new DateTime();
        $expiryTime = new DateTime($result['expiry']);
        
        if ($now > $expiryTime) {
            throw new Exception('OTP has expired. Please request a new one.');
        }
        
        // Verify the OTP
        if (!password_verify($otp, $result['token'])) {
            throw new Exception('Invalid OTP code. Please check and try again.');
        }

        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$password, $user_id]);

        // Delete all reset tokens for this user
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Password reset successful'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
}
?>