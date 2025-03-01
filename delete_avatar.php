<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include("connection.php");

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['username'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit();
}

$username = $data['username'];

// Fetch the current avatar
$query = "SELECT profile_url FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit();
}

$currentAvatar = $row["profile_url"];

if ($currentAvatar && $currentAvatar !== "default.png") {
    $filePath = "uploads/" . $currentAvatar;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

$defaultAvatar = "default.png";
$query = "UPDATE users SET profile_url = ? WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $defaultAvatar, $username);
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Avatar deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update database"]);
}
$stmt->close();
