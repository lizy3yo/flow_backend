<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'mysql-flow-app.alwaysdata.net');
define('DB_USER', 'flow-app');
define('DB_PASS', 'kier253022');
define('DB_NAME', 'flow-app_queue');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}
?>