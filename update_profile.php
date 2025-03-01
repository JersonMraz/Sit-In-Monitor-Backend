<?php
include "connection.php"; // Ensure database connection

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["oldUsername"])) {
    echo json_encode(["status" => "error", "message" => "Old username is required"]);
    exit;
}

$oldUsername = $data["oldUsername"];
$username = isset($data["username"]) ? $data["username"] : $oldUsername; // Use new username if changed

$fields = ["firstname", "midname", "lastname", "email", "course", "yearlvl", "username"];
$update_fields = [];

foreach ($fields as $field) {
    if (isset($data[$field]) && !empty($data[$field])) {
        $update_fields[] = "$field = '" . mysqli_real_escape_string($conn, $data[$field]) . "'";
    }
}

if (!empty($update_fields)) {
    $update_query = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE username = '$oldUsername'";

    if (mysqli_query($conn, $update_query)) {
        echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No valid data provided"]);
}
