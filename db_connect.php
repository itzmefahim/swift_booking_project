<?php
$host = "localhost";
$user = "swift_user";
$password = "swift_pass";
$database = "swift_book";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
