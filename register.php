<?php
include 'connection.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$requiredFields = ["idno", "firstname", "midname", "lastname", "course", "yearlvl", "username", "email", "password"];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        echo json_encode(["success" => false, "message" => "Field $field is required."]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idno = $data['idno'];
    $firstname = $data['firstname'];
    $midname = $data['midname'];
    $lastname = $data['lastname'];
    $course = $data['course'];
    $yearlvl = $data['yearlvl'];
    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];

    $checkQuery = "SELECT * FROM users WHERE email='$email' OR username='$username' OR idno='$idno'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        while ($row = $checkResult->fetch_assoc()) {
            if ($row['email'] == $email) {
                $errors[] = "Email is already taken.";
            }
            if ($row['username'] == $username) {
                $errors[] = "Username is already taken.";
            }
            if ($row['idno'] == $idno) {
                $errors[] = "IDNO is already exists.";
            }
        }
        echo json_encode(["message" => implode(" ", $errors), "success" => false]);
        exit;
    } else {
        // Insert new user
        $query = "INSERT INTO users (idno, firstname, midname, lastname, course, yearlvl, username, email, password, remaining_session) 
                  VALUES ('$idno', '$firstname', '$midname', '$lastname', '$course', '$yearlvl', '$username', '$email', '$password', 30)";

        if ($conn->query($query) === TRUE) {
            echo json_encode(["message" => "Successfully registered!", "success" => true]);
        } else {
            echo json_encode(["message" => "Error: " . $conn->error, "success" => false]);
        }
    }
}

$conn->close();
