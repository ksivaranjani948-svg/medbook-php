<?php
// Start the session on every page that includes this file.
session_start();

// Database connection settings.
// XAMPP's default MySQL has username "root" and no password.
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "medbook";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
