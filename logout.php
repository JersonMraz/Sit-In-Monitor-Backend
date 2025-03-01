<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$conn = new mysqli("127.0.0.1:3307", "root", "", "sullano");

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["resultStatus" => "Database connection error"]);
    exit();
}

// Read raw POST data
$eData = file_get_contents("php://input");
$dData = json_decode($eData, true);

$username = trim($dData['username'] ?? "");
error_log("📌 Received Data: " . print_r($dData, true));

if (empty($username)) {
    echo json_encode(["resultStatus" => "Username required!"]);
    exit();
}

// Get current session count
$stmt = $conn->prepare("SELECT remaining_session FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $currentSession = (int) $row['remaining_session'];

    if ($currentSession > 0) {
        $newSession = $currentSession - 1;
        $updateStmt = $conn->prepare("UPDATE users SET remaining_session = ? WHERE username = ?");
        $updateStmt->bind_param("is", $newSession, $username);
        $updateStmt->execute();
        $updateStmt->close();

        echo json_encode(["resultStatus" => "Logged out successfully!", "remainingSession" => $newSession]);
    } else {
        echo json_encode(["resultStatus" => "No sessions left!"]);
    }
} else {
    echo json_encode(["resultStatus" => "User not found!"]);
}

$stmt->close();
$conn->close();
ob_end_flush();
