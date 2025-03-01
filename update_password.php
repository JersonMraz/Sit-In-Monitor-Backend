<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include "connection.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["username"]) || !isset($data["current_password"]) || !isset($data["new_password"])) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

$username = $data["username"];
$current_password = $data["current_password"];
$new_password = $data["new_password"];

// Fetch existing password from the database
$sql = "SELECT password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($stored_password);

if ($stmt->num_rows > 0) {
    $stmt->fetch();

    // Check if the current password matches the stored password
    if ($stored_password !== $current_password) {
        echo json_encode(["status" => "error", "message" => "Current password is incorrect"]);
        exit;
    }

    // Update password in the database
    $update_sql = "UPDATE users SET password = ? WHERE username = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ss", $new_password, $username);

    if ($update_stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Password updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update password"]);
    }

    $update_stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

$stmt->close();
$conn->close();
