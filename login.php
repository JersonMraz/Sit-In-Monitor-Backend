<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$conn = new mysqli("127.0.0.1:3307", "root", "", "sullano");

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["resultStatus" => "Database connection error"]);
    exit();
}

// Read the raw POST data
$eData = file_get_contents("php://input");

// Debug: Log received raw data
error_log("📌 Raw Data Received: " . $eData);

// Decode JSON data
$dData = json_decode($eData, true);

// Debug: Log decoded data
error_log("📌 Decoded Data: " . print_r($dData, true));

$username = trim($dData['username'] ?? "");
$password = trim($dData['password'] ?? "");

if (empty($username) || empty($password)) {
    echo json_encode(["resultStatus" => "All fields are required!"]);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($password === $row['password']) {
        $resultStatus = "Logged in successfully!";
        $userData = array(
            "id" => $row['id'],
            "idno" => $row['idno'],
            "firstname" => $row['firstname'],
            "lastname" => $row['lastname'],
            "midname" => $row['midname'],
            "course" => $row['course'],
            "yearlvl" => $row['yearlvl'],
            "email" => $row['email'],
            "username" => $row['username'],
            "password" => $row['password'],
            "profile_url" => $row['profile_url'],
            "session" => $row['remaining_session']
        );
    } else {
        $resultStatus = "Invalid password!";
    }
} else {
    $resultStatus = "Invalid username!";
}
$stmt->close();
$conn->close();

$response = array("resultStatus" => $resultStatus, "user" => $userData ?? null);
echo json_encode($response);
