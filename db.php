<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'mysql-flow-app.alwaysdata.net');
define('DB_USER', 'flow-app');
define('DB_PASS', 'kier253022');
define('DB_NAME', 'flow-app_queue');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // For backward compatibility, create a mysqli-like object
    $conn = $pdo;
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]));
}
?>