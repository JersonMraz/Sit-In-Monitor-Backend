<?php
$db_server = "127.0.0.1:3307";
$db_user = "root";
$db_pass = "";
$db_name = "sullano";
$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
