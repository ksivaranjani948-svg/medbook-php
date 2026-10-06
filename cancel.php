<?php
require "config.php";
require "auth.php";

$id = $_GET["id"] ?? "";

if ($id !== "") {
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php");
exit;
?>
