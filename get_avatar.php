<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include("connection.php"); // Ensure this connects to your database

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $query = "SELECT profile_url FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($profile_url);
    $stmt->fetch();
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "profile_url" => $profile_url ? $profile_url : "default.jpg"
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
