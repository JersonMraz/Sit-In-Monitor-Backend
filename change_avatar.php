<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include("connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['profile_url']) || !isset($_POST['username'])) {
        echo json_encode(["status" => "error", "message" => "Invalid request"]);
        exit();
    }

    $username = $_POST['username'];
    $file = $_FILES['profile_url'];

    // Allowed image types
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $fileExtension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode(["status" => "error", "message" => "Invalid file type! Only JPG, JPEG, and PNG are allowed."]);
        exit();
    }

    // Generate a unique filename (avoid name conflicts)
    $fileName = time() . "_" . uniqid() . "." . $fileExtension;
    $targetPath = "uploads/" . $fileName;

    if (move_uploaded_file($file["tmp_name"], $targetPath)) {
        // Get old profile picture
        $query = "SELECT profile_url FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $oldImage = $row["profile_url"];
        $stmt->close();

        // Delete old image if it's not default.png
        if ($oldImage && $oldImage !== "default.png") {
            $oldPath = "uploads/" . $oldImage;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Update new image filename in the database
        $query = "UPDATE users SET profile_url = ? WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $fileName, $username);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["status" => "success", "profile_url" => $fileName]);
    } else {
        echo json_encode(["status" => "error", "message" => "Upload failed"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
